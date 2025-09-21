-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 21, 2025 at 08:46 AM
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
-- Database: `bookhaven`
--

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Judul` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Penulis` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Penerbit` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Tahun` year(4) NOT NULL,
  `Harga` decimal(10,0) NOT NULL,
  `Stok` int(100) NOT NULL,
  `Tanggal_Masuk` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Deskripsi` text DEFAULT NULL,
  `ID_Kategori` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id`, `Judul`, `Penulis`, `Penerbit`, `Tahun`, `Harga`, `Stok`, `Tanggal_Masuk`, `Deskripsi`, `ID_Kategori`) VALUES
('B001', 'Laut Bercerita', 'Leila S. Chudori', 'Gramedia Pustaka Utama', '2017', 100000, 50, '2025-09-21 06:35:02', 'Sebuah novel yang menggambarkan kehidupan di era reformasi.', 1),
('B002', 'Cantik Itu Luka', 'Eka Kurniawan', 'Gramedia Pustaka Utama', '2002', 95000, 60, '2025-09-21 06:35:02', 'Novel yang menggabungkan realisme magis dengan sejarah Indonesia.', 1),
('B003', 'Lukacita', 'Valerie Patkar', 'Gramedia Pustaka Utama', '2021', 120000, 40, '2025-09-21 06:35:02', 'Cerita tentang kehidupan remaja dan pencarian jati diri.', 2),
('B004', 'Heartbreak Motel', 'Valerie Patkar', 'Gramedia Pustaka Utama', '2020', 110000, 45, '2025-09-21 06:35:02', 'Novel romantis dengan konflik yang menyentuh hati.', 2),
('B005', 'Home Sweet Loan', 'Valerie Patkar', 'Gramedia Pustaka Utama', '2022', 115000, 30, '2025-09-21 06:35:02', 'Kisah tentang perjuangan hidup dan cinta di tengah kesulitan finansial.', 2),
('B006', 'Maria Beetle', 'Kotaro Isaka', 'Gramedia Pustaka Utama', '2022', 130000, 25, '2025-09-21 06:35:02', 'Thriller Jepang yang penuh aksi dan humor.', 3),
('B007', 'Perpustakaan Tengah Malam', 'Matt Haig', 'Gramedia Pustaka Utama', '2021', 125000, 35, '2025-09-21 06:35:02', 'Novel fantasi yang mengajarkan tentang pentingnya pilihan hidup.', 3),
('B008', 'Atomic Habits', 'James Clear', 'Gramedia Pustaka Utama', '2022', 140000, 50, '2025-09-21 06:35:02', 'Panduan praktis untuk membentuk kebiasaan baik.', 4),
('B009', 'The Humans', 'Matt Haig', 'Gramedia Pustaka Utama', '2020', 135000, 40, '2025-09-21 06:35:02', 'Cerita tentang alien yang belajar menjadi manusia.', 4),
('B010', 'A Crane Among Wolves', 'June Hur', 'Gramedia Pustaka Utama', '2022', 145000, 30, '2025-09-21 06:35:02', 'Novel sejarah berlatar era Joseon dengan misteri yang mendalam.', 5),
('B011', 'Digital Marketing Hacks', 'Anonymus', 'Gramedia Pustaka Utama', '2023', 150000, 20, '2025-09-21 06:35:02', 'Strategi pemasaran digital untuk bisnis modern.', 6),
('B012', 'Law of Attraction', 'Anonymus', 'Gramedia Pustaka Utama', '2023', 125000, 60, '2025-09-21 06:35:02', 'Panduan memahami dan menerapkan hukum tarik-menarik.', 6),
('B013', 'Insecurity is My Middle Name', 'Anonymus', 'Gramedia Pustaka Utama', '2022', 110000, 50, '2025-09-21 06:35:02', 'Buku self-help untuk mengatasi rasa tidak aman.', 4),
('B014', 'Melangkah', 'Anonymus', 'Gramedia Pustaka Utama', '2022', 100000, 55, '2025-09-21 06:35:02', 'Motivasi untuk memulai langkah pertama menuju perubahan.', 4),
('B015', 'Black Showman dan Pembunuhan di Kota Tak Bernama', 'Anonymus', 'Gramedia Pustaka Utama', '2022', 120000, 45, '2025-09-21 06:35:02', 'Novel misteri dengan alur yang menegangkan.', 3),
('B016', 'Jujutsu Kaisen 0', 'Gege Akutami', 'Gramedia Pustaka Utama', '2022', 95000, 70, '2025-09-21 06:35:02', 'Komik aksi dengan elemen supranatural yang kuat.', 7),
('B017', 'One Piece 99', 'Eiichiro Oda', 'Gramedia Pustaka Utama', '2022', 100000, 65, '2025-09-21 06:35:02', 'Petualangan lanjutan Luffy dan kru Topi Jerami.', 7),
('B018', 'Teach Like Finland', 'Timothy Walker', 'Gramedia Pustaka Utama', '2022', 135000, 30, '2025-09-21 06:35:02', 'Panduan pendidikan berdasarkan sistem Finlandia.', 4),
('B019', 'Quarter-Life Crisis', 'Anonymus', 'Gramedia Pustaka Utama', '2022', 110000, 50, '2025-09-21 06:35:02', 'Buku motivasi untuk menghadapi krisis usia 25 tahun.', 4),
('B020', 'Marketing 4.0', 'Philip Kotler', 'Gramedia Pustaka Utama', '2022', 145000, 40, '2025-09-21 06:35:02', 'Evolusi pemasaran di era digital.', 6),
('B021', 'Teach Like Finland', 'Timothy Walker', 'Gramedia Pustaka Utama', '2022', 130000, 30, '2025-09-21 06:35:02', 'Strategi mengajar efektif ala Finlandia.', 4),
('B022', 'Digital Marketing Hacks', 'Anonymus', 'Gramedia Pustaka Utama', '2023', 125000, 35, '2025-09-21 06:35:02', 'Trik dan tips pemasaran digital untuk pemula.', 6),
('B023', 'Law of Attraction', 'Anonymus', 'Gramedia Pustaka Utama', '2023', 120000, 50, '2025-09-21 06:35:02', 'Menerapkan hukum tarik-menarik dalam kehidupan sehari-hari.', 6),
('B024', 'Insecurity is My Middle Name', 'Anonymus', 'Gramedia Pustaka Utama', '2022', 115000, 45, '2025-09-21 06:35:02', 'Mengatasi rasa tidak aman dan membangun kepercayaan diri.', 4),
('B025', 'Melangkah', 'Anonymus', 'Gramedia Pustaka Utama', '2022', 110000, 60, '2025-09-21 06:35:02', 'Langkah pertama menuju perubahan besar dalam hidup.', 4),
('B026', 'Black Showman dan Pembunuhan di Kota Tak Bernama', 'Anonymus', 'Gramedia Pustaka Utama', '2022', 105000, 50, '2025-09-21 06:35:02', 'Cerita misteri dengan twist yang mengejutkan.', 3),
('B027', 'Jujutsu Kaisen 0', 'Gege Akutami', 'Gramedia Pustaka Utama', '2022', 100000, 70, '2025-09-21 06:35:02', 'Pertarungan seru dengan roh kutukan.', 7),
('B028', 'One Piece 99', 'Eiichiro Oda', 'Gramedia Pustaka Utama', '2022', 95000, 65, '2025-09-21 06:35:02', 'Petualangan epik di dunia bajak laut.', 7),
('B029', 'Teach Like Finland', 'Timothy Walker', 'Gramedia Pustaka Utama', '2022', 130000, 30, '2025-09-21 06:35:02', 'Mengadopsi metode mengajar dari Finlandia.', 4),
('B030', 'Quarter-Life Crisis', 'Anonymus', 'Gramedia Pustaka Utama', '2022', 115000, 50, '2025-09-21 06:35:02', 'Menghadapi tantangan hidup di usia 25 tahun.', 4);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int(20) NOT NULL,
  `Nama_Kategori` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `Nama_Kategori`) VALUES
