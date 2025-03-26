<?php
$host = "localhost";
$dbname = "gadget_store";  // Remplace par ton nom de base
$username = "root";  // Par défaut sous WAMP
$password = "";	

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die(json_encode(["success" => false, "message" => "Database connection failed: " . $e->getMessage()]));
}
?>
