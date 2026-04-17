<?php
require_once __DIR__ . '/../config/app.php';
requireLogin();
$currentUser = getCurrentUser();
$pageTitle   = 'Mon profil — ' . APP_NAME;
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'profil') {
        $db->prepare("UPDATE utilisateurs SET nom=?, prenom=?, email=? WHERE id=?")->execute([
            $_POST['nom'], $_POST['prenom'], $_POST['email'], $currentUser['id']
        ]);
        setFlash('success', 'Profil mis à jour.');
    } elseif ($action === 'mdp') {
        $user = $db->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id=?");
        $user->execute([$currentUser['id']]);
        $u = $user->fetch();
        if ($u && password_verify($_POST['ancien_mdp'], $u['mot_de_passe'])) {
            if ($_POST['nouveau_mdp'] === $_POST['confirm_mdp']) {
                $db->prepare("UPDATE utilisateurs SET mot_de_passe=? WHERE id=?")->execute([
                    password_hash($_POST['nouveau_mdp'], PASSWORD_DEFAULT), $currentUser['id']
                ]);
                setFlash('success', 'Mot de passe modifié.');
            } else {
                setFlash('danger', 'Les mots de passe ne correspondent pas.');
            }
        } else {
            setFlash('danger', 'Ancien mot de passe incorrect.');
        }
    }
    header('Location: ' . APP_URL . '/pages/profil.php'); exit;
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <h4 class="fw-bold mb-4"><i class="bi bi-person-circle me-2 text-primary"></i>Mon profil</h4>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white pt-4 border-0"><h6 class="fw-bold">Informations personnelles</h6></div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action" value="profil">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Prénom</label><input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($currentUser['prenom']) ?>" required></div>
            <div class="col-md-6"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($currentUser['nom']) ?>" required></div>
            <div class="col-12"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($currentUser['email']) ?>" required></div>
            <div class="col-12"><label class="form-label">Rôle</label><input type="text" class="form-control" value="<?= htmlspecialchars($currentUser['role']) ?>" disabled></div>
          </div>
          <div class="mt-3 text-end"><button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Enregistrer</button></div>
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white pt-4 border-0"><h6 class="fw-bold">Changer le mot de passe</h6></div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action" value="mdp">
          <div class="mb-3"><label class="form-label">Ancien mot de passe</label><input type="password" name="ancien_mdp" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Nouveau mot de passe</label><input type="password" name="nouveau_mdp" class="form-control" required minlength="6"></div>
          <div class="mb-3"><label class="form-label">Confirmer</label><input type="password" name="confirm_mdp" class="form-control" required minlength="6"></div>
          <div class="text-end"><button type="submit" class="btn btn-warning"><i class="bi bi-key me-1"></i>Changer</button></div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
