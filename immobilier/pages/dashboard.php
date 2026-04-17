<?php
$page_title = 'Tableau de bord';
require_once '../includes/header.php';

// === Données fictives (à remplacer par vrai DB) ===
$stats = [
  ['label'=>'Biens Totaux',       'value'=>'24',         'icon'=>'fa-city',               'class'=>'gold',  'trend'=>'+2',  'dir'=>'up'],
  ['label'=>'Locataires Actifs',  'value'=>'18',         'icon'=>'fa-users',              'class'=>'blue',  'trend'=>'+3',  'dir'=>'up'],
  ['label'=>'Recettes du mois',   'value'=>'4 250 000',  'icon'=>'fa-sack-dollar',        'class'=>'green', 'trend'=>'+8%', 'dir'=>'up'],
  ['label'=>'Loyers impayés',     'value'=>'3',          'icon'=>'fa-triangle-exclamation','class'=>'red',   'trend'=>'-1',  'dir'=>'down'],
];
?>

<!-- PAGE CONTENT -->
<div class="page-content">

  <!-- ALERTES -->
  <div class="alert alert-warning">
    <i class="fa-solid fa-bell"></i>
    <strong>3 loyers</strong> arrivent à échéance dans les 7 prochains jours. &nbsp;
    <a href="loyers.php" style="color:inherit; font-weight:700;">Voir les loyers →</a>
  </div>

  <!-- STAT CARDS -->
  <div class="stats-grid">
    <?php foreach($stats as $s): ?>
    <div class="stat-card <?= $s['class'] ?>">
      <div class="stat-header">
        <div class="stat-icon <?= $s['class'] ?>">
          <i class="fa-solid <?= $s['icon'] ?>" style="color:#fff; position:relative; z-index:1;"></i>
        </div>
        <div class="stat-trend <?= $s['dir'] ?>">
          <i class="fa-solid <?= $s['dir']=='up'?'fa-arrow-trend-up':'fa-arrow-trend-down' ?>"></i>
          <?= $s['trend'] ?>
        </div>
      </div>
      <div class="stat-value"><?= $s['value'] ?></div>
      <div class="stat-label"><?= $s['label'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ROW 1 : Graphique + Taux occupation -->
  <div class="grid-2">

    <!-- Graphique recettes -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <span class="title-icon icon-teal" style="border-radius:7px; width:30px; height:30px; display:flex; align-items:center; justify-content:center;">
            <i class="fa-solid fa-chart-line" style="color:#fff; font-size:13px;"></i>
          </span>
          Recettes mensuelles (FCFA)
        </div>
        <select class="btn btn-outline btn-sm" style="cursor:pointer;">
          <option>2024</option><option selected>2025</option>
        </select>
      </div>
      <div class="card-body">
        <canvas id="chartRecettes" height="110"></canvas>
      </div>
    </div>

    <!-- Taux occupation -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <span class="title-icon icon-gold" style="border-radius:7px; width:30px; height:30px; display:flex; align-items:center; justify-content:center;">
            <i class="fa-solid fa-house-circle-check" style="color:#fff; font-size:13px;"></i>
          </span>
          Taux d'occupation
        </div>
      </div>
      <div class="card-body">
        <div class="occupancy-wrap">
          <canvas id="chartOccupation" width="180" height="180"></canvas>
          <div class="ring-text">
            <div class="ring-pct">75%</div>
            <div class="ring-lbl">18 / 24 biens occupés</div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- ROW 2 : Locataires récents + Biens + Actions rapides -->
  <div class="grid-2">

    <!-- Locataires récents -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <span class="title-icon icon-green" style="border-radius:7px; width:30px; height:30px; display:flex; align-items:center; justify-content:center;">
            <i class="fa-solid fa-user-group" style="color:#fff; font-size:12px;"></i>
          </span>
          Locataires récents
        </div>
        <a href="locataires.php" class="btn btn-outline btn-sm">
          <i class="fa-solid fa-arrow-right"></i> Voir tout
        </a>
      </div>
      <div class="card-body" style="padding:0;">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Locataire</th>
                <th>Bien</th>
                <th>Loyer</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $tenants = [
                ['Moussa Diop',    'M',  '#3fb950', 'Appt T3 – Bloc A', '180 000', 'Payé'],
                ['Fatou Ndiaye',   'F',  '#1f6feb', 'Studio – Bloc B',  '95 000',  'Payé'],
                ['Aminata Sow',    'A',  '#8b5cf6', 'Villa F4 – Sacré', '350 000', 'Partiel'],
                ['Omar Ba',        'O',  '#e3b341', 'Appt T2 – Centre', '120 000', 'Impayé'],
                ['Ndèye Fall',     'N',  '#ec4899', 'Studio – HLM',     '80 000',  'Payé'],
              ];
              foreach($tenants as $t): ?>
              <tr>
                <td>
                  <div class="tenant-cell">
                    <div class="tenant-avatar" style="background:<?= $t[2] ?>20; color:<?= $t[2] ?>; border:1px solid <?= $t[2] ?>40;">
                      <i class="fa-solid fa-user" style="font-size:13px;"></i>
                    </div>
                    <?= $t[0] ?>
                  </div>
                </td>
                <td style="color:var(--text-muted); font-size:12px;"><?= $t[3] ?></td>
                <td style="font-weight:600; color:var(--accent-gold);"><?= $t[4] ?> <small style="font-size:10px; color:var(--text-muted);">FCFA</small></td>
                <td>
                  <?php
                  $bc = ['Payé'=>'badge-success','Impayé'=>'badge-danger','Partiel'=>'badge-warning'];
                  $ic = ['Payé'=>'fa-circle-check','Impayé'=>'fa-circle-xmark','Partiel'=>'fa-clock'];
                  ?>
                  <span class="badge <?= $bc[$t[5]] ?>">
                    <i class="fa-solid <?= $ic[$t[5]] ?>"></i>
                    <?= $t[5] ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Colonne droite -->
    <div style="display:flex; flex-direction:column; gap:18px;">

      <!-- Biens phares -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">
            <span class="title-icon icon-blue" style="border-radius:7px; width:30px; height:30px; display:flex; align-items:center; justify-content:center;">
              <i class="fa-solid fa-building-user" style="color:#fff; font-size:12px;"></i>
            </span>
            Biens phares
          </div>
        </div>
        <div class="card-body" style="padding: 12px 22px;">
          <?php
          $biens = [
            ['fa-building',          '#1f6feb', 'Résidence Les Baobabs', 'Dakar · 8 unités',   '1 440 000'],
            ['fa-house',             '#3fb950', 'Villa Corniche',        'Mermoz · 1 unité',   '350 000'],
            ['fa-hotel',             '#8b5cf6', 'Immeuble Touba',        'Médina · 5 unités',  '600 000'],
            ['fa-house-flag',        '#e3b341', 'Duplex Almadies',       'Almadies · 2 unités','420 000'],
          ];
          foreach($biens as $b): ?>
          <div class="property-item">
            <div class="property-img" style="background: <?= $b[1] ?>22; border:1px solid <?= $b[1] ?>33;">
              <i class="fa-solid <?= $b[0] ?>" style="color:<?= $b[1] ?>; position:relative; z-index:1;"></i>
            </div>
            <div class="property-info">
              <div class="property-name"><?= $b[2] ?></div>
              <div class="property-meta"><i class="fa-solid fa-location-dot" style="font-size:10px;"></i> <?= $b[3] ?></div>
            </div>
            <div class="property-rent"><?= $b[4] ?><br><small style="font-size:10px; color:var(--text-muted); font-weight:400;">FCFA/mois</small></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Actions rapides -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">
            <span class="title-icon icon-orange" style="border-radius:7px; width:30px; height:30px; display:flex; align-items:center; justify-content:center;">
              <i class="fa-solid fa-bolt" style="color:#fff; font-size:12px;"></i>
            </span>
            Actions rapides
          </div>
        </div>
        <div class="card-body">
          <div class="quick-actions">
            <a href="locataires.php?action=new" class="quick-btn">
              <span class="qb-icon icon-green"><i class="fa-solid fa-user-plus" style="color:#fff; font-size:13px;"></i></span>
              Nouveau locataire
            </a>
            <a href="biens.php?action=new" class="quick-btn">
              <span class="qb-icon icon-blue"><i class="fa-solid fa-building-circle-arrow-right" style="color:#fff; font-size:13px;"></i></span>
              Ajouter un bien
            </a>
            <a href="loyers.php?action=encaisser" class="quick-btn">
              <span class="qb-icon icon-gold"><i class="fa-solid fa-money-bill-wave" style="color:#fff; font-size:13px;"></i></span>
              Encaisser loyer
            </a>
            <a href="contrats.php?action=new" class="quick-btn">
              <span class="qb-icon icon-purple"><i class="fa-solid fa-pen-to-square" style="color:#fff; font-size:13px;"></i></span>
              Créer contrat
            </a>
            <a href="maintenances.php?action=new" class="quick-btn">
              <span class="qb-icon icon-red"><i class="fa-solid fa-toolbox" style="color:#fff; font-size:13px;"></i></span>
              Ticket maintenance
            </a>
            <a href="rapports.php" class="quick-btn">
              <span class="qb-icon icon-teal"><i class="fa-solid fa-file-chart-column" style="color:#fff; font-size:13px;"></i></span>
              Rapport mensuel
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- ROW 3 : Timeline paiements + Alertes -->
  <div class="grid-2">

    <!-- Timeline derniers paiements -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <span class="title-icon icon-gold" style="border-radius:7px; width:30px; height:30px; display:flex; align-items:center; justify-content:center;">
            <i class="fa-solid fa-clock-rotate-left" style="color:#fff; font-size:12px;"></i>
          </span>
          Derniers paiements
        </div>
      </div>
      <div class="card-body">
        <div class="timeline">
          <?php
          $pays = [
            ['fa-circle-check','icon-green', '#3fb950', 'Moussa Diop',    '180 000 FCFA', 'Appt T3 · Hier 14:32',      'up'],
            ['fa-circle-check','icon-green', '#3fb950', 'Fatou Ndiaye',   '95 000 FCFA',  'Studio · Hier 09:15',       'up'],
            ['fa-circle-xmark','icon-red',   '#f85149', 'Omar Ba',        '120 000 FCFA', 'Appt T2 · Impayé 5j',       'down'],
            ['fa-money-bill',  'icon-gold',  '#d4a017', 'Aminata Sow',    '175 000 FCFA', 'Villa F4 · Partiel · 2j',   'up'],
            ['fa-circle-check','icon-green', '#3fb950', 'Ndèye Fall',     '80 000 FCFA',  'Studio HLM · 03/07/2025',   'up'],
          ];
          foreach($pays as $p): ?>
          <div class="timeline-item">
            <div class="tl-icon <?= $p[1] ?>">
              <i class="fa-solid <?= $p[0] ?>" style="color:#fff; font-size:14px;"></i>
            </div>
            <div class="tl-body">
              <div class="tl-title"><?= $p[3] ?></div>
              <div class="tl-meta"><?= $p[5] ?></div>
            </div>
            <div class="tl-amount" style="color:<?= $p[2] ?>;"><?= $p[4] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Alertes & Rappels -->
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <span class="title-icon icon-red" style="border-radius:7px; width:30px; height:30px; display:flex; align-items:center; justify-content:center;">
            <i class="fa-solid fa-bell-ring" style="color:#fff; font-size:12px;"></i>
          </span>
          Alertes & Rappels
        </div>
      </div>
      <div class="card-body" style="display:flex; flex-direction:column; gap:12px;">

        <?php
        $alertes = [
          ['fa-file-signature',   'icon-orange', 'Contrat expirant',     'Moussa Diop · Expire dans 12 jours',        'warning'],
          ['fa-triangle-exclamation','icon-red', 'Loyer impayé',         'Omar Ba · Retard de 5 jours',               'danger'],
          ['fa-screwdriver-wrench','icon-pink',  'Maintenance urgente',  'Fuite d\'eau · Résidence Les Baobabs',      'danger'],
          ['fa-calendar-check',   'icon-green',  'Visite programmée',    'Nouvel appartement · 15 Juillet 09h00',     'success'],
          ['fa-file-invoice-dollar','icon-blue', 'Déclaration fiscale',  'Revenus fonciers 2025 · Avant 31 Juillet',  'info'],
        ];
        foreach($alertes as $a): ?>
        <div style="display:flex; align-items:center; gap:12px; padding:12px; background:rgba(255,255,255,.03); border-radius:9px; border:1px solid var(--border);">
          <div class="tl-icon <?= $a[1] ?>" style="width:36px; height:36px; border-radius:9px; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
            <i class="fa-solid <?= $a[0] ?>" style="color:#fff; font-size:14px;"></i>
          </div>
          <div style="flex:1;">
            <div style="font-size:13px; font-weight:600; color:var(--text-primary);"><?= $a[2] ?></div>
            <div style="font-size:12px; color:var(--text-muted); margin-top:2px;"><?= $a[3] ?></div>
          </div>
          <i class="fa-solid fa-chevron-right" style="color:var(--text-dim); font-size:11px;"></i>
        </div>
        <?php endforeach; ?>

      </div>
    </div>

  </div>

</div><!-- /.page-content -->

<?php require_once '../includes/footer.php'; ?>
