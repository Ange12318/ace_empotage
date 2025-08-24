<?php
// user_management_api.php : Gestion des utilisateurs
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

require_once __DIR__ . "/config.php";

// Vérifier l'authentification et les droits admin
if (!isAuthenticated() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(["error" => "Accès refusé. Droits administrateur requis."]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Lister tous les utilisateurs
    $sql = "SELECT id, username, role, created_at, updated_at, is_active FROM users ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true) ?? [];
    
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');
    $role = in_array($input['role'] ?? 'standard', ['admin', 'standard']) ? $input['role'] : 'standard';
    
    // Valider les données
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(["error" => "Nom d'utilisateur et mot de passe requis"]);
        exit;
    }
    
    try {
        $sql = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':role' => $role
        ]);
        
        $newId = $pdo->lastInsertId();
        logAction($_SESSION['user_id'], 'CREATE_USER', "Création utilisateur: $username", $newId, 'users');
        
        echo json_encode(["message" => "Utilisateur créé avec succès", "id" => $newId]);
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(["error" => "Erreur: " . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Méthode non autorisée"]);