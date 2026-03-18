<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if(!isset($_GET['id'])) {
    header('Location: events.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'];

// Récupérer l'image pour la supprimer
$query = "SELECT image_file FROM events WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if($event) {
    // Supprimer l'image si elle existe
    if($event['image_file'] && file_exists('../uploads/' . $event['image_file'])) {
        unlink('../uploads/' . $event['image_file']);
    }
    
    // Supprimer l'événement
    $query = "DELETE FROM events WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
}

header('Location: events.php?deleted=1');
exit();
?>