<?php
// config.php : connexion PDO MySQL (XAMPP par défaut)
$host = "localhost";
$user = "root";   // utilisateur MySQL
$pass = "";       // mot de passe (souvent vide sous XAMPP)
$db   = "bl_management";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode(["error" => "Erreur de connexion : " . $e->getMessage()]);
    exit;
}
