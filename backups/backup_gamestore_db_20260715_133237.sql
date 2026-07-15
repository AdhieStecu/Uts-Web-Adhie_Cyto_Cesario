-- Database Backup for BoloTopup.ID
-- Generated on 2026-07-15 13:32:37
-- PHP Version: 8.2.12

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `banners`;
CREATE TABLE `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `is_active`, `sort_order`, `created_at`) VALUES ('1', 'Top Up', 'topup', '💎', 'Top Up Diamond, Robux, dan lainnya', '1', '1', '2026-05-16 11:33:41');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `is_active`, `sort_order`, `created_at`) VALUES ('2', 'Game Key', 'game-key', '🎮', 'Steam Key, Epic Games, dan lainnya', '1', '2', '2026-05-16 11:33:41');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `is_active`, `sort_order`, `created_at`) VALUES ('3', 'Akun', 'akun', '👤', 'Jual beli akun game', '1', '3', '2026-05-16 11:33:41');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `is_active`, `sort_order`, `created_at`) VALUES ('4', 'Voucher', 'voucher', '🎫', 'Voucher game dan marketplace', '1', '4', '2026-05-16 11:33:41');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `is_active`, `sort_order`, `created_at`) VALUES ('5', 'Roblox Games', 'roblox', '🟥', 'Item dan currency Roblox', '1', '5', '2026-05-16 11:33:41');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `is_active`, `sort_order`, `created_at`) VALUES ('6', 'Item', 'item', '⚔️', 'Item dalam game', '1', '6', '2026-05-16 11:33:41');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `is_active`, `sort_order`, `created_at`) VALUES ('7', 'Koin Game', 'koin-game', '🪙', 'Koin dan currency game', '1', '7', '2026-05-16 11:33:41');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `is_active`, `sort_order`, `created_at`) VALUES ('8', 'RPG Games', 'rpg-games', '📖', 'Game dan item RPG', '1', '8', '2026-05-16 11:33:41');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `is_active`, `sort_order`, `created_at`) VALUES ('9', 'Random Steam Key', 'random-steam-key', '🗝️', 'Dapatkan key Steam game random', '1', '9', '2026-07-02 14:56:33');
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `is_active`, `sort_order`, `created_at`) VALUES ('10', 'Simulation Games', 'simulation-games', '🚜', 'Game simulasi seru', '1', '10', '2026-07-02 14:56:33');

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `link` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user` (`user_id`,`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('1', '4', 'Selamat Datang! 🎉', 'Akun kamu berhasil dibuat. Selamat berbelanja!', 'success', '1', '', '2026-05-16 19:56:47');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('2', '4', 'Saldo Ditambah 💰', 'Admin menambahkan saldo Rp 50.000 ke akun kamu.', 'success', '0', '', '2026-05-20 21:39:36');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('3', '5', 'Selamat Datang! 🎉', 'Akun kamu berhasil dibuat. Selamat berbelanja!', 'success', '1', '', '2026-05-20 21:40:54');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('4', '5', 'Order Dibuat! 🎉', 'Order #GS2026052042C6B2 berhasil dibuat. Silakan scan QRIS untuk membayar.', 'info', '1', 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=1', '2026-05-20 21:41:40');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('5', '5', 'Saldo Ditambah 💰', 'Admin menambahkan saldo Rp 99.999.999.999.999 ke akun kamu.', 'success', '1', '', '2026-05-20 21:45:44');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('6', '5', 'Order Dibuat! 🎉', 'Order #GS2026052037D8A4 berhasil dibuat. Silakan scan QRIS untuk membayar.', 'info', '1', 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=2', '2026-05-20 21:46:27');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('7', '5', 'Pembayaran Berhasil! ✅', 'Order #GS2026052037D8A4 telah dibayar. Seller sedang memproses.', 'success', '1', 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=2', '2026-05-20 21:46:31');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('8', '1', 'Ada Order Baru! 🎉', 'Order #GS2026052037D8A4 telah dibayar. Segera proses!', 'info', '1', 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=2', '2026-05-20 21:46:31');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('9', '1', 'Pesanan Selesai! 💰', 'Pesanan #GS2026052037D8A4 telah selesai. Dana telah ditransfer.', 'success', '1', '', '2026-05-20 21:46:40');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('10', '5', 'Order Dibuat! 🎉', 'Order #GS20260520B80977 berhasil dibuat. Silakan scan QRIS untuk membayar.', 'info', '1', 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=3', '2026-05-20 21:49:15');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('11', '1', 'Order Dibuat! 🎉', 'Order #GS2026052115D58E berhasil dibuat. Silakan scan QRIS untuk membayar.', 'info', '1', 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=4', '2026-05-21 14:32:49');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('12', '1', 'Pesanan Selesai ✅', 'Pesanan #GS2026052115D58E telah selesai.', 'success', '1', '', '2026-06-18 13:44:27');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('13', '2', 'Dana Diterima 💰', 'Dana Rp 65.000 telah masuk ke saldo kamu.', 'success', '0', '', '2026-06-18 13:44:27');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('14', '1', 'Pembayaran Berhasil! ✅', 'Order #GS2026052115D58E telah dibayar. Seller sedang memproses.', 'success', '1', 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=4', '2026-06-18 13:46:18');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('15', '2', 'Ada Order Baru! 🎉', 'Order #GS2026052115D58E telah dibayar. Segera proses!', 'info', '0', 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=4', '2026-06-18 13:46:18');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('16', '1', 'Pesanan Selesai ✅', 'Pesanan #GS2026052115D58E telah selesai.', 'success', '1', '', '2026-06-18 13:46:39');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('17', '2', 'Dana Diterima 💰', 'Dana Rp 65.000 telah masuk ke saldo kamu.', 'success', '0', '', '2026-06-18 13:46:39');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('18', '5', 'Pesanan Selesai ✅', 'Pesanan #GS20260520B80977 telah selesai.', 'success', '0', '', '2026-06-18 13:46:50');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('19', '2', 'Dana Diterima 💰', 'Dana Rp 12.000 telah masuk ke saldo kamu.', 'success', '0', '', '2026-06-18 13:46:50');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('20', '5', 'Pesanan Selesai ✅', 'Pesanan #GS2026052042C6B2 telah selesai.', 'success', '0', '', '2026-06-18 13:46:55');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('21', '2', 'Dana Diterima 💰', 'Dana Rp 120.000 telah masuk ke saldo kamu.', 'success', '0', '', '2026-06-18 13:46:55');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('22', '5', 'Pesanan Selesai ✅', 'Pesanan #GS2026052042C6B2 telah selesai.', 'success', '0', '', '2026-06-18 13:47:15');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('23', '2', 'Dana Diterima 💰', 'Dana Rp 120.000 telah masuk ke saldo kamu.', 'success', '0', '', '2026-06-18 13:47:15');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('24', '1', 'Pesanan Selesai ✅', 'Pesanan #GS2026052115D58E telah selesai.', 'success', '1', '', '2026-06-18 14:02:39');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('25', '2', 'Dana Diterima 💰', 'Dana Rp 65.000 telah masuk ke saldo kamu.', 'success', '0', '', '2026-06-18 14:02:39');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('26', '6', 'Selamat Datang! 🎉', 'Akun kamu berhasil dibuat menggunakan Google SSO. Selamat berbelanja!', 'success', '1', '', '2026-07-02 13:55:05');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('27', '7', 'Selamat Datang! 🎉', 'Akun kamu berhasil dibuat menggunakan Google SSO. Selamat berbelanja!', 'success', '0', '', '2026-07-08 13:30:17');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('28', '8', 'Selamat Datang! 🎉', 'Akun kamu berhasil dibuat menggunakan Google SSO. Selamat berbelanja!', 'success', '1', '', '2026-07-08 13:33:03');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('29', '8', 'Order Dibuat! 🎉', 'Order #GS20260708034DB4 berhasil dibuat. Silakan scan QRIS untuk membayar.', 'info', '1', 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=5', '2026-07-08 13:57:52');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('30', '8', 'Pembayaran Berhasil! ✅', 'Order #GS20260708034DB4 telah dibayar. Seller sedang memproses.', 'success', '1', 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=5', '2026-07-08 13:58:13');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('31', '2', 'Ada Order Baru! 🎉', 'Order #GS20260708034DB4 telah dibayar. Segera proses!', 'info', '0', 'http://localhost/Uts-Web-Adhie_Cyto_Cesario/pages/order-detail.php?id=5', '2026-07-08 13:58:13');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('32', '1', 'Top Up Berhasil! 💰', 'Saldo sebesar Rp 500.000 telah ditambahkan ke akun Anda.', 'success', '1', '', '2026-07-08 14:22:31');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('33', '8', 'Top Up Berhasil! 💰', 'Saldo sebesar Rp 25.000 telah ditambahkan ke akun Anda.', 'success', '1', '', '2026-07-08 14:24:41');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('34', '9', 'Selamat Datang! 🎉', 'Akun kamu berhasil dibuat menggunakan Google SSO. Selamat berbelanja!', 'success', '1', '', '2026-07-08 14:50:45');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('35', '9', 'Top Up Berhasil! 💰', 'Saldo sebesar Rp 25.000 telah ditambahkan ke akun Anda.', 'success', '1', '', '2026-07-08 14:55:23');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('36', '9', 'Tiket Bantuan Dibuat ✉️', 'Tiket aduan tentang \'saya ngantuk\' telah terkirim. Rincian telah dikirim ke email Anda.', 'info', '1', '', '2026-07-08 14:57:04');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('37', '8', 'Pesanan Selesai ✅', 'Pesanan #GS20260708034DB4 telah selesai.', 'success', '0', '', '2026-07-15 13:00:59');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES ('38', '2', 'Dana Diterima 💰', 'Dana Rp 29.000 telah masuk ke saldo kamu.', 'success', '0', '', '2026-07-15 13:00:59');

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `seller_id` (`seller_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_orders_buyer` (`buyer_id`),
  KEY `idx_orders_status` (`status`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `orders` (`id`, `order_number`, `buyer_id`, `seller_id`, `product_id`, `quantity`, `price`, `total_price`, `platform_fee`, `status`, `payment_method`, `payment_status`, `qris_code`, `qris_expired_at`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES ('1', 'GS2026052042C6B2', '5', '2', '7', '1', '120000.00', '123000.00', '3000.00', 'completed', 'QRIS', 'unpaid', 'eyJtZXJjaGFudF9pZCI6IkdBTUVTVE9SRTAwMSIsIm1lcmNoYW50X25hbWUiOiJHQU1FU1RPUkUuSUQiLCJhbW91bnQiOjEyMzAwMCwib3JkZXJfaWQiOiJHUzIwMjYwNTIwNDJDNkIyIiwidGltZXN0YW1wIjoxNzc5Mjg4MTAwLCJleHBpcmVkIjoxNzc5MjkxNzAwfQ==', '2026-05-20 22:41:40', NULL, NULL, '2026-05-20 21:41:40', '2026-06-18 13:46:55');
INSERT INTO `orders` (`id`, `order_number`, `buyer_id`, `seller_id`, `product_id`, `quantity`, `price`, `total_price`, `platform_fee`, `status`, `payment_method`, `payment_status`, `qris_code`, `qris_expired_at`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES ('2', 'GS2026052037D8A4', '5', '1', '9', '900', '1000.00', '900025.00', '25.00', 'completed', 'QRIS', 'paid', 'eyJtZXJjaGFudF9pZCI6IkdBTUVTVE9SRTAwMSIsIm1lcmNoYW50X25hbWUiOiJHQU1FU1RPUkUuSUQiLCJhbW91bnQiOjkwMDAyNSwib3JkZXJfaWQiOiJHUzIwMjYwNTIwMzdEOEE0IiwidGltZXN0YW1wIjoxNzc5Mjg4Mzg3LCJleHBpcmVkIjoxNzc5MjkxOTg3fQ==', '2026-05-20 22:46:27', NULL, '2026-05-20 21:46:40', '2026-05-20 21:46:27', '2026-05-20 21:46:40');
INSERT INTO `orders` (`id`, `order_number`, `buyer_id`, `seller_id`, `product_id`, `quantity`, `price`, `total_price`, `platform_fee`, `status`, `payment_method`, `payment_status`, `qris_code`, `qris_expired_at`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES ('3', 'GS20260520B80977', '5', '2', '3', '1', '12000.00', '12300.00', '300.00', 'completed', 'QRIS', 'unpaid', 'eyJtZXJjaGFudF9pZCI6IkdBTUVTVE9SRTAwMSIsIm1lcmNoYW50X25hbWUiOiJHQU1FU1RPUkUuSUQiLCJhbW91bnQiOjEyMzAwLCJvcmRlcl9pZCI6IkdTMjAyNjA1MjBCODA5NzciLCJ0aW1lc3RhbXAiOjE3NzkyODg1NTUsImV4cGlyZWQiOjE3NzkyOTIxNTV9', '2026-05-20 22:49:15', NULL, NULL, '2026-05-20 21:49:15', '2026-06-18 13:46:50');
INSERT INTO `orders` (`id`, `order_number`, `buyer_id`, `seller_id`, `product_id`, `quantity`, `price`, `total_price`, `platform_fee`, `status`, `payment_method`, `payment_status`, `qris_code`, `qris_expired_at`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES ('4', 'GS2026052115D58E', '1', '2', '6', '1', '65000.00', '66625.00', '1625.00', 'completed', 'QRIS', 'paid', 'eyJtZXJjaGFudF9pZCI6IkdBTUVTVE9SRTAwMSIsIm1lcmNoYW50X25hbWUiOiJHQU1FU1RPUkUuSUQiLCJhbW91bnQiOjY2NjI1LCJvcmRlcl9pZCI6IkdTMjAyNjA1MjExNUQ1OEUiLCJ0aW1lc3RhbXAiOjE3NzkzNDg3NjksImV4cGlyZWQiOjE3NzkzNTIzNjl9', '2026-05-21 15:32:49', NULL, NULL, '2026-05-21 14:32:49', '2026-06-18 14:02:39');
INSERT INTO `orders` (`id`, `order_number`, `buyer_id`, `seller_id`, `product_id`, `quantity`, `price`, `total_price`, `platform_fee`, `status`, `payment_method`, `payment_status`, `qris_code`, `qris_expired_at`, `notes`, `completed_at`, `created_at`, `updated_at`) VALUES ('5', 'GS20260708034DB4', '8', '2', '2', '1', '29000.00', '29725.00', '725.00', 'completed', 'QRIS', 'paid', 'eyJtZXJjaGFudF9pZCI6IkJPTE9UT1BVUDAwMSIsIm1lcmNoYW50X25hbWUiOiJCT0xPVE9QVVAuSUQiLCJhbW91bnQiOjI5NzI1LCJvcmRlcl9pZCI6IkdTMjAyNjA3MDgwMzREQjQiLCJ0aW1lc3RhbXAiOjE3ODM0OTM4NzIsImV4cGlyZWQiOjE3ODM0OTc0NzJ9', '2026-07-08 14:57:52', NULL, NULL, '2026-07-08 13:57:52', '2026-07-15 13:00:59');

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_password_resets_token` (`token`),
  KEY `idx_password_resets_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('pending','success','failed','expired') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `qris_string` text DEFAULT NULL,
  `qris_image_url` varchar(500) DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `status`, `transaction_id`, `qris_string`, `qris_image_url`, `expired_at`, `paid_at`, `created_at`) VALUES ('1', '1', 'QRIS', '123000.00', 'pending', NULL, 'eyJtZXJjaGFudF9pZCI6IkdBTUVTVE9SRTAwMSIsIm1lcmNoYW50X25hbWUiOiJHQU1FU1RPUkUuSUQiLCJhbW91bnQiOjEyMzAwMCwib3JkZXJfaWQiOiJHUzIwMjYwNTIwNDJDNkIyIiwidGltZXN0YW1wIjoxNzc5Mjg4MTAwLCJleHBpcmVkIjoxNzc5MjkxNzAwfQ==', 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=GAMESTORE%7CORDER%3AGS2026052042C6B2%7CAMT%3A123000%7CTS%3A1779288100&format=png', '2026-05-20 22:41:40', NULL, '2026-05-20 21:41:40');
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `status`, `transaction_id`, `qris_string`, `qris_image_url`, `expired_at`, `paid_at`, `created_at`) VALUES ('2', '2', 'QRIS', '900025.00', 'success', NULL, 'eyJtZXJjaGFudF9pZCI6IkdBTUVTVE9SRTAwMSIsIm1lcmNoYW50X25hbWUiOiJHQU1FU1RPUkUuSUQiLCJhbW91bnQiOjkwMDAyNSwib3JkZXJfaWQiOiJHUzIwMjYwNTIwMzdEOEE0IiwidGltZXN0YW1wIjoxNzc5Mjg4Mzg3LCJleHBpcmVkIjoxNzc5MjkxOTg3fQ==', 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=GAMESTORE%7CORDER%3AGS2026052037D8A4%7CAMT%3A900025%7CTS%3A1779288387&format=png', '2026-05-20 22:46:27', '2026-05-20 21:46:31', '2026-05-20 21:46:27');
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `status`, `transaction_id`, `qris_string`, `qris_image_url`, `expired_at`, `paid_at`, `created_at`) VALUES ('3', '3', 'QRIS', '12300.00', 'pending', NULL, 'eyJtZXJjaGFudF9pZCI6IkdBTUVTVE9SRTAwMSIsIm1lcmNoYW50X25hbWUiOiJHQU1FU1RPUkUuSUQiLCJhbW91bnQiOjEyMzAwLCJvcmRlcl9pZCI6IkdTMjAyNjA1MjBCODA5NzciLCJ0aW1lc3RhbXAiOjE3NzkyODg1NTUsImV4cGlyZWQiOjE3NzkyOTIxNTV9', 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=GAMESTORE%7CORDER%3AGS20260520B80977%7CAMT%3A12300%7CTS%3A1779288555&format=png', '2026-05-20 22:49:15', NULL, '2026-05-20 21:49:15');
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `status`, `transaction_id`, `qris_string`, `qris_image_url`, `expired_at`, `paid_at`, `created_at`) VALUES ('4', '4', 'QRIS', '66625.00', 'success', NULL, 'eyJtZXJjaGFudF9pZCI6IkdBTUVTVE9SRTAwMSIsIm1lcmNoYW50X25hbWUiOiJHQU1FU1RPUkUuSUQiLCJhbW91bnQiOjY2NjI1LCJvcmRlcl9pZCI6IkdTMjAyNjA1MjExNUQ1OEUiLCJ0aW1lc3RhbXAiOjE3NzkzNDg3NjksImV4cGlyZWQiOjE3NzkzNTIzNjl9', 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=GAMESTORE%7CORDER%3AGS2026052115D58E%7CAMT%3A66625%7CTS%3A1779348769&format=png', '2026-05-21 15:32:49', '2026-06-18 13:46:18', '2026-05-21 14:32:49');
INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `status`, `transaction_id`, `qris_string`, `qris_image_url`, `expired_at`, `paid_at`, `created_at`) VALUES ('5', '5', 'QRIS', '29725.00', 'success', NULL, 'eyJtZXJjaGFudF9pZCI6IkJPTE9UT1BVUDAwMSIsIm1lcmNoYW50X25hbWUiOiJCT0xPVE9QVVAuSUQiLCJhbW91bnQiOjI5NzI1LCJvcmRlcl9pZCI6IkdTMjAyNjA3MDgwMzREQjQiLCJ0aW1lc3RhbXAiOjE3ODM0OTM4NzIsImV4cGlyZWQiOjE3ODM0OTc0NzJ9', 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=BOLOTOPUP%7CORDER%3AGS20260708034DB4%7CAMT%3A29725%7CTS%3A1783493872&format=png', '2026-07-08 14:57:52', '2026-07-08 13:58:03', '2026-07-08 13:57:52');

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `seller_id` (`seller_id`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_status` (`status`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `category_id`, `seller_id`, `name`, `slug`, `description`, `price`, `original_price`, `stock`, `image`, `game_name`, `platform`, `delivery_type`, `status`, `is_featured`, `views`, `sold_count`, `rating`, `created_at`, `updated_at`) VALUES ('2', '1', '2', 'Mobile Legends 172 Diamond', 'ml-172-diamond', 'Top up 172 Diamond Mobile Legends langsung ke akun kamu', '29000.00', '35000.00', '99', NULL, 'Mobile Legends', 'Android/iOS', 'instant', 'active', '1', '5', '1', '0.00', '2026-05-16 11:33:51', '2026-07-08 13:58:03');
INSERT INTO `products` (`id`, `category_id`, `seller_id`, `name`, `slug`, `description`, `price`, `original_price`, `stock`, `image`, `game_name`, `platform`, `delivery_type`, `status`, `is_featured`, `views`, `sold_count`, `rating`, `created_at`, `updated_at`) VALUES ('3', '1', '2', 'Free Fire 70 Diamond', 'ff-70-diamond', 'Top up 70 Diamond Free Fire instan', '12000.00', '15000.00', '100', NULL, 'Free Fire', 'Android/iOS', 'instant', 'active', '1', '2', '0', '0.00', '2026-05-16 11:33:51', '2026-05-20 21:49:08');
INSERT INTO `products` (`id`, `category_id`, `seller_id`, `name`, `slug`, `description`, `price`, `original_price`, `stock`, `image`, `game_name`, `platform`, `delivery_type`, `status`, `is_featured`, `views`, `sold_count`, `rating`, `created_at`, `updated_at`) VALUES ('4', '2', '2', 'Steam Wallet 50.000', 'steam-wallet-50k', 'Steam Wallet Code senilai Rp 50.000', '55000.00', '60000.00', '50', NULL, 'Steam', 'PC', 'instant', 'active', '1', '0', '0', '0.00', '2026-05-16 11:33:51', '2026-05-16 11:33:51');
INSERT INTO `products` (`id`, `category_id`, `seller_id`, `name`, `slug`, `description`, `price`, `original_price`, `stock`, `image`, `game_name`, `platform`, `delivery_type`, `status`, `is_featured`, `views`, `sold_count`, `rating`, `created_at`, `updated_at`) VALUES ('5', '2', '2', 'Minecraft Java Edition', 'minecraft-java', 'Minecraft Java Edition Original Steam Key', '450000.00', '500000.00', '10', NULL, 'Minecraft', 'PC', 'manual', 'active', '0', '0', '0', '0.00', '2026-05-16 11:33:51', '2026-05-16 11:33:51');
INSERT INTO `products` (`id`, `category_id`, `seller_id`, `name`, `slug`, `description`, `price`, `original_price`, `stock`, `image`, `game_name`, `platform`, `delivery_type`, `status`, `is_featured`, `views`, `sold_count`, `rating`, `created_at`, `updated_at`) VALUES ('6', '5', '2', 'Robux 400', 'robux-400', '400 Robux untuk akun Roblox kamu', '65000.00', '75000.00', '199', NULL, 'Roblox', 'All Platform', 'instant', 'active', '1', '12', '1', '0.00', '2026-05-16 11:33:51', '2026-06-18 13:46:18');
INSERT INTO `products` (`id`, `category_id`, `seller_id`, `name`, `slug`, `description`, `price`, `original_price`, `stock`, `image`, `game_name`, `platform`, `delivery_type`, `status`, `is_featured`, `views`, `sold_count`, `rating`, `created_at`, `updated_at`) VALUES ('7', '5', '2', 'Robux 800', 'robux-800', '800 Robux untuk akun Roblox kamu', '120000.00', '145000.00', '200', NULL, 'Roblox', 'All Platform', 'instant', 'active', '0', '2', '0', '0.00', '2026-05-16 11:33:51', '2026-05-20 21:41:24');
INSERT INTO `products` (`id`, `category_id`, `seller_id`, `name`, `slug`, `description`, `price`, `original_price`, `stock`, `image`, `game_name`, `platform`, `delivery_type`, `status`, `is_featured`, `views`, `sold_count`, `rating`, `created_at`, `updated_at`) VALUES ('8', '6', '2', 'Sword God Grade Mobile Legends', 'sword-god-ml', 'Sword God Grade item langka ML', '250000.00', '300000.00', '5', NULL, 'Mobile Legends', 'Android/iOS', 'manual', 'active', '0', '3', '0', '0.00', '2026-05-16 11:33:51', '2026-05-20 21:48:12');
INSERT INTO `products` (`id`, `category_id`, `seller_id`, `name`, `slug`, `description`, `price`, `original_price`, `stock`, `image`, `game_name`, `platform`, `delivery_type`, `status`, `is_featured`, `views`, `sold_count`, `rating`, `created_at`, `updated_at`) VALUES ('9', '6', '1', 'chest', 'chest-1779288276', '0', '1000.00', '2000.00', '99', NULL, '0', '', 'instant', 'active', '1', '7', '900', '0.00', '2026-05-20 21:44:36', '2026-05-21 14:22:59');
INSERT INTO `products` (`id`, `category_id`, `seller_id`, `name`, `slug`, `description`, `price`, `original_price`, `stock`, `image`, `game_name`, `platform`, `delivery_type`, `status`, `is_featured`, `views`, `sold_count`, `rating`, `created_at`, `updated_at`) VALUES ('17', '6', '1', 'Dragon Breath', 'dragon-breath-1784095601', 'item GAG2', '7500.00', '10000.00', '999', 'product_1784095601_6a572371b1c80.jpeg', 'Roblox', '', 'instant', 'active', '1', '0', '0', '0.00', '2026-07-15 13:06:41', '2026-07-15 13:06:41');

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `buyer_id` (`buyer_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `google_id` varchar(255) DEFAULT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `google_id` (`google_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `google_id`, `username`, `email`, `password`, `full_name`, `phone`, `avatar`, `balance`, `role`, `is_verified`, `created_at`, `updated_at`) VALUES ('1', NULL, 'admin', 'admin@gamestore.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', NULL, 'default.png', '1400000.00', 'admin', '1', '2026-05-16 11:33:28', '2026-07-08 14:22:31');
INSERT INTO `users` (`id`, `google_id`, `username`, `email`, `password`, `full_name`, `phone`, `avatar`, `balance`, `role`, `is_verified`, `created_at`, `updated_at`) VALUES ('2', NULL, 'demo_seller', 'seller@gamestore.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo Seller', NULL, 'default.png', '476000.00', 'user', '1', '2026-05-16 11:33:28', '2026-07-15 13:00:59');
INSERT INTO `users` (`id`, `google_id`, `username`, `email`, `password`, `full_name`, `phone`, `avatar`, `balance`, `role`, `is_verified`, `created_at`, `updated_at`) VALUES ('3', NULL, 'demo_buyer', 'buyer@gamestore.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo Buyer', NULL, 'default.png', '0.00', 'user', '1', '2026-05-16 11:33:28', '2026-05-16 11:33:28');
INSERT INTO `users` (`id`, `google_id`, `username`, `email`, `password`, `full_name`, `phone`, `avatar`, `balance`, `role`, `is_verified`, `created_at`, `updated_at`) VALUES ('4', NULL, 'kosrah', 'kosrah@gmail.com', '$2y$10$l4g2paWDKc6jjepsRsfkx.GaXqigPumxlgAqSY8EcbwQb.TL7vHza', 'bolotopup', NULL, 'default.png', '50000.00', 'user', '1', '2026-05-16 19:56:47', '2026-05-20 21:39:36');
INSERT INTO `users` (`id`, `google_id`, `username`, `email`, `password`, `full_name`, `phone`, `avatar`, `balance`, `role`, `is_verified`, `created_at`, `updated_at`) VALUES ('5', NULL, 'Jujujuju', 'jujuju@gmail.com', '$2y$10$6FbU6xQPZZF93C94p/vMIO6xVG7YsNOmiDGRtXjSJKyCIJz0CBitS', 'sambelkecap', NULL, 'default.png', '9999999999999.99', 'user', '1', '2026-05-20 21:40:54', '2026-06-25 13:37:51');
INSERT INTO `users` (`id`, `google_id`, `username`, `email`, `password`, `full_name`, `phone`, `avatar`, `balance`, `role`, `is_verified`, `created_at`, `updated_at`) VALUES ('6', '118402472873768380585', 'cytoplasm1324', 'cytoplasm1324@gmail.com', '$2y$10$LOmVz/7Lphq62LXL1GMTUuLv4qTwoFvpo.JoWig3UxV/nhty0GerK', 'Cyto Plasm', '08239172947', 'avatars_1782976835_6a4611430f446.jpeg', '0.00', 'user', '1', '2026-07-02 13:55:05', '2026-07-02 14:20:35');
INSERT INTO `users` (`id`, `google_id`, `username`, `email`, `password`, `full_name`, `phone`, `avatar`, `balance`, `role`, `is_verified`, `created_at`, `updated_at`) VALUES ('7', '102366598283006767852', '1202407018', '1202407018@students.itspku.ac.id', '$2y$10$nr81HSMqfm2xWGD9Rqhq3uez1BzC/9zleFJQujqBEyRMSnZdhCRV6', 'Adhie Cyto Cesario', NULL, 'https://lh3.googleusercontent.com/a/ACg8ocKnM1AsxBihEa7vA1kI-L5IqyfhstSMAnidYFqhfi0o-Oj4Fg=s96-c', '0.00', 'user', '1', '2026-07-08 13:30:17', '2026-07-08 13:30:17');
INSERT INTO `users` (`id`, `google_id`, `username`, `email`, `password`, `full_name`, `phone`, `avatar`, `balance`, `role`, `is_verified`, `created_at`, `updated_at`) VALUES ('8', '116308624957072636075', 'bolotod1324', 'bolotod1324@gmail.com', '$2y$10$OPeeO1c0JnBRrtlopFSRSOBiwyD7tgz1icd2rHt77T.L/fUR2s7pq', 'Bolo TopUp', '0812317217241', 'avatars_1783492922_6a4df13a8947b.jpeg', '25000.00', 'user', '1', '2026-07-08 13:33:03', '2026-07-08 14:24:41');
INSERT INTO `users` (`id`, `google_id`, `username`, `email`, `password`, `full_name`, `phone`, `avatar`, `balance`, `role`, `is_verified`, `created_at`, `updated_at`) VALUES ('9', '118423652556611407638', 'cytoadhie22', 'cytoadhie22@gmail.com', '$2y$10$vEymY.3g3LCekBdIau0qL.3YoKLWe177w.5NnIpKLhS5xUJT9vvii', 'Adhie Cyto', NULL, 'https://lh3.googleusercontent.com/a/ACg8ocK_tt5Ck3r7ykKEr_2idB7q6xCU1WQ5jdt9ZboqZkcyTUHMyQ=s96-c', '25000.00', 'user', '1', '2026-07-08 14:50:45', '2026-07-08 14:55:23');

DROP TABLE IF EXISTS `visitor_logs`;
CREATE TABLE `visitor_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `visited_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_ip_date` (`ip_address`,`visited_date`),
  KEY `idx_visited_date` (`visited_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `withdrawals`;
CREATE TABLE `withdrawals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `withdrawals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
