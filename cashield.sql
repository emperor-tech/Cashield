-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 03, 2025 at 04:36 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cashield`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(255) NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `subject_type`, `subject_id`, `details`, `created_at`, `updated_at`) VALUES
(1, 1, 'update', 'report', 11, NULL, '2025-06-03 01:07:40', '2025-06-03 01:07:40');

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `badge_user`
--

CREATE TABLE `badge_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `badge_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `campus_zones`
--

CREATE TABLE `campus_zones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `boundaries` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`boundaries`)),
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checkpoint_scans`
--

CREATE TABLE `checkpoint_scans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `shift_id` bigint(20) UNSIGNED NOT NULL,
  `checkpoint_id` bigint(20) UNSIGNED NOT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emergency_responses`
--

CREATE TABLE `emergency_responses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `responder_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('en_route','on_scene','resolved','withdrawn') NOT NULL DEFAULT 'en_route',
  `eta_minutes` int(11) NOT NULL,
  `action_taken` text NOT NULL,
  `location_lat` double DEFAULT NULL,
  `location_lng` double DEFAULT NULL,
  `resources_needed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`resources_needed`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

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
-- Table structure for table `incident_categories`
--

CREATE TABLE `incident_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `default_severity` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `default_priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `expected_response_time` int(11) NOT NULL DEFAULT 60,
  `icon` varchar(255) DEFAULT NULL,
  `color` varchar(255) NOT NULL DEFAULT '#3b82f6',
  `requires_evidence` tinyint(1) NOT NULL DEFAULT 0,
  `requires_witness` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `incident_categories`
--

