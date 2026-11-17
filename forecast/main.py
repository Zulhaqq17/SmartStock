"""
This is the forecasting service or the automation part for module 5.

What it does for one product:
  1. Reads the product's daily usage history (stock_transactions of type 'used').
  2. Forecasts demand for the next N days with a simple, explainable model:
       exponential smoothing (level) x day-of-week pattern.
  3. Simulates FEFO (First-Expired-First-Out) against the forecast demand to predict how much
     stock will spoil before it can be used.
  4. Recommends a reorder quantity so stock does not drop below the minimum level.
  5. Measures its own accuracy by back-testing on the last 7 days of real data.

Laravel (PHP) calls GET /forecast/{product_id} and stores the result in `demand_forecasts`.
This service only READS the database. It never writes.
"""
from email import errors
import os
from datetime import date, timedelta
from typing import Optional

import pymysql
import pymysql.cursors
from fastapi import FastAPI, HTTPException

app = FastAPI(title="SmartStock Forecast Service")

MIN_HISTORY_DAYS = 14      # below this we refuse to forecast ("insufficient_data")
HISTORY_WINDOW_DAYS = 90   # how far back to read usage
BACKTEST_DAYS = 7          # days held out to measure accuracy
SEASONALITY_MIN_DAYS = 28  # need 4 weeks before we trust a day-of-week pattern
ALPHAS = [0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9]


# --- database ---
# create connection between the forecasting service and mysql database
def get_db():
    return pymysql.connect(
        host=os.environ.get("DB_HOST", "db"),
        port=int(os.environ.get("DB_PORT", "3306")),
        user=os.environ["DB_USERNAME"],
        password=os.environ["DB_PASSWORD"],
        database=os.environ["DB_DATABASE"],
        cursorclass=pymysql.cursors.DictCursor, # organise row using column names instead of numbered positions
    )

# Get product info, usage history, and batches from the database. 
def load_product(cur, product_id: int) -> Optional[dict]:
    cur.execute(
        "SELECT id, product_name, unit_of_measure, min_stock_level FROM products WHERE id = %s", # %s placeholder je ni
        (product_id,),
    )
    return cur.fetchone() # return product info


def load_daily_usage(cur, product_id: int, start: date, end: date) -> dict:
    """{date: quantity used that day} (positive numbers)."""
    cur.execute(
        """SELECT DATE(st.transaction_date) AS d, SUM(-st.quantity_changed) AS qty
           FROM stock_transactions st
           JOIN batches b ON b.id = st.batch_id
           WHERE b.product_id = %s AND st.transaction_type = 'used'
             AND DATE(st.transaction_date) BETWEEN %s AND %s
           GROUP BY DATE(st.transaction_date)""",
        (product_id, start, end),
    )
    return {row["d"]: float(row["qty"]) for row in cur.fetchall()}

# get current batch for product with quantity > 0
def load_batches(cur, product_id: int) -> list:
    cur.execute(
        """SELECT id, batch_number, current_quantity, expiry_date
           FROM batches WHERE product_id = %s AND current_quantity > 0""",
        (product_id,),
    )
    return [
        {
            "batch_id": r["id"],
            "batch_number": r["batch_number"],
            "qty": float(r["current_quantity"]),
            "expiry": r["expiry_date"],  # date or None
        }
        for r in cur.fetchall()
    ]


# --- the model ---
# This thing continuously count daily and missing days  consider as 0 
def build_series(usage: dict, end: date) -> list:
    """Continuous daily series [(date, qty)] from the first day with usage up to `end`. Missing days = 0."""
    if not usage:
        return []
    start = min(usage)
    days = (end - start).days + 1
    return [(start + timedelta(days=i), usage.get(start + timedelta(days=i), 0.0)) for i in range(days)]

# calculate day of week patterns

def weekday_indices(series: list) -> list: # list mean we expect the data to be in list
    """How busy each weekday is compared with the average day (1.0 = average). Monday = 0."""
    if len(series) < SEASONALITY_MIN_DAYS: #  minimum hari 28
        return [1.0] * 7
    overall = sum(quantity for _, quantity in series) / len(series) # sebab list akan bagi hari dekat quantity, using _ means kita taknak nama hari kita nak quantity je
    if overall <= 0:
        return [1.0] * 7
    weekday_patterns = [] # creates a list to store the usage patterns for each weekday
    for weekday in range(7): # berapa hari seminggu start kira dari 0 -> 6 = 7 hari
        vals = [quantity for day, quantity in series if day.weekday() == weekday] # quantity belong to one weekday
        mean = sum(vals) / len(vals) if vals else overall # average usage for one weekday
        weekday_patterns.append(max(0.2, min(3.0, mean / overall)))
    return weekday_patterns

