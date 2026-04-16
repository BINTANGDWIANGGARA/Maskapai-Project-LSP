<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (!is_logged_in() || !isset($_SESSION['temp_booking'])) {
    redirect('index.php');
}

$temp = $_SESSION['temp_booking'];
$user_id = $_SESSION['user']['id'];
$payment_method = $_POST['payment_method'] ?? 'transfer';

try {
    $pdo->beginTransaction();

    // 1. Create Booking
    $booking_code = 'TY' . strtoupper(substr(uniqid(), -6));
    $stmt = $pdo->prepare("INSERT INTO bookings (booking_code, user_id, flight_id, total_amount, status) VALUES (?, ?, ?, ?, ?)");
    $status = ($payment_method == 'transfer') ? 'waiting_payment' : 'waiting_payment'; // Initial status for both
    $stmt->execute([$booking_code, $user_id, $temp['flight_id'], $temp['grand_total'], $status]);
    $booking_id = $pdo->lastInsertId();

    // 2. Create Passengers & Link Seats
    foreach ($temp['passengers_data'] as $i => $p) {
        $seat_num = $temp['seats'][$i];
        
        $stmt = $pdo->prepare("INSERT INTO passengers (booking_id, full_name, nik_passport, birth_date, gender, seat_number) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$booking_id, $p['name'], $p['id_number'], $p['birth_date'], $p['gender'], $seat_num]);
        
        // Mark seat as unavailable
        $stmt = $pdo->prepare("INSERT INTO seats (flight_id, seat_number, is_available) VALUES (?, ?, 0) ON DUPLICATE KEY UPDATE is_available = 0");
        $stmt->execute([$temp['flight_id'], $seat_num]);
    }

    // 3. Create Add-ons
    // Baggage
    if ($temp['baggage_price'] > 0) {
        $stmt = $pdo->prepare("INSERT INTO add_ons (booking_id, type, price, description) VALUES (?, 'baggage', ?, 'Extra Baggage')");
        $stmt->execute([$booking_id, $temp['baggage_price']]);
    }
    // Other addons
    foreach ($temp['addons_data'] as $type => $val) {
        if (is_array($val)) {
            foreach($val as $v) {
                $stmt = $pdo->prepare("INSERT INTO add_ons (booking_id, type, price, description) VALUES (?, ?, ?, ?)");
                $stmt->execute([$booking_id, $type, intval($v), 'Extra Service']);
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO add_ons (booking_id, type, price, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$booking_id, $type, intval($val), 'Extra Service']);
        }
    }

    // 4. Create Payment
    $stmt = $pdo->prepare("INSERT INTO payments (booking_id, amount, method, status) VALUES (?, ?, ?, 'waiting')");
    $stmt->execute([$booking_id, $temp['grand_total'], $payment_method]);
    $payment_id = $pdo->lastInsertId();

    $pdo->commit();

    // Clear temp session
    unset($_SESSION['temp_booking']);

    // Redirect based on payment method
    if ($payment_method == 'transfer') {
        redirect('payment_transfer.php?booking_id=' . $booking_id);
    } else {
        redirect('payment_gateway.php?booking_id=' . $booking_id . '&method=' . $payment_method);
    }

} catch (Exception $e) {
    $pdo->rollBack();
    die("Booking failed: " . $e->getMessage());
}
