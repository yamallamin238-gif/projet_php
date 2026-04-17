<?php
$page_title = 'Locataires';
require_once '../config/app.php';
requireLogin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'ajouter') {
        $stmt = $db->prepare("INSERT INTO locataires (civilite, nom, prenom, email, telephone, num_cni, statut) VALUES (?,?,?,?,?,?,'actif')");
        $stmt->execute([$_POST['civilite'], $_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['telephone'], $_POST['num_cni']]);
        setFlash('success', 'Locataire ajouté avec succès.');
    }
    if ($_POST['action'] === 'modifier') {
        $stmt = $db->prepare("UPDATE locataires SET civilite=?, nom=?, prenom=?, email=?, telephone=?, num_cni=? WHERE id=?");
        $stmt->execute([$_POST['civilite'], $_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['telephone'], $_POST['num_cni'], $_POST['id']]);
        setFlash('success', 'Locataire modifié.');
    }
    if ($_POST['action'] === 'supprimer') {
        $db->prepare("DELETE FROM locataires WHERE id=?")->execute([$_POST['id']]);
        setFlash('warning', 'Locataire supprimé.');
    }
    header("Location: locataires.php"); exit;
}

$locataires = $db->query("SELECT * FROM locataires ORDER BY nom, prenom")->fetchAll();
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
      <h5 class="mb-0"><i class="bi bi-people-fill me-2 text-success"></i>Locataires</h5>
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjout">
        <i class="bi bi-plus-lg me-1"></i>Ajouter
      </button>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Nom complet</th><th>Téléphone</th><th>Email</th><th>CNI</th><th>Statut</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($locataires as $l): ?>
          <tr>
            <td><?= htmlspecialchars($l['civilite'].' '.$l['nom'].' '.$l['prenom']) ?></td>
            <td><?= htmlspecialchars($l['telephone']) ?></td>
            <td><?= htmlspecialchars($l['email']) ?></td>
            <td><?= htmlspecialchars($l['num_cni']) ?></td>
            <td><?= badgeStatut($l['statut']) ?></td>
            <td>
              <button class="btn btn-sm btn-outline-primary"
                onclick='editLocataire(<?= htmlspecialchars(json_encode($l, JSON_HEX_APOS|JSON_HEX_QUOT)) ?>)'
                data-bs-toggle="modal" data-bs-target="#modalModif">
                <i class="bi bi-pencil"></i>
              </button>
              <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce locataire ?')">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($locataires)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Aucun locataire enregistré</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ajout -->
<div class="modal fade" id="modalAjout" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Nouveau locataire</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="action" value="ajouter">
        <div class="mb-3">
          <label class="form-label">Civilité</label>
          <select name="civilite" class="form-select">
            <option value="M.">M.</option><option value="Mme">Mme</option><option value="Mlle">Mlle</option>
          </select>
        </div>
        <div class="row g-2 mb-3">
          <div class="col"><input name="nom" class="form-control" placeholder="Nom *" required></div>
          <div class="col"><input name="prenom" class="form-control" placeholder="Prénom"></div>
        </div>
        <div class="mb-3"><input name="telephone" class="form-control" placeholder="Téléphone"></div>
        <div class="mb-3"><input name="email" type="email" class="form-control" placeholder="Email"></div>
        <div class="mb-3"><input name="num_cni" class="form-control" placeholder="Numéro CNI"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div></div>
</div>

<!-- Modal Modifier -->
<div class="modal fade" id="modalModif" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Modifier locataire</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="action" value="modifier">
        <input type="hidden" name="id" id="editId">
        <div class="mb-3">
          <label class="form-label">Civilité</label>
          <select name="civilite" id="editCivilite" class="form-select">
            <option value="M.">M.</option><option value="Mme">Mme</option><option value="Mlle">Mlle</option>
          </select>
        </div>
        <div class="row g-2 mb-3">
          <div class="col"><input name="nom" id="editNom" class="form-control" placeholder="Nom *" required></div>
          <div class="col"><input name="prenom" id="editPrenom" class="form-control" placeholder="Prénom"></div>
        </div>
        <div class="mb-3"><input name="telephone" id="editTel" class="form-control" placeholder="Téléphone"></div>
        <div class="mb-3"><input name="email" id="editEmail" type="email" class="form-control" placeholder="Email"></div>
        <div class="mb-3"><input name="num_cni" id="editCni" class="form-control" placeholder="Numéro CNI"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary">Sauvegarder</button>
      </div>
    </form>
  </div></div>
</div>

<script>
function editLocataire(l) {
  document.getElementById('editId').value = l.id;
  document.getElementById('editCivilite').value = l.civilite;
  document.getElementById('editNom').value = l.nom;
  document.getElementById('editPrenom').value = l.prenom;
  document.getElementById('editTel').value = l.telephone;
  document.getElementById('editEmail').value = l.email;
  document.getElementById('editCni').value = l.num_cni;
}
</script>
<?php require_once '../includes/footer.php'; ?>