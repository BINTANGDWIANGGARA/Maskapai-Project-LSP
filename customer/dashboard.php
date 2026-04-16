<?php
require_once '../config.php';
require_once '../includes/functions.php';

if (!is_customer()) {
    redirect('../login.php');
}

$user = $_SESSION['user'];
$userId = $user['id'];

// Get Booking Stats
$stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM bookings WHERE user_id = ? GROUP BY status");
$stmt->execute([$userId]);
$stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Recent Bookings
$query = "
    SELECT b.*, f.origin, f.destination, f.flight_code, f.depart_date, f.depart_time, f.airline_name, p.method as payment_method
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    JOIN payments p ON b.id = p.booking_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
    LIMIT 5
";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$recentBookings = $stmt->fetchAll();

$pageTitle = 'Dashboard Saya — Tikecting.yuu';
?>
<!doctype html>
<html lang="id">
<head>
  <?php include '../includes/head.php'; ?>
</head>
<body class="tyy-bg-base">
  <?php include '../includes/navbar.php'; ?>

  <main class="tyy-container tyy-page-content">
    <!-- Welcome Banner -->
    <section class="tyy-welcome-banner fade-in" style="margin-bottom:2.5rem;">
      <div>
        <h2 style="margin-bottom:0.5rem;">Selamat Datang, <?=e($user['name'])?>! 👋</h2>
        <p style="opacity:0.9;">Temukan petualangan baru dan kelola perjalanan Anda dengan mudah.</p>
      </div>
      <div class="tyy-user-avatar" style="width:70px; height:70px; font-size:1.8rem; background:var(--bg-surface); color:var(--text-primary); border:2px solid var(--border-medium);">
        <?=get_user_initials()?>
      </div>
    </section>

    <!-- Quick Stats -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap:1.5rem; margin-bottom:3rem;" class="fade-in fade-in-delay-1">
      <div class="tyy-card" style="padding:1.25rem; text-align:center;">
        <div style="font-size:1.5rem; font-weight:800;"><?= $stats['ticket_issued'] ?? 0 ?></div>
        <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Tiket Aktif</div>
      </div>
      <div class="tyy-card" style="padding:1.25rem; text-align:center;">
        <div style="font-size:1.5rem; font-weight:800;"><?= ($stats['waiting_payment'] ?? 0) + ($stats['pending_verification'] ?? 0) ?></div>
        <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Menunggu</div>
      </div>
      <div class="tyy-card" style="padding:1.25rem; text-align:center;">
        <div style="font-size:1.5rem; font-weight:800;"><?= $stats['paid'] ?? 0 ?></div>
        <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Sudah Bayar</div>
      </div>
      <div class="tyy-card" style="padding:1.25rem; text-align:center;">
        <div style="font-size:1.5rem; font-weight:800;"><?= count($recentBookings) ?></div>
        <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Total Pesanan</div>
      </div>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 2.5rem;" class="fade-in fade-in-delay-2">
      <!-- Recent Bookings -->
      <div>
        <div class="tyy-flex-between" style="margin-bottom:1.5rem;">
          <h3 style="margin:0;">Pesanan Terbaru</h3>
          <a href="my_orders.php" class="tyy-btn tyy-btn-ghost tyy-btn-sm">Lihat Semua →</a>
        </div>

        <?php if(empty($recentBookings)): ?>
          <div class="tyy-card" style="padding:3rem; text-align:center;">
            <p style="color:var(--text-muted); margin-bottom:1.5rem;">Anda belum memiliki pesanan tiket.</p>
            <a href="../index.php" class="tyy-btn tyy-btn-primary">Cari Tiket Sekarang</a>
          </div>
        <?php else: ?>
          <?php foreach($recentBookings as $b): ?>
            <div class="tyy-card" style="margin-bottom:1.25rem; padding:1.5rem; display:flex; justify-content:space-between; align-items:center;">
              <div style="display:flex; gap:1.5rem; align-items:center;">
                <div style="width:50px; height:50px; background:var(--bg-surface); border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; font-size:1.5rem;">✈</div>
                <div>
                  <div style="font-weight:800; font-size:1.1rem; color:var(--text-primary);"><?=e($b['origin'])?> → <?=e($b['destination'])?></div>
                  <div style="font-size:0.85rem; color:var(--text-secondary);"><?=e($b['airline_name'])?> • <?=e($b['flight_code'])?></div>
                  <div style="font-size:0.85rem; color:var(--text-muted); margin-top:4px;"><?=date('d M Y', strtotime($b['depart_date']))?> • <?=date('H:i', strtotime($b['depart_time']))?></div>
                </div>
              </div>
              <div style="text-align:right;">
                <div style="font-weight:800; font-size:1.1rem; margin-bottom:8px;"><?=format_rupiah($b['total_amount'])?></div>
                <?php
                  $statusMap = [
                    'waiting_payment' => ['Belum Bayar', 'background:rgba(255,209,102,0.1); color:#FFD166; border:1px solid #FFD166;'],
                    'pending_verification' => ['Verifikasi', 'background:var(--gray-700); color:var(--text-primary);'],
                    'paid' => ['Sudah Bayar', 'background:var(--gray-800); color:var(--text-secondary);'],
                    'ticket_issued' => ['Tiket Terbit', 'background:var(--gradient-silver); color:var(--text-inverse);'],
                    'cancelled' => ['Dibatalkan', 'color:var(--text-disabled);'],
                  ];
                  $st = $statusMap[$b['status']] ?? [$b['status'], ''];
                ?>
                <span class="tyy-badge" style="padding:4px 12px; border-radius:99px; font-size:0.7rem; font-weight:800; text-transform:uppercase; <?=$st[1]?>"><?=$st[0]?></span>
                <div style="margin-top:10px;">
                  <?php if($b['status'] == 'waiting_payment' && $b['payment_method'] == 'transfer'): ?>
                    <a href="../payment_transfer.php?booking_id=<?=$b['id']?>" class="tyy-btn tyy-btn-primary tyy-btn-sm">Bayar Sekarang</a>
                  <?php elseif($b['status'] == 'ticket_issued'): ?>
                    <a href="view_ticket.php?booking_id=<?=$b['id']?>" class="tyy-btn tyy-btn-outline tyy-btn-sm">E-Tiket</a>
                  <?php else: ?>
                    <a href="booking_detail.php?id=<?=$b['id']?>" class="tyy-btn tyy-btn-ghost tyy-btn-sm">Detail</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Right Sidebar: Promotions -->
      <aside>
        <h3 style="margin-bottom:1.5rem;">Promo Spesial</h3>
        <div class="tyy-card" style="padding:0; overflow:hidden; margin-bottom:1.5rem;">
          <img src="https://images.unsplash.com/photo-1436491865332-7a61a109c0f2?q=80&w=600&auto=format&fit=crop" style="width:100%; height:150px; object-fit:cover;">
          <div style="padding:1.25rem;">
            <h4 style="margin-bottom:8px;">Cashback s/d 500rb!</h4>
            <p style="font-size:0.85rem; color:var(--text-secondary); line-height:1.5;">Gunakan metode pembayaran QRIS untuk mendapatkan cashback instan.</p>
            <a href="../index.php" class="tyy-btn tyy-btn-outline tyy-btn-sm tyy-btn-full" style="margin-top:1rem;">Cek Tiket</a>
          </div>
        </div>
        
        <div class="tyy-card" style="padding:1.5rem; background:var(--gradient-dark); border-color:var(--gray-700);">
          <h4 style="margin-bottom:12px; color:var(--text-primary);">Butuh Bantuan?</h4>
          <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1.5rem;">Tim support kami siap membantu perjalanan Anda 24/7.</p>
          <a href="#" class="tyy-btn tyy-btn-ghost tyy-btn-sm tyy-btn-full" style="border:1px solid var(--border-subtle);">Hubungi Kami</a>
        </div>
      </aside>
    </div>
  </main>

  <?php include '../includes/footer.php'; ?>
</body>
</html>
