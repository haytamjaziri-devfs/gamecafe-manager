-- GameCafe Manager : base de données pour la gestion d'un gaming café
DROP DATABASE IF EXISTS gamecafe;
CREATE DATABASE gamecafe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gamecafe;

CREATE TABLE clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  telephone VARCHAR(20) UNIQUE,
  email VARCHAR(120),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE postes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(50) NOT NULL UNIQUE,
  type ENUM('PC','PS5','VR') NOT NULL,
  tarif_heure DECIMAL(6,2) NOT NULL CHECK (tarif_heure > 0),
  statut ENUM('libre','occupe','maintenance') NOT NULL DEFAULT 'libre'
);

CREATE TABLE produits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(80) NOT NULL UNIQUE,
  prix DECIMAL(6,2) NOT NULL CHECK (prix >= 0),
  stock INT NOT NULL DEFAULT 0 CHECK (stock >= 0)
);

CREATE TABLE sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  poste_id INT NOT NULL,
  debut DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fin DATETIME NULL,
  montant DECIMAL(8,2) NULL,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
  FOREIGN KEY (poste_id) REFERENCES postes(id)
);

CREATE TABLE ventes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id INT NULL,
  produit_id INT NOT NULL,
  quantite INT NOT NULL CHECK (quantite > 0),
  prix_unitaire DECIMAL(6,2) NOT NULL,
  date_vente TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL,
  FOREIGN KEY (produit_id) REFERENCES produits(id)
);

CREATE INDEX idx_sessions_debut ON sessions(debut);
CREATE INDEX idx_ventes_date ON ventes(date_vente);

-- ===== TRIGGERS =====
DELIMITER //

-- Une session qui démarre rend le poste "occupé"
CREATE TRIGGER trg_session_start AFTER INSERT ON sessions
FOR EACH ROW
BEGIN
  UPDATE postes SET statut = 'occupe' WHERE id = NEW.poste_id;
END//

-- Quand la session se termine : calcul automatique du montant + poste libéré
CREATE TRIGGER trg_session_end BEFORE UPDATE ON sessions
FOR EACH ROW
BEGIN
  DECLARE v_tarif DECIMAL(6,2);
  IF OLD.fin IS NULL AND NEW.fin IS NOT NULL THEN
    SELECT tarif_heure INTO v_tarif FROM postes WHERE id = NEW.poste_id;
    SET NEW.montant = ROUND(TIMESTAMPDIFF(MINUTE, NEW.debut, NEW.fin) / 60 * v_tarif, 2);
    UPDATE postes SET statut = 'libre' WHERE id = NEW.poste_id;
  END IF;
END//

-- Une vente diminue le stock
CREATE TRIGGER trg_vente_stock AFTER INSERT ON ventes
FOR EACH ROW
BEGIN
  UPDATE produits SET stock = stock - NEW.quantite WHERE id = NEW.produit_id;
END//

DELIMITER ;

-- ===== VUES =====
CREATE VIEW v_ca_journalier AS
SELECT d AS jour, SUM(total) AS chiffre_affaires FROM (
  SELECT DATE(fin) AS d, montant AS total FROM sessions WHERE montant IS NOT NULL
  UNION ALL
  SELECT DATE(date_vente), quantite * prix_unitaire FROM ventes
) t GROUP BY d ORDER BY d DESC;

CREATE VIEW v_top_clients AS
SELECT c.id, c.nom, COUNT(s.id) AS nb_sessions, COALESCE(SUM(s.montant),0) AS total_depense
FROM clients c LEFT JOIN sessions s ON s.client_id = c.id
GROUP BY c.id, c.nom ORDER BY total_depense DESC;

-- ===== DONNÉES DE TEST =====
INSERT INTO clients (nom, telephone, email) VALUES
('Youssef Alami','0661000001','youssef@mail.com'),
('Sara Bennani','0661000002','sara@mail.com'),
('Omar Idrissi','0661000003',NULL),
('Imane Tazi','0661000004','imane@mail.com');

INSERT INTO postes (nom, type, tarif_heure) VALUES
('PC-01','PC',10.00),('PC-02','PC',10.00),('PC-03','PC',10.00),
('PS5-01','PS5',15.00),('PS5-02','PS5',15.00),('VR-01','VR',30.00);

INSERT INTO produits (nom, prix, stock) VALUES
('Coca-Cola',8.00,50),('Eau minérale',5.00,60),('Café',10.00,40),('Chips',6.00,30),('Red Bull',20.00,25);

-- Sessions terminées (les triggers calculent le montant)
INSERT INTO sessions (client_id, poste_id, debut) VALUES (1,1,NOW() - INTERVAL 3 HOUR);
UPDATE sessions SET fin = NOW() - INTERVAL 1 HOUR WHERE id = 1;
INSERT INTO sessions (client_id, poste_id, debut) VALUES (2,4,NOW() - INTERVAL 2 HOUR);
UPDATE sessions SET fin = NOW() - INTERVAL 30 MINUTE WHERE id = 2;
-- Session en cours
INSERT INTO sessions (client_id, poste_id, debut) VALUES (3,2,NOW() - INTERVAL 40 MINUTE);

INSERT INTO ventes (session_id, produit_id, quantite, prix_unitaire) VALUES
(1,1,2,8.00),(2,5,1,20.00),(3,3,1,10.00);

-- ===== REQUÊTES UTILES =====
-- 1) Postes libres :           SELECT nom, type FROM postes WHERE statut='libre';
-- 2) CA du jour :              SELECT * FROM v_ca_journalier;
-- 3) Meilleurs clients :       SELECT * FROM v_top_clients LIMIT 3;
-- 4) Produits en rupture :     SELECT nom, stock FROM produits WHERE stock < 10;
-- 5) Taux d'occupation :       SELECT type, ROUND(100*SUM(statut='occupe')/COUNT(*)) AS pct FROM postes GROUP BY type;
