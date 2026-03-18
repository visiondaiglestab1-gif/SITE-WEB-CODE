<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if(!isset($_GET['id'])) {
    header('Location: sermons.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'];

// Récupérer les informations pour supprimer les fichiers
$query = "SELECT audio_file, image_file FROM sermons WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$sermon = $stmt->fetch(PDO::FETCH_ASSOC);

if($sermon) {
    // Supprimer les fichiers
    if($sermon['audio_file'] && file_exists('../uploads/sermons/' . $sermon['audio_file'])) {
        unlink('../uploads/sermons/' . $sermon['audio_file']);
    }
    if($sermon['image_file'] && file_exists('../uploads/' . $sermon['image_file'])) {
        unlink('../uploads/' . $sermon['image_file']);
    }
    
    // Supprimer de la base de données
    $query = "DELETE FROM sermons WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
}

header('Location: sermons.php?deleted=1');
exit();
?>