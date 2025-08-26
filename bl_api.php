<?php
// bl_api.php : API REST pour la gestion des BL
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

require_once __DIR__ . "/config.php";

// Vérifier l'authentification
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(["error" => "Non authentifié"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "SELECT * FROM bl ORDER BY id DESC";
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true) ?? [];

    // Sécuriser/valider minimalement
    $banque = trim($input['banque'] ?? '');
    $client = trim($input['client'] ?? '');
    $transitaire = trim($input['transitaire'] ?? '');
    $produit = trim($input['produit'] ?? '');
    $numero_das = trim($input['numero_das'] ?? '');
    $poids = floatval($input['poids'] ?? 0);
    $date_accord_banque = $input['date_accord_banque'] ?: null;
    $date_empotage = $input['date_empotage'] ?: null;
    $statut = in_array(($input['statut'] ?? 'pending'), ['pending', 'completed']) ? $input['statut'] : 'pending';

    // Calcul des relances
    $relance_r1 = $relance_r2 = $relance_r3 = $date_alerte_banque = null;
    if ($date_empotage) {
        try {
            $date = new DateTime($date_empotage);
            $relance_r1 = (clone $date)->modify('+22 days')->format('Y-m-d');
            $relance_r2 = (clone $date)->modify('+30 days')->format('Y-m-d');
            $relance_r3 = (clone $date)->modify('+37 days')->format('Y-m-d');
            $date_alerte_banque = (clone $date)->modify('+44 days')->format('Y-m-d');
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => "Date d'empotage invalide"]);
            exit;
        }
    }

    $sql = "INSERT INTO bl 
            (banque, client, transitaire, produit, numero_das, poids, date_accord_banque, date_empotage, relance_r1, relance_r2, relance_r3, date_alerte_banque, statut)
            VALUES (:banque, :client, :transitaire, :produit, :numero_das, :poids, :date_accord_banque, :date_empotage, :relance_r1, :relance_r2, :relance_r3, :date_alerte_banque, :statut)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':banque' => $banque,
        ':client' => $client,
        ':transitaire' => $transitaire,
        ':produit' => $produit,
        ':numero_das' => $numero_das,
        ':poids' => $poids,
        ':date_accord_banque' => $date_accord_banque,
        ':date_empotage' => $date_empotage,
        ':relance_r1' => $relance_r1,
        ':relance_r2' => $relance_r2,
        ':relance_r3' => $relance_r3,
        ':date_alerte_banque' => $date_alerte_banque,
        ':statut' => $statut
    ]);
    
    $newId = $pdo->lastInsertId();
    logAction($_SESSION['user_id'], 'CREATE_BL', "Création BL #$newId", $newId, 'bl');
    
    echo json_encode(["message" => "BL ajouté avec succès", "id" => $newId]);
    exit;
}

if ($method === 'PUT') {
    $input = json_decode(file_get_contents("php://input"), true) ?? [];
    $id = intval($input['id'] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "ID requis"]);
        exit;
    }

    // Vérifier si le BL existe
    $stmt = $pdo->prepare("SELECT * FROM bl WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        http_response_code(404);
        echo json_encode(["error" => "BL non trouvé"]);
        exit;
    }

    // Si on essaye de changer la date d’empotage alors qu’elle existe déjà → bloqué
    if (isset($input['date_empotage']) && !empty($existing['date_empotage']) && $existing['date_empotage'] !== $input['date_empotage']) {
        http_response_code(403);
        echo json_encode(["error" => "La date d'empotage ne peut être modifiée que via l'action Modifier BL"]);
        exit;
    }

    // Mise à jour du statut uniquement
    if (isset($input['statut']) && count($input) === 2) {
        $statut = in_array($input['statut'], ['pending', 'completed']) ? $input['statut'] : 'pending';
        $sql = "UPDATE bl SET statut = :statut WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id, ':statut' => $statut]);
        
        logAction($_SESSION['user_id'], 'UPDATE_BL_STATUS', "Statut BL #$id changé à: $statut", $id, 'bl');
        
        echo json_encode(["message" => "Statut mis à jour avec succès"]);
        exit;
    }

    // Mise à jour complète (y compris première saisie date_empotage)
    $banque = trim($input['banque'] ?? $existing['banque']);
    $client = trim($input['client'] ?? $existing['client']);
    $transitaire = trim($input['transitaire'] ?? $existing['transitaire']);
    $produit = trim($input['produit'] ?? $existing['produit']);
    $numero_das = trim($input['numero_das'] ?? $existing['numero_das']);
    $poids = floatval($input['poids'] ?? $existing['poids']);
    $date_accord_banque = $input['date_accord_banque'] ?: $existing['date_accord_banque'];
    $date_empotage = $input['date_empotage'] ?: $existing['date_empotage'];
    $statut = in_array(($input['statut'] ?? $existing['statut']), ['pending', 'completed']) ? $input['statut'] : $existing['statut'];

    // Recalcul relances si date_empotage est fournie
    $relance_r1 = $relance_r2 = $relance_r3 = $date_alerte_banque = null;
    if ($date_empotage) {
        $date = new DateTime($date_empotage);
        $relance_r1 = (clone $date)->modify('+22 days')->format('Y-m-d');
        $relance_r2 = (clone $date)->modify('+30 days')->format('Y-m-d');
        $relance_r3 = (clone $date)->modify('+37 days')->format('Y-m-d');
        $date_alerte_banque = (clone $date)->modify('+44 days')->format('Y-m-d');
    }

    $sql = "UPDATE bl SET 
            banque = :banque,
            client = :client,
            transitaire = :transitaire,
            produit = :produit,
            numero_das = :numero_das,
            poids = :poids,
            date_accord_banque = :date_accord_banque,
            date_empotage = :date_empotage,
            relance_r1 = :relance_r1,
            relance_r2 = :relance_r2,
            relance_r3 = :relance_r3,
            date_alerte_banque = :date_alerte_banque,
            statut = :statut
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':banque' => $banque,
        ':client' => $client,
        ':transitaire' => $transitaire,
        ':produit' => $produit,
        ':numero_das' => $numero_das,
        ':poids' => $poids,
        ':date_accord_banque' => $date_accord_banque,
        ':date_empotage' => $date_empotage,
        ':relance_r1' => $relance_r1,
        ':relance_r2' => $relance_r2,
        ':relance_r3' => $relance_r3,
        ':date_alerte_banque' => $date_alerte_banque,
        ':statut' => $statut
    ]);
    
    logAction($_SESSION['user_id'], 'UPDATE_BL', "Mise à jour BL #$id", $id, 'bl');
    
    echo json_encode(["message" => "BL mis à jour avec succès"]);
    exit;
}

if ($method === 'DELETE') {
    // Vérifier les droits administrateur
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(["error" => "Droits administrateur requis pour la suppression"]);
        exit;
    }
    
    $input = json_decode(file_get_contents("php://input"), true) ?? [];
    $id = intval($input['id'] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "ID requis"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bl WHERE id = :id");
    $stmt->execute([':id' => $id]);
    if ($stmt->fetchColumn() == 0) {
        http_response_code(404);
        echo json_encode(["error" => "BL non trouvé"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM bl WHERE id = :id");
    $stmt->execute([':id' => $id]);
    
    logAction($_SESSION['user_id'], 'DELETE_BL', "Suppression BL #$id", $id, 'bl');
    
    echo json_encode(["message" => "BL supprimé avec succès"]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Méthode non autorisée"]);