(1, 'Fiksi'),
(2, 'Non-Fiksi'),
(3, 'Sejarah'),
(4, 'Gizi'),
(5, 'Komputer'),
(6, 'Majalah'),
(7, 'Romance'),
(8, 'Thriller'),
(9, 'Fantasi'),
(10, 'Sains'),
(11, 'Art & Design'),
(12, 'Biografi'),
(13, 'Self-development'),
(14, 'Ekonomi'),
(15, 'Finance'),
(16, 'Sastra'),
(17, 'Fiksi Remaja'),
(18, 'Psikologi'),
(19, 'Flora'),
(20, 'Fauna');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Tanggal` date NOT NULL,
  `Total_harga` decimal(20,0) NOT NULL,
  `Status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_buku` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `Tanggal`, `Total_harga`, `Status`, `id_user`, `id_buku`) VALUES
('T001', '2025-09-21', 100000, 'Selesai', 'U001', 'B001'),
('T002', '2025-09-20', 190000, 'Dibayar', 'U002', 'B002'),
('T003', '2025-09-19', 120000, 'Pending', 'U003', 'B003'),
('T004', '2025-09-18', 330000, 'Dikirim', 'U004', 'B004'),
('T005', '2025-09-17', 115000, 'Selesai', 'U005', 'B005'),
('T006', '2025-09-16', 130000, 'Dibayar', 'U006', 'B006'),
('T007', '2025-09-15', 85000, 'Pending', 'U007', 'B007'),
('T008', '2025-09-14', 163000, 'Selesai', 'U008', 'B008'),
('T009', '2025-09-13', 135000, 'Dikirim', 'U009', 'B009'),
('T010', '2025-09-12', 145000, 'Dibayar', 'U010', 'B010'),
('T011', '2025-09-11', 150000, 'Selesai', 'U011', 'B011'),
('T012', '2025-09-10', 250000, 'Pending', 'U012', 'B012'),
('T013', '2025-09-09', 63000, 'Dibayar', 'U013', 'B013'),
('T014', '2025-09-08', 132000, 'Dikirim', 'U014', 'B014'),
('T015', '2025-09-07', 120000, 'Selesai', 'U015', 'B015'),
('T016', '2025-09-06', 370000, 'Dibayar', 'U016', 'B016'),
('T017', '2025-09-05', 200000, 'Pending', 'U017', 'B017'),
('T018', '2025-09-04', 130000, 'Selesai', 'U018', 'B018'),
('T019', '2025-09-03', 157000, 'Dikirim', 'U019', 'B019'),
('T020', '2025-09-02', 145000, 'Dibayar', 'U020', 'B020'),
('T021', '2025-09-01', 130000, 'Pending', 'U021', 'B021'),
('T022', '2025-08-31', 125000, 'Selesai', 'U022', 'B022'),
('T023', '2025-08-30', 120000, 'Dikirim', 'U023', 'B023'),
('T024', '2025-08-29', 115000, 'Dibayar', 'U024', 'B024'),
('T025', '2025-08-28', 110000, 'Selesai', 'U025', 'B025'),
('T026', '2025-08-27', 105000, 'Pending', 'U026', 'B026'),
('T027', '2025-08-26', 100000, 'Dikirim', 'U027', 'B027'),
('T028', '2025-08-25', 95000, 'Dibayar', 'U028', 'B028'),
('T029', '2025-08-24', 198000, 'Selesai', 'U029', 'B029'),
('T030', '2025-08-23', 182000, 'Pending', 'U030', 'B030');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` varchar(50) NOT NULL,
  `Nama` varchar(100) NOT NULL,
  `Email` varchar(30) NOT NULL,
  `Password` varchar(12) NOT NULL,
  `alamat` varchar(200) DEFAULT NULL,
  `tanggal_masuk` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Nomor_telepon` varchar(13) DEFAULT NULL,
  `Role` enum('Admin','Pembeli','Penjual') DEFAULT 'Pembeli'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `Nama`, `Email`, `Password`, `alamat`, `tanggal_masuk`, `Nomor_telepon`, `Role`) VALUES
('U001', 'User_1', 'user1@example.com', 'a722c63db8ec', 'Jl. Contoh No.1, Kota Dummy', '2025-09-21 06:31:18', '08120000001', 'Pembeli'),
('U002', 'User_2', 'user2@example.com', 'c1572d05424d', 'Jl. Contoh No.2, Kota Dummy', '2025-09-21 06:31:18', '08120000002', 'Pembeli'),
('U003', 'User_3', 'user3@example.com', '3afc79b597f8', 'Jl. Contoh No.3, Kota Dummy', '2025-09-21 06:31:18', '08120000003', 'Pembeli'),
('U004', 'User_4', 'user4@example.com', 'fc2921d9057a', 'Jl. Contoh No.4, Kota Dummy', '2025-09-21 06:31:18', '08120000004', 'Penjual'),
('U005', 'User_5', 'user5@example.com', 'd35f6fa9a794', 'Jl. Contoh No.5, Kota Dummy', '2025-09-21 06:31:18', '08120000005', 'Admin'),
('U006', 'User_6', 'user6@example.com', 'e9568c9ea43a', 'Jl. Contoh No.6, Kota Dummy', '2025-09-21 06:31:18', '08120000006', 'Penjual'),
('U007', 'User_7', 'user7@example.com', '8c96c3884a82', 'Jl. Contoh No.7, Kota Dummy', '2025-09-21 06:31:18', '08120000007', 'Penjual'),
('U008', 'User_8', 'user8@example.com', 'ccd3cd182257', 'Jl. Contoh No.8, Kota Dummy', '2025-09-21 06:31:18', '08120000008', 'Pembeli'),
('U009', 'User_9', 'user9@example.com', 'c28cce9cbd2d', 'Jl. Contoh No.9, Kota Dummy', '2025-09-21 06:31:18', '08120000009', 'Penjual'),
('U010', 'User_10', 'user10@example.com', 'a3224611fd03', 'Jl. Contoh No.10, Kota Dummy', '2025-09-21 06:31:18', '08120000010', 'Penjual'),
('U011', 'User_11', 'user11@example.com', '0102812fbd5f', 'Jl. Contoh No.11, Kota Dummy', '2025-09-21 06:31:18', '08120000011', 'Penjual'),
('U012', 'User_12', 'user12@example.com', '0bd0fe6372c6', 'Jl. Contoh No.12, Kota Dummy', '2025-09-21 06:31:18', '08120000012', 'Penjual'),
('U013', 'User_13', 'user13@example.com', 'c868bff94e54', 'Jl. Contoh No.13, Kota Dummy', '2025-09-21 06:31:18', '08120000013', 'Admin'),
('U014', 'User_14', 'user14@example.com', 'd1f38b569c77', 'Jl. Contoh No.14, Kota Dummy', '2025-09-21 06:31:18', '08120000014', 'Pembeli'),
('U015', 'User_15', 'user15@example.com', 'b279786ec5a7', 'Jl. Contoh No.15, Kota Dummy', '2025-09-21 06:31:18', '08120000015', 'Penjual'),
('U016', 'User_16', 'user16@example.com', '66c99bf933f5', 'Jl. Contoh No.16, Kota Dummy', '2025-09-21 06:31:18', '08120000016', 'Penjual'),
('U017', 'User_17', 'user17@example.com', '6c2a5c9ead1d', 'Jl. Contoh No.17, Kota Dummy', '2025-09-21 06:31:18', '08120000017', 'Penjual'),
('U018', 'User_18', 'user18@example.com', '64152ab7368f', 'Jl. Contoh No.18, Kota Dummy', '2025-09-21 06:31:18', '08120000018', 'Penjual'),
('U019', 'User_19', 'user19@example.com', '1f61b744f2c9', 'Jl. Contoh No.19, Kota Dummy', '2025-09-21 06:31:18', '08120000019', 'Admin'),
('U020', 'User_20', 'user20@example.com', '90bfa11df19a', 'Jl. Contoh No.20, Kota Dummy', '2025-09-21 06:31:18', '08120000020', 'Pembeli'),
('U021', 'User_21', 'user21@example.com', '5cddd1f7857f', 'Jl. Contoh No.21, Kota Dummy', '2025-09-21 06:31:18', '08120000021', 'Admin'),
('U022', 'User_22', 'user22@example.com', 'b9974191c2e2', 'Jl. Contoh No.22, Kota Dummy', '2025-09-21 06:31:18', '08120000022', 'Penjual'),
('U023', 'User_23', 'user23@example.com', 'b9b09ad3b376', 'Jl. Contoh No.23, Kota Dummy', '2025-09-21 06:31:18', '08120000023', 'Penjual'),
('U024', 'User_24', 'user24@example.com', '87de23031d30', 'Jl. Contoh No.24, Kota Dummy', '2025-09-21 06:31:18', '08120000024', 'Admin'),
('U025', 'User_25', 'user25@example.com', '41e4652a622b', 'Jl. Contoh No.25, Kota Dummy', '2025-09-21 06:31:18', '08120000025', 'Pembeli'),
('U026', 'User_26', 'user26@example.com', 'ea8852d03533', 'Jl. Contoh No.26, Kota Dummy', '2025-09-21 06:31:18', '08120000026', 'Pembeli'),
('U027', 'User_27', 'user27@example.com', '713fdc6c473c', 'Jl. Contoh No.27, Kota Dummy', '2025-09-21 06:31:18', '08120000027', 'Pembeli'),
('U028', 'User_28', 'user28@example.com', '421b66e92350', 'Jl. Contoh No.28, Kota Dummy', '2025-09-21 06:31:18', '08120000028', 'Pembeli'),
('U029', 'User_29', 'user29@example.com', '6cf1cd514774', 'Jl. Contoh No.29, Kota Dummy', '2025-09-21 06:31:18', '08120000029', 'Pembeli'),
('U030', 'User_30', 'user30@example.com', '60d589174ca2', 'Jl. Contoh No.30, Kota Dummy', '2025-09-21 06:31:18', '08120000030', 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(50) NOT NULL,
  `Jumlah` int(10) NOT NULL,
  `id_buku` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_user` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `Jumlah`, `id_buku`, `id_user`) VALUES
