<?php
$page_title = 'Loyers & Paiements';
require_once '../config/app.php';
requireLogin();
require_once '../includes/header.php';
?>

<div class="page-content">

  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
    <div>
      <h1 style="font-family:'Playfair Display',serif; font-size:26px;">
        <i class="fa-solid fa-hand-holding-dollar" style="color:var(--accent-gold); margin-right:10px;"></i>
        Loyers & Paiements
      </h1>
      <p style="color:var(--text-muted); font-size:13px; margin-top:4px;">Juillet 2025 Â· 15/18 rÃ©glÃ©s</p>
    </div>
    <div style="display:flex; gap:10px;">
      <button class="btn btn-outline"><i class="fa-solid fa-file-pdf"></i> Quittances</button>
      <a href="?action=encaisser" class="btn btn-primary">
        <i class="fa-solid fa-money-bill-wave"></i> Encaisser un loyer
      </a>
    </div>
  </div>

  <!-- Stats loyers -->
  <div class="stats-grid" style="margin-bottom:24px;">
    <?php
    $ls = [
      ['fa-circle-dollar-to-slot','gold',  'Total attendu',  '2 130 000 FCFA', '', ''],
      ['fa-circle-check',         'green', 'EncaissÃ©',       '1 755 000 FCFA', '+82%','up'],
      ['fa-hourglass-half',       'orange','En attente',     '255 000 FCFA',   '',''],
      ['fa-circle-xmark',         'red',   'ImpayÃ©s',        '120 000 FCFA',   '3','down'],
    ];
    foreach($ls as $s): ?>
    <div class="stat-card <?= $s[1] ?>">
      <div class="stat-header">
        <div class="stat-icon <?= $s[1] ?>">
          <i class="fa-solid <?= $s[0] ?>" style="color:#fff; position:relative; z-index:1;"></i>
        </div>
        <?php if($s[4]): ?>
        <div class="stat-trend <?= $s[5] ?>">
          <i class="fa-solid <?= $s[5]=='up'?'fa-arrow-trend-up':'fa-arrow-trend-down' ?>"></i>
          <?= $s[4] ?>
        </div>
        <?php endif; ?>
      </div>
      <div class="stat-value" style="font-size:22px;"><?= $s[3] ?></div>
      <div class="stat-label"><?= $s[2] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Tableau loyers -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <span class="title-icon icon-orange" style="border-radius:7px; width:30px; height:30px; display:flex; align-items:center; justify-content:center;">
          <i class="fa-solid fa-receipt" style="color:#fff; font-size:12px;"></i>
        </span>
        Loyers Â· Juillet 2025
      </div>
      <div style="display:flex; gap:8px;">
        <select class="btn btn-outline btn-sm">
          <option>Juillet 2025</option><option>Juin 2025</option>
        </select>
      </div>
    </div>
    <div class="card-body" style="padding:0;">
      <table>
        <thead>
          <tr>
            <th>Locataire</th>
            <th>Bien</th>
            <th>Montant dÃ»</th>
            <th>PayÃ©</th>
            <th>Reste</th>
            <th>Date paiement</th>
            <th>Mode</th>
            <th>Statut</th>
            <th>Quittance</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $loyers = [
            ['Moussa Diop',    'Appt T3',  '180 000','180 000','0',      '01/07/2025','Wave',        'PayÃ©'],
            ['Fatou Ndiaye',   'Studio B', '95 000', '95 000', '0',      '02/07/2025','Orange Money','PayÃ©'],
            ['Aminata Sow',    'Villa F4', '350 000','175 000','175 000', '05/07/2025','Virement',    'Partiel'],
            ['Omar Ba',        'Appt T2',  '120 000','0',      '120 000', 'â€”',         'â€”',           'ImpayÃ©'],
            ['NdÃ¨ye Fall',     'Studio HLM','80 000','80 000', '0',      '01/07/2025','EspÃ¨ces',     'PayÃ©'],
            ['Ibrahima Sall',  'Appt T4',  '250 000','250 000','0',      '03/07/2025','Wave',        'PayÃ©'],
            ['Mariama Diallo', 'Studio MÃ©dina','75 000','75 000','0',    '04/07/2025','Orange Money','PayÃ©'],
          ];
          $colors = ['#3fb950','#1f6feb','#8b5cf6','#e3b341','#ec4899','#06b6d4','#f85149'];
          $mode_icons = ['Wave'=>'fa-mobile-screen-button','Orange Money'=>'fa-mobile-screen','Virement'=>'fa-building-columns','EspÃ¨ces'=>'fa-money-bills','â€”'=>'fa-minus'];
          foreach($loyers as $i => $l):
            $sc = match($l[7]) { 'PayÃ©'=>'badge-success','ImpayÃ©'=>'badge-danger','Partiel'=>'badge-warning', default=>'badge-info' };
            $si = match($l[7]) { 'PayÃ©'=>'fa-circle-check','ImpayÃ©'=>'fa-circle-xmark','Partiel'=>'fa-hourglass-half', default=>'fa-circle' };
          ?>
          <tr>
            <td>
              <div class="tenant-cell">
                <div class="tenant-avatar" style="background:<?= $colors[$i] ?>22; color:<?= $colors[$i] ?>; border:1px solid <?= $colors[$i] ?>44; width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                  <i class="fa-solid fa-user" style="font-size:13px;"></i>
                </div>
                <span style="font-weight:600;"><?= $l[0] ?></span>
              </div>
            </td>
            <td style="color:var(--text-muted); font-size:12.5px;">
              <i class="fa-solid fa-door-open" style="font-size:11px; margin-right:4px;"></i><?= $l[1] ?>
            </td>
            <td style="font-weight:600;"><?= number_format((int)str_replace(' ','',$l[2]),0,',',' ') ?> <small style="color:var(--text-muted); font-size:10px;">FCFA</small></td>
            <td style="color:var(--accent-green); font-weight:600;"><?= $l[3] != '0' ? number_format((int)str_replace(' ','',$l[3]),0,',',' ').' FCFA' : 'â€”' ?></td>
            <td style="color:<?= $l[4]!='0'?'var(--accent-red)':'var(--text-muted)' ?>; font-weight:600;"><?= $l[4] != '0' ? number_format((int)str_replace(' ','',$l[4]),0,',',' ').' FCFA' : 'â€”' ?></td>
            <td style="color:var(--text-muted); font-size:12.5px;">
              <i class="fa-solid fa-calendar" style="font-size:11px; margin-right:4px;"></i><?= $l[5] ?>
            </td>
            <td>
              <div style="display:flex; align-items:center; gap:5px; font-size:12.5px; color:var(--text-muted);">
                <i class="fa-solid <?= $mode_icons[$l[6]] ?? 'fa-circle' ?>" style="font-size:13px; color:var(--accent-blue);"></i>
                <?= $l[6] ?>
              </div>
            </td>
            <td><span class="badge <?= $sc ?>"><i class="fa-solid <?= $si ?>"></i><?= $l[7] ?></span></td>
            <td>
              <?php if($l[7]=='PayÃ©'): ?>
              <button class="btn btn-outline btn-sm">
                <i class="fa-solid fa-file-pdf" style="color:var(--accent-red);"></i> PDF
              </button>
              <?php else: ?>
              <button class="btn btn-primary btn-sm">
                <i class="fa-solid fa-cash-register"></i> Encaisser
              </button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require_once '../includes/footer.php'; ?>

