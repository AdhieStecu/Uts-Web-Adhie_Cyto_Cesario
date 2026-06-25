-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 20 Bulan Mei 2026 pada 19.51
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
-- Database: `gamestore_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Top Up', 'topup', '💎', 'Top Up Diamond, Robux, dan lainnya', 1, 1, '2026-05-16 04:33:41'),
(2, 'Game Key', 'game-key', '🎮', 'Steam Key, Epic Games, dan lainnya', 1, 2, '2026-05-16 04:33:41'),
(3, 'Akun', 'akun', '👤', 'Jual beli akun game', 1, 3, '2026-05-16 04:33:41'),
(4, 'Voucher', 'voucher', '🎫', 'Voucher game dan marketplace', 1, 4, '2026-05-16 04:33:41'),
(5, 'Roblox Games', 'roblox', '🟥', 'Item dan currency Roblox', 1, 5, '2026-05-16 04:33:41'),
(6, 'Item', 'item', '⚔️', 'Item dalam game', 1, 6, '2026-05-16 04:33:41'),
(7, 'Koin Game', 'koin-game', '🪙', 'Koin dan currency game', 1, 7, '2026-05-16 04:33:41'),
(8, 'RPG Games', 'rpg-games', '📖', 'Game dan item RPG', 1, 8, '2026-05-16 04:33:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `link` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES
(1, 4, 'Selamat Datang! 🎉', 'Akun kamu berhasil dibuat. Selamat berbelanja!', 'success', 1, '', '2026-05-16 12:56:47'),
(2, 4, 'Saldo Ditambah 💰', 'Admin menambahkan saldo Rp 50.000 ke akun kamu.', 'success', 0, '', '2026-05-20 14:39:36'),
(3, 5, 'Selamat Datang! 🎉', 'Akun kamu berhasil dibuat. Selamat berbelanja!', 'success', 1, '', '2026-05-20 14:40:54'),
(4, 5, 'Order Dibuat! 🎉', 'Order #GS2026052042C6B2 berhasil dibuat. Silakan scan QRIS untuk membayar.', 'info', 1, 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=1', '2026-05-20 14:41:40'),
(5, 5, 'Saldo Ditambah 💰', 'Admin menambahkan saldo Rp 99.999.999.999.999 ke akun kamu.', 'success', 1, '', '2026-05-20 14:45:44'),
(6, 5, 'Order Dibuat! 🎉', 'Order #GS2026052037D8A4 berhasil dibuat. Silakan scan QRIS untuk membayar.', 'info', 1, 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=2', '2026-05-20 14:46:27'),
(7, 5, 'Pembayaran Berhasil! ✅', 'Order #GS2026052037D8A4 telah dibayar. Seller sedang memproses.', 'success', 1, 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=2', '2026-05-20 14:46:31'),
(8, 1, 'Ada Order Baru! 🎉', 'Order #GS2026052037D8A4 telah dibayar. Segera proses!', 'info', 1, 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=2', '2026-05-20 14:46:31'),
(9, 1, 'Pesanan Selesai! 💰', 'Pesanan #GS2026052037D8A4 telah selesai. Dana telah ditransfer.', 'success', 1, '', '2026-05-20 14:46:40'),
(10, 5, 'Order Dibuat! 🎉', 'Order #GS20260520B80977 berhasil dibuat. Silakan scan QRIS untuk membayar.', 'info', 1, 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=3', '2026-05-20 14:49:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(15,2) NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `platform_fee` decimal(15,2) DEFAULT 0.00,
  `status` enum('pending','paid','processing','completed','cancelled','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('unpaid','paid','failed','refunded') DEFAULT 'unpaid',
  `qris_code` text DEFAULT NULL,
  `qris_expired_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `buyer_id`, `seller_id`, `product_id`, `quantity`, `price`, `total_price`, `platform_fee`, `status`, `payment_method`, `payment_status`, `qris_code`, `qris_expired_at`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 'GS2026052042C6B2', 5, 2, 7, 1, 120000.00, 123000.00, 3000.00, 'pending', 'QRIS', 'unpaid', 'eyJtZXJjaGFudF9pZCI6IkdBTUVTVE9SRTAwMSIsIm1lcmNoYW50X25hbWUiOiJHQU1FU1RPUkUuSUQiLCJhbW91bnQiOjEyMzAwMCwib3JkZXJfaWQiOiJHUzIwMjYwNTIwNDJDNkIyIiwidGltZXN0YW1wIjoxNzc5Mjg4MTAwLCJleHBpcmVkIjoxNzc5MjkxNzAwfQ==', '2026-05-20 22:41:40', NULL, NULL, '2026-05-20 14:41:40', '2026-05-20 14:41:40'),
