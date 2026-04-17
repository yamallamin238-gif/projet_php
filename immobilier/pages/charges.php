<?php
require_once __DIR__ . '/../config/app.php';
requireLogin();
$currentUser = getCurrentUser();
$pageTitle   = 'Charges & Dépenses — ' . APP_NAME;
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'ajouter') {
        $db->prepare("INSERT INTO charges (immeuble_id,logement_id,type,libelle,montant,date_charge,fournisseur,facture_ref,notes) VALUES (?,?,?,?,?,?,?,?,?)")->execute([
            $_POST['immeuble_id'] ?: null, $_POST['logement_id'] ?: null,
            $_POST['type'], $_POST['libelle'], $_POST['montant'],
            $_POST['date_charge'], $_POST['fournisseur'],
            $_POST['facture_ref'], $_POST['notes']
        ]);
        setFlash('success', 'Charge enregistrée.');
    } elseif ($action === 'supprimer') {
        $db->prepare("DELETE FROM charges WHERE id=?")->execute([(int)$_POST['id']]);
        setFlash('warning', 'Charge supprimée.');
    }
    header('Location: ' . APP_URL . '/pages/charges.php'); exit;
}

$charges = $db->query("
    SELECT ch.*, i.nom AS immeuble_nom, lg.reference AS logement_ref
    FROM charges ch
    LEFT JOIN immeubles i ON ch.immeuble_id = i.id
    LEFT JOIN logements lg ON ch.logement_id = lg.id
    ORDER BY ch.date_charge DESC
")->fetchAll();

$totalCharges = array_sum(array_column($charges, 'montant'));
$immeubles    = $db->query("SELECT id, nom FROM immeubles ORDER BY nom")->fetchAll();
$logements    = $db->query("SELECT lg.id, CONCAT(lg.reference,' (',i.nom,')') AS label FROM logements lg JOIN immeubles i ON lg.immeuble_id=i.id ORDER BY label")->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-0"><i class="bi bi-cash-stack me-2 text-primary"></i>Charges & Dépenses</h4>
    <small class="text-muted">Total : <strong class="text-danger"><?= formatMontant($totalCharges) ?></strong></small>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCharge">
    <i class="bi bi-plus-lg me-1"></i>Nouvelle charge
  </button>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Date</th><th>Type</th><th>Libellé</th><th>Immeuble/Logement</th><th>Fournisseur</th><th>Montant</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($charges as $ch): ?>
        <tr>
          <td><?= formatDate($ch['date_charge']) ?></td>
          <td><span class="badge bg-secondary"><?= htmlspecialchars($ch['type']) ?></span></td>
          <td><?= htmlspecialchars($ch['libelle']) ?></td>
          <td>
            <?= $ch['immeuble_nom'] ? htmlspecialchars($ch['immeuble_nom']) : '' ?>
            <?= $ch['logement_ref'] ? '<br><small class="text-muted">' . htmlspecialchars($ch['logement_ref']) . '</small>' : '' ?>
          </td>
          <td><?= htmlspecialchars($ch['fournisseur'] ?? '-') ?></td>
          <td class="fw-bold text-danger"><?= formatMontant($ch['montant']) ?></td>
          <td>
            <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">
              <input type="hidden" name="action" value="supprimer">
              <input type="hidden" name="id" value="<?= $ch['id'] ?>">
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL CHARGE -->
<div class="modal fade" id="modalCharge" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="ajouter">
        <div class="modal-header">
          <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Nouvelle charge</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Type *</label>
              <select name="type" class="form-select" required>
                <option value="travaux">Travaux</option>
                <option value="entretien">Entretien</option>
                <option value="assurance">Assurance</option>
                <option value="taxe">Taxe/Impôt</option>
                <option value="eau">Eau</option>
                <option value="electricite">Électricité</option>
                <option value="gardiennage">Gardiennage</option>
                <option value="autre">Autre</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Date *</label>
              <input type="date" name="date_charge" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Libellé *</label>
              <input type="text" name="libelle" class="form-control" required placeholder="Description de la charge...">
            </div>
            <div class="col-md-6">
              <label class="form-label">Immeuble (optionnel)</label>
              <select name="immeuble_id" class="form-select">
                <option value="">—</option>
                <?php foreach ($immeubles as $im): ?>
                <option value="<?= $im['id'] ?>"><?= htmlspecialchars($im['nom']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Logement (optionnel)</label>
              <select name="logement_id" class="form-select">
                <option value="">—</option>
                <?php foreach ($logements as $lg): ?>
                <option value="<?= $lg['id'] ?>"><?= htmlspecialchars($lg['label']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Montant (FCFA) *</label>
              <input type="number" name="montant" class="form-control" required min="0">
            </div>
            <div class="col-md-6">
              <label class="form-label">Fournisseur</label>
              <input type="text" name="fournisseur" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Référence facture</label>
              <input type="text" name="facture_ref" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
