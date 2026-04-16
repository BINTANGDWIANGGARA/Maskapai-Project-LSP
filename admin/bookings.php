<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if(!is_admin()) redirect('../login.php');

// Fetch all bookings with status filter
$status = $_GET['status'] ?? '';
$query = "
    SELECT b.*, u.name as customer_name, f.flight_code, f.origin_code, f.dest_code, p.method as payment_method, p.status as payment_status
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN flights f ON b.flight_id = f.id
    JOIN payments p ON b.id = p.booking_id
    WHERE 1=1
";
$params = [];
if ($status) {
    $query .= " AND b.status = ?";
    $params[] = $status;
}
$query .= " ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$pageTitle = 'Daftar Booking — Tikecting.yuu';
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
          <div class="tyy-section-label">📋 Booking</div>
          <h2 class="tyy-section-title">Semua Pesanan</h2>
        </div>
        <div style="display:flex; gap:1rem;">
            <select class="tyy-input" onchange="window.location.href='?status='+this.value">
                <option value="">Semua Status</option>
                <option value="waiting_payment" <?=$status=='waiting_payment'?'selected':''?>>Menunggu Bayar</option>
                <option value="pending_verification" <?=$status=='pending_verification'?'selected':''?>>Perlu Verifikasi</option>
                <option value="paid" <?=$status=='paid'?'selected':''?>>Sudah Bayar</option>
                <option value="ticket_issued" <?=$status=='ticket_issued'?'selected':''?>>Tiket Terbit</option>
                <option value="cancelled" <?=$status=='cancelled'?'selected':''?>>Dibatalkan</option>
            </select>
            <a href="dashboard.php" class="tyy-btn tyy-btn-outline tyy-btn-sm">← Dashboard</a>
        </div>
      </div>

      <div class="tyy-card fade-in">
        <div class="tyy-table-wrap" style="overflow-x:auto;">
          <table class="tyy-table" style="width:100%; border-collapse:collapse; font-size:0.9rem;">
            <thead>
              <tr style="text-align:left; border-bottom:1px solid var(--border-subtle); color:var(--text-muted);">
                <th style="padding:1rem;">TANGGAL</th>
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
              <?php foreach($bookings as $b): ?>
                <tr style="border-bottom:1px solid var(--border-subtle);">
                  <td style="padding:1rem; font-size:0.8rem;"><?=date('d M Y H:i', strtotime($b['created_at']))?></td>
                  <td style="padding:1rem; font-weight:700; color:var(--text-primary);"><?=e($b['booking_code'])?></td>
                  <td style="padding:1rem;"><?=e($b['customer_name'])?></td>
                  <td style="padding:1rem;"><?=e($b['origin_code'])?> → <?=e($b['dest_code'])?></td>
                  <td style="padding:1rem; text-transform:uppercase; font-size:0.75rem; font-weight:600;"><?=str_replace('_', ' ', $b['payment_method'])?></td>
                  <td style="padding:1rem; font-weight:700;"><?=format_rupiah($b['total_amount'])?></td>
                  <td style="padding:1rem;">
                    <?php
                      $statusMap = [
                        'pending_verification' => ['Verifikasi', 'background:var(--gray-400); color:var(--text-inverse);'],
                        'waiting_payment' => ['Menunggu', 'border:1px solid var(--border-medium); color:var(--text-muted);'],
                        'paid' => ['Lunas', 'background:var(--gray-700); color:var(--text-primary);'],
                        'ticket_issued' => ['Terbit', 'background:var(--gradient-silver); color:var(--text-inverse);'],
                        'cancelled' => ['Batal', 'color:var(--text-disabled);'],
                      ];
                      $st = $statusMap[$b['status']] ?? [$b['status'], ''];
                    ?>
                    <span class="tyy-badge" style="padding:4px 10px; border-radius:4px; font-size:0.7rem; font-weight:800; text-transform:uppercase; <?=$st[1]?>"><?=$st[0]?></span>
                  </td>
                  <td style="padding:1rem; text-align:right;">
                    <a href="booking_detail.php?id=<?=$b['id']?>" class="tyy-btn tyy-btn-ghost tyy-btn-sm">Detail</a>
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
