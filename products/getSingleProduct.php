<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once "db.php";

try {

    $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

    if ($product_id > 0) {

        $stmt = $pdo->prepare("SELECT * FROM products p,categories c WHERE p.category_id = c.id and p.id = :product_id ");
        $stmt->bindParam(":product_id", $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // produit trouvé
        if (!empty($products)) {
            echo json_encode([
                "success" => true,
                "product" => $products
            ], JSON_PRETTY_PRINT);

            // aucun produit trouvé
        } else {
            echo json_encode([
                "success" => false,
                "message" => "wrong product id"
            ], JSON_PRETTY_PRINT);
        }
    } else {
        // query incorrecte
        echo json_encode([
            "success" => false,
            "message" => "Wrong request"
        ], JSON_PRETTY_PRINT);
    }

    // Erreur de server
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}






