<?php
// =============================================
// DATABASE CONNECTION
// File: includes/db.php
// =============================================

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die('<div style="background:#1a1a2e;color:#ff4757;padding:30px;font-family:monospace;text-align:center;">
                <h2>Koneksi Database Gagal</h2>
                <p>' . $this->conn->connect_error . '</p>
                <small>Pastikan XAMPP MySQL sudah berjalan dan database gamestore_db sudah dibuat.</small>
            </div>');
        }
        $this->conn->set_charset('utf8mb4');
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