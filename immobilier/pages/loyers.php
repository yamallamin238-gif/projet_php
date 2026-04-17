<?php
$page_title = 'Loyers';
require_once '../config/app.php';
requireLogin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'ajouter') {
    $stmt = $db->prepare("INSERT INTO loyers (contrat_id, mois, montant, statut, date_echeance) VALUES (?,?,?,?,?)");
    $stmt->execute([$_POST['contrat_id'], $_POST['mois'], $_POST['montant'], 'impaye', $_POST['date_echeance']]);
  }
  if ($_POST['action'] === 'marquer_paye') {
    $db->prepare("UPDATE loyers SET statut='paye', date_paiement=NOW() WHERE id=?")->execute([$_POST['id']]);
    $stmt = $db->prepare("INSERT INTO paiements (loyer_id, montant, date_paiement, mode_paiement) VALUES (?,?,NOW(),?)");
    $stmt->execute([$_POST['id'], $_POST['montant'], $_POST['mode']]);
  }
  if ($_POST['action'] === 'supprimer') {
    $db->prepare("DELETE FROM loyers WHERE id=?")->execute([$_POST['id']]);
  }
  header("Location: loyers.php"); exit;
}

$loyers = $db->query("
  SELECT ly.*, c.loyer as loyer_contrat,
    CONCAT(l.nom,' ',l.prenom) as locataire_nom, b.titre as bien_titre
  FROM loyers ly
  JOIN contrats c ON ly.contrat_id = c.id
  JOIN locataires l ON c.locataire_id = l.id
  JOIN logements b ON c.logement_id = b.id
  ORDER BY ly.date_echeance DESC
")->fetchAll();

$contrats_actifs = $db->query("
  SELECT c.id, CONCAT(l.nom,' ',l.prenom,' — ',b.titre) as label, c.loyer
  FROM contrats c
  JOIN locataires l ON c.locataire_id = l.id
  JOIN logements b ON c.logement_id = b.id
  WHERE c.statut = 'actif'
")->fetchAll();

require_once '../includes/header.php';
?>
<div class="page-content">
  <div class="card">
    <div class="card-header">
      <div class="card-title">Gestion des Loyers</div>
      <button onclick="document.getElementById('modalLoyerAjout').style.display='flex'" style="padding:8px 16px;background:var(--accent);color:#000;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">+ Ajouter loyer</button>
    </div>
    <div class="card-body">
      <table style="width:100%;border-collapse:collapse;">
        <thead><tr>
          <th style="padding:10px;text-align:left;">Locataire</th>
          <th>Bien</th><th>Mois</th><th>Montant</th><th>Échéance</th><th>Statut</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach($loyers as $l): ?>
        <tr style="border-top:1px solid var(--border);">
          <td style="padding:10px;"><?= htmlspecialchars($l['locataire_nom']) ?></td>
          <td><?= htmlspecialchars($l['bien_titre']) ?></td>
          <td><?= $l['mois'] ?></td>
          <td><?= number_format($l['montant'],0,',',' ') ?> FCFA</td>
          <td><?= $l['date_echeance'] ?></td>
          <td>
            <span style="padding:3px 10px;border-radius:20px;font-size:12px;background:<?= $l['statut']==='paye'?'#27ae60':'#e74c3c' ?>;color:#fff;">
              <?= $l['statut'] === 'paye' ? 'Payé' : 'Impayé' ?>
            </span>
          </td>
          <td>
            <?php if($l['statut'] === 'impaye'): ?>
            <button onclick="payerLoyer(<?= $l['id'] ?>, <?= $l['montant'] ?>)" style="background:#27ae60;color:#fff;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;">Marquer payé</button>
            <?php endif; ?>
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

<!-- Modal Ajouter -->
<div id="modalLoyerAjout" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:var(--bg-card);padding:30px;border-radius:10px;width:420px;">
    <h3 style="margin-bottom:20px;">Ajouter un loyer</h3>
    <form method="POST">
      <input type="hidden" name="action" value="ajouter">
      <label style="color:var(--text-muted);font-size:13px;">Contrat actif</label>
      <select name="contrat_id" required style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);">
        <option value="">-- Choisir --</option>
        <?php foreach($contrats_actifs as $c): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['label']) ?></option>
        <?php endforeach; ?>
      </select><br>
      <input name="mois" type="month" required style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <input name="montant" type="number" placeholder="Montant (FCFA)" required style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <label style="color:var(--text-muted);font-size:13px;">Date d'échéance</label>
      <input name="date_echeance" type="date" required style="width:100%;padding:8px;margin-bottom:10px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);"><br>
      <div style="display:flex;gap:10px;">
        <button type="submit" style="flex:1;padding:10px;background:var(--accent);color:#000;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">Enregistrer</button>
        <button type="button" onclick="document.getElementById('modalLoyerAjout').style.display='none'" style="flex:1;padding:10px;background:var(--border);color:var(--text-primary);border:none;border-radius:6px;cursor:pointer;">Annuler</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Payer -->
<div id="modalPayer" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:var(--bg-card);padding:30px;border-radius:10px;width:360px;">
    <h3 style="margin-bottom:20px;">Confirmer le paiement</h3>
    <form method="POST">
      <input type="hidden" name="action" value="marquer_paye">
      <input type="hidden" name="id" id="payId">
      <input type="hidden" name="montant" id="payMontant">
      <p style="color:var(--text-muted);margin-bottom:15px;">Montant : <strong id="payAffichage" style="color:var(--text-primary);"></strong> FCFA</p>
      <label style="color:var(--text-muted);font-size:13px;">Mode de paiement</label>
      <select name="mode" style="width:100%;padding:8px;margin-bottom:15px;background:var(--bg-main);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);">
        <option value="espece">Espèces</option>
        <option value="wave">Wave</option>
        <option value="orange_money">Orange Money</option>
        <option value="virement">Virement</option>
      </select><br>
      <div style="display:flex;gap:10px;">
        <button type="submit" style="flex:1;padding:10px;background:#27ae60;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:bold;">Confirmer</button>
        <button type="button" onclick="document.getElementById('modalPayer').style.display='none'" style="flex:1;padding:10px;background:var(--border);color:var(--text-primary);border:none;border-radius:6px;cursor:pointer;">Annuler</button>
      </div>
    </form>
  </div>
</div>

<script>
function payerLoyer(id, montant) {
  document.getElementById('payId').value = id;
  document.getElementById('payMontant').value = montant;
  document.getElementById('payAffichage').textContent = montant.toLocaleString('fr-FR');
  document.getElementById('modalPayer').style.display = 'flex';
}
</script>
<?php require_once '../includes/footer.php'; ?>