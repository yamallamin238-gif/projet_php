<?php
$page_title = 'Contrats';
require_once '../config/app.php';
requireLogin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'ajouter') {
    $stmt = $db->prepare("INSERT INTO contrats (locataire_id, logement_id, date_debut, date_fin, loyer, caution, statut) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$_POST['locataire_id'], $_POST['logement_id'], $_POST['date_debut'], $_POST['date_fin'], $_POST['loyer'], $_POST['caution'], 'actif']);
    $db->prepare("UPDATE logements SET statut='occupe' WHERE id=?")->execute([$_POST['logement_id']]);
  }
  if ($_POST['action'] === 'resilier') {
    $db->prepare("UPDATE contrats SET statut='resilie' WHERE id=?")->execute([$_POST['id']]);
    $db->prepare("UPDATE logements SET statut='libre' WHERE id=(SELECT logement_id FROM contrats WHERE id=?)")->execute([$_POST['id']]);
  }
  if ($_POST['action'] === 'supprimer') {
    $db->prepare("DELETE FROM contrats WHERE id=?")->execute([$_POST['id']]);
  }
  header("Location: contrats.php"); exit;
}

$contrats = $db->query("
  SELECT c.*, CONCAT(l.nom,' ',l.prenom) as locataire_nom, b.titre as bien_titre
  FROM contrats c
  JOIN locataires l ON c.locataire_id = l.id
  JOIN logements b ON c.logement_id = b.id
  ORDER BY c.date_debut DESC
")->fetchAll();
$locataires = $db->query("SELECT * FROM locataires WHERE actif=1 ORDER BY nom")->fetchAll();
$biens_libres = $db->query("SELECT * FROM logements WHERE statut='libre' ORDER BY titre")->fetchAll();

require_once '../includes/header.php';
?>
<div class="page-content">
  <div class="card">
    <div class="card-header">
      <div class="card-title">Contrats de Location</div>
      <button onclick="document.getElementById('modalContratAjout').style.display='flex'" style="padding:8px 16px;background:var(--accent);color:#000;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">+ Nouveau contrat</button>
    </div>
    <div class="card-body">
      <table style="width:100%;border-collapse:collapse;">
        <thead><tr>
          <th style="padding:10px;text-align:left;">Locataire</th>
          <th>Bien</th><th>Début</th><th>Fin</th><th>Loyer</th><th>Statut</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach($contrats as $c): ?>
        <tr style="border-top:1px solid var(--border);">
          <td style="padding:10px;"><?= htmlspecialchars($c['locataire_nom']) ?></td>
          <td><?= htmlspecialchars($c['bien_titre']) ?></td>
          <td><?= $c['date_debut'] ?></td>
          <td><?= $c['date_fin'] ?></td>
          <td><?= number_format($c['loyer'],0,',',' ') ?> FCFA</td>
          <td><span style="padding:3px 10px;border-radius:20px;font-size:12px;background:<?= $c['statut']==='actif'?'#27ae60':'#95a5a6' ?>;color:#fff;"><?= $c['statut'] ?></span></td>
          <td>
            <?php if($c['statut']==='actif'): ?>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Résilier ce contrat ?')">
              <input type="hidden" name="action" value="resilier">
              <input type="hidden" name="id" value="<?= $c['id'] ?>">
              <button type="submit" style="background:#e67e22;color:#fff;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;">Résilier</button>
            </form>
            <?php endif; ?>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
              <input type="hidden" name="action" value="supprimer">
              <input type="hidden" name="id" value="<?= $c['id'] ?>">
              <button type="submit" style="background:#e74c3c;color:#fff;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;">Supprimer</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div id="modalContratAjout" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:var(--bg-card);padding:30px;border-radius:10px;width:420px;">
    <h3 style="margin-bottom:20px;">Nouveau contrat</h3>
    <form method="POST">
      <input type="hidden" name="action" value="ajouter">
      <label style="color:var(--text-muted);font-size:13px;">Locataire</label>
      <select name="locataire_id" required style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);">
        <option value="">-- Choisir --</option>
        <?php foreach($locataires as $l): ?>
        <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['nom'].' '.$l['prenom']) ?></option>
        <?php endforeach; ?>
      </select><br>
      <label style="color:var(--text-muted);font-size:13px;">Bien (libres uniquement)</label>
      <select name="logement_id" required style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);">
        <option value="">-- Choisir --</option>
        <?php foreach($biens_libres as $b): ?>
        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['titre']) ?> — <?= number_format($b['loyer_mensuel'],0,',',' ') ?> FCFA</option>
        <?php endforeach; ?>
      </select><br>
      <label style="color:var(--text-muted);font-size:13px;">Date début</label>
      <input name="date_debut" type="date" required style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <label style="color:var(--text-muted);font-size:13px;">Date fin</label>
      <input name="date_fin" type="date" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <input name="loyer" type="number" placeholder="Loyer mensuel (FCFA)" required style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <input name="caution" type="number" placeholder="Caution (FCFA)" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <div style="display:flex;gap:10px;">
        <button type="submit" style="flex:1;padding:10px;background:var(--accent);color:#000;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">Créer</button>
        <button type="button" onclick="document.getElementById('modalContratAjout').style.display='none'" style="flex:1;padding:10px;background:var(--border);color:var(--text-primary);border:none;border-radius:6px;cursor:pointer;">Annuler</button>
      </div>
    </form>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>