<?php

// register.php - User registration endpoint


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods:GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin: http://127.0.0.1:5501"); // Pas "*"


require_once "db.php";  // Importer la connexion


$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$password = isset($_GET['password']) ? trim($_GET['password']) : '';

// Validate inputs
if (empty($email || $password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields.'
    ], JSON_PRETTY_PRINT);
    exit;
}
try {

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $userExists = (bool)$stmt->fetchColumn();

    if (!$userExists) {
        // Email already registered
        echo json_encode([
            'success' => false,
            'message' => 'Email address does not exist, please register first or try another one'
        ], JSON_PRETTY_PRINT);
        exit;
    } else {

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // the same password 
        if ($user && password_verify($password, $user['password'])) {   
            echo json_encode([
                'success' => true,
                'message' => 'Login successful!',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email']
                ]
            ], JSON_PRETTY_PRINT);
            exit;
        } else {
            // Invalid password
            echo json_encode([
                'success' => false,
                'message' => 'Invalid password. Please try again.'
            ], JSON_PRETTY_PRINT);
            exit;
        }
    }
} catch (PDOException $e) {
    // registration failed by the server
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
    exit;
}
