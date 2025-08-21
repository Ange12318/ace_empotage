-- bl_management.sql : schéma MySQL
CREATE DATABASE IF NOT EXISTS bl_management CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE bl_management;

DROP TABLE IF EXISTS bl;
CREATE TABLE bl (
    id INT AUTO_INCREMENT PRIMARY KEY,
    banque VARCHAR(100) NOT NULL,
    client VARCHAR(100) NOT NULL,
    transitaire VARCHAR(100) NOT NULL,
    produit VARCHAR(150) NOT NULL,
    numero_das VARCHAR(100) NOT NULL,
    poids DECIMAL(10,2) NOT NULL,
    date_accord_banque DATE NULL,
    date_empotage DATE NULL,
    relance_r1 DATE NULL,
    relance_r2 DATE NULL,
    relance_r3 DATE NULL,
    relance_r4 DATE NULL,
    date_alerte_banque DATE NULL,
    statut ENUM('pending','completed') DEFAULT 'pending'
);

-- Optionnel : insérer un exemple
-- INSERT INTO bl (banque, client, transitaire, produit, numero_das, poids, date_accord_banque, date_empotage, statut)
-- VALUES ('BNI','Client 1','Moussa','Produit A','254ESE',4000,'2024-05-01','2024-05-05','pending');
