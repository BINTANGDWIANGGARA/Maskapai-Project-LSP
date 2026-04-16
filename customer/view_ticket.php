<?php
require_once '../config.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    redirect('../login.php');
}

$booking_id = $_GET['booking_id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT b.*, f.*, p.method as payment_method, p.status as payment_status
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    JOIN payments p ON b.id = p.booking_id
    WHERE b.id = ? AND b.user_id = ? AND b.status = 'ticket_issued'
");
$stmt->execute([$booking_id, $_SESSION['user']['id']]);
$b = $stmt->fetch();

if (!$b) {
    flash_set('danger', 'E-Tiket belum terbit atau tidak ditemukan.');
    redirect('dashboard.php');
}

// Get Passengers & Tickets
$stmt = $pdo->prepare("
    SELECT p.*, t.ticket_number
    FROM passengers p
    JOIN tickets t ON p.id = t.passenger_id
    WHERE p.booking_id = ?
");
$stmt->execute([$booking_id]);
$passengers = $stmt->fetchAll();

$pageTitle = 'E-Ticket #' . e($b['booking_code']) . ' — Tikecting.yuu';
?>
<!doctype html>
<html lang="id">
<head>
  <?php include '../includes/head.php'; ?>
</head>
<body class="tyy-bg-base">
  <?php include '../includes/navbar.php'; ?>

  <main class="tyy-container tyy-page-content">
    <div class="tyy-container-md">
      <div class="tyy-flex-between" style="margin-bottom:2rem;">
        <h2 style="margin:0;">E-Tiket Penerbangan</h2>
        <button onclick="window.print()" class="tyy-btn tyy-btn-outline tyy-btn-sm">🖨️ Cetak Tiket</button>
      </div>

      <?php foreach($passengers as $p): ?>
        <div class="tyy-card e-ticket-card fade-in" style="padding:0; overflow:hidden; margin-bottom:2rem; border:2px solid var(--border-medium);">
          <div class="ticket-header" style="background:var(--gradient-silver); padding:1.5rem 2rem; display:flex; justify-content:space-between; align-items:center;">
            <div class="brand" style="color:var(--text-inverse); font-weight:800; font-size:1.2rem;">Tikecting<span>.yuu</span></div>
            <div class="type" style="color:var(--text-inverse); font-weight:700; text-transform:uppercase; letter-spacing:0.1em; font-size:0.8rem;">Boarding Pass</div>
          </div>
          
          <div class="ticket-body" style="padding:2rem; display:grid; grid-template-columns: 2fr 1fr; gap:2rem;">
            <div class="main-info">
              <div style="display:grid; grid-template-columns: 1fr 1fr; gap:2rem; margin-bottom:2rem;">
                <div>
                  <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Penumpang</div>
                  <div style="font-weight:700; font-size:1.1rem; color:var(--text-primary);"><?=e($p['full_name'])?></div>
                </div>
                <div>
                  <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Nomor Tiket</div>
                  <div style="font-weight:700; font-size:1.1rem; color:var(--text-primary);"><?=e($p['ticket_number'])?></div>
                </div>
              </div>

              <div style="display:flex; justify-content:space-between; align-items:center; background:var(--bg-surface); padding:1.5rem; border-radius:var(--radius-lg); margin-bottom:2rem;">
                <div class="from">
                  <div style="font-size:2rem; font-weight:800;"><?=e($b['origin_code'])?></div>
                  <div style="font-size:0.85rem; color:var(--text-secondary);"><?=e($b['origin'])?></div>
                </div>
                <div class="arrow" style="font-size:1.5rem; color:var(--gray-500);">✈</div>
                <div class="to" style="text-align:right;">
                  <div style="font-size:2rem; font-weight:800;"><?=e($b['dest_code'])?></div>
                  <div style="font-size:0.85rem; color:var(--text-secondary);"><?=e($b['destination'])?></div>
                </div>
              </div>

              <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:1.5rem;">
                <div>
                  <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Flight</div>
                  <div style="font-weight:700;"><?=e($b['flight_code'])?></div>
                </div>
                <div>
                  <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Class</div>
                  <div style="font-weight:700; text-transform:capitalize;"><?=e($b['class'])?></div>
                </div>
                <div>
                  <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Seat</div>
                  <div style="font-weight:800; color:var(--text-primary);"><?=e($p['seat_number'])?></div>
                </div>
                <div>
                  <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Date</div>
                  <div style="font-weight:700;"><?=date('d M Y', strtotime($b['depart_date']))?></div>
                </div>
                <div>
                  <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Boarding</div>
                  <div style="font-weight:700;"><?=date('H:i', strtotime($b['depart_time'] . ' - 45 minutes'))?></div>
                </div>
                <div>
                  <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:4px;">Gate</div>
                  <div style="font-weight:700;">G-<?=rand(1, 15)?></div>
                </div>
              </div>
            </div>

            <div class="barcode-side" style="border-left:2px dashed var(--border-medium); padding-left:2rem; display:flex; flex-direction:column; align-items:center; justify-content:center;">
              <div style="background:white; padding:10px; border-radius:8px; margin-bottom:1rem;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?=e($p['ticket_number'])?>" style="width:120px; height:120px;">
              </div>
              <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.2em; transform: rotate(90deg); margin-top:2rem; width:150px; text-align:center;">
                <?=e($b['booking_code'])?>
              </div>
            </div>
          </div>
          
          <div class="ticket-footer" style="background:var(--bg-surface); padding:1rem 2rem; border-top:1px solid var(--border-subtle); font-size:0.75rem; color:var(--text-muted);">
            Harap tiba di bandara paling lambat 2 jam sebelum keberangkatan. Tunjukkan e-tiket ini saat check-in.
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

  <?php include '../includes/footer.php'; ?>

  <style>
    @media print {
      .tyy-navbar, .tyy-footer-enhanced, button { display: none !important; }
      body { background: white !important; }
      .tyy-container-md { width: 100% !important; max-width: none !important; margin: 0 !important; }
      .tyy-card { border: 1px solid #ddd !important; box-shadow: none !important; }
      .tyy-bg-base { background: white !important; }
    }
  </style>
</body>
</html>