(1, 1, 'B001', 'U001'),
(2, 2, 'B002', 'U002'),
(3, 1, 'B003', 'U003'),
(4, 3, 'B004', 'U004'),
(5, 1, 'B005', 'U005'),
(6, 2, 'B006', 'U006'),
(7, 1, 'B007', 'U007'),
(8, 1, 'B008', 'U008'),
(9, 2, 'B009', 'U009'),
(10, 1, 'B010', 'U010'),
(11, 1, 'B011', 'U011'),
(12, 2, 'B012', 'U012'),
(13, 4, 'B013', 'U013'),
(14, 3, 'B014', 'U014'),
(15, 1, 'B015', 'U015'),
(16, 2, 'B016', 'U016'),
(17, 1, 'B017', 'U017'),
(18, 1, 'B018', 'U018'),
(19, 2, 'B019', 'U019'),
(20, 1, 'B020', 'U020'),
(21, 1, 'B021', 'U021'),
(22, 2, 'B022', 'U022'),
(23, 8, 'B023', 'U023'),
(24, 3, 'B024', 'U024'),
(25, 1, 'B025', 'U025'),
(26, 2, 'B026', 'U026'),
(27, 9, 'B027', 'U027'),
(28, 1, 'B028', 'U028'),
(29, 2, 'B029', 'U029'),
(30, 1, 'B030', 'U030');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_buku_kategori` (`ID_Kategori`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_transaksi` (`id_user`),
  ADD KEY `fk_buku_transaksi` (`id_buku`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `EMAIL` (`Email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`,`Jumlah`),
  ADD KEY `fk_wishlist_user` (`id_user`),
  ADD KEY `fk_wishlist_buku` (`id_buku`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `fk_buku_kategori` FOREIGN KEY (`ID_Kategori`) REFERENCES `kategori` (`id`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_user_transaksi` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`);

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_buku` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id`),
  ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
