<?php
$page_title = 'Contrats';
require_once '../config/app.php';
requireLogin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'ajouter') {
        $num = generateNumero('CTR');
        $stmt = $db->prepare("INSERT INTO contrats (numero, locataire_id, logement_id, date_debut, date_fin, loyer_mensuel, charges_mensuelles, depot_garantie, statut) VALUES (?,?,?,?,?,?,?,?,'en_cours')");
        $stmt->execute([$num, $_POST['locataire_id'], $_POST['logement_id'], $_POST['date_debut'], $_POST['date_fin'] ?: null, $_POST['loyer_mensuel'], $_POST['charges_mensuelles'] ?: 0, $_POST['depot_garantie'] ?: 0]);
        $db->prepare("UPDATE logements SET statut='occupe' WHERE id=?")->execute([$_POST['logement_id']]);
        setFlash('success', 'Contrat créé : '.$num);
    }
    if ($_POST['action'] === 'resilier') {
        $db->prepare("UPDATE contrats SET statut='resilie', date_resiliation=NOW(), motif_resiliation=? WHERE id=?")->execute([$_POST['motif'] ?? '', $_POST['id']]);
        $row = $db->prepare("SELECT logement_id FROM contrats WHERE id=?");
        $row->execute([$_POST['id']]);
        $lid = $row->fetchColumn();
        if($lid) $db->prepare("UPDATE logements SET statut='libre' WHERE id=?")->execute([$lid]);
        setFlash('warning', 'Contrat résilié.');
    }
    if ($_POST['action'] === 'supprimer') {
        $db->prepare("DELETE FROM contrats WHERE id=?")->execute([$_POST['id']]);
        setFlash('warning', 'Contrat supprimé.');
    }
    header("Location: contrats.php"); exit;
}

$contrats = $db->query("
    SELECT c.*, CONCAT(l.civilite,' ',l.nom,' ',l.prenom) as locataire_nom, lg.reference as logement_ref, lg.type as logement_type
    FROM contrats c
    JOIN locataires l ON c.locataire_id = l.id
    JOIN logements lg ON c.logement_id = lg.id
    ORDER BY c.created_at DESC
")->fetchAll();
$locataires = $db->query("SELECT id, civilite, nom, prenom FROM locataires WHERE statut='actif' ORDER BY nom")->fetchAll();
$logements_libres = $db->query("SELECT id, reference, type, loyer_base FROM logements WHERE statut='libre' ORDER BY reference")->fetchAll();
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
      <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2 text-purple"></i>Contrats de Location</h5>
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalContratAjout">
        <i class="bi bi-plus-lg me-1"></i>Nouveau contrat
      </button>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr><th>Numéro</th><th>Locataire</th><th>Logement</th><th>Début</th><th>Fin</th><th>Loyer</th><th>Statut</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php foreach($contrats as $c): ?>
          <tr>
            <td><code><?= htmlspecialchars($c['numero']) ?></code></td>
            <td><?= htmlspecialchars($c['locataire_nom']) ?></td>
            <td><?= htmlspecialchars($c['logement_ref'].' ('.$c['logement_type'].')') ?></td>
            <td><?= formatDate($c['date_debut']) ?></td>
            <td><?= formatDate($c['date_fin']) ?></td>
            <td><?= number_format($c['loyer_mensuel'],0,',',' ') ?> FCFA</td>
            <td><?= badgeStatut($c['statut']) ?></td>
            <td>
              <?php if($c['statut']==='en_cours'): ?>
              <button class="btn btn-sm btn-outline-warning"
                onclick="document.getElementById('resilId').value=<?= $c['id'] ?>"
                data-bs-toggle="modal" data-bs-target="#modalResilier" title="Résilier">
                <i class="bi bi-x-circle"></i>
              </button>
              <?php endif; ?>
              <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce contrat ?')">
                <input type="hidden" name="action" value="supprimer">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($contrats)): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">Aucun contrat enregistré</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ajout -->
<div class="modal fade" id="modalContratAjout" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Nouveau contrat</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="action" value="ajouter">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Locataire *</label>
            <select name="locataire_id" class="form-select" required>
              <option value="">-- Choisir --</option>
              <?php foreach($locataires as $l): ?>
              <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['civilite'].' '.$l['nom'].' '.$l['prenom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Logement libre *</label>
            <select name="logement_id" class="form-select" required>
              <option value="">-- Choisir --</option>
              <?php foreach($logements_libres as $lg): ?>
              <option value="<?= $lg['id'] ?>"><?= htmlspecialchars($lg['reference'].' — '.$lg['type']) ?> (<?= number_format($lg['loyer_base'],0,',',' ') ?> FCFA)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Date début *</label>
            <input name="date_debut" type="date" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Date fin (optionnel)</label>
            <input name="date_fin" type="date" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Loyer mensuel (FCFA) *</label>
            <input name="loyer_mensuel" type="number" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Charges mensuelles</label>
            <input name="charges_mensuelles" type="number" class="form-control" value="0">
          </div>
          <div class="col-md-4">
            <label class="form-label">Dépôt de garantie</label>
            <input name="depot_garantie" type="number" class="form-control" value="0">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary">Créer le contrat</button>
      </div>
    </form>
  </div></div>
</div>

<!-- Modal Résilier -->
<div class="modal fade" id="modalResilier" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header bg-warning"><h5 class="modal-title">Résilier le contrat</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="action" value="resilier">
        <input type="hidden" name="id" id="resilId">
        <div class="mb-3">
          <label class="form-label">Motif de résiliation</label>
          <textarea name="motif" class="form-control" rows="3" placeholder="Motif (optionnel)..."></textarea>
        </div>
        <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Le logement sera remis en statut <strong>Libre</strong>.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-warning">Confirmer la résiliation</button>
      </div>
    </form>
  </div></div>
</div>
<?php require_once '../includes/footer.php'; ?>