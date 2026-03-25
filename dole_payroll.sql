-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2026 at 06:14 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dole_payroll`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deduction_types`
--

DROP TABLE IF EXISTS `deduction_types`;
CREATE TABLE `deduction_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL COMMENT 'e.g. PAGIBIG1, GSIS_MPL, WHT',
  `name` varchar(255) NOT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `category` varchar(30) NOT NULL COMMENT 'mandatory, loan, voluntary, tax, union',
  `is_fixed_amount` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'true = fixed peso, false = percentage or variable',
  `default_amount` decimal(12,2) DEFAULT NULL,
  `display_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Order on payslip per DOLE RO9 standard',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `divisions`
--

DROP TABLE IF EXISTS `divisions`;
CREATE TABLE `divisions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'division' COMMENT 'division, field_office, satellite_office',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `plantilla_item_no` varchar(50) NOT NULL,
  `employee_no` varchar(30) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `civil_status` varchar(20) DEFAULT NULL,
  `position_title` varchar(255) NOT NULL,
  `salary_grade` tinyint(3) UNSIGNED NOT NULL,
  `step` tinyint(3) UNSIGNED NOT NULL,
  `basic_salary` decimal(12,2) NOT NULL,
  `pera` decimal(10,2) NOT NULL DEFAULT 2000.00 COMMENT 'Personnel Economic Relief Allowance',
  `division_id` bigint(20) UNSIGNED NOT NULL,
  `employment_status` varchar(30) NOT NULL DEFAULT 'permanent' COMMENT 'permanent, casual, coterminous',
  `original_appointment_date` date DEFAULT NULL,
  `last_promotion_date` date DEFAULT NULL,
  `hire_date` date NOT NULL,
  `gsis_bp_no` varchar(30) DEFAULT NULL,
  `pagibig_no` varchar(30) DEFAULT NULL,
  `philhealth_no` varchar(30) DEFAULT NULL,
  `tin` varchar(30) DEFAULT NULL,
  `sss_no` varchar(30) DEFAULT NULL,
  `vacation_leave_balance` decimal(6,3) NOT NULL DEFAULT 0.000,
  `sick_leave_balance` decimal(6,3) NOT NULL DEFAULT 0.000,
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'active, on_leave, separated, retired',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_deduction_enrollments`
--

DROP TABLE IF EXISTS `employee_deduction_enrollments`;
CREATE TABLE `employee_deduction_enrollments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `deduction_type_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL COMMENT 'Amount per cut-off',
  `effectivity_date` date NOT NULL,
  `end_date` date DEFAULT NULL COMMENT 'null = ongoing',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_promotion_history`
--

DROP TABLE IF EXISTS `employee_promotion_history`;
CREATE TABLE `employee_promotion_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(30) NOT NULL COMMENT 'promotion, step_increment, salary_adjustment, nosi, nosa',
  `old_salary_grade` tinyint(3) UNSIGNED NOT NULL,
  `old_step` tinyint(3) UNSIGNED NOT NULL,
  `old_basic_salary` decimal(12,2) NOT NULL,
  `new_salary_grade` tinyint(3) UNSIGNED NOT NULL,
  `new_step` tinyint(3) UNSIGNED NOT NULL,
  `new_basic_salary` decimal(12,2) NOT NULL,
  `effectivity_date` date NOT NULL,
  `csb_no` varchar(50) DEFAULT NULL COMMENT 'Civil Service Bulletin No.',
  `remarks` varchar(255) DEFAULT NULL,
  `recorded_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_03_20_141352_create_personal_access_tokens_table', 1),
(5, '2026_03_20_141405_create_permission_tables', 1),
(6, '2026_03_20_200001_create_divisions_table', 1),
(7, '2026_03_20_200002_create_salary_index_tables_table', 1),
(8, '2026_03_20_200003_create_employees_table', 1),
(9, '2026_03_20_200004_create_deduction_types_table', 1),
(10, '2026_03_20_200005_create_employee_deduction_enrollments_table', 1),
(11, '2026_03_20_200006_create_employee_promotion_history_table', 1),
(12, '2026_03_20_200007_create_payroll_batches_table', 1),
(13, '2026_03_20_200008_create_payroll_entries_table', 1),
(14, '2026_03_20_200009_create_payroll_deductions_table', 1),
(15, '2026_03_20_200010_create_payroll_audit_log_table', 1),
(16, '2026_03_20_200011_create_special_payroll_batches_table', 1),
(17, '2026_03_20_200012_create_per_diem_rates_table', 1),
(18, '2026_03_20_200013_create_office_orders_table', 1),
(19, '2026_03_20_200014_create_tev_requests_table', 1),
(20, '2026_03_20_200015_create_tev_itinerary_lines_table', 1),
(21, '2026_03_20_200016_create_tev_certifications_table', 1),
(22, '2026_03_20_200017_create_tev_approval_logs_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1);

-- --------------------------------------------------------

--
-- Table structure for table `office_orders`
--

DROP TABLE IF EXISTS `office_orders`;
CREATE TABLE `office_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `office_order_no` varchar(50) NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `travel_type` varchar(20) NOT NULL DEFAULT 'local' COMMENT 'local, regional, national',
  `travel_date_start` date NOT NULL,
  `travel_date_end` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft' COMMENT 'draft, approved, cancelled',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_audit_log`
