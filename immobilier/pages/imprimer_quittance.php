<?php
require_once __DIR__ . '/../config/app.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$db = getDB();

$q = $db->prepare("
    SELECT q.*, c.numero AS contrat_num, c.loyer_mensuel, c.charges_mensuelles,
           c.jour_echeance, c.type AS bail_type,
           CONCAT(l.civilite,' ',l.prenom,' ',l.nom) AS locataire_nom,
           l.adresse_precedente AS locataire_adresse, l.telephone,
           lg.reference AS logement_ref, lg.type AS logement_type,
           lg.surface, lg.etage,
           i.nom AS immeuble_nom, i.adresse AS immeuble_adresse, i.ville,
           p.nom AS proprio_nom, p.prenom AS proprio_prenom, p.adresse AS proprio_adresse
    FROM quittances q
    JOIN contrats c ON q.contrat_id = c.id
    JOIN locataires l ON c.locataire_id = l.id
    JOIN logements lg ON c.logement_id = lg.id
    JOIN immeubles i ON lg.immeuble_id = i.id
    JOIN proprietaires p ON i.proprietaire_id = p.id
    WHERE q.id = ?
");
$q->execute([$id]);
$quittance = $q->fetch();

if (!$quittance) {
    die('<p class="text-center text-danger mt-5">Quittance introuvable.</p>');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Quittance <?= htmlspecialchars($quittance['numero']) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body { background: #f5f5f5; }
    .doc { max-width: 750px; margin: 30px auto; background: #fff; padding: 50px; border-radius: 8px; box-shadow: 0 2px 20px rgba(0,0,0,.1); }
    .header-bar { border-bottom: 3px solid #0d6efd; padding-bottom: 15px; margin-bottom: 25px; }
    .label { font-size: .78rem; text-transform: uppercase; letter-spacing: .5px; color: #888; }
    .value { font-size: 1rem; font-weight: 600; }
    .montant-box { background: #f0f7ff; border: 2px solid #0d6efd; border-radius: 10px; padding: 20px; text-align: center; }
    .montant-box .montant { font-size: 2rem; font-weight: 800; color: #0d6efd; }
    .signature-zone { border-top: 2px solid #dee2e6; margin-top: 40px; padding-top: 20px; }
    @media print {
      body { background: #fff !important; }
      .no-print { display: none !important; }
      .doc { box-shadow: none; padding: 20px; }
    }
  </style>
</head>
<body>

<div class="doc">
  <!-- Boutons d'action -->
  <div class="no-print text-end mb-3">
    <button onclick="window.print()" class="btn btn-primary btn-sm me-2">
      <i class="bi bi-printer me-1"></i>Imprimer
    </button>
    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">Retour</a>
  </div>

  <!-- En-tête -->
  <div class="header-bar d-flex justify-content-between align-items-start">
    <div>
      <div class="label">Propriétaire / Bailleur</div>
      <div class="value"><?= htmlspecialchars($quittance['proprio_prenom'] . ' ' . $quittance['proprio_nom']) ?></div>
      <div class="text-muted small"><?= htmlspecialchars($quittance['proprio_adresse'] ?? '') ?></div>
    </div>
    <div class="text-end">
      <div class="fs-4 fw-bold text-primary">QUITTANCE DE LOYER</div>
      <div class="text-muted small">N° <?= htmlspecialchars($quittance['numero']) ?></div>
      <div class="text-muted small">Générée le <?= formatDate(date('Y-m-d')) ?></div>
    </div>
  </div>

  <!-- Parties -->
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="label mb-1">Locataire</div>
      <div class="value"><?= htmlspecialchars($quittance['locataire_nom']) ?></div>
      <div class="text-muted small"><?= htmlspecialchars($quittance['locataire_adresse'] ?? '') ?></div>
      <div class="text-muted small"><?= htmlspecialchars($quittance['telephone'] ?? '') ?></div>
    </div>
    <div class="col-md-6">
      <div class="label mb-1">Logement concerné</div>
      <div class="value"><?= htmlspecialchars($quittance['logement_ref']) ?> — <?= htmlspecialchars($quittance['logement_type']) ?></div>
      <div class="text-muted small"><?= htmlspecialchars($quittance['immeuble_nom']) ?></div>
      <div class="text-muted small"><?= htmlspecialchars($quittance['immeuble_adresse'] . ', ' . $quittance['ville']) ?></div>
      <?php if ($quittance['surface']): ?>
      <div class="text-muted small">Surface : <?= $quittance['surface'] ?> m² — Étage : <?= $quittance['etage'] ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Période -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="label">Période</div>
      <div class="value"><?= formatDate($quittance['periode_debut']) ?> au <?= formatDate($quittance['periode_fin']) ?></div>
    </div>
    <div class="col-md-4">
      <div class="label">Contrat</div>
      <div class="value"><?= htmlspecialchars($quittance['contrat_num']) ?></div>
      <div class="text-muted small">Type : <?= htmlspecialchars($quittance['bail_type']) ?></div>
    </div>
    <div class="col-md-4">
      <div class="label">Échéance</div>
      <div class="value"><?= formatDate($quittance['date_echeance']) ?></div>
    </div>
  </div>

  <!-- Détail montants -->
  <table class="table table-bordered mb-4">
    <thead class="table-light">
      <tr><th>Désignation</th><th class="text-end">Montant (FCFA)</th></tr>
    </thead>
    <tbody>
      <tr>
        <td>Loyer mensuel</td>
        <td class="text-end"><?= number_format($quittance['montant_loyer'], 0, ',', ' ') ?></td>
      </tr>
      <?php if ($quittance['montant_charges'] > 0): ?>
      <tr>
        <td>Charges locatives</td>
        <td class="text-end"><?= number_format($quittance['montant_charges'], 0, ',', ' ') ?></td>
      </tr>
      <?php endif; ?>
      <?php if ($quittance['penalite_retard'] > 0): ?>
      <tr class="table-danger">
        <td>Pénalité de retard</td>
        <td class="text-end text-danger"><?= number_format($quittance['penalite_retard'], 0, ',', ' ') ?></td>
      </tr>
      <?php endif; ?>
      <tr class="table-primary fw-bold">
        <td>TOTAL DÛ</td>
        <td class="text-end"><?= number_format($quittance['montant_total'], 0, ',', ' ') ?></td>
      </tr>
    </tbody>
  </table>

  <!-- Paiement -->
  <div class="montant-box mb-4">
    <div class="label">Montant reçu</div>
    <div class="montant"><?= number_format($quittance['montant_paye'], 0, ',', ' ') ?> FCFA</div>
    <?php if ($quittance['date_paiement']): ?>
    <div class="text-muted mt-1">
      Reçu le <?= formatDate($quittance['date_paiement']) ?>
      <?php if ($quittance['mode_paiement']): ?>
        — par <?= htmlspecialchars($quittance['mode_paiement']) ?>
      <?php endif; ?>
      <?php if ($quittance['reference_paiement']): ?>
        (Réf: <?= htmlspecialchars($quittance['reference_paiement']) ?>)
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php
    $reste = $quittance['montant_total'] - $quittance['montant_paye'];
    if ($reste > 0):
    ?>
    <div class="text-danger fw-bold mt-2">
      Reste à payer : <?= number_format($reste, 0, ',', ' ') ?> FCFA
    </div>
    <?php else: ?>
    <div class="text-success fw-bold mt-2">✓ Quittance soldée</div>
    <?php endif; ?>
  </div>

  <!-- Déclaration -->
  <p class="text-muted small">
    Je soussigné(e), <strong><?= htmlspecialchars($quittance['proprio_prenom'] . ' ' . $quittance['proprio_nom']) ?></strong>,
    propriétaire du logement désigné ci-dessus, reconnais avoir reçu de
    <strong><?= htmlspecialchars($quittance['locataire_nom']) ?></strong>
    la somme de <strong><?= number_format($quittance['montant_paye'], 0, ',', ' ') ?> FCFA</strong>
    à titre de loyer et charges pour la période du <?= formatDate($quittance['periode_debut']) ?> au <?= formatDate($quittance['periode_fin']) ?>,
    et lui en donne bonne et valable quittance, sous réserve de tous mes droits.
  </p>

  <!-- Signature -->
  <div class="signature-zone row">
    <div class="col-md-6">
      <div class="label">Signature du bailleur</div>
      <div style="height: 80px; border-bottom: 1px dashed #ccc; margin-top: 10px;"></div>
      <div class="text-muted small mt-1"><?= htmlspecialchars($quittance['proprio_prenom'] . ' ' . $quittance['proprio_nom']) ?></div>
    </div>
    <div class="col-md-6 text-end">
      <div class="label">Date et lieu</div>
      <div class="mt-3"><?= htmlspecialchars($quittance['ville']) ?>, le <?= formatDate(date('Y-m-d')) ?></div>
    </div>
  </div>

</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
