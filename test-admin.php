<?php
// test-admin.php à la racine
$admin_path = __DIR__ . '/admin/requests.php';

echo "<h1>Diagnostic</h1>";
echo "<p>Chemin testé : " . $admin_path . "</p>";

if(file_exists($admin_path)) {
    echo "<p style='color:green'>✓ Le fichier requests.php existe !</p>";
    echo "<p><a href='admin/requests.php'>Cliquez ici pour y accéder</a></p>";
} else {
    echo "<p style='color:red'>✗ Le fichier requests.php n'existe pas</p>";
    echo "<p>Créez-le avec le code ci-dessous :</p>";
}
?>