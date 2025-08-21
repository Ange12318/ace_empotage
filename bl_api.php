<?php
// bl_api.php : API REST pour la gestion des BL
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

require __DIR__ . "/config.php";

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
    $statut = in_array(($input['statut'] ?? 'pending'), ['pending', 'completed']) ? $input['statut'] : 'pending';

    $sql = "INSERT INTO bl (banque, client, transitaire, produit, numero_das, poids, date_accord_banque, statut)
            VALUES (:banque, :client, :transitaire, :produit, :numero_das, :poids, :date_accord_banque, :statut)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':banque' => $banque,
        ':client' => $client,
        ':transitaire' => $transitaire,
        ':produit' => $produit,
        ':numero_das' => $numero_das,
        ':poids' => $poids,
        ':date_accord_banque' => $date_accord_banque,
        ':statut' => $statut
    ]);
    echo json_encode(["message" => "BL ajouté avec succès", "id" => $pdo->lastInsertId()]);
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
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bl WHERE id = :id");
    $stmt->execute([':id' => $id]);
    if ($stmt->fetchColumn() == 0) {
        http_response_code(404);
        echo json_encode(["error" => "BL non trouvé"]);
        exit;
    }

    // Mise à jour du statut uniquement
    if (isset($input['statut']) && count($input) === 2) {
        $statut = in_array($input['statut'], ['pending', 'completed']) ? $input['statut'] : 'pending';
        $sql = "UPDATE bl SET statut = :statut WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id, ':statut' => $statut]);
        echo json_encode(["message" => "Statut mis à jour avec succès"]);
        exit;
    }

    // Mise à jour de la date d'empotage et calcul des relances
    if (isset($input['date_empotage'])) {
        $date_empotage = $input['date_empotage'] ?: null;
        $relances = [];
        if ($date_empotage) {
            try {
                $date = new DateTime($date_empotage);
                $relances = [
                    'relance_r1' => (clone $date)->modify('+22 days')->format('Y-m-d'),
                    'relance_r2' => (clone $date)->modify('+29 days')->format('Y-m-d'),
                    'relance_r3' => (clone $date)->modify('+36 days')->format('Y-m-d'),
                    'relance_r4' => (clone $date)->modify('+43 days')->format('Y-m-d'),
                    'date_alerte_banque' => (clone $date)->modify('+103 days')->format('Y-m-d')
                ];
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["error" => "Date d'empotage invalide"]);
                exit;
            }
        } else {
            $relances = [
                'relance_r1' => null,
                'relance_r2' => null,
                'relance_r3' => null,
                'relance_r4' => null,
                'date_alerte_banque' => null
            ];
        }

        $sql = "UPDATE bl SET 
                date_empotage = :date_empotage,
                relance_r1 = :relance_r1,
                relance_r2 = :relance_r2,
                relance_r3 = :relance_r3,
                relance_r4 = :relance_r4,
                date_alerte_banque = :date_alerte_banque
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':date_empotage' => $date_empotage,
            ':relance_r1' => $relances['relance_r1'],
            ':relance_r2' => $relances['relance_r2'],
            ':relance_r3' => $relances['relance_r3'],
            ':relance_r4' => $relances['relance_r4'],
            ':date_alerte_banque' => $relances['date_alerte_banque']
        ]);
        echo json_encode(["message" => "Date d'empotage et relances mises à jour avec succès"]);
        exit;
    }

    // Mise à jour complète d'un BL (sans date_empotage ni relances)
    $banque = trim($input['banque'] ?? '');
    $client = trim($input['client'] ?? '');
    $transitaire = trim($input['transitaire'] ?? '');
    $produit = trim($input['produit'] ?? '');
    $numero_das = trim($input['numero_das'] ?? '');
    $poids = floatval($input['poids'] ?? 0);
    $date_accord_banque = $input['date_accord_banque'] ?: null;
    $statut = in_array(($input['statut'] ?? 'pending'), ['pending', 'completed']) ? $input['statut'] : 'pending';

    $sql = "UPDATE bl SET 
            banque = :banque,
            client = :client,
            transitaire = :transitaire,
            produit = :produit,
            numero_das = :numero_das,
            poids = :poids,
            date_accord_banque = :date_accord_banque,
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
        ':statut' => $statut
    ]);
    echo json_encode(["message" => "BL mis à jour avec succès"]);
    exit;
}

if ($method === 'DELETE') {
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
    echo json_encode(["message" => "BL supprimé avec succès"]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Méthode non autorisée"]);
?>