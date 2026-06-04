-- Migration adding payment status fields to invoices.
-- Kept for history; for a final fresh install, import sql/full_setup.sql instead.

ALTER TABLE invoices
  ADD COLUMN payment_status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid' AFTER notes,
  ADD COLUMN paid_at DATE NULL AFTER payment_status;
