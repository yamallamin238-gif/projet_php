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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
      <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-5">
          <div class="text-center mb-4">
            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px">
              <i class="bi bi-building-fill text-white fs-2"></i>
            </div>
            <h4 class="fw-bold"><?= APP_NAME ?></h4>
            <p class="text-muted small">Connectez-vous à votre espace</p>
          </div>

          <?php if ($error): ?>
          <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold">Email</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control" placeholder="admin@immobilier.sn"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
              </div>
            </div>
            <div class="mb-4">
              <label class="form-label fw-semibold">Mot de passe</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="mot_de_passe" class="form-control" placeholder="••••••••" required>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
              <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
            </button>
          </form>

          <div class="mt-4 p-3 bg-light rounded text-center">
            <small class="text-muted">
              <strong>Démo :</strong> admin@immobilier.sn / admin123
            </small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
