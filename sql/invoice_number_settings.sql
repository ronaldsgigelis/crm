-- Migration adding invoice numbering settings to invoice profiles.
-- Kept for history; for a final fresh install, import sql/full_setup.sql instead.

ALTER TABLE invoice_profiles
  ADD COLUMN numbering_type VARCHAR(50) NOT NULL DEFAULT 'yearly_sequence',
  ADD COLUMN numbering_prefix VARCHAR(50) NULL DEFAULT 'R',
  ADD COLUMN numbering_separator VARCHAR(10) NULL DEFAULT '-',
  ADD COLUMN numbering_padding INT NOT NULL DEFAULT 4;
