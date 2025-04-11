<?php

header("Access-Control-Allow-Methods:PATCH, OPTIONS");
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
$quantity = intval($body["quantity"]);
$user_id = intval($body["user_id"]);


if (!isset($cart_id) || !isset($quantity) || !isset($user_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data provided'
    ], JSON_PRETTY_PRINT);
    exit;
}



if ($cart_id <= 0 || $quantity <= 0 || $user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "invalid Data"
    ], JSON_PRETTY_PRINT);
    exit;
}

try {

    $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    $existing = $stmt->fetch();

    // si le produit n'existe pas dans le panier
    if (!$existing) {

        echo json_encode([
            "success" => false,
            "message" => "no items found in the cart"
        ], JSON_PRETTY_PRINT);
        exit;

        // si le produit n'existe pas dans le panier donc l'ajouter
    } else {
        // get product price
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$existing["product_id"]]);
        $product = $stmt->fetch();
        $price = $product["price"];

        // update quantity
        $newPrice = $price * $quantity;
        $updateStmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, price = ? WHERE id = ?");
        $updateStmt->execute([$quantity, $newPrice, $cart_id]);

        echo json_encode([
            "success" => true,
            "message" => "item quantity updated"
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
