-- Sample data for development. Dates are relative to today so FEFO tests always work.
INSERT INTO roles (role_name) VALUES ('Manager'), ('Warehouse Staff'), ('Production Worker');

INSERT INTO categories (category_name, description, expiry_alert_days) VALUES
  ('Dairy', 'Milk, butter, cream', 5),
  ('Dry Goods', 'Flour, sugar, yeast', 30),
  ('Packaging', 'Boxes and wrappers', 60);

INSERT INTO products (category_id, product_name, sku_code, unit_of_measure, min_stock_level) VALUES
  (1, 'Fresh Milk', 'DAI-001', 'litre', 20),
  (1, 'Butter', 'DAI-002', 'kg', 10),
  (2, 'Wheat Flour', 'DRY-001', 'kg', 50),
  (2, 'Sugar', 'DRY-002', 'kg', 30);

INSERT INTO batches (product_id, batch_number, initial_quantity, current_quantity, manufacture_date, expiry_date) VALUES
  (1, 'MILK-A1', 40, 15, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 3 DAY)),
  (1, 'MILK-A2', 40, 40, DATE_SUB(CURDATE(), INTERVAL 2 DAY),  DATE_ADD(CURDATE(), INTERVAL 3 DAY)),
  (2, 'BUT-B1',  20, 8,  DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 40 DAY)),
  (3, 'FLR-C1', 100, 100, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 120 DAY)),
  (4, 'SUG-D1',  60, 60, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 20 DAY));
-- Expected: MILK-A1 expired | MILK-A2 near expiry (5-day rule) | BUT-B1 low stock (8 < 10) | SUG-D1 near expiry (30-day rule)
