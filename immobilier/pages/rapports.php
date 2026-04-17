<?php
require_once __DIR__ . '/../config/app.php';
requireLogin();
$currentUser = getCurrentUser();
$pageTitle   = 'Rapports — ' . APP_NAME;
$db = getDB();

$annee = (int)($_GET['annee'] ?? date('Y'));

// Recettes par mois
$recettes = $db->prepare("
    SELECT MONTH(date_paiement) AS mois, SUM(montant_paye) AS total
    FROM quittances
    WHERE YEAR(date_paiement) = ? AND statut IN ('paye','partiel')
    GROUP BY mois ORDER BY mois
");
$recettes->execute([$annee]);
$recettesData = array_fill(1, 12, 0);
foreach ($recettes->fetchAll() as $r) $recettesData[$r['mois']] = (float)$r['total'];

// Charges par type
$chargesType = $db->prepare("
    SELECT type, SUM(montant) AS total
    FROM charges WHERE YEAR(date_charge) = ?
    GROUP BY type ORDER BY total DESC
");
$chargesType->execute([$annee]);
$chargesType = $chargesType->fetchAll();

// Taux occupation
$tauxOcc = $db->query("
    SELECT COUNT(*) AS total,
           SUM(CASE WHEN statut='occupe' THEN 1 ELSE 0 END) AS occupes
    FROM logements
")->fetch();
$taux = $tauxOcc['total'] > 0 ? round($tauxOcc['occupes'] / $tauxOcc['total'] * 100) : 0;

// Top locataires impayés
$topImpayes = $db->query("
    SELECT CONCAT(l.prenom,' ',l.nom) AS locataire,
           SUM(q.montant_total - q.montant_paye) AS dette
    FROM quittances q
    JOIN contrats c ON q.contrat_id = c.id
    JOIN locataires l ON c.locataire_id = l.id
    WHERE q.statut IN ('retard','partiel','en_attente')
    GROUP BY l.id ORDER BY dette DESC LIMIT 5
")->fetchAll();

$totalRecettes = array_sum($recettesData);
$totalCharges  = $db->prepare("SELECT COALESCE(SUM(montant),0) FROM charges WHERE YEAR(date_charge)=?");
$totalCharges->execute([$annee]);
$totalCharges = (float)$totalCharges->fetchColumn();
$benefice = $totalRecettes - $totalCharges;

$moisNoms = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-bar-chart me-2 text-primary"></i>Rapports</h4>
  <form method="GET" class="d-flex gap-2">
    <select name="annee" class="form-select form-select-sm" style="width:auto">
      <?php for ($y = date('Y'); $y >= date('Y')-5; $y--): ?>
      <option value="<?= $y ?>" <?= $annee==$y?'selected':'' ?>><?= $y ?></option>
      <?php endfor; ?>
    </select>
    <button class="btn btn-outline-primary btn-sm">Filtrer</button>
  </form>
</div>

<!-- KPI -->
<div class="row g-4 mb-4">
  <div class="col-sm-4">
    <div class="card border-0 shadow-sm text-center">
      <div class="card-body">
        <div class="fs-4 fw-bold text-success"><?= formatMontant($totalRecettes) ?></div>
        <div class="text-muted small">Recettes <?= $annee ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card border-0 shadow-sm text-center">
      <div class="card-body">
        <div class="fs-4 fw-bold text-danger"><?= formatMontant($totalCharges) ?></div>
        <div class="text-muted small">Charges <?= $annee ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card border-0 shadow-sm text-center">
      <div class="card-body">
        <div class="fs-4 fw-bold <?= $benefice >= 0 ? 'text-primary' : 'text-danger' ?>"><?= formatMontant($benefice) ?></div>
        <div class="text-muted small">Bénéfice net <?= $annee ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Graphique recettes -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 pt-4 pb-0">
        <h6 class="fw-bold">Recettes mensuelles <?= $annee ?></h6>
      </div>
      <div class="card-body">
        <canvas id="chartRecettes" height="120"></canvas>
      </div>
    </div>
  </div>

  <!-- Taux d'occupation -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body text-center d-flex flex-column justify-content-center">
        <h6 class="fw-bold mb-3">Taux d'occupation</h6>
        <div class="display-1 fw-bold <?= $taux >= 75 ? 'text-success' : ($taux >= 50 ? 'text-warning' : 'text-danger') ?>">
          <?= $taux ?>%
        </div>
        <div class="text-muted mt-2"><?= $tauxOcc['occupes'] ?> / <?= $tauxOcc['total'] ?> logements occupés</div>
        <div class="progress mt-3" style="height:10px">
          <div class="progress-bar <?= $taux >= 75 ? 'bg-success' : ($taux >= 50 ? 'bg-warning' : 'bg-danger') ?>"
               style="width:<?= $taux ?>%"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Top impayés -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 pt-4 pb-0">
        <h6 class="fw-bold">Top impayés</h6>
      </div>
      <div class="card-body">
        <?php if (empty($topImpayes)): ?>
          <p class="text-center text-muted py-3"><i class="bi bi-check-circle text-success fs-2 d-block mb-2"></i>Aucun impayé</p>
        <?php else: ?>
        <?php foreach ($topImpayes as $i): ?>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span><?= htmlspecialchars($i['locataire']) ?></span>
          <strong class="text-danger"><?= formatMontant($i['dette']) ?></strong>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Charges par type -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 pt-4 pb-0">
        <h6 class="fw-bold">Charges par type — <?= $annee ?></h6>
      </div>
      <div class="card-body">
        <?php if (empty($chargesType)): ?>
          <p class="text-center text-muted py-3">Aucune charge cette année</p>
        <?php else: ?>
        <?php foreach ($chargesType as $ch): ?>
        <?php $pct = $totalCharges > 0 ? round($ch['total']/$totalCharges*100) : 0; ?>
        <div class="mb-2">
          <div class="d-flex justify-content-between">
            <small><?= htmlspecialchars($ch['type']) ?></small>
            <small class="fw-bold"><?= formatMontant($ch['total']) ?> (<?= $pct ?>%)</small>
          </div>
          <div class="progress" style="height:6px">
            <div class="progress-bar bg-danger" style="width:<?= $pct ?>%"></div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('chartRecettes');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($moisNoms) ?>,
        datasets: [{
            label: 'Recettes (FCFA)',
            data: <?= json_encode(array_values($recettesData)) ?>,
            backgroundColor: 'rgba(13,110,253,0.7)',
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => new Intl.NumberFormat('fr-FR').format(v) } }
        }
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
