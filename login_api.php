<?php
// login_api.php : API pour gérer l'authentification
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

require_once __DIR__ . "/config.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true) ?? [];
    
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(["error" => "Identifiant et mot de passe requis"]);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND is_active = TRUE");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            
            logAction($user['id'], 'LOGIN', "Connexion réussie");
            
            echo json_encode([
                "message" => "Connexion réussie", 
                "success" => true,
                "role" => $user['role'],
                "username" => $user['username']
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Identifiant ou mot de passe incorrect"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Erreur de connexion: " . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Méthode non autorisée"]);