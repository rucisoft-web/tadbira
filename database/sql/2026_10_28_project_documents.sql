-- Tabel dokumen kegiatan, untuk server tanpa terminal (jalankan di phpMyAdmin > SQL).
-- Dengan terminal, cukup: php artisan migrate --force

CREATE TABLE IF NOT EXISTS `project_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ulid` char(26) NOT NULL,
  `project_id` bigint(20) unsigned NOT NULL,
  `task_id` bigint(20) unsigned DEFAULT NULL,
  `category` varchar(40) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `extension` varchar(10) NOT NULL,
  `mime` varchar(120) DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_documents_ulid_unique` (`ulid`),
  KEY `project_documents_task_id_foreign` (`task_id`),
  KEY `project_documents_uploaded_by_foreign` (`uploaded_by`),
  KEY `project_documents_project_id_created_at_index` (`project_id`,`created_at`),
  CONSTRAINT `project_documents_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_documents_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_28_000005_create_project_documents_table', x.b
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS b FROM `migrations`) AS x
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_10_28_000005_create_project_documents_table');
