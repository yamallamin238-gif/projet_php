# 🏢 Application de Gestion Immobilière — PHP/MySQL

Application web complète de gestion immobilière : locataires, loyers, contrats, charges et rapports.

---

## 📋 Fonctionnalités

| Module | Fonctionnalités |
|--------|----------------|
| **Propriétaires** | CRUD complet, coordonnées bancaires |
| **Immeubles** | Gestion par propriétaire, types multiples |
| **Logements** | Unités locatives, statuts, loyers de base |
| **Locataires** | Fiche complète, CNI, solvabilité |
| **Contrats** | Création, résiliation, types de bail |
| **Loyers / Quittances** | Génération, encaissement, suivi retards |
| **États des lieux** | Entrée/sortie, restitution dépôt |
| **Charges** | Dépenses par immeuble/logement |
| **Rapports** | Graphiques, taux occupation, KPI |
| **Impression** | Quittances imprimables |

---

## ⚙️ Installation

### Prérequis
- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10+
- Serveur web : Apache (WAMP/XAMPP) ou Nginx

### Étapes

**1. Copier les fichiers**
```
Copiez le dossier `immobilier/` dans votre répertoire web :
- XAMPP : C:\xampp\htdocs\
- WAMP  : C:\wamp64\www\
- Linux : /var/www/html/
```

**2. Créer la base de données**
```sql
-- Via phpMyAdmin ou ligne de commande :
mysql -u root -p < sql/database.sql
```

**3. Configurer la connexion**

Editez le fichier `config/database.php` :
```php
define('DB_HOST', 'localhost');     // Votre hôte MySQL
define('DB_NAME', 'gestion_immobiliere');
define('DB_USER', 'root');          // Votre utilisateur
define('DB_PASS', '');              // Votre mot de passe
define('APP_URL', 'http://localhost/immobilier');  // URL du projet
```

**4. Créer le dossier uploads**
```bash
mkdir uploads
chmod 775 uploads   # Linux uniquement
```

**5. Accéder à l'application**
```
http://localhost/immobilier/
```

---

## 🔑 Connexion par défaut

| Champ | Valeur |
|-------|--------|
| Email | `admin@immobilier.sn` |
| Mot de passe | `admin123` |

> ⚠️ Changez le mot de passe après la première connexion !

---

## 📂 Structure des fichiers

```
immobilier/
├── index.php                  ← Page de connexion
├── config/
│   ├── database.php           ← Connexion PDO
│   └── app.php                ← Fonctions globales & session
├── includes/
│   ├── header.php             ← Navbar + en-tête HTML
│   └── footer.php             ← Pied de page + JS
├── pages/
│   ├── dashboard.php          ← Tableau de bord
│   ├── proprietaires.php      ← Gestion propriétaires
│   ├── immeubles.php          ← Gestion immeubles
│   ├── logements.php          ← Gestion logements
│   ├── locataires.php         ← Gestion locataires
│   ├── contrats.php           ← Contrats de location
│   ├── quittances.php         ← Loyers & paiements
│   ├── etats_lieux.php        ← États des lieux
│   ├── charges.php            ← Charges & dépenses
│   ├── rapports.php           ← Rapports & graphiques
│   ├── imprimer_quittance.php ← Impression quittance
│   ├── profil.php             ← Profil utilisateur
│   └── logout.php             ← Déconnexion
├── assets/
│   ├── css/style.css          ← Styles personnalisés
│   └── js/app.js              ← JavaScript
├── sql/
│   └── database.sql           ← Script BDD complet
└── uploads/                   ← Fichiers uploadés
```

---

## 🗄️ Structure de la base de données

| Table | Description |
|-------|-------------|
| `utilisateurs` | Comptes admin/gestionnaire |
| `proprietaires` | Propriétaires des biens |
| `immeubles` | Immeubles et résidences |
| `logements` | Unités locatives |
| `locataires` | Locataires |
| `contrats` | Contrats de location |
| `quittances` | Quittances de loyer |
| `paiements` | Détail des paiements |
| `charges` | Dépenses et charges |
| `etats_lieux` | États des lieux entrée/sortie |
| `documents` | Pièces jointes |
| `alertes` | Notifications système |

### Vues SQL créées
- `v_contrats_actifs` — Vue des contrats en cours
- `v_loyers_impayes` — Vue des loyers impayés
- `v_tableau_de_bord` — Statistiques globales

---

## 🔧 Technologies utilisées

- **Backend** : PHP 8+ (PDO, sessions)
- **Base de données** : MySQL / MariaDB
- **Frontend** : Bootstrap 5.3 + Bootstrap Icons
- **Graphiques** : Chart.js
- **Sécurité** : `password_hash()`, requêtes préparées PDO

---

## 📞 Support

Pour toute question, consultez le code source commenté ou adaptez selon vos besoins.
