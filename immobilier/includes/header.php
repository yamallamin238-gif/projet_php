<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?? 'ImmoGest Pro' ?></title>

  <!-- Font Awesome 6 Free (Solid = fa-solid = fas) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap">
  <!-- App CSS -->
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">

  <!-- Logo -->
  <div class="sidebar-logo">
    <div class="logo-icon-wrap">
      <i class="fa-solid fa-building" style="color:#000; font-size:20px; position:relative; z-index:1;"></i>
    </div>
    <div class="logo-text">
      <h2>ImmoGest</h2>
      <span>Pro Â· Tableau de bord</span>
    </div>
  </div>

  <!-- NAV: PRINCIPAL -->
  <div class="nav-section">
    <p class="nav-label">Principal</p>

    <a href="dashboard.php" class="nav-item <?= $current_page=='dashboard'?'active':'' ?>">
      <span class="nav-icon icon-gold">
        <i class="fa-solid fa-gauge-high" style="color:#fff;"></i>
      </span>
      Tableau de bord
    </a>

    <a href="biens.php" class="nav-item <?= $current_page=='biens'?'active':'' ?>">
      <span class="nav-icon icon-blue">
        <i class="fa-solid fa-city" style="color:#fff;"></i>
      </span>
      Biens immobiliers
    </a>

    <a href="locataires.php" class="nav-item <?= $current_page=='locataires'?'active':'' ?>">
      <span class="nav-icon icon-green">
        <i class="fa-solid fa-users" style="color:#fff;"></i>
      </span>
      Locataires
    </a>

    <a href="contrats.php" class="nav-item <?= $current_page=='contrats'?'active':'' ?>">
      <span class="nav-icon icon-purple">
        <i class="fa-solid fa-file-contract" style="color:#fff;"></i>
      </span>
      Contrats
    </a>
  </div>

  <!-- NAV: FINANCES -->
  <div class="nav-section">
    <p class="nav-label">Finances</p>

    <a href="loyers.php" class="nav-item <?= $current_page=='loyers'?'active':'' ?>">
      <span class="nav-icon icon-orange">
        <i class="fa-solid fa-hand-holding-dollar" style="color:#fff;"></i>
      </span>
      Loyers & Paiements
      <span class="nav-badge">3</span>
    </a>

    <a href="charges.php" class="nav-item <?= $current_page=='charges'?'active':'' ?>">
      <span class="nav-icon icon-red">
        <i class="fa-solid fa-receipt" style="color:#fff;"></i>
      </span>
      Charges & DÃ©penses
    </a>

    <a href="rapports.php" class="nav-item <?= $current_page=='rapports'?'active':'' ?>">
      <span class="nav-icon icon-teal">
        <i class="fa-solid fa-chart-line" style="color:#fff;"></i>
      </span>
      Rapports & Stats
    </a>
  </div>

  <!-- NAV: GESTION -->
  <div class="nav-section">
    <p class="nav-label">Gestion</p>

    <a href="maintenances.php" class="nav-item <?= $current_page=='maintenances'?'active':'' ?>">
      <span class="nav-icon icon-pink">
        <i class="fa-solid fa-screwdriver-wrench" style="color:#fff;"></i>
      </span>
      Maintenance
      <span class="nav-badge">2</span>
    </a>

    <a href="documents.php" class="nav-item <?= $current_page=='documents'?'active':'' ?>">
      <span class="nav-icon icon-blue">
        <i class="fa-solid fa-folder-open" style="color:#fff;"></i>
      </span>
      Documents
    </a>

    <a href="agenda.php" class="nav-item <?= $current_page=='agenda'?'active':'' ?>">
      <span class="nav-icon icon-green">
        <i class="fa-solid fa-calendar-days" style="color:#fff;"></i>
      </span>
      Agenda
    </a>

    <a href="parametres.php" class="nav-item <?= $current_page=='parametres'?'active':'' ?>">
      <span class="nav-icon icon-gold">
        <i class="fa-solid fa-gear" style="color:#fff;"></i>
      </span>
      ParamÃ¨tres
    </a>
  </div>

  <!-- USER CARD -->
  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar">A</div>
      <div class="user-info">
        <div class="user-name">Admin</div>
        <div class="user-role">Gestionnaire</div>
      </div>
      <i class="fa-solid fa-ellipsis-vertical" style="color:var(--text-muted); font-size:13px;"></i>
    </div>
  </div>

</aside>

<!-- ===== TOPBAR ===== -->
<div class="main">
<header class="topbar">
  <div class="topbar-title">
    <?= $page_title ?? 'Tableau de bord' ?>
    <?php if(isset($page_subtitle)): ?>
      <span> Â· <?= $page_subtitle ?></span>
    <?php endif; ?>
  </div>

  <!-- Search -->
  <div class="search-box">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" placeholder="Rechercher...">
  </div>

  <!-- Actions -->
  <div class="topbar-actions">
    <button class="btn-icon" title="Notifications">
      <i class="fa-solid fa-bell"></i>
      <span class="notif-dot"></span>
    </button>
    <button class="btn-icon" title="Messages">
      <i class="fa-solid fa-envelope"></i>
    </button>
    <button class="btn-icon" id="toggleTheme" title="Mode clair/sombre" onclick="toggleTheme()"><i class="fa-solid fa-sun" id="themeIcon"></i></button>
  </div>
</header>

