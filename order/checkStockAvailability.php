<?php

header("Access-Control-Allow-Methods:GET, OPTIONS");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Origin: *");
require_once "db.php";

// Secure the data
$user_id = intval($_GET["user_id"]);

if (empty($user_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data provided'
    ], JSON_PRETTY_PRINT);
    exit;
}

if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid user ID."
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    // Get the cart items
    $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC); // Corrected to fetchAll

    if (count($cartItems) === 0) {
        echo json_encode([
            "success" => false,
            "message" => "Empty cart",
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Check stock availability
    $message = [];
    foreach ($cartItems as $item) {
        $stmt = $pdo->prepare("SELECT name, qte FROM products WHERE id = ?");
        $stmt->execute([$item["product_id"]]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product["qte"] < $item["quantity"]) {
            $message[] = [
                "product_name" => $product["name"],
                "max_units" => $product["qte"],
            ];
        }
    }

    if (count($message) > 0) {
        echo json_encode([
            "success" => false,
            "products" => $message,
        ], JSON_PRETTY_PRINT);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Stock available",
    ], JSON_PRETTY_PRINT);
    exit;
} catch (PDOException $e) {
    // Handle server error
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
    exit;
}



