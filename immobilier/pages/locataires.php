<?php
$page_title = 'Locataires';
require_once '../config/app.php';
requireLogin();
require_once '../includes/header.php';
?>

<div class="page-content">

  <!-- En-tÃªte -->
  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
    <div>
      <h1 style="font-family:'Playfair Display',serif; font-size:26px; color:var(--text-primary);">
        <i class="fa-solid fa-users" style="color:var(--accent-gold); margin-right:10px;"></i>
        Gestion des locataires
      </h1>
      <p style="color:var(--text-muted); font-size:13px; margin-top:4px;">18 locataires actifs Â· 2 en attente</p>
    </div>
    <a href="?action=new" class="btn btn-primary">
      <i class="fa-solid fa-user-plus"></i> Nouveau locataire
    </a>
  </div>

  <!-- Filtres -->
  <div class="card" style="margin-bottom:18px;">
    <div class="card-body" style="display:flex; gap:12px; flex-wrap:wrap; padding:16px 22px;">
      <div class="search-box" style="width:260px;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Nom, tÃ©lÃ©phone...">
      </div>
      <select class="form-control form-select" style="width:160px; padding:8px 14px;">
        <option>Tous les statuts</option>
        <option>Actif</option>
        <option>Inactif</option>
        <option>En attente</option>
      </select>
      <select class="form-control form-select" style="width:160px; padding:8px 14px;">
        <option>Tous les biens</option>
        <option>RÃ©sidence Baobabs</option>
        <option>Villa Corniche</option>
      </select>
      <button class="btn btn-outline">
        <i class="fa-solid fa-sliders"></i> Filtrer
      </button>
    </div>
  </div>

  <!-- Tableau locataires -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <span class="title-icon icon-green" style="border-radius:7px; width:30px; height:30px; display:flex; align-items:center; justify-content:center;">
          <i class="fa-solid fa-user-group" style="color:#fff; font-size:12px;"></i>
        </span>
        Liste des locataires
      </div>
      <div style="display:flex; gap:8px;">
        <button class="btn btn-outline btn-sm"><i class="fa-solid fa-file-excel"></i> Exporter</button>
        <button class="btn btn-outline btn-sm"><i class="fa-solid fa-print"></i> Imprimer</button>
      </div>
    </div>
    <div class="card-body" style="padding:0;">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Locataire</th>
              <th>Contact</th>
              <th>Bien louÃ©</th>
              <th>Loyer</th>
              <th>Contrat</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $locataires = [
              [1,'Moussa Diop',    '77 123 45 67','moussa@email.com',    'Appt T3 â€“ Bloc A',    '180 000','Jan 2024','Jan 2026','Actif'],
              [2,'Fatou Ndiaye',   '76 234 56 78','fatou@email.com',     'Studio â€“ Bloc B',     '95 000', 'Mar 2024','Mar 2026','Actif'],
              [3,'Aminata Sow',    '70 345 67 89','aminata@email.com',   'Villa F4 â€“ SacrÃ© CÅ“ur','350 000','Juil 2023','Juil 2025','Actif'],
              [4,'Omar Ba',        '78 456 78 90','omar@email.com',      'Appt T2 â€“ Centre',    '120 000','FÃ©v 2024','FÃ©v 2026','ImpayÃ©'],
              [5,'NdÃ¨ye Fall',     '77 567 89 01','ndeye@email.com',     'Studio â€“ HLM',        '80 000', 'Mai 2024','Mai 2026','Actif'],
              [6,'Ibrahima Sall',  '76 678 90 12','ibra@email.com',      'Appt T4 â€“ Plateau',   '250 000','Oct 2023','Oct 2025','Actif'],
              [7,'Mariama Diallo', '70 789 01 23','mariama@email.com',   'Studio â€“ MÃ©dina',     '75 000', 'Juin 2024','Juin 2026','En attente'],
            ];
            $colors = ['#3fb950','#1f6feb','#8b5cf6','#e3b341','#ec4899','#06b6d4','#f85149'];
            foreach($locataires as $i => $l):
              $statut_class = match($l[8]) {
                'Actif' => 'badge-success', 'ImpayÃ©' => 'badge-danger',
                'En attente' => 'badge-warning', default => 'badge-info'
              };
              $statut_icon = match($l[8]) {
                'Actif' => 'fa-circle-check', 'ImpayÃ©' => 'fa-circle-xmark',
                'En attente' => 'fa-clock', default => 'fa-circle'
              };
            ?>
            <tr>
              <td style="color:var(--text-muted); font-size:12px;">#<?= str_pad($l[0],3,'0',STR_PAD_LEFT) ?></td>
              <td>
                <div class="tenant-cell">
                  <div class="tenant-avatar" style="background:<?= $colors[$i] ?>22; color:<?= $colors[$i] ?>; border:1px solid <?= $colors[$i] ?>44; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-user" style="font-size:14px;"></i>
                  </div>
                  <div>
                    <div style="font-weight:600;"><?= $l[1] ?></div>
                  </div>
                </div>
              </td>
              <td>
                <div style="font-size:12.5px;"><?= $l[2] ?></div>
                <div style="font-size:11px; color:var(--text-muted);"><?= $l[3] ?></div>
              </td>
              <td>
                <div style="display:flex; align-items:center; gap:6px;">
                  <i class="fa-solid fa-building" style="color:var(--accent-blue); font-size:12px;"></i>
                  <span style="font-size:13px;"><?= $l[4] ?></span>
                </div>
              </td>
              <td style="font-weight:700; color:var(--accent-gold);">
                <?= number_format($l[5],0,',',' ') ?> <small style="font-size:10px; color:var(--text-muted); font-weight:400;">FCFA</small>
              </td>
              <td>
                <div style="font-size:12px; color:var(--text-muted);">
                  <i class="fa-solid fa-calendar-day" style="font-size:10px; margin-right:3px;"></i><?= $l[6] ?>
                </div>
                <div style="font-size:12px; color:var(--text-muted);">
                  <i class="fa-solid fa-calendar-check" style="font-size:10px; margin-right:3px;"></i><?= $l[7] ?>
                </div>
              </td>
              <td>
                <span class="badge <?= $statut_class ?>">
                  <i class="fa-solid <?= $statut_icon ?>"></i>
                  <?= $l[8] ?>
                </span>
              </td>
              <td>
                <div style="display:flex; gap:6px;">
                  <button class="btn-icon" title="Voir" style="width:30px; height:30px; border-radius:7px;">
                    <i class="fa-solid fa-eye" style="font-size:12px;"></i>
                  </button>
                  <button class="btn-icon" title="Modifier" style="width:30px; height:30px; border-radius:7px;">
                    <i class="fa-solid fa-pen" style="font-size:12px;"></i>
                  </button>
                  <button class="btn-icon" title="Supprimer" style="width:30px; height:30px; border-radius:7px; border-color:var(--accent-red)20;">
                    <i class="fa-solid fa-trash-can" style="font-size:12px; color:var(--accent-red);"></i>
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

</div>

<?php require_once '../includes/footer.php'; ?>

