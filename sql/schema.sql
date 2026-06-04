-- Early base schema / historical setup file.
-- For the final coursework/demo fresh install, import sql/full_setup.sql instead.

CREATE DATABASE IF NOT EXISTS crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crm;
CREATE TABLE IF NOT EXISTS clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(40) NULL,
  email VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS jobs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  title VARCHAR(160) NOT NULL,
  description TEXT NULL,
  status ENUM('new', 'in_progress', 'done', 'canceled') NOT NULL DEFAULT 'new',
  price DECIMAL(10, 2) NULL,
  scheduled_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_jobs_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;
-- 1) Meistari
CREATE TABLE IF NOT EXISTS masters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(40) NULL,
  email VARCHAR(120) NULL,
  token CHAR(64) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_masters_token (token)
) ENGINE = InnoDB;
-- 2) Jobs piesaiste meistaram
ALTER TABLE jobs
ADD COLUMN master_id INT NULL
AFTER client_id,
  ADD CONSTRAINT fk_jobs_master FOREIGN KEY (master_id) REFERENCES masters(id) ON DELETE
SET NULL ON UPDATE CASCADE;
CREATE TABLE IF NOT EXISTS invoice_settings (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  seller_name VARCHAR(160) NOT NULL,
  seller_registration_no VARCHAR(80) NOT NULL,
  seller_vat_no VARCHAR(80) NULL,
  seller_address TEXT NOT NULL,
  seller_email VARCHAR(160) NULL,
  seller_phone VARCHAR(80) NULL,
  seller_bank_name VARCHAR(160) NULL,
  seller_iban VARCHAR(80) NULL,
  seller_swift VARCHAR(40) NULL,
  invoice_note TEXT NULL,
  logo_path VARCHAR(255) NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB;
CREATE TABLE IF NOT EXISTS invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_number VARCHAR(30) NOT NULL,
  job_id INT NULL,
  invoice_date DATE NOT NULL,
  due_date DATE NULL,
  seller_name VARCHAR(160) NOT NULL,
  seller_registration_no VARCHAR(80) NOT NULL,
  seller_vat_no VARCHAR(80) NULL,
  seller_address TEXT NOT NULL,
  seller_email VARCHAR(160) NULL,
  seller_phone VARCHAR(80) NULL,
  seller_bank_name VARCHAR(160) NULL,
  seller_iban VARCHAR(80) NULL,
  seller_swift VARCHAR(40) NULL,
  seller_logo_path VARCHAR(255) NULL,
  payer_type ENUM('private_person', 'company', 'self_employed', 'association', 'public_institution', 'other') NOT NULL,
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
  unit_price DECIMAL(10, 2) NOT NULL,
  vat_rate DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
  subtotal DECIMAL(10, 2) NOT NULL,
  vat_amount DECIMAL(10, 2) NOT NULL,
  total DECIMAL(10, 2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'EUR',
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_invoices_invoice_number (invoice_number),
  UNIQUE KEY uq_invoices_job_id (job_id),
  CONSTRAINT fk_invoices_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;
