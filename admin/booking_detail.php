<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if(!is_admin()) redirect('../login.php');

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("
    SELECT b.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
           f.flight_code, f.airline_name, f.origin, f.destination, f.origin_code, f.dest_code, f.depart_date, f.depart_time,
           p.method as payment_method, p.status as payment_status, p.amount as payment_amount
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN flights f ON b.flight_id = f.id
    JOIN payments p ON b.id = p.booking_id
    WHERE b.id = ?
");
$stmt->execute([$id]);
$b = $stmt->fetch();

if (!$b) redirect('dashboard.php');

// Fetch passengers
$stmt = $pdo->prepare("SELECT * FROM passengers WHERE booking_id = ?");
$stmt->execute([$id]);
$passengers = $stmt->fetchAll();

// Fetch add-ons
$stmt = $pdo->prepare("SELECT * FROM add_ons WHERE booking_id = ?");
$stmt->execute([$id]);
$addons = $stmt->fetchAll();

$pageTitle = 'Detail Booking #' . $b['booking_code'] . ' — Tikecting.yuu';
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
      <div class="tyy-flex-between" style="margin-bottom:2.5rem;">
        <div>
          <div class="tyy-section-label">📋 Detail Booking</div>
          <h2 class="tyy-section-title">Kode: <?=e($b['booking_code'])?></h2>
        </div>
        <a href="bookings.php" class="tyy-btn tyy-btn-outline tyy-btn-sm">← Kembali ke Daftar</a>
      </div>

      <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <div>
          <!-- Flight Info -->
          <div class="tyy-card" style="padding:2rem; margin-bottom:2rem;">
            <h4 style="margin-bottom:1.5rem; display:flex; align-items:center; gap:10px;"><span>✈</span> Info Penerbangan</h4>
            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:2rem;">
              <div>
                <span style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase;">Maskapai</span>
                <div style="font-weight:700;"><?=e($b['airline_name'])?> (<?=e($b['flight_code'])?>)</div>
              </div>
              <div>
                <span style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase;">Rute</span>
                <div style="font-weight:700;"><?=e($b['origin_code'])?> → <?=e($b['dest_code'])?></div>
              </div>
              <div>
                <span style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase;">Waktu</span>
                <div style="font-weight:700;"><?=date('d M Y', strtotime($b['depart_date']))?> • <?=date('H:i', strtotime($b['depart_time']))?></div>
              </div>
            </div>
          </div>

          <!-- Passengers Info -->
          <div class="tyy-card" style="padding:2rem;">
            <h4 style="margin-bottom:1.5rem; display:flex; align-items:center; gap:10px;"><span>👥</span> Daftar Penumpang</h4>
            <div class="tyy-table-wrap">
              <table class="tyy-table" style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                <thead>
                  <tr style="text-align:left; border-bottom:1px solid var(--border-subtle); color:var(--text-muted);">
                    <th style="padding:1rem;">NAMA</th>
                    <th style="padding:1rem;">NIK / PASSPORT</th>
                    <th style="padding:1rem;">KURSI</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($passengers as $p): ?>
                    <tr style="border-bottom:1px solid var(--border-subtle);">
                      <td style="padding:1rem; font-weight:700;"><?=e($p['full_name'])?></td>
                      <td style="padding:1rem;"><?=e($p['nik_passport'])?></td>
                      <td style="padding:1rem; font-weight:800; color:var(--text-primary);">💺 <?=e($p['seat_number'])?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div>
          <!-- Customer Info -->
          <div class="tyy-card" style="padding:2rem; margin-bottom:2rem;">
            <h4 style="margin-bottom:1.5rem;">Customer</h4>
            <div style="font-size:0.9rem;">
              <div style="font-weight:700; font-size:1.1rem; margin-bottom:4px;"><?=e($b['customer_name'])?></div>
              <div style="color:var(--text-secondary); margin-bottom:4px;">📧 <?=e($b['customer_email'])?></div>
              <div style="color:var(--text-secondary);">📞 <?=e($b['customer_phone'])?></div>
            </div>
          </div>

          <!-- Payment Summary -->
          <div class="tyy-card" style="padding:2rem;">
            <h4 style="margin-bottom:1.5rem;">Pembayaran</h4>
            <div style="font-size:0.9rem; margin-bottom:1.5rem;">
              <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span style="color:var(--text-muted);">Status</span>
                <?php
                  $statusMap = [
                    'pending_verification' => ['Verifikasi', 'background:var(--gray-400); color:var(--text-inverse);'],
                    'waiting_payment' => ['Menunggu', 'border:1px solid var(--border-medium); color:var(--text-muted);'],
                    'paid' => ['Lunas', 'background:var(--gray-700); color:var(--text-primary);'],
                    'ticket_issued' => ['Terbit', 'background:var(--gradient-silver); color:var(--text-inverse);'],
                  ];
                  $st = $statusMap[$b['status']] ?? [$b['status'], ''];
                ?>
                <span class="tyy-badge" style="padding:2px 8px; border-radius:4px; font-size:0.65rem; font-weight:800; text-transform:uppercase; <?=$st[1]?>"><?=$st[0]?></span>
              </div>
              <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span style="color:var(--text-muted);">Metode</span>
                <span style="font-weight:700; text-transform:uppercase;"><?=str_replace('_', ' ', $b['payment_method'])?></span>
              </div>
              <div style="display:flex; justify-content:space-between;">
                <span style="color:var(--text-muted);">Total Tagihan</span>
                <span style="font-weight:800; font-size:1.1rem; color:var(--text-primary);"><?=format_rupiah($b['total_amount'])?></span>
              </div>
            </div>

            <?php if($b['status'] == 'pending_verification'): ?>
                <a href="confirm_transactions.php" class="tyy-btn tyy-btn-primary tyy-btn-full">Verifikasi Sekarang →</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
