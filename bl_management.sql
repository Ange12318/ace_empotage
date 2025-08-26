-- Supprimer l'ancienne base si elle existe
DROP DATABASE IF EXISTS bl_management;

-- Créer une nouvelle base de données
CREATE DATABASE bl_management CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE bl_management;

-- Table principale des BL
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

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'standard') DEFAULT 'standard',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Table de journalisation des actions
CREATE TABLE user_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    action_details TEXT,
    target_id INT NULL,
    target_table VARCHAR(50) NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insérer l'administrateur par défaut
INSERT INTO users (username, password, role) 
VALUES ('admin', 'admin123', 'admin');

-- Optionnel : insérer un exemple de BL
INSERT INTO bl (banque, client, transitaire, produit, numero_das, poids, date_accord_banque, date_empotage, statut)
VALUES ('BNI', 'Client 1', 'Moussa', 'Produit A', '254ESE', 4000, '2024-05-01', '2024-05-05', 'pending');