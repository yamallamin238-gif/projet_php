-- ============================================================
-- APPLICATION DE GESTION IMMOBILIÃˆRE
-- Base de donnÃ©es complÃ¨te
-- ============================================================

CREATE DATABASE IF NOT EXISTS gestionimmobilier
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE gestionimmobilier;

-- ============================================================
-- TABLE : utilisateurs (administrateurs du systÃ¨me)
-- ============================================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin','gestionnaire','comptable') DEFAULT 'gestionnaire',
    actif TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE : proprietaires
-- ============================================================
CREATE TABLE IF NOT EXISTS proprietaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    civilite ENUM('M.','Mme','Mlle','SociÃ©tÃ©') DEFAULT 'M.',
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100),
    email VARCHAR(150),
    telephone VARCHAR(20),
    adresse TEXT,
    ville VARCHAR(100),
    code_postal VARCHAR(10),
    pays VARCHAR(60) DEFAULT 'SÃ©nÃ©gal',
    rib VARCHAR(30),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE : immeubles / rÃ©sidences
-- ============================================================
CREATE TABLE IF NOT EXISTS immeubles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proprietaire_id INT NOT NULL,
    nom VARCHAR(150) NOT NULL,
    adresse TEXT NOT NULL,
    ville VARCHAR(100) NOT NULL,
    code_postal VARCHAR(10),
    pays VARCHAR(60) DEFAULT 'SÃ©nÃ©gal',
    type ENUM('appartements','maisons','commerces','mixte') DEFAULT 'appartements',
    nb_unites INT DEFAULT 1,
    description TEXT,
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (proprietaire_id) REFERENCES proprietaires(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE : logements / unitÃ©s locatives
-- ============================================================
CREATE TABLE IF NOT EXISTS logements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    immeuble_id INT NOT NULL,
    reference VARCHAR(50) NOT NULL,
    type ENUM('studio','F1','F2','F3','F4','F5+','commerce','bureau','parking') DEFAULT 'F2',
    etage INT DEFAULT 0,
    surface DECIMAL(8,2),
    nb_pieces INT DEFAULT 1,
    nb_chambres INT DEFAULT 1,
    loyer_base DECIMAL(10,2) NOT NULL,
    charges DECIMAL(10,2) DEFAULT 0.00,
    depot_garantie DECIMAL(10,2) DEFAULT 0.00,
    statut ENUM('libre','occupe','travaux','indisponible') DEFAULT 'libre',
    description TEXT,
    equipements TEXT,
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (immeuble_id) REFERENCES immeubles(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE : locataires
-- ============================================================
CREATE TABLE IF NOT EXISTS locataires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    civilite ENUM('M.','Mme','Mlle') DEFAULT 'M.',
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE,
    lieu_naissance VARCHAR(100),
    nationalite VARCHAR(80) DEFAULT 'SÃ©nÃ©galaise',
    num_cni VARCHAR(50),
    email VARCHAR(150),
    telephone VARCHAR(20),
    telephone2 VARCHAR(20),
    adresse_precedente TEXT,
    profession VARCHAR(100),
    employeur VARCHAR(150),
    salaire_mensuel DECIMAL(10,2),
    contact_urgence_nom VARCHAR(150),
    contact_urgence_tel VARCHAR(20),
    notes TEXT,
    statut ENUM('actif','inactif','liste_noire') DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE : contrats de location
-- ============================================================
CREATE TABLE IF NOT EXISTS contrats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(50) NOT NULL UNIQUE,
    locataire_id INT NOT NULL,
    logement_id INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE,
    type ENUM('cdi','cdd','saisonnier','meuble') DEFAULT 'cdi',
    loyer_mensuel DECIMAL(10,2) NOT NULL,
    charges_mensuelles DECIMAL(10,2) DEFAULT 0.00,
    depot_garantie DECIMAL(10,2) DEFAULT 0.00,
    depot_garantie_paye TINYINT(1) DEFAULT 0,
    depot_garantie_date_paiement DATE,
    jour_echeance INT DEFAULT 1,
    frequence_paiement ENUM('mensuel','trimestriel','annuel') DEFAULT 'mensuel',
    indexation DECIMAL(5,2) DEFAULT 0.00,
    statut ENUM('en_cours','resilie','expire','suspendu') DEFAULT 'en_cours',
    date_resiliation DATE,
    motif_resiliation TEXT,
    conditions_particulieres TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (locataire_id) REFERENCES locataires(id) ON DELETE RESTRICT,
    FOREIGN KEY (logement_id) REFERENCES logements(id) ON DELETE RESTRICT
);

-- ============================================================
-- TABLE : quittances / loyers
-- ============================================================
CREATE TABLE IF NOT EXISTS quittances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrat_id INT NOT NULL,
    numero VARCHAR(50) NOT NULL UNIQUE,
    periode_debut DATE NOT NULL,
    periode_fin DATE NOT NULL,
    date_echeance DATE NOT NULL,
    montant_loyer DECIMAL(10,2) NOT NULL,
    montant_charges DECIMAL(10,2) DEFAULT 0.00,
    montant_total DECIMAL(10,2) NOT NULL,
    montant_paye DECIMAL(10,2) DEFAULT 0.00,
    date_paiement DATE,
    mode_paiement ENUM('especes','virement','cheque','mobile_money','autre') DEFAULT 'especes',
    reference_paiement VARCHAR(100),
    statut ENUM('en_attente','partiel','paye','retard','annule') DEFAULT 'en_attente',
    penalite_retard DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contrat_id) REFERENCES contrats(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE : paiements (dÃ©tail des paiements)
-- ============================================================
CREATE TABLE IF NOT EXISTS paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quittance_id INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    date_paiement DATE NOT NULL,
    mode_paiement ENUM('especes','virement','cheque','mobile_money','autre') DEFAULT 'especes',
    reference VARCHAR(100),
    recu_numero VARCHAR(50),
    notes TEXT,
    utilisateur_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quittance_id) REFERENCES quittances(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- ============================================================
-- TABLE : charges et dÃ©penses
-- ============================================================
CREATE TABLE IF NOT EXISTS charges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    immeuble_id INT,
    logement_id INT,
    type ENUM('travaux','entretien','assurance','taxe','eau','electricite','gardiennage','autre') NOT NULL,
    libelle VARCHAR(200) NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    date_charge DATE NOT NULL,
    fournisseur VARCHAR(150),
    facture_ref VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (immeuble_id) REFERENCES immeubles(id) ON DELETE SET NULL,
    FOREIGN KEY (logement_id) REFERENCES logements(id) ON DELETE SET NULL
);

