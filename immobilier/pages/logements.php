<?php
require_once __DIR__ . '/../config/app.php';
requireLogin();
$currentUser = getCurrentUser();
$pageTitle   = 'Logements — ' . APP_NAME;
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'ajouter') {
        $db->prepare("INSERT INTO logements (immeuble_id,reference,type,etage,surface,nb_pieces,nb_chambres,loyer_base,charges,depot_garantie,description,statut) VALUES (?,?,?,?,?,?,?,?,?,?,?,'libre')")->execute([
            $_POST['immeuble_id'], $_POST['reference'], $_POST['type'],
            $_POST['etage'] ?: 0, $_POST['surface'] ?: null,
            $_POST['nb_pieces'] ?: 1, $_POST['nb_chambres'] ?: 0,
            $_POST['loyer_base'], $_POST['charges'] ?: 0,
            $_POST['depot_garantie'] ?: 0, $_POST['description']
        ]);
        setFlash('success', 'Logement ajouté.');
    } elseif ($action === 'supprimer') {
        $db->prepare("DELETE FROM logements WHERE id=?")->execute([(int)$_POST['id']]);
        setFlash('warning', 'Logement supprimé.');
    }
    header('Location: ' . APP_URL . '/pages/logements.php' . (isset($_GET['immeuble_id']) ? '?immeuble_id=' . (int)$_GET['immeuble_id'] : ''));
    exit;
}

$imbFilter = (int)($_GET['immeuble_id'] ?? 0);
$where  = $imbFilter ? "WHERE lg.immeuble_id = $imbFilter" : "";

$logements = $db->query("
    SELECT lg.*, i.nom AS immeuble_nom, i.ville
    FROM logements lg JOIN immeubles i ON lg.immeuble_id = i.id
    $where ORDER BY i.nom, lg.reference
")->fetchAll();

$immeubles = $db->query("SELECT id, nom FROM immeubles ORDER BY nom")->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-door-open me-2 text-primary"></i>Logements</h4>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalLogement">
    <i class="bi bi-plus-lg me-1"></i>Nouveau logement
  </button>
</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-2">
    <form method="GET" class="d-flex gap-2">
      <select name="immeuble_id" class="form-select">
        <option value="">Tous les immeubles</option>
        <?php foreach ($immeubles as $im): ?>
        <option value="<?= $im['id'] ?>" <?= $imbFilter==$im['id']?'selected':'' ?>><?= htmlspecialchars($im['nom']) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-outline-primary"><i class="bi bi-funnel"></i></button>
      <?php if ($imbFilter): ?><a href="?" class="btn btn-outline-secondary">×</a><?php endif; ?>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Référence</th><th>Immeuble</th><th>Type</th><th>Surface</th><th>Loyer base</th><th>Charges</th><th>Dépôt</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($logements as $lg): ?>
        <tr>
          <td class="fw-bold"><?= htmlspecialchars($lg['reference']) ?></td>
          <td>
            <div><?= htmlspecialchars($lg['immeuble_nom']) ?></div>
            <small class="text-muted"><?= htmlspecialchars($lg['ville']) ?></small>
          </td>
          <td><?= htmlspecialchars($lg['type']) ?></td>
          <td><?= $lg['surface'] ? $lg['surface'] . ' m²' : '-' ?></td>
          <td class="fw-semibold"><?= formatMontant($lg['loyer_base']) ?></td>
          <td><?= formatMontant($lg['charges']) ?></td>
          <td><?= formatMontant($lg['depot_garantie']) ?></td>
          <td><?= badgeStatut($lg['statut']) ?></td>
          <td>
            <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce logement ?')">
              <input type="hidden" name="action" value="supprimer">
              <input type="hidden" name="id" value="<?= $lg['id'] ?>">
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

<!-- MODAL AJOUT LOGEMENT -->
<div class="modal fade" id="modalLogement" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="ajouter">
        <div class="modal-header">
          <h5 class="modal-title fw-bold"><i class="bi bi-door-open me-2"></i>Nouveau logement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Immeuble *</label>
              <select name="immeuble_id" class="form-select" required>
                <option value="">— Choisir —</option>
                <?php foreach ($immeubles as $im): ?>
                <option value="<?= $im['id'] ?>" <?= $imbFilter==$im['id']?'selected':'' ?>><?= htmlspecialchars($im['nom']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Référence *</label>
              <input type="text" name="reference" class="form-control" required placeholder="ex: APT-01">
            </div>
            <div class="col-md-3">
              <label class="form-label">Type *</label>
              <select name="type" class="form-select">
                <option>studio</option><option selected>F2</option><option>F3</option><option>F4</option><option>F5+</option><option>commerce</option><option>bureau</option><option>parking</option>
              </select>
            </div>
            <div class="col-md-3"><label class="form-label">Étage</label><input type="number" name="etage" class="form-control" value="0" min="0"></div>
            <div class="col-md-3"><label class="form-label">Surface (m²)</label><input type="number" name="surface" class="form-control" step="0.01"></div>
            <div class="col-md-3"><label class="form-label">Nb pièces</label><input type="number" name="nb_pieces" class="form-control" value="2" min="1"></div>
            <div class="col-md-3"><label class="form-label">Nb chambres</label><input type="number" name="nb_chambres" class="form-control" value="1" min="0"></div>
            <div class="col-md-4"><label class="form-label">Loyer de base (FCFA) *</label><input type="number" name="loyer_base" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Charges (FCFA)</label><input type="number" name="charges" class="form-control" value="0"></div>
            <div class="col-md-4"><label class="form-label">Dépôt de garantie</label><input type="number" name="depot_garantie" class="form-control" value="0"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
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
