<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if(!is_admin()) redirect('../login.php');

// Handle Approval/Rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $payment_id = $_POST['payment_id'];
    $proof_id = $_POST['proof_id'];
    $action = $_POST['action'];
    $note = $_POST['admin_note'] ?? '';

    try {
        $pdo->beginTransaction();

        if ($action === 'approve') {
            // Update Proof
            $stmt = $pdo->prepare("UPDATE payment_proofs SET status = 'approved', verified_at = NOW(), admin_note = ? WHERE id = ?");
            $stmt->execute([$note, $proof_id]);

            // Update Payment
            $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', payment_date = NOW() WHERE id = ?");
            $stmt->execute([$payment_id]);

            // Update Booking & Issue Ticket
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'ticket_issued' WHERE id = ?");
            $stmt->execute([$booking_id]);

            // Generate Ticket Records
            $stmt = $pdo->prepare("SELECT * FROM passengers WHERE booking_id = ?");
            $stmt->execute([$booking_id]);
            $passengers = $stmt->fetchAll();
            
            foreach ($passengers as $p) {
                $ticket_num = 'TY' . date('Ymd') . rand(1000, 9999);
                $stmt = $pdo->prepare("INSERT INTO tickets (booking_id, passenger_id, ticket_number) VALUES (?, ?, ?)");
                $stmt->execute([$booking_id, $p['id'], $ticket_num]);
            }

            flash_set('success', 'Pembayaran berhasil disetujui dan tiket telah diterbitkan.');
        } else {
            // Reject Action
            $stmt = $pdo->prepare("UPDATE payment_proofs SET status = 'rejected', admin_note = ? WHERE id = ?");
            $stmt->execute([$note, $proof_id]);

            $stmt = $pdo->prepare("UPDATE bookings SET status = 'waiting_payment' WHERE id = ?");
            $stmt->execute([$booking_id]);

            flash_set('warning', 'Pembayaran ditolak. User perlu mengupload bukti transfer ulang.');
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        flash_set('danger', 'Gagal memproses verifikasi: ' . $e->getMessage());
    }
}

// Fetch pending proofs
$pending = $pdo->query('
    SELECT b.*, u.name as customer_name, f.flight_code, p.id as payment_id, pp.id as proof_id, pp.file_path, pp.sender_name, pp.bank_name, pp.created_at as proof_date
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN flights f ON b.flight_id = f.id
    JOIN payments p ON b.id = p.booking_id
    JOIN payment_proofs pp ON p.id = pp.payment_id
    WHERE b.status = "pending_verification" AND pp.status = "pending"
    ORDER BY pp.created_at ASC
')->fetchAll();

$pageTitle = 'Verifikasi Pembayaran — Tikecting.yuu';
?>
<!doctype html>
<html lang="id">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body class="tyy-bg-base">
  <?php include __DIR__ . '/../includes/navbar.php'; ?>

  <div class="tyy-page-content">
    <div class="tyy-container">
      <div style="margin-bottom:2.5rem;" class="fade-in">
        <div class="tyy-section-label">✅ Verifikasi</div>
        <h2 class="tyy-section-title">Konfirmasi Pembayaran Manual</h2>
        <p style="color:var(--text-secondary);margin-top:6px;">Terdapat <?=count($pending)?> pembayaran yang menunggu persetujuan Anda.</p>
      </div>

      <?php if(empty($pending)): ?>
        <div class="tyy-card fade-in" style="padding:4rem; text-align:center;">
          <div style="font-size:3rem; margin-bottom:1.5rem;">🎉</div>
          <h4>Semua Beres!</h4>
          <p style="color:var(--text-muted);">Tidak ada pembayaran yang menunggu verifikasi saat ini.</p>
          <a href="dashboard.php" class="tyy-btn tyy-btn-outline tyy-btn-sm" style="margin-top:1.5rem;">Kembali ke Dashboard</a>
        </div>
      <?php else: ?>
        <div style="display:grid; grid-template-columns: 1fr; gap:2rem;">
          <?php foreach($pending as $p): ?>
            <div class="tyy-card fade-in" style="padding:2rem;">
              <div style="display:grid; grid-template-columns: 1fr 1fr; gap:2.5rem;">
                <!-- Left: Proof Details -->
                <div>
                  <h4 style="margin-bottom:1.5rem; display:flex; align-items:center; gap:10px;"><span>📄</span> Detail Bukti Transfer</h4>
                  <div style="background:var(--bg-surface); border:1px solid var(--border-subtle); border-radius:var(--radius-lg); overflow:hidden; margin-bottom:1.5rem;">
                    <a href="<?=get_base_url()?>/<?=$p['file_path']?>" target="_blank">
                      <img src="<?=get_base_url()?>/<?=$p['file_path']?>" alt="Bukti Transfer" style="width:100%; height:300px; object-fit:contain; background:#000;">
                    </a>
                  </div>
                  <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; font-size:0.9rem;">
                    <div>
                      <span style="color:var(--text-muted); display:block; margin-bottom:4px;">Pengirim</span>
                      <span style="font-weight:700;"><?=e($p['sender_name'])?></span>
                    </div>
                    <div>
                      <span style="color:var(--text-muted); display:block; margin-bottom:4px;">Bank</span>
                      <span style="font-weight:700;"><?=e($p['bank_name'])?></span>
                    </div>
                    <div>
                      <span style="color:var(--text-muted); display:block; margin-bottom:4px;">Waktu Upload</span>
                      <span style="font-weight:700;"><?=date('d M Y H:i', strtotime($p['proof_date']))?></span>
                    </div>
                    <div>
                      <span style="color:var(--text-muted); display:block; margin-bottom:4px;">Total Tagihan</span>
                      <span style="font-weight:700; color:var(--text-primary);"><?=format_rupiah($p['total_amount'])?></span>
                    </div>
                  </div>
                </div>

                <!-- Right: Booking Info & Actions -->
                <div>
                  <h4 style="margin-bottom:1.5rem; display:flex; align-items:center; gap:10px;"><span>📋</span> Info Pesanan</h4>
                  <div style="margin-bottom:2rem; padding:1.5rem; background:rgba(255,255,255,0.02); border-radius:var(--radius-md); border:1px solid var(--border-subtle);">
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                      <span style="color:var(--text-muted);">Kode Booking</span>
                      <span style="font-weight:700;"><?=e($p['booking_code'])?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                      <span style="color:var(--text-muted);">Customer</span>
                      <span style="font-weight:700;"><?=e($p['customer_name'])?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                      <span style="color:var(--text-muted);">Penerbangan</span>
                      <span style="font-weight:700;"><?=e($p['flight_code'])?></span>
                    </div>
                  </div>

                  <form action="" method="POST">
                    <input type="hidden" name="booking_id" value="<?=$p['id']?>">
                    <input type="hidden" name="payment_id" value="<?=$p['payment_id']?>">
                    <input type="hidden" name="proof_id" value="<?=$p['proof_id']?>">
                    
                    <div class="tyy-form-group">
                      <label class="tyy-label">Catatan Admin (Opsional)</label>
                      <textarea name="admin_note" class="tyy-input" rows="3" placeholder="Alasan penolakan atau catatan tambahan..."></textarea>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-top:2rem;">
                      <button type="submit" name="action" value="reject" class="tyy-btn tyy-btn-danger tyy-btn-full" onclick="return confirm('Tolak pembayaran ini?')">❌ Tolak</button>
                      <button type="submit" name="action" value="approve" class="tyy-btn tyy-btn-primary tyy-btn-full" style="background:var(--gradient-silver); color:var(--text-inverse);">✅ Setujui & Terbitkan Tiket</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
