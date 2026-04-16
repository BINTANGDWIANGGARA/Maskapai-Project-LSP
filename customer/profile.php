<?php
require_once __DIR__ . '/../config.php';
if(!is_customer()) redirect('../login.php');

$uid = $_SESSION['user']['id'];

// Ambil data user lengkap dari database
$stm = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stm->execute([$uid]);
$user = $stm->fetch();

$msg = '';
$msg_type = '';

// Handle profile update (hanya jika bukan form password)
if($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['change_password'])){
    $name      = trim($_POST['name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $nik       = trim($_POST['nik'] ?? '');
    $gender    = $_POST['gender'] ?? null;
    $birthdate = $_POST['birthdate'] ?? null;
    $address   = trim($_POST['address'] ?? '');

    if(empty($name)){
        $msg = 'Nama lengkap wajib diisi.';
        $msg_type = 'error';
    } else {
        // Handle photo upload
        $photo = $user['photo'] ?? null; // tetap pakai foto lama
        if(isset($_FILES['photo']) && $_FILES['photo']['error'] === 0){
            $file = $_FILES['photo'];
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if($file['size'] > 2 * 1024 * 1024){
                $msg = 'Ukuran foto maks 2MB.';
                $msg_type = 'error';
            } elseif(!in_array($ext, $allowed)){
                $msg = 'Format foto tidak valid. Gunakan JPG, PNG, atau WebP.';
                $msg_type = 'error';
            } else {
                if(!is_dir(__DIR__ . '/../uploads/photos')) mkdir(__DIR__ . '/../uploads/photos', 0777, true);
                $photo = 'uploads/photos/user_' . $uid . '_' . time() . '.' . $ext;
                move_uploaded_file($file['tmp_name'], __DIR__ . '/../' . $photo);
            }
        }

        if($msg_type !== 'error'){
            $upd = $pdo->prepare('UPDATE users SET name=?, email=?, phone=?, nik=?, gender=?, birth_date=?, address=?, photo=? WHERE id=?');
            $upd->execute([$name, $email, $phone, $nik ?: null, $gender ?: null, $birthdate ?: null, $address, $photo, $uid]);

            // Update session name
            $_SESSION['user']['name'] = $name;

            $msg = 'Profil berhasil diperbarui!';
            $msg_type = 'success';

            // Refresh user data
            $stm->execute([$uid]);
            $user = $stm->fetch();
        }
    }
}

// Handle password change
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])){
    $current  = $_POST['current_password'] ?? '';
    $newpass   = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if(empty($current) || empty($newpass)){
        $msg = 'Password lama dan baru wajib diisi.';
        $msg_type = 'error';
    } elseif(hash('sha256', $current) !== $user['password']){
        $msg = 'Password lama salah.';
        $msg_type = 'error';
    } elseif(strlen($newpass) < 5){
        $msg = 'Password baru minimal 5 karakter.';
        $msg_type = 'error';
    } elseif($newpass !== $confirm){
        $msg = 'Konfirmasi password tidak cocok.';
        $msg_type = 'error';
    } else {
        $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')
            ->execute([hash('sha256', $newpass), $uid]);
        $msg = 'Password berhasil diubah!';
        $msg_type = 'success';

        // Refresh user data
        $stm->execute([$uid]);
        $user = $stm->fetch();
    }
}

// Hitung statistik customer
$totalOrders = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE user_id = ?');
$totalOrders->execute([$uid]);
$orderCount = $totalOrders->fetchColumn();

$totalSpent = $pdo->prepare("SELECT COALESCE(SUM(p.amount),0) FROM payments p JOIN bookings b ON p.booking_id=b.id WHERE b.user_id=? AND p.status='paid'");
$totalSpent->execute([$uid]);
$spentAmount = $totalSpent->fetchColumn();

