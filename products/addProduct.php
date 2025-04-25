<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once "db.php";

try {

    $body = json_decode(file_get_contents("php://input"), true);
    if ($body === null) {
        // Gérer l'erreur JSON
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data or no data provided'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $name = $body['name'];
    $price =  intval($body['price']);
    $discount = intval($body['discount']);
    $quantity = intval($body['quantity']);
    $category_id = intval($body['category_id']);
    $brand = $body['brand'];
    $description =  $body['description'];
    $image = $body['image'];

    if (empty($name) || empty($price) || empty($discount) || empty($quantity) || empty($category_id) || empty($brand) || empty($description) || empty($image)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid data provided'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $price_discounted = $price - ($price * $discount / 100);

    $query = "INSERT INTO products (name, price, price_discounted, discount, qte, category_id, brand, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$name, $price, $price_discounted, $discount, $quantity, $category_id, $brand, $description, $image]);
    $result = $stmt->rowCount();


    if ($result > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Product added successfully"
        ], JSON_PRETTY_PRINT);
        exit;
    }

    echo json_encode([
        "success" => false,
        "message" => "Product not added"
    ], JSON_PRETTY_PRINT);
    exit;

    // Erreur de server
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