# --- main model function ---
# calculate which day is the busiest or not
def fit(series: list) -> dict:
    """Pick the smoothing factor (alpha) with the smallest one-step-ahead error, return the final level."""
    weekday_patterns = weekday_indices(series)
    adjusted_usage = [quantity / weekday_patterns[day.weekday()] for day, quantity in series]
    starting_level = sum(adjusted_usage[:7]) / len(adjusted_usage[:7])

    best_result = None
    for alpha in ALPHAS:
        current_level, total_squared_error = starting_level, 0.0
        for actual_usage in adjusted_usage:
            total_squared_error += (actual_usage - current_level) ** 2
            current_level = alpha * actual_usage + (1 - alpha) * current_level
        if best_result is None or total_squared_error < best_result[0]:
            best_result = (total_squared_error, alpha, current_level)
    return {"alpha": best_result[1], "level": best_result[2], "idx": weekday_patterns}


# train model to predict future demand 
def predict(model: dict, start: date, days: int) -> list:
    return [max(0.0, model["level"] * model["idx"][(start + timedelta(days=i)).weekday()]) for i in range(days)]

# test how accurate the forecast model is 
def backtest(series: list) -> dict:
    """Hide the last 7 days, predict them, compare with what really happened."""
    if len(series) < MIN_HISTORY_DAYS + BACKTEST_DAYS:
        return {"wape": None, "mae": None, "accuracy": None}
    training_data = series[:-BACKTEST_DAYS]
    testing_data = series[-BACKTEST_DAYS:]
    forecast_model = fit(training_data)
    predicted_usage = predict(forecast_model, testing_data[0][0], BACKTEST_DAYS)
    prediction_errors = [abs(p - a) for p, (_, a) in zip(predicted_usage, testing_data)]
    mean_absolute_error = sum(prediction_errors) / len(prediction_errors)
    total_actual_usage = sum(a for _, a in testing_data)
    if total_actual_usage <= 0:
        return {"wape": None, "mae": round(mean_absolute_error, 2), "accuracy": None}
    weighted_absolute_percentage_error = sum(prediction_errors) / total_actual_usage          # weighted absolute percentage error
    return {"wape": round(weighted_absolute_percentage_error, 4), "mae": round(mean_absolute_error, 2), "accuracy": round(max(0.0, 100 * (1 - weighted_absolute_percentage_error)), 2)}



# --- FEFO simulation ---
def simulate_fefo(batches: list, daily_demand: list, today: date) -> dict:
    """
    Simulate daily stock usage by taking stock from the batch
    that expires first.
    Remaining stock that expires is recorded as spoilage.
    Stock that has already expired is reported separately.
    """

    # Calculate the quantity of stock that has already expired
    already_expired_quantity = sum(
        batch["qty"]
        for batch in batches
        if batch["expiry"] is not None and batch["expiry"] < today
    )

    # get batch that have not expired yet
    usable_batches = [
        dict(batch)
        for batch in batches
        if batch["expiry"] is None or batch["expiry"] >= today
    ]

    # arrange batches by earliest expiry date first
    usable_batches.sort(
        key=lambda batch: (
            batch["expiry"] is None,
            batch["expiry"] or date.max
        )
    )

    # Calculate the total usable stock before simulation
    total_usable_stock = sum(batch["qty"] for batch in usable_batches)

    # Create an empty list to store spoiled batches
    spoiled_batches = []

    # Simulate stock usage for each forecast day
    for day_index, forecasted_demand in enumerate(daily_demand):

        current_day = today + timedelta(days=day_index)

        # Quantity of stock needed for this day
        remaining_demand = forecasted_demand

        # Take stock from batches that expire first
        for batch in usable_batches:

            if remaining_demand <= 0:
                break

            if (
                batch["qty"] > 0
                and (batch["expiry"] is None or batch["expiry"] >= current_day)
            ):
                stock_taken = min(batch["qty"], remaining_demand)

                batch["qty"] -= stock_taken
                remaining_demand -= stock_taken

        # Check for batches that expire on or before the current day
        for batch in usable_batches:

            if (
                batch["expiry"] is not None
                and batch["expiry"] <= current_day
                and batch["qty"] > 0
            ):
                spoiled_batches.append({
                    "batch_id": batch["batch_id"],
                    "batch_number": batch["batch_number"],
                    "qty": round(batch["qty"], 2),
                    "expiry": batch["expiry"].isoformat()
                })

                # Remove the spoiled stock from usable quantity
                batch["qty"] = 0.0

    return {
        "usable_stock": total_usable_stock,
        "already_expired_qty": already_expired_quantity,
        "spoilage": sum(batch["qty"] for batch in spoiled_batches),
        "spoilage_batches": spoiled_batches,
    }


