<?php
// admin/test-db.php
$host = 'sql103.infinityfree.com';
$dbname = 'if0_41372313_visiondaigles';
$username = 'if0_41372313';
$password = 'OnctionProph';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    echo "✅ Connexion BDD réussie !";
} catch(PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>