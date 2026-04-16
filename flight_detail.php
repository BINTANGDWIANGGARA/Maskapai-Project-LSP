<?php
require_once 'config.php';
require_once 'includes/functions.php';

$id = $_GET['id'] ?? 0;
$passengers = $_GET['passengers'] ?? 1;

$stmt = $pdo->prepare("SELECT * FROM flights WHERE id = ?");
$stmt->execute([$id]);
$f = $stmt->fetch();

if (!$f) {
    redirect('index.php');
}

$pageTitle = 'Detail Penerbangan ' . e($f['flight_code']) . ' — Tikecting.yuu';
?>
<!doctype html>
<html lang="id">
<head>
  <?php include 'includes/head.php'; ?>
</head>
<body class="tyy-bg-base">
  <?php include 'includes/navbar.php'; ?>

  <main class="tyy-container tyy-page-content">
    <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 2.5rem; align-items: start;">
      <!-- Main Content -->
      <div class="tyy-flight-detail-main">
        <!-- Flight Header Card -->
        <div class="tyy-card" style="margin-bottom:2rem; padding:2rem;">
          <div class="tyy-flex-between" style="margin-bottom:2rem;">
            <div class="airline-info">
              <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                <div style="width:48px; height:48px; background:var(--bg-surface); border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; font-size:1.5rem;">✈</div>
                <div>
                  <h3 style="margin:0;"><?=e($f['airline_name'])?></h3>
                  <span style="font-size:0.85rem; color:var(--text-muted); font-weight:600;"><?=e($f['flight_code'])?> • <?=ucfirst(e($f['class']))?></span>
                </div>
              </div>
            </div>
            <div class="rating-info" style="text-align:right;">
              <div style="font-size:1.2rem; font-weight:800; color:var(--text-primary);">⭐ <?=e($f['rating'])?>/5.0</div>
              <div style="font-size:0.8rem; color:var(--text-muted);">Berdasarkan ulasan penumpang</div>
            </div>
          </div>

          <div class="tyy-flight-timeline" style="display:flex; justify-content:space-between; align-items:center; padding:1.5rem; background:rgba(255,255,255,0.02); border-radius:var(--radius-lg); border:1px solid var(--border-subtle);">
            <div class="stop">
              <div class="time" style="font-size:1.5rem; font-weight:800;"><?=date('H:i', strtotime($f['depart_time']))?></div>
              <div class="city" style="font-weight:700; color:var(--text-primary);"><?=e($f['origin'])?> (<?=e($f['origin_code'])?>)</div>
              <div class="date" style="font-size:0.85rem; color:var(--text-secondary);"><?=date('d M Y', strtotime($f['depart_date']))?></div>
            </div>
            
            <div class="duration-info" style="text-align:center; flex:1;">
              <div style="font-size:0.85rem; color:var(--text-muted); margin-bottom:8px;"><?=e($f['duration'])?></div>
              <div style="height:2px; background:var(--border-medium); position:relative; margin:0 2rem;">
                <span style="position:absolute; top:-4px; left:0; width:10px; height:10px; border-radius:50%; background:var(--gray-400);"></span>
                <span style="position:absolute; top:-4px; right:0; width:10px; height:10px; border-radius:50%; background:var(--gray-400);"></span>
              </div>
              <div style="font-size:0.8rem; color:var(--text-secondary); margin-top:8px;"><?=e($f['transit'] == 'direct' ? 'Langsung' : str_replace('_', ' ', $f['transit']))?></div>
            </div>

            <div class="stop" style="text-align:right;">
              <div class="time" style="font-size:1.5rem; font-weight:800;"><?=date('H:i', strtotime($f['arrive_time']))?></div>
              <div class="city" style="font-weight:700; color:var(--text-primary);"><?=e($f['destination'])?> (<?=e($f['dest_code'])?>)</div>
              <div class="date" style="font-size:0.85rem; color:var(--text-secondary);"><?=date('d M Y', strtotime($f['depart_date']))?></div>
            </div>
          </div>
        </div>

        <!-- Details Sections -->
        <div class="tyy-card" style="padding:2rem;">
          <div class="tyy-tabs" style="display:flex; gap:2rem; border-bottom:1px solid var(--border-subtle); margin-bottom:2rem;">
            <button class="tyy-tab active" data-target="info">Informasi</button>
            <button class="tyy-tab" data-target="facilities">Fasilitas</button>
            <button class="tyy-tab" data-target="policy">Kebijakan</button>
          </div>

          <div id="info" class="tyy-tab-content active">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:2rem;">
              <div class="info-item">
                <label style="display:block; font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px;">Bagasi Kabin & Terdaftar</label>
                <div style="font-weight:700; display:flex; align-items:center; gap:8px;">
                  <span>👜 Kabin 7kg</span>
                  <span style="color:var(--text-muted);">•</span>
                  <span>🧳 Terdaftar <?=e($f['baggage_capacity'])?></span>
                </div>
              </div>
              <div class="info-item">
                <label style="display:block; font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px;">Status Ketepatan Waktu</label>
                <div style="font-weight:700; color:var(--gray-300);">🕒 <?=e($f['delay_history'])?></div>
              </div>
              <div class="info-item">
                <label style="display:block; font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px;">Kursi Tersedia</label>
                <div style="font-weight:700;">💺 <?=e($f['seats_available'])?> Kursi</div>
              </div>
              <div class="info-item">
                <label style="display:block; font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px;">Kelas Layanan</label>
                <div style="font-weight:700; text-transform:capitalize;">✨ <?=e($f['class'])?> Class</div>
              </div>
            </div>
          </div>

          <div id="facilities" class="tyy-tab-content">
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:1.5rem;">
              <div class="facility-item" style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:1.5rem;">📶</span>
                <span>Wi-Fi Gratis</span>
              </div>
              <div class="facility-item" style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:1.5rem;">📺</span>
                <span>In-flight Entertainment</span>
              </div>
              <div class="facility-item" style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:1.5rem;">🔌</span>
                <span>USB Port & Power Outlet</span>
              </div>
              <div class="facility-item" style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:1.5rem;">🍱</span>
                <span>Makanan & Minuman</span>
              </div>
            </div>
            <p style="margin-top:2rem; font-size:0.9rem; color:var(--text-secondary);"><?=nl2br(e($f['facilities']))?></p>
          </div>

          <div id="policy" class="tyy-tab-content">
            <h5 style="margin-bottom:1rem;">Kebijakan Refund</h5>
            <p style="font-size:0.9rem; color:var(--text-secondary); margin-bottom:2rem; line-height:1.7;"><?=nl2br(e($f['refund_policy'] ?: "Refund tersedia hingga 24 jam sebelum keberangkatan dengan biaya administrasi. Silakan cek syarat dan ketentuan lengkap."))?></p>
            
            <h5 style="margin-bottom:1rem;">Kebijakan Reschedule</h5>
            <p style="font-size:0.9rem; color:var(--text-secondary); line-height:1.7;">Reschedule diperbolehkan dengan dikenakan biaya selisih harga tiket dan biaya perubahan jadwal sesuai ketentuan maskapai.</p>
          </div>
        </div>
      </div>

      <!-- Price Sidebar -->
      <aside class="tyy-price-sidebar">
        <div class="tyy-card" style="padding:1.75rem; position:sticky; top:100px;">
          <div style="font-size:0.9rem; color:var(--text-secondary); margin-bottom:4px;">Harga Per Orang</div>
          <div style="font-size:1.8rem; font-weight:800; color:var(--text-primary); margin-bottom:1.5rem;"><?=format_rupiah($f['price'])?></div>
          
          <ul style="list-style:none; padding:0; margin:0 0 1.5rem 0; font-size:0.85rem; color:var(--text-secondary); display:flex; flex-direction:column; gap:10px;">
            <li style="display:flex; align-items:center; gap:8px;">✅ Pajak & Biaya Layanan</li>
            <li style="display:flex; align-items:center; gap:8px;">✅ Bagasi Kabin 7kg</li>
            <li style="display:flex; align-items:center; gap:8px;">✅ Bagasi Terdaftar <?=e($f['baggage_capacity'])?></li>
          </ul>

          <a href="booking_step1.php?flight_id=<?=$f['id']?>&passengers=<?=$passengers?>" class="tyy-btn tyy-btn-primary tyy-btn-full tyy-btn-lg" style="margin-bottom:1rem;">Pesan Sekarang →</a>
          <p style="text-align:center; font-size:0.75rem; color:var(--text-muted);">Sisa <?=e($f['seats_available'])?> kursi tersedia di harga ini!</p>
        </div>
      </aside>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>

  <script>
    // Simple Tabs Script
    document.querySelectorAll('.tyy-tab').forEach(tab => {
      tab.addEventListener('click', () => {
        const target = tab.getAttribute('data-target');
        
        document.querySelectorAll('.tyy-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tyy-tab-content').forEach(c => c.classList.remove('active'));
        
        tab.classList.add('active');
        document.getElementById(target).classList.add('active');
      });
    });
  </script>

  <style>
    .tyy-tab {
      background: none;
      border: none;
      color: var(--text-muted);
      font-weight: 700;
      font-size: 1rem;
      padding: 0 0 12px 0;
      cursor: pointer;
      position: relative;
      transition: color 0.3s;
    }
    .tyy-tab.active {
      color: var(--text-primary);
    }
    .tyy-tab.active::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 100%;
      height: 2px;
      background: var(--gradient-silver);
    }
    .tyy-tab-content {
      display: none;
    }
    .tyy-tab-content.active {
      display: block;
      animation: fadeIn 0.4s ease-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</body>
</html>