--

DROP TABLE IF EXISTS `payroll_audit_log`;
CREATE TABLE `payroll_audit_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payroll_batch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(50) NOT NULL COMMENT 'created, computed, status_changed, entry_overridden, locked, released',
  `old_value` varchar(255) DEFAULT NULL,
  `new_value` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_batches`
--

DROP TABLE IF EXISTS `payroll_batches`;
CREATE TABLE `payroll_batches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `year` smallint(5) UNSIGNED NOT NULL,
  `month` tinyint(3) UNSIGNED NOT NULL,
  `cutoff` tinyint(3) UNSIGNED NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `release_date` date DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `prepared_by` bigint(20) UNSIGNED DEFAULT NULL,
  `prepared_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `released_by` bigint(20) UNSIGNED DEFAULT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_deductions`
--

DROP TABLE IF EXISTS `payroll_deductions`;
CREATE TABLE `payroll_deductions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payroll_entry_id` bigint(20) UNSIGNED NOT NULL,
  `deduction_type_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `is_overridden` tinyint(1) NOT NULL DEFAULT 0,
  `override_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_entries`
--

DROP TABLE IF EXISTS `payroll_entries`;
CREATE TABLE `payroll_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payroll_batch_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `basic_salary` decimal(12,2) NOT NULL,
  `pera` decimal(10,2) NOT NULL DEFAULT 2000.00,
  `salary_grade` tinyint(3) UNSIGNED NOT NULL,
  `step` tinyint(3) UNSIGNED NOT NULL,
  `days_worked` decimal(5,3) NOT NULL DEFAULT 0.000,
  `lwop_days` decimal(5,3) NOT NULL DEFAULT 0.000 COMMENT 'Leave Without Pay in decimal days',
  `tardy_minutes` int(11) NOT NULL DEFAULT 0,
  `undertime_minutes` int(11) NOT NULL DEFAULT 0,
  `gross_pay` decimal(12,2) NOT NULL DEFAULT 0.00,
  `lwop_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tardy_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(12,2) NOT NULL DEFAULT 0.00,
  `withholding_tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_pay` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending, computed, locked',
  `is_manually_overridden` tinyint(1) NOT NULL DEFAULT 0,
  `override_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `per_diem_rates`
--

DROP TABLE IF EXISTS `per_diem_rates`;
CREATE TABLE `per_diem_rates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `travel_type` varchar(20) NOT NULL COMMENT 'local, regional, national',
  `destination_category` varchar(50) DEFAULT NULL COMMENT 'e.g. Metro Manila, Regional Center, Others',
  `year` smallint(5) UNSIGNED NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL COMMENT 'Full day per diem per COA Circular',
  `half_day_rate` decimal(10,2) DEFAULT NULL,
  `coa_circular_ref` varchar(50) DEFAULT NULL COMMENT 'e.g. COA Circular 2021-001',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'payroll_officer', 'web', '2026-03-21 11:45:17', '2026-03-21 11:45:17'),
