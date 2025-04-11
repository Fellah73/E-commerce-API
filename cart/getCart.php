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


    $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $existing = $stmt->fetch();

    // si le user n'a pas de panier
    if (!$existing) {
        echo json_encode([
            "success" => false,
            "message" => "user does not have a cart",
            "user" => $user["name"]
        ], JSON_PRETTY_PRINT);
        exit;

        // si le user a un panier le retourner
    } else {
        $selctItems = $pdo->prepare("SELECT c.id, p.id AS product_id, p.name, p.image,p.price as product_price,cat.categ ,c.price as subtotal, c.quantity FROM cart_items c JOIN products p ON c.product_id = p.id JOIN categories cat ON p.category_id = cat.id JOIN users u ON c.user_id = u.id WHERE c.user_id = ?;");
        $selctItems->execute([$user_id]);
        $selctItems->setFetchMode(PDO::FETCH_ASSOC);
        $cartItems = $selctItems->fetchAll();


        if (!$cartItems) {
            echo json_encode([
                "success" => true,
                "message" => "iternal server error",
            ], JSON_PRETTY_PRINT);
            exit;
        }

        echo json_encode([
            "success" => true,
            "message" => "cart items",
            "cart_items" => $cartItems,
            "user" => $user["name"]
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
