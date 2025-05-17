-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 17 Bulan Mei 2025 pada 08.53
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `swastha_raksa`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `swastha_raksa_blacklist_key`
--

CREATE TABLE `swastha_raksa_blacklist_key` (
  `id_blacklist` int(11) NOT NULL,
  `key` text NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `swastha_raksa_migrations`
--

CREATE TABLE `swastha_raksa_migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `swastha_raksa_migrations`
--

INSERT INTO `swastha_raksa_migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(42, '2025-05-13-103106', 'App\\Database\\Migrations\\CreateUsers', 'default', 'App', 1747464797, 1),
(43, '2025-05-13-111040', 'App\\Database\\Migrations\\CreateProducts', 'default', 'App', 1747464797, 1),
(44, '2025-05-13-133946', 'App\\Database\\Migrations\\CreateBlacklistKey', 'default', 'App', 1747464797, 1),
(45, '2025-05-13-140341', 'App\\Database\\Migrations\\CreateTransaction', 'default', 'App', 1747464797, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `swastha_raksa_products`
--

CREATE TABLE `swastha_raksa_products` (
  `product_id` int(11) NOT NULL,
  `owner_uid` varchar(255) NOT NULL,
  `nama_product` varchar(255) NOT NULL,
  `jenis_product` varchar(255) NOT NULL,
  `harga_product` int(11) NOT NULL,
  `stok_product` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `swastha_raksa_transaction`
--

CREATE TABLE `swastha_raksa_transaction` (
  `id_transaction` int(11) NOT NULL,
  `seller_uid` varchar(255) NOT NULL,
  `buyer_uid` varchar(255) NOT NULL,
  `transaction_time` int(11) UNSIGNED NOT NULL,
  `product_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`product_data`)),
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `swastha_raksa_users`
--

CREATE TABLE `swastha_raksa_users` (
  `uid` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` text NOT NULL,
  `profile_picture` text DEFAULT 'nophoto.jpg',
  `tipe_akun` enum('Pembeli','Penjual') NOT NULL DEFAULT 'Pembeli',
  `data_toko` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data_toko`)),
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `swastha_raksa_blacklist_key`
--
ALTER TABLE `swastha_raksa_blacklist_key`
  ADD PRIMARY KEY (`id_blacklist`);

--
-- Indeks untuk tabel `swastha_raksa_migrations`
--
ALTER TABLE `swastha_raksa_migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `swastha_raksa_products`
--
ALTER TABLE `swastha_raksa_products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `swastha_raksa_products_owner_uid_foreign` (`owner_uid`);

--
-- Indeks untuk tabel `swastha_raksa_transaction`
--
ALTER TABLE `swastha_raksa_transaction`
  ADD PRIMARY KEY (`id_transaction`);

--
-- Indeks untuk tabel `swastha_raksa_users`
--
ALTER TABLE `swastha_raksa_users`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `swastha_raksa_blacklist_key`
--
ALTER TABLE `swastha_raksa_blacklist_key`
  MODIFY `id_blacklist` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `swastha_raksa_migrations`
--
ALTER TABLE `swastha_raksa_migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT untuk tabel `swastha_raksa_products`
--
ALTER TABLE `swastha_raksa_products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `swastha_raksa_transaction`
--
ALTER TABLE `swastha_raksa_transaction`
  MODIFY `id_transaction` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `swastha_raksa_products`
--
ALTER TABLE `swastha_raksa_products`
  ADD CONSTRAINT `swastha_raksa_products_owner_uid_foreign` FOREIGN KEY (`owner_uid`) REFERENCES `swastha_raksa_users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
