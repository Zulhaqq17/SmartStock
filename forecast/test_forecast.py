"""Ini self test jee (no database needed). Run: python test_forecast.py"""
from datetime import date, timedelta
from main import build_series, compute_forecast, simulate_fefo, recommend_reorder

today = date(2026, 11, 2)  # a Monday
# 60 days of usage: base 6/day, busier on Saturday, quieter on Sunday
usage = {}
for i in range(1, 61):
    d = today - timedelta(days=i)
    usage[d] = 6.0 * {5: 1.4, 6: 0.6}.get(d.weekday(), 1.0)

product = {"id": 1, "product_name": "Fresh Milk", "unit_of_measure": "litre", "min_stock_level": 20}
batches = [
    {"batch_id": 1, "batch_number": "MILK-A1", "qty": 15.0, "expiry": today - timedelta(days=3)},  # already expired
    {"batch_id": 2, "batch_number": "MILK-A2", "qty": 40.0, "expiry": today + timedelta(days=3)},  # will spoil partly
]

r = compute_forecast(product, usage, batches, today, 7)
print({k: r[k] for k in ("method", "predicted_demand_qty", "predicted_spoilage_qty",
                         "recommended_reorder_qty", "confidence_score", "current_usable_stock", "already_expired_qty")})
assert r["method"] == "exp_smoothing_x_weekday"
assert 38 <= r["predicted_demand_qty"] <= 46          # about 6/day x 7
assert r["already_expired_qty"] == 15.0
assert r["spoilage_batches"] and r["spoilage_batches"][0]["batch_number"] == "MILK-A2"
assert r["confidence_score"] is not None and r["confidence_score"] > 80

# not enough history
short = {today - timedelta(days=i): 5.0 for i in range(1, 6)}
assert compute_forecast(product, short, batches, today, 7)["method"] == "insufficient_data"

# FEFO takes the earliest-expiring batch first
bs = [{"batch_id": 1, "batch_number": "LATE", "qty": 10.0, "expiry": today + timedelta(days=10)},
      {"batch_id": 2, "batch_number": "SOON", "qty": 10.0, "expiry": today + timedelta(days=1)}]
sim = simulate_fefo(bs, [4.0, 4.0, 4.0], today)
assert sim["spoilage"] == 2.0 and sim["spoilage_batches"][0]["batch_number"] == "SOON"

# reorder formula
assert recommend_reorder(40, 5, 20, 30) == 35 and recommend_reorder(5, 0, 0, 100) == 0
print("all checks passed")
