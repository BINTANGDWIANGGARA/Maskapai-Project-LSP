<?php
// config.php
session_start();

$host = 'localhost';
$db   = 'tikecting_yuu';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// ---------- Auto-migration: tambah kolom biodata jika belum ada ----------
// Cek langsung apakah kolom 'nik' ada (tanpa session cache)
$_colCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'nik'")->fetch();
if(!$_colCheck){
    $addCols = [
        "ADD COLUMN `nik` varchar(20) DEFAULT NULL AFTER `phone`",
        "ADD COLUMN `gender` enum('L','P') DEFAULT NULL AFTER `nik`",
        "ADD COLUMN `birth_date` date DEFAULT NULL AFTER `gender`",
        "ADD COLUMN `address` text DEFAULT NULL AFTER `birth_date`",
        "ADD COLUMN `photo` varchar(255) DEFAULT NULL AFTER `address`",
    ];
    foreach($addCols as $col){
        try { $pdo->exec("ALTER TABLE `users` $col"); } catch(PDOException $e) {}
    }
}
unset($_colCheck);

function get_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Hitung kedalaman script dari root app (config.php ada di root app)
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    // Hapus nama file, lalu hitung berapa level di atas config.php
    $scriptDir  = dirname($scriptName); // e.g. /Maskapai/admin
    // Cari app root: directory tempat config.php berada
    $appDir = str_replace('\\', '/', __DIR__); // e.g. C:/xampp/htdocs/Maskapai
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $basePath = '/' . trim(str_replace($docRoot, '', $appDir), '/');
    return rtrim($protocol . '://' . $host . $basePath, '/');
}

function get_user_initials() {
    if (!isset($_SESSION['user']['name'])) return '?';
    $words = explode(' ', trim($_SESSION['user']['name']));
    $initials = '';
    foreach (array_slice($words, 0, 2) as $word) {
        $initials .= strtoupper(mb_substr($word, 0, 1));
    }
    return $initials;
}

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function is_logged_in(){
    return isset($_SESSION['user']);
}

function is_admin(){
    return is_logged_in() && $_SESSION['user']['role'] === 'admin';
}

function is_customer(){
    return is_logged_in() && $_SESSION['user']['role'] === 'customer';
}

function format_rupiah($amount){
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function flash_set($type, $message){
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(){
    if(isset($_SESSION['_flash'])){
        $flash = $_SESSION['_flash'];
        unset($_SESSION['_flash']);
        return $flash;
    }
    return null;
}

function redirect($url){ header('Location: '.$url); exit; }
