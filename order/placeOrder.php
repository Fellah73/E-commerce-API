<?php

header("Access-Control-Allow-Methods:POST, OPTIONS");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Origin: *");
require_once "db.php";

// Secure the data
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
$full_name = $body["full_name"];
$email = $body["email"];
$phone = $body["phone"];
$country = $body["country"];
$street_address = $body["street_address"];
$home_address = $body["home_address"];
$city = $body["city"];
$state = $body["state"];
$zip_code = $body["zip_code"];
$payment_method = $body["payment_method"];
$total = intval($body["total"]);

if (
    empty($user_id) || empty($full_name) || empty($email) ||
    empty($country) || empty($street_address) || empty($city) ||
    empty($payment_method) || empty($total)
) {
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required for checkout'
    ], JSON_PRETTY_PRINT);
    exit;
}

if ($total <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid total'
    ], JSON_PRETTY_PRINT);
    exit;
}
// check email validity
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ], JSON_PRETTY_PRINT);
    exit;
}

try {

    // verifier si les qte des produits sont dispo dans le stock
    $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $cart_items = $stmt->fetchAll();

    if (count($cart_items) === 0) {
        echo json_encode([
            "success" => false,
            "message" => "Empty cart",
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Check stock availability
    $message = [];
    foreach ($cart_items as $cart_item) {
        $stmt = $pdo->prepare("SELECT name, qte FROM products WHERE id = ?");
        $stmt->execute([$cart_item["product_id"]]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product["qte"] < $cart_item["quantity"]) {
            $message[] = [
                "product_name" => $product["name"],
            ];
        }
    }

    if (count($message) > 0) {
        echo json_encode([
            "success" => false,
            "message" => "cart items not available now ,please try again later",
        ], JSON_PRETTY_PRINT);
        exit;
    }



    // insertion dans la table orders
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            user_id, full_name, email, phone, country, 
            street_address, home_address, city, state, zip_code, 
            payment_method, total
        ) VALUES (
            :user_id, :full_name, :email, :phone, :country, 
            :street_address, :home_address, :city, :state, :zip_code, 
            :payment_method, :total
        )
    ");

    // Exécuter la requête avec les données
    $stmt->execute([
        ':user_id' => $user_id,
        ':full_name' => $full_name,
        ':email' => $email,
        ':phone' => $phone,
        ':country' => $country,
        ':street_address' => $street_address,
        ':home_address' => $home_address,
        ':city' => $city,
        ':state' => $state,
        ':zip_code' => $zip_code,
        ':payment_method' => $payment_method,
        ':total' => $total
    ]);


    $order_id = $pdo->lastInsertId();

    if (!$order_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Error creating order'
        ], JSON_PRETTY_PRINT);
        exit;
    }


    // insertion des items dans la table order_items
    foreach ($cart_items as $cart_item) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity,subtotal) VALUES (:order_id, :product_id, :quantity, :subtotal)");
        $stmt->execute([
            ':order_id' => $order_id,
            ':product_id' => $cart_item['product_id'],
            ':quantity' => $cart_item['quantity'],
            ':subtotal' => $cart_item['price']
        ]);
    }

    // update des quantités dans la base de données
    foreach ($cart_items as $cart_item) {
        $stmt = $pdo->prepare("UPDATE products SET qte = qte - :quantity WHERE id = :product_id");
        $stmt->execute([
            ':quantity' => $cart_item['quantity'],
            ':product_id' => $cart_item['product_id']
        ]);
    }

    // vider le panier
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $stmt->execute([$user_id]);


    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id
    ], JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    // Handle server error
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
    exit;
}
