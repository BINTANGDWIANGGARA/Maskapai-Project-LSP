<?php
// includes/functions.php - Tikecting.yuu Helper Functions
require_once __DIR__ . '/../config.php';

function login($username, $password){
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if($user && hash('sha256', $password) === $user['password']){
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['name'],
            'role' => $user['role']
        ];
        return true;
    }
    return false;
}

function logout_user(){
    session_unset();
    session_destroy();
}

function get_ticket_count(){
    global $pdo;
    return $pdo->query('SELECT COUNT(*) FROM flights')->fetchColumn();
}

function get_order_count_by_user($user_id){
    global $pdo;
    $stm = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE user_id = ?');
    $stm->execute([$user_id]);
    return $stm->fetchColumn();
}

function get_waiting_transactions_count(){
    global $pdo;
    return $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='pending_verification'")->fetchColumn();
}

function get_city_landmark($city_string) {
    // Ekstrak nama kota sebelum tanda kurung jika ada (misal: "Jakarta (CGK)" -> "Jakarta")
    $city = trim(explode('(', $city_string)[0]);
    $city = strtolower($city);
    
    $landmarks = [
        'jakarta' => 'https://images.unsplash.com/photo-1555899434-94d1368aa7af?q=80&w=800&auto=format&fit=crop', // Monas
        'surabaya' => 'https://images.unsplash.com/photo-1583011814013-68936307374a?q=80&w=800&auto=format&fit=crop', // Sura & Baya
        'bali' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?q=80&w=800&auto=format&fit=crop', // Temple
        'bandung' => 'https://images.unsplash.com/photo-1596724570093-979f426543b3?q=80&w=800&auto=format&fit=crop', // Gedung Sate
        'yogyakarta' => 'https://images.unsplash.com/photo-1584810359583-96fc3448beaa?q=80&w=800&auto=format&fit=crop', // Borobudur/Prambanan
        'medan' => 'https://images.unsplash.com/photo-1616428782359-541571217f2e?q=80&w=800&auto=format&fit=crop', // Maimun Palace
        'makassar' => 'https://images.unsplash.com/photo-1589146522960-e837943f1f7d?q=80&w=800&auto=format&fit=crop', // Losari
    ];
    
    return isset($landmarks[$city]) ? $landmarks[$city] : 'https://images.unsplash.com/photo-1436491865332-7a61a109c0f2?q=80&w=800&auto=format&fit=crop'; // Default plane img
}
