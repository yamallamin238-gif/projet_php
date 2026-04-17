<?php
require_once __DIR__ . '/../config/app.php';
requireLogin();
$currentUser = getCurrentUser();
$pageTitle   = 'États des lieux — ' . APP_NAME;
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'ajouter') {
        $db->prepare("INSERT INTO etats_lieux (contrat_id,type,date_etat,etat_general,observations,depot_restitue,date_restitution) VALUES (?,?,?,?,?,?,?)")->execute([
            $_POST['contrat_id'], $_POST['type'], $_POST['date_etat'],
            $_POST['etat_general'], $_POST['observations'],
            $_POST['depot_restitue'] ?: 0,
            $_POST['date_restitution'] ?: null
        ]);
        // Si sortie, résilier contrat et libérer logement
        if ($_POST['type'] === 'sortie') {
            $c = $db->prepare("SELECT logement_id FROM contrats WHERE id=?");
            $c->execute([$_POST['contrat_id']]);
            $contrat = $c->fetch();
            if ($contrat) {
                $db->prepare("UPDATE logements SET statut='libre' WHERE id=?")->execute([$contrat['logement_id']]);
                $db->prepare("UPDATE contrats SET statut='resilie', date_resiliation=? WHERE id=?")->execute([date('Y-m-d'), $_POST['contrat_id']]);
            }
        }
        setFlash('success', 'État des lieux enregistré.');
    }
    header('Location: ' . APP_URL . '/pages/etats_lieux.php'); exit;
}

$etats = $db->query("
    SELECT el.*, c.numero AS contrat_num,
           CONCAT(l.prenom,' ',l.nom) AS locataire,
           lg.reference AS logement, i.nom AS immeuble
    FROM etats_lieux el
    JOIN contrats c ON el.contrat_id = c.id
    JOIN locataires l ON c.locataire_id = l.id
    JOIN logements lg ON c.logement_id = lg.id
    JOIN immeubles i ON lg.immeuble_id = i.id
    ORDER BY el.date_etat DESC
")->fetchAll();

$contrats = $db->query("
    SELECT c.id, c.numero, CONCAT(l.prenom,' ',l.nom) AS locataire, c.depot_garantie
    FROM contrats c JOIN locataires l ON c.locataire_id=l.id
    WHERE c.statut='en_cours' ORDER BY c.numero
")->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-clipboard-check me-2 text-primary"></i>États des lieux</h4>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEtat">
    <i class="bi bi-plus-lg me-1"></i>Nouvel état des lieux
  </button>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Date</th><th>Type</th><th>Contrat</th><th>Locataire</th><th>Logement</th><th>État</th><th>Dépôt restitué</th></tr>
        </thead>
        <tbody>
        <?php if (empty($etats)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Aucun état des lieux enregistré</td></tr>
        <?php endif; ?>
        <?php foreach ($etats as $e): ?>
        <tr>
          <td><?= formatDate($e['date_etat']) ?></td>
          <td>
            <span class="badge <?= $e['type']==='entree' ? 'bg-success' : 'bg-warning text-dark' ?>">
              <?= $e['type'] === 'entree' ? '🔑 Entrée' : '🚪 Sortie' ?>
            </span>
          </td>
          <td><small class="text-primary fw-bold"><?= htmlspecialchars($e['contrat_num']) ?></small></td>
          <td><?= htmlspecialchars($e['locataire']) ?></td>
          <td>
            <div><?= htmlspecialchars($e['logement']) ?></div>
            <small class="text-muted"><?= htmlspecialchars($e['immeuble']) ?></small>
          </td>
          <td>
            <?php $colors = ['tres_bon'=>'success','bon'=>'info','moyen'=>'warning','mauvais'=>'danger']; ?>
            <span class="badge bg-<?= $colors[$e['etat_general']] ?? 'secondary' ?>">
              <?= ucfirst(str_replace('_',' ',$e['etat_general'])) ?>
            </span>
          </td>
          <td>
            <?php if ($e['depot_restitue'] > 0): ?>
              <span class="text-success fw-bold"><?= formatMontant($e['depot_restitue']) ?></span>
              <?php if ($e['date_restitution']): ?>
                <br><small class="text-muted"><?= formatDate($e['date_restitution']) ?></small>
              <?php endif; ?>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL ÉTAT DES LIEUX -->
<div class="modal fade" id="modalEtat" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="ajouter">
        <div class="modal-header">
          <h5 class="modal-title fw-bold"><i class="bi bi-clipboard-plus me-2"></i>Nouvel état des lieux</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Contrat *</label>
              <select name="contrat_id" class="form-select" required id="eContrat">
                <option value="">— Choisir un contrat —</option>
                <?php foreach ($contrats as $c): ?>
                <option value="<?= $c['id'] ?>" data-depot="<?= $c['depot_garantie'] ?>">
                  <?= htmlspecialchars($c['numero'] . ' — ' . $c['locataire']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Type *</label>
              <select name="type" class="form-select" id="eType" required>
                <option value="entree">🔑 Entrée</option>
                <option value="sortie">🚪 Sortie</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Date *</label>
              <input type="date" name="date_etat" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">État général</label>
              <select name="etat_general" class="form-select">
                <option value="tres_bon">Très bon</option>
                <option value="bon" selected>Bon</option>
                <option value="moyen">Moyen</option>
                <option value="mauvais">Mauvais</option>
              </select>
            </div>
            <div class="col-md-4" id="depotBlock" style="display:none">
              <label class="form-label">Dépôt restitué (FCFA)</label>
              <input type="number" name="depot_restitue" id="eDepot" class="form-control" value="0">
            </div>
            <div class="col-md-4" id="dateRestBlock" style="display:none">
              <label class="form-label">Date restitution dépôt</label>
              <input type="date" name="date_restitution" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Observations</label>
              <textarea name="observations" class="form-control" rows="4"
                placeholder="Décrivez l'état des murs, sols, plafonds, équipements..."></textarea>
            </div>
            <div class="col-12" id="alerteSortie" style="display:none">
              <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>État de sortie :</strong> Le contrat sera automatiquement résilié et le logement remis en libre.
              </div>
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

<script>
document.getElementById('eType').addEventListener('change', function() {
    const isSortie = this.value === 'sortie';
    document.getElementById('depotBlock').style.display    = isSortie ? '' : 'none';
    document.getElementById('dateRestBlock').style.display = isSortie ? '' : 'none';
    document.getElementById('alerteSortie').style.display  = isSortie ? '' : 'none';
});
document.getElementById('eContrat').addEventListener('change', function() {
    const depot = this.options[this.selectedIndex].dataset.depot || 0;
    document.getElementById('eDepot').value = depot;
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