(2, 'GS2026052037D8A4', 5, 1, 9, 900, 1000.00, 900025.00, 25.00, 'completed', 'QRIS', 'paid', 'eyJtZXJjaGFudF9pZCI6IkdBTUVTVE9SRTAwMSIsIm1lcmNoYW50X25hbWUiOiJHQU1FU1RPUkUuSUQiLCJhbW91bnQiOjkwMDAyNSwib3JkZXJfaWQiOiJHUzIwMjYwNTIwMzdEOEE0IiwidGltZXN0YW1wIjoxNzc5Mjg4Mzg3LCJleHBpcmVkIjoxNzc5MjkxOTg3fQ==', '2026-05-20 22:46:27', NULL, '2026-05-20 21:46:40', '2026-05-20 14:46:27', '2026-05-20 14:46:40'),
(3, 'GS20260520B80977', 5, 2, 3, 1, 12000.00, 12300.00, 300.00, 'pending', 'QRIS', 'unpaid', 'eyJtZXJjaGFudF9pZCI6IkdBTUVTVE9SRTAwMSIsIm1lcmNoYW50X25hbWUiOiJHQU1FU1RPUkUuSUQiLCJhbW91bnQiOjEyMzAwLCJvcmRlcl9pZCI6IkdTMjAyNjA1MjBCODA5NzciLCJ0aW1lc3RhbXAiOjE3NzkyODg1NTUsImV4cGlyZWQiOjE3NzkyOTIxNTV9', '2026-05-20 22:49:15', NULL, NULL, '2026-05-20 14:49:15', '2026-05-20 14:49:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('pending','success','failed','expired') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `qris_string` text DEFAULT NULL,
  `qris_image_url` varchar(500) DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `status`, `transaction_id`, `qris_string`, `qris_image_url`, `expired_at`, `paid_at`, `created_at`) VALUES
