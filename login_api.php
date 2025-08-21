<?php
// login_api.php : API pour gérer l'authentification
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true) ?? [];
    
    // Comptes prédéfinis
    $users = [
        'Admin' => '01234',
        'SUPPORT' => '012345'
    ];
    
    $id = trim($input['id'] ?? '');
    $password = trim($input['password'] ?? '');
    
    if (isset($users[$id]) && $users[$id] === $password) {
        echo json_encode(["message" => "Connexion réussie", "success" => true]);
        exit;
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Identifiant ou mot de passe incorrect"]);
        exit;
    }
}

http_response_code(405);
echo json_encode(["error" => "Méthode non autorisée"]);
?>