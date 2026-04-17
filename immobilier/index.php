<?php
require_once __DIR__ . '/config/app.php';
if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/pages/dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp   = $_POST['mot_de_passe'] ?? '';
    if ($email && $mdp) {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = ? AND actif = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($mdp, $user['mot_de_passe'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: ' . APP_URL . '/pages/dashboard.php');
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion &mdash; <?= APP_NAME ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'DM Sans', sans-serif;
      min-height: 100vh;
      background: #f5f0e8;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      position: relative;
    }

    /* PANNEAU GAUCHE BRUN CHAUD */
    .panel-left {
      position: fixed;
      left: 0; top: 0;
      width: 45%;
      height: 100%;
      background: linear-gradient(160deg, #2c2416 0%, #5a3e1b 45%, #8b6234 100%);
      clip-path: polygon(0 0, 88% 0, 100% 100%, 0 100%);
      z-index: 1;
    }
    .panel-left::before {
      content: '';
      position: absolute; inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M0 0h40v40H0zm40 40h40v40H40z'/%3E%3C/g%3E%3C/svg%3E");
    }

    .panel-content {
      position: fixed;
      left: 0; top: 0;
      width: 40%;
      height: 100%;
      z-index: 2;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 60px 50px;
      color: #fff;
    }

    .brand-pill {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,0.1);
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 50px; padding: 8px 18px;
      font-size: 11px; letter-spacing: 2.5px;
      text-transform: uppercase; color: #f0d080;
      margin-bottom: 40px; width: fit-content;
    }

    .panel-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 54px; line-height: 1.1;
      font-weight: 700; color: #fff;
      margin-bottom: 20px;
    }
    .panel-title em { color: #d4a94a; font-style: normal; }

    .panel-desc {
      font-size: 14px;
      color: rgba(255,255,255,0.55);
      line-height: 1.8;
      max-width: 270px;
      margin-bottom: 50px;
    }

    .stats { display: flex; gap: 32px; }
    .stat-val {
      font-family: 'Cormorant Garamond', serif;
      font-size: 34px; font-weight: 700;
      color: #d4a94a;
    }
    .stat-lbl {
      font-size: 10px; color: rgba(255,255,255,0.45);
      text-transform: uppercase; letter-spacing: 1.5px;
      margin-top: 2px;
    }

    /* CARTE LOGIN */
    .login-outer {
      position: relative; z-index: 10;
      margin-left: 40%;
      display: flex; align-items: center; justify-content: center;
      width: 60%; min-height: 100vh;
      padding: 40px;
    }

    .login-card {
      background: #fff;
      border-radius: 28px;
      padding: 52px 48px;
      width: 100%;
      max-width: 420px;
      box-shadow:
        0 2px 4px rgba(0,0,0,0.04),
        0 8px 16px rgba(0,0,0,0.06),
        0 32px 64px rgba(0,0,0,0.10);
      animation: slideIn .5s ease forwards;
    }

    @keyframes slideIn {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .card-eyebrow {
      font-size: 11px; letter-spacing: 3px;
      text-transform: uppercase;
      color: #c4902a; font-weight: 500;
      margin-bottom: 10px;
    }
    .card-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 38px; font-weight: 700;
      color: #1a1208; margin-bottom: 6px;
      line-height: 1.1;
    }
    .card-sub {
      font-size: 13px; color: #9a8e7a;
      margin-bottom: 38px;
    }

    .form-label {
      display: block;
      font-size: 12px; font-weight: 500;
      color: #5a4f3e; letter-spacing: 0.5px;
      margin-bottom: 8px;
    }
    .input-wrap {
      position: relative; margin-bottom: 22px;
    }
    .input-icon {
      position: absolute; left: 16px;
      top: 50%; transform: translateY(-50%);
      color: #c0aa88; font-size: 16px;
      pointer-events: none;
    }
    .form-input {
      width: 100%;
      padding: 14px 16px 14px 46px;
      border: 1.5px solid #e8dfd0;
      border-radius: 14px;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px; color: #2c2416;
      background: #faf8f4;
      outline: none;
      transition: border-color .2s, box-shadow .2s, background .2s;
    }
    .form-input:focus {
      border-color: #c4902a;
      background: #fff;
      box-shadow: 0 0 0 4px rgba(196,144,42,0.12);
    }
    .form-input::placeholder { color: #c5b99f; }

    .eye-toggle {
      position: absolute; right: 14px;
      top: 50%; transform: translateY(-50%);
      background: none; border: none;
      color: #b8a98a; cursor: pointer;
      font-size: 16px; padding: 4px;
    }
    .eye-toggle:hover { color: #8b6234; }

    .btn-submit {
      width: 100%; padding: 15px;
      background: linear-gradient(135deg, #c4902a 0%, #8b6234 100%);
      color: #fff; border: none;
      border-radius: 14px;
      font-family: 'DM Sans', sans-serif;
      font-size: 15px; font-weight: 600;
      cursor: pointer; letter-spacing: 0.5px;
      box-shadow: 0 8px 24px rgba(196,144,42,0.4);
      transition: transform .2s, box-shadow .2s;
      margin-top: 4px;
    }
    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 36px rgba(196,144,42,0.5);
    }
    .btn-submit:active { transform: translateY(0); }

    .alert-err {
      background: #fef2f0;
      border: 1px solid #f5c6c0;
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 13px; color: #c0392b;
      margin-bottom: 24px;
      display: flex; align-items: center; gap: 8px;
    }

    .demo-box {
      margin-top: 26px;
      padding: 14px 18px;
      background: #faf6ee;
      border: 1px dashed #d4b96a;
      border-radius: 14px;
      display: flex; align-items: center; gap: 10px;
    }
    .demo-dot {
      width: 8px; height: 8px;
      background: #c4902a;
      border-radius: 50%; flex-shrink: 0;
    }
    .demo-text { font-size: 12px; color: #8a7555; line-height: 1.5; }
    .demo-text strong { color: #5a3e1b; }

    @media (max-width: 768px) {
      .panel-left, .panel-content { display: none; }
      .login-outer { margin-left: 0; width: 100%; }
    }
  </style>
</head>
<body>

<div class="panel-left"></div>

<div class="panel-content">
  <div class="brand-pill">✦ ImmoGest Pro</div>
  <h1 class="panel-title">Gérez votre<br>patrimoine<br><em>immobilier</em></h1>
  <p class="panel-desc">Plateforme complète pour la gestion de vos biens, locataires, contrats et finances.</p>
  <div class="stats">
    <div>
      <div class="stat-val">100%</div>
      <div class="stat-lbl">Sécurisé</div>
    </div>
    <div>
      <div class="stat-val">∞</div>
      <div class="stat-lbl">Biens</div>
    </div>
    <div>
      <div class="stat-val">24/7</div>
      <div class="stat-lbl">Accès</div>
    </div>
  </div>
</div>

<div class="login-outer">
  <div class="login-card">

    <div class="card-eyebrow">✦ Espace sécurisé</div>
    <h2 class="card-title">Connexion</h2>
    <p class="card-sub">Accédez à votre tableau de bord</p>

    <?php if ($error): ?>
    <div class="alert-err">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <label class="form-label">Adresse email</label>
      <div class="input-wrap">
        <i class="bi bi-envelope input-icon"></i>
        <input type="email" name="email" class="form-input"
               placeholder="admin@immobilier.sn"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               required autofocus>
      </div>

      <label class="form-label">Mot de passe</label>
      <div class="input-wrap">
        <i class="bi bi-lock input-icon"></i>
        <input type="password" name="mot_de_passe" id="mdp"
               class="form-input" placeholder="••••••••" required>
        <button type="button" class="eye-toggle"
          onclick="const i=document.getElementById('mdp'); i.type=i.type==='password'?'text':'password'; this.querySelector('i').className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash'">
          <i class="bi bi-eye"></i>
        </button>
      </div>

      <button type="submit" class="btn-submit">
        <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
      </button>
    </form>

    <div class="demo-box">
      <div class="demo-dot"></div>
      <div class="demo-text">
        <strong>Compte démo :</strong><br>
        admin@immobilier.sn &nbsp;/&nbsp; admin123
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>