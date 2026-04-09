-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: dole_payroll
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `deduction_types`
--

LOCK TABLES `deduction_types` WRITE;
/*!40000 ALTER TABLE `deduction_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `deduction_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `divisions`
--

LOCK TABLES `divisions` WRITE;
/*!40000 ALTER TABLE `divisions` DISABLE KEYS */;
INSERT INTO `divisions` VALUES (1,'Office of the Regional Director','ORD','Office of the Regional Director, DOLE Regional Office IX',1,NULL,'2026-04-09 05:41:38','2026-04-09 05:41:38'),(2,'Internal Management Services Division','IMSD','Handles administrative, finance, and human resource functions.',1,NULL,'2026-04-09 05:41:38','2026-04-09 05:41:38'),(3,'Technical Support & Services Division','TSSD','Handles labor standards, employment facilitation, and HRIS.',1,NULL,'2026-04-09 05:41:38','2026-04-09 05:41:38'),(4,'Labor Laws Compliance Division','LLCD','Labor inspectorate and compliance monitoring.',1,NULL,'2026-04-09 05:41:38','2026-04-09 05:41:38');
/*!40000 ALTER TABLE `divisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `employee_deduction_enrollments`
--

LOCK TABLES `employee_deduction_enrollments` WRITE;
/*!40000 ALTER TABLE `employee_deduction_enrollments` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_deduction_enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `employee_promotion_history`
--

LOCK TABLES `employee_promotion_history` WRITE;
/*!40000 ALTER TABLE `employee_promotion_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_promotion_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_03_20_141352_create_personal_access_tokens_table',1),(5,'2026_03_20_141405_create_permission_tables',1),(6,'2026_03_20_200001_create_divisions_table',1),(7,'2026_03_20_200002_create_salary_index_tables_table',1),(8,'2026_03_20_200003_create_employees_table',1),(9,'2026_03_20_200004_create_deduction_types_table',1),(10,'2026_03_20_200005_create_employee_deduction_enrollments_table',1),(11,'2026_03_20_200006_create_employee_promotion_history_table',1),(12,'2026_03_20_200007_create_payroll_batches_table',1),(13,'2026_03_20_200008_create_payroll_entries_table',1),(14,'2026_03_20_200009_create_payroll_deductions_table',1),(15,'2026_03_20_200010_create_payroll_audit_log_table',1),(16,'2026_03_20_200011_create_special_payroll_batches_table',1),(17,'2026_03_20_200012_create_per_diem_rates_table',1),(18,'2026_03_20_200013_create_office_orders_table',1),(19,'2026_03_20_200014_create_tev_requests_table',1),(20,'2026_03_20_200015_create_tev_itinerary_lines_table',1),(21,'2026_03_20_200016_create_tev_certifications_table',1),(22,'2026_03_20_200017_create_tev_approval_logs_table',1),(23,'2026_03_23_164026_fix_payroll_tables_phase1b',1),(24,'2026_03_26_141914_add_prepared_at_to_payroll_batches_table',1),(25,'2026_03_28_140140_make_payroll_batch_id_nullable_in_audit_log',1),(26,'2026_03_28_140519_make_action_text_in_payroll_audit_log',1),(27,'2026_04_01_062127_add_times_to_tev_itinerary_lines_table',1),(28,'2026_04_05_000001_add_workflow_columns_to_payroll_batches',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `office_orders`
--

LOCK TABLES `office_orders` WRITE;
/*!40000 ALTER TABLE `office_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `office_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payroll_audit_log`
--

LOCK TABLES `payroll_audit_log` WRITE;
/*!40000 ALTER TABLE `payroll_audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `payroll_audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payroll_batches`
--

LOCK TABLES `payroll_batches` WRITE;
/*!40000 ALTER TABLE `payroll_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `payroll_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payroll_deductions`
--

LOCK TABLES `payroll_deductions` WRITE;
/*!40000 ALTER TABLE `payroll_deductions` DISABLE KEYS */;
/*!40000 ALTER TABLE `payroll_deductions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payroll_entries`
--

LOCK TABLES `payroll_entries` WRITE;
/*!40000 ALTER TABLE `payroll_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `payroll_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `per_diem_rates`
--

LOCK TABLES `per_diem_rates` WRITE;
/*!40000 ALTER TABLE `per_diem_rates` DISABLE KEYS */;
/*!40000 ALTER TABLE `per_diem_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `salary_index_tables`
--

LOCK TABLES `salary_index_tables` WRITE;
/*!40000 ALTER TABLE `salary_index_tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `salary_index_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('t4MFRqmBmzLToXQK5m3gCXumgvY2vZAOrXqoqWt8',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiak44NXZWR3V5bnJyVHhUYmhTR3daVlVnVktnQ3J0bHd1N2FqZGhlTyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1775713335);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `special_payroll_batches`
--

LOCK TABLES `special_payroll_batches` WRITE;
/*!40000 ALTER TABLE `special_payroll_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `special_payroll_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tev_approval_logs`
--

LOCK TABLES `tev_approval_logs` WRITE;
/*!40000 ALTER TABLE `tev_approval_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `tev_approval_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tev_certifications`
--

LOCK TABLES `tev_certifications` WRITE;
/*!40000 ALTER TABLE `tev_certifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `tev_certifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tev_itinerary_lines`
--

LOCK TABLES `tev_itinerary_lines` WRITE;
/*!40000 ALTER TABLE `tev_itinerary_lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `tev_itinerary_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tev_requests`
--

LOCK TABLES `tev_requests` WRITE;
/*!40000 ALTER TABLE `tev_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `tev_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'dole_payroll'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-09 13:49:39
