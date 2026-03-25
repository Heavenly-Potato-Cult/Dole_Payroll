-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: dole_payroll
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deduction_types`
--

DROP TABLE IF EXISTS `deduction_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deduction_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'misc',
  `is_computed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'true = auto-calculated by payroll engine',
  `is_fixed_amount` tinyint(1) NOT NULL DEFAULT '0',
  `default_amount` decimal(12,2) DEFAULT NULL,
  `display_order` smallint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `deduction_types_code_unique` (`code`),
  KEY `deduction_types_category_index` (`category`),
  KEY `deduction_types_display_order_index` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deduction_types`
--

LOCK TABLES `deduction_types` WRITE;
/*!40000 ALTER TABLE `deduction_types` DISABLE KEYS */;
INSERT INTO `deduction_types` VALUES (1,'PAGIBIG_1','PAG-IBIG I',NULL,'pagibig',1,0,NULL,1,1,'Mandatory HDMF contribution. Computed: 2% of basic salary, max ₱100.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(2,'HDMF_MPL','HDMF Multi-Purpose Loan',NULL,'pagibig',0,0,NULL,2,1,'Manual entry — individual loan amortization amount.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(3,'HDMF_CALAMITY','HDMF Calamity Loan',NULL,'pagibig',0,0,NULL,3,1,'Manual entry — individual calamity loan amortization.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(4,'HDMF_HOUSING','HDMF House & Lot',NULL,'pagibig',0,0,NULL,4,1,'Manual entry — housing loan amortization.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(5,'PAGIBIG_2','PAG-IBIG II',NULL,'pagibig',0,0,NULL,5,1,'Voluntary additional PAG-IBIG contribution. Manual entry.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(6,'PHILHEALTH','PhilHealth',NULL,'philhealth',1,0,NULL,6,1,'Computed: 5% of basic salary ÷ 2 (employee share, semi-monthly). Per PhilHealth circular.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(7,'GSIS_LIFE_RET','GSIS Life/Retirement',NULL,'gsis',1,0,NULL,7,1,'Computed: 9% of basic salary (employee share). Mandatory for permanent employees.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(8,'GSIS_CONSO','GSIS Conso Loan',NULL,'gsis',0,0,NULL,8,1,'Manual entry — consolidated loan amortization.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(9,'GSIS_POLICY','GSIS Policy Loan',NULL,'gsis',0,0,NULL,9,1,'Manual entry.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(10,'GSIS_REALESTATE','GSIS Real Estate',NULL,'gsis',0,0,NULL,10,1,'Manual entry — real estate loan amortization.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(11,'GSIS_MPL','GSIS MPL',NULL,'gsis',0,0,NULL,11,1,'Manual entry — GSIS Multi-Purpose Loan.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(12,'GSIS_CPL','GSIS CPL',NULL,'gsis',0,0,NULL,12,1,'Manual entry — Consolidated Policy Loan.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(13,'GSIS_MPL_LITE','GSIS MPL Lite',NULL,'gsis',0,0,NULL,13,1,'Manual entry.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(14,'GSIS_GFAL','GSIS GFAL',NULL,'gsis',0,0,NULL,14,1,'Manual entry — GSIS Financial Assistance Loan.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(15,'GSIS_HELP','GSIS HELP',NULL,'gsis',0,0,NULL,15,1,'Manual entry — GSIS Housing Emergency Loan Program.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(16,'GSIS_EMERG','GSIS Emergency Loan',NULL,'gsis',0,0,NULL,16,1,'Manual entry.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(17,'GSIS_EDUC','GSIS Educ Loan',NULL,'gsis',0,0,NULL,17,1,'Manual entry — GSIS Educational Loan.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(18,'MASS','MASS',NULL,'other_gov',0,0,NULL,18,1,'Mutual Aid Support System. Manual entry — fixed amount per employee.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(19,'SSS','SSS Contribution',NULL,'other_gov',0,0,NULL,19,1,'For employees with prior private sector service. Manual entry.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(20,'PROVIDENT','Provident Fund',NULL,'other_gov',0,0,NULL,20,1,'DOLE Provident Fund contribution. Manual entry.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(21,'WHT','W/Holding Tax',NULL,'other_gov',1,0,NULL,21,1,'Computed: based on BIR tax table (TRAIN Law). Uses annual gross projected from monthly salary.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(22,'LBP_LOAN','LBP Loan',NULL,'loan',0,0,NULL,22,1,'Land Bank of the Philippines salary loan. Manual entry.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(23,'HMO','HMO',NULL,'other_gov',0,0,NULL,23,1,'Health Maintenance Organization premium. Manual entry.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(24,'CARESS_UNION','CARESS IX Union Dues',NULL,'caress',0,0,NULL,24,1,'Manual entry — fixed union dues per member.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(25,'CARESS_MORTUARY','CARESS IX Mortuary',NULL,'caress',0,0,NULL,25,1,'Manual entry — mortuary fund contribution.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(26,'CARESS_LOAN','CARESS IX CAREs Loan',NULL,'caress',0,0,NULL,26,1,'Manual entry — CARESS cooperative loan amortization.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(27,'SMART_EXCESS','Smart Plan Gold Excess',NULL,'misc',0,0,NULL,27,1,'Manual entry — excess charges on Smart Plan Gold.','2026-03-25 11:58:51','2026-03-25 11:58:51'),(28,'REFUND','Refund (Various)',NULL,'misc',0,0,NULL,28,1,'Cash advance refunds, BTR disallowances, etc. Manual entry per payroll period.','2026-03-25 11:58:51','2026-03-25 11:58:51');
/*!40000 ALTER TABLE `deduction_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `divisions`
--

DROP TABLE IF EXISTS `divisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `divisions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `divisions_name_unique` (`name`),
  UNIQUE KEY `divisions_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `divisions`
--

LOCK TABLES `divisions` WRITE;
/*!40000 ALTER TABLE `divisions` DISABLE KEYS */;
INSERT INTO `divisions` VALUES (1,'Office of the Regional Director','ORD','Office of the Regional Director, DOLE Regional Office IX',1,NULL,'2026-03-25 11:58:49','2026-03-25 11:58:49'),(2,'Internal Management Services Division','IMSD','Handles administrative, finance, and human resource functions.',1,NULL,'2026-03-25 11:58:49','2026-03-25 11:58:49'),(3,'Technical Support & Services Division','TSSD','Handles labor standards, employment facilitation, and HRIS.',1,NULL,'2026-03-25 11:58:49','2026-03-25 11:58:49'),(4,'Labor Laws Compliance Division','LLCD','Labor inspectorate and compliance monitoring.',1,NULL,'2026-03-25 11:58:49','2026-03-25 11:58:49');
/*!40000 ALTER TABLE `divisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_deduction_enrollments`
--

DROP TABLE IF EXISTS `employee_deduction_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_deduction_enrollments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `deduction_type_id` bigint unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL COMMENT 'Amount per cut-off',
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL COMMENT 'null = ongoing',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ede_unique` (`employee_id`,`deduction_type_id`,`effective_from`),
  KEY `employee_deduction_enrollments_deduction_type_id_foreign` (`deduction_type_id`),
  KEY `employee_deduction_enrollments_employee_id_is_active_index` (`employee_id`,`is_active`),
  CONSTRAINT `employee_deduction_enrollments_deduction_type_id_foreign` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_types` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `employee_deduction_enrollments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_deduction_enrollments`
--

LOCK TABLES `employee_deduction_enrollments` WRITE;
/*!40000 ALTER TABLE `employee_deduction_enrollments` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_deduction_enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_promotion_history`
--

DROP TABLE IF EXISTS `employee_promotion_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_promotion_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'promotion, step_increment, salary_adjustment, nosi, nosa',
  `old_salary_grade` tinyint unsigned NOT NULL,
  `old_step` tinyint unsigned NOT NULL,
  `old_basic_salary` decimal(12,2) NOT NULL,
  `new_salary_grade` tinyint unsigned NOT NULL,
  `new_step` tinyint unsigned NOT NULL,
  `new_basic_salary` decimal(12,2) NOT NULL,
  `effectivity_date` date NOT NULL,
  `csb_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Civil Service Bulletin No.',
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recorded_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_promotion_history_recorded_by_foreign` (`recorded_by`),
  KEY `employee_promotion_history_employee_id_effectivity_date_index` (`employee_id`,`effectivity_date`),
  CONSTRAINT `employee_promotion_history_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_promotion_history_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_promotion_history`
--

LOCK TABLES `employee_promotion_history` WRITE;
/*!40000 ALTER TABLE `employee_promotion_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_promotion_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plantilla_item_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_no` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suffix` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `civil_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `salary_grade` tinyint unsigned NOT NULL,
  `step` tinyint unsigned NOT NULL,
  `sit_year` smallint unsigned NOT NULL DEFAULT '2022',
  `basic_salary` decimal(12,2) NOT NULL,
  `pera` decimal(10,2) NOT NULL DEFAULT '2000.00' COMMENT 'Personnel Economic Relief Allowance',
  `division_id` bigint unsigned NOT NULL,
  `employment_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'permanent' COMMENT 'permanent, casual, coterminous',
  `original_appointment_date` date DEFAULT NULL,
  `last_promotion_date` date DEFAULT NULL,
  `hire_date` date NOT NULL,
  `gsis_bp_no` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pagibig_no` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `philhealth_no` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tin` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sss_no` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vacation_leave_balance` decimal(6,3) NOT NULL DEFAULT '0.000',
  `sick_leave_balance` decimal(6,3) NOT NULL DEFAULT '0.000',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'active, on_leave, separated, retired',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_plantilla_item_no_unique` (`plantilla_item_no`),
  UNIQUE KEY `employees_employee_no_unique` (`employee_no`),
  KEY `employees_last_name_first_name_index` (`last_name`,`first_name`),
  KEY `employees_division_id_index` (`division_id`),
  KEY `employees_status_index` (`status`),
  CONSTRAINT `employees_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,'DOLE9-001',NULL,'SANTOS','MARIA','REYES',NULL,NULL,NULL,NULL,'Administrative Aide IV',4,1,2022,14993.00,2000.00,2,'permanent',NULL,NULL,'2015-06-01','GSIS-001','PAGIBIG-001','PH-001','111-222-333-000',NULL,15.000,15.000,'active',NULL,'2026-03-25 11:58:51','2026-03-25 11:58:51'),(2,'DOLE9-002',NULL,'DELA CRUZ','JUAN','GARCIA',NULL,NULL,NULL,NULL,'Labor and Employment Officer II',15,3,2022,35858.00,2000.00,2,'permanent',NULL,NULL,'2010-03-15','GSIS-002','PAGIBIG-002','PH-002','111-222-333-001',NULL,15.000,15.000,'active',NULL,'2026-03-25 11:58:51','2026-03-25 11:58:51'),(3,'DOLE9-003',NULL,'MENDOZA','ANA','LUNA',NULL,NULL,NULL,NULL,'Labor and Employment Officer III',18,2,2022,45706.00,2000.00,2,'permanent',NULL,NULL,'2008-01-10','GSIS-003','PAGIBIG-003','PH-003','111-222-333-002',NULL,15.000,15.000,'active',NULL,'2026-03-25 11:58:51','2026-03-25 11:58:51');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_03_20_141352_create_personal_access_tokens_table',1),(5,'2026_03_20_141405_create_permission_tables',1),(6,'2026_03_20_200001_create_divisions_table',1),(7,'2026_03_20_200002_create_salary_index_tables_table',1),(8,'2026_03_20_200003_create_employees_table',1),(9,'2026_03_20_200004_create_deduction_types_table',1),(10,'2026_03_20_200005_create_employee_deduction_enrollments_table',1),(11,'2026_03_20_200006_create_employee_promotion_history_table',1),(12,'2026_03_20_200007_create_payroll_batches_table',1),(13,'2026_03_20_200008_create_payroll_entries_table',1),(14,'2026_03_20_200009_create_payroll_deductions_table',1),(15,'2026_03_20_200010_create_payroll_audit_log_table',1),(16,'2026_03_20_200011_create_special_payroll_batches_table',1),(17,'2026_03_20_200012_create_per_diem_rates_table',1),(18,'2026_03_20_200013_create_office_orders_table',1),(19,'2026_03_20_200014_create_tev_requests_table',1),(20,'2026_03_20_200015_create_tev_itinerary_lines_table',1),(21,'2026_03_20_200016_create_tev_certifications_table',1),(22,'2026_03_20_200017_create_tev_approval_logs_table',1),(23,'2026_03_23_164026_fix_payroll_tables_phase1b',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `office_orders`
--

DROP TABLE IF EXISTS `office_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `office_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `office_order_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `purpose` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `travel_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local' COMMENT 'local, regional, national',
  `travel_date_start` date NOT NULL,
  `travel_date_end` date NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft, approved, cancelled',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `office_orders_office_order_no_unique` (`office_order_no`),
  KEY `office_orders_approved_by_foreign` (`approved_by`),
  KEY `office_orders_employee_id_status_index` (`employee_id`,`status`),
  CONSTRAINT `office_orders_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `office_orders_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `office_orders`
--

LOCK TABLES `office_orders` WRITE;
/*!40000 ALTER TABLE `office_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `office_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_audit_log`
--

DROP TABLE IF EXISTS `payroll_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_audit_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payroll_batch_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'created, computed, status_changed, entry_overridden, locked, released',
  `old_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `performed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `payroll_audit_log_payroll_batch_id_performed_at_index` (`payroll_batch_id`,`performed_at`),
  KEY `payroll_audit_log_user_id_index` (`user_id`),
  CONSTRAINT `payroll_audit_log_payroll_batch_id_foreign` FOREIGN KEY (`payroll_batch_id`) REFERENCES `payroll_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_audit_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_audit_log`
--

LOCK TABLES `payroll_audit_log` WRITE;
/*!40000 ALTER TABLE `payroll_audit_log` DISABLE KEYS */;
INSERT INTO `payroll_audit_log` VALUES (1,1,1,'created',NULL,'draft',NULL,'172.18.0.1','2026-03-25 12:00:30'),(2,1,1,'computed','draft','computed',NULL,'172.18.0.1','2026-03-25 12:00:36');
/*!40000 ALTER TABLE `payroll_audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_batches`
--

DROP TABLE IF EXISTS `payroll_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `period_year` smallint unsigned NOT NULL,
  `period_month` tinyint unsigned NOT NULL,
  `cutoff` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `release_date` date DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` bigint unsigned DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_batch_unique` (`period_year`,`period_month`,`cutoff`),
  KEY `payroll_batches_created_by_foreign` (`created_by`),
  KEY `payroll_batches_approved_by_foreign` (`approved_by`),
  KEY `payroll_batches_period_year_period_month_status_index` (`period_year`,`period_month`,`status`),
  CONSTRAINT `payroll_batches_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_batches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_batches`
--

LOCK TABLES `payroll_batches` WRITE;
/*!40000 ALTER TABLE `payroll_batches` DISABLE KEYS */;
INSERT INTO `payroll_batches` VALUES (1,2026,3,'1st','2026-03-01','2026-03-15',NULL,'computed',1,NULL,NULL,NULL,NULL,'2026-03-25 12:00:30','2026-03-25 12:00:36');
/*!40000 ALTER TABLE `payroll_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_deductions`
--

DROP TABLE IF EXISTS `payroll_deductions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_deductions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payroll_entry_id` bigint unsigned NOT NULL,
  `deduction_type_id` bigint unsigned NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `is_overridden` tinyint(1) NOT NULL DEFAULT '0',
  `override_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pd_unique` (`payroll_entry_id`,`deduction_type_id`),
  KEY `payroll_deductions_deduction_type_id_foreign` (`deduction_type_id`),
  KEY `payroll_deductions_payroll_entry_id_index` (`payroll_entry_id`),
  CONSTRAINT `payroll_deductions_deduction_type_id_foreign` FOREIGN KEY (`deduction_type_id`) REFERENCES `deduction_types` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `payroll_deductions_payroll_entry_id_foreign` FOREIGN KEY (`payroll_entry_id`) REFERENCES `payroll_entries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_deductions`
--

LOCK TABLES `payroll_deductions` WRITE;
/*!40000 ALTER TABLE `payroll_deductions` DISABLE KEYS */;
INSERT INTO `payroll_deductions` VALUES (1,1,6,'PHILHEALTH','PhilHealth',187.42,0,NULL,'2026-03-25 12:00:36','2026-03-25 12:00:36'),(2,2,6,'PHILHEALTH','PhilHealth',448.23,0,NULL,'2026-03-25 12:00:36','2026-03-25 12:00:36'),(3,3,6,'PHILHEALTH','PhilHealth',571.33,0,NULL,'2026-03-25 12:00:36','2026-03-25 12:00:36');
/*!40000 ALTER TABLE `payroll_deductions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_entries`
--

DROP TABLE IF EXISTS `payroll_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payroll_batch_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `basic_salary` decimal(12,2) NOT NULL,
  `pera` decimal(10,2) NOT NULL DEFAULT '2000.00',
  `rata` decimal(10,2) NOT NULL DEFAULT '0.00',
  `gross_income` decimal(12,2) NOT NULL DEFAULT '0.00',
  `lwop_days` decimal(5,3) NOT NULL DEFAULT '0.000',
  `lwop_deduction` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tardiness` decimal(10,2) NOT NULL DEFAULT '0.00',
  `undertime` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `withholding_tax` decimal(10,2) NOT NULL DEFAULT '0.00',
  `net_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'computed',
  `is_manually_overridden` tinyint(1) NOT NULL DEFAULT '0',
  `override_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pe_unique` (`payroll_batch_id`,`employee_id`),
  KEY `payroll_entries_employee_id_foreign` (`employee_id`),
  KEY `payroll_entries_payroll_batch_id_status_index` (`payroll_batch_id`,`status`),
  CONSTRAINT `payroll_entries_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `payroll_entries_payroll_batch_id_foreign` FOREIGN KEY (`payroll_batch_id`) REFERENCES `payroll_batches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_entries`
--

LOCK TABLES `payroll_entries` WRITE;
/*!40000 ALTER TABLE `payroll_entries` DISABLE KEYS */;
INSERT INTO `payroll_entries` VALUES (1,1,1,7496.50,1000.00,0.00,8496.50,0.000,0.00,0.00,0.00,187.42,0.00,8309.08,'computed',0,NULL,'2026-03-25 12:00:36','2026-03-25 12:00:36'),(2,1,2,17929.00,1000.00,0.00,18929.00,0.000,0.00,0.00,0.00,448.23,0.00,18480.77,'computed',0,NULL,'2026-03-25 12:00:36','2026-03-25 12:00:36'),(3,1,3,22853.00,1000.00,0.00,23853.00,0.000,0.00,0.00,0.00,571.33,0.00,23281.67,'computed',0,NULL,'2026-03-25 12:00:36','2026-03-25 12:00:36');
/*!40000 ALTER TABLE `payroll_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `per_diem_rates`
--

DROP TABLE IF EXISTS `per_diem_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `per_diem_rates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `travel_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'local, regional, national',
  `destination_category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g. Metro Manila, Regional Center, Others',
  `year` smallint unsigned NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL COMMENT 'Full day per diem per COA Circular',
  `half_day_rate` decimal(10,2) DEFAULT NULL,
  `coa_circular_ref` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g. COA Circular 2021-001',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pdr_unique` (`travel_type`,`destination_category`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `per_diem_rates`
--

LOCK TABLES `per_diem_rates` WRITE;
/*!40000 ALTER TABLE `per_diem_rates` DISABLE KEYS */;
/*!40000 ALTER TABLE `per_diem_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'payroll_officer','web','2026-03-25 11:58:51','2026-03-25 11:58:51'),(2,'hrmo','web','2026-03-25 11:58:51','2026-03-25 11:58:51'),(3,'accountant','web','2026-03-25 11:58:51','2026-03-25 11:58:51'),(4,'budget_officer','web','2026-03-25 11:58:51','2026-03-25 11:58:51'),(5,'chief_admin_officer','web','2026-03-25 11:58:51','2026-03-25 11:58:51'),(6,'ard','web','2026-03-25 11:58:51','2026-03-25 11:58:51'),(7,'cashier','web','2026-03-25 11:58:51','2026-03-25 11:58:51');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_index_tables`
--

DROP TABLE IF EXISTS `salary_index_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salary_index_tables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `salary_grade` tinyint unsigned NOT NULL,
  `step` tinyint unsigned NOT NULL,
  `year` smallint unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sit_unique` (`salary_grade`,`step`,`year`),
  KEY `sit_lookup` (`salary_grade`,`step`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=492 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_index_tables`
--

LOCK TABLES `salary_index_tables` WRITE;
/*!40000 ALTER TABLE `salary_index_tables` DISABLE KEYS */;
INSERT INTO `salary_index_tables` VALUES (1,1,1,2021,12034.00,NULL,NULL),(2,1,2,2021,12134.00,NULL,NULL),(3,1,3,2021,12236.00,NULL,NULL),(4,1,4,2021,12339.00,NULL,NULL),(5,1,5,2021,12442.00,NULL,NULL),(6,1,6,2021,12545.00,NULL,NULL),(7,1,7,2021,12651.00,NULL,NULL),(8,1,8,2021,12756.00,NULL,NULL),(9,2,1,2021,12790.00,NULL,NULL),(10,2,2,2021,12888.00,NULL,NULL),(11,2,3,2021,12987.00,NULL,NULL),(12,2,4,2021,13087.00,NULL,NULL),(13,2,5,2021,13187.00,NULL,NULL),(14,2,6,2021,13288.00,NULL,NULL),(15,2,7,2021,13390.00,NULL,NULL),(16,2,8,2021,13493.00,NULL,NULL),(17,3,1,2021,13572.00,NULL,NULL),(18,3,2,2021,13677.00,NULL,NULL),(19,3,3,2021,13781.00,NULL,NULL),(20,3,4,2021,13888.00,NULL,NULL),(21,3,5,2021,13994.00,NULL,NULL),(22,3,6,2021,14101.00,NULL,NULL),(23,3,7,2021,14210.00,NULL,NULL),(24,3,8,2021,14319.00,NULL,NULL),(25,4,1,2021,14400.00,NULL,NULL),(26,4,2,2021,14511.00,NULL,NULL),(27,4,3,2021,14622.00,NULL,NULL),(28,4,4,2021,14735.00,NULL,NULL),(29,4,5,2021,14848.00,NULL,NULL),(30,4,6,2021,14961.00,NULL,NULL),(31,4,7,2021,15077.00,NULL,NULL),(32,4,8,2021,15192.00,NULL,NULL),(33,5,1,2021,15275.00,NULL,NULL),(34,5,2,2021,15393.00,NULL,NULL),(35,5,3,2021,15511.00,NULL,NULL),(36,5,4,2021,15630.00,NULL,NULL),(37,5,5,2021,15750.00,NULL,NULL),(38,5,6,2021,15871.00,NULL,NULL),(39,5,7,2021,15993.00,NULL,NULL),(40,5,8,2021,16115.00,NULL,NULL),(41,6,1,2021,16200.00,NULL,NULL),(42,6,2,2021,16325.00,NULL,NULL),(43,6,3,2021,16450.00,NULL,NULL),(44,6,4,2021,16577.00,NULL,NULL),(45,6,5,2021,16704.00,NULL,NULL),(46,6,6,2021,16832.00,NULL,NULL),(47,6,7,2021,16962.00,NULL,NULL),(48,6,8,2021,17092.00,NULL,NULL),(49,7,1,2021,17179.00,NULL,NULL),(50,7,2,2021,17311.00,NULL,NULL),(51,7,3,2021,17444.00,NULL,NULL),(52,7,4,2021,17578.00,NULL,NULL),(53,7,5,2021,17713.00,NULL,NULL),(54,7,6,2021,17849.00,NULL,NULL),(55,7,7,2021,17985.00,NULL,NULL),(56,7,8,2021,18124.00,NULL,NULL),(57,8,1,2021,18251.00,NULL,NULL),(58,8,2,2021,18417.00,NULL,NULL),(59,8,3,2021,18583.00,NULL,NULL),(60,8,4,2021,18751.00,NULL,NULL),(61,8,5,2021,18920.00,NULL,NULL),(62,8,6,2021,19091.00,NULL,NULL),(63,8,7,2021,19264.00,NULL,NULL),(64,8,8,2021,19438.00,NULL,NULL),(65,9,1,2021,19593.00,NULL,NULL),(66,9,2,2021,19757.00,NULL,NULL),(67,9,3,2021,19922.00,NULL,NULL),(68,9,4,2021,20089.00,NULL,NULL),(69,9,5,2021,20257.00,NULL,NULL),(70,9,6,2021,20426.00,NULL,NULL),(71,9,7,2021,20597.00,NULL,NULL),(72,9,8,2021,20769.00,NULL,NULL),(73,10,1,2021,21205.00,NULL,NULL),(74,10,2,2021,21382.00,NULL,NULL),(75,10,3,2021,21561.00,NULL,NULL),(76,10,4,2021,21741.00,NULL,NULL),(77,10,5,2021,21923.00,NULL,NULL),(78,10,6,2021,22106.00,NULL,NULL),(79,10,7,2021,22291.00,NULL,NULL),(80,10,8,2021,22477.00,NULL,NULL),(81,11,1,2021,23877.00,NULL,NULL),(82,11,2,2021,24161.00,NULL,NULL),(83,11,3,2021,24450.00,NULL,NULL),(84,11,4,2021,24742.00,NULL,NULL),(85,11,5,2021,25038.00,NULL,NULL),(86,11,6,2021,25339.00,NULL,NULL),(87,11,7,2021,25643.00,NULL,NULL),(88,11,8,2021,25952.00,NULL,NULL),(89,12,1,2021,26052.00,NULL,NULL),(90,12,2,2021,26336.00,NULL,NULL),(91,12,3,2021,26624.00,NULL,NULL),(92,12,4,2021,26915.00,NULL,NULL),(93,12,5,2021,27210.00,NULL,NULL),(94,12,6,2021,27509.00,NULL,NULL),(95,12,7,2021,27811.00,NULL,NULL),(96,12,8,2021,28117.00,NULL,NULL),(97,13,1,2021,28276.00,NULL,NULL),(98,13,2,2021,28589.00,NULL,NULL),(99,13,3,2021,28905.00,NULL,NULL),(100,13,4,2021,29225.00,NULL,NULL),(101,13,5,2021,29550.00,NULL,NULL),(102,13,6,2021,29878.00,NULL,NULL),(103,13,7,2021,30210.00,NULL,NULL),(104,13,8,2021,30547.00,NULL,NULL),(105,14,1,2021,30799.00,NULL,NULL),(106,14,2,2021,31143.00,NULL,NULL),(107,14,3,2021,31491.00,NULL,NULL),(108,14,4,2021,31844.00,NULL,NULL),(109,14,5,2021,32200.00,NULL,NULL),(110,14,6,2021,32561.00,NULL,NULL),(111,14,7,2021,32927.00,NULL,NULL),(112,14,8,2021,33297.00,NULL,NULL),(113,15,1,2021,33575.00,NULL,NULL),(114,15,2,2021,33953.00,NULL,NULL),(115,15,3,2021,34336.00,NULL,NULL),(116,15,4,2021,34724.00,NULL,NULL),(117,15,5,2021,35116.00,NULL,NULL),(118,15,6,2021,35513.00,NULL,NULL),(119,15,7,2021,35915.00,NULL,NULL),(120,15,8,2021,36323.00,NULL,NULL),(121,16,1,2021,36628.00,NULL,NULL),(122,16,2,2021,37044.00,NULL,NULL),(123,16,3,2021,37465.00,NULL,NULL),(124,16,4,2021,37891.00,NULL,NULL),(125,16,5,2021,38323.00,NULL,NULL),(126,16,6,2021,38760.00,NULL,NULL),(127,16,7,2021,39203.00,NULL,NULL),(128,16,8,2021,39650.00,NULL,NULL),(129,17,1,2021,39986.00,NULL,NULL),(130,18,1,2021,43681.00,NULL,NULL),(131,18,2,2021,44184.00,NULL,NULL),(132,18,3,2021,44694.00,NULL,NULL),(133,18,4,2021,45209.00,NULL,NULL),(134,18,5,2021,45732.00,NULL,NULL),(135,18,6,2021,46261.00,NULL,NULL),(136,18,7,2021,46796.00,NULL,NULL),(137,18,8,2021,47338.00,NULL,NULL),(138,19,1,2021,48313.00,NULL,NULL),(139,19,2,2021,49052.00,NULL,NULL),(140,19,3,2021,49803.00,NULL,NULL),(141,19,4,2021,50566.00,NULL,NULL),(142,19,5,2021,51342.00,NULL,NULL),(143,19,6,2021,52130.00,NULL,NULL),(144,19,7,2021,52931.00,NULL,NULL),(145,19,8,2021,53746.00,NULL,NULL),(146,20,1,2021,54251.00,NULL,NULL),(147,20,2,2021,55085.00,NULL,NULL),(148,20,3,2021,55934.00,NULL,NULL),(149,20,4,2021,56796.00,NULL,NULL),(150,20,5,2021,57673.00,NULL,NULL),(151,20,6,2021,58564.00,NULL,NULL),(152,20,7,2021,59469.00,NULL,NULL),(153,20,8,2021,60389.00,NULL,NULL),(154,21,1,2021,60901.00,NULL,NULL),(155,21,2,2021,61844.00,NULL,NULL),(156,21,3,2021,62803.00,NULL,NULL),(157,21,4,2021,63777.00,NULL,NULL),(158,21,5,2021,64768.00,NULL,NULL),(159,21,6,2021,65774.00,NULL,NULL),(160,21,7,2021,66797.00,NULL,NULL),(161,21,8,2021,67837.00,NULL,NULL),(162,22,1,2021,68415.00,NULL,NULL),(163,22,2,2021,69481.00,NULL,NULL),(164,22,3,2021,70565.00,NULL,NULL),(165,22,4,2021,71666.00,NULL,NULL),(166,22,5,2021,72785.00,NULL,NULL),(167,22,6,2021,73923.00,NULL,NULL),(168,22,7,2021,75079.00,NULL,NULL),(169,22,8,2021,76253.00,NULL,NULL),(170,23,1,2021,76907.00,NULL,NULL),(171,23,2,2021,78111.00,NULL,NULL),(172,23,3,2021,79336.00,NULL,NULL),(173,23,4,2021,80583.00,NULL,NULL),(174,23,5,2021,81899.00,NULL,NULL),(175,23,6,2021,83235.00,NULL,NULL),(176,23,7,2021,84594.00,NULL,NULL),(177,23,8,2021,85975.00,NULL,NULL),(178,24,1,2021,86742.00,NULL,NULL),(179,24,2,2021,88158.00,NULL,NULL),(180,24,3,2021,89597.00,NULL,NULL),(181,24,4,2021,91059.00,NULL,NULL),(182,24,5,2021,92545.00,NULL,NULL),(183,24,6,2021,94057.00,NULL,NULL),(184,24,7,2021,95592.00,NULL,NULL),(185,24,8,2021,97152.00,NULL,NULL),(186,25,1,2021,98886.00,NULL,NULL),(187,25,2,2021,100500.00,NULL,NULL),(188,25,3,2021,102140.00,NULL,NULL),(189,25,4,2021,103808.00,NULL,NULL),(190,25,5,2021,105502.00,NULL,NULL),(191,25,6,2021,107224.00,NULL,NULL),(192,25,7,2021,108974.00,NULL,NULL),(193,25,8,2021,110753.00,NULL,NULL),(194,26,1,2021,111742.00,NULL,NULL),(195,26,2,2021,113565.00,NULL,NULL),(196,26,3,2021,115419.00,NULL,NULL),(197,26,4,2021,117303.00,NULL,NULL),(198,26,5,2021,119217.00,NULL,NULL),(199,26,6,2021,121163.00,NULL,NULL),(200,26,7,2021,123140.00,NULL,NULL),(201,26,8,2021,125150.00,NULL,NULL),(202,27,1,2021,126267.00,NULL,NULL),(203,27,2,2021,128329.00,NULL,NULL),(204,27,3,2021,130423.00,NULL,NULL),(205,27,4,2021,132552.00,NULL,NULL),(206,27,5,2021,134715.00,NULL,NULL),(207,27,6,2021,136914.00,NULL,NULL),(208,27,7,2021,139149.00,NULL,NULL),(209,27,8,2021,141420.00,NULL,NULL),(210,28,1,2021,142683.00,NULL,NULL),(211,28,2,2021,145011.00,NULL,NULL),(212,28,3,2021,147378.00,NULL,NULL),(213,28,4,2021,149784.00,NULL,NULL),(214,28,5,2021,152228.00,NULL,NULL),(215,28,6,2021,154714.00,NULL,NULL),(216,28,7,2021,157239.00,NULL,NULL),(217,28,8,2021,159804.00,NULL,NULL),(218,29,1,2021,161231.00,NULL,NULL),(219,29,2,2021,163863.00,NULL,NULL),(220,29,3,2021,166537.00,NULL,NULL),(221,29,4,2021,169256.00,NULL,NULL),(222,29,5,2021,172018.00,NULL,NULL),(223,29,6,2021,174826.00,NULL,NULL),(224,29,7,2021,177679.00,NULL,NULL),(225,29,8,2021,180579.00,NULL,NULL),(226,30,1,2021,182191.00,NULL,NULL),(227,30,2,2021,185165.00,NULL,NULL),(228,30,3,2021,188187.00,NULL,NULL),(229,30,4,2021,191259.00,NULL,NULL),(230,30,5,2021,194380.00,NULL,NULL),(231,30,6,2021,197553.00,NULL,NULL),(232,30,7,2021,200777.00,NULL,NULL),(233,30,8,2021,204054.00,NULL,NULL),(234,1,1,2022,12517.00,NULL,NULL),(235,1,2,2022,12621.00,NULL,NULL),(236,1,3,2022,12728.00,NULL,NULL),(237,1,4,2022,12834.00,NULL,NULL),(238,1,5,2022,12941.00,NULL,NULL),(239,1,6,2022,13049.00,NULL,NULL),(240,1,7,2022,13159.00,NULL,NULL),(241,1,8,2022,13628.00,NULL,NULL),(242,2,1,2022,13305.00,NULL,NULL),(243,2,2,2022,13406.00,NULL,NULL),(244,2,3,2022,13509.00,NULL,NULL),(245,2,4,2022,13613.00,NULL,NULL),(246,2,5,2022,13718.00,NULL,NULL),(247,2,6,2022,13823.00,NULL,NULL),(248,2,7,2022,13929.00,NULL,NULL),(249,2,8,2022,14035.00,NULL,NULL),(250,3,1,2022,14125.00,NULL,NULL),(251,3,2,2022,14234.00,NULL,NULL),(252,3,3,2022,14343.00,NULL,NULL),(253,3,4,2022,14454.00,NULL,NULL),(254,3,5,2022,14565.00,NULL,NULL),(255,3,6,2022,14676.00,NULL,NULL),(256,3,7,2022,14790.00,NULL,NULL),(257,3,8,2022,14903.00,NULL,NULL),(258,4,1,2022,14993.00,NULL,NULL),(259,4,2,2022,15109.00,NULL,NULL),(260,4,3,2022,15224.00,NULL,NULL),(261,4,4,2022,15341.00,NULL,NULL),(262,4,5,2022,15459.00,NULL,NULL),(263,4,6,2022,15577.00,NULL,NULL),(264,4,7,2022,15698.00,NULL,NULL),(265,4,8,2022,15818.00,NULL,NULL),(266,5,1,2022,15909.00,NULL,NULL),(267,5,2,2022,16032.00,NULL,NULL),(268,5,3,2022,16155.00,NULL,NULL),(269,5,4,2022,16279.00,NULL,NULL),(270,5,5,2022,16404.00,NULL,NULL),(271,5,6,2022,16530.00,NULL,NULL),(272,5,7,2022,16657.00,NULL,NULL),(273,5,8,2022,16784.00,NULL,NULL),(274,6,1,2022,16877.00,NULL,NULL),(275,6,2,2022,17007.00,NULL,NULL),(276,6,3,2022,17137.00,NULL,NULL),(277,6,4,2022,17269.00,NULL,NULL),(278,6,5,2022,17402.00,NULL,NULL),(279,6,6,2022,17535.00,NULL,NULL),(280,6,7,2022,17670.00,NULL,NULL),(281,6,8,2022,17806.00,NULL,NULL),(282,7,1,2022,17899.00,NULL,NULL),(283,7,2,2022,18037.00,NULL,NULL),(284,7,3,2022,18176.00,NULL,NULL),(285,7,4,2022,18315.00,NULL,NULL),(286,7,5,2022,18455.00,NULL,NULL),(287,7,6,2022,18598.00,NULL,NULL),(288,7,7,2022,18740.00,NULL,NULL),(289,7,8,2022,18884.00,NULL,NULL),(290,8,1,2022,18998.00,NULL,NULL),(291,8,2,2022,19170.00,NULL,NULL),(292,8,3,2022,19343.00,NULL,NULL),(293,8,4,2022,19518.00,NULL,NULL),(294,8,5,2022,19694.00,NULL,NULL),(295,8,6,2022,19872.00,NULL,NULL),(296,8,7,2022,20052.00,NULL,NULL),(297,8,8,2022,20233.00,NULL,NULL),(298,9,1,2022,20402.00,NULL,NULL),(299,9,2,2022,20572.00,NULL,NULL),(300,9,3,2022,20745.00,NULL,NULL),(301,9,4,2022,20918.00,NULL,NULL),(302,9,5,2022,21093.00,NULL,NULL),(303,9,6,2022,21269.00,NULL,NULL),(304,9,7,2022,21447.00,NULL,NULL),(305,9,8,2022,21626.00,NULL,NULL),(306,10,1,2022,22190.00,NULL,NULL),(307,10,2,2022,22376.00,NULL,NULL),(308,10,3,2022,22563.00,NULL,NULL),(309,10,4,2022,22752.00,NULL,NULL),(310,10,5,2022,22942.00,NULL,NULL),(311,10,6,2022,23134.00,NULL,NULL),(312,10,7,2022,23327.00,NULL,NULL),(313,10,8,2022,23522.00,NULL,NULL),(314,11,1,2022,25439.00,NULL,NULL),(315,11,2,2022,25723.00,NULL,NULL),(316,11,3,2022,26012.00,NULL,NULL),(317,11,4,2022,26304.00,NULL,NULL),(318,11,5,2022,26600.00,NULL,NULL),(319,11,6,2022,26901.00,NULL,NULL),(320,11,7,2022,27205.00,NULL,NULL),(321,11,8,2022,27514.00,NULL,NULL),(322,12,1,2022,27608.00,NULL,NULL),(323,12,2,2022,27892.00,NULL,NULL),(324,12,3,2022,28180.00,NULL,NULL),(325,12,4,2022,28471.00,NULL,NULL),(326,12,5,2022,28766.00,NULL,NULL),(327,12,6,2022,29065.00,NULL,NULL),(328,12,7,2022,29367.00,NULL,NULL),(329,12,8,2022,29673.00,NULL,NULL),(330,13,1,2022,29798.00,NULL,NULL),(331,13,2,2022,30111.00,NULL,NULL),(332,13,3,2022,30427.00,NULL,NULL),(333,13,4,2022,30747.00,NULL,NULL),(334,13,5,2022,31072.00,NULL,NULL),(335,13,6,2022,31400.00,NULL,NULL),(336,13,7,2022,31732.00,NULL,NULL),(337,13,8,2022,32069.00,NULL,NULL),(338,14,1,2022,32321.00,NULL,NULL),(339,14,2,2022,32665.00,NULL,NULL),(340,14,3,2022,33013.00,NULL,NULL),(341,14,4,2022,33366.00,NULL,NULL),(342,14,5,2022,33722.00,NULL,NULL),(343,14,6,2022,34083.00,NULL,NULL),(344,14,7,2022,34449.00,NULL,NULL),(345,14,8,2022,34819.00,NULL,NULL),(346,15,1,2022,35097.00,NULL,NULL),(347,15,2,2022,35475.00,NULL,NULL),(348,15,3,2022,35858.00,NULL,NULL),(349,15,4,2022,36246.00,NULL,NULL),(350,15,5,2022,36638.00,NULL,NULL),(351,15,6,2022,37035.00,NULL,NULL),(352,15,7,2022,37437.00,NULL,NULL),(353,15,8,2022,37845.00,NULL,NULL),(354,16,1,2022,38150.00,NULL,NULL),(355,16,2,2022,38566.00,NULL,NULL),(356,16,3,2022,38987.00,NULL,NULL),(357,16,4,2022,39413.00,NULL,NULL),(358,16,5,2022,39845.00,NULL,NULL),(359,16,6,2022,40282.00,NULL,NULL),(360,16,7,2022,40725.00,NULL,NULL),(361,16,8,2022,41172.00,NULL,NULL),(362,17,1,2022,41508.00,NULL,NULL),(363,17,2,2022,41966.00,NULL,NULL),(364,17,3,2022,42429.00,NULL,NULL),(365,17,4,2022,42898.00,NULL,NULL),(366,17,5,2022,43373.00,NULL,NULL),(367,17,6,2022,43854.00,NULL,NULL),(368,17,7,2022,44340.00,NULL,NULL),(369,17,8,2022,44833.00,NULL,NULL),(370,18,1,2022,45203.00,NULL,NULL),(371,18,2,2022,45706.00,NULL,NULL),(372,18,3,2022,46216.00,NULL,NULL),(373,18,4,2022,46731.00,NULL,NULL),(374,18,5,2022,47254.00,NULL,NULL),(375,18,6,2022,47783.00,NULL,NULL),(376,18,7,2022,48318.00,NULL,NULL),(377,18,8,2022,48860.00,NULL,NULL),(378,19,1,2022,49835.00,NULL,NULL),(379,19,2,2022,50574.00,NULL,NULL),(380,19,3,2022,51325.00,NULL,NULL),(381,19,4,2022,52088.00,NULL,NULL),(382,19,5,2022,52864.00,NULL,NULL),(383,19,6,2022,53652.00,NULL,NULL),(384,19,7,2022,54454.00,NULL,NULL),(385,19,8,2022,55268.00,NULL,NULL),(386,20,1,2022,55799.00,NULL,NULL),(387,20,2,2022,56633.00,NULL,NULL),(388,20,3,2022,57482.00,NULL,NULL),(389,20,4,2022,58344.00,NULL,NULL),(390,20,5,2022,59221.00,NULL,NULL),(391,20,6,2022,60112.00,NULL,NULL),(392,20,7,2022,61017.00,NULL,NULL),(393,20,8,2022,61937.00,NULL,NULL),(394,21,1,2022,62449.00,NULL,NULL),(395,21,2,2022,63392.00,NULL,NULL),(396,21,3,2022,64351.00,NULL,NULL),(397,21,4,2022,65325.00,NULL,NULL),(398,21,5,2022,66316.00,NULL,NULL),(399,21,6,2022,67322.00,NULL,NULL),(400,21,7,2022,68345.00,NULL,NULL),(401,21,8,2022,69385.00,NULL,NULL),(402,22,1,2022,69963.00,NULL,NULL),(403,22,2,2022,71029.00,NULL,NULL),(404,22,3,2022,72113.00,NULL,NULL),(405,22,4,2022,73214.00,NULL,NULL),(406,22,5,2022,74333.00,NULL,NULL),(407,22,6,2022,75471.00,NULL,NULL),(408,22,7,2022,76627.00,NULL,NULL),(409,22,8,2022,77801.00,NULL,NULL),(410,23,1,2022,78455.00,NULL,NULL),(411,23,2,2022,79659.00,NULL,NULL),(412,23,3,2022,80884.00,NULL,NULL),(413,23,4,2022,82133.00,NULL,NULL),(414,23,5,2022,83474.00,NULL,NULL),(415,23,6,2022,84836.00,NULL,NULL),(416,23,7,2022,86220.00,NULL,NULL),(417,23,8,2022,87628.00,NULL,NULL),(418,24,1,2022,88410.00,NULL,NULL),(419,24,2,2022,89853.00,NULL,NULL),(420,24,3,2022,91320.00,NULL,NULL),(421,24,4,2022,92810.00,NULL,NULL),(422,24,5,2022,94325.00,NULL,NULL),(423,24,6,2022,95865.00,NULL,NULL),(424,24,7,2022,97430.00,NULL,NULL),(425,24,8,2022,99020.00,NULL,NULL),(426,25,1,2022,100788.00,NULL,NULL),(427,25,2,2022,102433.00,NULL,NULL),(428,25,3,2022,104105.00,NULL,NULL),(429,25,4,2022,105804.00,NULL,NULL),(430,25,5,2022,107531.00,NULL,NULL),(431,25,6,2022,109286.00,NULL,NULL),(432,25,7,2022,111070.00,NULL,NULL),(433,25,8,2022,112883.00,NULL,NULL),(434,26,1,2022,113891.00,NULL,NULL),(435,26,2,2022,115749.00,NULL,NULL),(436,26,3,2022,117639.00,NULL,NULL),(437,26,4,2022,119558.00,NULL,NULL),(438,26,5,2022,121510.00,NULL,NULL),(439,26,6,2022,123493.00,NULL,NULL),(440,26,7,2022,125508.00,NULL,NULL),(441,26,8,2022,127557.00,NULL,NULL),(442,27,1,2022,128696.00,NULL,NULL),(443,27,2,2022,130797.00,NULL,NULL),(444,27,3,2022,132931.00,NULL,NULL),(445,27,4,2022,135101.00,NULL,NULL),(446,27,5,2022,137306.00,NULL,NULL),(447,27,6,2022,139547.00,NULL,NULL),(448,27,7,2022,141825.00,NULL,NULL),(449,27,8,2022,144140.00,NULL,NULL),(450,28,1,2022,145427.00,NULL,NULL),(451,28,2,2022,147800.00,NULL,NULL),(452,28,3,2022,150213.00,NULL,NULL),(453,28,4,2022,152664.00,NULL,NULL),(454,28,5,2022,155155.00,NULL,NULL),(455,28,6,2022,157689.00,NULL,NULL),(456,28,7,2022,160262.00,NULL,NULL),(457,28,8,2022,162877.00,NULL,NULL),(458,29,1,2022,164332.00,NULL,NULL),(459,29,2,2022,167015.00,NULL,NULL),(460,29,3,2022,169740.00,NULL,NULL),(461,29,4,2022,172511.00,NULL,NULL),(462,29,5,2022,175326.00,NULL,NULL),(463,29,6,2022,178188.00,NULL,NULL),(464,29,7,2022,181096.00,NULL,NULL),(465,29,8,2022,184052.00,NULL,NULL),(466,30,1,2022,185695.00,NULL,NULL),(467,30,2,2022,188726.00,NULL,NULL),(468,30,3,2022,191806.00,NULL,NULL),(469,30,4,2022,194937.00,NULL,NULL),(470,30,5,2022,198118.00,NULL,NULL),(471,30,6,2022,201352.00,NULL,NULL),(472,30,7,2022,204638.00,NULL,NULL),(473,30,8,2022,207978.00,NULL,NULL),(474,31,1,2022,273278.00,NULL,NULL),(475,31,2,2022,278615.00,NULL,NULL),(476,31,3,2022,284057.00,NULL,NULL),(477,31,4,2022,289605.00,NULL,NULL),(478,31,5,2022,295262.00,NULL,NULL),(479,31,6,2022,301028.00,NULL,NULL),(480,31,7,2022,306908.00,NULL,NULL),(481,31,8,2022,312902.00,NULL,NULL),(482,32,1,2022,325807.00,NULL,NULL),(483,32,2,2022,332378.00,NULL,NULL),(484,32,3,2022,339080.00,NULL,NULL),(485,32,4,2022,345918.00,NULL,NULL),(486,32,5,2022,352894.00,NULL,NULL),(487,32,6,2022,360011.00,NULL,NULL),(488,32,7,2022,367272.00,NULL,NULL),(489,32,8,2022,374678.00,NULL,NULL),(490,33,1,2022,411382.00,NULL,NULL),(491,33,2,2022,423723.00,NULL,NULL);
/*!40000 ALTER TABLE `salary_index_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('cONd0kYUDltwqtDJ3zt0gK6Th7Sw4u7Kj3ose8PM',1,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiY2NlMlg1N2Q4Wkh1TGxpV1J4MVpWSzdsdW1hMDVWUzR5bVBqMU01cSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvZW1wbG95ZWVzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9',1774416029);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `special_payroll_batches`
--

DROP TABLE IF EXISTS `special_payroll_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `special_payroll_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'newly_hired, salary_differential, nosi, nosa, step_increment',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` smallint unsigned NOT NULL,
  `month` tinyint unsigned NOT NULL,
  `effectivity_date` date NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `old_basic_salary` decimal(12,2) DEFAULT NULL,
  `new_basic_salary` decimal(12,2) DEFAULT NULL,
  `differential_amount` decimal(12,2) DEFAULT NULL,
  `pro_rated_days` decimal(5,3) DEFAULT NULL COMMENT 'Days worked out of 22-day denominator',
  `gross_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `deductions_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `special_payroll_batches_approved_by_foreign` (`approved_by`),
  KEY `special_payroll_batches_employee_id_type_year_index` (`employee_id`,`type`,`year`),
  CONSTRAINT `special_payroll_batches_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `special_payroll_batches_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `special_payroll_batches`
--

LOCK TABLES `special_payroll_batches` WRITE;
/*!40000 ALTER TABLE `special_payroll_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `special_payroll_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tev_approval_logs`
--

DROP TABLE IF EXISTS `tev_approval_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tev_approval_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tev_request_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `step` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'submitted, hr_approved, accountant_certified, rd_approved, cashier_released',
  `action` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'approved, rejected, returned',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `performed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tev_approval_logs_user_id_foreign` (`user_id`),
  KEY `tev_approval_logs_tev_request_id_performed_at_index` (`tev_request_id`,`performed_at`),
  CONSTRAINT `tev_approval_logs_tev_request_id_foreign` FOREIGN KEY (`tev_request_id`) REFERENCES `tev_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tev_approval_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tev_approval_logs`
--

LOCK TABLES `tev_approval_logs` WRITE;
/*!40000 ALTER TABLE `tev_approval_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `tev_approval_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tev_certifications`
--

DROP TABLE IF EXISTS `tev_certifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tev_certifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tev_request_id` bigint unsigned NOT NULL,
  `date_returned` date DEFAULT NULL,
  `place_reported_back` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `travel_completed` tinyint(1) NOT NULL DEFAULT '0',
  `annex_a_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `annex_a_particulars` text COLLATE utf8mb4_unicode_ci,
  `agency_visited` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `appearance_date` date DEFAULT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certified_by` bigint unsigned DEFAULT NULL,
  `certified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tev_certifications_tev_request_id_unique` (`tev_request_id`),
  KEY `tev_certifications_certified_by_foreign` (`certified_by`),
  CONSTRAINT `tev_certifications_certified_by_foreign` FOREIGN KEY (`certified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tev_certifications_tev_request_id_foreign` FOREIGN KEY (`tev_request_id`) REFERENCES `tev_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tev_certifications`
--

LOCK TABLES `tev_certifications` WRITE;
/*!40000 ALTER TABLE `tev_certifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `tev_certifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tev_itinerary_lines`
--

DROP TABLE IF EXISTS `tev_itinerary_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tev_itinerary_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tev_request_id` bigint unsigned NOT NULL,
  `travel_date` date NOT NULL,
  `origin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mode_of_transport` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'bus, jeepney, boat, plane, vehicle',
  `transportation_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `per_diem_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'From per_diem_rates lookup',
  `is_half_day` tinyint(1) NOT NULL DEFAULT '0',
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tev_itinerary_lines_tev_request_id_travel_date_index` (`tev_request_id`,`travel_date`),
  CONSTRAINT `tev_itinerary_lines_tev_request_id_foreign` FOREIGN KEY (`tev_request_id`) REFERENCES `tev_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tev_itinerary_lines`
--

LOCK TABLES `tev_itinerary_lines` WRITE;
/*!40000 ALTER TABLE `tev_itinerary_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `tev_itinerary_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tev_requests`
--

DROP TABLE IF EXISTS `tev_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tev_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tev_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `office_order_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `track` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reimbursement' COMMENT 'cash_advance, reimbursement',
  `purpose` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `travel_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  `travel_date_start` date NOT NULL,
  `travel_date_end` date NOT NULL,
  `total_days` int NOT NULL DEFAULT '0',
  `total_per_diem` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_transportation` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_other_expenses` decimal(10,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `cash_advance_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Amount released if CA track',
  `balance_due` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Grand total minus CA amount',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'draft, submitted, hr_approved, accountant_certified, rd_approved, cashier_released, completed',
  `submitted_by` bigint unsigned DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tev_requests_tev_no_unique` (`tev_no`),
  KEY `tev_requests_submitted_by_foreign` (`submitted_by`),
  KEY `tev_requests_employee_id_status_index` (`employee_id`,`status`),
  KEY `tev_requests_office_order_id_index` (`office_order_id`),
  CONSTRAINT `tev_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `tev_requests_office_order_id_foreign` FOREIGN KEY (`office_order_id`) REFERENCES `office_orders` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `tev_requests_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tev_requests`
--

LOCK TABLES `tev_requests` WRITE;
/*!40000 ALTER TABLE `tev_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `tev_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Payroll Admin','admin@dole9.gov.ph','2026-03-25 11:58:52','$2y$12$xarrQDR2D6YvoXbw4XycYeaOotOYU5PwjGumauJQ6FS.9LsxALtlC',NULL,'2026-03-25 11:58:52','2026-03-25 11:58:52');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-25  5:27:31
