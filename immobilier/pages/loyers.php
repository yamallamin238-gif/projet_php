<?php
$page_title = 'Loyers & Paiements';
require_once '../config/app.php';
requireLogin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'ajouter') {
        $stmt = $db->prepare("INSERT INTO loyers (contrat_id, mois, montant, statut, date_echeance) VALUES (?,?,?,'impaye',?)");
        $stmt->execute([$_POST['contrat_id'], $_POST['mois'], $_POST['montant'], $_POST['date_echeance']]);
        setFlash('success', 'Loyer ajouté.');
    }
    if ($_POST['action'] === 'marquer_paye') {
        $db->prepare("UPDATE loyers SET statut='paye', date_paiement=NOW() WHERE id=?")->execute([$_POST['id']]);
        $stmt = $db->prepare("INSERT INTO paiements (loyer_id, montant, date_paiement, mode_paiement) VALUES (?,?,NOW(),?)");
        $stmt->execute([$_POST['id'], $_POST['montant'], $_POST['mode']]);
        setFlash('success', 'Paiement enregistré.');
    }
    if ($_POST['action'] === 'supprimer') {
        $db->prepare("DELETE FROM loyers WHERE id=?")->execute([$_POST['id']]);
        setFlash('warning', 'Loyer supprimé.');
    }
    header("Location: loyers.php"); exit;
}

$loyers = $db->query("
    SELECT ly.*, c.loyer_mensuel,
        CONCAT(l.civilite,' ',l.nom,' ',l.prenom) as locataire_nom,
        lg.reference as logement_ref
    FROM loyers ly
    JOIN contrats c ON ly.contrat_id = c.id
    JOIN locataires l ON c.locataire_id = l.id
    JOIN logements lg ON c.logement_id = lg.id
    ORDER BY ly.date_echeance DESC
")->fetchAll();

$contrats_actifs = $db->query("
    SELECT c.id, CONCAT(l.civilite,' ',l.nom,' ',l.prenom,' — ',lg.reference) as label, c.loyer_mensuel
    FROM contrats c
    JOIN locataires l ON c.locataire_id = l.id
    JOIN logements lg ON c.logement_id = lg.id
    WHERE c.statut = 'en_cours'
    ORDER BY l.nom
")->fetchAll();

$stats = [
    'total' => count($loyers),
    'payes' => count(array_filter($loyers, fn($l) => $l['statut']==='paye')),
    'impayes' => count(array_filter($loyers, fn($l) => $l['statut']==='impaye')),
    'montant_du' => array_sum(array_map(fn($l) => $l['statut']==='impaye' ? $l['montant'] : 0, $loyers)),
];

$flash = getFlash();
require_once '../includes/header.php';
?>
<div class="container-fluid py-4">
  <?php if($flash): ?>
  <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
    <?= $flash['msg'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card text-center border-0 shadow-sm">
        <div class="card-body"><div class="fs-2 fw-bold"><?= $stats['total'] ?></div><div class="text-muted small">Total loyers</div></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center border-0 shadow-sm bg-success text-white">
        <div class="card-body"><div class="fs-2 fw-bold"><?= $stats['payes'] ?></div><div class="small">Payés</div></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center border-0 shadow-sm bg-danger text-white">
        <div class="card-body"><div class="fs-2 fw-bold"><?= $stats['impayes'] ?></div><div class="small">Impayés</div></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center border-0 shadow-sm bg-warning">
        <div class="card-body"><div class="fs-5 fw-bold"><?= number_format($stats['montant_du'],0,',',' ') ?></div><div class="small">FCFA dus</div></div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
      <h5 class="mb-0"><i class="bi bi-cash-coin me-2 text-success"></i>Loyers & Paiements</h5>
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalLoyerAjout">
        <i class="bi bi-plus-lg me-1"></i>Ajouter loyer
      </button>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr><th>Locataire</th><th>Logement</th><th>Mois</th><th>Montant</th><th>Échéance</th><th>Statut</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php foreach($loyers as $l): ?>
          <tr class="<?= $l['statut']==='impaye' ? 'table-danger' : '' ?>">
            <td><?= htmlspecialchars($l['locataire_nom']) ?></td>
            <td><?= htmlspecialchars($l['logement_ref']) ?></td>
            <td><?= $l['mois'] ?></td>
            <td><strong><?= number_format($l['montant'],0,',',' ') ?> FCFA</strong></td>
            <td><?= formatDate($l['date_echeance']) ?></td>
            <td>
              <span class="badge bg-<?= $l['statut']==='paye' ? 'success' : 'danger' ?>">
                <?= $l['statut']==='paye' ? 'Payé' : 'Impayé' ?>
              </span>
            </td>
            <td>
              <?php if($l['statut']==='impaye'): ?>
              <button class="btn btn-sm btn-success"
                onclick="payerLoyer(<?= $l['id'] ?>, <?= $l['montant'] ?>)"
                data-bs-toggle="modal" data-bs-target="#modalPayer">
                <i class="bi bi-check-circle me-1"></i>Payer
              </button>
              <?php endif; ?>
              <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce loyer ?')">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($loyers)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Aucun loyer enregistré</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ajouter -->
<div class="modal fade" id="modalLoyerAjout" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Ajouter un loyer</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="action" value="ajouter">
        <div class="mb-3">
          <label class="form-label">Contrat actif *</label>
          <select name="contrat_id" class="form-select" required>
            <option value="">-- Choisir --</option>
            <?php foreach($contrats_actifs as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Mois *</label>
          <input name="mois" type="month" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Montant (FCFA) *</label>
          <input name="montant" type="number" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Date d'échéance *</label>
          <input name="date_echeance" type="date" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div></div>
</div>

<!-- Modal Payer -->
<div class="modal fade" id="modalPayer" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header bg-success text-white"><h5 class="modal-title">Confirmer le paiement</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="action" value="marquer_paye">
        <input type="hidden" name="id" id="payId">
        <input type="hidden" name="montant" id="payMontant">
        <div class="alert alert-success">
          Montant : <strong id="payAffichage"></strong> FCFA
        </div>
        <div class="mb-3">
          <label class="form-label">Mode de paiement</label>
          <select name="mode" class="form-select">
            <option value="espece">Espèces</option>
            <option value="wave">Wave</option>
            <option value="orange_money">Orange Money</option>
            <option value="free_money">Free Money</option>
            <option value="virement">Virement bancaire</option>
            <option value="cheque">Chèque</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-success">Confirmer le paiement</button>
      </div>
    </form>
  </div></div>
</div>

<script>
function payerLoyer(id, montant) {
  document.getElementById('payId').value = id;
  document.getElementById('payMontant').value = montant;
  document.getElementById('payAffichage').textContent = new Intl.NumberFormat('fr-FR').format(montant);
}
</script>
<?php require_once '../includes/footer.php'; ?>