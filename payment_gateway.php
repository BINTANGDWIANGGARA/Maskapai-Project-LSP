<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$booking_id = $_GET['booking_id'] ?? 0;
$method = $_GET['method'] ?? 'qris';

$stmt = $pdo->prepare("
    SELECT b.*, f.origin, f.destination, f.flight_code, p.id as payment_id, p.amount as payment_amount
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    JOIN payments p ON b.id = p.booking_id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user']['id']]);
$b = $stmt->fetch();

if (!$b) {
    redirect('index.php');
}

// Handle Auto-Approve Simulation
if (isset($_POST['simulate_payment'])) {
    // 1. Update Payment Status
    $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', payment_date = NOW() WHERE id = ?");
    $stmt->execute([$b['payment_id']]);
    
    // 2. Update Booking Status
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'paid' WHERE id = ?");
    $stmt->execute([$booking_id]);

    // 3. Auto Issue Ticket (Update status to ticket_issued)
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'ticket_issued' WHERE id = ?");
    $stmt->execute([$booking_id]);

    // 4. Generate Ticket Records
    $stmt = $pdo->prepare("SELECT * FROM passengers WHERE booking_id = ?");
    $stmt->execute([$booking_id]);
    $passengers = $stmt->fetchAll();
    
    foreach ($passengers as $p) {
        $ticket_num = 'TY' . date('Ymd') . rand(1000, 9999);
        $stmt = $pdo->prepare("INSERT INTO tickets (booking_id, passenger_id, ticket_number) VALUES (?, ?, ?)");
        $stmt->execute([$booking_id, $p['id'], $ticket_num]);
    }

    flash_set('success', 'Pembayaran berhasil dikonfirmasi secara otomatis! E-Tiket Anda telah terbit.');
    redirect('customer/dashboard.php');
}

$pageTitle = 'Pembayaran Instan — Tikecting.yuu';
?>
<!doctype html>
<html lang="id">
<head>
  <?php include 'includes/head.php'; ?>
</head>
<body class="tyy-bg-base">
  <?php include 'includes/navbar.php'; ?>

  <main class="tyy-container tyy-page-content">
    <div class="tyy-container-sm">
      <div class="tyy-card" style="padding:2.5rem; text-align:center;">
        <div class="tyy-section-label" style="margin-bottom:1rem;">⚡ Pembayaran Otomatis</div>
        <h2 style="margin-bottom:1.5rem;"><?= strtoupper(str_replace('_', ' ', $method)) ?> Payment</h2>
        
        <div style="background:var(--bg-surface); padding:1.5rem; border-radius:var(--radius-lg); border:1px solid var(--border-subtle); margin-bottom:2rem;">
          <p style="font-size:0.9rem; color:var(--text-secondary); margin-bottom:1rem;">Nominal Pembayaran:</p>
          <div style="font-size:2rem; font-weight:800; color:var(--text-primary); margin-bottom:0.5rem;"><?=format_rupiah($b['payment_amount'])?></div>
          <div style="font-size:0.8rem; color:var(--text-muted);">ID Booking: <strong><?=e($b['booking_code'])?></strong></div>
        </div>

        <?php if($method == 'qris'): ?>
          <div style="background:white; padding:1.5rem; border-radius:var(--radius-lg); width:250px; margin:0 auto 2rem auto; box-shadow:0 10px 30px rgba(0,0,0,0.4);">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=TikectingYuuPayment" alt="QRIS" style="width:100%;">
            <div style="margin-top:10px; color:black; font-weight:800; font-size:1.2rem;">QRIS</div>
            <div style="font-size:0.7rem; color:#666;">Silakan scan dengan GoPay, OVO, Dana, dll.</div>
          </div>
        <?php elseif($method == 'va'): ?>
          <div style="background:var(--bg-card); padding:1.5rem; border-radius:var(--radius-md); border:1px solid var(--border-medium); margin-bottom:2rem; text-align:left;">
            <div style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px;">Nomor Virtual Account</div>
            <div style="font-size:1.5rem; font-weight:800; color:var(--text-primary); margin-bottom:10px;">9876 5432 1098 7654</div>
            <div style="font-size:0.85rem; color:var(--text-secondary);">Bank Mandiri / BNI / BRI</div>
          </div>
        <?php else: ?>
          <div style="background:var(--bg-card); padding:2rem; border-radius:var(--radius-md); border:1px solid var(--border-medium); margin-bottom:2rem;">
            <p>Silakan selesaikan pembayaran di aplikasi E-Wallet Anda.</p>
          </div>
        <?php endif; ?>

        <form action="" method="POST">
          <button type="submit" name="simulate_payment" class="tyy-btn tyy-btn-primary tyy-btn-lg tyy-btn-full" style="margin-bottom:1rem;">Simulasikan Pembayaran Berhasil ✅</button>
          <p style="font-size:0.75rem; color:var(--text-muted);">Sistem akan secara otomatis mendeteksi pembayaran Anda dalam hitungan detik.</p>
        </form>
      </div>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>
</body>
</html>
