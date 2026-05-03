-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 12, 2025 at 04:42 AM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mbc_cs`
--

-- --------------------------------------------------------

--
-- Table structure for table `alumni`
--

CREATE TABLE `alumni` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `data_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas_yang_akan_diikuti` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leads` enum('Iklan','Instagram','Facebook','Tiktok','Lain-Lain') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Iklan',
  `leads_custom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provinsi_id` bigint(20) UNSIGNED NOT NULL,
  `provinsi_nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kota_id` bigint(20) UNSIGNED NOT NULL,
  `kota_nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_bisnis` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_bisnis` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_wa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kendala` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ikut_kelas` tinyint(1) NOT NULL DEFAULT 0,
  `kelas_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sudah_pernah_ikut_kelas_apa_saja` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelas_yang_belum_diikuti_apa_saja` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_activities`
--

CREATE TABLE `daily_activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily_activities`
--

INSERT INTO `daily_activities` (`id`, `tanggal`, `created_at`, `updated_at`) VALUES
(1, '2025-08-07', '2025-08-06 19:41:59', '2025-08-06 19:41:59'),
(2, '2025-08-09', '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(3, '2025-08-11', '2025-08-10 18:17:37', '2025-08-10 18:17:37');

-- --------------------------------------------------------

--
-- Table structure for table `daily_activityitems`
--

CREATE TABLE `daily_activityitems` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `daily_activity_id` bigint(20) UNSIGNED NOT NULL,
  `kategori` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aktivitas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target` int(11) DEFAULT NULL,
  `real` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily_activityitems`
--

INSERT INTO `daily_activityitems` (`id`, `daily_activity_id`, `kategori`, `aktivitas`, `deskripsi`, `target`, `real`, `created_at`, `updated_at`) VALUES
(1, 1, 'pribadi', 'Niat & Doa Pagi', 'Niatkan untuk memberi manfaat kepada sesama Muslim melalui coaching', 1, 1, '2025-08-06 19:41:59', '2025-08-06 19:41:59'),
(2, 1, 'mencari_leads', 'Konten Harian (Story, Feed, TikTok, dll.)', 'Posting Edukasi, testimoni, penawaran soft selling (bergantian)', 1, 1, '2025-08-06 19:41:59', '2025-08-06 19:41:59'),
(6, 2, 'pribadi', 'Review Target Harian', 'Melihat kembali target prospek dan closing', 1, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(7, 2, 'pribadi', 'Belajar dan Catat', 'Apa tambahan Ilmu dan perbaikan saya hari ini', 1, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(8, 2, 'mencari_leads', 'List Building / sales plan', 'Tambah database baru WA, email, atau DM list', 10, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(9, 2, 'mencari_leads', 'Interaksi Manual', 'Komentar', 10, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(10, 2, 'mencari_leads', 'Join Komunitas', 'Bergabung ke grup yang prospek', 1, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(11, 2, 'memprospek', 'Follow-Up Soft', 'Kirim konten edukatif, reminder kelas, testimoni', 200, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(12, 2, 'memprospek', 'Membangun Hubungan', 'Menggali masalah dan impian calon peserta', 20, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(13, 2, 'memprospek', 'Kirim Penawaran', 'Kirim info kelas dengan penjelasan manfaat', 20, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(14, 2, 'closing', 'Tanya Keberatan', 'Apa yang menghambat mereka daftar?', 20, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(15, 2, 'closing', 'Atasi Keberatan', 'Kirim voice note, testimoni, atau diskusi', 20, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(16, 2, 'closing', 'Penawaran Khusus', 'Diskon, bonus, urgency terbatas', 10, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(17, 2, 'closing', 'Pendaftaran', 'Fix mengikuti dan hadir kelas', 2500000, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(18, 2, 'closing', 'Finalisasi Pembayaran', 'Kirim link invoice, konfirmasi pembayaran', 2500000, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(19, 2, 'merawat_customer', 'Follow-up Peserta', 'Bangun Hubungan, tanya progress, kirim semangat', 50, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(20, 2, 'merawat_customer', 'Minta Testimoni', 'Peserta yang puas diminta testimoni', 3, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(21, 2, 'merawat_customer', 'Program Referral', 'Ajak mereka referensikan teman', 10, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(22, 2, 'merawat_customer', 'Edukasi Lanjutan', 'Kirim konten lanjutan/upgrade kelas berikutnya', 20, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(23, 2, 'merawat_customer', 'Komentar Positive', 'komentasi positif untuk peserta mbc', 10, NULL, '2025-08-08 22:34:18', '2025-08-08 22:34:18'),
(24, 3, 'pribadi', NULL, NULL, NULL, 1, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(25, 3, 'pribadi', NULL, NULL, NULL, 1, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(26, 3, 'pribadi', NULL, NULL, NULL, 1, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(27, 3, 'mencari_leads', NULL, NULL, NULL, 4, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(28, 3, 'mencari_leads', NULL, NULL, NULL, 5, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(29, 3, 'mencari_leads', NULL, NULL, NULL, 10, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(30, 3, 'mencari_leads', NULL, NULL, NULL, 100, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(31, 3, 'mencari_leads', NULL, NULL, NULL, 100, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(32, 3, 'mencari_leads', NULL, NULL, NULL, 1, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(33, 3, 'memprospek', NULL, NULL, NULL, 200, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(34, 3, 'memprospek', NULL, NULL, NULL, 20, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(35, 3, 'memprospek', NULL, NULL, NULL, 20, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(36, 3, 'closing', NULL, NULL, NULL, 20, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(37, 3, 'closing', NULL, NULL, NULL, 20, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(38, 3, 'closing', NULL, NULL, NULL, 10, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(39, 3, 'closing', NULL, NULL, NULL, 2500000, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(40, 3, 'closing', NULL, NULL, NULL, 2500000, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(41, 3, 'merawat_customer', NULL, NULL, NULL, 50, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(42, 3, 'merawat_customer', NULL, NULL, NULL, 3, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(43, 3, 'merawat_customer', NULL, NULL, NULL, 10, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(44, 3, 'merawat_customer', NULL, NULL, NULL, 20, '2025-08-10 18:17:37', '2025-08-10 18:17:37'),
(45, 3, 'merawat_customer', NULL, NULL, NULL, 10, '2025-08-10 18:17:37', '2025-08-10 18:17:37');

-- --------------------------------------------------------

--
-- Table structure for table `data`
--

CREATE TABLE `data` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `leads` enum('Iklan','Instagram','Facebook','Tiktok','Lain-Lain') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Iklan',
  `leads_custom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provinsi_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provinsi_nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kota_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kota_nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenisbisnis` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_bisnis` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_wa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `situasi_bisnis` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kendala` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ikut_kelas` tinyint(1) NOT NULL DEFAULT 0,
  `kelas_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_by_role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `data`
--

INSERT INTO `data` (`id`, `nama`, `leads`, `leads_custom`, `provinsi_id`, `provinsi_nama`, `kota_id`, `kota_nama`, `jenisbisnis`, `nama_bisnis`, `no_wa`, `situasi_bisnis`, `kendala`, `ikut_kelas`, `kelas_id`, `created_by`, `created_by_role`, `created_at`, `updated_at`) VALUES
(27, 'cek', 'Facebook', '', '33', 'JAWA TENGAH', '3312', 'KABUPATEN WONOGIRI', 'Bisnis Transportasi & Logistik', 'Cek', '089999999', 'cek', 'cek', 1, 2, 'mbchamasah@gmail.com', 'admninistrator', '2025-08-11 00:37:29', '2025-08-11 00:37:29'),
(28, 'Jufri', 'Iklan', '', '35', 'JAWA TIMUR', '3578', 'KOTA SURABAYA', 'Bisnis Agribisnis', 'Pertenakan Udang', '081331809396', 'sedang mengembangan internal', 'Pengembangan team dan sistem', 1, 8, 'Yasmin', 'cs', '2025-08-11 00:46:29', '2025-08-11 01:51:59');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_kelas` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id`, `nama_kelas`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 'Sistemasi Bisnis', 'Deskripsi untuk Kelas A', '2025-07-30 01:25:56', '2025-07-30 01:25:56'),
(2, 'Great Manager', 'Deskripsi untuk Kelas B', '2025-07-30 01:25:56', '2025-07-30 01:25:56'),
(3, 'Scale-Up 10X', 'Deskripsi untuk Kelas C', '2025-07-30 01:25:56', '2025-07-30 01:25:56'),
(4, 'Leadership', 'Deskripsi untuk Kelas C', '2025-07-30 01:25:56', '2025-07-30 01:25:56'),
(5, 'CS dan Sales Jago Closing', 'Deskripsi untuk Kelas C', '2025-07-30 01:25:56', '2025-07-30 01:25:56'),
(6, 'Repeat Order', 'Deskripsi untuk Kelas C', '2025-07-30 01:25:56', '2025-07-30 01:25:56'),
(7, 'Keuangan', 'Deskripsi untuk Kelas C', '2025-07-30 01:25:56', '2025-07-30 01:25:56'),
(8, 'HRD Mastery', 'Deskripsi untuk Kelas C', '2025-07-30 01:25:56', '2025-07-30 01:25:56'),
(9, 'Public Speaking', 'Deskripsi untuk Kelas C', '2025-07-30 01:25:56', '2025-07-30 01:25:56');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2025_07_15_075547_create_crms_table', 2),
(5, '2025_07_30_013832_remove_total_omset_from_crms_table', 3),
(6, '2025_07_30_063629_create_kelas_table', 4),
(7, '2025_07_30_064405_create_data_table', 5),
(8, '2025_07_30_074253_create_jenisbisnis_table', 6),
(9, '2025_07_30_081625_create_kelas_table', 7),
(10, '2025_07_30_090241_rename_jenisbisnis_to_jenis_bisnis', 8),
(11, '2025_08_01_034246_create_alumni_table', 9),
(12, '2025_08_02_012208_create_salesplans_table', 10),
(13, '2025_08_02_034833_add_kelas_to_salesplans_table', 11),
(14, '2025_08_04_011916_create_salesplans_table', 12),
(15, '2025_08_05_065152_create_daily_activities_table', 13),
(16, '2025_08_05_090404_add_deleted_at_to_salesplans_table', 14),
(17, '2025_08_06_161318_create_daily_activities_table', 15),
(18, '2025_08_06_161524_create_daily_activityitems_table', 15),
(19, '2025_08_07_023609_add_kelas_yang_akan_diikuti_to_alumni_table', 15),
(20, '2025_08_07_025350_add_data_id_to_alumni_table', 16),
(21, '2025_08_07_043610_add_nominal_to_salesplans_table', 17),
(22, '2025_08_07_064953_add_kelas_id_to_salesplans_table', 18),
(23, '2025_08_07_065236_add_tanggal_to_salesplans_table', 19),
(24, '2025_08_11_060106_add_role_to_users_table', 20),
(25, '2025_08_11_072517_add_created_by_to_data_table', 21);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salesplans`
--

