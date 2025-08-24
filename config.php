<?php
// config.php : connexion PDO MySQL
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
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

// Fonctions utilitaires
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function logAction($userId, $actionType, $details = '', $targetId = null, $targetTable = null) {
    global $pdo;
    
    try {
        $sql = "INSERT INTO user_actions (user_id, action_type, action_details, target_id, target_table, ip_address, user_agent) 
                VALUES (:user_id, :action_type, :action_details, :target_id, :target_table, :ip_address, :user_agent)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':action_type' => $actionType,
            ':action_details' => $details,
            ':target_id' => $targetId,
            ':target_table' => $targetTable,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        error_log("Erreur journalisation: " . $e->getMessage());
    }
}