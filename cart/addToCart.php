<?php

header("Access-Control-Allow-Methods:POST, OPTIONS");
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
$user_id = intval($body["user_id"]);
$product_id = intval($body["product_id"]);
$quantity = intval($body["quantity"]);
$price = intval($body["price"]);

if (empty($user_id) || empty($product_id) || empty($quantity) || empty($price)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data provided'
    ], JSON_PRETTY_PRINT);
    exit;
}



if ($user_id <= 0 || $product_id <= 0 || $quantity <= 0 || $price <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Données invalides."
    ], JSON_PRETTY_PRINT);
    exit;
}

try {

    $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch();

    // si le produit existe deja dans le panier
    if ($existing) {
        // Mise à jour de la quantité
        $newQuantity = $existing["quantity"] + $quantity;
        $newPrice = $newQuantity * $price;
        $updateStmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, price = ? WHERE id = ?");
        $updateStmt->execute([$newQuantity, $newPrice, $existing["id"]]);

        echo json_encode([
            "success" => true,
            "message" => "Quantité mise à jour"
        ], JSON_PRETTY_PRINT);
        exit;

        // si le produit n'existe pas dans le panier donc l'ajouter
    } else {
        // Insertion d'un nouvel item
        $insertStmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $insertStmt->execute([$user_id, $product_id, $quantity, $price*$quantity]);

        echo json_encode([
            "success" => true,
            "message" => "Item ajouté au panier"
        ], JSON_PRETTY_PRINT);
        exit;
    }
} catch (PDOException $e) {
    // probleme depuis le serveur
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
    exit;
}
