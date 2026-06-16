-- CRM coursework standalone demo database setup.
-- Import this file into a local MySQL/MariaDB server for demo or review.
-- This file creates/selects the crm database, creates the current schema,
-- and inserts safe fictional demo data.
-- No real client data, passwords, API keys, private keys, or production credentials are included.

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

START TRANSACTION;

INSERT INTO clients (id, name, phone, email, created_at) VALUES
(1, 'Alina Demo', '+371 20000001', 'alina.demo@example.test', '2026-06-13 10:25:27'),
(3, 'Ralfs Demo', '+371 20000003', 'ralfs.demo@example.test', '2026-06-13 10:27:00'),
(4, 'Liene Demo', '+371 20000004', 'liene.demo@example.test', '2026-06-13 10:27:43'),
(5, 'Raitis Demo', '+371 20000005', 'raitis.demo@example.test', '2026-06-13 10:28:48'),
(8, 'Viviana Demo', '+371 20000008', 'viviana.demo@example.test', '2026-06-13 11:53:32');

INSERT INTO masters (id, name, phone, email, token, created_at) VALUES
(1, 'Ritvars Demo', '+371 21000001', 'ritvars.demo@example.test', '1111111111111111111111111111111111111111111111111111111111111111', '2026-06-13 10:33:09'),
(2, 'Janis Demo', '+371 21000002', 'janis.demo@example.test', '2222222222222222222222222222222222222222222222222222222222222222', '2026-06-13 10:35:59'),
(3, 'Ivans Demo', '+371 21000003', 'ivans.demo@example.test', '3333333333333333333333333333333333333333333333333333333333333333', '2026-06-13 10:44:31'),
(6, 'Evalds Demo', '+371 21000006', 'evalds.demo@example.test', '6666666666666666666666666666666666666666666666666666666666666666', '2026-06-13 11:52:56');

INSERT INTO invoice_profiles (
  id, profile_name, is_default, seller_name, seller_registration_no, seller_vat_no,
  seller_address, seller_email, seller_phone, seller_bank_name, seller_iban, seller_swift,
  invoice_note, logo_path, show_logo, show_seller_vat_no, show_seller_email,
  show_seller_phone, show_seller_bank_name, show_seller_iban, show_seller_swift,
  show_invoice_note, numbering_type, numbering_prefix, numbering_separator,
  numbering_padding, created_at, updated_at
) VALUES
(
  1, 'Demo services without VAT', 1, 'Demo Services Ltd.', '00000000001', NULL,
  'Demo Street 1, Riga, LV-1000', 'billing@example.test', '+371 22000001',
  'Demo Bank', 'LV00DEMO0000000000001', 'DEMOLV22',
  'Invoice issued for demonstration purposes only.', NULL, 1, 0, 1,
  1, 1, 1, 1, 1, 'daily_short_year', 'DEMO', '-', 2,
  '2026-06-13 11:05:20', '2026-06-13 11:46:43'
),
(
  3, 'Manual invoice demo profile', 0, 'Demo Company Ltd.', '00000000003', 'LV00000000003',
  'Demo Avenue 5, Riga, LV-1000', 'manual.billing@example.test', '+371 22000003',
  'Demo Bank', 'LV00DEMO0000000000003', 'DEMOLV22',
  NULL, NULL, 1, 1, 1,
  1, 1, 1, 1, 1, 'manual', 'R', '-', 4,
  '2026-06-13 12:00:02', '2026-06-13 12:00:02'
);

INSERT INTO jobs (id, client_id, master_id, title, description, status, price, scheduled_at, created_at) VALUES
(1, 3, NULL, 'Piano lesson', 'Introductory piano lesson in a demo classroom.', 'new', 120.00, '2026-11-25 18:10:00', '2026-06-13 10:32:00'),
(2, 4, 2, 'Office cleaning', 'Demo office cleaning and window washing task.', 'new', 180.00, '2026-06-15 15:00:00', '2026-06-13 10:38:57'),
(3, 1, 3, 'Piano tuning', 'Tune a demo upright piano for a sample client.', 'new', 70.00, '2026-06-13 13:00:00', '2026-06-13 10:46:07'),
(6, 8, 3, 'Piano consultation', 'Provide a technical condition consultation for a demo piano.', 'done', 35.00, '2026-06-11 17:20:00', '2026-06-13 11:55:26');