INSERT INTO `incident_categories` (`id`, `name`, `slug`, `description`, `default_severity`, `default_priority`, `expected_response_time`, `icon`, `color`, `requires_evidence`, `requires_witness`, `active`, `parent_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Theft', 'theft', 'Incidents involving theft of personal or university property', 'medium', 'medium', 60, 'fa-solid fa-shopping-bag', '#f59e0b', 0, 0, 1, NULL, '2025-06-02 08:18:21', '2025-06-02 08:18:21', NULL),
(2, 'Assault', 'assault', 'Physical or verbal assault incidents', 'high', 'high', 60, 'fa-solid fa-hand-fist', '#ef4444', 0, 0, 1, NULL, '2025-06-02 08:18:21', '2025-06-02 08:18:21', NULL),
(3, 'Suspicious Activity', 'suspicious-activity', 'Unusual or suspicious behavior that requires attention', 'low', 'medium', 60, 'fa-solid fa-eye', '#a78bfa', 0, 0, 1, NULL, '2025-06-02 08:18:21', '2025-06-02 08:18:21', NULL),
(4, 'Vandalism', 'vandalism', 'Damage to campus property or facilities', 'medium', 'medium', 60, 'fa-solid fa-hammer', '#f97316', 0, 0, 1, NULL, '2025-06-02 08:18:21', '2025-06-02 08:18:21', NULL),
(5, 'Medical Emergency', 'medical-emergency', 'Health-related emergencies requiring immediate attention', 'high', 'critical', 60, 'fa-solid fa-kit-medical', '#dc2626', 0, 0, 1, NULL, '2025-06-02 08:18:21', '2025-06-02 08:18:21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

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
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `building` varchar(255) DEFAULT NULL,
  `floor` varchar(255) DEFAULT NULL,
  `room` varchar(255) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

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
(4, '2024_03_21_000000_create_security_tables', 1),
(5, '2025_05_05_000002_create_report_categories_table', 1),
(6, '2025_05_06_045642_add_role_to_users_table', 1),
(7, '2025_05_06_051726_create_reports_table', 1),
(8, '2025_05_06_180000_create_notifications_table', 1),
(9, '2025_05_10_000002_create_report_comments_table', 1),
(10, '2025_05_10_000003_create_audit_logs_table', 1),
(11, '2025_05_10_230624_create_push_subscriptions_table', 1),
(12, '2025_05_20_000000_create_chat_messages_table', 1),
(13, '2025_05_20_010000_create_subscriptions_table', 1),
(14, '2025_05_20_020000_create_badges_table', 1),
(15, '2025_05_20_030000_add_avatar_and_prefs_to_users_table', 1),
(16, '2025_05_21_000000_create_locations_table', 1),
(17, '2025_05_21_000001_create_emergency_responses_tables', 1),
(18, '2025_05_21_000002_create_security_resources_table', 1),
(19, '2025_05_21_000003_create_notification_preferences_table', 1),
(20, '2025_05_21_000004_add_safety_points_to_users_table', 1),
(21, '2025_05_21_000005_create_ranks_table', 1),
(22, '2025_05_28_040659_add_type_to_subscriptions_table', 1),
(23, '2025_06_01_000002_create_incident_categories_table', 1),
(24, '2025_06_01_000005_create_response_protocols_table', 1),
(25, '2025_06_02_083136_add_deleted_at_to_users_table', 1),
(26, '2025_06_02_083442_add_is_active_and_status_to_users_table', 1),
(27, '2025_06_02_085635_add_category_id_to_reports_table', 1),
(28, '2025_06_02_092902_add_deleted_at_to_report_comments_table', 2),
(29, '2025_06_01_000001_add_coordinates_to_reports_table', 3),
(30, '2025_06_02_140000_add_incident_date_to_reports_table', 4),
(31, '2025_06_02_145000_add_status_history_to_reports_table', 5),
(32, '2025_06_02_145500_add_is_panic_to_reports_table', 6),
(33, '2025_06_02_150500_add_priority_level_to_reports_table', 7),
(34, '2025_06_02_151500_add_default_campus_to_reports_table', 8),
(35, '2024_03_14_add_is_public_to_reports', 9),
(36, '2025_06_02_160211_rename_comment_to_content_in_report_comments', 10),
(37, '2025_06_03_014843_add_tracking_code_and_contact_info_to_reports_table', 11),
(38, '2025_06_03_020248_add_evidence_column_to_reports_table', 12);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_preferences`
--

CREATE TABLE `notification_preferences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `incident_type` varchar(255) NOT NULL DEFAULT 'all',
  `severity_level` varchar(255) NOT NULL DEFAULT 'all',
  `area_radius` double DEFAULT NULL,
  `notification_methods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '["web","email"]' CHECK (json_valid(`notification_methods`)),
  `quiet_hours_start` time DEFAULT NULL,
  `quiet_hours_end` time DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `push_subscriptions`
--

CREATE TABLE `push_subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscribable_type` varchar(255) NOT NULL,
  `subscribable_id` bigint(20) UNSIGNED NOT NULL,
  `endpoint` varchar(500) NOT NULL,
  `public_key` varchar(255) DEFAULT NULL,
  `auth_token` varchar(255) DEFAULT NULL,
  `content_encoding` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ranks`
--

CREATE TABLE `ranks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `required_points` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ranks`
--

INSERT INTO `ranks` (`id`, `name`, `icon`, `required_points`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Rookie Guardian', '🌱', 0, 'Just starting your journey as a campus guardian', NULL, NULL),
(2, 'Bronze Guardian', '🥉', 100, 'Showing promise in campus safety', NULL, NULL),
(3, 'Silver Guardian', '🥈', 500, 'A reliable member of the campus safety community', NULL, NULL),
(4, 'Gold Guardian', '🥇', 1000, 'An experienced and trusted safety contributor', NULL, NULL),
(5, 'Platinum Guardian', '💫', 2500, 'A distinguished member of the campus safety network', NULL, NULL),
(6, 'Diamond Guardian', '💎', 5000, 'An elite campus safety expert', NULL, NULL),
(7, 'Legendary Guardian', '👑', 10000, 'A legendary figure in campus safety', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `campus` varchar(255) NOT NULL DEFAULT 'Main Campus',
  `location` varchar(255) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `tracking_code` varchar(255) DEFAULT NULL COMMENT 'Tracking code for anonymous reports',
  `contact_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Contact information for anonymous reporters' CHECK (json_valid(`contact_info`)),
  `evidence` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Evidence files for the report' CHECK (json_valid(`evidence`)),
  `description` text NOT NULL,
  `incident_date` timestamp NULL DEFAULT NULL,
  `severity` enum('low','medium','high') NOT NULL DEFAULT 'low',
  `priority_level` varchar(255) NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','resolved') NOT NULL DEFAULT 'open',
  `is_panic` tinyint(1) NOT NULL DEFAULT 0,
  `status_history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`status_history`)),
  `anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `media_path` varchar(255) DEFAULT NULL,
  `assigned_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `zone_id` bigint(20) UNSIGNED DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `response_time` int(11) DEFAULT NULL,
  `resolution_time` int(11) DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `category_id`, `user_id`, `campus`, `location`, `latitude`, `longitude`, `tracking_code`, `contact_info`, `evidence`, `description`, `incident_date`, `severity`, `priority_level`, `status`, `is_panic`, `status_history`, `anonymous`, `is_public`, `media_path`, `assigned_user_id`, `assigned_team_id`, `zone_id`, `resolved_at`, `response_time`, `resolution_time`, `details`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Main Campus', 'Library', NULL, NULL, NULL, NULL, NULL, 'My laptop was stolen while I was in the bathroom', NULL, 'medium', 'medium', 'open', 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-02 08:22:12', '2025-06-02 08:22:12', NULL),
(2, NULL, 3, 'Main Campus', 'User\'s last known location', NULL, NULL, NULL, NULL, NULL, 'PANIC BUTTON ACTIVATED - Immediate assistance required', '2025-06-02 14:18:28', 'high', 'high', 'open', 1, '[{\"status\":\"open\",\"user_id\":3,\"timestamp\":\"2025-06-02T15:18:28+00:00\",\"notes\":\"Initial report created\"}]', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-02 14:18:28', '2025-06-02 14:18:28', NULL),
(3, NULL, 3, 'Main Campus', 'User\'s last known location', 9.06362880, 7.44488960, NULL, NULL, NULL, 'PANIC BUTTON ACTIVATED - Immediate assistance required', '2025-06-02 14:23:47', 'high', 'high', 'open', 1, '[{\"status\":\"open\",\"user_id\":3,\"timestamp\":\"2025-06-02T15:23:47+00:00\",\"notes\":\"Initial report created\"}]', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-02 14:23:47', '2025-06-02 14:23:47', NULL),
(4, NULL, 3, 'Main Campus', 'User\'s last known location', NULL, NULL, NULL, NULL, NULL, 'PANIC BUTTON ACTIVATED - Immediate assistance required', '2025-06-02 14:24:18', 'high', 'high', 'open', 1, '[{\"status\":\"open\",\"user_id\":3,\"timestamp\":\"2025-06-02T15:24:18+00:00\",\"notes\":\"Initial report created\"}]', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-02 14:24:18', '2025-06-02 14:24:18', NULL),
(5, NULL, 3, 'Main Campus', 'User\'s last known location', NULL, NULL, NULL, NULL, NULL, 'PANIC BUTTON ACTIVATED - Immediate assistance required', '2025-06-02 14:28:17', 'high', 'high', 'open', 1, '[{\"status\":\"open\",\"user_id\":3,\"timestamp\":\"2025-06-02T15:28:17+00:00\",\"notes\":\"Initial report created\"}]', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-02 14:28:17', '2025-06-02 14:28:17', NULL),
(6, NULL, 3, 'Main Campus', 'User\'s last known location', NULL, NULL, NULL, NULL, NULL, 'PANIC BUTTON ACTIVATED - Immediate assistance required', '2025-06-02 14:32:34', 'high', 'high', 'open', 1, '[{\"status\":\"open\",\"user_id\":3,\"timestamp\":\"2025-06-02T15:32:34+00:00\",\"notes\":\"Initial report created\"}]', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-02 14:32:34', '2025-06-02 14:32:34', NULL),
(7, NULL, 3, 'Main Campus', 'User\'s last known location', 9.06362880, 7.44488960, NULL, NULL, NULL, 'PANIC BUTTON ACTIVATED - Immediate assistance required', '2025-06-02 14:39:53', 'high', 'high', 'open', 1, '[{\"status\":\"open\",\"user_id\":3,\"timestamp\":\"2025-06-02T15:39:53+00:00\",\"notes\":\"Initial report created\"}]', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-02 14:39:53', '2025-06-02 14:39:53', NULL),
(8, NULL, 3, 'Main Campus', 'User\'s last known location', NULL, NULL, NULL, NULL, NULL, 'PANIC BUTTON ACTIVATED - Immediate assistance required', '2025-06-02 14:41:06', 'high', 'high', 'open', 1, '[{\"status\":\"open\",\"user_id\":3,\"timestamp\":\"2025-06-02T15:41:06+00:00\",\"notes\":\"Initial report created\"}]', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-02 14:41:06', '2025-06-02 14:41:06', NULL),
(9, NULL, 1, 'Main Campus', 'Bush Canteen Road', 9.06024797, 7.44235539, NULL, '{\"email\":\"oladejoabdullateef2005@gmail.com\",\"phone\":\"09032617923\"}', '[\"evidence\\/TLTJMeh2CrUCJGEcDPQ9yjL5v9vgug9VDkKOP1Qn.png\"]', 'I was going through the BK Road when these fiery-looking bandits attack me and collect my two arms and one leg.', '2025-06-03 01:03:48', 'medium', 'medium', 'open', 0, '[{\"status\":\"open\",\"user_id\":1,\"timestamp\":\"2025-06-03T02:03:48+00:00\",\"notes\":\"Initial report created\"}]', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-03 01:03:48', '2025-06-03 01:03:48', NULL),
(10, NULL, 1, 'Main Campus', 'Test location', 0.00000000, 0.00000000, 'DFKKRKKT', NULL, NULL, 'Test anonymous report', '2025-06-03 01:06:50', 'low', 'low', 'open', 0, '[{\"status\":\"open\",\"user_id\":1,\"timestamp\":\"2025-06-03T02:06:50+00:00\",\"notes\":\"Initial report created\"}]', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-03 01:06:50', '2025-06-03 01:06:50', NULL),
(11, NULL, 1, 'Main Campus', 'Bush Canteen Road', 9.06024797, 7.44235539, '4CLZCY7W', '{\"email\":\"oladejoabdullateef2005@gmail.com\",\"phone\":\"09032617923\"}', '[\"evidence\\/CfVLO1Ki9FIFGE0apaWFcrjVYKaI0C7NGeZtH6qw.png\"]', 'I was going through the BK Road when these fiery-looking bandits attack me and collect my two arms and one leg.', '2025-06-03 01:07:40', 'medium', 'medium', 'open', 0, '[{\"status\":\"open\",\"user_id\":1,\"timestamp\":\"2025-06-03T02:07:40+00:00\",\"notes\":\"Initial report created\"}]', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-03 01:07:40', '2025-06-03 01:07:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `report_categories`
--

CREATE TABLE `report_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `severity_level` enum('low','medium','high') NOT NULL DEFAULT 'low',
  `response_time` int(11) DEFAULT NULL,
  `requires_approval` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `report_categories`
--

INSERT INTO `report_categories` (`id`, `name`, `description`, `severity_level`, `response_time`, `requires_approval`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Theft', 'Reports related to theft of personal or university property', 'medium', 30, 0, '2025-06-02 08:21:13', '2025-06-02 08:21:13', NULL),
(2, 'Assault', 'Reports related to physical or verbal assault', 'high', 15, 1, '2025-06-02 08:21:23', '2025-06-02 08:21:23', NULL),
(3, 'Suspicious Activity', 'Reports of suspicious persons or activities on campus', 'medium', 20, 0, '2025-06-02 08:21:35', '2025-06-02 08:21:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `report_comments`
--

CREATE TABLE `report_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `response_protocols`
--

CREATE TABLE `response_protocols` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `applies_to_all_categories` tinyint(1) NOT NULL DEFAULT 0,
  `required_teams` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Types of teams required for this protocol' CHECK (json_valid(`required_teams`)),
  `required_resources` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Equipment and resources needed' CHECK (json_valid(`required_resources`)),
  `external_agencies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'External agencies to notify if needed' CHECK (json_valid(`external_agencies`)),
  `target_response_time` int(10) UNSIGNED NOT NULL DEFAULT 15 COMMENT 'Target response time in minutes',
  `resolution_time_target` int(10) UNSIGNED NOT NULL DEFAULT 60 COMMENT 'Target resolution time in minutes',
  `steps_low` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Response steps for low severity' CHECK (json_valid(`steps_low`)),
  `steps_medium` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Response steps for medium severity' CHECK (json_valid(`steps_medium`)),
  `steps_high` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Response steps for high severity' CHECK (json_valid(`steps_high`)),
  `steps_critical` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Response steps for critical severity' CHECK (json_valid(`steps_critical`)),
  `escalation_triggers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Conditions that trigger escalation' CHECK (json_valid(`escalation_triggers`)),
  `escalation_steps` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Steps to take when escalating' CHECK (json_valid(`escalation_steps`)),
  `auto_escalation_time` int(10) UNSIGNED DEFAULT NULL COMMENT 'Time in minutes after which to auto-escalate',
  `notification_list` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Who to notify for this type of incident' CHECK (json_valid(`notification_list`)),
  `requires_police_report` tinyint(1) NOT NULL DEFAULT 0,
  `requires_medical_response` tinyint(1) NOT NULL DEFAULT 0,
  `requires_evacuation_plan` tinyint(1) NOT NULL DEFAULT 0,
  `requires_evidence_collection` tinyint(1) NOT NULL DEFAULT 0,
  `required_documentation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Documents that must be completed' CHECK (json_valid(`required_documentation`)),
  `follow_up_actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Actions required after resolution' CHECK (json_valid(`follow_up_actions`)),
  `notes` text DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `last_reviewed` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `version` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `response_protocols`
--

INSERT INTO `response_protocols` (`id`, `name`, `code`, `description`, `priority`, `category_id`, `applies_to_all_categories`, `required_teams`, `required_resources`, `external_agencies`, `target_response_time`, `resolution_time_target`, `steps_low`, `steps_medium`, `steps_high`, `steps_critical`, `escalation_triggers`, `escalation_steps`, `auto_escalation_time`, `notification_list`, `requires_police_report`, `requires_medical_response`, `requires_evacuation_plan`, `requires_evidence_collection`, `required_documentation`, `follow_up_actions`, `notes`, `author`, `last_reviewed`, `active`, `version`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Theft Response Protocol', 'TRP-1', 'Standard protocol for responding to theft incidents on campus', 'medium', 1, 0, '[\"investigation\",\"patrol\"]', '[\"evidence_collection_kit\",\"camera\",\"incident_forms\"]', NULL, 15, 120, '[\"Dispatch patrol officer to take report\",\"Document incident details and affected items\",\"Secure the area if needed\",\"Provide incident reference number to reporter\",\"Follow up within 48 hours\"]', '[\"Dispatch patrol team to scene immediately\",\"Secure the area and preserve evidence\",\"Interview witnesses and collect statements\",\"Review nearest CCTV footage\",\"Complete incident report with all details\",\"Follow up within 24 hours\"]', '[\"Dispatch investigation team and patrol units immediately\",\"Secure crime scene with perimeter\",\"Document and photograph all evidence\",\"Interview all witnesses\",\"Review all CCTV in the area\",\"Report to campus management\",\"Consider police involvement if high value\",\"Complete detailed investigation report\",\"Follow up within 12 hours\"]', NULL, '[\"Value of stolen items exceeds \\u20a650,000\",\"Pattern matching previous thefts\",\"Forced entry or property damage\",\"Theft from secure\\/restricted areas\"]', NULL, NULL, '{\"security_director\":\"all\",\"zone_manager\":\"all\",\"campus_management\":\"high\",\"police\":\"high\"}', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-06-02 08:18:22', '2025-06-02 08:18:22', NULL),
(2, 'Medical Emergency Protocol', 'MEP-1', 'Protocol for medical emergencies requiring immediate response', 'critical', 5, 0, '[\"response\",\"emergency\"]', '[\"first_aid_kit\",\"AED\",\"emergency_contacts\",\"stretcher\"]', '[\"campus_clinic\",\"nearest_hospital\"]', 5, 30, '[\"Dispatch rapid response team with medical kit\",\"Alert campus clinic for immediate response\",\"Clear area and ensure access for medical personnel\",\"Provide emergency first aid\",\"Contact external emergency services if needed\",\"Document all actions taken\"]', '[\"Dispatch nearest security personnel with first aid training\",\"Contact campus clinic\",\"Provide basic first aid if trained\",\"Arrange transport to medical facility if needed\",\"Document incident details\"]', '[\"Dispatch all available medical response personnel\",\"Call emergency services (ambulance) immediately\",\"Perform CPR\\/first aid as required\",\"Clear routes for ambulance access\",\"Assign escort to guide medical services\",\"Notify family\\/emergency contacts\",\"Secure the scene if injury was result of incident\",\"Complete full documentation of response\"]', NULL, '[\"Unconsciousness\",\"Severe bleeding\",\"Difficulty breathing\",\"Suspected heart attack or stroke\",\"Multiple casualties\"]', NULL, NULL, '{\"campus_clinic\":\"all\",\"security_director\":\"high\",\"emergency_services\":\"high,critical\"}', 0, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-06-02 08:18:22', '2025-06-02 08:18:22', NULL),
(3, 'Assault Response Protocol', 'ARP-1', 'Protocol for responding to physical or verbal assault incidents', 'high', 2, 0, '[\"response\",\"investigation\"]', '[\"first_aid_kit\",\"camera\",\"evidence_collection_kit\"]', '[\"police\",\"campus_clinic\"]', 5, 60, '[\"Dispatch multiple response teams immediately\",\"Secure scene and separate involved parties\",\"Assess injuries and arrange medical attention\",\"Identify and isolate aggressor(s)\",\"Interview witnesses immediately\",\"Collect evidence and document scene\",\"Notify campus management\",\"Consider campus ban for aggressors\",\"Arrange escort for victim if needed\"]', '[\"Dispatch security team to separate parties\",\"Assess for injuries and provide first aid if needed\",\"Interview involved parties separately\",\"Document incident with photos and statements\",\"Refer to student affairs\\/disciplinary committee\",\"Offer counseling resources to affected individuals\"]', '[\"Call police immediately\",\"Dispatch all available security personnel\",\"Secure scene and establish perimeter\",\"Provide emergency medical care\",\"Identify and detain perpetrator if safe to do so\",\"Evacuate area if ongoing threat\",\"Preserve all evidence\",\"Activate campus emergency response team\",\"Implement communication plan for campus community\"]', NULL, '[\"Weapons involved\",\"Serious injury\",\"Multiple attackers\",\"Hate crime elements\",\"Sexual assault component\"]', NULL, NULL, '{\"security_director\":\"all\",\"campus_management\":\"high,critical\",\"police\":\"high,critical\",\"counseling_services\":\"all\"}', 1, 1, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-06-02 08:18:22', '2025-06-02 08:18:22', NULL),
(4, 'Suspicious Activity Protocol', 'SAP-1', 'Protocol for investigating and responding to suspicious activities', 'medium', 3, 0, '[\"patrol\",\"surveillance\"]', '[\"radio\",\"camera\",\"binoculars\"]', NULL, 10, 60, '[\"Dispatch patrol officer to observe discreetly\",\"Monitor situation without direct confrontation\",\"Document observations and behaviors\",\"Check if person has legitimate campus business\",\"Increase patrols in area\"]', '[\"Dispatch two patrol officers to area\",\"Approach subject professionally and request identification\",\"Determine reason for presence on campus\",\"Document interaction completely\",\"Escort off campus if no legitimate purpose\",\"Add to watch list if appropriate\"]', '[\"Deploy multiple teams to establish surveillance\",\"Position officers to intercept if subject attempts to flee\",\"Approach with caution and request identification\",\"Contact relevant departments to verify identity claims\",\"Search campus access logs\",\"Consider detention if warranted\",\"Document all actions and findings\"]', NULL, '[\"Subject refuses to identify\",\"Attempts to access restricted areas\",\"Matches description of previous security incidents\",\"Displays threatening behavior\",\"Appears to be monitoring security patterns\"]', NULL, NULL, '{\"security_director\":\"medium,high\",\"zone_manager\":\"all\"}', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-06-02 08:18:22', '2025-06-02 08:18:22', NULL),
(5, 'Vandalism Response Protocol', 'VRP-1', 'Protocol for responding to property damage and vandalism', 'medium', 4, 0, '[\"patrol\",\"investigation\"]', '[\"camera\",\"evidence_collection_kit\",\"property_damage_form\"]', NULL, 15, 120, '[\"Document damage with photos\",\"Estimate repair costs\",\"Check nearby CCTV cameras\",\"Complete incident report\",\"Notify facilities management\"]', '[\"Secure area to prevent further damage\",\"Document extensively with photos and measurements\",\"Collect any physical evidence\",\"Interview potential witnesses\",\"Review CCTV footage\",\"Complete detailed damage assessment\",\"Notify facilities and department head\"]', '[\"Establish perimeter around damaged area\",\"Dispatch investigation team\",\"Document all damage comprehensively\",\"Collect all available evidence\",\"Interview all potential witnesses\",\"Review extended CCTV footage\",\"Determine if targeted or random\",\"Assess security vulnerabilities\",\"Consider police report for extensive damage\",\"Implement temporary security measures\"]', NULL, '[\"Damage exceeds \\u20a6100,000\",\"Affects critical infrastructure\",\"Contains threatening messages\",\"Shows pattern of targeted vandalism\",\"Impacts campus operations\"]', NULL, NULL, '{\"facilities_management\":\"all\",\"security_director\":\"medium,high\",\"department_head\":\"all\",\"campus_management\":\"high\"}', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-06-02 08:18:22', '2025-06-02 08:18:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `response_updates`
--

CREATE TABLE `response_updates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `emergency_response_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('en_route','on_scene','resolved','withdrawn') NOT NULL,
  `message` text NOT NULL,
  `location_lat` double DEFAULT NULL,
  `location_lng` double DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_resources`
--

CREATE TABLE `security_resources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `available` tinyint(1) NOT NULL DEFAULT 1,
  `current_location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_to_id` bigint(20) UNSIGNED DEFAULT NULL,
  `last_used` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `capabilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`capabilities`)),
  `maintenance_due` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'operational',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_shifts`
--

CREATE TABLE `security_shifts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `zone_id` bigint(20) UNSIGNED NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ended_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `route_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`route_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_teams`
--

CREATE TABLE `security_teams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `leader_id` bigint(20) UNSIGNED NOT NULL,
  `zone_id` bigint(20) UNSIGNED NOT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_team_members`
--

CREATE TABLE `security_team_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `security_team_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

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
('esJd6KctWyol0fmfetn7nnnWYPvusr62otmDhIkF', 2, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRlZ2dU1TQkFOb25vTm9PSjJMd3VtbUlTZ3JCdXp2ak1sU3pYREhmaCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbiI7fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=', 1748917047),
('oUPLxhisHWKRdRtycR7khjF2dnw5SHqoGGSyyPG1', 2, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiZWl3SmJpYlRuRnBlUU9STXFEYWFXdVlIajZKUGFkS1Nkd0ljazQzWSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mjt9', 1748918082);

-- --------------------------------------------------------

--
-- Table structure for table `shift_incidents`
--

CREATE TABLE `shift_incidents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `shift_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `severity` varchar(255) NOT NULL DEFAULT 'low',
  `location` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`location`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `area` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'area',
  `area_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `safety_points` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'student',
  `avatar` varchar(255) DEFAULT NULL,
  `notification_prefs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_prefs`)),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(255) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `safety_points`, `created_at`, `updated_at`, `role`, `avatar`, `notification_prefs`, `deleted_at`, `is_active`, `status`) VALUES