(2, 'hrmo', 'web', '2026-03-21 11:45:17', '2026-03-21 11:45:17'),
(3, 'accountant', 'web', '2026-03-21 11:45:17', '2026-03-21 11:45:17'),
(4, 'budget_officer', 'web', '2026-03-21 11:45:17', '2026-03-21 11:45:17'),
(5, 'chief_admin_officer', 'web', '2026-03-21 11:45:17', '2026-03-21 11:45:17'),
(6, 'ard', 'web', '2026-03-21 11:45:17', '2026-03-21 11:45:17'),
(7, 'cashier', 'web', '2026-03-21 11:45:17', '2026-03-21 11:45:17');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_index_tables`
--

DROP TABLE IF EXISTS `salary_index_tables`;
CREATE TABLE `salary_index_tables` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `salary_grade` tinyint(3) UNSIGNED NOT NULL,
  `step` tinyint(3) UNSIGNED NOT NULL,
  `year` smallint(5) UNSIGNED NOT NULL,
  `monthly_salary` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('ttMwnQgYd1Jo1U7pOQ8yWGGK5zjYOVsPbWJaQ06v', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTWpvbU00WDZTYzRtN1d4YmZ4VVRyQWRQcWI2eFlsTXh6YWNUMnhsbyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fX0=', 1774093571);

-- --------------------------------------------------------

--
-- Table structure for table `special_payroll_batches`
--

DROP TABLE IF EXISTS `special_payroll_batches`;
CREATE TABLE `special_payroll_batches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(30) NOT NULL COMMENT 'newly_hired, salary_differential, nosi, nosa, step_increment',
  `title` varchar(255) NOT NULL,
  `year` smallint(5) UNSIGNED NOT NULL,
  `month` tinyint(3) UNSIGNED NOT NULL,
  `effectivity_date` date NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `old_basic_salary` decimal(12,2) DEFAULT NULL,
  `new_basic_salary` decimal(12,2) DEFAULT NULL,
  `differential_amount` decimal(12,2) DEFAULT NULL,
  `pro_rated_days` decimal(5,3) DEFAULT NULL COMMENT 'Days worked out of 22-day denominator',
  `gross_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deductions_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tev_approval_logs`
--

DROP TABLE IF EXISTS `tev_approval_logs`;
CREATE TABLE `tev_approval_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tev_request_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `step` varchar(50) NOT NULL COMMENT 'submitted, hr_approved, accountant_certified, rd_approved, cashier_released',
  `action` varchar(20) NOT NULL COMMENT 'approved, rejected, returned',
  `remarks` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tev_certifications`
--

DROP TABLE IF EXISTS `tev_certifications`;
CREATE TABLE `tev_certifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tev_request_id` bigint(20) UNSIGNED NOT NULL,
  `date_returned` date DEFAULT NULL,
  `place_reported_back` varchar(100) DEFAULT NULL,
  `travel_completed` tinyint(1) NOT NULL DEFAULT 0,
  `annex_a_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `annex_a_particulars` text DEFAULT NULL,
  `agency_visited` varchar(255) DEFAULT NULL,
  `appearance_date` date DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `certified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `certified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tev_itinerary_lines`
--

