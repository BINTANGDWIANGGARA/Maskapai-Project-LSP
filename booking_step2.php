<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$flight_id = $_POST['flight_id'];
$passengers_count = $_POST['passengers_count'];
$passengers_data = $_POST['passenger'];

// Simpan data sementara di session
$_SESSION['temp_booking'] = [
    'flight_id' => $flight_id,
    'passengers_count' => $passengers_count,
    'passengers_data' => $passengers_data,
    'emergency_name' => $_POST['emergency_name'],
    'emergency_phone' => $_POST['emergency_phone'],
];

$stmt = $pdo->prepare("SELECT * FROM flights WHERE id = ?");
$stmt->execute([$flight_id]);
$f = $stmt->fetch();

$pageTitle = 'Pilih Kursi — Tikecting.yuu';
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
      <div class="step">1. Data Penumpang</div>
      <div class="step active">2. Pilih Kursi</div>
      <div class="step">3. Add-ons</div>
      <div class="step">4. Pembayaran</div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 400px; gap: 2.5rem; align-items: start;">
      <!-- Seat Map Section -->
      <div class="tyy-card" style="padding:2.5rem; text-align:center;">
        <h3 style="margin-bottom:2rem;">Pilih Kursi Penumpang</h3>
        
        <div class="seat-map-container" style="max-width:300px; margin:0 auto; background:rgba(255,255,255,0.02); padding:2rem; border-radius:var(--radius-xl); border:1px solid var(--border-subtle);">
          <div class="plane-front" style="height:60px; background:var(--bg-surface); border-radius:100% 100% 0 0; margin-bottom:2rem; display:flex; align-items:center; justify-content:center; font-weight:700; color:var(--text-muted); font-size:0.8rem; border:1px solid var(--border-subtle); border-bottom:none;">KOKPIT</div>
          
          <div class="seat-grid" style="display:grid; grid-template-columns: repeat(4, 1fr); gap:12px; position:relative;">
            <!-- Seats Row 1-10 (Simplified for Demo) -->
            <?php 
            $rows = 10;
            $cols = ['A', 'B', 'C', 'D'];
            for($r=1; $r<=$rows; $r++):
              foreach($cols as $index => $c):
                $seat_num = $r . $c;
                $is_aisle = ($index == 1); // Between B and C
                $is_window = ($index == 0 || $index == 3);
                $is_premium = ($r <= 2);
                $is_taken = (rand(1, 10) > 8); // Randomly mark some as taken
            ?>
                <div class="seat-item <?= $is_taken ? 'taken' : '' ?> <?= $is_premium ? 'premium' : '' ?> <?= $is_aisle ? 'aisle-margin' : '' ?>" 
                     data-seat="<?= $seat_num ?>" 
                     onclick="selectSeat('<?= $seat_num ?>', this)"
                     title="Kursi <?= $seat_num ?><?= $is_premium ? ' (Premium)' : '' ?>">
                  <?= $seat_num ?>
                </div>
            <?php endforeach; endfor; ?>
          </div>
        </div>

        <div class="seat-legend" style="display:flex; justify-content:center; gap:20px; margin-top:2.5rem; flex-wrap:wrap;">
          <div class="legend-item"><span class="seat-box available"></span> Tersedia</div>
          <div class="legend-item"><span class="seat-box selected"></span> Dipilih</div>
          <div class="legend-item"><span class="seat-box premium"></span> Premium</div>
          <div class="legend-item"><span class="seat-box taken"></span> Terisi</div>
        </div>
      </div>

      <!-- Selection Sidebar -->
      <aside class="tyy-selection-sidebar">
        <div class="tyy-card" style="padding:1.75rem; position:sticky; top:100px;">
          <h4 style="margin-bottom:1.5rem;">Pemilihan Kursi</h4>
          
          <form action="booking_step3.php" method="POST" id="seatForm">
            <div id="selected-seats-list">
              <?php for($i=1; $i<=$passengers_count; $i++): ?>
                <div class="passenger-seat-select" style="margin-bottom:1.25rem; padding-bottom:1.25rem; border-bottom:1px solid var(--border-subtle);">
                  <div style="font-weight:700; font-size:0.9rem; margin-bottom:8px;">Penumpang #<?=$i?>: <?=e($passengers_data[$i]['name'])?></div>
                  <input type="text" name="seat[<?=$i?>]" class="tyy-input seat-input" placeholder="Pilih di peta kursi" readonly required>
                </div>
              <?php endfor; ?>
            </div>

            <button type="submit" class="tyy-btn tyy-btn-primary tyy-btn-full tyy-btn-lg" style="margin-top:1rem;">Lanjut ke Add-ons →</button>
          </form>
        </div>
      </aside>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>

  <script>
    let currentPassenger = 1;
    const totalPassengers = <?= $passengers_count ?>;
    const seatInputs = document.querySelectorAll('.seat-input');

    function selectSeat(seatNum, element) {
      if (element.classList.contains('taken')) return;

      // Check if seat already selected by another passenger
      let alreadySelected = false;
      seatInputs.forEach(input => {
        if (input.value === seatNum) alreadySelected = true;
      });
      if (alreadySelected) {
        // Deselect if clicking the same seat
        seatInputs.forEach(input => {
          if (input.value === seatNum) {
            input.value = '';
            element.classList.remove('selected');
          }
        });
        return;
      }

      // Find first empty input
      let targetInput = null;
      for (let input of seatInputs) {
        if (!input.value) {
          targetInput = input;
          break;
        }
      }

      if (targetInput) {
        targetInput.value = seatNum;
        element.classList.add('selected');
      } else {
        alert("Semua penumpang sudah memiliki kursi. Batalkan satu kursi untuk mengganti.");
      }
    }
  </script>

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

    .seat-item {
      width: 40px;
      height: 40px;
      background: var(--bg-surface);
      border: 1px solid var(--border-medium);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.75rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
      color: var(--text-muted);
    }
    .seat-item:hover {
      border-color: var(--gray-300);
      background: var(--bg-card-hover);
      color: var(--text-primary);
    }
    .seat-item.taken {
      background: var(--bg-elevated);
      color: var(--text-disabled);
      cursor: not-allowed;
      border-color: transparent;
      opacity: 0.5;
    }
    .seat-item.premium {
      border-color: var(--gray-400);
      box-shadow: 0 0 10px rgba(200,200,200,0.1);
    }
    .seat-item.selected {
      background: var(--gradient-silver);
      color: var(--text-inverse);
      border: none;
      transform: scale(1.1);
      box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    }
    .aisle-margin {
      margin-right: 20px;
    }

    .legend-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.85rem;
      color: var(--text-secondary);
    }
    .seat-box {
      width: 16px;
      height: 16px;
      border-radius: 4px;
      border: 1px solid var(--border-medium);
    }
    .seat-box.available { background: var(--bg-surface); }
    .seat-box.selected { background: var(--gradient-silver); border: none; }
    .seat-box.premium { border-color: var(--gray-400); }
    .seat-box.taken { background: var(--bg-elevated); border: none; opacity: 0.5; }
  </style>
</body>
</html>
