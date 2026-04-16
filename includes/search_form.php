<?php
// includes/search_form.php - Advanced Flight Search Component
$origin = $_GET['origin'] ?? '';
$destination = $_GET['destination'] ?? '';
$depart_date = $_GET['depart_date'] ?? '';
$return_date = $_GET['return_date'] ?? '';
$passengers = $_GET['passengers'] ?? '1';
$class = $_GET['class'] ?? 'economy';
?>

<form action="search_results.php" method="GET" class="tyy-advanced-search-form">
  <div class="tyy-search-grid">
    <!-- Row 1: Origins & Destination -->
    <div class="tyy-search-field">
      <label class="tyy-label">Kota Asal</label>
      <div class="tyy-input-with-icon">
        <span class="icon">🛫</span>
        <input type="text" name="origin" class="tyy-input" placeholder="Jakarta (CGK)" value="<?=e($origin)?>" required>
      </div>
    </div>

    <div class="tyy-search-field">
      <label class="tyy-label">Kota Tujuan</label>
      <div class="tyy-input-with-icon">
        <span class="icon">🛬</span>
        <input type="text" name="destination" class="tyy-input" placeholder="Surabaya (SUB)" value="<?=e($destination)?>" required>
      </div>
    </div>

    <!-- Row 2: Dates -->
    <div class="tyy-search-field">
      <label class="tyy-label">Tanggal Berangkat</label>
      <div class="tyy-input-with-icon">
        <span class="icon">📅</span>
        <input type="date" name="depart_date" class="tyy-input" value="<?=e($depart_date)?>" required>
      </div>
    </div>

    <div class="tyy-search-field">
      <label class="tyy-label">Tanggal Pulang (Opsional)</label>
      <div class="tyy-input-with-icon">
        <span class="icon">📅</span>
        <input type="date" name="return_date" class="tyy-input" value="<?=e($return_date)?>">
      </div>
    </div>

    <!-- Row 3: Passengers & Class -->
    <div class="tyy-search-field">
      <label class="tyy-label">Penumpang</label>
      <div class="tyy-input-with-icon">
        <span class="icon">👤</span>
        <select name="passengers" class="tyy-input">
          <?php for($i=1; $i<=10; $i++): ?>
            <option value="<?=$i?>" <?=$passengers == $i ? 'selected' : ''?>><?=$i?> Penumpang</option>
          <?php endfor; ?>
        </select>
      </div>
    </div>

    <div class="tyy-search-field">
      <label class="tyy-label">Kelas</label>
      <div class="tyy-input-with-icon">
        <span class="icon">✨</span>
        <select name="class" class="tyy-input">
          <option value="economy" <?=$class == 'economy' ? 'selected' : ''?>>Economy</option>
          <option value="business" <?=$class == 'business' ? 'selected' : ''?>>Business</option>
          <option value="first" <?=$class == 'first' ? 'selected' : ''?>>First Class</option>
        </select>
      </div>
    </div>
  </div>

  <div class="tyy-search-actions">
    <div class="tyy-search-options">
      <label class="tyy-checkbox-label">
        <input type="checkbox" name="flexible_date" value="1"> Fleksibel Tanggal
      </label>
      <a href="#" class="tyy-link-sm">✈ Multi-city</a>
    </div>
    <button type="submit" class="tyy-btn tyy-btn-primary tyy-btn-lg">Cari Tiket Penerbangan →</button>
  </div>
</form>

<style>
.tyy-advanced-search-form {
  background: var(--bg-card);
  padding: 2.5rem;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-subtle);
  box-shadow: var(--shadow-elevated);
  margin-top: -3rem;
  position: relative;
  z-index: 10;
}

.tyy-search-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
}

@media (max-width: 768px) {
  .tyy-search-grid {
    grid-template-columns: 1fr;
  }
}

.tyy-input-with-icon {
  position: relative;
}

.tyy-input-with-icon .icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1.1rem;
  pointer-events: none;
}

.tyy-input-with-icon .tyy-input {
  padding-left: 44px;
}

.tyy-search-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
  flex-wrap: wrap;
}

.tyy-search-options {
  display: flex;
  align-items: center;
  gap: 20px;
}

.tyy-checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  color: var(--text-secondary);
  cursor: pointer;
}

.tyy-link-sm {
  font-size: 0.85rem;
  color: var(--gray-400);
  font-weight: 600;
}

.tyy-link-sm:hover {
  color: var(--text-primary);
}
</style>
