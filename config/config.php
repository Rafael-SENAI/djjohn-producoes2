<?php
// ========================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// ========================================

define('DB_HOST', 'localhost:3307');
define('DB_NAME', 'djjohn_eventos');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ========================================
// CONFIGURAÇÕES DO SISTEMA
// ========================================

define('BASE_URL', 'http://localhost/djjohn-producoes/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');  // ← CORRIGIDO AQUI (era só /uploads/)
define('UPLOAD_URL', BASE_URL . 'uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
define('ALLOWED_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'application/pdf']);

// ========================================
// CONFIGURAÇÕES DE SEGURANÇA
// ========================================

define('SECRET_KEY', 'sua_chave_secreta_aqui_' . md5(__DIR__));
define('SESSION_LIFETIME', 7200);

// ========================================
// TIMEZONE
// ========================================
date_default_timezone_set('America/Sao_Paulo');

// ========================================
// CONFIGURAÇÕES DE ERRO
// ========================================
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 1);

// ========================================
// FUNÇÃO DE CONEXÃO COM BANCO
// ========================================
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

// ========================================
// FUNÇÕES DE AUTENTICAÇÃO
// ========================================

function requireAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit;
    }
}

function requireAdmin() {
    requireAuth();
    if ($_SESSION['user_role'] !== 'admin') {
        die('Acesso negado. Apenas administradores.');
    }
}

// ← FUNÇÃO QUE ESTAVA FALTANDO!
function isAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role']
    ];
}

// ========================================
// FUNÇÕES AUXILIARES
// ========================================

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function formatDate($date) {
    if (!$date) return '-';
    $dt = new DateTime($date);
    return $dt->format('d/m/Y');
}

function formatDateTime($datetime) {
    if (!$datetime) return '-';
    $dt = new DateTime($datetime);
    return $dt->format('d/m/Y H:i');
}

function formatCurrency($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function uploadFile($file, $allowedTypes = null, $maxSize = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Erro no upload'];
    }
    
    $allowedTypes = $allowedTypes ?? ALLOWED_TYPES;
    $maxSize = $maxSize ?? MAX_UPLOAD_SIZE;
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Tipo de arquivo não permitido'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Arquivo muito grande'];
    }
    
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $destination = UPLOAD_DIR . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename, 'url' => UPLOAD_URL . $filename];
    }
    
    return ['success' => false, 'error' => 'Falha ao mover arquivo'];
}

function deleteFile($filename) {
    $filepath = UPLOAD_DIR . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

function generateToken($data) {
    return hash_hmac('sha256', $data, SECRET_KEY);
}

function verifyToken($data, $token) {
    return hash_equals(generateToken($data), $token);
}
?>