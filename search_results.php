<?php
require_once 'config.php';
require_once 'includes/functions.php';

$origin = $_GET['origin'] ?? '';
$destination = $_GET['destination'] ?? '';
$depart_date = $_GET['depart_date'] ?? '';
$class = $_GET['class'] ?? 'economy';
$passengers = $_GET['passengers'] ?? 1;

// Base query
$query = "SELECT * FROM flights WHERE 1=1";
$params = [];

if ($origin) {
    // Try to match city name or airport code
    $query .= " AND (origin LIKE ? OR origin_code LIKE ?)";
    $params[] = "%$origin%";
    $params[] = "%$origin%";
}
if ($destination) {
    $query .= " AND (destination LIKE ? OR dest_code LIKE ?)";
    $params[] = "%$destination%";
    $params[] = "%$destination%";
}
if ($depart_date) {
    $query .= " AND depart_date = ?";
    $params[] = $depart_date;
}
if ($class) {
    $query .= " AND class = ?";
    $params[] = $class;
}

// Sorting
$sort = $_GET['sort'] ?? 'price_asc';
switch($sort) {
    case 'price_asc': $query .= " ORDER BY price ASC"; break;
    case 'price_desc': $query .= " ORDER BY price DESC"; break;
    case 'time_asc': $query .= " ORDER BY depart_time ASC"; break;
    case 'duration_asc': $query .= " ORDER BY duration ASC"; break;
    default: $query .= " ORDER BY price ASC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$flights = $stmt->fetchAll();

$pageTitle = 'Hasil Pencarian Tiket — Tikecting.yuu';
?>
<!doctype html>
<html lang="id">
<head>
  <?php include 'includes/head.php'; ?>
</head>
<body class="tyy-bg-base">
  <?php include 'includes/navbar.php'; ?>

  <div class="tyy-search-header">
    <div class="tyy-container">
      <div class="tyy-search-summary">
        <div class="route">
          <span class="city"><?=e($origin ?: 'Semua')?></span>
          <span class="arrow">✈</span>
          <span class="city"><?=e($destination ?: 'Semua')?></span>
        </div>
        <div class="meta">
          <span><?= $depart_date ? date('d M Y', strtotime($depart_date)) : 'Semua Tanggal' ?></span>
          <span class="dot"></span>
          <span><?= e($passengers) ?> Penumpang</span>
          <span class="dot"></span>
          <span><?= ucfirst(e($class)) ?></span>
        </div>
      </div>
      <a href="index.php" class="tyy-btn tyy-btn-outline tyy-btn-sm">Ubah Pencarian</a>
    </div>
  </div>

  <main class="tyy-container tyy-page-content">
    <div class="tyy-search-layout">
      <!-- Sidebar Filters -->
      <aside class="tyy-search-sidebar">
        <div class="tyy-card" style="padding:1.5rem;">
          <h4 style="margin-bottom:1.25rem;">Filter</h4>
          
          <div class="filter-section">
            <label class="filter-label">Transit</label>
            <div class="filter-options">
              <label class="tyy-checkbox-label"><input type="checkbox" name="transit" value="direct" checked> Langsung</label>
              <label class="tyy-checkbox-label"><input type="checkbox" name="transit" value="1"> 1 Transit</label>
            </div>
          </div>

          <div class="filter-section">
            <label class="filter-label">Fasilitas</label>
            <div class="filter-options">
              <label class="tyy-checkbox-label"><input type="checkbox" checked> Bagasi</label>
              <label class="tyy-checkbox-label"><input type="checkbox" checked> Hiburan</label>
              <label class="tyy-checkbox-label"><input type="checkbox"> Makanan</label>
            </div>
          </div>

          <div class="filter-section">
            <label class="filter-label">Urutkan</label>
            <select class="tyy-input" onchange="window.location.href='?<?=http_build_query(array_merge($_GET, ['sort' => '']))?>' + this.value">
              <option value="price_asc" <?=$sort=='price_asc'?'selected':''?>>Harga Terendah</option>
              <option value="price_desc" <?=$sort=='price_desc'?'selected':''?>>Harga Tertinggi</option>
              <option value="time_asc" <?=$sort=='time_asc'?'selected':''?>>Keberangkatan Terawal</option>
              <option value="duration_asc" <?=$sort=='duration_asc'?'selected':''?>>Durasi Tercepat</option>
            </select>
          </div>
        </div>
      </aside>

      <!-- Results List -->
      <div class="tyy-search-results">
        <div class="tyy-flex-between" style="margin-bottom:1.5rem;">
          <div style="color:var(--text-secondary); font-size:0.9rem;">
            Menampilkan <strong><?=count($flights)?></strong> tiket penerbangan
          </div>
        </div>

        <?php if(empty($flights)): ?>
          <div class="tyy-card tyy-empty-state" style="padding:4rem;">
            <div class="icon">✈️</div>
            <h4>Tidak Ada Penerbangan</h4>
            <p>Maaf, kami tidak menemukan penerbangan yang sesuai dengan kriteria Anda.</p>
            <a href="index.php" class="tyy-btn tyy-btn-primary" style="margin-top:1.5rem;">Cari Rute Lain</a>
          </div>
        <?php else: ?>
          <div class="tyy-flights-list">
            <?php foreach($flights as $f): ?>
              <div class="tyy-flight-row-card fade-in">
                <div class="main-info">
                  <div class="airline">
                    <div class="logo">✈</div>
                    <div class="name"><?=e($f['airline_name'])?></div>
                  </div>
                  <div class="time-route">
                    <div class="departure">
                      <div class="time"><?=date('H:i', strtotime($f['depart_time']))?></div>
                      <div class="code"><?=e($f['origin_code'])?></div>
                    </div>
                    <div class="duration-line">
                      <div class="duration"><?=e($f['duration'])?></div>
                      <div class="line">
                        <span class="dot"></span>
                        <span class="path"></span>
                        <span class="dot"></span>
                      </div>
                      <div class="transit"><?=e($f['transit'] == 'direct' ? 'Langsung' : str_replace('_', ' ', $f['transit']))?></div>
                    </div>
                    <div class="arrival">
                      <div class="time"><?=date('H:i', strtotime($f['arrive_time']))?></div>
                      <div class="code"><?=e($f['dest_code'])?></div>
                    </div>
                  </div>
                </div>
                <div class="price-action">
                  <div class="price"><?=format_rupiah($f['price'])?><span>/pax</span></div>
                  <a href="flight_detail.php?id=<?=$f['id']?>&passengers=<?=$passengers?>" class="tyy-btn tyy-btn-primary">Pilih Tiket</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>

  <style>
    .tyy-search-header {
      background: var(--bg-card);
      border-bottom: 1px solid var(--border-subtle);
      padding: 1.5rem 0;
      position: sticky;
      top: 72px;
      z-index: 90;
    }
    .tyy-search-header .tyy-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .tyy-search-summary .route {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 4px;
    }
    .tyy-search-summary .route .city {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--text-primary);
    }
    .tyy-search-summary .route .arrow {
      color: var(--gray-400);
    }
    .tyy-search-summary .meta {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.85rem;
      color: var(--text-secondary);
    }
    .tyy-search-summary .meta .dot {
      width: 4px;
      height: 4px;
      border-radius: 50%;
      background: var(--gray-600);
    }

    .tyy-search-layout {
      display: grid;
      grid-template-columns: 280px 1fr;
      gap: 2rem;
    }
    @media (max-width: 992px) {
      .tyy-search-layout {
        grid-template-columns: 1fr;
      }
      .tyy-search-sidebar {
        display: none;
      }
    }

    .filter-section {
      margin-bottom: 1.5rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px solid var(--border-subtle);
    }
    .filter-section:last-child {
      border-bottom: none;
      padding-bottom: 0;
      margin-bottom: 0;
    }
    .filter-label {
      display: block;
      font-size: 0.85rem;
      font-weight: 700;
      color: var(--text-primary);
      margin-bottom: 12px;
    }
    .filter-options {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .tyy-flight-row-card {
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
      border-radius: var(--radius-lg);
      padding: 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.25rem;
      transition: all var(--transition-normal);
    }
    .tyy-flight-row-card:hover {
      border-color: var(--border-light);
      box-shadow: var(--shadow-card-hover);
      transform: translateY(-2px);
    }

    .tyy-flight-row-card .main-info {
      display: flex;
      align-items: center;
      gap: 3rem;
      flex: 1;
    }
    .tyy-flight-row-card .airline {
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 150px;
    }
    .tyy-flight-row-card .airline .logo {
      width: 40px;
      height: 40px;
      background: var(--bg-surface);
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
    }
    .tyy-flight-row-card .airline .name {
      font-weight: 700;
      font-size: 0.95rem;
    }

    .tyy-flight-row-card .time-route {
      display: flex;
      align-items: center;
      gap: 2rem;
      flex: 1;
      justify-content: center;
    }
    .tyy-flight-row-card .time-route .time {
      font-size: 1.2rem;
      font-weight: 800;
      color: var(--text-primary);
    }
    .tyy-flight-row-card .time-route .code {
      font-size: 0.8rem;
      color: var(--text-muted);
      font-weight: 600;
    }

    .duration-line {
      display: flex;
      flex-direction: column;
      align-items: center;
      min-width: 120px;
    }
    .duration-line .duration {
      font-size: 0.75rem;
      color: var(--text-muted);
      margin-bottom: 4px;
    }
    .duration-line .line {
      width: 100%;
      height: 2px;
      background: var(--border-medium);
      position: relative;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .duration-line .line .dot {
      width: 6px;
      height: 6px;
      background: var(--gray-400);
      border-radius: 50%;
    }
    .duration-line .transit {
      font-size: 0.75rem;
      color: var(--text-secondary);
      margin-top: 4px;
    }

    .tyy-flight-row-card .price-action {
      text-align: right;
      padding-left: 2rem;
      border-left: 1px solid var(--border-subtle);
    }
    .tyy-flight-row-card .price {
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--text-primary);
      margin-bottom: 0.75rem;
    }
    .tyy-flight-row-card .price span {
      font-size: 0.8rem;
      color: var(--text-muted);
      font-weight: 400;
    }

    @media (max-width: 768px) {
      .tyy-flight-row-card {
        flex-direction: column;
        align-items: stretch;
        gap: 1.5rem;
      }
      .tyy-flight-row-card .main-info {
        flex-direction: column;
        gap: 1.5rem;
        align-items: flex-start;
      }
      .tyy-flight-row-card .time-route {
        width: 100%;
        justify-content: space-between;
      }
      .tyy-flight-row-card .price-action {
        border-left: none;
        border-top: 1px solid var(--border-subtle);
        padding-left: 0;
        padding-top: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }
      .tyy-flight-row-card .price {
        margin-bottom: 0;
      }
    }
  </style>
</body>
</html>