DROP TABLE IF EXISTS `tev_itinerary_lines`;
CREATE TABLE `tev_itinerary_lines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tev_request_id` bigint(20) UNSIGNED NOT NULL,
  `travel_date` date NOT NULL,
  `origin` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `mode_of_transport` varchar(50) DEFAULT NULL COMMENT 'bus, jeepney, boat, plane, vehicle',
  `transportation_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `per_diem_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'From per_diem_rates lookup',
  `is_half_day` tinyint(1) NOT NULL DEFAULT 0,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tev_requests`
--

DROP TABLE IF EXISTS `tev_requests`;
CREATE TABLE `tev_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tev_no` varchar(50) NOT NULL,
  `office_order_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `track` varchar(20) NOT NULL DEFAULT 'reimbursement' COMMENT 'cash_advance, reimbursement',
  `purpose` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `travel_type` varchar(20) NOT NULL DEFAULT 'local',
  `travel_date_start` date NOT NULL,
  `travel_date_end` date NOT NULL,
  `total_days` int(11) NOT NULL DEFAULT 0,
  `total_per_diem` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_transportation` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_other_expenses` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cash_advance_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Amount released if CA track',
  `balance_due` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Grand total minus CA amount',
  `status` varchar(30) NOT NULL DEFAULT 'draft' COMMENT 'draft, submitted, hr_approved, accountant_certified, rd_approved, cashier_released, completed',
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Payroll Admin', 'admin@dole9.gov.ph', '2026-03-21 11:45:18', '$2y$12$axEjpuRksmUqFwf8wCXNFe3glVjInqK7kAV9ATLKivEqLG3ZNBw1i', NULL, '2026-03-21 11:45:18', '2026-03-21 11:45:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `deduction_types`
--
ALTER TABLE `deduction_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `deduction_types_code_unique` (`code`),
  ADD KEY `deduction_types_category_index` (`category`),
  ADD KEY `deduction_types_display_order_index` (`display_order`);

--
-- Indexes for table `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `divisions_code_unique` (`code`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employees_plantilla_item_no_unique` (`plantilla_item_no`),
  ADD UNIQUE KEY `employees_employee_no_unique` (`employee_no`),
  ADD KEY `employees_last_name_first_name_index` (`last_name`,`first_name`),
  ADD KEY `employees_division_id_index` (`division_id`),
  ADD KEY `employees_status_index` (`status`);

--
-- Indexes for table `employee_deduction_enrollments`
--
ALTER TABLE `employee_deduction_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ede_unique` (`employee_id`,`deduction_type_id`,`effectivity_date`),
  ADD KEY `employee_deduction_enrollments_deduction_type_id_foreign` (`deduction_type_id`),
  ADD KEY `employee_deduction_enrollments_employee_id_is_active_index` (`employee_id`,`is_active`);

--
-- Indexes for table `employee_promotion_history`
--
ALTER TABLE `employee_promotion_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_promotion_history_recorded_by_foreign` (`recorded_by`),
  ADD KEY `employee_promotion_history_employee_id_effectivity_date_index` (`employee_id`,`effectivity_date`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `office_orders`
--
ALTER TABLE `office_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `office_orders_office_order_no_unique` (`office_order_no`),
  ADD KEY `office_orders_approved_by_foreign` (`approved_by`),
  ADD KEY `office_orders_employee_id_status_index` (`employee_id`,`status`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payroll_audit_log`
--
ALTER TABLE `payroll_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payroll_audit_log_payroll_batch_id_performed_at_index` (`payroll_batch_id`,`performed_at`),
  ADD KEY `payroll_audit_log_user_id_index` (`user_id`);

--
-- Indexes for table `payroll_batches`
--
ALTER TABLE `payroll_batches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payroll_batch_unique` (`year`,`month`,`cutoff`),
  ADD KEY `payroll_batches_prepared_by_foreign` (`prepared_by`),
  ADD KEY `payroll_batches_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `payroll_batches_approved_by_foreign` (`approved_by`),
  ADD KEY `payroll_batches_released_by_foreign` (`released_by`),
  ADD KEY `payroll_batches_year_month_status_index` (`year`,`month`,`status`);

--
-- Indexes for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pd_unique` (`payroll_entry_id`,`deduction_type_id`),
  ADD KEY `payroll_deductions_deduction_type_id_foreign` (`deduction_type_id`),
  ADD KEY `payroll_deductions_payroll_entry_id_index` (`payroll_entry_id`);

--
-- Indexes for table `payroll_entries`
--
ALTER TABLE `payroll_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pe_unique` (`payroll_batch_id`,`employee_id`),
  ADD KEY `payroll_entries_employee_id_foreign` (`employee_id`),
  ADD KEY `payroll_entries_payroll_batch_id_status_index` (`payroll_batch_id`,`status`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `per_diem_rates`
--
ALTER TABLE `per_diem_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pdr_unique` (`travel_type`,`destination_category`,`year`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `salary_index_tables`
--
ALTER TABLE `salary_index_tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sit_unique` (`salary_grade`,`step`,`year`),
  ADD KEY `sit_lookup` (`salary_grade`,`step`,`year`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `special_payroll_batches`
--
ALTER TABLE `special_payroll_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `special_payroll_batches_approved_by_foreign` (`approved_by`),
  ADD KEY `special_payroll_batches_employee_id_type_year_index` (`employee_id`,`type`,`year`);

--
-- Indexes for table `tev_approval_logs`
--
ALTER TABLE `tev_approval_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tev_approval_logs_user_id_foreign` (`user_id`),
  ADD KEY `tev_approval_logs_tev_request_id_performed_at_index` (`tev_request_id`,`performed_at`);

--
-- Indexes for table `tev_certifications`
--
ALTER TABLE `tev_certifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tev_certifications_tev_request_id_unique` (`tev_request_id`),
  ADD KEY `tev_certifications_certified_by_foreign` (`certified_by`);

--
-- Indexes for table `tev_itinerary_lines`
--
ALTER TABLE `tev_itinerary_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tev_itinerary_lines_tev_request_id_travel_date_index` (`tev_request_id`,`travel_date`);

--
-- Indexes for table `tev_requests`
--
ALTER TABLE `tev_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tev_requests_tev_no_unique` (`tev_no`),
  ADD KEY `tev_requests_submitted_by_foreign` (`submitted_by`),
  ADD KEY `tev_requests_employee_id_status_index` (`employee_id`,`status`),
  ADD KEY `tev_requests_office_order_id_index` (`office_order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `deduction_types`
--
ALTER TABLE `deduction_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `divisions`
--
ALTER TABLE `divisions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_deduction_enrollments`
--
ALTER TABLE `employee_deduction_enrollments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_promotion_history`
--
ALTER TABLE `employee_promotion_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `office_orders`
--
ALTER TABLE `office_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_audit_log`
--
ALTER TABLE `payroll_audit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_batches`
--
ALTER TABLE `payroll_batches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_entries`
--
ALTER TABLE `payroll_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `per_diem_rates`
--
ALTER TABLE `per_diem_rates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `salary_index_tables`
--
ALTER TABLE `salary_index_tables`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `special_payroll_batches`
--
ALTER TABLE `special_payroll_batches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tev_approval_logs`
--
ALTER TABLE `tev_approval_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tev_certifications`
--
ALTER TABLE `tev_certifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tev_itinerary_lines`
--
ALTER TABLE `tev_itinerary_lines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tev_requests`
--
ALTER TABLE `tev_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`);

--
-- Constraints for table `employee_deduction_enrollments`
--
ALTER TABLE `employee_deduction_enrollments`
  ADD CONSTRAINT `employee_deduction_enrollments_deduction_type_id_foreign` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_types` (`id`),
  ADD CONSTRAINT `employee_deduction_enrollments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_promotion_history`
--
ALTER TABLE `employee_promotion_history`
  ADD CONSTRAINT `employee_promotion_history_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_promotion_history_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `office_orders`
--
ALTER TABLE `office_orders`
  ADD CONSTRAINT `office_orders_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `office_orders_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `payroll_audit_log`
--
ALTER TABLE `payroll_audit_log`
  ADD CONSTRAINT `payroll_audit_log_payroll_batch_id_foreign` FOREIGN KEY (`payroll_batch_id`) REFERENCES `payroll_batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_audit_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payroll_batches`
--
ALTER TABLE `payroll_batches`
  ADD CONSTRAINT `payroll_batches_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payroll_batches_prepared_by_foreign` FOREIGN KEY (`prepared_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payroll_batches_released_by_foreign` FOREIGN KEY (`released_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payroll_batches_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  ADD CONSTRAINT `payroll_deductions_deduction_type_id_foreign` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_types` (`id`),
  ADD CONSTRAINT `payroll_deductions_payroll_entry_id_foreign` FOREIGN KEY (`payroll_entry_id`) REFERENCES `payroll_entries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll_entries`
--
ALTER TABLE `payroll_entries`
  ADD CONSTRAINT `payroll_entries_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `payroll_entries_payroll_batch_id_foreign` FOREIGN KEY (`payroll_batch_id`) REFERENCES `payroll_batches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `special_payroll_batches`
--
ALTER TABLE `special_payroll_batches`
  ADD CONSTRAINT `special_payroll_batches_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `special_payroll_batches_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `tev_approval_logs`
--
ALTER TABLE `tev_approval_logs`
  ADD CONSTRAINT `tev_approval_logs_tev_request_id_foreign` FOREIGN KEY (`tev_request_id`) REFERENCES `tev_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tev_approval_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `tev_certifications`
--
ALTER TABLE `tev_certifications`
  ADD CONSTRAINT `tev_certifications_certified_by_foreign` FOREIGN KEY (`certified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tev_certifications_tev_request_id_foreign` FOREIGN KEY (`tev_request_id`) REFERENCES `tev_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tev_itinerary_lines`
--
ALTER TABLE `tev_itinerary_lines`
  ADD CONSTRAINT `tev_itinerary_lines_tev_request_id_foreign` FOREIGN KEY (`tev_request_id`) REFERENCES `tev_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tev_requests`
--
ALTER TABLE `tev_requests`
  ADD CONSTRAINT `tev_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `tev_requests_office_order_id_foreign` FOREIGN KEY (`office_order_id`) REFERENCES `office_orders` (`id`),
  ADD CONSTRAINT `tev_requests_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
