<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PATCH, OPTIONS");
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



    $product_id = intval($body["product_id"]);
    $quantity = intval($body["quantity"]);

    if (empty($product_id) || empty($quantity)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid data provided'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE products SET qte = qte + ? WHERE id = ?");
    $stmt->execute([$quantity, $product_id]);
    $result = $stmt->rowCount();

    if ($result > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Stock updated successfully'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    echo json_encode([
        'success' => false,
        'message' => 'Stock not updated'
    ], JSON_PRETTY_PRINT);
    exit;
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
