<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - LastGrade</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Mobile Responsive */
    @media (max-width: 768px) {
      .calculator-container {
        padding: 30px 20px;
      }

      .calculator-container h1 {
        font-size: 24px;
      }

      .calculator-desc {
        font-size: 14px;
      }
    }

    @media (max-width: 480px) {
      .calculator-section {
        padding: 20px 15px;
      }

      .calculator-container {
        padding: 20px 15px;
        border-radius: 12px;
      }

      .calculator-container h1 {
        font-size: 20px;
        margin-bottom: 8px;
      }

      .calculator-desc {
        font-size: 13px;
        margin-bottom: 20px;
      }

      .calculator-form .form-group {
        margin-bottom: 15px;
      }

      .calculator-form label {
        font-size: 13px;
      }

      .calculator-form input {
        padding: 12px;
        font-size: 16px;
      }

      .calculator-form .btn-primary {
        padding: 14px;
        font-size: 15px;
      }

      .result-container {
        padding: 25px 15px;
        border-radius: 12px;
      }

      .result-container h2 {
        font-size: 18px;
      }

      .result-container .final-grade {
        font-size: 32px;
      }

      .result-container .grade-info {
        font-size: 16px;
      }

      .chart-container {
        padding: 20px 15px;
        border-radius: 12px;
      }

      .chart-container h2 {
        font-size: 18px;
      }

      .line-chart-wrapper {
        height: 180px;
      }
    }

    @media (max-width: 360px) {
      .calculator-container h1 {
        font-size: 18px;
      }

      .result-container .final-grade {
        font-size: 28px;
      }

      .result-container .grade-info {
        font-size: 14px;
      }
    }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar">
    <div class="logo">LastGrade</div>
    <ul class="nav-links">
      <li><a href="dashboard.php" class="active">Dashboard</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>

  <!-- CALCULATOR SECTION -->
  <section class="calculator-section">
    <div class="calculator-container">
      <h1>Kalkulator Nilai Akhir</h1>
      <p class="calculator-desc">Hitung nilai akhir mata pelajaran Anda dengan mudah berdasarkan bobot yang ditentukan.</p>
      
      <form class="calculator-form" id="gradeForm">
        <div class="form-group">
          <label for="tugas">Nilai Tugas (%)</label>
          <input type="number" id="tugas" name="tugas" min="0" max="100" placeholder="Masukkan nilai tugas" required>
        </div>
        
        <div class="form-group">
          <label for="bobot-tugas">Bobot Tugas (%)</label>
          <input type="number" id="bobot-tugas" name="bobot-tugas" min="0" max="100" value="30" required>
        </div>
        
        <div class="form-group">
          <label for="uts">Nilai UTS (%)</label>
          <input type="number" id="uts" name="uts" min="0" max="100" placeholder="Masukkan nilai UTS" required>
        </div>
        
        <div class="form-group">
          <label for="bobot-uts">Bobot UTS (%)</label>
          <input type="number" id="bobot-uts" name="bobot-uts" min="0" max="100" value="30" required>
        </div>
        
        <div class="form-group">
          <label for="uas">Nilai UAS (%)</label>
          <input type="number" id="uas" name="uas" min="0" max="100" placeholder="Masukkan nilai UAS" required>
        </div>
        
        <div class="form-group">
          <label for="bobot-uas">Bobot UAS (%)</label>
          <input type="number" id="bobot-uas" name="bobot-uas" min="0" max="100" value="40" required>
        </div>
        
        <button type="button" class="btn-primary" onclick="calculateGrade()">Hitung Nilai Akhir</button>
      </form>
    </div>
  </section>

  <!-- RESULT SECTION -->
  <section class="result-section" id="resultSection" style="display: none;">
    <div class="result-container">
      <h2>Hasil Perhitungan</h2>
      <p class="final-grade">Nilai Akhir: <span id="finalGrade">0</span></p>
      <p class="grade-info" id="gradeInfo"></p>
    </div>
  </section>

  <!-- CHART SECTION -->
  <section class="chart-section" id="chartSection" style="display: none;">
    <div class="chart-container">
      <h2>Grafik Nilai</h2>
      <div class="line-chart-wrapper">
        <canvas id="lineChart"></canvas>
      </div>
      <div class="chart-legend">
        <div class="legend-item"><span class="legend-color" style="background: #22c55e;"></span> Keseluruhan Nilai</div>
      </div>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    let lineChart = null;
    
    function calculateGrade() {
      const tugas = parseFloat(document.getElementById('tugas').value) || 0;
      const bobotTugas = parseFloat(document.getElementById('bobot-tugas').value) || 0;
      const uts = parseFloat(document.getElementById('uts').value) || 0;
      const bobotUts = parseFloat(document.getElementById('bobot-uts').value) || 0;
      const uas = parseFloat(document.getElementById('uas').value) || 0;
      const bobotUas = parseFloat(document.getElementById('bobot-uas').value) || 0;
      
      const totalBobot = bobotTugas + bobotUts + bobotUas;
      
      if (totalBobot !== 100) {
        alert('Total bobot harus 100%! Saat ini: ' + totalBobot + '%');
        return;
      }
      
      const nilaiAkhir = (tugas * bobotTugas / 100) + (uts * bobotUts / 100) + (uas * bobotUas / 100);
      
      // Show result section
      const resultSection = document.getElementById('resultSection');
      const finalGradeSpan = document.getElementById('finalGrade');
      const gradeInfoP = document.getElementById('gradeInfo');
      
      finalGradeSpan.textContent = nilaiAkhir.toFixed(2);
      
      let gradeInfo = '';
      let gradeColor = '#22c55e';
      if (nilaiAkhir >= 90) {
        gradeInfo = 'Grade: A (Sangat Baik)';
        gradeColor = '#22c55e';
      } else if (nilaiAkhir >= 80) {
        gradeInfo = 'Grade: B (Baik)';
        gradeColor = '#3b82f6';
      } else if (nilaiAkhir >= 70) {
        gradeInfo = 'Grade: C (Cukup)';
        gradeColor = '#facc15';
      } else if (nilaiAkhir >= 60) {
        gradeInfo = 'Grade: D (Kurang)';
        gradeColor = '#f97316';
      } else {
        gradeInfo = 'Grade: E (Sangat Kurang)';
        gradeColor = '#ef4444';
      }
      
      gradeInfoP.textContent = gradeInfo;
      gradeInfoP.style.color = gradeColor;
      
      resultSection.style.display = 'block';
      resultSection.style.animation = 'slideUp 0.5s ease-out';
      
      // Show chart section
      const chartSection = document.getElementById('chartSection');
      chartSection.style.display = 'block';
      chartSection.style.animation = 'slideUp 0.5s ease-out';
      
      // Auto scroll to result
      resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
      
      // Update line chart
      updateLineChart(tugas, uts, uas, nilaiAkhir);
    }
    
    function updateLineChart(tugas, uts, uas, nilaiAkhir) {
      const ctx = document.getElementById('lineChart').getContext('2d');
      
      if (lineChart) {
        lineChart.destroy();
      }
      
      const isMobile = window.innerWidth <= 480;
      
      lineChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['Tugas', 'UTS', 'UAS'],
          datasets: [
            {
              label: 'Keseluruhan Nilai',
              data: [tugas, uts, uas],
              borderColor: '#22c55e',
              backgroundColor: 'rgba(34, 197, 94, 0.1)',
              tension: 0.4,
              fill: true,
              pointRadius: isMobile ? 4 : 6,
              pointHoverRadius: isMobile ? 6 : 8,
              borderWidth: isMobile ? 2 : 3
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            intersect: false,
            mode: 'index',
          },
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              ticks: {
                callback: function(value) {
                  return value + '%';
                },
                stepSize: 20,
                font: {
                  size: isMobile ? 10 : 12
                }
              },
              grid: {
                display: true,
                drawBorder: false,
                color: 'rgba(0,0,0,0.05)'
              }
            },
            x: {
              ticks: {
                font: {
                  size: isMobile ? 10 : 12
                }
              },
              grid: {
                display: false
              }
            }
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              enabled: true,
              backgroundColor: 'rgba(0,0,0,0.8)',
              titleFont: {
                size: isMobile ? 12 : 14
              },
              bodyFont: {
                size: isMobile ? 11 : 13
              },
              padding: isMobile ? 8 : 12,
              cornerRadius: isMobile ? 4 : 8
            }
          }
        }
      });
    }
  </script>

  <!-- FOOTER -->
  <footer>
    <p>© 2026 All Rights Reserved - LastGrade</p>
  </footer>
</body>
</html>
