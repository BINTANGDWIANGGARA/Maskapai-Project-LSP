<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if(!is_customer()) redirect('../login.php');

$userId = $_SESSION['user']['id'];

$stm = $pdo->prepare('
    SELECT b.*, f.flight_code, f.origin, f.destination, f.origin_code, f.dest_code, f.depart_date, f.depart_time, p.method as payment_method
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    JOIN payments p ON b.id = p.booking_id
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
');
$stm->execute([$userId]);
$bookings = $stm->fetchAll();

$pageTitle = 'Riwayat Pesanan — Tikecting.yuu';
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
        <div class="tyy-section-label">📋 Riwayat Perjalanan</div>
        <h2 class="tyy-section-title">Pesanan Saya</h2>
        <p style="color:var(--text-secondary);margin-top:6px;">Kelola semua tiket dan riwayat pemesanan Anda di sini.</p>
      </div>

      <?php if(empty($bookings)): ?>
        <div class="tyy-card fade-in" style="padding:4rem; text-align:center;">
          <div style="font-size:3rem; margin-bottom:1.5rem;">📋</div>
          <h4>Belum Ada Pesanan</h4>
          <p style="color:var(--text-muted);">Anda belum melakukan pemesanan tiket pesawat apapun.</p>
          <a href="../index.php" class="tyy-btn tyy-btn-primary" style="margin-top:1.5rem;">Cari Tiket Sekarang</a>
        </div>
      <?php else: ?>
        <div style="display:grid; grid-template-columns: 1fr; gap:1.5rem;" class="fade-in fade-in-delay-1">
          <?php foreach($bookings as $b): ?>
            <div class="tyy-card" style="padding:1.5rem; display:flex; justify-content:space-between; align-items:center; transition:transform 0.2s;">
              <div style="display:flex; gap:2rem; align-items:center;">
                <div style="width:60px; height:60px; background:var(--bg-surface); border-radius:var(--radius-lg); display:flex; align-items:center; justify-content:center; font-size:1.8rem; border:1px solid var(--border-subtle);">✈</div>
                <div>
                  <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; text-transform:uppercase; margin-bottom:4px;"><?=e($b['booking_code'])?> • <?=date('d M Y', strtotime($b['created_at']))?></div>
                  <div style="font-weight:800; font-size:1.2rem; color:var(--text-primary); margin-bottom:4px;"><?=e($b['origin'])?> (<?=e($b['origin_code'])?>) → <?=e($b['destination'])?> (<?=e($b['dest_code'])?>)</div>
                  <div style="font-size:0.85rem; color:var(--text-secondary);">
                    <span style="font-weight:700;"><?=e($b['flight_code'])?></span> • 
                    <span><?=date('d M Y', strtotime($b['depart_date']))?></span> • 
                    <span><?=date('H:i', strtotime($b['depart_time']))?></span>
                  </div>
                </div>
              </div>
              
              <div style="text-align:right; min-width:200px;">
                <div style="font-weight:800; font-size:1.2rem; margin-bottom:8px;"><?=format_rupiah($b['total_amount'])?></div>
                <?php
                  $statusMap = [
                    'waiting_payment' => ['Menunggu Pembayaran', 'background:rgba(255,209,102,0.1); color:#FFD166; border:1px solid #FFD166;'],
                    'pending_verification' => ['Verifikasi Admin', 'background:var(--gray-700); color:var(--text-primary);'],
                    'paid' => ['Lunas', 'background:var(--gray-800); color:var(--text-secondary);'],
                    'ticket_issued' => ['Tiket Terbit', 'background:var(--gradient-silver); color:var(--text-inverse);'],
                    'cancelled' => ['Dibatalkan', 'color:var(--text-disabled);'],
                  ];
                  $st = $statusMap[$b['status']] ?? [$b['status'], ''];
                ?>
                <div style="margin-bottom:12px;">
                  <span class="tyy-badge" style="padding:6px 14px; border-radius:99px; font-size:0.75rem; font-weight:800; text-transform:uppercase; <?=$st[1]?>"><?=$st[0]?></span>
                </div>
                
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                  <?php if($b['status'] == 'waiting_payment' && $b['payment_method'] == 'transfer'): ?>
                    <a href="../payment_transfer.php?booking_id=<?=$b['id']?>" class="tyy-btn tyy-btn-primary tyy-btn-sm">Bayar Sekarang</a>
                  <?php elseif($b['status'] == 'ticket_issued'): ?>
                    <a href="view_ticket.php?booking_id=<?=$b['id']?>" class="tyy-btn tyy-btn-outline tyy-btn-sm">Lihat E-Tiket</a>
                  <?php else: ?>
                    <a href="booking_detail.php?id=<?=$b['id']?>" class="tyy-btn tyy-btn-ghost tyy-btn-sm">Detail Pesanan</a>
                  <?php endif; ?>
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
