<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    flash_set('warning', 'Silakan login terlebih dahulu untuk melakukan pemesanan.');
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

$flight_id = $_GET['flight_id'] ?? 0;
$passengers_count = $_GET['passengers'] ?? 1;

$stmt = $pdo->prepare("SELECT * FROM flights WHERE id = ?");
$stmt->execute([$flight_id]);
$f = $stmt->fetch();

if (!$f) {
    redirect('index.php');
}

// Ambil data profil user untuk pre-fill penumpang pertama
$user_id = $_SESSION['user']['id'];
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user_profile = $stmtUser->fetch();

$pageTitle = 'Data Penumpang — Tikecting.yuu';
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
      <div class="step active">1. Data Penumpang</div>
      <div class="step">2. Pilih Kursi</div>
      <div class="step">3. Add-ons</div>
      <div class="step">4. Pembayaran</div>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 2.5rem; align-items: start;">
      <!-- Form Content -->
      <form action="booking_step2.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="flight_id" value="<?=$f['id']?>">
        <input type="hidden" name="passengers_count" value="<?=$passengers_count?>">

        <h3 style="margin-bottom:1.5rem;">Informasi Penumpang</h3>
        
        <?php for($i=1; $i<=$passengers_count; $i++): 
          // Pre-fill untuk penumpang pertama dari profil user
          $p_name = ($i === 1) ? ($user_profile['name'] ?? '') : '';
          $p_nik = ($i === 1) ? ($user_profile['nik'] ?? '') : '';
          $p_birth = ($i === 1) ? ($user_profile['birth_date'] ?? '') : '';
          $p_gender = ($i === 1) ? ($user_profile['gender'] ?? 'L') : 'L';
          $p_phone = ($i === 1) ? ($user_profile['phone'] ?? '') : '';
        ?>
          <div class="tyy-card fade-in" style="margin-bottom:2rem; padding:2rem;">
            <div class="tyy-section-label" style="margin-bottom:1.25rem;">👤 Penumpang #<?=$i?> <?= ($i === 1) ? '(Data Profil)' : '' ?></div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
              <div class="tyy-form-group">
                <label class="tyy-label">Nama Lengkap (sesuai ID)</label>
                <input type="text" name="passenger[<?=$i?>][name]" class="tyy-input" placeholder="Contoh: John Doe" value="<?=e($p_name)?>" required>
              </div>
              <div class="tyy-form-group">
                <label class="tyy-label">NIK / Passport</label>
                <input type="text" name="passenger[<?=$i?>][id_number]" class="tyy-input" placeholder="16 Digit NIK" value="<?=e($p_nik)?>" required>
              </div>
              <div class="tyy-form-group">
                <label class="tyy-label">Tanggal Lahir</label>
                <input type="date" name="passenger[<?=$i?>][birth_date]" class="tyy-input" value="<?=e($p_birth)?>" required>
              </div>
              <div class="tyy-form-group">
                <label class="tyy-label">Gender</label>
                <select name="passenger[<?=$i?>][gender]" class="tyy-input" required>
                  <option value="L" <?= $p_gender === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                  <option value="P" <?= $p_gender === 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
              </div>
              <div class="tyy-form-group" style="grid-column: span 2;">
                <label class="tyy-label">Upload Identitas (Foto NIK/Passport)</label>
                <input type="file" name="passenger_id_file_<?=$i?>" class="tyy-input" accept="image/*" <?= ($i === 1 && !empty($user_profile['nik'])) ? '' : 'required' ?>>
                <small style="color:var(--text-muted); display:block; margin-top:4px;">Format JPG/PNG, Maksimal 2MB. <?= ($i === 1 && !empty($user_profile['nik'])) ? 'Opsional jika sudah ada di profil.' : '' ?></small>
              </div>
            </div>
          </div>
        <?php endfor; ?>

        <!-- Emergency Contact -->
        <div class="tyy-card fade-in" style="margin-bottom:2rem; padding:2rem;">
          <div class="tyy-section-label" style="margin-bottom:1.25rem;">🚨 Kontak Darurat</div>
          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
            <div class="tyy-form-group">
              <label class="tyy-label">Nama Kontak Darurat</label>
              <input type="text" name="emergency_name" class="tyy-input" placeholder="Nama Lengkap" value="<?=e($user_profile['name'] ?? '')?>" required>
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label">Nomor Telepon</label>
              <input type="tel" name="emergency_phone" class="tyy-input" placeholder="Contoh: 08123456789" value="<?=e($user_profile['phone'] ?? '')?>" required>
            </div>
          </div>
        </div>

        <div style="text-align:right; margin-bottom:4rem;">
          <button type="submit" class="tyy-btn tyy-btn-primary tyy-btn-lg">Simpan & Lanjut Pilih Kursi →</button>
        </div>
      </form>

      <!-- Sidebar Summary -->
      <aside class="tyy-booking-summary-sidebar">
        <div class="tyy-card" style="padding:1.75rem; position:sticky; top:100px;">
          <h4 style="margin-bottom:1.25rem;">Ringkasan Penerbangan</h4>
          
          <div style="padding-bottom:1.25rem; border-bottom:1px solid var(--border-subtle); margin-bottom:1.25rem;">
            <div style="font-weight:700; font-size:1rem; margin-bottom:4px;"><?=e($f['origin'])?> → <?=e($f['destination'])?></div>
            <div style="font-size:0.85rem; color:var(--text-secondary);"><?=date('d M Y', strtotime($f['depart_date']))?> • <?=date('H:i', strtotime($f['depart_time']))?></div>
            <div style="font-size:0.85rem; color:var(--text-muted); margin-top:4px;"><?=e($f['airline_name'])?> • <?=e($f['flight_code'])?></div>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <span style="font-size:0.9rem; color:var(--text-secondary);"><?=e($passengers_count)?>x Dewasa</span>
            <span style="font-weight:700;"><?=format_rupiah($f['price'] * $passengers_count)?></span>
          </div>
          
          <div style="display:flex; justify-content:space-between; align-items:center; padding-top:1.25rem; border-top:1px solid var(--border-subtle); margin-top:1.25rem;">
            <span style="font-weight:700; font-size:1.1rem;">Total Pembayaran</span>
            <span style="font-weight:800; font-size:1.3rem; color:var(--text-primary);"><?=format_rupiah($f['price'] * $passengers_count)?></span>
          </div>
        </div>
      </aside>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>

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
  </style>
</body>
</html>
