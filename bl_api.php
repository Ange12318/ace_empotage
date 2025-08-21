<?php
// bl_api.php : mini API REST pour les BL
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

require __DIR__ . "/config.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Filtres optionnels ?banque=&client=&transitaire= (pour futur)
    $sql = "SELECT * FROM bl ORDER BY id DESC";
    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll());
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
    $relance_r1 = $input['relance_r1'] ?: null;
    $relance_r2 = $input['relance_r2'] ?: null;
    $relance_r3 = $input['relance_r3'] ?: null;
    $relance_r4 = $input['relance_r4'] ?: null;
    $date_alerte_banque = $input['date_alerte_banque'] ?: null;
    $statut = in_array(($input['statut'] ?? 'pending'), ['pending','completed']) ? $input['statut'] : 'pending';

    $sql = "INSERT INTO bl (banque, client, transitaire, produit, numero_das, poids, date_accord_banque, date_empotage,
            relance_r1, relance_r2, relance_r3, relance_r4, date_alerte_banque, statut)
            VALUES (:banque,:client,:transitaire,:produit,:numero_das,:poids,:date_accord_banque,:date_empotage,
                    :relance_r1,:relance_r2,:relance_r3,:relance_r4,:date_alerte_banque,:statut)";
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
        ':relance_r4' => $relance_r4,
        ':date_alerte_banque' => $date_alerte_banque,
        ':statut' => $statut
    ]);
    echo json_encode(["message" => "BL ajouté avec succès", "id" => $pdo->lastInsertId()]);
    exit;
}

if ($method === 'PUT') {
    // Mise à jour du statut
    parse_str(file_get_contents("php://input"), $input);
    $id = intval($input['id'] ?? 0);
    $statut = $input['statut'] ?? 'pending';
    if (!$id || !in_array($statut, ['pending','completed'])) {
        http_response_code(400);
        echo json_encode(["error" => "Paramètres invalides"]);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE bl SET statut = :s WHERE id = :id");
    $stmt->execute([':s' => $statut, ':id' => $id]);
    echo json_encode(["message" => "Statut mis à jour"]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Méthode non autorisée"]);
