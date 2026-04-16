<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

if(!is_admin()) redirect('../login.php');

$msg = '';
$msg_type = '';

// Handle flight creation/edit
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){
    $flight_code = trim($_POST['flight_code'] ?? '');
    $airline_name = trim($_POST['airline_name'] ?? 'Tikecting Air');
    $origin = trim($_POST['origin'] ?? '');
    $origin_code = strtoupper(trim($_POST['origin_code'] ?? ''));
    $destination = trim($_POST['destination'] ?? '');
    $dest_code = strtoupper(trim($_POST['dest_code'] ?? ''));
    $depart_date = $_POST['depart_date'] ?? '';
    $depart_time = $_POST['depart_time'] ?? '';
    $arrive_time = $_POST['arrive_time'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $transit = $_POST['transit'] ?? 'direct';
    $price = floatval($_POST['price'] ?? 0);
    $seats_available = intval($_POST['seats_available'] ?? 100);
    $class = $_POST['class'] ?? 'economy';
    $is_promo = isset($_POST['is_promo']) ? 1 : 0;
    $promo_badge = trim($_POST['promo_badge'] ?? '');

    if(empty($flight_code) || empty($origin) || empty($destination) || empty($depart_date) || empty($depart_time) || empty($arrive_time) || $price <= 0){
        $msg = 'Field utama wajib diisi dan harga harus lebih dari 0.';
        $msg_type = 'error';
    } else {
        if($_POST['action'] === 'add'){
            $ins = $pdo->prepare('INSERT INTO flights (flight_code, airline_name, origin, origin_code, destination, dest_code, depart_date, depart_time, arrive_time, duration, transit, price, seats_available, class, is_promo, promo_badge) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $ins->execute([$flight_code, $airline_name, $origin, $origin_code, $destination, $dest_code, $depart_date, $depart_time, $arrive_time, $duration, $transit, $price, $seats_available, $class, $is_promo, $promo_badge]);
            $msg = 'Penerbangan baru berhasil ditambahkan!';
            $msg_type = 'success';
        } elseif($_POST['action'] === 'edit'){
            $id = intval($_POST['id']);
            $upd = $pdo->prepare('UPDATE flights SET flight_code=?, airline_name=?, origin=?, origin_code=?, destination=?, dest_code=?, depart_date=?, depart_time=?, arrive_time=?, duration=?, transit=?, price=?, seats_available=?, class=?, is_promo=?, promo_badge=? WHERE id=?');
            $upd->execute([$flight_code, $airline_name, $origin, $origin_code, $destination, $dest_code, $depart_date, $depart_time, $arrive_time, $duration, $transit, $price, $seats_available, $class, $is_promo, $promo_badge, $id]);
            $msg = 'Penerbangan berhasil diupdate!';
            $msg_type = 'success';
        }
    }
}

// Handle delete
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $pdo->prepare('DELETE FROM flights WHERE id = ?')->execute([$id]);
    $msg = 'Penerbangan berhasil dihapus.';
    $msg_type = 'success';
}

// Fetch all flights
$flights = $pdo->query('SELECT * FROM flights ORDER BY depart_date DESC, depart_time ASC')->fetchAll();

// Edit mode
$editFlight = null;
if(isset($_GET['edit'])){
    $editId = intval($_GET['edit']);
    $stmEdit = $pdo->prepare('SELECT * FROM flights WHERE id = ?');
    $stmEdit->execute([$editId]);
    $editFlight = $stmEdit->fetch();
}

$pageTitle = 'Kelola Penerbangan — Tikecting.yuu';
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

      <div class="tyy-flex-between" style="margin-bottom:2rem;">
        <div>
          <div class="tyy-section-label">✈ Penerbangan</div>
          <h2 class="tyy-section-title">Manajemen Jadwal Terbang</h2>
        </div>
        <a href="dashboard.php" class="tyy-btn tyy-btn-outline tyy-btn-sm">← Dashboard</a>
      </div>

      <?php if($msg): ?>
        <div class="tyy-alert tyy-alert-<?=$msg_type?>" style="margin-bottom:2rem; padding:1rem; border-radius:var(--radius-md); background:<?=$msg_type=='success'?'rgba(0,255,0,0.1)':'rgba(255,0,0,0.1)'?>; color:<?=$msg_type=='success'?'#4caf50':'#ff5252'?>; border:1px solid currentColor;">
          <span><?=$msg_type === 'success' ? '✅' : '⚠️'?></span> <?=e($msg)?>
        </div>
      <?php endif; ?>

      <!-- Add / Edit Form -->
      <div class="tyy-card fade-in" style="margin-bottom:3rem; padding:2.5rem;">
        <h4 style="margin-bottom:1.5rem;"><?=$editFlight ? '✏️ Edit Penerbangan #'.$editFlight['id'] : '➕ Tambah Jadwal Baru'?></h4>

        <form method="post" id="flightForm">
          <input type="hidden" name="action" value="<?=$editFlight ? 'edit' : 'add'?>">
          <?php if($editFlight): ?>
            <input type="hidden" name="id" value="<?=$editFlight['id']?>">
          <?php endif; ?>

          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem;">
            <div class="tyy-form-group">
              <label class="tyy-label">Kode Penerbangan</label>
              <input type="text" name="flight_code" class="tyy-input" placeholder="TY-101" required value="<?=e($editFlight['flight_code'] ?? '')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label">Maskapai</label>
              <input type="text" name="airline_name" class="tyy-input" placeholder="Tikecting Air" value="<?=e($editFlight['airline_name'] ?? 'Tikecting Air')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label">Asal (Kota)</label>
              <input type="text" name="origin" class="tyy-input" placeholder="Jakarta" required value="<?=e($editFlight['origin'] ?? '')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label">Kode Asal (IATA)</label>
              <input type="text" name="origin_code" class="tyy-input" placeholder="CGK" required value="<?=e($editFlight['origin_code'] ?? '')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label">Tujuan (Kota)</label>
              <input type="text" name="destination" class="tyy-input" placeholder="Bali" required value="<?=e($editFlight['destination'] ?? '')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label">Kode Tujuan (IATA)</label>
              <input type="text" name="dest_code" class="tyy-input" placeholder="DPS" required value="<?=e($editFlight['dest_code'] ?? '')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label">Tanggal Berangkat</label>
              <input type="date" name="depart_date" class="tyy-input" required value="<?=e($editFlight['depart_date'] ?? '')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label">Waktu Berangkat</label>
              <input type="time" name="depart_time" class="tyy-input" required value="<?=e($editFlight['depart_time'] ?? '')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label">Waktu Tiba</label>
              <input type="time" name="arrive_time" class="tyy-input" required value="<?=e($editFlight['arrive_time'] ?? '')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label">Durasi (Contoh: 1j 30m)</label>
              <input type="text" name="duration" class="tyy-input" value="<?=e($editFlight['duration'] ?? '')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label">Transit</label>
              <select name="transit" class="tyy-input">
                <option value="direct" <?=(($editFlight['transit']??'')=='direct')?'selected':''?>>Langsung</option>
                <option value="1_transit" <?=(($editFlight['transit']??'')=='1_transit')?'selected':''?>>1 Transit</option>
                <option value="2_transit" <?=(($editFlight['transit']??'')=='2_transit')?'selected':''?>>2 Transit</option>
              </select>
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label">Harga (Rp)</label>
              <input type="number" name="price" class="tyy-input" required value="<?=e($editFlight['price'] ?? '')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label">Kursi</label>
              <input type="number" name="seats_available" class="tyy-input" value="<?=e($editFlight['seats_available'] ?? '100')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label">Kelas</label>
              <select name="class" class="tyy-input">
                <option value="economy" <?=(($editFlight['class']??'')=='economy')?'selected':''?>>Economy</option>
                <option value="business" <?=(($editFlight['class']??'')=='business')?'selected':''?>>Business</option>
                <option value="first" <?=(($editFlight['class']??'')=='first')?'selected':''?>>First Class</option>
              </select>
            </div>
            <div class="tyy-form-group" style="display:flex; align-items:flex-end; gap:10px;">
              <label class="tyy-checkbox-label" style="margin-bottom:12px;">
                <input type="checkbox" name="is_promo" <?=(($editFlight['is_promo']??0)==1)?'checked':''?>> Set as Promo
              </label>
              <input type="text" name="promo_badge" class="tyy-input" placeholder="Badge (e.g. Best Deal)" value="<?=e($editFlight['promo_badge'] ?? '')?>">
            </div>
          </div>

          <div style="margin-top:2rem; display:flex; gap:1rem;">
            <button type="submit" class="tyy-btn tyy-btn-primary"><?=$editFlight ? 'Update Penerbangan' : 'Simpan Penerbangan'?> →</button>
            <?php if($editFlight): ?>
              <a href="tickets.php" class="tyy-btn tyy-btn-outline">Batal</a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- Flight List -->
      <div class="tyy-card fade-in">
        <h4 style="margin-bottom:1.5rem;">Daftar Jadwal Penerbangan</h4>
        <div class="tyy-table-wrap" style="overflow-x:auto;">
          <table class="tyy-table" style="width:100%; border-collapse:collapse;">
            <thead>
              <tr style="text-align:left; border-bottom:1px solid var(--border-subtle); color:var(--text-muted);">
                <th style="padding:1rem;">KODE</th>
                <th style="padding:1rem;">RUTE</th>
                <th style="padding:1rem;">WAKTU</th>
                <th style="padding:1rem;">KELAS</th>
                <th style="padding:1rem;">HARGA</th>
                <th style="padding:1rem;">STATUS</th>
                <th style="padding:1rem; text-align:right;">AKSI</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($flights as $f): ?>
                <tr style="border-bottom:1px solid var(--border-subtle);">
                  <td style="padding:1rem;">
                    <div style="font-weight:700; color:var(--text-primary);"><?=e($f['flight_code'])?></div>
                    <div style="font-size:0.75rem; color:var(--text-muted);"><?=e($f['airline_name'])?></div>
                  </td>
                  <td style="padding:1rem;">
                    <div style="font-weight:600;"><?=e($f['origin_code'])?> → <?=e($f['dest_code'])?></div>
                    <div style="font-size:0.75rem; color:var(--text-secondary);"><?=e($f['origin'])?> to <?=e($f['destination'])?></div>
                  </td>
                  <td style="padding:1rem;">
                    <div style="font-weight:600;"><?=date('d M Y', strtotime($f['depart_date']))?></div>
                    <div style="font-size:0.75rem; color:var(--text-secondary);"><?=date('H:i', strtotime($f['depart_time']))?> - <?=date('H:i', strtotime($f['arrive_time']))?></div>
                  </td>
                  <td style="padding:1rem; text-transform:capitalize;"><?=e($f['class'])?></td>
                  <td style="padding:1rem; font-weight:700;"><?=format_rupiah($f['price'])?></td>
                  <td style="padding:1rem;">
                    <?php if($f['is_promo']): ?>
                      <span class="tyy-badge" style="background:var(--gradient-silver); color:var(--text-inverse); padding:2px 8px; border-radius:4px; font-size:0.65rem; font-weight:800;">PROMO</span>
                    <?php else: ?>
                      <span style="color:var(--text-muted); font-size:0.75rem;">Regular</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding:1rem; text-align:right;">
                    <a href="?edit=<?=$f['id']?>" class="tyy-btn tyy-btn-ghost tyy-btn-sm">Edit</a>
                    <a href="?delete=<?=$f['id']?>" class="tyy-btn tyy-btn-ghost tyy-btn-sm" style="color:#ff5252;" onclick="return confirm('Hapus penerbangan ini?')">Hapus</a>
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
