### ตัวอย่างคำตอบ

```sql
SELECT
  c.name,
  c.phone,
  SUM(o.total_price) AS total_spent
FROM customers AS c
JOIN orders AS o
  ON o.customer_id = c.id
GROUP BY c.id, c.name, c.phone
HAVING SUM(o.total_price) > 500
ORDER BY total_spent DESC;
```

#### อธิบายย่อ:

- SUM(o.total_price) รวมยอดซื้อรายลูกค้า
- GROUP BY จัดกลุ่มตามลูกค้า
- HAVING กรองเฉพาะกลุ่มที่มียอดรวม > 500
- ORDER BY ... DESC เรียงจากยอดซื้อมากไปน้อย

### ทางเลือก (ทำรวมก่อนแล้วค่อย JOIN)
```sql
SELECT c.name, c.phone, t.total_spent
FROM customers c
JOIN (
  SELECT customer_id, SUM(total_price) AS total_spent
  FROM orders
  GROUP BY customer_id
) t ON t.customer_id = c.id
WHERE t.total_spent > 500
ORDER BY t.total_spent DESC;
```