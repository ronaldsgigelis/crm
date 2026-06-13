-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2026 at 02:27 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crm`
--

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `phone`, `email`, `created_at`) VALUES
(1, 'Alina Demo', '+371 20000001', 'alina.demo@example.test', '2026-06-13 10:25:27'),
(3, 'Ralfs Demo', '+371 20000003', 'ralfs.demo@example.test', '2026-06-13 10:27:00'),
(4, 'Liene Demo', '+371 20000004', 'liene.demo@example.test', '2026-06-13 10:27:43'),
(5, 'Raitis Demo', '+371 20000005', 'raitis.demo@example.test', '2026-06-13 10:28:48'),
(8, 'Viviana Demo', '+371 20000008', 'viviana.demo@example.test', '2026-06-13 11:53:32');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(30) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `invoice_profile_id` int(11) DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `seller_name` varchar(160) NOT NULL,
  `seller_registration_no` varchar(80) NOT NULL,
  `seller_vat_no` varchar(80) DEFAULT NULL,
  `seller_address` text NOT NULL,
  `seller_email` varchar(160) DEFAULT NULL,
  `seller_phone` varchar(80) DEFAULT NULL,
  `seller_bank_name` varchar(160) DEFAULT NULL,
  `seller_iban` varchar(80) DEFAULT NULL,
  `seller_swift` varchar(40) DEFAULT NULL,
  `seller_logo_path` varchar(255) DEFAULT NULL,
  `payer_type` enum('private_person','company','self_employed','association','public_institution','other') NOT NULL,
  `payer_name` varchar(160) NOT NULL,
  `payer_registration_no` varchar(80) DEFAULT NULL,
  `payer_vat_no` varchar(80) DEFAULT NULL,
  `payer_address` text DEFAULT NULL,
  `payer_email` varchar(160) DEFAULT NULL,
  `payer_phone` varchar(80) DEFAULT NULL,
  `service_receiver_name` varchar(160) DEFAULT NULL,
  `service_receiver_address` text DEFAULT NULL,
  `service_receiver_notes` text DEFAULT NULL,
  `service_description` text NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit` varchar(30) NOT NULL DEFAULT 'gab.',
  `unit_price` decimal(10,2) NOT NULL,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL,
  `vat_amount` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'EUR',
  `notes` text DEFAULT NULL,
  `payment_status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `paid_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `job_id`, `invoice_profile_id`, `invoice_date`, `due_date`, `seller_name`, `seller_registration_no`, `seller_vat_no`, `seller_address`, `seller_email`, `seller_phone`, `seller_bank_name`, `seller_iban`, `seller_swift`, `seller_logo_path`, `payer_type`, `payer_name`, `payer_registration_no`, `payer_vat_no`, `payer_address`, `payer_email`, `payer_phone`, `service_receiver_name`, `service_receiver_address`, `service_receiver_notes`, `service_description`, `quantity`, `unit`, `unit_price`, `vat_rate`, `subtotal`, `vat_amount`, `total`, `currency`, `notes`, `payment_status`, `paid_at`, `created_at`) VALUES
