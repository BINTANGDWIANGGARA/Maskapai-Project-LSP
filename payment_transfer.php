<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$booking_id = $_GET['booking_id'] ?? 0;
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

// Handle Proof Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['proof'])) {
    $proof = $_FILES['proof'];
    $ext = pathinfo($proof['name'], PATHINFO_EXTENSION);
    $filename = 'proof_' . time() . '_' . rand(100, 999) . '.' . $ext;
    $target = 'uploads/proofs/' . $filename;

    if (!is_dir('uploads/proofs')) mkdir('uploads/proofs', 0777, true);

    if (move_uploaded_file($proof['tmp_name'], $target)) {
        // Create Payment Proof Record
        $stmt = $pdo->prepare("INSERT INTO payment_proofs (payment_id, file_path, sender_name, bank_name, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$b['payment_id'], $target, $_POST['sender_name'], $_POST['bank_name']]);
        
        // Update Booking Status
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'pending_verification' WHERE id = ?");
        $stmt->execute([$booking_id]);

        flash_set('success', 'Bukti transfer berhasil diupload. Admin akan segera memverifikasi pembayaran Anda.');
        redirect('customer/dashboard.php');
    } else {
        flash_set('danger', 'Gagal mengupload bukti transfer.');
    }
}

$pageTitle = 'Konfirmasi Pembayaran — Tikecting.yuu';
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
      <div class="tyy-card" style="padding:2.5rem; text-align:center; margin-bottom:2rem;">
        <div class="tyy-section-label" style="margin-bottom:1rem;">Menunggu Pembayaran</div>
        <h2 style="margin-bottom:1.5rem;">Transfer Bank Manual</h2>
        
        <div style="background:var(--bg-surface); padding:1.5rem; border-radius:var(--radius-lg); border:1px solid var(--border-subtle); margin-bottom:2rem;">
          <p style="font-size:0.9rem; color:var(--text-secondary); margin-bottom:1rem;">Silakan transfer tepat sesuai nominal berikut:</p>
          <div style="font-size:2rem; font-weight:800; color:var(--text-primary); margin-bottom:0.5rem;"><?=format_rupiah($b['payment_amount'])?></div>
          <div style="font-size:0.8rem; color:var(--text-muted);">ID Booking: <strong><?=e($b['booking_code'])?></strong></div>
        </div>

        <div style="text-align:left; margin-bottom:2rem;">
          <p style="font-weight:700; font-size:1rem; margin-bottom:1rem;">Rekening Tujuan:</p>
          <div style="background:var(--bg-card); padding:1.25rem; border-radius:var(--radius-md); border:1px solid var(--border-medium); display:flex; align-items:center; gap:1.5rem;">
            <div style="width:60px; height:40px; background:white; color:blue; border-radius:4px; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:0.8rem;">BCA</div>
            <div>
              <div style="font-weight:700; font-size:1.1rem; color:var(--text-primary);">123-456-7890</div>
              <div style="font-size:0.85rem; color:var(--text-muted);">A/N PT TIKECTING YUU INDONESIA</div>
            </div>
          </div>
        </div>

        <div style="padding:1.5rem; background:rgba(255,255,255,0.03); border-radius:var(--radius-md); border-left:4px solid var(--gray-400); text-align:left; font-size:0.85rem; line-height:1.6; color:var(--text-secondary);">
          <strong>PENTING:</strong><br>
          - Transfer tepat sampai 3 digit terakhir.<br>
          - Upload bukti transfer di bawah ini setelah melakukan pembayaran.<br>
          - Admin akan melakukan verifikasi dalam waktu 10-30 menit.
        </div>
      </div>

      <div class="tyy-card" style="padding:2.5rem;">
        <h4 style="margin-bottom:1.5rem;">Upload Bukti Transfer</h4>
        <form action="" method="POST" enctype="multipart/form-data">
          <div class="tyy-form-group">
            <label class="tyy-label">Nama Pengirim (Sesuai Rekening)</label>
            <input type="text" name="sender_name" class="tyy-input" placeholder="Contoh: John Doe" required>
          </div>
          <div class="tyy-form-group">
            <label class="tyy-label">Bank Asal</label>
            <input type="text" name="bank_name" class="tyy-input" placeholder="Contoh: Mandiri / BNI" required>
          </div>
          <div class="tyy-form-group">
            <label class="tyy-label">File Bukti Transfer</label>
            <input type="file" name="proof" class="tyy-input" accept="image/*" required>
            <small style="color:var(--text-muted); margin-top:4px; display:block;">Format JPG/PNG/JPEG, Maks 2MB</small>
          </div>
          <button type="submit" class="tyy-btn tyy-btn-primary tyy-btn-lg tyy-btn-full" style="margin-top:1.5rem;">Kirim Konfirmasi →</button>
        </form>
      </div>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>
</body>
</html>
