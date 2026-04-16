<?php
require_once 'config.php';
if(is_logged_in()) redirect('index.php');

$err = '';
$success = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $name      = trim($_POST['name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $nik       = trim($_POST['nik'] ?? '');
    $gender    = $_POST['gender'] ?? null;
    $birthdate = $_POST['birthdate'] ?? null;

    if(empty($username) || empty($password) || empty($name)){
        $err = 'Username, password, dan nama lengkap wajib diisi.';
    } elseif(strlen($password) < 5){
        $err = 'Password minimal 5 karakter.';
    } else {
        // Check if username exists
        $check = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $check->execute([$username]);
        if($check->fetch()){
            $err = 'Username sudah digunakan. Pilih username lain.';
        } else {
            $hashed = hash('sha256', $password);
            $ins = $pdo->prepare('INSERT INTO users (username, password, name, email, phone, nik, gender, birth_date, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $ins->execute([$username, $hashed, $name, $email, $phone, $nik ?: null, $gender ?: null, $birthdate ?: null, 'customer']);
            
            // Auto-login after registration
            if(login($username, $password)) {
                $showTerms = true;
            } else {
                $success = 'Akun berhasil dibuat! Silakan login.';
            }
        }
    }
}

$pageTitle = 'Daftar Akun — Tikecting.yuu';
?>
<!doctype html>
<html lang="id">
<head>
  <?php include 'includes/head.php'; ?>
</head>
<body>
  <div class="tyy-auth-wrapper">
    <div class="tyy-auth-card" style="max-width:520px;">
      <div class="tyy-card fade-in" style="padding:2.5rem;">
        <!-- Logo -->
        <div class="tyy-auth-logo">
          <a href="index.php" class="tyy-brand" style="justify-content:center;">
            <div class="tyy-brand-icon" style="width:48px;height:48px;font-size:1.4rem;border-radius:14px;">✈</div>
          </a>
          <div class="tyy-brand-text" style="font-size:1.5rem;margin-top:12px;text-align:center;">Tikecting<span>.yuu</span></div>
        </div>

        <h3 style="text-align:center;margin-bottom:6px;">Buat Akun Baru</h3>
        <p style="text-align:center;color:var(--text-secondary);font-size:0.9rem;margin-bottom:2rem;">
          Daftar dan lengkapi biodata untuk memudahkan pemesanan tiket
        </p>

        <?php if($err): ?>
          <div class="tyy-alert tyy-alert-error"><span>⚠️</span> <?=e($err)?></div>
        <?php endif; ?>
        <?php if($success): ?>
          <div class="tyy-alert tyy-alert-success"><span>✅</span> <?=e($success)?></div>
        <?php endif; ?>

        <form method="post" id="registerForm">
          <!-- Section: Akun -->
          <div class="tyy-order-section-title" style="margin-top:0;">
            <span>🔐</span> Data Akun
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="tyy-form-group">
              <label class="tyy-label" for="username">Username <span style="color:var(--brand-accent);">*</span></label>
              <input type="text" name="username" id="username" class="tyy-input" placeholder="Pilih username" required
                     value="<?=e($_POST['username'] ?? '')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label" for="password">Password <span style="color:var(--brand-accent);">*</span></label>
              <input type="password" name="password" id="password" class="tyy-input" placeholder="Min 5 karakter" required>
            </div>
          </div>

          <!-- Section: Biodata -->
          <div class="tyy-order-section-title">
            <span>👤</span> Biodata Pribadi
          </div>

          <div class="tyy-form-group">
            <label class="tyy-label" for="name">Nama Lengkap (sesuai KTP) <span style="color:var(--brand-accent);">*</span></label>
            <input type="text" name="name" id="name" class="tyy-input" placeholder="Nama lengkap Anda" required
                   value="<?=e($_POST['name'] ?? '')?>">
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="tyy-form-group">
              <label class="tyy-label" for="nik">NIK (No. KTP)</label>
              <input type="text" name="nik" id="nik" class="tyy-input" placeholder="16 digit NIK" maxlength="20"
                     value="<?=e($_POST['nik'] ?? '')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label" for="gender">Jenis Kelamin</label>
              <select name="gender" id="gender" class="tyy-input tyy-select-dropdown">
                <option value="">— Pilih —</option>
                <option value="Laki-laki" <?=($_POST['gender'] ?? '') === 'Laki-laki' ? 'selected' : ''?>>👨 Laki-laki</option>
                <option value="Perempuan" <?=($_POST['gender'] ?? '') === 'Perempuan' ? 'selected' : ''?>>👩 Perempuan</option>
              </select>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="tyy-form-group">
              <label class="tyy-label" for="birthdate">Tanggal Lahir</label>
              <input type="date" name="birthdate" id="birthdate" class="tyy-input"
                     value="<?=e($_POST['birthdate'] ?? '')?>">
            </div>
            <div class="tyy-form-group">
              <label class="tyy-label" for="phone">No. Telepon</label>
              <input type="text" name="phone" id="phone" class="tyy-input" placeholder="08xxxxxxxxxx"
                     value="<?=e($_POST['phone'] ?? '')?>">
            </div>
          </div>

          <div class="tyy-form-group">
            <label class="tyy-label" for="email">Email</label>
            <input type="email" name="email" id="email" class="tyy-input" placeholder="email@contoh.com"
                   value="<?=e($_POST['email'] ?? '')?>">
          </div>

          <button type="submit" class="tyy-btn tyy-btn-primary tyy-btn-lg tyy-btn-full" style="margin-top:0.5rem;">
            Daftar Sekarang →
          </button>
        </form>

        <div class="tyy-auth-footer">
          Sudah punya akun? <a href="login.php">Masuk di sini</a>
        </div>
      </div>
    </div>
  </div>

  <?php if(isset($showTerms) && $showTerms): ?>
    <!-- Terms and Conditions Modal -->
    <div class="tyy-modal-overlay" id="termsModal">
      <div class="tyy-modal-box fade-in">
        <div class="tyy-modal-header">
          <div class="icon">📜</div>
          <h3>Syarat & Ketentuan Penggunaan</h3>
        </div>
        <div class="tyy-modal-body">
          <div class="tyy-terms-content">
            <p>Selamat datang di <strong>Tikecting.yuu</strong>! Sebelum Anda mulai memesan tiket, harap baca syarat dan ketentuan berikut:</p>
            <ul>
              <li>Anda bertanggung jawab penuh atas keakuratan data yang dimasukkan.</li>
              <li>Pemesanan tiket hanya dianggap sah setelah pembayaran diverifikasi oleh sistem.</li>
              <li>Tiket yang sudah diterbitkan tidak dapat dibatalkan secara sepihak kecuali melalui prosedur refund yang tersedia.</li>
              <li>Sistem menggunakan enkripsi modern untuk menjaga keamanan data pribadi Anda.</li>
              <li>Penggunaan platform ini tunduk pada hukum yang berlaku di Indonesia.</li>
            </ul>
            <p>Dengan menekan tombol setuju atau menunggu waktu habis, Anda dianggap telah membaca dan menyetujui seluruh persyaratan kami.</p>
          </div>
        </div>
        <div class="tyy-modal-footer">
          <div class="tyy-countdown-text">
            Mengarahkan ke Dashboard dalam <span id="countdown">5</span> detik...
          </div>
          <a href="customer/dashboard.php" class="tyy-btn tyy-btn-primary tyy-btn-sm">Setuju & Masuk Sekarang →</a>
        </div>
      </div>
    </div>

    <style>
      .tyy-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
      }
      .tyy-modal-box {
        background: var(--bg-card);
        width: 100%;
        max-width: 550px;
        border-radius: var(--radius-xl);
        border: 1px solid var(--border-light);
        box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        overflow: hidden;
      }
      .tyy-modal-header {
        padding: 2rem 2rem 1.5rem;
        text-align: center;
        border-bottom: 1px solid var(--border-subtle);
      }
      .tyy-modal-header .icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
      }
      .tyy-modal-body {
        padding: 2rem;
        max-height: 400px;
        overflow-y: auto;
      }
      .tyy-terms-content {
        color: var(--text-secondary);
        font-size: 0.9rem;
        line-height: 1.7;
      }
      .tyy-terms-content ul {
        margin: 1.5rem 0;
        padding-left: 1.5rem;
      }
      .tyy-terms-content li {
        margin-bottom: 0.75rem;
      }
      .tyy-modal-footer {
        padding: 1.5rem 2rem 2rem;
        background: rgba(255,255,255,0.02);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.5rem;
        border-top: 1px solid var(--border-subtle);
      }
      .tyy-countdown-text {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 600;
      }
      #countdown {
        color: var(--text-primary);
        font-weight: 800;
        font-size: 1.1rem;
      }
    </style>

    <script>
      let seconds = 5;
      const countdownEl = document.getElementById('countdown');
      
      const timer = setInterval(() => {
        seconds--;
        countdownEl.innerText = seconds;
        
        if (seconds <= 0) {
          clearInterval(timer);
          window.location.href = 'customer/dashboard.php';
        }
      }, 1000);
    </script>
  <?php endif; ?>
</body>
</html>