(1, 'Anonymous User', 'anonymous@cashield.local', NULL, '$2y$12$G3ZMkZ4.jB4xu9FoxQZz/uFa1.vJROP4HpOirguBzN4/pKtygruMW', NULL, 0, '2025-06-02 08:18:38', '2025-06-02 08:18:38', 'anonymous', NULL, NULL, NULL, 1, 'active'),
(2, 'Campus Admin', 'admin@cashield.ng', NULL, '$2y$12$yFpvJQRioiLBGkCQhMtsleSjd9A1m5tqvrgQrbr.UbhZy86D6L3Na', NULL, 0, '2025-06-02 08:18:38', '2025-06-02 08:18:38', 'admin', NULL, NULL, NULL, 1, 'active'),
(3, 'Test User', 'test@example.com', '2025-06-02 08:18:38', '$2y$12$Bo68ubEoq/SJFPPn22n0Rek4x5KGEGqzgy1zeYFnkNuAxwP9Ym6G6', '6g22IUJnQKSx6xrHRFoDNNc1xsS9T055MwKwAHHNUwMVnXtZToWJM2367yzB', 0, '2025-06-02 08:18:39', '2025-06-02 08:18:39', 'student', NULL, NULL, NULL, 1, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `zone_checkpoints`
--

CREATE TABLE `zone_checkpoints` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `zone_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `location` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`location`)),
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_id_foreign` (`user_id`),
  ADD KEY `audit_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`);

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `badge_user`
--
ALTER TABLE `badge_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `badge_user_user_id_foreign` (`user_id`),
  ADD KEY `badge_user_badge_id_foreign` (`badge_id`);

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
-- Indexes for table `campus_zones`
--
ALTER TABLE `campus_zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `campus_zones_code_unique` (`code`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_messages_report_id_foreign` (`report_id`),
  ADD KEY `chat_messages_user_id_foreign` (`user_id`);

--
-- Indexes for table `checkpoint_scans`
--
ALTER TABLE `checkpoint_scans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checkpoint_scans_shift_id_foreign` (`shift_id`),
  ADD KEY `checkpoint_scans_checkpoint_id_foreign` (`checkpoint_id`);

--
-- Indexes for table `emergency_responses`
--
ALTER TABLE `emergency_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emergency_responses_report_id_foreign` (`report_id`),
  ADD KEY `emergency_responses_responder_id_foreign` (`responder_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `incident_categories`
--
ALTER TABLE `incident_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `incident_categories_slug_unique` (`slug`),
  ADD KEY `incident_categories_name_index` (`name`),
  ADD KEY `incident_categories_slug_index` (`slug`),
  ADD KEY `incident_categories_default_severity_index` (`default_severity`),
  ADD KEY `incident_categories_parent_id_active_index` (`parent_id`,`active`);

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
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_preferences_user_id_foreign` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `push_subscriptions_endpoint_unique` (`endpoint`),
  ADD KEY `push_subscriptions_subscribable_type_subscribable_id_index` (`subscribable_type`,`subscribable_id`);

--
-- Indexes for table `ranks`
--
ALTER TABLE `ranks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reports_user_id_foreign` (`user_id`),
  ADD KEY `reports_assigned_user_id_foreign` (`assigned_user_id`),
  ADD KEY `reports_assigned_team_id_foreign` (`assigned_team_id`),
  ADD KEY `reports_zone_id_foreign` (`zone_id`),
  ADD KEY `reports_severity_index` (`severity`),
  ADD KEY `reports_status_index` (`status`),
  ADD KEY `reports_created_at_index` (`created_at`),
  ADD KEY `reports_status_severity_index` (`status`,`severity`),
  ADD KEY `reports_category_id_foreign` (`category_id`);

--
-- Indexes for table `report_categories`
--
ALTER TABLE `report_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_categories_name_unique` (`name`);

--
-- Indexes for table `report_comments`
--
ALTER TABLE `report_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_comments_report_id_foreign` (`report_id`),
  ADD KEY `report_comments_user_id_foreign` (`user_id`);

--
-- Indexes for table `response_protocols`
--
ALTER TABLE `response_protocols`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `response_protocols_code_unique` (`code`),
  ADD KEY `response_protocols_priority_index` (`priority`),
  ADD KEY `response_protocols_category_id_active_index` (`category_id`,`active`),
  ADD KEY `response_protocols_emergency_index` (`requires_police_report`,`requires_medical_response`);

--
-- Indexes for table `response_updates`
--
ALTER TABLE `response_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `response_updates_emergency_response_id_foreign` (`emergency_response_id`);

--
-- Indexes for table `security_resources`
--
ALTER TABLE `security_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `security_resources_current_location_id_foreign` (`current_location_id`),
  ADD KEY `security_resources_assigned_to_id_foreign` (`assigned_to_id`);

--
-- Indexes for table `security_shifts`
--
ALTER TABLE `security_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `security_shifts_user_id_foreign` (`user_id`),
  ADD KEY `security_shifts_team_id_foreign` (`team_id`),
  ADD KEY `security_shifts_zone_id_foreign` (`zone_id`);

--
-- Indexes for table `security_teams`
--
ALTER TABLE `security_teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `security_teams_leader_id_foreign` (`leader_id`),
  ADD KEY `security_teams_zone_id_foreign` (`zone_id`);

--
-- Indexes for table `security_team_members`
--
ALTER TABLE `security_team_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `security_team_members_security_team_id_user_id_unique` (`security_team_id`,`user_id`),
  ADD KEY `security_team_members_user_id_foreign` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `shift_incidents`
--
ALTER TABLE `shift_incidents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shift_incidents_shift_id_foreign` (`shift_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriptions_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `zone_checkpoints`
--
ALTER TABLE `zone_checkpoints`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `zone_checkpoints_code_unique` (`code`),
  ADD KEY `zone_checkpoints_zone_id_foreign` (`zone_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `badge_user`
--
ALTER TABLE `badge_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `campus_zones`
--
ALTER TABLE `campus_zones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checkpoint_scans`
--
ALTER TABLE `checkpoint_scans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emergency_responses`
--
ALTER TABLE `emergency_responses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `incident_categories`
--
ALTER TABLE `incident_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `push_subscriptions`
--
ALTER TABLE `push_subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ranks`
--
ALTER TABLE `ranks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `report_categories`
--
ALTER TABLE `report_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `report_comments`
--
ALTER TABLE `report_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `response_protocols`
--
ALTER TABLE `response_protocols`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `response_updates`
--
ALTER TABLE `response_updates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_resources`
--
ALTER TABLE `security_resources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_shifts`
--
ALTER TABLE `security_shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_teams`
--
ALTER TABLE `security_teams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_team_members`
--
ALTER TABLE `security_team_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shift_incidents`
--
ALTER TABLE `shift_incidents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `zone_checkpoints`
--
ALTER TABLE `zone_checkpoints`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `badge_user`
--
ALTER TABLE `badge_user`
  ADD CONSTRAINT `badge_user_badge_id_foreign` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `badge_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `checkpoint_scans`
--
ALTER TABLE `checkpoint_scans`
  ADD CONSTRAINT `checkpoint_scans_checkpoint_id_foreign` FOREIGN KEY (`checkpoint_id`) REFERENCES `zone_checkpoints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `checkpoint_scans_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `security_shifts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `emergency_responses`
--
ALTER TABLE `emergency_responses`
  ADD CONSTRAINT `emergency_responses_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `emergency_responses_responder_id_foreign` FOREIGN KEY (`responder_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `incident_categories`
--
ALTER TABLE `incident_categories`
  ADD CONSTRAINT `incident_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `incident_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD CONSTRAINT `notification_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_assigned_team_id_foreign` FOREIGN KEY (`assigned_team_id`) REFERENCES `security_teams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reports_assigned_user_id_foreign` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reports_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `report_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reports_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `campus_zones` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `report_comments`
--
ALTER TABLE `report_comments`
  ADD CONSTRAINT `report_comments_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `response_protocols`
--
ALTER TABLE `response_protocols`
  ADD CONSTRAINT `response_protocols_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `incident_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `response_updates`
--
ALTER TABLE `response_updates`
  ADD CONSTRAINT `response_updates_emergency_response_id_foreign` FOREIGN KEY (`emergency_response_id`) REFERENCES `emergency_responses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `security_resources`
--
ALTER TABLE `security_resources`
  ADD CONSTRAINT `security_resources_assigned_to_id_foreign` FOREIGN KEY (`assigned_to_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `security_resources_current_location_id_foreign` FOREIGN KEY (`current_location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `security_shifts`
--
ALTER TABLE `security_shifts`
  ADD CONSTRAINT `security_shifts_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `security_teams` (`id`),
  ADD CONSTRAINT `security_shifts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `security_shifts_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `campus_zones` (`id`);

--
-- Constraints for table `security_teams`
--
ALTER TABLE `security_teams`
  ADD CONSTRAINT `security_teams_leader_id_foreign` FOREIGN KEY (`leader_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `security_teams_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `campus_zones` (`id`);

--
-- Constraints for table `security_team_members`
--
ALTER TABLE `security_team_members`
  ADD CONSTRAINT `security_team_members_security_team_id_foreign` FOREIGN KEY (`security_team_id`) REFERENCES `security_teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `security_team_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shift_incidents`
--
ALTER TABLE `shift_incidents`
  ADD CONSTRAINT `shift_incidents_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `security_shifts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `zone_checkpoints`
--
ALTER TABLE `zone_checkpoints`
  ADD CONSTRAINT `zone_checkpoints_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `campus_zones` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
