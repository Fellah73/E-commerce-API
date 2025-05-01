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



    $user_id = intval($body["user_id"]);
    $order_id = intval($body["order_id"]);

    if (empty($user_id) || empty($order_id)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid data provided'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $stm = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stm->execute([$user_id]);
    $adminUser = $stm->fetch(PDO::FETCH_ASSOC);

    if ($adminUser["role"] != "admin" && $adminUser["role"] != "super_admin") {
        echo json_encode([
            'success' => false,
            'message' => 'You are not admin'
        ], JSON_PRETTY_PRINT);
        exit;
    }


    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);


    // no roder found
    if (empty($order)) {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    if ($order["status"] !== "processing") {
        echo json_encode([
            'success' => false,
            'message' => 'Order already processed'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // update the order status
    $stmt = $pdo->prepare("UPDATE orders SET status = 'shipped' WHERE id = ?");
    $stmt->execute([$order_id]);

    // update the shipping date
    $stmt = $pdo->prepare("UPDATE orders SET shipped_at = ? WHERE id = ?");
    $stmt->execute([date("Y-m-d H:i:s"), $order_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Order shipped successfully'
    ], JSON_PRETTY_PRINT);
    exit;

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