(1, 1, 'QRIS', 123000.00, 'pending', NULL, 'eyJtZXJjaGFudF9pZCI6IkdBTUVTVE9SRTAwMSIsIm1lcmNoYW50X25hbWUiOiJHQU1FU1RPUkUuSUQiLCJhbW91bnQiOjEyMzAwMCwib3JkZXJfaWQiOiJHUzIwMjYwNTIwNDJDNkIyIiwidGltZXN0YW1wIjoxNzc5Mjg4MTAwLCJleHBpcmVkIjoxNzc5MjkxNzAwfQ==', 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=GAMESTORE%7CORDER%3AGS2026052042C6B2%7CAMT%3A123000%7CTS%3A1779288100&format=png', '2026-05-20 22:41:40', NULL, '2026-05-20 14:41:40'),
(2, 2, 'QRIS', 900025.00, 'success', NULL, 'eyJtZXJjaGFudF9pZCI6IkdBTUVTVE9SRTAwMSIsIm1lcmNoYW50X25hbWUiOiJHQU1FU1RPUkUuSUQiLCJhbW91bnQiOjkwMDAyNSwib3JkZXJfaWQiOiJHUzIwMjYwNTIwMzdEOEE0IiwidGltZXN0YW1wIjoxNzc5Mjg4Mzg3LCJleHBpcmVkIjoxNzc5MjkxOTg3fQ==', 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=GAMESTORE%7CORDER%3AGS2026052037D8A4%7CAMT%3A900025%7CTS%3A1779288387&format=png', '2026-05-20 22:46:27', '2026-05-20 21:46:31', '2026-05-20 14:46:27'),
(3, 3, 'QRIS', 12300.00, 'pending', NULL, 'eyJtZXJjaGFudF9pZCI6IkdBTUVTVE9SRTAwMSIsIm1lcmNoYW50X25hbWUiOiJHQU1FU1RPUkUuSUQiLCJhbW91bnQiOjEyMzAwLCJvcmRlcl9pZCI6IkdTMjAyNjA1MjBCODA5NzciLCJ0aW1lc3RhbXAiOjE3NzkyODg1NTUsImV4cGlyZWQiOjE3NzkyOTIxNTV9', 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=GAMESTORE%7CORDER%3AGS20260520B80977%7CAMT%3A12300%7CTS%3A1779288555&format=png', '2026-05-20 22:49:15', NULL, '2026-05-20 14:49:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `original_price` decimal(15,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  `game_name` varchar(100) DEFAULT NULL,
  `platform` varchar(50) DEFAULT NULL,
  `delivery_type` enum('instant','manual') DEFAULT 'instant',
  `status` enum('active','inactive','sold') DEFAULT 'active',
  `is_featured` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `sold_count` int(11) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `category_id`, `seller_id`, `name`, `slug`, `description`, `price`, `original_price`, `stock`, `image`, `game_name`, `platform`, `delivery_type`, `status`, `is_featured`, `views`, `sold_count`, `rating`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'Mobile Legends 86 Diamond', 'ml-86-diamond', 'Top up 86 Diamond Mobile Legends langsung ke akun kamu', 15000.00, 18000.00, 100, NULL, 'Mobile Legends', 'Android/iOS', 'instant', 'active', 1, 0, 0, 0.00, '2026-05-16 04:33:51', '2026-05-16 04:33:51'),
(2, 1, 2, 'Mobile Legends 172 Diamond', 'ml-172-diamond', 'Top up 172 Diamond Mobile Legends langsung ke akun kamu', 29000.00, 35000.00, 100, NULL, 'Mobile Legends', 'Android/iOS', 'instant', 'active', 1, 2, 0, 0.00, '2026-05-16 04:33:51', '2026-05-20 17:45:24'),
(3, 1, 2, 'Free Fire 70 Diamond', 'ff-70-diamond', 'Top up 70 Diamond Free Fire instan', 12000.00, 15000.00, 100, NULL, 'Free Fire', 'Android/iOS', 'instant', 'active', 1, 2, 0, 0.00, '2026-05-16 04:33:51', '2026-05-20 14:49:08'),
(4, 2, 2, 'Steam Wallet 50.000', 'steam-wallet-50k', 'Steam Wallet Code senilai Rp 50.000', 55000.00, 60000.00, 50, NULL, 'Steam', 'PC', 'instant', 'active', 1, 0, 0, 0.00, '2026-05-16 04:33:51', '2026-05-16 04:33:51'),
(5, 2, 2, 'Minecraft Java Edition', 'minecraft-java', 'Minecraft Java Edition Original Steam Key', 450000.00, 500000.00, 10, NULL, 'Minecraft', 'PC', 'manual', 'active', 0, 0, 0, 0.00, '2026-05-16 04:33:51', '2026-05-16 04:33:51'),
(6, 5, 2, 'Robux 400', 'robux-400', '400 Robux untuk akun Roblox kamu', 65000.00, 75000.00, 200, NULL, 'Roblox', 'All Platform', 'instant', 'active', 1, 10, 0, 0.00, '2026-05-16 04:33:51', '2026-05-16 12:55:01'),
(7, 5, 2, 'Robux 800', 'robux-800', '800 Robux untuk akun Roblox kamu', 120000.00, 145000.00, 200, NULL, 'Roblox', 'All Platform', 'instant', 'active', 0, 2, 0, 0.00, '2026-05-16 04:33:51', '2026-05-20 14:41:24'),
(8, 6, 2, 'Sword God Grade Mobile Legends', 'sword-god-ml', 'Sword God Grade item langka ML', 250000.00, 300000.00, 5, NULL, 'Mobile Legends', 'Android/iOS', 'manual', 'active', 0, 3, 0, 0.00, '2026-05-16 04:33:51', '2026-05-20 14:48:12'),
(9, 6, 1, 'chest', 'chest-1779288276', '0', 1000.00, 2000.00, 99, NULL, '0', '', 'instant', 'active', 1, 5, 900, 0.00, '2026-05-20 14:44:36', '2026-05-20 14:47:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default.png',
  `balance` decimal(15,2) DEFAULT 0.00,
  `role` enum('user','admin') DEFAULT 'user',
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `avatar`, `balance`, `role`, `is_verified`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@gamestore.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', NULL, 'default.png', 900000.00, 'admin', 1, '2026-05-16 04:33:28', '2026-05-20 14:46:40'),
(2, 'demo_seller', 'seller@gamestore.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo Seller', NULL, 'default.png', 0.00, 'user', 1, '2026-05-16 04:33:28', '2026-05-16 04:33:28'),
(3, 'demo_buyer', 'buyer@gamestore.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo Buyer', NULL, 'default.png', 0.00, 'user', 1, '2026-05-16 04:33:28', '2026-05-16 04:33:28'),
(4, 'kosrah', 'kosrah@gmail.com', '$2y$10$l4g2paWDKc6jjepsRsfkx.GaXqigPumxlgAqSY8EcbwQb.TL7vHza', 'bolotopup', NULL, 'default.png', 50000.00, 'user', 1, '2026-05-16 12:56:47', '2026-05-20 14:39:36'),
(5, 'Jujujuju', 'jujuju@gmail.com', '$2y$10$6FbU6xQPZZF93C94p/vMIO6xVG7YsNOmiDGRtXjSJKyCIJz0CBitS', 'sambelkecap', NULL, 'default.png', 9999999999999.99, 'user', 1, '2026-05-20 14:40:54', '2026-05-20 14:45:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`,`is_read`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_orders_buyer` (`buyer_id`),
  ADD KEY `idx_orders_status` (`status`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_status` (`status`);

--
-- Indeks untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Ketidakleluasaan untuk tabel `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
