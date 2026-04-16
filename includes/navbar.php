<?php
// includes/navbar.php - Tikecting.yuu Navigation Bar
$base = get_base_url();
$currentPage = $_SERVER['PHP_SELF'];
$isAdmin = is_admin();
$isCustomer = is_customer();
?>
<?php if($isAdmin): ?>
<!-- ADMIN SIDEBAR LAYOUT -->
<div class="tyy-admin-layout">
  <aside class="tyy-sidebar">
    <div class="tyy-sidebar-header">
      <a href="<?=$base?>/index.php" class="tyy-sidebar-brand">
        <div class="tyy-sidebar-brand-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 16v-2l-8-5V3.5a1.5 1.5 0 0 0-3 0V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
          </svg>
        </div>
        <div class="tyy-sidebar-brand-text">Tikecting<span>.yuu</span></div>
      </a>
    </div>

    <div class="tyy-sidebar-user">
      <div class="tyy-sidebar-user-avatar"><?=get_user_initials()?></div>
      <div class="tyy-sidebar-user-info">
        <span class="tyy-sidebar-user-name"><?=e($_SESSION['user']['name'])?></span>
        <span class="tyy-sidebar-user-role">Administrator</span>
      </div>
    </div>

    <nav class="tyy-sidebar-nav">
      <div class="tyy-sidebar-nav-label">Main Menu</div>
      <a href="<?=$base?>/admin/dashboard.php" class="tyy-sidebar-link <?=strpos($currentPage, 'dashboard') !== false ? 'active' : ''?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="7" height="7"></rect>
          <rect x="14" y="3" width="7" height="7"></rect>
          <rect x="14" y="14" width="7" height="7"></rect>
          <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
        Dashboard
      </a>
      <a href="<?=$base?>/admin/tickets.php" class="tyy-sidebar-link <?=strpos($currentPage, 'tickets') !== false ? 'active' : ''?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/>
        </svg>
        Kelola Tiket
      </a>
      <a href="<?=$base?>/admin/bookings.php" class="tyy-sidebar-link <?=strpos($currentPage, 'bookings') !== false ? 'active' : ''?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
          <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
        </svg>
        Daftar Booking
      </a>
      <a href="<?=$base?>/admin/confirm_transactions.php" class="tyy-sidebar-link <?=strpos($currentPage, 'confirm') !== false ? 'active' : ''?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <polyline points="16 11 18 13 22 9"/>
        </svg>
        Konfirmasi
        <span id="waiting-count-badge" class="tyy-sidebar-badge" style="display:none;"></span>
      </a>

      <div class="tyy-sidebar-nav-label" style="margin-top: 1.5rem;">Account</div>
      <a href="<?=$base?>/customer/profile.php" class="tyy-sidebar-link <?=strpos($currentPage, 'profile') !== false ? 'active' : ''?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
        Profil
      </a>
      <a href="<?=$base?>/logout.php" class="tyy-sidebar-link tyy-sidebar-link-logout">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        Logout
      </a>
    </nav>

    <div class="tyy-sidebar-footer">
      <a href="<?=$base?>/index.php" class="tyy-sidebar-footer-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        Kembali ke Beranda
      </a>
    </div>
  </aside>
  <main class="tyy-admin-main">
<?php else: ?>
<!-- CUSTOMER / GUEST NAVBAR CENTER LAYOUT -->
<nav class="tyy-navbar">
  <div class="tyy-navbar-inner">
    <!-- Brand -->
    <a href="<?=$base?>/index.php" class="tyy-brand">
      <div class="tyy-brand-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 16v-2l-8-5V3.5a1.5 1.5 0 0 0-3 0V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
        </svg>
      </div>
      <div class="tyy-brand-text">Tikecting<span>.yuu</span></div>
    </a>

    <?php if(is_logged_in()): ?>
    <!-- Navigation Links Center -->
    <div class="tyy-nav-links">
      <a href="<?=$base?>/customer/dashboard.php" class="tyy-nav-link <?=strpos($currentPage, 'dashboard') !== false ? 'active' : ''?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="7" height="7"></rect>
          <rect x="14" y="3" width="7" height="7"></rect>
          <rect x="14" y="14" width="7" height="7"></rect>
          <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
        Dashboard
      </a>
      <a href="<?=$base?>/customer/my_orders.php" class="tyy-nav-link <?=strpos($currentPage, 'my_orders') !== false ? 'active' : ''?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
          <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
        </svg>
        Pesanan Saya
      </a>
      <a href="<?=$base?>/customer/profile.php" class="tyy-nav-link <?=strpos($currentPage, 'profile') !== false ? 'active' : ''?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
        Profil
      </a>
    </div>
    <?php endif; ?>

    <!-- Right Side Actions -->
    <div class="tyy-nav-actions">
      <?php if(is_logged_in()): ?>
        <!-- User Menu -->
        <div class="tyy-user-menu">
          <div class="tyy-user-info">
            <div class="tyy-user-avatar"><?=get_user_initials()?></div>
            <div class="tyy-user-details">
              <span class="tyy-user-name"><?=e($_SESSION['user']['name'])?></span>
              <span class="tyy-user-role tyy-user-role-customer">Customer</span>
            </div>
          </div>
          <a href="<?=$base?>/logout.php" class="tyy-btn tyy-btn-logout">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>Logout</span>
          </a>
        </div>
      <?php else: ?>
        <a href="<?=$base?>/login.php" class="tyy-btn tyy-btn-login">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
            <polyline points="10 17 15 12 10 7"/>
            <line x1="15" y1="12" x2="3" y2="12"/>
          </svg>
          Masuk
        </a>
        <a href="<?=$base?>/register.php" class="tyy-btn tyy-btn-register">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="8.5" cy="7" r="4"/>
            <line x1="20" y1="8" x2="20" y2="14"/>
            <line x1="23" y1="11" x2="17" y2="11"/>
          </svg>
          Daftar
        </a>
      <?php endif; ?>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="tyy-mobile-toggle" id="mobileToggle" aria-label="Toggle menu">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>

  <!-- Mobile Menu -->
  <div class="tyy-mobile-menu" id="mobileMenu">
    <?php if(is_logged_in()): ?>
      <div class="tyy-mobile-user">
        <div class="tyy-user-avatar" style="width:48px;height:48px;font-size:1rem;"><?=get_user_initials()?></div>
        <div>
          <div class="tyy-user-name"><?=e($_SESSION['user']['name'])?></div>
          <div class="tyy-user-role tyy-user-role-customer">Customer</div>
        </div>
      </div>
      <div class="tyy-mobile-links">
        <a href="<?=$base?>/customer/dashboard.php" class="tyy-mobile-link">Dashboard</a>
        <a href="<?=$base?>/customer/my_orders.php" class="tyy-mobile-link">Pesanan Saya</a>
        <a href="<?=$base?>/customer/profile.php" class="tyy-mobile-link">Profil Saya</a>
      </div>
      <a href="<?=$base?>/logout.php" class="tyy-btn tyy-btn-logout tyy-btn-full" style="margin-top:1rem;">Logout</a>
    <?php else: ?>
      <a href="<?=$base?>/login.php" class="tyy-btn tyy-btn-login tyy-btn-full">Masuk</a>
      <a href="<?=$base?>/register.php" class="tyy-btn tyy-btn-register tyy-btn-full" style="margin-top:0.75rem;">Daftar</a>
    <?php endif; ?>
  </div>
</nav>
<?php endif; ?>

<script>
document.getElementById('mobileToggle')?.addEventListener('click', function() {
  this.classList.toggle('active');
  document.getElementById('mobileMenu')?.classList.toggle('active');
});
</script>
