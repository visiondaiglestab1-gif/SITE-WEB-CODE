<?php
require_once '../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

if($id) {
    $database = new Database();
    $db = $database->getConnection();
    $stmt = $db->prepare("UPDATE sermons SET plays = plays + 1 WHERE id = ?");
    $stmt->execute([$id]);
}

echo json_encode(['success' => true]);
?>