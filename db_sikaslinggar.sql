-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 21, 2025 at 02:49 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_sikaslinggar`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_laporan_riwayat` (IN `p_user_id` INT, IN `p_jenis` VARCHAR(20), IN `p_dari_tanggal` DATE, IN `p_sampai_tanggal` DATE)   BEGIN
    SELECT 
        r.*,
        u.nama as nama_user,
        CASE 
            WHEN r.jenis_transaksi = 'donasi' THEN 'Donasi Masuk'
            WHEN r.jenis_transaksi = 'pemasukan' THEN 'Pemasukan'
            WHEN r.jenis_transaksi = 'pengeluaran' THEN 'Pengeluaran'
            ELSE 'Transaksi Lainnya'
        END as label_transaksi
    FROM riwayat r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE 
        (p_user_id IS NULL OR r.user_id = p_user_id)
        AND (p_jenis IS NULL OR r.jenis_transaksi = p_jenis)
        AND DATE(r.tanggal_transaksi) BETWEEN p_dari_tanggal AND p_sampai_tanggal
    ORDER BY r.tanggal_transaksi DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_verifikasi_donasi` (IN `p_donasi_id` INT, IN `p_status` ENUM('verified','rejected'), IN `p_verified_by` INT, IN `p_keterangan` TEXT)   BEGIN
    UPDATE donasi 
    SET 
        status = p_status,
        verified_by = p_verified_by,
        tanggal_verifikasi = NOW(),
        keterangan_verifikasi = p_keterangan,
        updated_at = NOW()
    WHERE 
        id = p_donasi_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `activity` varchar(100) NOT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `activity`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 3, 'Menambah pemasukan dari jawa sebesar Rp 100.000', NULL, NULL, NULL, '2025-09-18 05:21:15'),
(2, 3, 'Menambah pengeluaran untuk tetef sebesar Rp 100.000', NULL, NULL, NULL, '2025-09-18 05:21:28'),
(3, 3, 'Mengubah data pengeluaran ID: 2', NULL, NULL, NULL, '2025-09-18 05:21:42'),
(4, 3, 'Menghapus pengeluaran untuk teteff sebesar Rp 100.000', NULL, NULL, NULL, '2025-09-18 05:21:48'),
(5, 3, 'Mengubah data pemasukan ID: 1', NULL, NULL, NULL, '2025-09-18 05:21:53'),
(6, 3, 'Menghapus pemasukan dari jawaef sebesar Rp 100.000', NULL, NULL, NULL, '2025-09-18 05:21:58'),
(7, 4, 'User baru terdaftar: testing (testing)', NULL, NULL, NULL, '2025-09-19 06:41:53'),
(8, 5, 'User baru terdaftar: testing12 (testing1)', NULL, NULL, NULL, '2025-09-21 02:44:44');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int NOT NULL,
  `tanggal` date NOT NULL,
  `jenis` enum('pemasukan','pengeluaran') NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `sumber` varchar(255) NOT NULL COMMENT 'Sumber pemasukan atau tujuan pengeluaran',
  `jumlah` decimal(15,2) NOT NULL,
  `keterangan` text,
  `bukti_transaksi` varchar(255) DEFAULT NULL COMMENT 'Path file bukti transaksi',
  `user_id` int DEFAULT NULL COMMENT 'ID user yang menginput',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `username`, `password`, `google_id`, `avatar`, `role`, `status`, `created_at`, `updated_at`) VALUES
(3, 'Ahmadhan Syafiere R. A', 'ahmadansyafiere@gmail.com', NULL, NULL, '107099694444491992260', 'https://lh3.googleusercontent.com/a/ACg8ocL6u_JPW7WGUWZDi2sJtmHLqXCnjmnn2osUxjJpF495faZ1nJOH=s96-c', 'admin', 'aktif', '2025-08-20 12:48:32', '2025-08-24 07:59:52'),
(4, 'testing', 'tes123@gmail.com', 'testing', '$2y$10$lM2IKtIXPdgHiKBJPmZRe.pPwpnMKuPg2YxNNL67Vehw9LqN/cJ7S', NULL, NULL, 'admin', 'aktif', '2025-09-19 06:41:53', '2025-09-19 07:06:23'),
(5, 'testing12', 'tes1213@gmail.com', 'testing1', '$2y$10$i16Nk5L/CD8zMTWI1xHNiuAFwsynOoeWyQYDZo5LWOiTDyjF.r.mi', NULL, NULL, 'user', 'aktif', '2025-09-21 02:44:44', '2025-09-21 02:44:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