(5, 'DEMO130626-01', 3, 1, '2026-06-13', '2026-06-27', 'Demo Services Ltd.', '00000000001', NULL, 'Demo Street 1, Riga, LV-1000', 'billing@example.test', '+371 22000001', 'Demo Bank', 'LV00DEMO0000000000001', 'DEMOLV22', NULL, 'private_person', 'Alina Demo', NULL, NULL, NULL, 'alina.demo@example.test', '+371 20000001', 'Alina Demo', NULL, NULL, 'Piano tuning', 1.00, 'gab.', 70.00, 0.00, 70.00, 0.00, 70.00, 'EUR', 'Demo invoice without VAT.', 'unpaid', NULL, '2026-06-13 11:47:37'),
(6, 'manual-demo-01', NULL, 1, '2026-06-13', '2026-06-27', 'Demo Services Ltd.', '00000000001', NULL, 'Demo Street 1, Riga, LV-1000', 'billing@example.test', '+371 22000001', 'Demo Bank', 'LV00DEMO0000000000001', 'DEMOLV22', NULL, 'private_person', 'Manual Demo Client', NULL, NULL, NULL, NULL, NULL, 'Manual Demo Client', NULL, NULL, 'Manual demo invoice service', 1.00, 'gab.', 99.00, 0.00, 99.00, 0.00, 99.00, 'EUR', 'Demo invoice without VAT.', 'paid', '2026-06-13', '2026-06-13 11:50:12'),
(7, 'DEMO130626-02', 6, 1, '2026-06-13', '2026-06-27', 'Demo Services Ltd.', '00000000001', NULL, 'Demo Street 1, Riga, LV-1000', 'billing@example.test', '+371 22000001', 'Demo Bank', 'LV00DEMO0000000000001', 'DEMOLV22', NULL, 'private_person', 'Viviana Demo', NULL, NULL, NULL, 'viviana.demo@example.test', '+371 20000008', 'Viviana Demo', NULL, NULL, 'Piano consultation', 1.00, 'gab.', 35.00, 0.00, 35.00, 0.00, 35.00, 'EUR', 'Demo invoice without VAT.', 'paid', '2026-06-13', '2026-06-13 11:55:58'),
(8, 'manual-demo-02', NULL, 3, '2026-06-08', '2026-06-10', 'Demo Company Ltd.', '00000000003', 'LV00000000003', 'Demo Avenue 5, Riga, LV-1000', 'manual.billing@example.test', '+371 22000003', 'Demo Bank', 'LV00DEMO0000000000003', 'DEMOLV22', NULL, 'company', 'Demo Buyer Ltd.', '00000000010', 'LV00000000010', 'Buyer Street 10, Riga, LV-1000', 'buyer@example.test', '+371 23000010', 'Demo Receiver', 'Receiver Street 2, Riga, LV-1000', 'Demo delivery entrance', 'Retail goods delivery', 14.00, 'gab.', 88.00, 21.00, 1232.00, 258.72, 1490.72, 'EUR', NULL, 'unpaid', NULL, '2026-06-13 12:07:08');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_profiles`
--

CREATE TABLE `invoice_profiles` (
  `id` int(11) NOT NULL,
  `profile_name` varchar(255) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `seller_name` varchar(255) NOT NULL,
  `seller_registration_no` varchar(100) NOT NULL,
  `seller_vat_no` varchar(100) DEFAULT NULL,
  `seller_address` text NOT NULL,
  `seller_email` varchar(255) DEFAULT NULL,
  `seller_phone` varchar(100) DEFAULT NULL,
  `seller_bank_name` varchar(255) DEFAULT NULL,
  `seller_iban` varchar(100) DEFAULT NULL,
  `seller_swift` varchar(100) DEFAULT NULL,
  `invoice_note` text DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `show_logo` tinyint(1) NOT NULL DEFAULT 1,
  `show_seller_vat_no` tinyint(1) NOT NULL DEFAULT 1,
  `show_seller_email` tinyint(1) NOT NULL DEFAULT 1,
  `show_seller_phone` tinyint(1) NOT NULL DEFAULT 1,
  `show_seller_bank_name` tinyint(1) NOT NULL DEFAULT 1,
  `show_seller_iban` tinyint(1) NOT NULL DEFAULT 1,
  `show_seller_swift` tinyint(1) NOT NULL DEFAULT 1,
  `show_invoice_note` tinyint(1) NOT NULL DEFAULT 1,
  `numbering_type` varchar(50) NOT NULL DEFAULT 'yearly_sequence',
  `numbering_prefix` varchar(50) DEFAULT 'R',
  `numbering_separator` varchar(10) DEFAULT '-',
  `numbering_padding` int(11) NOT NULL DEFAULT 4,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_profiles`
--

