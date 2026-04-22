-- ============================================
-- SECURITY ENHANCEMENT - Failed Login Attempts Table
-- ============================================
-- Tabel ini untuk tracking failed login attempts
-- dan blocking IP yang mencurigakan
-- ============================================

-- Buat tabel failed_login_attempts
CREATE TABLE IF NOT EXISTS `failed_login_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL COMMENT 'IPv4/IPv6 address',
  `username` varchar(255) DEFAULT NULL COMMENT 'Username yang dicoba',
  `user_agent` text DEFAULT NULL COMMENT 'Browser/device info',
  `reason` varchar(50) DEFAULT 'invalid_credentials' COMMENT 'Alasan gagal',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `failed_login_attempts_ip_address_index` (`ip_address`),
  KEY `failed_login_attempts_created_at_index` (`created_at`),
  KEY `failed_login_attempts_ip_created_index` (`ip_address`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VERIFICATION QUERY
-- ============================================
-- Cek apakah tabel sudah dibuat
-- SELECT COUNT(*) FROM information_schema.tables 
-- WHERE table_schema = 'project' AND table_name = 'failed_login_attempts';

-- ============================================
-- UTILITY QUERIES
-- ============================================

-- Lihat semua failed login attempts (10 terbaru)
-- SELECT * FROM failed_login_attempts ORDER BY created_at DESC LIMIT 10;

-- Hitung failed attempts per IP dalam 1 jam terakhir
-- SELECT ip_address, COUNT(*) as attempts, MAX(created_at) as last_attempt
-- FROM failed_login_attempts
-- WHERE created_at >= NOW() - INTERVAL 1 HOUR
-- GROUP BY ip_address
-- ORDER BY attempts DESC;

-- Hapus data lama (lebih dari 24 jam)
-- DELETE FROM failed_login_attempts WHERE created_at < NOW() - INTERVAL 24 HOUR;

-- Hapus semua failed attempts untuk IP tertentu (unblock IP)
-- DELETE FROM failed_login_attempts WHERE ip_address = '192.168.1.100';

-- ============================================
-- INSERT TEST DATA (OPTIONAL)
-- ============================================
-- Uncomment untuk insert test data
-- INSERT INTO failed_login_attempts (ip_address, username, user_agent, reason) 
-- VALUES ('192.168.1.100', 'admin', 'Mozilla/5.0', 'invalid_credentials');
