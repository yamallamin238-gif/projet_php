<?php
require_once __DIR__ . '/../config/app.php';
requireLogin();
$currentUser = getCurrentUser();
$pageTitle   = 'Gestion des loyers — ' . APP_NAME;
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'generer') {
        // Générer une quittance
        $contrat = $db->prepare("SELECT * FROM contrats WHERE id=? AND statut='en_cours'");
        $contrat->execute([(int)$_POST['contrat_id']]);
        $c = $contrat->fetch();
        if ($c) {
            $num    = generateNumero('QUIT');
            $debut  = $_POST['periode_debut'];
            $fin    = $_POST['periode_fin'];
            $echeance = date('Y-m-d', strtotime($debut . ' +' . ($c['jour_echeance']-1) . ' days'));
            $total  = $c['loyer_mensuel'] + $c['charges_mensuelles'];
            $db->prepare("INSERT INTO quittances (contrat_id,numero,periode_debut,periode_fin,date_echeance,montant_loyer,montant_charges,montant_total,statut)
                VALUES (?,?,?,?,?,?,?,?,'en_attente')")->execute([
                $c['id'], $num, $debut, $fin, $echeance,
                $c['loyer_mensuel'], $c['charges_mensuelles'], $total
            ]);
            setFlash('success', "Quittance $num générée.");
        }
    } elseif ($action === 'encaisser') {
        $qId   = (int)$_POST['quittance_id'];
        $montant = (float)$_POST['montant'];
        $mode  = $_POST['mode_paiement'];
        $date  = $_POST['date_paiement'];
        $ref   = $_POST['reference'];

        // Récupérer quittance
        $q = $db->prepare("SELECT * FROM quittances WHERE id=?");
        $q->execute([$qId]);
        $quittance = $q->fetch();

        if ($quittance) {
            $nouveau_paye = $quittance['montant_paye'] + $montant;
            $statut = $nouveau_paye >= $quittance['montant_total'] ? 'paye' : 'partiel';

            $db->prepare("UPDATE quittances SET montant_paye=?, date_paiement=?, mode_paiement=?, reference_paiement=?, statut=? WHERE id=?")->execute([
                $nouveau_paye, $date, $mode, $ref, $statut, $qId
            ]);
            // Enregistrer le paiement
            $db->prepare("INSERT INTO paiements (quittance_id,montant,date_paiement,mode_paiement,reference,utilisateur_id) VALUES (?,?,?,?,?,?)")->execute([
                $qId, $montant, $date, $mode, $ref, $_SESSION['user_id']
            ]);
            setFlash('success', "Paiement de " . formatMontant($montant) . " enregistré.");
        }
    }
    header('Location: ' . APP_URL . '/pages/quittances.php' . (isset($_GET['contrat_id']) ? '?contrat_id=' . (int)$_GET['contrat_id'] : ''));
    exit;
}

$contratFilter = (int)($_GET['contrat_id'] ?? 0);
$statutFilter  = $_GET['statut'] ?? '';

$where  = [];
$params = [];
if ($contratFilter) { $where[] = "q.contrat_id = ?"; $params[] = $contratFilter; }
if ($statutFilter)  { $where[] = "q.statut = ?";     $params[] = $statutFilter; }
$whereSQL = $where ? "WHERE " . implode(' AND ', $where) : '';

