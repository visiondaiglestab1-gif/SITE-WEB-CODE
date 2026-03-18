<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$type = $_GET['type'] ?? 'email';

if ($type == 'email') {
    $query = "SELECT email, name FROM newsletter_subscribers WHERE status = 'actif' AND email IS NOT NULL";
    $filename = "abonnes_email_" . date('Y-m-d') . ".csv";
} else {
    $query = "SELECT phone, name FROM newsletter_subscribers WHERE status = 'actif' AND phone IS NOT NULL";
    $filename = "abonnes_whatsapp_" . date('Y-m-d') . ".csv";
}

$subscribers = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

if ($type == 'email') {
    fputcsv($output, ['Nom', 'Email']);
    foreach ($subscribers as $s) {
        fputcsv($output, [$s['name'], $s['email']]);
    }
} else {
    fputcsv($output, ['Nom', 'Téléphone']);
    foreach ($subscribers as $s) {
        // Formater le numéro pour WhatsApp
        $phone = preg_replace('/[^0-9]/', '', $s['phone']);
        fputcsv($output, [$s['name'], $phone]);
    }
}

fclose($output);
exit();
?>