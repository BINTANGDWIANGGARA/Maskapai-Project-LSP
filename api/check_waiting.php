<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache');

$stm = $pdo->query("SELECT COUNT(*) as c FROM bookings WHERE status='pending_verification'");
$r = $stm->fetch();

echo json_encode([
    'waiting' => intval($r['c']),
    'timestamp' => date('Y-m-d H:i:s')
]);
