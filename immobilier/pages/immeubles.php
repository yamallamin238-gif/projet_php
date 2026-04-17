<?php
require_once __DIR__ . '/../config/app.php';
requireLogin();
$currentUser = getCurrentUser();
$pageTitle   = 'Immeubles — ' . APP_NAME;
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'ajouter') {
        $db->prepare("INSERT INTO immeubles (proprietaire_id,nom,adresse,ville,code_postal,pays,type,nb_unites,description) VALUES (?,?,?,?,?,?,?,?,?)")->execute([
            $_POST['proprietaire_id'], $_POST['nom'], $_POST['adresse'],
            $_POST['ville'], $_POST['code_postal'], $_POST['pays'],
            $_POST['type'], $_POST['nb_unites'], $_POST['description']
        ]);
        setFlash('success', 'Immeuble ajouté avec succès.');
    } elseif ($action === 'supprimer') {
        $db->prepare("DELETE FROM immeubles WHERE id=?")->execute([(int)$_POST['id']]);
        setFlash('warning', 'Immeuble supprimé.');
    }
    header('Location: ' . APP_URL . '/pages/immeubles.php'); exit;
}

$immeubles = $db->query("SELECT i.*, p.nom AS proprio_nom, p.prenom AS proprio_prenom,
    (SELECT COUNT(*) FROM logements WHERE immeuble_id=i.id) AS nb_logements,
    (SELECT COUNT(*) FROM logements WHERE immeuble_id=i.id AND statut='occupe') AS nb_occupes
    FROM immeubles i JOIN proprietaires p ON i.proprietaire_id=p.id ORDER BY i.nom")->fetchAll();

$proprietaires = $db->query("SELECT id, CONCAT(civilite,' ',prenom,' ',nom) AS nom FROM proprietaires ORDER BY nom")->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-buildings me-2 text-primary"></i>Immeubles</h4>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalImmeuble">
    <i class="bi bi-plus-lg me-1"></i>Nouvel immeuble
  </button>
</div>

<div class="row g-4">
<?php foreach ($immeubles as $im): ?>
<div class="col-md-6 col-xl-4">
  <div class="card border-0 shadow-sm h-100">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <h6 class="fw-bold mb-0"><?= htmlspecialchars($im['nom']) ?></h6>
        <span class="badge bg-primary"><?= htmlspecialchars($im['type']) ?></span>
      </div>
      <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($im['adresse'] . ', ' . $im['ville']) ?></p>
      <p class="text-muted small mb-3"><i class="bi bi-person me-1"></i><?= htmlspecialchars($im['proprio_prenom'] . ' ' . $im['proprio_nom']) ?></p>
      <div class="d-flex gap-3">
        <div class="text-center">
          <div class="fs-4 fw-bold text-primary"><?= $im['nb_logements'] ?></div>
          <div class="text-muted" style="font-size:.75rem">Logements</div>
        </div>
        <div class="text-center">
          <div class="fs-4 fw-bold text-success"><?= $im['nb_occupes'] ?></div>
          <div class="text-muted" style="font-size:.75rem">Occupés</div>
        </div>
        <div class="text-center">
          <div class="fs-4 fw-bold text-info"><?= $im['nb_logements'] - $im['nb_occupes'] ?></div>
          <div class="text-muted" style="font-size:.75rem">Libres</div>
        </div>
      </div>
    </div>
    <div class="card-footer bg-white border-0 d-flex gap-2">
      <a href="<?= APP_URL ?>/pages/logements.php?immeuble_id=<?= $im['id'] ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
        <i class="bi bi-door-open me-1"></i>Logements
      </a>
      <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet immeuble ?')">
        <input type="hidden" name="action" value="supprimer">
        <input type="hidden" name="id" value="<?= $im['id'] ?>">
        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>

<!-- MODAL AJOUTER IMMEUBLE -->
<div class="modal fade" id="modalImmeuble" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="ajouter">
        <div class="modal-header">
          <h5 class="modal-title fw-bold"><i class="bi bi-building-add me-2"></i>Nouvel immeuble</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Propriétaire *</label>
              <select name="proprietaire_id" class="form-select" required>
                <option value="">— Choisir —</option>
                <?php foreach ($proprietaires as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12"><label class="form-label">Nom *</label><input type="text" name="nom" class="form-control" required></div>
            <div class="col-12"><label class="form-label">Adresse *</label><input type="text" name="adresse" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Ville *</label><input type="text" name="ville" class="form-control" required value="Dakar"></div>
            <div class="col-md-6"><label class="form-label">Code postal</label><input type="text" name="code_postal" class="form-control"></div>
            <div class="col-md-6">
              <label class="form-label">Pays</label>
              <input type="text" name="pays" class="form-control" value="Sénégal">
            </div>
            <div class="col-md-6">
              <label class="form-label">Type *</label>
              <select name="type" class="form-select">
                <option value="appartements">Appartements</option>
                <option value="maisons">Maisons</option>
                <option value="commerces">Commerces</option>
                <option value="mixte">Mixte</option>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label">Nb unités</label><input type="number" name="nb_unites" class="form-control" value="1" min="1"></div>
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
