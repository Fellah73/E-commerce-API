<?php

header("Access-Control-Allow-Methods:GET, OPTIONS");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Origin: *");
require_once "db.php";


// Sécurisation des données
$user_id = intval($_GET['user_id']);

if (empty($user_id) || $user_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid userId provided'
    ], JSON_PRETTY_PRINT);
    exit;
}

try {

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $user = $stmt->fetch();

    // si le user n'existe pas
    if (!$user) {
        echo json_encode([
            "success" => false,
            "message" => "user does not exist",
        ], JSON_PRETTY_PRINT);
        exit;
    }


    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $userInfo = $stmt->fetch();

    // si le user n'existe pas
    if (!$userInfo) {
        echo json_encode([
            "success" => false,
            "message" => "no user found",
        ], JSON_PRETTY_PRINT);
        exit;
    }

    //retourne the role
    echo json_encode([
        "success" => true,
        "message" => "user role",
        "role" => $userInfo["role"],
        "name" => $userInfo["name"]
    ], JSON_PRETTY_PRINT);
    exit;

} catch (PDOException $e) {
    // probleme depuis le serveur
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
    exit;
}
