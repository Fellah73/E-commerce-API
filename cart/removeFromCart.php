<?php

header("Access-Control-Allow-Methods:DELETE, OPTIONS");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Origin: *");
require_once "db.php";


$body = json_decode(file_get_contents("php://input"), true);
if ($body === null) {
    // Gérer l'erreur JSON
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data or no data provided'
    ], JSON_PRETTY_PRINT);
    exit;
}
// Sécurisation des données
$cart_id = intval($body["cart_id"]);
$product_id = intval($body["product_id"]);

if (empty($cart_id) || empty($product_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data provided'
    ], JSON_PRETTY_PRINT);
    exit;
}



if ($cart_id <= 0 || $product_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Données invalides."
    ], JSON_PRETTY_PRINT);
    exit;
}

try {

    $stmt = $pdo->prepare("SELECT * FROM cart_items  WHERE id = ? AND product_id = ?");
    $stmt->execute([$cart_id, $product_id]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $existing = $stmt->fetch();

    // si le produit n'existe pas dans le panier
    if (!$existing) {
        echo json_encode([
            "success" => false,
            "message" => "Item n'existe pas dans le panier"
        ], JSON_PRETTY_PRINT);
        exit;
    }
    // si le produit nexiste  dans le panier donc le supprimer
    $updateStmt = $pdo->prepare("DELETE FROM cart_items  WHERE cart_items.product_id = ? AND cart_items.id = ? ");
    $updateStmt->execute([$product_id, $cart_id]);


    echo json_encode([
        "success" => true,
        "message" => "Item supprimé du panier"
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
