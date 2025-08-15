-- OPTIONAL: ล้างตารางก่อน (เอาคอมเมนต์ออกถ้าต้องการ)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE order_items;
TRUNCATE TABLE orders;
TRUNCATE TABLE products;
TRUNCATE TABLE customers;
SET FOREIGN_KEY_CHECKS = 1;

START TRANSACTION;

-- Customers
INSERT INTO customers (id, name, phone, email, created_at, updated_at) VALUES
(1, 'Anan P.',   '081-111-1111', 'anan@example.com',   '2025-08-10 09:00:00', '2025-08-10 09:00:00'),
(2, 'Benjamas T.','081-222-2222', 'benjamas@example.com','2025-08-10 09:05:00', '2025-08-10 09:05:00'),
(3, 'Chai S.',    '081-333-3333', 'chai@example.com',    '2025-08-10 09:10:00', '2025-08-10 09:10:00'),
(4, 'Duangchan',  '081-444-4444', 'duangchan@example.com','2025-08-10 09:15:00','2025-08-10 09:15:00'),
(5, 'Ekkasit K.', '081-555-5555', 'ekkasit@example.com', '2025-08-10 09:20:00', '2025-08-10 09:20:00'),
(6, 'Fah S.',     '081-666-6666', 'fah@example.com',     '2025-08-10 09:25:00', '2025-08-10 09:25:00'),
(7, 'Gun N.',     '081-777-7777', 'gun@example.com',     '2025-08-10 09:30:00', '2025-08-10 09:30:00'),
(8, 'Hana W.',    '081-888-8888', 'hana@example.com',    '2025-08-10 09:35:00', '2025-08-10 09:35:00');

-- Products
INSERT INTO products (id, name, price, sku, created_at, updated_at) VALUES
(1, 'Coffee Beans 500g', 250.00, 'CFB-500', '2025-08-10 09:00:00', '2025-08-10 09:00:00'),
(2, 'Green Tea 200g',    200.00, 'GRT-200', '2025-08-10 09:00:00', '2025-08-10 09:00:00'),
(3, 'Chocolate Bar',       35.00, 'CHB-001', '2025-08-10 09:00:00', '2025-08-10 09:00:00'),
(4, 'Cookies Pack',        80.00, 'CKP-001', '2025-08-10 09:00:00', '2025-08-10 09:00:00'),
(5, 'Honey 500g',         300.00, 'HNY-500', '2025-08-10 09:00:00', '2025-08-10 09:00:00'),
(6, 'Milk 1L',             55.00, 'MLK-1L',  '2025-08-10 09:00:00', '2025-08-10 09:00:00'),
(7, 'Bread Loaf',          65.00, 'BRD-001', '2025-08-10 09:00:00', '2025-08-10 09:00:00'),
(8, 'Orange Juice 1L',     95.00, 'OJ-1L',   '2025-08-10 09:00:00', '2025-08-10 09:00:00');

-- Orders (total_amount ตรงกับผลรวมของรายการใน order_items ด้านล่าง)
INSERT INTO orders (id, customer_id, order_date, status, total_amount, created_at, updated_at) VALUES
(1, 1, '2025-08-10 10:15:00', 'paid',    745.00, '2025-08-10 10:15:00', '2025-08-10 10:15:00'),
(2, 2, '2025-08-11 14:30:00', 'pending', 340.00, '2025-08-11 14:30:00', '2025-08-11 14:30:00'),
(3, 3, '2025-08-12 11:05:00', 'shipped', 490.00, '2025-08-12 11:05:00', '2025-08-12 11:05:00'),
(4, 1, '2025-08-12 16:40:00', 'canceled',130.00, '2025-08-12 16:40:00', '2025-08-12 16:40:00'),
(5, 5, '2025-08-13 09:20:00', 'paid',    950.00, '2025-08-13 09:20:00', '2025-08-13 09:20:00'),
(6, 6, '2025-08-14 13:50:00', 'paid',    415.00, '2025-08-14 13:50:00', '2025-08-14 13:50:00');

-- Order Items
-- Order #1 total 745 = 250*2 + 80*1 + 55*3
INSERT INTO order_items (id, order_id, product_id, price, quantity, subtotal) VALUES
(1, 1, 1, 250.00, 2, 500.00),
(2, 1, 4,  80.00, 1,  80.00),
(3, 1, 6,  55.00, 3, 165.00);

-- Order #2 total 340 = 200*1 + 35*4
INSERT INTO order_items (id, order_id, product_id, price, quantity, subtotal) VALUES
(4, 2, 2, 200.00, 1, 200.00),
(5, 2, 3,  35.00, 4, 140.00);

-- Order #3 total 490 = 300*1 + 95*2
INSERT INTO order_items (id, order_id, product_id, price, quantity, subtotal) VALUES
(6, 3, 5, 300.00, 1, 300.00),
(7, 3, 8,  95.00, 2, 190.00);

-- Order #4 total 130 = 65*2
INSERT INTO order_items (id, order_id, product_id, price, quantity, subtotal) VALUES
(8, 4, 7, 65.00, 2, 130.00);

-- Order #5 total 950 = 250*1 + 200*2 + 300*1
INSERT INTO order_items (id, order_id, product_id, price, quantity, subtotal) VALUES
(9,  5, 1, 250.00, 1, 250.00),
(10, 5, 2, 200.00, 2, 400.00),
(11, 5, 5, 300.00, 1, 300.00);

-- Order #6 total 415 = 80*3 + 55*2 + 65*1
INSERT INTO order_items (id, order_id, product_id, price, quantity, subtotal) VALUES
(12, 6, 4, 80.00, 3, 240.00),
(13, 6, 6, 55.00, 2, 110.00),
(14, 6, 7, 65.00, 1,  65.00);

COMMIT;

-- หมายเหตุ:
-- 1) หากเปิด unique (order_id, product_id) ใน order_items อยู่แล้ว ชุดข้อมูลนี้ไม่ซ้ำ
-- 2) หากไม่ต้องการกำหนด id เอง ให้ลบคอลัมน์ id ออกจาก INSERT ทั้งหมด ระบบจะ AUTO_INCREMENT ให้
