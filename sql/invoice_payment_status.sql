ALTER TABLE invoices
  ADD COLUMN payment_status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid' AFTER notes,
  ADD COLUMN paid_at DATE NULL AFTER payment_status;