$base = get_base_url();
$pageTitle = 'Profil Saya — Tikecting.yuu';
?>
<!doctype html>
<html lang="id">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body>
  <?php include __DIR__ . '/../includes/navbar.php'; ?>

  <div class="tyy-page-content">
    <div class="tyy-container" style="max-width:900px;">

      <!-- Profile Header Card -->
      <div class="tyy-card fade-in" style="padding:2.5rem;margin-bottom:2rem;">
        <div style="display:flex;align-items:center;gap:2rem;flex-wrap:wrap;">
          <!-- Avatar -->
          <div class="tyy-profile-avatar-wrap">
            <?php if($user['photo']): ?>
              <img src="<?=$base?>/<?=e($user['photo'])?>" alt="Foto Profil" class="tyy-profile-avatar-img">
            <?php else: ?>
              <div class="tyy-profile-avatar-placeholder">
                <?=get_user_initials()?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Info -->
          <div style="flex:1;min-width:200px;">
            <h2 style="margin-bottom:4px;"><?=e($user['name'])?></h2>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
              <span class="tyy-nav-role tyy-nav-role-customer">Customer</span>
              <span style="color:var(--text-muted);font-size:0.85rem;">@<?=e($user['username'])?></span>
              <span style="color:var(--text-muted);font-size:0.82rem;">• Bergabung <?=date('d M Y', strtotime($user['created_at']))?></span>
            </div>

            <!-- Mini stats -->
            <div style="display:flex;gap:1.5rem;flex-wrap:wrap;">
              <div>
                <div style="font-size:1.3rem;font-weight:800;background:var(--gradient-primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?=$orderCount?></div>
                <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;">Total Order</div>
              </div>
              <div>
                <div style="font-size:1.3rem;font-weight:800;background:var(--gradient-primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?=format_rupiah($spentAmount)?></div>
                <div style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;">Total Belanja</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <?php if($msg): ?>
        <div class="tyy-alert tyy-alert-<?=$msg_type?>">
          <span><?=$msg_type === 'success' ? '✅' : '⚠️'?></span> <?=e($msg)?>
        </div>
      <?php endif; ?>

      <!-- Profile Tabs -->
      <div class="tyy-profile-tabs">
        <button class="tyy-profile-tab active" onclick="switchTab('biodata', this)" id="tabBiodata">👤 Biodata</button>
        <button class="tyy-profile-tab" onclick="switchTab('password', this)" id="tabPassword">🔒 Ubah Password</button>
      </div>

      <!-- Tab: Biodata -->
      <div class="tyy-card fade-in" id="panelBiodata" style="padding:2.5rem;">
        <div class="tyy-section-label">👤 Informasi Pribadi</div>
        <h4 style="margin-bottom:1.5rem;">Edit Biodata</h4>
        <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:1.5rem;">
          Data ini akan digunakan saat pemesanan tiket. Pastikan sesuai dengan identitas resmi Anda.
        </p>

        <form method="post" enctype="multipart/form-data" id="profileForm">
          <!-- Row 1: Nama + Username -->
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="tyy-form-group">
              <label class="tyy-label" for="name">Nama Lengkap <span style="color:var(--brand-accent);">*</span></label>
              <input type="text" name="name" id="name" class="tyy-input" required
                     value="<?=e($user['name'])?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label">Username</label>
              <input type="text" class="tyy-input" value="<?=e($user['username'])?>" disabled
                     style="opacity:0.5;cursor:not-allowed;">
            </div>
          </div>

          <!-- Row 2: NIK + Gender -->
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="tyy-form-group">
              <label class="tyy-label" for="nik">NIK (No. KTP)</label>
              <input type="text" name="nik" id="nik" class="tyy-input" placeholder="16 digit NIK"
                     maxlength="20" value="<?=e($user['nik'] ?? '')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label" for="gender">Jenis Kelamin</label>
              <select name="gender" id="gender" class="tyy-input tyy-select-dropdown">
                <option value="">— Pilih —</option>
                <option value="Laki-laki" <?=($user['gender'] ?? '') === 'Laki-laki' ? 'selected' : ''?>>👨 Laki-laki</option>
                <option value="Perempuan" <?=($user['gender'] ?? '') === 'Perempuan' ? 'selected' : ''?>>👩 Perempuan</option>
              </select>
            </div>
          </div>

          <!-- Row 3: Birthdate + Phone -->
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="tyy-form-group">
              <label class="tyy-label" for="birthdate">Tanggal Lahir</label>
              <input type="date" name="birthdate" id="birthdate" class="tyy-input"
                     value="<?=e($user['birth_date'] ?? '')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label" for="phone">No. Telepon / WhatsApp</label>
              <input type="text" name="phone" id="phone" class="tyy-input" placeholder="08xxxxxxxxxx"
                     value="<?=e($user['phone'] ?? '')?>">
            </div>
          </div>

          <!-- Email -->
          <div class="tyy-form-group">
            <label class="tyy-label" for="email">Email</label>
            <input type="email" name="email" id="email" class="tyy-input" placeholder="email@contoh.com"
                   value="<?=e($user['email'] ?? '')?>">
          </div>

          <!-- Address -->
          <div class="tyy-form-group">
            <label class="tyy-label" for="address">Alamat Lengkap</label>
            <textarea name="address" id="address" class="tyy-input" rows="3"
                      placeholder="Jl. Contoh No.1, Kota, Provinsi"
                      style="resize:vertical;min-height:80px;"><?=e($user['address'] ?? '')?></textarea>
          </div>

          <!-- Photo Upload -->
          <div class="tyy-form-group">
            <label class="tyy-label" for="photo">Foto Profil</label>
            <input type="file" name="photo" id="photo" class="tyy-input tyy-input-file"
                   accept="image/jpeg,image/png,image/webp">
            <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;">Format: JPG, PNG, WebP. Maks 2MB.</div>
          </div>

          <button type="submit" class="tyy-btn tyy-btn-primary tyy-btn-lg" style="margin-top:0.5rem;">
            💾 Simpan Biodata
          </button>
        </form>
      </div>

      <!-- Tab: Password -->
      <div class="tyy-card fade-in" id="panelPassword" style="padding:2.5rem;display:none;">
        <div class="tyy-section-label">🔒 Keamanan</div>
        <h4 style="margin-bottom:1.5rem;">Ubah Password</h4>

        <form method="post" id="passwordForm" style="max-width:400px;">
          <input type="hidden" name="change_password" value="1">

          <div class="tyy-form-group">
            <label class="tyy-label" for="current_password">Password Lama</label>
            <input type="password" name="current_password" id="current_password" class="tyy-input"
                   placeholder="Masukkan password saat ini" required>
          </div>

          <div class="tyy-form-group">
            <label class="tyy-label" for="new_password">Password Baru</label>
            <input type="password" name="new_password" id="new_password" class="tyy-input"
                   placeholder="Minimal 5 karakter" required>
          </div>

          <div class="tyy-form-group">
            <label class="tyy-label" for="confirm_password">Konfirmasi Password Baru</label>
            <input type="password" name="confirm_password" id="confirm_password" class="tyy-input"
                   placeholder="Ulangi password baru" required>
          </div>

          <button type="submit" class="tyy-btn tyy-btn-primary tyy-btn-lg">
            🔒 Ubah Password
          </button>
        </form>
      </div>

    </div>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>

  <script>
    function switchTab(tab, el) {
      // Hide all panels
      document.getElementById('panelBiodata').style.display = 'none';
      document.getElementById('panelPassword').style.display = 'none';

      // Deactivate all tabs
      document.querySelectorAll('.tyy-profile-tab').forEach(t => t.classList.remove('active'));

      // Show selected panel & activate tab
      if(tab === 'biodata'){
        document.getElementById('panelBiodata').style.display = 'block';
      } else {
        document.getElementById('panelPassword').style.display = 'block';
      }
      el.classList.add('active');
    }
  </script>
</body>
</html>
