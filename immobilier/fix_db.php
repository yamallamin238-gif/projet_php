<?php
require_once __DIR__ . '/config/database.php';
$db = getDB();

// Table loyers manquante
$db->exec("CREATE TABLE IF NOT EXISTS loyers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  contrat_id INT NOT NULL,
  mois VARCHAR(7) NOT NULL,
  montant DECIMAL(12,2) NOT NULL,
  statut ENUM('impaye','paye','partiel') DEFAULT 'impaye',
  date_echeance DATE,
  date_paiement DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (contrat_id) REFERENCES contrats(id) ON DELETE CASCADE
)");
echo "Table loyers OK<br>";

// Table paiements si manquante
$db->exec("CREATE TABLE IF NOT EXISTS paiements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  loyer_id INT NOT NULL,
  montant DECIMAL(12,2) NOT NULL,
  date_paiement DATETIME NOT NULL,
  mode_paiement VARCHAR(50) DEFAULT 'espece',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (loyer_id) REFERENCES loyers(id) ON DELETE CASCADE
)");
echo "Table paiements OK<br>";

// Verifier/corriger mot de passe admin
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$db->prepare("UPDATE utilisateurs SET mot_de_passe=? WHERE email='admin@immobilier.sn'")->execute([$hash]);
echo "Mot de passe admin reset: admin123<br>";

// Verifier structure locataires
$cols = $db->query("SHOW COLUMNS FROM locataires")->fetchAll(PDO::FETCH_COLUMN);
echo "Colonnes locataires: " . implode(', ', $cols) . "<br>";

$cols2 = $db->query("SHOW COLUMNS FROM logements")->fetchAll(PDO::FETCH_COLUMN);
echo "Colonnes logements: " . implode(', ', $cols2) . "<br>";

$cols3 = $db->query("SHOW COLUMNS FROM contrats")->fetchAll(PDO::FETCH_COLUMN);
echo "Colonnes contrats: " . implode(', ', $cols3) . "<br>";

echo "<br><strong>TOUT OK - Supprimez ce fichier apres.</strong>";