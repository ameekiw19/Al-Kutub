-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 10, 2026 at 08:31 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id_bookmark` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `id_kitab` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookmarks`
--

INSERT INTO `bookmarks` (`id_bookmark`, `user_id`, `id_kitab`, `created_at`, `updated_at`) VALUES
(20, 3, 27, '2025-11-11 03:42:52', '2025-11-11 03:42:52');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id_comment` int(11) NOT NULL,
  `id_kitab` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `isi_comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id_comment`, `id_kitab`, `user_id`, `isi_comment`, `created_at`, `updated_at`) VALUES
(1, 27, 3, 'p', '2025-11-05 03:38:18', '2025-11-05 03:38:18'),
(2, 27, 3, 'p', '2025-11-05 03:38:32', '2025-11-05 03:38:32'),
(3, 27, 5, 'halo', '2025-11-06 07:05:36', '2025-11-06 07:05:36'),
(4, 27, 3, 'p', '2025-11-08 00:15:46', '2025-11-08 00:15:46'),
(5, 26, 3, 'halo', '2025-11-11 03:38:50', '2025-11-11 03:38:50'),
(6, 26, 6, 'p', '2025-12-11 03:43:05', '2025-12-11 03:43:05');

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
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kitab_id` int(11) NOT NULL,
  `last_read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `history`
--

INSERT INTO `history` (`id`, `user_id`, `kitab_id`, `last_read_at`, `created_at`, `updated_at`) VALUES
(4, 3, 27, '2025-12-11 03:17:38', '2025-11-18 03:07:11', '2025-12-11 03:17:38'),
(5, 3, 26, '2025-12-11 03:42:27', '2025-12-11 03:42:27', '2025-12-11 03:42:27'),
(6, 6, 26, '2025-12-11 03:42:58', '2025-12-11 03:42:58', '2025-12-11 03:42:58'),
(8, 5, 26, '2026-01-10 00:26:04', '2026-01-10 00:26:04', '2026-01-10 00:26:04');

-- --------------------------------------------------------

--
-- Table structure for table `kitab`
--

CREATE TABLE `kitab` (
  `id_kitab` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `penulis` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `bahasa` enum('Arab','Indonesia') NOT NULL,
  `cover` varchar(255) NOT NULL,
  `file_pdf` varchar(255) DEFAULT NULL,
  `views` int(11) NOT NULL,
  `downloads` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `viewed_by` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`viewed_by`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kitab`
--

INSERT INTO `kitab` (`id_kitab`, `judul`, `penulis`, `deskripsi`, `kategori`, `bahasa`, `cover`, `file_pdf`, `views`, `downloads`, `created_at`, `updated_at`, `viewed_by`) VALUES
(26, 'adfa', 'adfa', 'adfa', 'aqidah', 'Indonesia', '1761353723_profile gw nantinya.jpeg', '1761353723_TERJEMAH MATAN JURUMIYAH.pdf', 4, 0, '2025-10-24 17:55:23', '2025-11-11 02:59:36', '[5,6,3]'),
(27, 'adfas', 'adfas', 'asdfas', 'tauhid', 'Arab', '1761353751_Screenshot from 2025-10-18 21-33-21.png', '1761353751_TERJEMAH MATAN JURUMIYAH.pdf', 9, 0, '2025-10-24 17:55:51', '2025-11-18 03:07:11', '[5,6,3]');

-- --------------------------------------------------------

--
-- Table structure for table `kitab_views`
--

CREATE TABLE `kitab_views` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `id_kitab` bigint(20) UNSIGNED NOT NULL,
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
(1, '2014_10_12_100000_create_password_resets_table', 1),
(2, '2019_08_19_000000_create_failed_jobs_table', 1),
(3, '2019_12_14_000001_create_personal_access_tokens_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `deskripsi`, `role`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'mimin', '$2y$10$dvlnNKHdkPXES1G8zVIs5.lFTrWn5uTzOWSHGQ1ydhCVZRyo4gAXi', 'mimin@gmail.com', NULL, 'admin', '081254674376', '2025-10-21 07:13:08', '2025-10-23 14:21:26'),
(3, 'asepp', '$2y$10$e8ln7XAYKlxgZkTij09HauHIDGuVSSDm0wZYasHun4lZXMA3DpYna', 'asep13@gmail.com', NULL, 'user', '085476386725', '2025-10-22 03:26:50', '2025-11-07 06:59:20'),
(4, 'agus', '$2y$10$m9v1v90abv/jwouARHIXSutHm.5A6QIG0jXZyhfJ5nwzi8pGW12QO', 'agus11@gmail.com', 'p balap', 'user', '085476386726', '2025-10-27 07:24:07', '2025-10-27 07:24:07'),
(5, 'Amir', '$2y$10$L8Jk5BLeE2yZPCJyF2lf4ONHL9aHifReR7eqoMcpjcrPUabZa/oZS', 'amir@gmail.com', NULL, 'user', '081387693487', '2025-11-06 07:05:07', '2025-11-11 03:53:16'),
(6, 'dede', '$2y$10$m2/.UAGq6nbA/A0h96QZseB0YgZFDhzRU8NND4YBYq/fGT2RUne7C', 'dede@gmail.com', NULL, 'user', '081254674376', '2025-11-08 00:58:23', '2025-11-08 00:58:23'),
(7, 'amee', '$2y$10$xHQnAU.yQFsVQevulj85W.njBPSwQhqXR7FI.vapOptxScFOYtFz6', 'amee@gmail.com', 'halo gess', 'user', '0812421451', '2026-01-07 04:06:44', '2026-01-07 04:06:44'),
(8, 'beni', '$2y$10$6zC7qRg7FJfrCufV5oNkSek7WHI9nu207ftmEL4vBihTHmtlU0C.K', 'beni@gmail.com', 'asdfsafdsaf', 'user', '085756488d', '2026-01-09 17:22:19', '2026-01-09 17:22:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id_bookmark`),
  ADD KEY `fk_bookmark_user` (`user_id`),
  ADD KEY `fk_bookmark_kitab` (`id_kitab`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id_comment`),
  ADD KEY `fk_comments_kitab` (`id_kitab`),
  ADD KEY `fk_comments_user` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_history_user` (`user_id`),
  ADD KEY `fk_history_kitab` (`kitab_id`);

--
-- Indexes for table `kitab`
--
ALTER TABLE `kitab`
  ADD PRIMARY KEY (`id_kitab`);

--
-- Indexes for table `kitab_views`
--
ALTER TABLE `kitab_views`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id_bookmark` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id_comment` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kitab`
--
ALTER TABLE `kitab`
  MODIFY `id_kitab` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `kitab_views`
--
ALTER TABLE `kitab_views`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `fk_bookmark_kitab` FOREIGN KEY (`id_kitab`) REFERENCES `kitab` (`id_kitab`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bookmark_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments_kitab` FOREIGN KEY (`id_kitab`) REFERENCES `kitab` (`id_kitab`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `history`
--
ALTER TABLE `history`
  ADD CONSTRAINT `fk_history_kitab` FOREIGN KEY (`kitab_id`) REFERENCES `kitab` (`id_kitab`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