INSERT INTO `invoice_profiles` (`id`, `profile_name`, `is_default`, `seller_name`, `seller_registration_no`, `seller_vat_no`, `seller_address`, `seller_email`, `seller_phone`, `seller_bank_name`, `seller_iban`, `seller_swift`, `invoice_note`, `logo_path`, `show_logo`, `show_seller_vat_no`, `show_seller_email`, `show_seller_phone`, `show_seller_bank_name`, `show_seller_iban`, `show_seller_swift`, `show_invoice_note`, `numbering_type`, `numbering_prefix`, `numbering_separator`, `numbering_padding`, `created_at`, `updated_at`) VALUES
(1, 'Demo services without VAT', 1, 'Demo Services Ltd.', '00000000001', NULL, 'Demo Street 1, Riga, LV-1000', 'billing@example.test', '+371 22000001', 'Demo Bank', 'LV00DEMO0000000000001', 'DEMOLV22', 'Invoice issued for demonstration purposes only.', NULL, 1, 0, 1, 1, 1, 1, 1, 1, 'daily_short_year', 'DEMO', '-', 2, '2026-06-13 11:05:20', '2026-06-13 11:46:43'),
(3, 'Manual invoice demo profile', 0, 'Demo Company Ltd.', '00000000003', 'LV00000000003', 'Demo Avenue 5, Riga, LV-1000', 'manual.billing@example.test', '+371 22000003', 'Demo Bank', 'LV00DEMO0000000000003', 'DEMOLV22', NULL, NULL, 1, 1, 1, 1, 1, 1, 1, 1, 'manual', 'R', '-', 4, '2026-06-13 12:00:02', '2026-06-13 12:00:02');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_templates`
--

CREATE TABLE `invoice_templates` (
  `id` int(11) NOT NULL,
  `template_name` varchar(160) NOT NULL,
  `invoice_profile_id` int(11) DEFAULT NULL,
  `payer_type` enum('private_person','company','self_employed','association','public_institution','other') NOT NULL DEFAULT 'private_person',
  `payer_name` varchar(160) NOT NULL,
  `payer_registration_no` varchar(80) DEFAULT NULL,
  `payer_vat_no` varchar(80) DEFAULT NULL,
  `payer_address` text DEFAULT NULL,
  `payer_email` varchar(160) DEFAULT NULL,
  `payer_phone` varchar(80) DEFAULT NULL,
  `service_receiver_name` varchar(160) DEFAULT NULL,
  `service_receiver_address` text DEFAULT NULL,
  `service_receiver_notes` text DEFAULT NULL,
  `service_description` text NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit` varchar(30) NOT NULL DEFAULT 'gab.',
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `currency` char(3) NOT NULL DEFAULT 'EUR',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_templates`
--

INSERT INTO `invoice_templates` (`id`, `template_name`, `invoice_profile_id`, `payer_type`, `payer_name`, `payer_registration_no`, `payer_vat_no`, `payer_address`, `payer_email`, `payer_phone`, `service_receiver_name`, `service_receiver_address`, `service_receiver_notes`, `service_description`, `quantity`, `unit`, `unit_price`, `vat_rate`, `currency`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Demo company invoice template', 3, 'company', 'Demo Buyer Ltd.', '00000000010', 'LV00000000010', 'Buyer Street 10, Riga, LV-1000', 'buyer@example.test', '+371 23000010', 'Demo Receiver', 'Receiver Street 2, Riga, LV-1000', 'Demo delivery entrance', 'Retail goods delivery', 14.00, 'gab.', 13.00, 21.00, 'EUR', NULL, '2026-06-13 12:05:31', '2026-06-13 12:05:31');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `master_id` int(11) DEFAULT NULL,
  `title` varchar(160) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('new','in_progress','done','canceled') NOT NULL DEFAULT 'new',
  `price` decimal(10,2) DEFAULT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `client_id`, `master_id`, `title`, `description`, `status`, `price`, `scheduled_at`, `created_at`) VALUES
(1, 3, NULL, 'Piano lesson', 'Introductory piano lesson in a demo classroom.', 'new', 120.00, '2026-11-25 18:10:00', '2026-06-13 10:32:00'),
(2, 4, 2, 'Office cleaning', 'Demo office cleaning and window washing task.', 'new', 180.00, '2026-06-15 15:00:00', '2026-06-13 10:38:57'),
(3, 1, 3, 'Piano tuning', 'Tune a demo upright piano for a sample client.', 'new', 70.00, '2026-06-13 13:00:00', '2026-06-13 10:46:07'),
(6, 8, 3, 'Piano consultation', 'Provide a technical condition consultation for a demo piano.', 'done', 35.00, '2026-06-11 17:20:00', '2026-06-13 11:55:26');

-- --------------------------------------------------------

--
-- Table structure for table `masters`
--

CREATE TABLE `masters` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `token` char(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `masters`
--

INSERT INTO `masters` (`id`, `name`, `phone`, `email`, `token`, `created_at`) VALUES
(1, 'Ritvars Demo', '+371 21000001', 'ritvars.demo@example.test', '1111111111111111111111111111111111111111111111111111111111111111', '2026-06-13 10:33:09'),
(2, 'Janis Demo', '+371 21000002', 'janis.demo@example.test', '2222222222222222222222222222222222222222222222222222222222222222', '2026-06-13 10:35:59'),
(3, 'Ivans Demo', '+371 21000003', 'ivans.demo@example.test', '3333333333333333333333333333333333333333333333333333333333333333', '2026-06-13 10:44:31'),
(6, 'Evalds Demo', '+371 21000006', 'evalds.demo@example.test', '6666666666666666666666666666666666666666666666666666666666666666', '2026-06-13 11:52:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_invoices_invoice_number` (`invoice_number`),
  ADD UNIQUE KEY `uq_invoices_job_id` (`job_id`),
  ADD KEY `fk_invoices_profile` (`invoice_profile_id`);

--
-- Indexes for table `invoice_profiles`
--
ALTER TABLE `invoice_profiles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_templates`
--
ALTER TABLE `invoice_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_invoice_templates_profile` (`invoice_profile_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jobs_client` (`client_id`),
  ADD KEY `fk_jobs_master` (`master_id`);

--
-- Indexes for table `masters`
--
ALTER TABLE `masters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_masters_token` (`token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `invoice_profiles`
--
ALTER TABLE `invoice_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `invoice_templates`
--
ALTER TABLE `invoice_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `masters`
--
ALTER TABLE `masters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invoices_profile` FOREIGN KEY (`invoice_profile_id`) REFERENCES `invoice_profiles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `invoice_templates`
--
ALTER TABLE `invoice_templates`
  ADD CONSTRAINT `fk_invoice_templates_profile` FOREIGN KEY (`invoice_profile_id`) REFERENCES `invoice_profiles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `fk_jobs_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_jobs_master` FOREIGN KEY (`master_id`) REFERENCES `masters` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
