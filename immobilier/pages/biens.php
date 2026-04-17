<?php
$page_title = 'Biens immobiliers';
require_once '../config/app.php';
requireLogin();
require_once '../includes/header.php';
?>

<div class="page-content">

  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
    <div>
      <h1 style="font-family:'Playfair Display',serif; font-size:26px;">
        <i class="fa-solid fa-city" style="color:var(--accent-gold); margin-right:10px;"></i>
        Biens immobiliers
      </h1>
      <p style="color:var(--text-muted); font-size:13px; margin-top:4px;">24 biens Â· 18 occupÃ©s Â· 6 vacants</p>
    </div>
    <a href="?action=new" class="btn btn-primary">
      <i class="fa-solid fa-building-circle-arrow-right"></i> Ajouter un bien
    </a>
  </div>

  <!-- Cards biens -->
  <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:18px;">
    <?php
    $biens = [
      ['fa-building',       '#1f6feb','RÃ©sidence Les Baobabs','Dakar, SacrÃ© CÅ“ur','Immeuble','8 appts','1 440 000','6/8 occupÃ©s','Actif'],
      ['fa-house',          '#3fb950','Villa Corniche',        'Dakar, Mermoz',    'Villa',   '4 piÃ¨ces','350 000',  '1/1 occupÃ©', 'Actif'],
      ['fa-hotel',          '#8b5cf6','Immeuble Touba',        'Dakar, MÃ©dina',    'Immeuble','5 studios','500 000', '4/5 occupÃ©s','Actif'],
      ['fa-house-flag',     '#e3b341','Duplex Almadies',       'Dakar, Almadies',  'Duplex',  '6 piÃ¨ces','420 000',  '1/1 occupÃ©', 'Actif'],
      ['fa-warehouse',      '#06b6d4','EntrepÃ´t Zone Indus.',  'ThiÃ¨s, ZI',        'Commercial','500mÂ²', '280 000',  '1/1 occupÃ©', 'Actif'],
      ['fa-building-columns','#ec4899','Bureaux Centre Aff.',   'Dakar, Plateau',   'Bureau',  '3 bureaux','600 000', '2/3 occupÃ©s','Actif'],
    ];
    foreach($biens as $b): ?>
    <div class="card" style="transition:transform .2s, box-shadow .2s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 36px rgba(0,0,0,.5)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
      <!-- Header image simulÃ© -->
      <div style="height:130px; background:linear-gradient(135deg, <?= $b[1] ?>22, <?= $b[1] ?>08); border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden;">
        <!-- Pattern dÃ©coratif -->
        <div style="position:absolute; width:200px; height:200px; background:<?= $b[1] ?>10; border-radius:50%; right:-50px; top:-50px;"></div>
        <div style="position:absolute; width:120px; height:120px; background:<?= $b[1] ?>08; border-radius:50%; left:-30px; bottom:-30px;"></div>
        <i class="fa-solid <?= $b[0] ?>" style="font-size:52px; color:<?= $b[1] ?>; position:relative; z-index:1; filter:drop-shadow(0 4px 12px <?= $b[1] ?>66);"></i>
        <!-- Badge statut -->
        <div style="position:absolute; top:12px; right:12px;">
          <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i><?= $b[8] ?></span>
        </div>
      </div>

      <div class="card-body">
        <div style="margin-bottom:14px;">
          <h3 style="font-size:16px; font-weight:700; color:var(--text-primary); margin-bottom:4px;"><?= $b[2] ?></h3>
          <p style="font-size:12.5px; color:var(--text-muted); display:flex; align-items:center; gap:5px;">
            <i class="fa-solid fa-location-dot" style="color:var(--accent-gold); font-size:11px;"></i><?= $b[3] ?>
          </p>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px;">
          <div style="background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:8px; padding:10px; text-align:center;">
            <div style="font-size:11px; color:var(--text-muted); margin-bottom:3px;">Type</div>
            <div style="font-size:13px; font-weight:600; display:flex; align-items:center; justify-content:center; gap:5px;">
              <i class="fa-solid fa-tag" style="font-size:11px; color:var(--accent-blue);"></i><?= $b[4] ?>
            </div>
          </div>
          <div style="background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:8px; padding:10px; text-align:center;">
            <div style="font-size:11px; color:var(--text-muted); margin-bottom:3px;">Surface/UnitÃ©s</div>
            <div style="font-size:13px; font-weight:600; display:flex; align-items:center; justify-content:center; gap:5px;">
              <i class="fa-solid fa-door-open" style="font-size:11px; color:var(--accent-green);"></i><?= $b[5] ?>
            </div>
          </div>
          <div style="background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:8px; padding:10px; text-align:center;">
            <div style="font-size:11px; color:var(--text-muted); margin-bottom:3px;">Loyer/mois</div>
            <div style="font-size:13px; font-weight:700; color:var(--accent-gold);"><?= number_format((int)str_replace(' ','',$b[6]),0,',',' ') ?></div>
          </div>
          <div style="background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:8px; padding:10px; text-align:center;">
            <div style="font-size:11px; color:var(--text-muted); margin-bottom:3px;">Occupation</div>
            <div style="font-size:13px; font-weight:600; color:var(--accent-green); display:flex; align-items:center; justify-content:center; gap:5px;">
              <i class="fa-solid fa-users" style="font-size:11px;"></i><?= $b[7] ?>
            </div>
          </div>
        </div>

        <div style="display:flex; gap:8px;">
          <a href="biens.php" class="btn btn-outline btn-sm" style="flex:1; justify-content:center;">
            <i class="fa-solid fa-eye"></i> DÃ©tails
          </a>
          <button class="btn btn-primary btn-sm" style="flex:1; justify-content:center;">
            <i class="fa-solid fa-pen"></i> Modifier
          </button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

</div>

<?php require_once '../includes/footer.php'; ?>


