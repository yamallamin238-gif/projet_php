<?php
require_once __DIR__ . '/../config/app.php';
requireLogin();
$currentUser = getCurrentUser();
$pageTitle   = 'Propriétaires — ' . APP_NAME;
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'ajouter' || $action === 'modifier') {
        $data = [
            $_POST['civilite'], $_POST['nom'], $_POST['prenom'],
            $_POST['email'], $_POST['telephone'], $_POST['adresse'],
            $_POST['ville'], $_POST['code_postal'], $_POST['pays'],
            $_POST['rib'], $_POST['notes']
        ];
        if ($action === 'ajouter') {
            $db->prepare("INSERT INTO proprietaires (civilite,nom,prenom,email,telephone,adresse,ville,code_postal,pays,rib,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)")->execute($data);
            setFlash('success', 'Propriétaire ajouté.');
        } else {
            $data[] = (int)$_POST['id'];
            $db->prepare("UPDATE proprietaires SET civilite=?,nom=?,prenom=?,email=?,telephone=?,adresse=?,ville=?,code_postal=?,pays=?,rib=?,notes=? WHERE id=?")->execute($data);
            setFlash('success', 'Propriétaire mis à jour.');
        }
    } elseif ($action === 'supprimer') {
        $db->prepare("DELETE FROM proprietaires WHERE id=?")->execute([(int)$_POST['id']]);
        setFlash('warning', 'Propriétaire supprimé.');
    }
    header('Location: ' . APP_URL . '/pages/proprietaires.php'); exit;
}

$proprietaires = $db->query("
    SELECT p.*,
        (SELECT COUNT(*) FROM immeubles WHERE proprietaire_id=p.id) AS nb_immeubles
    FROM proprietaires p ORDER BY p.nom, p.prenom
")->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-person-badge me-2 text-primary"></i>Propriétaires</h4>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProprio">
    <i class="bi bi-plus-lg me-1"></i>Nouveau propriétaire
  </button>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Nom</th><th>Contact</th><th>Ville</th><th>RIB/Banque</th><th>Immeubles</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (empty($proprietaires)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Aucun propriétaire enregistré</td></tr>
        <?php endif; ?>
        <?php foreach ($proprietaires as $p): ?>
        <tr>
          <td class="fw-semibold"><?= htmlspecialchars($p['civilite'] . ' ' . $p['prenom'] . ' ' . $p['nom']) ?></td>
          <td>
            <div><?= htmlspecialchars($p['email'] ?? '-') ?></div>
            <small class="text-muted"><?= htmlspecialchars($p['telephone'] ?? '') ?></small>
          </td>
          <td><?= htmlspecialchars($p['ville'] ?? '-') ?></td>
          <td><small><?= htmlspecialchars($p['rib'] ?? '-') ?></small></td>
          <td><span class="badge bg-primary"><?= $p['nb_immeubles'] ?> immeuble(s)</span></td>
          <td>
            <button class="btn btn-sm btn-outline-primary me-1"
              data-bs-toggle="modal" data-bs-target="#modalProprio"
              data-prop='<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>'>
              <i class="bi bi-pencil"></i>
            </button>
            <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce propriétaire ?')">
              <input type="hidden" name="action" value="supprimer">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
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

<!-- MODAL -->
<div class="modal fade" id="modalProprio" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" id="propAction" value="ajouter">
        <input type="hidden" name="id" id="propId">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="propTitle"><i class="bi bi-person-plus me-2"></i>Nouveau propriétaire</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-2">
              <label class="form-label">Civilité</label>
              <select name="civilite" id="pCivilite" class="form-select">
                <option>M.</option><option>Mme</option><option>Mlle</option><option>Société</option>
              </select>
            </div>
            <div class="col-md-5"><label class="form-label">Nom *</label><input type="text" name="nom" id="pNom" class="form-control" required></div>
            <div class="col-md-5"><label class="form-label">Prénom</label><input type="text" name="prenom" id="pPrenom" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" id="pEmail" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Téléphone</label><input type="text" name="telephone" id="pTel" class="form-control"></div>
            <div class="col-12"><label class="form-label">Adresse</label><input type="text" name="adresse" id="pAdresse" class="form-control"></div>
            <div class="col-md-5"><label class="form-label">Ville</label><input type="text" name="ville" id="pVille" class="form-control" value="Dakar"></div>
            <div class="col-md-3"><label class="form-label">Code postal</label><input type="text" name="code_postal" id="pCP" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Pays</label><input type="text" name="pays" id="pPays" class="form-control" value="Sénégal"></div>
            <div class="col-12"><label class="form-label">RIB / Coordonnées bancaires</label><input type="text" name="rib" id="pRib" class="form-control"></div>
            <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" id="pNotes" class="form-control" rows="2"></textarea></div>
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
document.getElementById('modalProprio').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    const data = btn ? btn.dataset.prop : null;
    if (data) {
        const d = JSON.parse(data);
        document.getElementById('propAction').value = 'modifier';
        document.getElementById('propTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Modifier propriétaire';
        document.getElementById('propId').value     = d.id;
        document.getElementById('pCivilite').value  = d.civilite;
        document.getElementById('pNom').value       = d.nom;
        document.getElementById('pPrenom').value    = d.prenom || '';
        document.getElementById('pEmail').value     = d.email || '';
        document.getElementById('pTel').value       = d.telephone || '';
        document.getElementById('pAdresse').value   = d.adresse || '';
        document.getElementById('pVille').value     = d.ville || '';
        document.getElementById('pCP').value        = d.code_postal || '';
        document.getElementById('pPays').value      = d.pays || '';
        document.getElementById('pRib').value       = d.rib || '';
        document.getElementById('pNotes').value     = d.notes || '';
    } else {
        document.getElementById('propAction').value = 'ajouter';
        document.getElementById('propTitle').innerHTML = '<i class="bi bi-person-plus me-2"></i>Nouveau propriétaire';
        document.getElementById('propId').value = '';
        document.querySelector('#modalProprio form').reset();
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