$quittances = $db->prepare("
    SELECT q.*, c.numero AS contrat_num, c.loyer_mensuel, c.locataire_id,
           CONCAT(l.prenom,' ',l.nom) AS locataire, l.telephone,
           lg.reference AS logement, i.nom AS immeuble
    FROM quittances q
    JOIN contrats c ON q.contrat_id = c.id
    JOIN locataires l ON c.locataire_id = l.id
    JOIN logements lg ON c.logement_id = lg.id
    JOIN immeubles i ON lg.immeuble_id = i.id
    $whereSQL
    ORDER BY q.date_echeance DESC
");
$quittances->execute($params);
$quittances = $quittances->fetchAll();

$contrats = $db->query("SELECT c.id, c.numero, CONCAT(l.prenom,' ',l.nom) AS locataire, c.loyer_mensuel, c.charges_mensuelles
    FROM contrats c JOIN locataires l ON c.locataire_id=l.id WHERE c.statut='en_cours' ORDER BY c.numero")->fetchAll();

$totalImpayes = array_sum(array_column(array_filter($quittances, fn($q) => in_array($q['statut'], ['retard','partiel','en_attente'])), 'montant_total'))
              - array_sum(array_column(array_filter($quittances, fn($q) => in_array($q['statut'], ['retard','partiel','en_attente'])), 'montant_paye'));
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i>Gestion des loyers</h4>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGenerer">
    <i class="bi bi-plus-lg me-1"></i>Générer quittance
  </button>
</div>

<!-- Filtres -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label small mb-1">Contrat</label>
        <select name="contrat_id" class="form-select form-select-sm">
          <option value="">Tous les contrats</option>
          <?php foreach ($contrats as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $contratFilter==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['numero'] . ' — ' . $c['locataire']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1">Statut</label>
        <select name="statut" class="form-select form-select-sm">
          <option value="">Tous</option>
          <option value="en_attente" <?= $statutFilter=='en_attente'?'selected':'' ?>>En attente</option>
          <option value="paye"       <?= $statutFilter=='paye'?'selected':'' ?>>Payé</option>
          <option value="retard"     <?= $statutFilter=='retard'?'selected':'' ?>>Retard</option>
          <option value="partiel"    <?= $statutFilter=='partiel'?'selected':'' ?>>Partiel</option>
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-outline-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filtrer</button>
        <a href="?" class="btn btn-outline-secondary btn-sm ms-1">Réinitialiser</a>
      </div>
    </form>
  </div>
</div>

<?php if ($totalImpayes > 0): ?>
<div class="alert alert-danger d-flex align-items-center mb-4">
  <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
  <div><strong>Total impayés affiché : <?= formatMontant($totalImpayes) ?></strong></div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>N° Quittance</th><th>Locataire</th><th>Période</th><th>Échéance</th><th>Total</th><th>Payé</th><th>Reste</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($quittances as $q): ?>
        <?php $reste = $q['montant_total'] - $q['montant_paye']; ?>
        <tr class="<?= $q['statut']==='retard' ? 'table-danger' : '' ?>">
          <td><span class="fw-bold text-primary small"><?= htmlspecialchars($q['numero']) ?></span></td>
          <td>
            <div class="fw-semibold"><?= htmlspecialchars($q['locataire']) ?></div>
            <small class="text-muted"><?= htmlspecialchars($q['logement']) ?></small>
          </td>
          <td><small><?= formatDate($q['periode_debut']) ?> - <?= formatDate($q['periode_fin']) ?></small></td>
          <td><?= formatDate($q['date_echeance']) ?></td>
          <td><?= formatMontant($q['montant_total']) ?></td>
          <td class="text-success"><?= formatMontant($q['montant_paye']) ?></td>
          <td class="<?= $reste > 0 ? 'text-danger fw-bold' : '' ?>"><?= formatMontant($reste) ?></td>
          <td><?= badgeStatut($q['statut']) ?></td>
          <td>
            <?php if ($q['statut'] !== 'paye' && $q['statut'] !== 'annule'): ?>
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalEncaisser"
                    data-id="<?= $q['id'] ?>"
                    data-num="<?= htmlspecialchars($q['numero']) ?>"
                    data-reste="<?= $reste ?>"
                    data-locataire="<?= htmlspecialchars($q['locataire']) ?>">
              <i class="bi bi-cash-coin"></i> Encaisser
            </button>
            <?php else: ?>
            <span class="text-success small"><i class="bi bi-check-circle"></i> Soldé</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL GÉNÉRER QUITTANCE -->
<div class="modal fade" id="modalGenerer" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="generer">
        <div class="modal-header">
          <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-plus me-2"></i>Générer une quittance</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Contrat *</label>
            <select name="contrat_id" class="form-select" required>
              <option value="">— Choisir un contrat —</option>
              <?php foreach ($contrats as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['numero'] . ' — ' . $c['locataire'] . ' (' . formatMontant($c['loyer_mensuel'] + $c['charges_mensuelles']) . ')') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Période début *</label>
              <input type="date" name="periode_debut" class="form-control" required value="<?= date('Y-m-01') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Période fin *</label>
              <input type="date" name="periode_fin" class="form-control" required value="<?= date('Y-m-t') ?>">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Générer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL ENCAISSER -->
<div class="modal fade" id="modalEncaisser" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="encaisser">
        <input type="hidden" name="quittance_id" id="encQId">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title"><i class="bi bi-cash-coin me-2"></i>Encaisser un paiement</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Quittance : <strong id="encNum"></strong></p>
          <p>Locataire : <strong id="encLoc"></strong></p>
          <p>Reste à payer : <strong class="text-danger" id="encReste"></strong></p>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Montant encaissé (FCFA) *</label>
              <input type="number" name="montant" id="encMontant" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Date de paiement *</label>
              <input type="date" name="date_paiement" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Mode de paiement</label>
              <select name="mode_paiement" class="form-select">
                <option value="especes">Espèces</option>
                <option value="virement">Virement bancaire</option>
                <option value="cheque">Chèque</option>
                <option value="mobile_money">Mobile Money</option>
                <option value="autre">Autre</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Référence</label>
              <input type="text" name="reference" class="form-control" placeholder="N° virement, chèque...">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Valider</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('modalEncaisser').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('encQId').value    = btn.dataset.id;
    document.getElementById('encNum').textContent  = btn.dataset.num;
    document.getElementById('encLoc').textContent  = btn.dataset.locataire;
    document.getElementById('encReste').textContent = new Intl.NumberFormat('fr-FR').format(btn.dataset.reste) + ' FCFA';
    document.getElementById('encMontant').value     = btn.dataset.reste;
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
