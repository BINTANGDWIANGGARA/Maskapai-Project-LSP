<?php
require_once 'config.php';
if(is_logged_in()){
    if(is_admin()) redirect('admin/dashboard.php');
    else redirect('index.php');
}

$err = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if(empty($u) || empty($p)){
        $err = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$u]);
        $user = $stmt->fetch();

        if($user && hash('sha256', $p) === $user['password']){
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'role' => $user['role']
            ];
            if($user['role'] === 'admin'){
                redirect('admin/dashboard.php');
            } else {
                redirect('index.php');
            }
        } else {
            $err = 'Login gagal — periksa kembali username dan password Anda.';
        }
    }
}

$pageTitle = 'Masuk — Tikecting.yuu';
?>
<!doctype html>
<html lang="id">
<head>
  <?php include 'includes/head.php'; ?>
</head>
<body>
  <div class="tyy-auth-wrapper">
    <div class="tyy-auth-card">
      <div class="tyy-card fade-in" style="padding:2.5rem;">
        <!-- Logo -->
        <div class="tyy-auth-logo">
          <a href="index.php" class="tyy-brand" style="justify-content:center;">
            <div class="tyy-brand-icon" style="width:48px;height:48px;font-size:1.4rem;border-radius:14px;">✈</div>
          </a>
          <div class="tyy-brand-text" style="font-size:1.5rem;margin-top:12px;text-align:center;">Tikecting<span>.yuu</span></div>
        </div>

        <h3 style="text-align:center;margin-bottom:6px;">Selamat Datang Kembali</h3>
        <p style="text-align:center;color:var(--text-secondary);font-size:0.9rem;margin-bottom:2rem;">
          Masuk ke akun Anda untuk melanjutkan
        </p>

        <?php if($err): ?>
          <div class="tyy-alert tyy-alert-error">
            <span>⚠️</span> <?=e($err)?>
          </div>
        <?php endif; ?>

        <form method="post" id="loginForm">
          <div class="tyy-form-group">
            <label class="tyy-label" for="username">Username</label>
            <input type="text" name="username" id="username" class="tyy-input" placeholder="Masukkan username" required autofocus
                   value="<?=e($_POST['username'] ?? '')?>">
          </div>

          <div class="tyy-form-group">
            <label class="tyy-label" for="password">Password</label>
            <input type="password" name="password" id="password" class="tyy-input" placeholder="Masukkan password" required>
          </div>

          <button type="submit" class="tyy-btn tyy-btn-primary tyy-btn-lg tyy-btn-full" style="margin-top:0.5rem;">
            Masuk →
          </button>
        </form>

        <div class="tyy-auth-footer">
          Belum punya akun? <a href="register.php">Daftar sekarang</a>
        </div>

        <hr class="tyy-divider">

        <div style="text-align:center;font-size:0.78rem;color:var(--text-muted);">
          <strong>Demo Login:</strong><br>
          Admin: <code style="color:var(--brand-primary-light);">admin</code> / <code style="color:var(--brand-primary-light);">admin123</code><br>
          Customer: <code style="color:var(--brand-secondary);">cust</code> / <code style="color:var(--brand-secondary);">cust123</code>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
