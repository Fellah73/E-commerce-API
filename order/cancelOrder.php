<?php

header("Access-Control-Allow-Methods:DELETE, OPTIONS");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Origin: *");
require_once "db.php";


// Sécurisation des données
$order_id = intval($_GET['order_id']);

if (empty($order_id) || $order_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid orderId provided'
    ], JSON_PRETTY_PRINT);
    exit;
}

try {

    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $order_items = $stmt->fetchAll();



    // update order status
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$order_id]);

    // update the stock than delete the order items
    foreach ($order_items as $order_item) {
        // update du stock
        $stmt = $pdo->prepare("UPDATE products SET qte = qte + :quantity WHERE id = :product_id");
        $stmt->execute([
            ':quantity' => $order_item['quantity'],
            ':product_id' => $order_item['product_id']
        ]);

        // delete the order item
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE id = ?");
        $stmt->execute([$order_item['id']]);
    }

    // delete the order
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Order cancelled'
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