# how much should we order???
# FORMULA: reorder = demand + spoilage + minimum stock - current usable stock
def recommend_reorder(demand: float, spoilage: float, min_level: float, usable_stock: float) -> float:
    """Order enough to END the period with at least the minimum level: demand + spoilage + minimum - stock."""
    return max(0.0, demand + spoilage + min_level - usable_stock)


# --- API ---
# main function that put all 
def compute_forecast(product: dict, usage: dict, batches: list, today: date, horizon: int) -> dict:
    usage_history = build_series(usage, today - timedelta(days=1))   # up to yesterday (today is incomplete)
    forecast_result = {
        "product_id": product["id"],
        "horizon_days": horizon,
        "history_days": len(usage_history),
        "predicted_demand_qty": 0.0,
        "predicted_spoilage_qty": 0.0,
        "recommended_reorder_qty": 0.0,
        "confidence_score": None,
        "current_usable_stock": 0.0,
        "already_expired_qty": 0.0,
        "spoilage_batches": [],
        "daily_forecast": [],
    }
    if len(usage_history) < MIN_HISTORY_DAYS:
        return {**forecast_result, "method": "insufficient_data",
                "note": f"Need at least {MIN_HISTORY_DAYS} days of usage history, found {len(usage_history)}."}

    # build the forecast model
    forecasting_model = fit(usage_history)
    # predict product usage for requested number of days
    predicted_daily_usage = predict(forecasting_model, today, horizon)
    fefo_simulation = simulate_fefo(batches, predicted_daily_usage, today)
    total_predicted_demand = sum(predicted_daily_usage)
    backtest_result = backtest(usage_history)
    minimum_stock_level = float(product["min_stock_level"])

    return {
        **forecast_result,
        "method": "exp_smoothing_x_weekday" if len(usage_history) >= SEASONALITY_MIN_DAYS else "exp_smoothing",
        "alpha": forecasting_model["alpha"],
        "predicted_demand_qty": round(total_predicted_demand, 2),
        "predicted_spoilage_qty": round(fefo_simulation["spoilage"], 2),
        "recommended_reorder_qty": round(recommend_reorder(total_predicted_demand, fefo_simulation["spoilage"], minimum_stock_level, fefo_simulation["usable_stock"]), 2),
        "confidence_score": backtest_result["accuracy"],
        "backtest": backtest_result,
        "current_usable_stock": round(fefo_simulation["usable_stock"], 2),
        "already_expired_qty": round(fefo_simulation["already_expired_qty"], 2),
        "spoilage_batches": fefo_simulation["spoilage_batches"],
        "daily_forecast": [{"date": (today + timedelta(days=i)).isoformat(), "qty": round(q, 2)}
                           for i, q in enumerate(predicted_daily_usage)],
    }

# API endpoint to check whether the service running onot
@app.get("/health")
def health():
    return {"status": "ok"}

# forecast particular product
@app.get("/forecast/{product_id}")
def forecast(product_id: int, horizon: int = 7):
    if not 1 <= horizon <= 30:
        raise HTTPException(status_code=400, detail="horizon must be between 1 and 30")

    today = date.today() # get today's data
    database_connection = get_db()
    try:
        with database_connection.cursor() as cur:
            product = load_product(cur, product_id)
            # check if the product exist or not
            if product is None:
                raise HTTPException(status_code=404, detail="product not found")
            # get product daily usage history
            historical_usage = load_daily_usage(cur, product_id, today - timedelta(days=HISTORY_WINDOW_DAYS), today - timedelta(days=1))
            product_batches = load_batches(cur, product_id)
    finally:
        database_connection.close()

    return compute_forecast(product, historical_usage, product_batches, today, horizon)