INSERT INTO invoice_templates (
  id, template_name, invoice_profile_id, payer_type, payer_name, payer_registration_no,
  payer_vat_no, payer_address, payer_email, payer_phone, service_receiver_name,
  service_receiver_address, service_receiver_notes, service_description, quantity,
  unit, unit_price, vat_rate, currency, notes, created_at, updated_at
) VALUES
(
  1, 'Demo company invoice template', 3, 'company', 'Demo Buyer Ltd.', '00000000010',
  'LV00000000010', 'Buyer Street 10, Riga, LV-1000', 'buyer@example.test',
  '+371 23000010', 'Demo Receiver', 'Receiver Street 2, Riga, LV-1000',
  'Demo delivery entrance', 'Retail goods delivery', 14.00,
  'gab.', 13.00, 21.00, 'EUR', NULL, '2026-06-13 12:05:31', '2026-06-13 12:05:31'
);

INSERT INTO invoices (
  id, invoice_number, job_id, invoice_profile_id, invoice_date, due_date,
  seller_name, seller_registration_no, seller_vat_no, seller_address, seller_email,
  seller_phone, seller_bank_name, seller_iban, seller_swift, seller_logo_path,
  payer_type, payer_name, payer_registration_no, payer_vat_no, payer_address,
  payer_email, payer_phone, service_receiver_name, service_receiver_address,
  service_receiver_notes, service_description, quantity, unit, unit_price,
  vat_rate, subtotal, vat_amount, total, currency, notes, payment_status,
  paid_at, created_at
) VALUES
(
  5, 'DEMO130626-01', 3, 1, '2026-06-13', '2026-06-27',
  'Demo Services Ltd.', '00000000001', NULL, 'Demo Street 1, Riga, LV-1000',
  'billing@example.test', '+371 22000001', 'Demo Bank', 'LV00DEMO0000000000001',
  'DEMOLV22', NULL, 'private_person', 'Alina Demo', NULL, NULL, NULL,
  'alina.demo@example.test', '+371 20000001', 'Alina Demo', NULL,
  NULL, 'Piano tuning', 1.00, 'gab.', 70.00,
  0.00, 70.00, 0.00, 70.00, 'EUR', 'Demo invoice without VAT.', 'unpaid',
  NULL, '2026-06-13 11:47:37'
),
(
  6, 'manual-demo-01', NULL, 1, '2026-06-13', '2026-06-27',
  'Demo Services Ltd.', '00000000001', NULL, 'Demo Street 1, Riga, LV-1000',
  'billing@example.test', '+371 22000001', 'Demo Bank', 'LV00DEMO0000000000001',
  'DEMOLV22', NULL, 'private_person', 'Manual Demo Client', NULL, NULL, NULL,
  NULL, NULL, 'Manual Demo Client', NULL,
  NULL, 'Manual demo invoice service', 1.00, 'gab.', 99.00,
  0.00, 99.00, 0.00, 99.00, 'EUR', 'Demo invoice without VAT.', 'paid',
  '2026-06-13', '2026-06-13 11:50:12'
),
(
  7, 'DEMO130626-02', 6, 1, '2026-06-13', '2026-06-27',
  'Demo Services Ltd.', '00000000001', NULL, 'Demo Street 1, Riga, LV-1000',
  'billing@example.test', '+371 22000001', 'Demo Bank', 'LV00DEMO0000000000001',
  'DEMOLV22', NULL, 'private_person', 'Viviana Demo', NULL, NULL, NULL,
  'viviana.demo@example.test', '+371 20000008', 'Viviana Demo', NULL,
  NULL, 'Piano consultation', 1.00, 'gab.', 35.00,
  0.00, 35.00, 0.00, 35.00, 'EUR', 'Demo invoice without VAT.', 'paid',
  '2026-06-13', '2026-06-13 11:55:58'
),
(
  8, 'manual-demo-02', NULL, 3, '2026-06-08', '2026-06-10',
  'Demo Company Ltd.', '00000000003', 'LV00000000003',
  'Demo Avenue 5, Riga, LV-1000', 'manual.billing@example.test',
  '+371 22000003', 'Demo Bank', 'LV00DEMO0000000000003',
  'DEMOLV22', NULL, 'company', 'Demo Buyer Ltd.', '00000000010',
  'LV00000000010', 'Buyer Street 10, Riga, LV-1000',
  'buyer@example.test', '+371 23000010', 'Demo Receiver',
  'Receiver Street 2, Riga, LV-1000', 'Demo delivery entrance',
  'Retail goods delivery', 14.00, 'gab.', 88.00,
  21.00, 1232.00, 258.72, 1490.72, 'EUR', NULL, 'unpaid',
  NULL, '2026-06-13 12:07:08'
);

COMMIT;
