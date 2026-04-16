<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process add-ons and save to session
    $_SESSION['temp_booking']['baggage_price'] = $_POST['baggage'] ?? 0;
    $_SESSION['temp_booking']['addons_data'] = $_POST['addons'] ?? [];
}

$flight_id = $_SESSION['temp_booking']['flight_id'];
$passengers_count = $_SESSION['temp_booking']['passengers_count'];
$stmt = $pdo->prepare("SELECT * FROM flights WHERE id = ?");
$stmt->execute([$flight_id]);
$f = $stmt->fetch();

// Calculate total
$base_total = $f['price'] * $passengers_count;
$baggage_total = $_SESSION['temp_booking']['baggage_price'];
$addons_total = 0;
foreach($_SESSION['temp_booking']['addons_data'] as $type => $val) {
    if (is_array($val)) {
        foreach($val as $v) $addons_total += intval($v);
    } else {
        $addons_total += intval($val);
    }
}
$grand_total = $base_total + $baggage_total + $addons_total;
$_SESSION['temp_booking']['grand_total'] = $grand_total;

$pageTitle = 'Checkout & Pembayaran — Tikecting.yuu';
?>
<!doctype html>
<html lang="id">
<head>
  <?php include 'includes/head.php'; ?>
</head>
<body class="tyy-bg-base">
  <?php include 'includes/navbar.php'; ?>

  <main class="tyy-container tyy-page-content">
    <div class="tyy-booking-steps">
      <div class="step">1. Data Penumpang</div>
      <div class="step">2. Pilih Kursi</div>
      <div class="step">3. Add-ons</div>
      <div class="step active">4. Pembayaran</div>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 2.5rem; align-items: start;">
      <div class="tyy-payment-selection">
        <h3 style="margin-bottom:1.5rem;">Pilih Metode Pembayaran</h3>
        
        <form action="process_booking.php" method="POST" id="paymentForm">
          <!-- Manual Transfer Section -->
          <div class="tyy-card" style="margin-bottom:2rem; padding:2rem;">
            <div class="tyy-section-label" style="margin-bottom:1.5rem;">🏦 Transfer Bank (Manual)</div>
            <div style="display:grid; grid-template-columns: 1fr; gap:1rem;">
              <label class="payment-method-option">
                <input type="radio" name="payment_method" value="transfer" checked>
                <div class="box horizontal">
                  <div style="display:flex; align-items:center; gap:12px;">
                    <div class="bank-logo">BCA</div>
                    <div>
                      <span class="name">Transfer Bank Manual</span>
                      <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Konfirmasi manual oleh admin (10-30 menit)</span>
                    </div>
                  </div>
                  <span class="price">Gratis Biaya Layanan</span>
                </div>
              </label>
            </div>
          </div>

          <!-- Automated Payment Section -->
          <div class="tyy-card" style="margin-bottom:2rem; padding:2rem;">
            <div class="tyy-section-label" style="margin-bottom:1.5rem;">⚡ Pembayaran Otomatis (Instan)</div>
            <div style="display:grid; grid-template-columns: 1fr; gap:1rem;">
              <label class="payment-method-option">
                <input type="radio" name="payment_method" value="qris">
                <div class="box horizontal">
                  <div style="display:flex; align-items:center; gap:12px;">
                    <div class="bank-logo">QRIS</div>
                    <div>
                      <span class="name">QRIS All Payment</span>
                      <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Otomatis terverifikasi. Dukung GoPay, OVO, Dana, dll.</span>
                    </div>
                  </div>
                  <span class="price">+Rp 2.500</span>
                </div>
              </label>
              
              <label class="payment-method-option">
                <input type="radio" name="payment_method" value="va">
                <div class="box horizontal">
                  <div style="display:flex; align-items:center; gap:12px;">
                    <div class="bank-logo">VA</div>
                    <div>
                      <span class="name">Virtual Account</span>
                      <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Mandiri, BNI, Permata, BRI. Instan 24 jam.</span>
                    </div>
                  </div>
                  <span class="price">+Rp 4.000</span>
                </div>
              </label>

              <label class="payment-method-option">
                <input type="radio" name="payment_method" value="e_wallet">
                <div class="box horizontal">
                  <div style="display:flex; align-items:center; gap:12px;">
                    <div class="bank-logo">📱</div>
                    <div>
                      <span class="name">E-Wallet (OVO / ShopeePay)</span>
                      <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Langsung diarahkan ke aplikasi.</span>
                    </div>
                  </div>
                  <span class="price">+1.5%</span>
                </div>
              </label>
            </div>
          </div>

          <div style="text-align:right; margin-bottom:4rem;">
            <button type="submit" class="tyy-btn tyy-btn-primary tyy-btn-lg" style="width:100%;">Bayar Sekarang & Konfirmasi →</button>
          </div>
        </form>
      </div>

      <!-- Final Summary -->
      <aside class="tyy-final-summary">
        <div class="tyy-card" style="padding:1.75rem; position:sticky; top:100px;">
          <h4 style="margin-bottom:1.5rem;">Detail Pemesanan</h4>
          
          <div style="margin-bottom:1.5rem;">
            <div style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px;">Penerbangan</div>
            <div style="font-weight:700; font-size:0.95rem; margin-bottom:4px;"><?=e($f['origin'])?> → <?=e($f['destination'])?></div>
            <div style="font-size:0.85rem; color:var(--text-secondary);"><?=date('d M Y', strtotime($f['depart_date']))?></div>
          </div>

          <div style="margin-bottom:1.5rem; padding-bottom:1.5rem; border-bottom:1px solid var(--border-subtle);">
            <div style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px;">Penumpang & Kursi</div>
            <?php for($i=1; $i<=$passengers_count; $i++): ?>
              <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:4px;">
                <span><?=e($_SESSION['temp_booking']['passengers_data'][$i]['name'])?></span>
                <span style="font-weight:700; color:var(--text-primary);">💺 <?=e($_SESSION['temp_booking']['seats'][$i])?></span>
              </div>
            <?php endfor; ?>
          </div>

          <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:1.5rem;">
            <div style="display:flex; justify-content:space-between; font-size:0.9rem; color:var(--text-secondary);">
              <span>Harga Tiket (x<?=$passengers_count?>)</span>
              <span><?=format_rupiah($base_total)?></span>
            </div>
            <?php if($baggage_total > 0): ?>
              <div style="display:flex; justify-content:space-between; font-size:0.9rem; color:var(--text-secondary);">
                <span>Bagasi Tambahan</span>
                <span><?=format_rupiah($baggage_total)?></span>
              </div>
            <?php endif; ?>
            <?php if($addons_total > 0): ?>
              <div style="display:flex; justify-content:space-between; font-size:0.9rem; color:var(--text-secondary);">
                <span>Layanan Tambahan</span>
                <span><?=format_rupiah($addons_total)?></span>
              </div>
            <?php endif; ?>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center; padding-top:1.5rem; border-top:1px solid var(--border-subtle); margin-top:1.5rem;">
            <span style="font-weight:700; font-size:1.1rem;">Total Akhir</span>
            <span style="font-weight:800; font-size:1.4rem; color:var(--text-primary);"><?=format_rupiah($grand_total)?></span>
          </div>
        </div>
      </aside>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>

  <style>
    .payment-method-option {
      cursor: pointer;
    }
    .payment-method-option input {
      display: none;
    }
    .payment-method-option .box {
      border: 1px solid var(--border-medium);
      border-radius: var(--radius-md);
      padding: 1.25rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: all 0.2s;
      background: var(--bg-surface);
    }
    .payment-method-option input:checked + .box {
      background: rgba(200,200,200,0.08);
      border-color: var(--gray-400);
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .bank-logo {
      width: 45px;
      height: 45px;
      background: var(--bg-card);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.8rem;
      font-weight: 800;
      color: var(--text-primary);
      border: 1px solid var(--border-subtle);
    }
    .payment-method-option .name {
      font-weight: 700;
      color: var(--text-primary);
      display: block;
    }
    .payment-method-option .price {
      font-size: 0.8rem;
      color: var(--text-secondary);
      font-weight: 600;
    }
  </style>
</body>
</html>
