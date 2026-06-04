-- Manual invoice templates table setup.
-- Kept for history; for a final fresh install, import sql/full_setup.sql instead.

CREATE TABLE IF NOT EXISTS invoice_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  template_name VARCHAR(160) NOT NULL,
  invoice_profile_id INT NULL,
  payer_type ENUM('private_person', 'company', 'self_employed', 'association', 'public_institution', 'other') NOT NULL DEFAULT 'private_person',
  payer_name VARCHAR(160) NOT NULL,
  payer_registration_no VARCHAR(80) NULL,
  payer_vat_no VARCHAR(80) NULL,
  payer_address TEXT NULL,
  payer_email VARCHAR(160) NULL,
  payer_phone VARCHAR(80) NULL,
  service_receiver_name VARCHAR(160) NULL,
  service_receiver_address TEXT NULL,
  service_receiver_notes TEXT NULL,
  service_description TEXT NOT NULL,
  quantity DECIMAL(10, 2) NOT NULL DEFAULT 1.00,
  unit VARCHAR(30) NOT NULL DEFAULT 'gab.',
  unit_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  vat_rate DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
  currency CHAR(3) NOT NULL DEFAULT 'EUR',
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_invoice_templates_profile FOREIGN KEY (invoice_profile_id) REFERENCES invoice_profiles(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;
