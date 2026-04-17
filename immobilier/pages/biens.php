<?php
$page_title = 'Biens Immobiliers';
require_once '../config/app.php';
requireLogin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'ajouter') {
    $stmt = $db->prepare("INSERT INTO logements (titre, type, adresse, surface, loyer_mensuel, statut) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$_POST['titre'], $_POST['type'], $_POST['adresse'], $_POST['surface'], $_POST['loyer'], $_POST['statut']]);
  }
  if ($_POST['action'] === 'modifier') {
    $stmt = $db->prepare("UPDATE logements SET titre=?, type=?, adresse=?, surface=?, loyer_mensuel=?, statut=? WHERE id=?");
    $stmt->execute([$_POST['titre'], $_POST['type'], $_POST['adresse'], $_POST['surface'], $_POST['loyer'], $_POST['statut'], $_POST['id']]);
  }
  if ($_POST['action'] === 'supprimer') {
    $db->prepare("DELETE FROM logements WHERE id=?")->execute([$_POST['id']]);
  }
  header("Location: biens.php"); exit;
}

$biens = $db->query("SELECT * FROM logements ORDER BY titre")->fetchAll();
require_once '../includes/header.php';
?>
<div class="page-content">
  <div class="card">
    <div class="card-header">
      <div class="card-title">Biens Immobiliers</div>
      <button class="btn btn-primary" onclick="document.getElementById('modalBienAjout').style.display='flex'">+ Ajouter</button>
    </div>
    <div class="card-body">
      <table style="width:100%;border-collapse:collapse;">
        <thead><tr style="background:var(--bg-card);">
          <th style="padding:10px;text-align:left;">Titre</th>
          <th>Type</th><th>Adresse</th><th>Surface</th><th>Loyer</th><th>Statut</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach($biens as $b): ?>
        <tr style="border-top:1px solid var(--border);">
          <td style="padding:10px;"><?= htmlspecialchars($b['titre']) ?></td>
          <td><?= htmlspecialchars($b['type']) ?></td>
          <td><?= htmlspecialchars($b['adresse']) ?></td>
          <td><?= $b['surface'] ?> m²</td>
          <td><?= number_format($b['loyer_mensuel'],0,',',' ') ?> FCFA</td>
          <td><span style="padding:3px 10px;border-radius:20px;background:<?= $b['statut']==='libre'?'#27ae60':'#e74c3c' ?>;color:#fff;font-size:12px;"><?= htmlspecialchars($b['statut']) ?></span></td>
          <td>
            <button onclick="editBien(<?= htmlspecialchars(json_encode($b)) ?>)" style="background:var(--accent);color:#000;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;">Modifier</button>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce bien ?')">
              <input type="hidden" name="action" value="supprimer">
              <input type="hidden" name="id" value="<?= $b['id'] ?>">
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

<div id="modalBienAjout" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:var(--bg-card);padding:30px;border-radius:10px;width:420px;">
    <h3 style="margin-bottom:20px;">Nouveau bien</h3>
    <form method="POST">
      <input type="hidden" name="action" value="ajouter">
      <input name="titre" placeholder="Titre" required style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <select name="type" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);">
        <option value="appartement">Appartement</option>
        <option value="maison">Maison</option>
        <option value="studio">Studio</option>
        <option value="bureau">Bureau</option>
      </select><br>
      <input name="adresse" placeholder="Adresse" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <input name="surface" placeholder="Surface (m²)" type="number" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <input name="loyer" placeholder="Loyer mensuel (FCFA)" type="number" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <select name="statut" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);">
        <option value="libre">Libre</option>
        <option value="occupe">Occupé</option>
      </select><br>
      <div style="display:flex;gap:10px;">
        <button type="submit" style="flex:1;padding:10px;background:var(--accent);color:#000;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">Enregistrer</button>
        <button type="button" onclick="document.getElementById('modalBienAjout').style.display='none'" style="flex:1;padding:10px;background:var(--border);color:var(--text-primary);border:none;border-radius:6px;cursor:pointer;">Annuler</button>
      </div>
    </form>
  </div>
</div>

<div id="modalBienModif" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:var(--bg-card);padding:30px;border-radius:10px;width:420px;">
    <h3 style="margin-bottom:20px;">Modifier bien</h3>
    <form method="POST">
      <input type="hidden" name="action" value="modifier">
      <input type="hidden" name="id" id="bEditId">
      <input name="titre" id="bEditTitre" placeholder="Titre" required style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <select name="type" id="bEditType" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);">
        <option value="appartement">Appartement</option>
        <option value="maison">Maison</option>
        <option value="studio">Studio</option>
        <option value="bureau">Bureau</option>
      </select><br>
      <input name="adresse" id="bEditAdresse" placeholder="Adresse" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <input name="surface" id="bEditSurface" type="number" placeholder="Surface" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <input name="loyer" id="bEditLoyer" type="number" placeholder="Loyer" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <select name="statut" id="bEditStatut" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);">
        <option value="libre">Libre</option>
        <option value="occupe">Occupé</option>
      </select><br>
      <div style="display:flex;gap:10px;">
        <button type="submit" style="flex:1;padding:10px;background:var(--accent);color:#000;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">Sauvegarder</button>
        <button type="button" onclick="document.getElementById('modalBienModif').style.display='none'" style="flex:1;padding:10px;background:var(--border);color:var(--text-primary);border:none;border-radius:6px;cursor:pointer;">Annuler</button>
      </div>
    </form>
  </div>
</div>

<script>
function editBien(b) {
  document.getElementById('bEditId').value = b.id;
  document.getElementById('bEditTitre').value = b.titre;
  document.getElementById('bEditType').value = b.type;
  document.getElementById('bEditAdresse').value = b.adresse;
  document.getElementById('bEditSurface').value = b.surface;
  document.getElementById('bEditLoyer').value = b.loyer_mensuel;
  document.getElementById('bEditStatut').value = b.statut;
  document.getElementById('modalBienModif').style.display = 'flex';
}
</script>
<?php require_once '../includes/footer.php'; ?>