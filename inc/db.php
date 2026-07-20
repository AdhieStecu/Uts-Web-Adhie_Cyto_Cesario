<?php
// =============================================
// DATABASE CONNECTION
// File: inc/db.php
// =============================================

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($this->conn->connect_error) {
            die('<div style="background:#1a1a2e;color:#ff4757;padding:30px;font-family:monospace;text-align:center;">
                <h2>Koneksi Database Gagal</h2>
                <p>' . $this->conn->connect_error . '</p>
                <small>Pastikan database settings sudah benar dan server database online/aktif.</small>
            </div>');
        }
        $this->conn->set_charset('utf8mb4');
        
        // Auto initialize extra tables
        $this->conn->query("CREATE TABLE IF NOT EXISTS `password_resets` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `email` varchar(100) NOT NULL,
          `token` varchar(255) NOT NULL,
          `expires_at` datetime NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `idx_password_resets_token` (`token`),
          KEY `idx_password_resets_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->conn->query("CREATE TABLE IF NOT EXISTS `visitor_logs` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ip_address` varchar(45) NOT NULL,
          `user_agent` varchar(255) DEFAULT NULL,
          `visited_date` date NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `idx_ip_date` (`ip_address`, `visited_date`),
          KEY `idx_visited_date` (`visited_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->conn->query("CREATE TABLE IF NOT EXISTS `email_otps` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `email` varchar(100) NOT NULL,
          `otp_code` varchar(6) NOT NULL,
          `type` varchar(20) NOT NULL,
          `expires_at` datetime NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `idx_email` (`email`),
          KEY `idx_otp` (`otp_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Auto check / alter orders table for escrow columns
        $checkCol = $this->conn->query("SHOW COLUMNS FROM `orders` LIKE 'escrow_amount'");
        if ($checkCol && $checkCol->num_rows == 0) {
            $this->conn->query("ALTER TABLE `orders` 
                ADD COLUMN `escrow_amount` DECIMAL(10,2) DEFAULT 0.00,
                ADD COLUMN `escrow_release_at` DATETIME NULL,
                ADD COLUMN `escrow_status` VARCHAR(20) DEFAULT 'none'");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConn() {
        return $this->conn;
    }

    // Query - FIXED: handle null types
    public function query($sql, $types = '', ...$params) {
        if ($types === null) $types = '';
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Query Error: " . $this->conn->error . " | SQL: " . $sql);
            return false;
        }
        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute()) {
            error_log("Execute Error: " . $stmt->error);
            return false;
        }
        return $stmt;
    }

    public function fetchAll($sql, $types = '', ...$params) {
        if ($types === null) $types = '';
        $stmt = $this->query($sql, $types, ...$params);
        if (!$stmt) return [];
        $result = $stmt->get_result();
        if (!$result) return [];
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchOne($sql, $types = '', ...$params) {
        if ($types === null) $types = '';
        $stmt = $this->query($sql, $types, ...$params);
        if (!$stmt) return null;
        $result = $stmt->get_result();
        if (!$result) return null;
        return $result->fetch_assoc();
    }

    public function insert($sql, $types = '', ...$params) {
        if ($types === null) $types = '';
        $stmt = $this->query($sql, $types, ...$params);
        if (!$stmt) return false;
        return $this->conn->insert_id;
    }

    public function execute($sql, $types = '', ...$params) {
        if ($types === null) $types = '';
        $stmt = $this->query($sql, $types, ...$params);
        if (!$stmt) return false;
        return $stmt->affected_rows;
    }

    public function escape($str) {
        return $this->conn->real_escape_string($str);
    }
}

function db() {
    return Database::getInstance();
}