-- ============================================================
-- TABLE : documents
-- ============================================================
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('contrat','cni','justificatif','etat_lieux','autre') DEFAULT 'autre',
    entite_type ENUM('locataire','contrat','logement','immeuble') NOT NULL,
    entite_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    fichier VARCHAR(255) NOT NULL,
    taille INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE : etats des lieux
-- ============================================================
CREATE TABLE IF NOT EXISTS etats_lieux (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrat_id INT NOT NULL,
    type ENUM('entree','sortie') NOT NULL,
    date_etat DATE NOT NULL,
    etat_general ENUM('tres_bon','bon','moyen','mauvais') DEFAULT 'bon',
    observations TEXT,
    depot_restitue DECIMAL(10,2) DEFAULT 0.00,
    date_restitution DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contrat_id) REFERENCES contrats(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE : alertes / notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS alertes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('loyer_impaye','contrat_expire','document_manquant','maintenance','autre') NOT NULL,
    entite_type VARCHAR(50),
    entite_id INT,
    message TEXT NOT NULL,
    niveau ENUM('info','warning','danger') DEFAULT 'info',
    lue TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- DONNÃ‰ES INITIALES
-- ============================================================

-- Administrateur par dÃ©faut (mot de passe: admin123)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES
('Administrateur', 'SystÃ¨me', 'admin@immobilier.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- PropriÃ©taire exemple
INSERT INTO proprietaires (civilite, nom, prenom, email, telephone, adresse, ville, pays) VALUES
('M.', 'Diallo', 'Mamadou', 'mdiallo@email.sn', '+221 77 123 45 67', '15 Rue des Almadies', 'Dakar', 'SÃ©nÃ©gal'),
('Mme', 'Seck', 'Fatou', 'fseck@email.sn', '+221 76 987 65 43', '8 Avenue Bourguiba', 'ThiÃ¨s', 'SÃ©nÃ©gal');

-- Immeuble exemple
INSERT INTO immeubles (proprietaire_id, nom, adresse, ville, type, nb_unites) VALUES
(1, 'RÃ©sidence Les Almadies', '15 Rue des Almadies, Almadies', 'Dakar', 'appartements', 6),
(2, 'Immeuble Centre ThiÃ¨s', '8 Avenue Bourguiba', 'ThiÃ¨s', 'mixte', 4);

-- Logements exemple
INSERT INTO logements (immeuble_id, reference, type, etage, surface, nb_pieces, nb_chambres, loyer_base, charges, depot_garantie, statut) VALUES
(1, 'ALMD-01', 'F3', 1, 85.50, 4, 2, 250000, 15000, 500000, 'occupe'),
(1, 'ALMD-02', 'F2', 1, 65.00, 3, 1, 180000, 10000, 360000, 'occupe'),
(1, 'ALMD-03', 'F4', 2, 110.00, 5, 3, 350000, 20000, 700000, 'libre'),
(1, 'ALMD-04', 'studio', 0, 35.00, 1, 1, 100000, 5000, 200000, 'libre'),
(2, 'THIES-01', 'commerce', 0, 120.00, 2, 0, 400000, 0, 800000, 'occupe'),
(2, 'THIES-02', 'F3', 1, 90.00, 4, 2, 200000, 12000, 400000, 'libre');

-- Locataires exemple
INSERT INTO locataires (civilite, nom, prenom, date_naissance, nationalite, num_cni, email, telephone, profession, salaire_mensuel, statut) VALUES
('M.', 'Ndiaye', 'Ibrahima', '1985-03-15', 'SÃ©nÃ©galaise', '1234567890123', 'indiaye@email.sn', '+221 77 111 22 33', 'IngÃ©nieur', 800000, 'actif'),
('Mme', 'Ba', 'Aminata', '1990-07-22', 'SÃ©nÃ©galaise', '9876543210987', 'aba@email.sn', '+221 76 444 55 66', 'MÃ©decin', 1200000, 'actif'),
('M.', 'Fall', 'Ousmane', '1978-11-08', 'SÃ©nÃ©galaise', '5555555555555', 'ofall@email.sn', '+221 70 777 88 99', 'CommerÃ§ant', 600000, 'actif');

-- Contrats exemple
INSERT INTO contrats (numero, locataire_id, logement_id, date_debut, type, loyer_mensuel, charges_mensuelles, depot_garantie, depot_garantie_paye, jour_echeance, statut) VALUES
('CTR-2024-001', 1, 1, '2024-01-01', 'cdi', 250000, 15000, 500000, 1, 5, 'en_cours'),
('CTR-2024-002', 2, 2, '2024-03-01', 'cdi', 180000, 10000, 360000, 1, 1, 'en_cours'),
('CTR-2024-003', 3, 5, '2024-06-01', 'cdi', 400000, 0, 800000, 1, 1, 'en_cours');

-- Quittances exemple
INSERT INTO quittances (contrat_id, numero, periode_debut, periode_fin, date_echeance, montant_loyer, montant_charges, montant_total, montant_paye, date_paiement, mode_paiement, statut) VALUES
(1, 'QUIT-2024-001', '2024-01-01', '2024-01-31', '2024-01-05', 250000, 15000, 265000, 265000, '2024-01-04', 'virement', 'paye'),
(1, 'QUIT-2024-002', '2024-02-01', '2024-02-29', '2024-02-05', 250000, 15000, 265000, 265000, '2024-02-03', 'virement', 'paye'),
(1, 'QUIT-2024-003', '2024-03-01', '2024-03-31', '2024-03-05', 250000, 15000, 265000, 0, NULL, NULL, 'retard'),
(2, 'QUIT-2024-004', '2024-03-01', '2024-03-31', '2024-03-01', 180000, 10000, 190000, 190000, '2024-03-01', 'especes', 'paye'),
(3, 'QUIT-2024-005', '2024-06-01', '2024-06-30', '2024-06-01', 400000, 0, 400000, 400000, '2024-05-30', 'cheque', 'paye');

-- ============================================================
-- VUES UTILES
-- ============================================================

CREATE OR REPLACE VIEW v_contrats_actifs AS
SELECT
    c.id, c.numero, c.date_debut, c.date_fin, c.loyer_mensuel, c.charges_mensuelles, c.statut,
    CONCAT(l.civilite, ' ', l.prenom, ' ', l.nom) AS locataire_nom,
    l.email AS locataire_email, l.telephone AS locataire_telephone,
    lg.reference AS logement_ref, lg.type AS logement_type,
    i.nom AS immeuble_nom, i.ville AS immeuble_ville
FROM contrats c
JOIN locataires l ON c.locataire_id = l.id
JOIN logements lg ON c.logement_id = lg.id
JOIN immeubles i ON lg.immeuble_id = i.id
WHERE c.statut = 'en_cours';

CREATE OR REPLACE VIEW v_loyers_impayes AS
SELECT
    q.id, q.numero, q.periode_debut, q.periode_fin, q.montant_total, q.montant_paye,
    (q.montant_total - q.montant_paye) AS reste_a_payer, q.date_echeance, q.statut,
    CONCAT(l.prenom, ' ', l.nom) AS locataire_nom, l.telephone,
    lg.reference AS logement, i.nom AS immeuble
FROM quittances q
JOIN contrats c ON q.contrat_id = c.id
JOIN locataires l ON c.locataire_id = l.id
JOIN logements lg ON c.logement_id = lg.id
JOIN immeubles i ON lg.immeuble_id = i.id
WHERE q.statut IN ('en_attente','partiel','retard');

CREATE OR REPLACE VIEW v_tableau_de_bord AS
SELECT
    (SELECT COUNT(*) FROM logements WHERE statut='occupe') AS logements_occupes,
    (SELECT COUNT(*) FROM logements WHERE statut='libre') AS logements_libres,
    (SELECT COUNT(*) FROM locataires WHERE statut='actif') AS locataires_actifs,
    (SELECT COUNT(*) FROM contrats WHERE statut='en_cours') AS contrats_actifs,
    (SELECT COALESCE(SUM(montant_total - montant_paye),0) FROM quittances WHERE statut IN ('retard','partiel','en_attente')) AS total_impayes,
    (SELECT COALESCE(SUM(montant_paye),0) FROM quittances WHERE MONTH(date_paiement)=MONTH(CURDATE()) AND YEAR(date_paiement)=YEAR(CURDATE())) AS recettes_mois;

-- ============================================================
-- FIN DU SCRIPT
-- ============================================================

