-- Invoice seller profile table setup/migration from development.
-- Kept for history; for a final fresh install, import sql/full_setup.sql instead.

CREATE TABLE IF NOT EXISTS invoice_profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  profile_name VARCHAR(255) NOT NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  seller_name VARCHAR(255) NOT NULL,
  seller_registration_no VARCHAR(100) NOT NULL,
  seller_vat_no VARCHAR(100) NULL,
  seller_address TEXT NOT NULL,
  seller_email VARCHAR(255) NULL,
  seller_phone VARCHAR(100) NULL,
  seller_bank_name VARCHAR(255) NULL,
  seller_iban VARCHAR(100) NULL,
  seller_swift VARCHAR(100) NULL,
  invoice_note TEXT NULL,
  logo_path VARCHAR(255) NULL,
  show_logo TINYINT(1) NOT NULL DEFAULT 1,
  show_seller_vat_no TINYINT(1) NOT NULL DEFAULT 1,
  show_seller_email TINYINT(1) NOT NULL DEFAULT 1,
  show_seller_phone TINYINT(1) NOT NULL DEFAULT 1,
  show_seller_bank_name TINYINT(1) NOT NULL DEFAULT 1,
  show_seller_iban TINYINT(1) NOT NULL DEFAULT 1,
  show_seller_swift TINYINT(1) NOT NULL DEFAULT 1,
  show_invoice_note TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB;

INSERT INTO invoice_profiles (
  profile_name,
  is_default,
  seller_name,
  seller_registration_no,
  seller_vat_no,
  seller_address,
  seller_email,
  seller_phone,
  seller_bank_name,
  seller_iban,
  seller_swift,
  invoice_note,
  logo_path
)
SELECT
  COALESCE(NULLIF(seller_name, ''), 'Noklusētais profils') AS profile_name,
  1 AS is_default,
  seller_name,
  seller_registration_no,
  seller_vat_no,
  seller_address,
  seller_email,
  seller_phone,
  seller_bank_name,
  seller_iban,
  seller_swift,
  invoice_note,
  logo_path
FROM invoice_settings
WHERE id = 1
  AND seller_name <> ''
  AND seller_registration_no <> ''
  AND seller_address <> ''
  AND NOT EXISTS (SELECT 1 FROM invoice_profiles);
