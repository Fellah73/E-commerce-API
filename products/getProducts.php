<?php
// Autoriser les requêtes venant de n'importe où (*), ou préciser l'origine exacte
header("Access-Control-Allow-Origin: *");

// Autoriser les méthodes HTTP
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Autoriser les en-têtes personnalisés
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Spécifier que la réponse est en JSON
header("Content-Type: application/json");

require_once "db.php";  // Importer la connexion


try {
    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

    if ($category_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = :category_id");
        $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $stmt = $pdo->query("SELECT * FROM products");
    }

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "products" => $products], JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
