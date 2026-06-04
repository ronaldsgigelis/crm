-- Migration adding invoice_profile_id relation to invoices.
-- Kept for history; for a final fresh install, import sql/full_setup.sql instead.

ALTER TABLE invoices
  ADD COLUMN invoice_profile_id INT NULL AFTER job_id,
  ADD CONSTRAINT fk_invoices_profile
    FOREIGN KEY (invoice_profile_id) REFERENCES invoice_profiles(id)
    ON DELETE SET NULL ON UPDATE CASCADE;