CREATE TABLE `salesplans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kelas_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `data_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fu1_hasil` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fu1_tindak_lanjut` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fu2_hasil` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fu2_tindak_lanjut` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fu3_hasil` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fu3_tindak_lanjut` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fu4_hasil` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fu4_tindak_lanjut` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fu5_hasil` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fu5_tindak_lanjut` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fu6_hasil` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fu6_tindak_lanjut` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fu7_hasil` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fu7_tindak_lanjut` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fu8_hasil` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fu8_tindak_lanjut` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('cold','warm','hot','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cold',
  `nominal` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `salesplans`
--

INSERT INTO `salesplans` (`id`, `kelas_id`, `tanggal`, `data_id`, `fu1_hasil`, `fu1_tindak_lanjut`, `fu2_hasil`, `fu2_tindak_lanjut`, `fu3_hasil`, `fu3_tindak_lanjut`, `fu4_hasil`, `fu4_tindak_lanjut`, `fu5_hasil`, `fu5_tindak_lanjut`, `fu6_hasil`, `fu6_tindak_lanjut`, `fu7_hasil`, `fu7_tindak_lanjut`, `fu8_hasil`, `fu8_tindak_lanjut`, `keterangan`, `status`, `nominal`, `created_at`, `updated_at`, `deleted_at`) VALUES
(23, NULL, NULL, 27, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dimasukkan otomatis berdasarkan kelas: Tidak diketahui', 'cold', NULL, '2025-08-11 15:17:31', '2025-08-11 15:17:31', NULL),
(24, NULL, NULL, 28, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dimasukkan otomatis berdasarkan kelas: Tidak diketahui', 'cold', NULL, '2025-08-11 19:23:22', '2025-08-11 19:23:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cs'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`) VALUES
(1, 'mbchamasah@gmail.com', 'mbchamasah@gmail.com', NULL, '$2y$10$IqLKbrpNlN7nBdQCqScZxemGnv5uS611USa6sMCiR6bX7tapi57/m', '8YjaeDYZjNcbkYnQQ2PSxbo1rxgtn94alVL9TTFPY3cPGdK4KiV76lcSuyiy', '2025-07-13 18:49:44', '2025-07-13 18:49:44', 'admninistrator'),
(2, 'Linda', 'mbc.hamasah1@gmail.com', NULL, '$2y$10$UcxKfgw0zb9g5GVxlLq0q.zkit0.2pUBIgL/I6Dz3.y.JTQqNiQsu', NULL, '2025-08-10 22:45:53', '2025-08-10 22:45:53', 'cs'),
(3, 'Yasmin', 'mbchamasah2@gmail.com', NULL, '$2y$10$nG1f1duznhfaeHoribSCruBJaoy1hUK99qKjAg2/bpdcokV3tWFwe', '5RXMP5BKQ2FxiIOKPqfuRjcTzO6Iovm8wFwgqqjyRv0VQZnPJOXpr8ou8NfG', '2025-08-10 22:55:33', '2025-08-10 22:55:33', 'cs'),
(4, 'Tursia', 'tursiambc@gmail.com', NULL, '$2y$10$30hWGqpHSvB6t87O8eET8e.6Ou0Shs9qgCWTtc.WAJSGsLk.TMPPK', NULL, '2025-08-10 22:56:15', '2025-08-10 22:56:15', 'cs'),
(5, 'Livia', 'mbcfjs1@gmail.com', NULL, '$2y$10$X1pkzqezNsZFp0Px.L6Fa.v.WZN6SlrABxRZnBgvbvWcg4qNcswjO', NULL, '2025-08-10 22:57:22', '2025-08-10 22:57:22', 'cs'),
(6, 'shafa', 'mbcshafa@gmail.com', NULL, '$2y$10$U9HJ.6aWM6QaxjXRJHZrEe8ay2RMjJgKQAVgqBqbpRx2MPCrsAZFu', NULL, '2025-08-10 22:58:24', '2025-08-10 22:58:24', 'cs');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alumni`
--
ALTER TABLE `alumni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alumni_kelas_id_foreign` (`kelas_id`),
  ADD KEY `alumni_data_id_foreign` (`data_id`);

--
-- Indexes for table `daily_activities`
--
ALTER TABLE `daily_activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daily_activityitems`
--
ALTER TABLE `daily_activityitems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `daily_activityitems_daily_activity_id_foreign` (`daily_activity_id`);

--
-- Indexes for table `data`
--
ALTER TABLE `data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `data_kelas_id_foreign` (`kelas_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
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
-- Indexes for table `salesplans`
--
ALTER TABLE `salesplans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salesplans_data_id_foreign` (`data_id`);

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
-- AUTO_INCREMENT for table `alumni`
--
ALTER TABLE `alumni`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `daily_activities`
--
ALTER TABLE `daily_activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `daily_activityitems`
--
ALTER TABLE `daily_activityitems`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `data`
--
ALTER TABLE `data`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `salesplans`
--
ALTER TABLE `salesplans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alumni`
--
ALTER TABLE `alumni`
  ADD CONSTRAINT `alumni_data_id_foreign` FOREIGN KEY (`data_id`) REFERENCES `data` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alumni_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `daily_activityitems`
--
ALTER TABLE `daily_activityitems`
  ADD CONSTRAINT `daily_activityitems_daily_activity_id_foreign` FOREIGN KEY (`daily_activity_id`) REFERENCES `daily_activities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `data`
--
ALTER TABLE `data`
  ADD CONSTRAINT `data_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `salesplans`
--
ALTER TABLE `salesplans`
  ADD CONSTRAINT `salesplans_data_id_foreign` FOREIGN KEY (`data_id`) REFERENCES `data` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
