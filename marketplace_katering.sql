-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Database target: MariaDB XAMPP
-- Nama database aplikasi: marketplace_katering

CREATE DATABASE IF NOT EXISTS `marketplace_katering`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `marketplace_katering`;

SET SQL_MODE
= "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone
= "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `marketplace_katering`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang`
--

CREATE TABLE IF NOT EXISTS `barang`
(
  `id` int
(11) NOT NULL,
  `id_barang` varchar
(255) NOT NULL,
  `id_kategori` int
(11) NOT NULL,
  `nama_barang` text NOT NULL,
  `merk` varchar
(255) NOT NULL,
  `harga_beli` varchar
(255) NOT NULL,
  `harga_jual` varchar
(255) NOT NULL,
  `satuan_barang` varchar
(255) NOT NULL,
  `stok` text NOT NULL,
  `tgl_input` varchar
(255) NOT NULL,
  `tgl_update` varchar
(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `barang`
--

INSERT INTO `barang` (`id`, `id_barang`, `id_kategori`, `nama_barang`, `merk`, `harga_beli`, `harga_jual`, `satuan_barang`, `stok`, `tgl_input`, `tgl_update`) VALUES
(1, 'BR001', 1, 'PULPEN', 'standar', '1500', '2000', 'satu nya', '17', '25 January 2021, 16:51', '28 January 2021, 19:17'),
(2, 'BR002', 1, 'PENGHAPUS', 'ATK', '300', '500', 'PCS', '11', '25 January 2021, 16:53', '28 January 2021, 19:16'),
(3, 'BR003', 2, 'MOMOGI', '56g', '400', '500', 'satu nya', '96', '25 January 2021, 16:55', '28 January 2021, 19:15'),
(4, 'BR004', 2, 'SUKRO', '43g', '4000', '5000', 'PCS', '27', '25 January 2021, 16:57', '28 January 2021, 19:15'),
(5, 'BR005', 3, 'AQUA', '3 iiter', '2500', '3000', 'satu nya', '46', '25 January 2021, 16:59', '28 January 2021, 19:14'),
(6, 'BR006', 3, 'SPRITE', 'SODA', '4500', '5000', 'PCS', '22', '25 January 2021, 17:01', '28 January 2021, 19:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE IF NOT EXISTS `kategori`
(
  `id_kategori` int
(11) NOT NULL,
  `nama_kategori` varchar
(255) NOT NULL,
  `tgl_input` varchar
(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `tgl_input`) VALUES
(1, 'ATK', '25 January 2021, 17:07'),
(2, 'MAKANAN', '25 January 2021, 17:08'),
(3, 'MINUMAN', '25 January 2021, 17:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `login`
--

CREATE TABLE IF NOT EXISTS `login`
(
  `id_login` int
(11) NOT NULL,
  `user` varchar
(255) NOT NULL,
  `pass` char
(32) NOT NULL,
  `id_member` int
(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `login`
--

INSERT INTO `login` (`id_login`, `user`, `pass`, `id_member`) VALUES
(1, 'admin', '202cb962ac59075b964b07152d234b70', 1),
(2, 'sangga', '202cb962ac59075b964b07152d234b70', 2),
(3, 'sylvi', '202cb962ac59075b964b07152d234b70', 3),
(4, 'nanang', '202cb962ac59075b964b07152d234b70', 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `member`
--

CREATE TABLE IF NOT EXISTS `member`
(
  `id_member` int
(11) NOT NULL,
  `nm_member` varchar
(255) NOT NULL,
  `alamat_member` text NOT NULL,
  `telepon` varchar
(255) NOT NULL,
  `email` varchar
(255) NOT NULL,
  `gambar` text NOT NULL,
  `NIK` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `member`
--

INSERT INTO `member` (`id_member`, `nm_member`, `alamat_member`, `telepon`, `email`, `gambar`, `NIK`) VALUES
(1, 'admin kasir', 'unmuh ponorogo', '0', 'adminkasircoba@gmail.com', 'admin.jpg', 'kasir'),
(2, 'sangga', 'unmuh ponorogo', '082236334054', 'zolesan.cox@gmail.com', 'sangga.png', '18532979'),
(3, 'sylvi', 'unmuh ponorogo', '082324540004', 'sylvikhoirutun5@gmail.com', 'sylvi.png', '18533053'),
(4, 'nanang', 'unmuh ponorogo', '082230378117', 'nananghardita2@gmail.com', 'nanang.png', '18532889');

-- --------------------------------------------------------

--
-- Struktur dari tabel `nota`
--

CREATE TABLE IF NOT EXISTS `nota`
(
  `id_nota` int
(11) NOT NULL,
  `id_barang` varchar
(255) NOT NULL,
  `id_member` int
(11) NOT NULL,
  `jumlah` varchar
(255) NOT NULL,
  `total` varchar
(255) NOT NULL,
  `tanggal_input` varchar
(255) NOT NULL,
  `periode` varchar
(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `nota`
--

INSERT INTO `nota` (`id_nota`, `id_barang`, `id_member`, `jumlah`, `total`, `tanggal_input`, `periode`) VALUES
(1, 'BR001', 1, '0', '0', '29 januari 2021', '2 febuari 2021'),
(2, 'BR002', 1, '0', '0', '29 januari 2021', '2 febuari 2021'),
(3, 'BR003', 1, '0', '0', '29 januari 2021', '2 febuari 2021'),
(4, 'BR004', 1, '0', '0', '29 januari 2021', '2 febuari 2021'),
(5, 'BR005', 1, '0', '0', '29 januari 2021', '2 febuari 2021'),
(6, 'BR006', 1, '0', '0', '29 januari 2021', '2 febuari 2021'),
(39, 'BR001', 1, '1', '2000', '29 januari 2021', '01-2021'),
(40, 'BR002', 1, '0', '0', '29 januari 2021', '01-2021'),
(41, 'BR003', 1, '0', '0', '29 januari 2021', '01-2021'),
(42, 'BR004', 1, '0', '0', '29 januari 2021', '01-2021'),
(43, 'BR005', 1, '0', '0', '29 januari 2021', '01-2021'),
(44, 'BR006', 1, '0', '0', '29 januari 2021', '01-2021'),
(45, 'BR001', 1, '1', '2000', '29 januari 2021', '01-2021'),
(46, 'BR002', 1, '1', '500', '29 januari 2021', '01-2021'),
(47, 'BR003', 1, '0', '0', '29 januari 2021', '01-2021'),
(48, 'BR004', 1, '0', '0', '29 januari 2021', '01-2021'),
(49, 'BR005', 1, '0', '0', '29 januari 2021', '01-2021'),
(50, 'BR006', 1, '0', '0', '29 januari 2021', '01-2021'),
(51, 'BR001', 1, '0', '0', '29 January 2021, 18:28', '01-2021'),
(52, 'BR002', 1, '0', '0', '29 January 2021, 18:30', '01-2021'),
(53, 'BR001', 1, '0', '0', '29 januari 2021', '01-2021'),
(54, 'BR002', 1, '0', '0', '29 januari 2021', '01-2021'),
(55, 'BR003', 1, '0', '0', '29 januari 2021', '01-2021'),
(56, 'BR004', 1, '0', '0', '29 januari 2021', '01-2021'),
(57, 'BR005', 1, '0', '0', '29 januari 2021', '01-2021'),
(58, 'BR006', 1, '0', '0', '29 januari 2021', '01-2021'),
(59, 'BR001', 1, '0', '0', '29 January 2021, 18:28', '01-2021'),
(60, 'BR002', 1, '0', '0', '29 January 2021, 18:30', '01-2021'),
(61, 'BR003', 1, '1', '500', '29 January 2021, 18:33', '01-2021'),
(62, 'BR001', 1, '0', '0', '29 January 2021, 18:34', '01-2021');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

CREATE TABLE IF NOT EXISTS `penjualan`
(
  `id_penjualan` int
(11) NOT NULL,
  `id_barang` varchar
(255) NOT NULL,
  `id_member` int
(11) NOT NULL,
  `jumlah` varchar
(255) NOT NULL,
  `total` varchar
(255) NOT NULL,
  `tanggal_input` varchar
(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `penjualan`
--

INSERT INTO `penjualan` (`id_penjualan`, `id_barang`, `id_member`, `jumlah`, `total`, `tanggal_input`) VALUES
(1, 'BR001', 1, '0', '0', '29 januari 2021'),
(2, 'BR002', 1, '0', '0', '29 januari 2021'),
(3, 'BR003', 1, '0', '0', '29 januari 2021'),
(4, 'BR004', 1, '0', '0', '29 januari 2021'),
(5, 'BR005', 1, '0', '0', '29 januari 2021'),
(6, 'BR006', 1, '0', '0', '29 januari 2021'),
(33, 'BR001', 1, '0', '0', '29 January 2021, 18:28'),
(34, 'BR002', 1, '0', '0', '29 January 2021, 18:30'),
(35, 'BR003', 1, '1', '500', '29 January 2021, 18:33'),
(36, 'BR001', 1, '0', '0', '29 January 2021, 18:34');

-- --------------------------------------------------------

--
-- Struktur dari tabel `toko`
--

CREATE TABLE IF NOT EXISTS `toko`
(
  `id_toko` int
(11) NOT NULL,
  `nama_toko` varchar
(255) NOT NULL,
  `alamat_toko` text NOT NULL,
  `tlp` varchar
(255) NOT NULL,
  `nama_pemilik` varchar
(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `toko`
--

INSERT INTO `toko` (`id_toko`, `nama_toko`, `alamat_toko`, `tlp`, `nama_pemilik`) VALUES
(1, 'CV toko prosus', 'unmuh ponorogo', '082230378117', 'kelompok prosus');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `barang`
--
ALTER TABLE `barang`
ADD PRIMARY KEY
(`id`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
ADD PRIMARY KEY
(`id_kategori`);

--
-- Indeks untuk tabel `login`
--
ALTER TABLE `login`
ADD PRIMARY KEY
(`id_login`);

--
-- Indeks untuk tabel `member`
--
ALTER TABLE `member`
ADD PRIMARY KEY
(`id_member`);

--
-- Indeks untuk tabel `nota`
--
ALTER TABLE `nota`
ADD PRIMARY KEY
(`id_nota`) USING BTREE;

--
-- Indeks untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
ADD PRIMARY KEY
(`id_penjualan`);

--
-- Indeks untuk tabel `toko`
--
ALTER TABLE `toko`
ADD PRIMARY KEY
(`id_toko`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `barang`
--
ALTER TABLE `barang`
  MODIFY `id` int
(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int
(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `login`
--
ALTER TABLE `login`
  MODIFY `id_login` int
(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `member`
--
ALTER TABLE `member`
  MODIFY `id_member` int
(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `nota`
--
ALTER TABLE `nota`
  MODIFY `id_nota` int
(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id_penjualan` int
(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT untuk tabel `toko`
--
ALTER TABLE `toko`
  MODIFY `id_toko` int
(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
