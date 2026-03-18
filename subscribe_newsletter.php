<?php
header('Content-Type: application/json');

require_once '../config/database.php';

// Récupérer les données
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) && !isset($data['phone'])) {
    echo json_encode(['success' => false, 'error' => 'Email ou téléphone requis']);
    exit();
}

$email = isset($data['email']) ? filter_var($data['email'], FILTER_SANITIZE_EMAIL) : '';
$phone = isset($data['phone']) ? filter_var($data['phone'], FILTER_SANITIZE_STRING) : '';
$name = isset($data['name']) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : '';
$preferred = isset($data['preferred']) ? $data['preferred'] : 'email';

// Validation
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Email invalide']);
    exit();
}

if (!empty($phone)) {
    // Nettoyer le numéro de téléphone
    $phone = preg_replace('/[^0-9+]/', '', $phone);
}

$database = new Database();
$db = $database->getConnection();

// Créer la table si elle n'existe pas (avec téléphone)
$db->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(50),
    name VARCHAR(100),
    preferred_contact ENUM('email', 'whatsapp') DEFAULT 'email',
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('actif', 'désabonné') DEFAULT 'actif',
    unsubscribe_token VARCHAR(64)
)");

// Vérifier si l'email ou téléphone existe déjà
$query = "SELECT id, status FROM newsletter_subscribers WHERE ";
$params = [];

if (!empty($email) && !empty($phone)) {
    $query .= "email = ? OR phone = ?";
    $params = [$email, $phone];
} elseif (!empty($email)) {
    $query .= "email = ?";
    $params = [$email];
} elseif (!empty($phone)) {
    $query .= "phone = ?";
    $params = [$phone];
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    if ($existing['status'] == 'désabonné') {
        // Réactiver l'abonnement
        $token = bin2hex(random_bytes(32));
        $update = "UPDATE newsletter_subscribers SET status = 'actif', unsubscribe_token = ? WHERE id = ?";
        $stmt = $db->prepare($update);
        $stmt->execute([$token, $existing['id']]);
        echo json_encode(['success' => true, 'message' => 'Votre abonnement a été réactivé !']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Cet email ou numéro est déjà inscrit à notre newsletter.']);
    }
    exit();
}

// Ajouter le nouvel abonné
$token = bin2hex(random_bytes(32));
$insert = "INSERT INTO newsletter_subscribers (email, phone, name, preferred_contact, unsubscribe_token) 
           VALUES (?, ?, ?, ?, ?)";
$stmt = $db->prepare($insert);
$success = $stmt->execute([$email ?: null, $phone ?: null, $name, $preferred, $token]);

if ($success) {
    // Déterminer le message selon le contact préféré
    if ($preferred == 'whatsapp' && $phone) {
        $contact_msg = " WhatsApp (" . $phone . ")";
    } else {
        $contact_msg = " email (" . $email . ")";
    }
    
    echo json_encode(['success' => true, 'message' => 'Merci ! Vous êtes inscrit à notre newsletter via' . $contact_msg]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'inscription. Veuillez réessayer.']);
}
?>