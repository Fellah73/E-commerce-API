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

    $stmt = $pdo->prepare("SELECT id,total,created_at,status FROM orders WHERE user_id = ? and status != 'cancelled'");
    $stmt->execute([$user_id]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $orders = $stmt->fetchAll();

    // si aucun order
    if (count($orders) === 0) {
        echo json_encode([
            "success" => true,
            "message" => "User does not have any orders",
            "user" => $user["name"],
            "length" => count($orders),
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // update order status
    foreach ($orders as $order) {
        $order_date = new DateTime($order["created_at"]);
        $current_date = new DateTime();
        if ($order_date->diff($current_date)->days <  7) {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'processing' WHERE id = ?");
            $stmt->execute([$order["id"]]);
        }else if($order_date->diff($current_date)->days <  14 && $order_date->diff($current_date)->days >=  7){
            $stmt = $pdo->prepare("UPDATE orders SET status = 'shipped' WHERE id = ?");
            $stmt->execute([$order["id"]]);
        }else if($order_date->diff($current_date)->days >=  14){
            $stmt = $pdo->prepare("UPDATE orders SET status = 'delivered' WHERE id = ?");
            $stmt->execute([$order["id"]]);
        }
    }





    $order_data = [];
    foreach ($orders as $order) {
        $order_details = $order;
        $stmt = $pdo->prepare("SELECT p.name,p.price_discounted,p.category_id,o.quantity,o.subtotal FROM order_items o,products p WHERE order_id = ? and o.product_id = p.id");
        $stmt->execute([$order["id"]]);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $order_items = $stmt->fetchAll();

        $order_details["items"] = $order_items;
        $order_data[] = $order_details;
    }

    echo json_encode([
        "success" => true,
        "message" => "User orders",
        "user" => $user["name"],
        "length" => count($orders),
        "orders" => $order_data,
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
