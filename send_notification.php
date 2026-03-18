<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer tous les abonnements
$subscriptions = $db->query("SELECT subscription FROM push_subscriptions")->fetchAll(PDO::FETCH_ASSOC);

// Envoyer la notification à tous
foreach($subscriptions as $sub) {
    $subscription = json_decode($sub['subscription'], true);
    
    // Envoyer via web-push (nécessite bibliothèque)
    // sendWebPush($subscription, $title, $body, $url);
}

header('Location: ../admin/push_notifications.php?success=1');
?>