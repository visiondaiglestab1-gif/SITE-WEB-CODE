<?php
header('Content-Type: application/json');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$current_version = $_GET['current'] ?? 0;

$latest = $db->query("SELECT * FROM app_versions ORDER BY version_code DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if($latest) {
    $needs_update = $latest['version_code'] > $current_version;
    
    echo json_encode([
        'success' => true,
        'latest_version' => $latest['version_name'],
        'latest_code' => $latest['version_code'],
        'needs_update' => $needs_update,
        'is_mandatory' => (bool)$latest['is_mandatory'],
        'download_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/uploads/apps/' . $latest['file_name'],
        'release_notes' => $latest['release_notes'],
        'file_size' => $latest['file_size']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Aucune version disponible'
    ]);
}
?>