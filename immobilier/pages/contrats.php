<?php
$page_title = 'Contrats';
require_once '../includes/header.php';
?>

<div class="page-content">

  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
    <div>
      <h1 style="font-family:'Playfair Display',serif; font-size:26px;">
        <i class="fa-solid fa-file-contract" style="color:var(--accent-gold); margin-right:10px;"></i>
        Gestion des contrats
      </h1>
      <p style="color:var(--text-muted); font-size:13px; margin-top:4px;">18 actifs · 2 en cours de renouvellement · 1 expiré</p>
    </div>
    <a href="?action=new" class="btn btn-primary">
      <i class="fa-solid fa-pen-to-square"></i> Nouveau contrat
    </a>
  </div>

  <!-- Alertes contrats -->
  <div class="alert alert-warning">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <strong>3 contrats</strong> expirent dans les 30 prochains jours. Pensez à les renouveler.
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <span class="title-icon icon-purple" style="border-radius:7px; width:30px; height:30px; display:flex; align-items:center; justify-content:center;">
          <i class="fa-solid fa-file-contract" style="color:#fff; font-size:12px;"></i>
        </span>
        Liste des contrats
      </div>
    </div>
    <div class="card-body" style="padding:0;">
      <table>
        <thead>
          <tr>
            <th>N° Contrat</th>
            <th>Locataire</th>
            <th>Bien</th>
            <th>Type</th>
            <th>Début</th>
            <th>Fin</th>
            <th>Loyer</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $contrats = [
            ['CTR-001','Moussa Diop',    'Appt T3 – Bloc A',    'Location nue',     '01/01/2024','31/12/2025','180 000','Actif'],
            ['CTR-002','Fatou Ndiaye',   'Studio – Bloc B',     'Location meublée', '01/03/2024','28/02/2026','95 000', 'Actif'],
            ['CTR-003','Aminata Sow',    'Villa F4 – Sacré Cœur','Location nue',   '01/07/2023','30/06/2025','350 000','Expiration proche'],
            ['CTR-004','Omar Ba',        'Appt T2 – Centre',    'Location nue',     '01/02/2024','31/01/2026','120 000','Actif'],
            ['CTR-005','Ndèye Fall',     'Studio – HLM',        'Location meublée', '01/05/2024','30/04/2026','80 000', 'Actif'],
            ['CTR-006','Ibrahima Sall',  'Appt T4 – Plateau',   'Location nue',     '01/10/2023','30/09/2025','250 000','Expiration proche'],
          ];
          $colors = ['#3fb950','#1f6feb','#8b5cf6','#e3b341','#ec4899','#06b6d4'];
          foreach($contrats as $i => $c):
            $sc = match($c[7]) {
              'Actif'=>'badge-success','Expiration proche'=>'badge-warning',
              'Expiré'=>'badge-danger','Résilié'=>'badge-danger', default=>'badge-info'
            };
            $si = match($c[7]) {
              'Actif'=>'fa-circle-check','Expiration proche'=>'fa-clock',
              'Expiré'=>'fa-circle-xmark', default=>'fa-circle'
            };
          ?>
          <tr>
            <td>
              <span style="font-family:monospace; background:rgba(255,255,255,.05); padding:3px 8px; border-radius:5px; font-size:12px; color:var(--accent-gold);">
                <?= $c[0] ?>
              </span>
            </td>
            <td>
              <div class="tenant-cell">
                <div class="tenant-avatar" style="background:<?= $colors[$i] ?>22; color:<?= $colors[$i] ?>; border:1px solid <?= $colors[$i] ?>44; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                  <i class="fa-solid fa-user" style="font-size:13px;"></i>
                </div>
                <span style="font-weight:600;"><?= $c[1] ?></span>
              </div>
            </td>
            <td style="font-size:12.5px; color:var(--text-muted);">
              <i class="fa-solid fa-building" style="font-size:11px; color:var(--accent-blue); margin-right:4px;"></i><?= $c[2] ?>
            </td>
            <td>
              <span style="font-size:12px; display:flex; align-items:center; gap:5px;">
                <i class="fa-solid fa-<?= strpos($c[3],'meublée')!==false ? 'couch' : 'key' ?>" style="color:var(--accent-purple, #8b5cf6);"></i>
                <?= $c[3] ?>
              </span>
            </td>
            <td style="font-size:12.5px;">
              <i class="fa-solid fa-play" style="font-size:9px; color:var(--accent-green); margin-right:4px;"></i><?= $c[4] ?>
            </td>
            <td style="font-size:12.5px;">
              <i class="fa-solid fa-stop" style="font-size:9px; color:var(--accent-red); margin-right:4px;"></i><?= $c[5] ?>
            </td>
            <td style="font-weight:700; color:var(--accent-gold);">
              <?= number_format((int)str_replace(' ','',$c[6]),0,',',' ') ?> <small style="font-size:10px; color:var(--text-muted); font-weight:400;">FCFA</small>
            </td>
            <td><span class="badge <?= $sc ?>"><i class="fa-solid <?= $si ?>"></i><?= $c[7] ?></span></td>
            <td>
              <div style="display:flex; gap:5px;">
                <button class="btn-icon" title="Voir" style="width:30px; height:30px; border-radius:7px;">
                  <i class="fa-solid fa-eye" style="font-size:12px;"></i>
                </button>
                <button class="btn-icon" title="PDF" style="width:30px; height:30px; border-radius:7px;">
                  <i class="fa-solid fa-file-pdf" style="font-size:12px; color:var(--accent-red);"></i>
                </button>
                <button class="btn-icon" title="Renouveler" style="width:30px; height:30px; border-radius:7px;">
                  <i class="fa-solid fa-rotate" style="font-size:12px; color:var(--accent-blue);"></i>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require_once '../includes/footer.php'; ?>
