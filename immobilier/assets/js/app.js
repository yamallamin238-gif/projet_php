/* ===== ImmoGest Pro – app.js ===== */

document.addEventListener('DOMContentLoaded', () => {

  // ── Graphique Recettes ──────────────────────────────
  const ctxR = document.getElementById('chartRecettes');
  if (ctxR) {
    new Chart(ctxR, {
      type: 'line',
      data: {
        labels: ['Janv','Févr','Mars','Avr','Mai','Juin','Juil'],
        datasets: [{
          label: 'Recettes (FCFA)',
          data: [3200000, 3450000, 3100000, 3800000, 4100000, 3950000, 4250000],
          fill: true,
          borderColor: '#d4a017',
          backgroundColor: 'rgba(212,160,23,.1)',
          tension: .45,
          pointBackgroundColor: '#d4a017',
          pointBorderColor: '#0d1117',
          pointBorderWidth: 2,
          pointRadius: 5,
          pointHoverRadius: 7,
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#7d8590', font: { size: 12 } } },
          y: {
            grid: { color: 'rgba(255,255,255,.04)' },
            ticks: {
              color: '#7d8590', font: { size: 11 },
              callback: v => (v/1000000).toFixed(1) + 'M'
            }
          }
        }
      }
    });
  }

  // ── Doughnut Occupation ─────────────────────────────
  const ctxO = document.getElementById('chartOccupation');
  if (ctxO) {
    new Chart(ctxO, {
      type: 'doughnut',
      data: {
        labels: ['Occupés', 'Vacants'],
        datasets: [{
          data: [18, 6],
          backgroundColor: ['#d4a017', 'rgba(255,255,255,.07)'],
          borderColor:      ['#d4a017', 'rgba(255,255,255,.1)'],
          borderWidth: 2,
          hoverOffset: 6,
        }]
      },
      options: {
        cutout: '72%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: '#7d8590',
              padding: 16,
              font: { size: 12 },
              usePointStyle: true,
              pointStyleWidth: 8,
            }
          }
        }
      }
    });
  }

  // ── Compteurs animés ────────────────────────────────
  document.querySelectorAll('.stat-value').forEach(el => {
    const raw = el.textContent.replace(/\s/g, '').replace(/[^\d]/g, '');
    if (!raw) return;
    const target = parseInt(raw);
    const suffix = el.textContent.includes(' ') ? ' ' + el.textContent.split(' ').slice(1).join(' ') : '';
    let current = 0;
    const step = Math.ceil(target / 60);
    const timer = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = current.toLocaleString('fr-FR') + suffix;
      if (current >= target) clearInterval(timer);
    }, 20);
  });

  // ── Hamburger mobile ───────────────────────────────
  const hamburger = document.getElementById('hamburger');
  const sidebar   = document.querySelector('.sidebar');
  if (hamburger && sidebar) {
    hamburger.addEventListener('click', () => sidebar.classList.toggle('open'));
  }

});

// ── Mode clair / sombre ─────────────────────────────
function toggleTheme() {
  const body = document.body;
  const icon = document.getElementById('themeIcon');
  const isLight = body.classList.toggle('light-mode');
  localStorage.setItem('theme', isLight ? 'light' : 'dark');
  if (icon) {
    icon.className = isLight ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
  }
}

// Appliquer le thème sauvegardé au chargement
(function() {
  if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
    const icon = document.getElementById('themeIcon');
    if (icon) icon.className = 'fa-solid fa-moon';
  }
})();
