<?php
require_once 'config.php';
require_once 'includes/functions.php';

$pageTitle = 'Tikecting.yuu — Pesan Tiket Pesawat Online';
?>
<!doctype html>
<html lang="id">
<head>
  <?php include 'includes/head.php'; ?>
</head>
<body>
  <?php include 'includes/navbar.php'; ?>

  <!-- Hero Section -->
  <header class="tyy-hero">
    <div class="tyy-container">
      <div class="tyy-hero-tagline">
        <span class="tyy-pulse-dot"></span>
        E-Tiketing Maskapai Terpercaya
      </div>
      <h1>Terbang Nyaman dengan <span class="gradient-text">Tikecting.yuu</span></h1>
      <p>Platform pemesanan tiket pesawat online yang modern, aman, dan mudah digunakan. Pesan tiket impianmu sekarang!</p>
    </div>
  </header>

  <!-- Search Section -->
  <section class="tyy-container">
    <?php include 'includes/search_form.php'; ?>
  </section>

  <!-- Flights List -->
  <main class="tyy-container" style="padding-top:4rem; padding-bottom:4rem;">
    <div class="tyy-card fade-in" style="padding:2rem 2.5rem; background:transparent; border:none; box-shadow:none;">
      <div class="tyy-flex-between" style="margin-bottom:2rem;">
        <div>
          <div class="tyy-section-label">✨ Rekomendasi Terpopuler</div>
          <h2 class="tyy-section-title">Promo Penerbangan Terbaik</h2>
        </div>
      </div>

      <?php
      // Ambil tiket promo/terbaru
      $stmt = $pdo->query('SELECT * FROM flights WHERE is_promo = 1 ORDER BY created_at DESC LIMIT 6');
      $promoFlights = $stmt->fetchAll();
      ?>

      <?php if(empty($promoFlights)): ?>
        <div class="tyy-empty-state">
          <div class="icon">✈️</div>
          <h4>Belum Ada Promo</h4>
          <p>Nantikan promo menarik lainnya segera!</p>
        </div>
      <?php else: ?>
        <div class="tyy-tickets-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 2.5rem;">
          <?php foreach($promoFlights as $i => $f): ?>
            <div class="tyy-ticket-card-with-img fade-in fade-in-delay-<?=min($i+1, 4)?>">
              <?php if($f['is_promo'] && $f['promo_badge']): ?>
                <div class="tyy-badge-promo" style="position:absolute; top:12px; left:12px; z-index:5; background:var(--gradient-silver); color:var(--text-inverse); padding:4px 12px; border-radius:var(--radius-sm); font-size:0.75rem; font-weight:700; box-shadow:0 4px 10px rgba(0,0,0,0.3);"><?=e($f['promo_badge'])?></div>
              <?php endif; ?>
              <div class="tyy-ticket-img-wrapper">
                <img src="<?=get_city_landmark($f['origin'])?>" alt="<?=e($f['origin'])?>" class="tyy-ticket-img" loading="lazy">
              </div>
              <div class="tyy-ticket-content">
                <div class="tyy-ticket-route-new">
                  <?=e($f['origin'])?> (<?=e($f['origin_code'])?>) ✈ <?=e($f['destination'])?> (<?=e($f['dest_code'])?>)
                </div>
                
                <div style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:0.5rem; display:flex; align-items:center; gap:8px;">
                  <span style="font-weight:700;"><?=e($f['airline_name'])?></span>
                  <span style="color:var(--text-muted);">|</span>
                  <span><?=e($f['flight_code'])?></span>
                </div>

                <div class="tyy-ticket-info-grid">
                  <div class="tyy-ticket-info-item">
                    <span class="tyy-ticket-info-label">Berangkat</span>
                    <span class="tyy-ticket-info-value"><?=date('d M Y', strtotime($f['depart_date']))?></span>
                  </div>
                  <div class="tyy-ticket-info-item">
                    <span class="tyy-ticket-info-label">Waktu</span>
                    <span class="tyy-ticket-info-value"><?=date('H:i', strtotime($f['depart_time']))?></span>
                  </div>
                  <div class="tyy-ticket-info-item">
                    <span class="tyy-ticket-info-label">Kelas</span>
                    <span class="tyy-ticket-info-value" style="text-transform:capitalize;"><?=e($f['class'])?></span>
                  </div>
                  <div class="tyy-ticket-info-item">
                    <span class="tyy-ticket-info-label">Kursi</span>
                    <span class="tyy-ticket-info-value"><?=e($f['seats_available'])?> tersedia</span>
                  </div>
                </div>
              </div>

              <div class="tyy-ticket-footer-new">
                <div class="tyy-ticket-price"><?=format_rupiah($f['price'])?></div>
                <a href="flight_detail.php?id=<?=$f['id']?>" class="tyy-btn tyy-btn-outline tyy-btn-sm">Lihat Detail →</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>
</body>
</html>
