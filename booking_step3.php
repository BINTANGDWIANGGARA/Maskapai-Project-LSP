<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

// Simpan data kursi ke session
$_SESSION['temp_booking']['seats'] = $_POST['seat'];

$flight_id = $_SESSION['temp_booking']['flight_id'];
$passengers_count = $_SESSION['temp_booking']['passengers_count'];

$stmt = $pdo->prepare("SELECT * FROM flights WHERE id = ?");
$stmt->execute([$flight_id]);
$f = $stmt->fetch();

$pageTitle = 'Layanan Tambahan — Tikecting.yuu';
?>
<!doctype html>
<html lang="id">
<head>
  <?php include 'includes/head.php'; ?>
</head>
<body class="tyy-bg-base">
  <?php include 'includes/navbar.php'; ?>

  <main class="tyy-container tyy-page-content">
    <!-- Progress Bar -->
    <div class="tyy-booking-steps">
      <div class="step">1. Data Penumpang</div>
      <div class="step">2. Pilih Kursi</div>
      <div class="step active">3. Add-ons</div>
      <div class="step">4. Pembayaran</div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 400px; gap: 2.5rem; align-items: start;">
      <!-- Add-ons Content -->
      <form action="booking_checkout.php" method="POST" id="addonsForm">
        <h3 style="margin-bottom:1.5rem;">Layanan Tambahan (Add-ons)</h3>
        
        <!-- Baggage Extra -->
        <div class="tyy-card" style="margin-bottom:2rem; padding:2rem;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <div style="display:flex; align-items:center; gap:12px;">
              <span style="font-size:2rem;">🧳</span>
              <div>
                <h4 style="margin:0;">Bagasi Ekstra</h4>
                <p style="margin:0; font-size:0.85rem; color:var(--text-secondary);">Tambahkan kuota bagasi untuk kenyamanan perjalanan Anda.</p>
              </div>
            </div>
          </div>
          <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:1rem;">
            <label class="addon-option">
              <input type="radio" name="baggage" value="0" checked>
              <div class="box">
                <span class="weight">0 kg</span>
                <span class="price">Gratis</span>
              </div>
            </label>
            <label class="addon-option">
              <input type="radio" name="baggage" value="150000">
              <div class="box">
                <span class="weight">+5 kg</span>
                <span class="price">Rp 150.000</span>
              </div>
            </label>
            <label class="addon-option">
              <input type="radio" name="baggage" value="250000">
              <div class="box">
                <span class="weight">+10 kg</span>
                <span class="price">Rp 250.000</span>
              </div>
            </label>
          </div>
        </div>

        <!-- Meals -->
        <div class="tyy-card" style="margin-bottom:2rem; padding:2rem;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <div style="display:flex; align-items:center; gap:12px;">
              <span style="font-size:2rem;">🍱</span>
              <div>
                <h4 style="margin:0;">Makanan Pesawat</h4>
                <p style="margin:0; font-size:0.85rem; color:var(--text-secondary);">Nikmati hidangan lezat selama penerbangan Anda.</p>
              </div>
            </div>
          </div>
          <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:1rem;">
            <label class="addon-option">
              <input type="checkbox" name="addons[meal][]" value="50000">
              <div class="box horizontal">
                <span class="name">Nasi Lemak Ayam</span>
                <span class="price">Rp 50.000</span>
              </div>
            </label>
            <label class="addon-option">
              <input type="checkbox" name="addons[meal][]" value="45000">
              <div class="box horizontal">
                <span class="name">Pasta Carbonara</span>
                <span class="price">Rp 45.000</span>
              </div>
            </label>
          </div>
        </div>

        <!-- Insurance -->
        <div class="tyy-card" style="margin-bottom:2rem; padding:2rem;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <div style="display:flex; align-items:center; gap:12px;">
              <span style="font-size:2rem;">🛡️</span>
              <div>
                <h4 style="margin:0;">Asuransi Perjalanan</h4>
                <p style="margin:0; font-size:0.85rem; color:var(--text-secondary);">Proteksi penuh untuk perjalanan Anda.</p>
              </div>
            </div>
          </div>
          <label class="addon-option full-width">
            <input type="checkbox" name="addons[insurance]" value="35000">
            <div class="box horizontal">
              <div style="flex:1;">
                <span class="name">SafeTravel Plus</span>
                <span style="font-size:0.75rem; color:var(--text-muted); display:block; margin-top:4px;">Proteksi pembatalan, bagasi hilang, dan asuransi kesehatan.</span>
              </div>
              <span class="price">Rp 35.000 /pax</span>
            </div>
          </label>
        </div>

        <!-- Fast Track -->
        <div class="tyy-card" style="margin-bottom:2rem; padding:2rem;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <div style="display:flex; align-items:center; gap:12px;">
              <span style="font-size:2rem;">⚡</span>
              <div>
                <h4 style="margin:0;">Fast Track Bandara</h4>
                <p style="margin:0; font-size:0.85rem; color:var(--text-secondary);">Layanan VIP check-in dan imigrasi cepat.</p>
              </div>
            </div>
          </div>
          <label class="addon-option full-width">
            <input type="checkbox" name="addons[fast_track]" value="100000">
            <div class="box horizontal">
              <span class="name">Priority Boarding & Fast Track</span>
              <span class="price">Rp 100.000</span>
            </div>
          </label>
        </div>

        <div style="text-align:right; margin-bottom:4rem;">
          <button type="submit" class="tyy-btn tyy-btn-primary tyy-btn-lg">Lanjut ke Checkout →</button>
        </div>
      </form>

      <!-- Selection Sidebar -->
      <aside class="tyy-selection-sidebar">
        <div class="tyy-card" style="padding:1.75rem; position:sticky; top:100px;">
          <h4 style="margin-bottom:1.5rem;">Ringkasan Biaya</h4>
          
          <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:1.5rem; padding-bottom:1.5rem; border-bottom:1px solid var(--border-subtle);">
            <div style="display:flex; justify-content:space-between;">
              <span style="font-size:0.9rem; color:var(--text-secondary);">Tiket Dasar (x<?=$passengers_count?>)</span>
              <span style="font-weight:700;"><?=format_rupiah($f['price'] * $passengers_count)?></span>
            </div>
            <div id="summary-baggage" style="display:flex; justify-content:space-between; display:none;">
              <span style="font-size:0.9rem; color:var(--text-secondary);">Bagasi Tambahan</span>
              <span style="font-weight:700;" class="amount">Rp 0</span>
            </div>
            <div id="summary-addons" style="display:flex; justify-content:space-between; display:none;">
              <span style="font-size:0.9rem; color:var(--text-secondary);">Layanan Tambahan</span>
              <span style="font-weight:700;" class="amount">Rp 0</span>
            </div>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-weight:700; font-size:1.1rem;">Total Akhir</span>
            <span style="font-weight:800; font-size:1.3rem; color:var(--text-primary);" id="grand-total"><?=format_rupiah($f['price'] * $passengers_count)?></span>
          </div>
        </div>
      </aside>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>

  <script>
    const basePrice = <?= $f['price'] * $passengers_count ?>;
    const addonsForm = document.getElementById('addonsForm');
    const grandTotalEl = document.getElementById('grand-total');

    function updateSummary() {
      let total = basePrice;
      
      // Baggage
      const baggageVal = parseInt(addonsForm.querySelector('input[name="baggage"]:checked').value);
      if (baggageVal > 0) {
        document.getElementById('summary-baggage').style.display = 'flex';
        document.getElementById('summary-baggage').querySelector('.amount').innerText = 'Rp ' + baggageVal.toLocaleString('id-ID');
        total += baggageVal;
      } else {
        document.getElementById('summary-baggage').style.display = 'none';
      }

      // Other addons
      let addonsTotal = 0;
      addonsForm.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => {
        addonsTotal += parseInt(cb.value);
      });

      if (addonsTotal > 0) {
        document.getElementById('summary-addons').style.display = 'flex';
        document.getElementById('summary-addons').querySelector('.amount').innerText = 'Rp ' + addonsTotal.toLocaleString('id-ID');
        total += addonsTotal;
      } else {
        document.getElementById('summary-addons').style.display = 'none';
      }

      grandTotalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    addonsForm.addEventListener('change', updateSummary);
  </script>

  <style>
    .tyy-booking-steps {
      display: flex;
      justify-content: space-between;
      margin-bottom: 3rem;
      padding: 0 1rem;
      position: relative;
    }
    .tyy-booking-steps::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      height: 2px;
      background: var(--border-subtle);
      z-index: 1;
    }
    .tyy-booking-steps .step {
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
      padding: 10px 24px;
      border-radius: var(--radius-full);
      font-size: 0.9rem;
      font-weight: 700;
      color: var(--text-muted);
      position: relative;
      z-index: 2;
    }
    .tyy-booking-steps .step.active {
      background: var(--gradient-silver);
      color: var(--text-inverse);
      border: none;
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }

    .addon-option {
      cursor: pointer;
    }
    .addon-option input {
      display: none;
    }
    .addon-option .box {
      border: 1px solid var(--border-medium);
      border-radius: var(--radius-md);
      padding: 1.25rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      transition: all 0.2s;
      background: var(--bg-surface);
    }
    .addon-option .box.horizontal {
      flex-direction: row;
      justify-content: space-between;
      width: 100%;
    }
    .addon-option input:checked + .box {
      background: rgba(200,200,200,0.08);
      border-color: var(--gray-400);
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .addon-option .weight, .addon-option .name {
      font-weight: 700;
      color: var(--text-primary);
    }
    .addon-option .price {
      font-size: 0.85rem;
      color: var(--text-secondary);
      font-weight: 600;
    }
    .full-width {
      display: block;
      width: 100%;
      margin-bottom: 1rem;
    }
  </style>
</body>
</html>
