<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if(!is_admin()) redirect('../login.php');

// Stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$totalFlights = $pdo->query("SELECT COUNT(*) FROM flights")->fetchColumn();
$totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'")->fetchColumn();
$pendingVerification = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='pending_verification'")->fetchColumn();

// Recent bookings
$recent = $pdo->query('
    SELECT b.*, u.name as customer_name, f.flight_code, f.origin_code, f.dest_code, p.method as payment_method, p.status as payment_status
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN flights f ON b.flight_id = f.id
    JOIN payments p ON b.id = p.booking_id
    ORDER BY b.created_at DESC LIMIT 5
')->fetchAll();

$pageTitle = 'Admin Dashboard — Tikecting.yuu';
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
      <!-- Dashboard Header -->
      <div style="margin-bottom:2.5rem;" class="fade-in">
        <div class="tyy-section-label">📊 Panel Kendali Utama</div>
        <h2 class="tyy-section-title">Admin Dashboard</h2>
        <p style="color:var(--text-secondary);margin-top:6px;">Selamat datang kembali, <?=e($_SESSION['user']['name'])?>. Berikut adalah ringkasan performa sistem hari ini.</p>
      </div>

      <!-- Stats Grid -->
      <div class="tyy-stats-grid fade-in fade-in-delay-1" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem; margin-bottom:2.5rem;">
        <div class="tyy-stat-card" style="background:var(--bg-card); padding:1.5rem; border-radius:var(--radius-lg); border:1px solid var(--border-subtle); text-align:center;">
          <div style="font-size:2rem; font-weight:800; color:var(--text-primary); margin-bottom:4px;"><?=number_format($totalUsers)?></div>
          <div style="font-size:0.85rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Customer</div>
        </div>
        <div class="tyy-stat-card" style="background:var(--bg-card); padding:1.5rem; border-radius:var(--radius-lg); border:1px solid var(--border-subtle); text-align:center;">
          <div style="font-size:2rem; font-weight:800; color:var(--text-primary); margin-bottom:4px;"><?=number_format($totalFlights)?></div>
          <div style="font-size:0.85rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Penerbangan</div>
        </div>
        <div class="tyy-stat-card" style="background:var(--bg-card); padding:1.5rem; border-radius:var(--radius-lg); border:1px solid var(--border-subtle); text-align:center;">
          <div style="font-size:2rem; font-weight:800; color:var(--text-primary); margin-bottom:4px;"><?=number_format($totalBookings)?></div>
          <div style="font-size:0.85rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Total Pesanan</div>
        </div>
        <div class="tyy-stat-card" style="background:var(--bg-card); padding:1.5rem; border-radius:var(--radius-lg); border:1px solid var(--border-subtle); text-align:center;">
          <div style="font-size:1.5rem; font-weight:800; color:var(--text-primary); margin-bottom:4px;"><?=format_rupiah($totalRevenue)?></div>
          <div style="font-size:0.85rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Pendapatan</div>
        </div>
        <div class="tyy-stat-card" style="background:var(--bg-card); padding:1.5rem; border-radius:var(--radius-lg); border:2px solid <?=$pendingVerification > 0 ? 'var(--gray-400)' : 'var(--border-subtle)'?>; text-align:center;">
          <div style="font-size:2rem; font-weight:800; color:var(--text-primary); margin-bottom:4px;"><?=$pendingVerification?></div>
          <div style="font-size:0.85rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Perlu Verifikasi</div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="tyy-card fade-in fade-in-delay-2" style="margin-bottom:2.5rem; padding:2rem;">
        <h4 style="margin-bottom:1.5rem; display:flex; align-items:center; gap:10px;"><span>⚡</span> Kelola Cepat</h4>
        <div style="display:flex; flex-wrap:wrap; gap:12px;">
          <a href="tickets.php" class="tyy-btn tyy-btn-outline tyy-btn-sm">✈ Kelola Penerbangan</a>
          <a href="confirm_transactions.php" class="tyy-btn tyy-btn-primary tyy-btn-sm">
            ✅ Verifikasi Pembayaran
            <?php if($pendingVerification > 0): ?>
              <span style="background:rgba(0,0,0,0.3); padding:2px 8px; border-radius:99px; font-size:0.7rem; margin-left:6px;"><?=$pendingVerification?></span>
            <?php endif; ?>
          </a>
          <a href="bookings.php" class="tyy-btn tyy-btn-outline tyy-btn-sm">📋 Daftar Booking</a>
          <a href="#" class="tyy-btn tyy-btn-outline tyy-btn-sm">📊 Laporan Penjualan</a>
        </div>
      </div>

      <!-- Recent Activity Table -->
      <div class="tyy-card fade-in fade-in-delay-3" style="padding:2rem;">
        <div class="tyy-flex-between" style="margin-bottom:1.5rem;">
          <h4 style="margin:0; display:flex; align-items:center; gap:10px;"><span>🕐</span> Pesanan Terbaru</h4>
          <a href="bookings.php" class="tyy-btn tyy-btn-ghost tyy-btn-sm">Lihat Semua →</a>
        </div>

        <div class="tyy-table-wrap" style="overflow-x:auto;">
          <table class="tyy-table" style="width:100%; border-collapse:collapse; font-size:0.9rem;">
            <thead>
              <tr style="text-align:left; border-bottom:1px solid var(--border-subtle); color:var(--text-muted);">
                <th style="padding:1rem;">KODE</th>
                <th style="padding:1rem;">CUSTOMER</th>
                <th style="padding:1rem;">RUTE</th>
                <th style="padding:1rem;">METODE</th>
                <th style="padding:1rem;">TOTAL</th>
                <th style="padding:1rem;">STATUS</th>
                <th style="padding:1rem; text-align:right;">AKSI</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($recent as $r): ?>
                <tr style="border-bottom:1px solid var(--border-subtle); transition:background 0.2s;">
                  <td style="padding:1rem; font-weight:700; color:var(--text-primary);"><?=e($r['booking_code'])?></td>
                  <td style="padding:1rem;"><?=e($r['customer_name'])?></td>
                  <td style="padding:1rem;"><?=e($r['origin_code'])?> → <?=e($r['dest_code'])?></td>
                  <td style="padding:1rem; text-transform:uppercase; font-size:0.75rem; font-weight:600;"><?=str_replace('_', ' ', $r['payment_method'])?></td>
                  <td style="padding:1rem; font-weight:700;"><?=format_rupiah($r['total_amount'])?></td>
                  <td style="padding:1rem;">
                    <?php
                      $statusMap = [
                        'pending_verification' => ['Verifikasi', 'background:var(--gray-400); color:var(--text-inverse);'],
                        'waiting_payment' => ['Menunggu', 'border:1px solid var(--border-medium); color:var(--text-muted);'],
                        'paid' => ['Lunas', 'background:var(--gray-700); color:var(--text-primary);'],
                        'ticket_issued' => ['Terbit', 'background:var(--gradient-silver); color:var(--text-inverse);'],
                        'cancelled' => ['Batal', 'color:var(--text-disabled);'],
                      ];
                      $st = $statusMap[$r['status']] ?? [$r['status'], ''];
                    ?>
                    <span class="tyy-badge" style="padding:4px 10px; border-radius:4px; font-size:0.7rem; font-weight:800; text-transform:uppercase; <?=$st[1]?>"><?=$st[0]?></span>
                  </td>
                  <td style="padding:1rem; text-align:right;">
                    <a href="booking_detail.php?id=<?=$r['id']?>" class="tyy-btn tyy-btn-ghost tyy-btn-sm">Detail</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
