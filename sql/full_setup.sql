-- CRM coursework final fresh-install database setup.
-- Import this file into an empty MySQL/MariaDB server/database for demo or review.
-- This file creates the current schema required by the application.
-- Historical phase/migration SQL files are kept separately for development history.
-- No real client data, passwords, or secrets are included.

CREATE DATABASE IF NOT EXISTS crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crm;

CREATE TABLE IF NOT EXISTS clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(40) NULL,
  email VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS masters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(40) NULL,
  email VARCHAR(120) NULL,
  token CHAR(64) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_masters_token (token)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS jobs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  master_id INT NULL,
  title VARCHAR(160) NOT NULL,
  description TEXT NULL,
  status ENUM('new', 'in_progress', 'done', 'canceled') NOT NULL DEFAULT 'new',
  price DECIMAL(10, 2) NULL,
  scheduled_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_jobs_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_jobs_master FOREIGN KEY (master_id) REFERENCES masters(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

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
  numbering_type VARCHAR(50) NOT NULL DEFAULT 'yearly_sequence',
  numbering_prefix VARCHAR(50) NULL DEFAULT 'R',
  numbering_separator VARCHAR(10) NULL DEFAULT '-',
  numbering_padding INT NOT NULL DEFAULT 4,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_number VARCHAR(30) NOT NULL,
  job_id INT NULL,
  invoice_profile_id INT NULL,
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
  payment_status ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  paid_at DATE NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_invoices_invoice_number (invoice_number),
  UNIQUE KEY uq_invoices_job_id (job_id),
  CONSTRAINT fk_invoices_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_invoices_profile FOREIGN KEY (invoice_profile_id) REFERENCES invoice_profiles(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

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
