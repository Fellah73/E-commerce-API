<?php

header("Access-Control-Allow-Methods:GET, OPTIONS");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Origin: *");
require_once "db.php";


// Sécurisation des données
$limit = intval($_GET['limit']);

if (empty($limit) || $limit <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid limit provided'
    ], JSON_PRETTY_PRINT);
    exit;
}

try {

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE status ='shipped' or status = 'delivered'");
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $approved_orders = $stmt->fetch();

    if (!$approved_orders || count($approved_orders) === 0) {
        echo json_encode([
            "success" => false,
            "message" => "no orders found",
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $stmt = $pdo->prepare("SELECT p.*, SUM(oi.quantity) AS total_sold
             FROM orders o JOIN order_items oi ON o.id = oi.order_id
             JOIN products p ON oi.product_id = p.id
             WHERE o.status IN ('shipped', 'delivered')
             GROUP BY oi.product_id
             ORDER BY total_sold DESC
             LIMIT :limit;");

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $best_sellers = $stmt->fetchAll();

    if (!$best_sellers || count($best_sellers) === 0) {
        echo json_encode([
            "success" => false,
            "message" => "no sales found",
        ], JSON_PRETTY_PRINT);
        exit;
    }


    echo json_encode([
        "success" => true,
        "message" => "best sellers found",
        "sales" => $best_sellers,
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
