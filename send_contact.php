<?php
// send_contact.php - Version avec envoi d'email via Gmail SMTP

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclure PHPMailer (à télécharger d'abord)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Récupérer les données du formulaire
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$subject = $_POST['subject'] ?? 'Message depuis le site web';
$message = $_POST['message'] ?? '';

// Validation simple
if(empty($name) || empty($email) || empty($message)) {
    die("Tous les champs obligatoires doivent être remplis");
}

// Configuration de l'email
$to_email = "votre-email@gmail.com"; // REMPLACEZ PAR VOTRE EMAIL
$to_name = "Administrateur VISION D'AIGLES";

// Créer une instance de PHPMailer
$mail = new PHPMailer(true);

try {
    // Configuration du serveur SMTP (avec Gmail)
    $mail->SMTPDebug = 0; // Mettre à 2 pour le débogage
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'votre-email@gmail.com'; // REMPLACEZ PAR VOTRE EMAIL
    $mail->Password   = 'votre-mot-de-passe-application'; // REMPLACEZ PAR VOTRE MOT DE PASSE D'APPLICATION
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Expéditeur et destinataires
    $mail->setFrom($email, $name);
    $mail->addAddress($to_email, $to_name);
    $mail->addReplyTo($email, $name);

    // Contenu
    $mail->isHTML(true);
    $mail->Subject = 'Contact depuis le site : ' . $subject;
    
    // Corps du message en HTML
    $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #1e3c72; color: white; padding: 10px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .field { margin-bottom: 10px; }
            .label { font-weight: bold; color: #1e3c72; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Nouveau message de contact</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>Nom :</span> " . htmlspecialchars($name) . "
                </div>
                <div class='field'>
                    <span class='label'>Email :</span> " . htmlspecialchars($email) . "
                </div>
                <div class='field'>
                    <span class='label'>Sujet :</span> " . htmlspecialchars($subject) . "
                </div>
                <div class='field'>
                    <span class='label'>Message :</span><br>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Version texte simple
    $mail->AltBody = "Nom: $name\nEmail: $email\nSujet: $subject\nMessage: $message";

    $mail->send();
    
    // Message de succès
    $success_message = "Merci $name ! Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.";
    
} catch (Exception $e) {
    $success_message = "Une erreur est survenue lors de l'envoi du message. Veuillez réessayer plus tard.";
    // Logguer l'erreur
    error_log("Erreur envoi email: " . $mail->ErrorInfo);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message envoyé - VISION D'AIGLES</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .message-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            font-size: 5rem;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        h2 {
            color: #1e3c72;
            margin-bottom: 20px;
        }
        p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #1e3c72;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2a5298;
        }
    </style>
</head>
<body>
    <div class="message-container">
        <div class="success-icon">✓</div>
        <h2>Message envoyé avec succès !</h2>
        <p><?php echo htmlspecialchars($success_message); ?></p>
        <a href="index.php" class="btn">Retour à l'accueil</a>
    </div>
</body>
</html>