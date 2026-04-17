<?php
$page_title = 'Biens Immobiliers';
require_once '../config/app.php';
requireLogin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'ajouter') {
        $stmt = $db->prepare("INSERT INTO logements (reference, type, surface, loyer_base, charges, statut) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$_POST['reference'], $_POST['type'], $_POST['surface'], $_POST['loyer_base'], $_POST['charges'], $_POST['statut']]);
        setFlash('success', 'Bien ajouté.');
    }
    if ($_POST['action'] === 'modifier') {
        $stmt = $db->prepare("UPDATE logements SET reference=?, type=?, surface=?, loyer_base=?, charges=?, statut=? WHERE id=?");
        $stmt->execute([$_POST['reference'], $_POST['type'], $_POST['surface'], $_POST['loyer_base'], $_POST['charges'], $_POST['statut'], $_POST['id']]);
        setFlash('success', 'Bien modifié.');
    }
    if ($_POST['action'] === 'supprimer') {
        $db->prepare("DELETE FROM logements WHERE id=?")->execute([$_POST['id']]);
        setFlash('warning', 'Bien supprimé.');
    }
    header("Location: biens.php"); exit;
}

$biens = $db->query("SELECT * FROM logements ORDER BY reference")->fetchAll();
$flash = getFlash();
require_once '../includes/header.php';
?>
<div class="container-fluid py-4">
  <?php if($flash): ?>
  <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
    <?= $flash['msg'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
      <h5 class="mb-0"><i class="bi bi-building me-2 text-primary"></i>Biens Immobiliers</h5>
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalBienAjout">
        <i class="bi bi-plus-lg me-1"></i>Ajouter
      </button>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr><th>Référence</th><th>Type</th><th>Surface</th><th>Loyer base</th><th>Charges</th><th>Statut</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php foreach($biens as $b): ?>
          <tr>
            <td><strong><?= htmlspecialchars($b['reference']) ?></strong></td>
            <td><?= htmlspecialchars($b['type']) ?></td>
            <td><?= $b['surface'] ?> m²</td>
            <td><?= number_format($b['loyer_base'],0,',',' ') ?> FCFA</td>
            <td><?= number_format($b['charges'],0,',',' ') ?> FCFA</td>
            <td><?= badgeStatut($b['statut']) ?></td>
            <td>
              <button class="btn btn-sm btn-outline-primary"
                onclick='editBien(<?= htmlspecialchars(json_encode($b, JSON_HEX_APOS|JSON_HEX_QUOT)) ?>)'
                data-bs-toggle="modal" data-bs-target="#modalBienModif">
                <i class="bi bi-pencil"></i>
              </button>
              <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce bien ?')">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($biens)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Aucun bien enregistré</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ajout -->
<div class="modal fade" id="modalBienAjout" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Nouveau bien</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="action" value="ajouter">
        <div class="mb-3"><input name="reference" class="form-control" placeholder="Référence (ex: APT-01) *" required></div>
        <div class="mb-3">
          <select name="type" class="form-select">
            <option value="appartement">Appartement</option>
            <option value="maison">Maison</option>
            <option value="studio">Studio</option>
            <option value="bureau">Bureau</option>
            <option value="commerce">Commerce</option>
          </select>
        </div>
        <div class="row g-2 mb-3">
          <div class="col"><input name="surface" type="number" step="0.01" class="form-control" placeholder="Surface m²"></div>
          <div class="col"><input name="loyer_base" type="number" class="form-control" placeholder="Loyer (FCFA) *" required></div>
        </div>
        <div class="mb-3"><input name="charges" type="number" class="form-control" placeholder="Charges (FCFA)" value="0"></div>
        <div class="mb-3">
          <select name="statut" class="form-select">
            <option value="libre">Libre</option>
            <option value="occupe">Occupé</option>
            <option value="travaux">Travaux</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div></div>
</div>

<!-- Modal Modifier -->
<div class="modal fade" id="modalBienModif" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Modifier bien</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="action" value="modifier">
        <input type="hidden" name="id" id="bEditId">
        <div class="mb-3"><input name="reference" id="bEditRef" class="form-control" placeholder="Référence *" required></div>
        <div class="mb-3">
          <select name="type" id="bEditType" class="form-select">
            <option value="appartement">Appartement</option>
            <option value="maison">Maison</option>
            <option value="studio">Studio</option>
            <option value="bureau">Bureau</option>
            <option value="commerce">Commerce</option>
          </select>
        </div>
        <div class="row g-2 mb-3">
          <div class="col"><input name="surface" id="bEditSurface" type="number" step="0.01" class="form-control" placeholder="Surface m²"></div>
          <div class="col"><input name="loyer_base" id="bEditLoyer" type="number" class="form-control" placeholder="Loyer *" required></div>
        </div>
        <div class="mb-3"><input name="charges" id="bEditCharges" type="number" class="form-control" placeholder="Charges"></div>
        <div class="mb-3">
          <select name="statut" id="bEditStatut" class="form-select">
            <option value="libre">Libre</option>
            <option value="occupe">Occupé</option>
            <option value="travaux">Travaux</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary">Sauvegarder</button>
      </div>
    </form>
  </div></div>
</div>

<script>
function editBien(b) {
  document.getElementById('bEditId').value = b.id;
  document.getElementById('bEditRef').value = b.reference;
  document.getElementById('bEditType').value = b.type;
  document.getElementById('bEditSurface').value = b.surface;
  document.getElementById('bEditLoyer').value = b.loyer_base;
  document.getElementById('bEditCharges').value = b.charges;
  document.getElementById('bEditStatut').value = b.statut;
}
</script>
<?php require_once '../includes/footer.php'; ?>