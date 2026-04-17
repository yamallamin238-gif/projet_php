<?php
$page_title = 'Locataires';
require_once '../config/app.php';
requireLogin();
$db = getDB();

// AJOUTER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'ajouter') {
    $stmt = $db->prepare("INSERT INTO locataires (nom, prenom, telephone, email, cin, actif) VALUES (?,?,?,?,?,1)");
    $stmt->execute([$_POST['nom'], $_POST['prenom'], $_POST['telephone'], $_POST['email'], $_POST['cin']]);
  }
  if ($_POST['action'] === 'modifier') {
    $stmt = $db->prepare("UPDATE locataires SET nom=?, prenom=?, telephone=?, email=?, cin=? WHERE id=?");
    $stmt->execute([$_POST['nom'], $_POST['prenom'], $_POST['telephone'], $_POST['email'], $_POST['cin'], $_POST['id']]);
  }
  if ($_POST['action'] === 'supprimer') {
    $db->prepare("DELETE FROM locataires WHERE id=?")->execute([$_POST['id']]);
  }
  header("Location: locataires.php"); exit;
}

$locataires = $db->query("SELECT * FROM locataires ORDER BY nom")->fetchAll();
require_once '../includes/header.php';
?>
<div class="page-content">
  <div class="card">
    <div class="card-header">
      <div class="card-title">Locataires</div>
      <button class="btn btn-primary" onclick="document.getElementById('modalAjout').style.display='flex'">+ Ajouter</button>
    </div>
    <div class="card-body">
      <table style="width:100%;border-collapse:collapse;">
        <thead><tr style="background:var(--bg-card);">
          <th style="padding:10px;text-align:left;">Nom</th>
          <th>Téléphone</th><th>Email</th><th>CIN</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach($locataires as $l): ?>
        <tr style="border-top:1px solid var(--border);">
          <td style="padding:10px;"><?= htmlspecialchars($l['nom'].' '.$l['prenom']) ?></td>
          <td><?= htmlspecialchars($l['telephone']) ?></td>
          <td><?= htmlspecialchars($l['email']) ?></td>
          <td><?= htmlspecialchars($l['cin']) ?></td>
          <td>
            <button onclick="editLocataire(<?= htmlspecialchars(json_encode($l)) ?>)" class="btn btn-sm" style="background:var(--accent);color:#000;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;">Modifier</button>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ?')">
              <input type="hidden" name="action" value="supprimer">
              <input type="hidden" name="id" value="<?= $l['id'] ?>">
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

<!-- Modal Ajout -->
<div id="modalAjout" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:var(--bg-card);padding:30px;border-radius:10px;width:400px;">
    <h3 style="margin-bottom:20px;">Nouveau locataire</h3>
    <form method="POST">
      <input type="hidden" name="action" value="ajouter">
      <input name="nom" placeholder="Nom" required style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <input name="prenom" placeholder="Prénom" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <input name="telephone" placeholder="Téléphone" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <input name="email" placeholder="Email" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <input name="cin" placeholder="CIN" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <div style="display:flex;gap:10px;margin-top:10px;">
        <button type="submit" style="flex:1;padding:10px;background:var(--accent);color:#000;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">Enregistrer</button>
        <button type="button" onclick="document.getElementById('modalAjout').style.display='none'" style="flex:1;padding:10px;background:var(--border);color:var(--text-primary);border:none;border-radius:6px;cursor:pointer;">Annuler</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Modifier -->
<div id="modalModif" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:var(--bg-card);padding:30px;border-radius:10px;width:400px;">
    <h3 style="margin-bottom:20px;">Modifier locataire</h3>
    <form method="POST">
      <input type="hidden" name="action" value="modifier">
      <input type="hidden" name="id" id="editId">
      <input name="nom" id="editNom" placeholder="Nom" required style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <input name="prenom" id="editPrenom" placeholder="Prénom" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <input name="telephone" id="editTel" placeholder="Téléphone" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <input name="email" id="editEmail" placeholder="Email" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <input name="cin" id="editCin" placeholder="CIN" style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <div style="display:flex;gap:10px;margin-top:10px;">
        <button type="submit" style="flex:1;padding:10px;background:var(--accent);color:#000;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">Sauvegarder</button>
        <button type="button" onclick="document.getElementById('modalModif').style.display='none'" style="flex:1;padding:10px;background:var(--border);color:var(--text-primary);border:none;border-radius:6px;cursor:pointer;">Annuler</button>
      </div>
    </form>
  </div>
</div>

<script>
function editLocataire(l) {
  document.getElementById('editId').value = l.id;
  document.getElementById('editNom').value = l.nom;
  document.getElementById('editPrenom').value = l.prenom;
  document.getElementById('editTel').value = l.telephone;
  document.getElementById('editEmail').value = l.email;
  document.getElementById('editCin').value = l.cin;
  document.getElementById('modalModif').style.display = 'flex';
}
</script>
<?php require_once '../includes/footer.php'; ?>