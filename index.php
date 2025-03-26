<?php
header("Content-Type: application/json");

echo json_encode([
    "success" => true,
    "message" => "Welcome to Gadget Store API 🚀"
], JSON_PRETTY_PRINT);
?>
