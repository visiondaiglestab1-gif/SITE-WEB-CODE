<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$unread_requests = $db->query("SELECT COUNT(*) FROM user_requests WHERE status = 'non lu'")->fetchColumn();

// Créer la table des abonnements
$db->exec("CREATE TABLE IF NOT EXISTS push_subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    endpoint TEXT NOT NULL,
    auth_key VARCHAR(255),
    p256dh_key VARCHAR(255),
    user_agent VARCHAR(255),
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$message = '';

// Envoyer une notification
if(isset($_POST['send_push'])) {
    $title = $_POST['title'];
    $body = $_POST['body'];
    $url = $_POST['url'];
    
    // Récupérer tous les abonnements
    $subscriptions = $db->query("SELECT * FROM push_subscriptions")->fetchAll(PDO::FETCH_ASSOC);
    
    // Ici vous intégrerez l'envoi réel avec web-push
    $message = "Notification envoyée à " . count($subscriptions) . " appareils !";
}

$count = $db->query("SELECT COUNT(*) FROM push_subscriptions")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Notifications Push - Administration</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { display: flex; }
        .sidebar { width: 280px; background: linear-gradient(180deg, #1e3c72 0%, #0a1a2f 100%); color: white; padding: 20px 0; position: fixed; height: 100vh; }
        .main-content { margin-left: 280px; padding: 30px; flex: 1; }
        .card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-primary { background: #1e3c72; color: white; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat { background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center; flex: 1; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #1e3c72; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header" style="padding: 0 20px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <h3 style="color: #ffd700;">VISION D'AIGLES</h3>
                <p>Espace administration</p>
            </div>
            <ul class="sidebar-menu" style="list-style: none; padding: 0;">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="stats.php"><i class="fas fa-chart-bar"></i> Statistiques</a></li>
                <li><a href="requests.php"><i class="fas fa-inbox"></i> Requêtes <?php if($unread_requests > 0): ?><span class="badge"><?php echo $unread_requests; ?></span><?php endif; ?></a></li>
                <li><a href="sermons.php"><i class="fas fa-microphone"></i> Prédications</a></li>
                <li><a href="add_sermon.php"><i class="fas fa-plus-circle"></i> Ajouter prédication</a></li>
                <li><a href="events.php"><i class="fas fa-calendar"></i> Événements</a></li>
                <li><a href="calendar.php"><i class="fas fa-calendar-alt"></i> Calendrier</a></li>
                <li><a href="pastors.php"><i class="fas fa-users"></i> Pasteurs</a></li>
                <li><a href="newsletter.php"><i class="fas fa-envelope-open-text"></i> Newsletter</a></li>
                <li><a href="gallery.php"><i class="fas fa-images"></i> Galerie</a></li>
                <li><a href="faq.php"><i class="fas fa-question-circle"></i> FAQ</a></li>
                <li><a href="bible.php"><i class="fas fa-bible"></i> Bible</a></li>
                <li><a href="push_notifications.php" class="active"><i class="fas fa-bell"></i> Notifications</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <h1><i class="fas fa-bell"></i> Notifications Push</h1>
            
            <?php if($message): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <div class="stats">
                <div class="stat">
                    <h3>Appareils abonnés</h3>
                    <div class="stat-number"><?php echo $count; ?></div>
                </div>
            </div>
            
            <div class="card">
                <h2>Envoyer une notification</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Titre</label>
                        <input type="text" name="title" required placeholder="Ex: Nouvelle prédication disponible">
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="body" rows="4" required placeholder="Contenu de la notification..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>URL de redirection (optionnel)</label>
                        <input type="url" name="url" placeholder="https://votresite.com/page">
                    </div>
                    <button type="submit" name="send_push" class="btn btn-primary">Envoyer la notification</button>
                </form>
            </div>
            
            <div class="card">
                <h2>Code à intégrer dans votre site</h2>
                <p>Ajoutez ce code dans votre page pour permettre aux visiteurs de s'abonner :</p>
                <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;">
&lt;button onclick="subscribeToPush()" class="btn"&gt;
    &lt;i class="fas fa-bell"&gt;&lt;/i&gt; Activer les notifications
&lt;/button&gt;

&lt;script&gt;
async function subscribeToPush() {
    if ('serviceWorker' in navigator) {
        const registration = await navigator.serviceWorker.register('/sw.js');
        // Logique d'abonnement...
    }
}
&lt;/script&gt;
                </pre>
            </div>
        </div>
    </div>
</body>
</html>