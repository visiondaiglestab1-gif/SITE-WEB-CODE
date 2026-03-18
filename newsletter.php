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

// Traitements (ajout, suppression, envoi)
$message = '';

if(isset($_POST['add_subscriber'])) {
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $name = $_POST['name'];
    $preferred = $_POST['preferred'];
    $token = bin2hex(random_bytes(32));
    
    try {
        $stmt = $db->prepare("INSERT INTO newsletter_subscribers (email, phone, name, preferred_contact, unsubscribe_token) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$email ?: null, $phone ?: null, $name, $preferred, $token]);
        $message = "Abonné ajouté avec succès !";
    } catch(PDOException $e) {
        $message = "Erreur : " . $e->getMessage();
    }
}

if(isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: newsletter.php');
    exit();
}

$subscribers = $db->query("SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Newsletter - Administration</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { display: flex; }
        .sidebar { width: 280px; background: linear-gradient(180deg, #1e3c72 0%, #0a1a2f 100%); color: white; padding: 20px 0; position: fixed; height: 100vh; }
        .main-content { margin-left: 280px; padding: 30px; flex: 1; }
        .card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-primary { background: #1e3c72; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3c72; color: white; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .badge-email { background: #17a2b8; color: white; padding: 3px 10px; border-radius: 15px; }
        .badge-whatsapp { background: #25D366; color: white; padding: 3px 10px; border-radius: 15px; }
        .badge-actif { background: #28a745; color: white; padding: 3px 10px; border-radius: 15px; }
        .whatsapp-link { color: #25D366; text-decoration: none; }
        .whatsapp-link:hover { text-decoration: underline; }
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
                <li><a href="newsletter.php" class="active"><i class="fas fa-envelope-open-text"></i> Newsletter</a></li>
                <li><a href="gallery.php"><i class="fas fa-images"></i> Galerie</a></li>
                <li><a href="faq.php"><i class="fas fa-question-circle"></i> FAQ</a></li>
                <li><a href="bible.php"><i class="fas fa-bible"></i> Bible</a></li>
                <li><a href="push_notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <h1><i class="fas fa-envelope-open-text"></i> Gestion de la Newsletter</h1>
            
            <?php if($message): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <!-- Formulaire d'ajout -->
            <div class="card">
                <h2>Ajouter un abonné manuellement</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Nom (optionnel)</label>
                        <input type="text" name="name">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email">
                    </div>
                    <div class="form-group">
                        <label>Téléphone WhatsApp</label>
                        <input type="text" name="phone" placeholder="+242 XX XXX XXXX">
                    </div>
                    <div class="form-group">
                        <label>Contact préféré</label>
                        <select name="preferred">
                            <option value="email">Email</option>
                            <option value="whatsapp">WhatsApp</option>
                        </select>
                    </div>
                    <button type="submit" name="add_subscriber" class="btn btn-success">Ajouter</button>
                </form>
            </div>
            
            <!-- Liste des abonnés -->
            <div class="card">
                <h2>Liste des abonnés (<?php echo count($subscribers); ?>)</h2>
                <table>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>WhatsApp</th>
                        <th>Contact préféré</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach($subscribers as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['name'] ?: '-'); ?></td>
                        <td><?php echo $s['email'] ?: '-'; ?></td>
                        <td>
                            <?php if($s['phone']): ?>
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $s['phone']); ?>" 
                                   target="_blank" class="whatsapp-link">
                                    <i class="fab fa-whatsapp"></i> <?php echo $s['phone']; ?>
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($s['preferred_contact'] == 'email'): ?>
                                <span class="badge-email"><i class="fas fa-envelope"></i> Email</span>
                            <?php else: ?>
                                <span class="badge-whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($s['subscribed_at'])); ?></td>
                        <td>
                            <a href="?delete=<?php echo $s['id']; ?>" class="btn btn-danger btn-sm" 
                               onclick="return confirm('Supprimer cet abonné ?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            
            <!-- Export -->
            <div class="card">
                <h2>Exporter les contacts</h2>
                <p>Téléchargez la liste des abonnés pour vos campagnes WhatsApp ou Email.</p>
                <a href="export_newsletter.php?type=email" class="btn btn-primary">
                    <i class="fas fa-download"></i> Exporter les emails
                </a>
                <a href="export_newsletter.php?type=whatsapp" class="btn btn-primary" style="background: #25D366;">
                    <i class="fab fa-whatsapp"></i> Exporter les numéros WhatsApp
                </a>
            </div>
        </div>
    </div>
</body>
</html>