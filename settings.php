<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 'super_admin') {
    header('Location: dashboard.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';

// Récupérer les paramètres actuels
$settings = [];
$query = "SELECT * FROM settings";
$stmt = $db->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Mise à jour des paramètres
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach($_POST as $key => $value) {
        if($key != 'submit') {
            $query = "UPDATE settings SET setting_value = :value WHERE setting_key = :key";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':value', $value);
            $stmt->bindParam(':key', $key);
            $stmt->execute();
        }
    }
    $message = 'Paramètres mis à jour avec succès !';
    
    // Rafraîchir les paramètres
    $query = "SELECT * FROM settings";
    $stmt = $db->query($query);
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres du site</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #1e3c72; color: white; padding: 20px; }
        .main-content { flex: 1; background: #f5f5f5; padding: 20px; }
        .form-container { background: white; padding: 30px; border-radius: 5px; max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
        }
        .form-group textarea { height: 100px; }
        .btn-save { background: #4CAF50; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; }
        .message { padding: 10px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 20px; }
        .section-title { font-size: 1.2rem; color: #1e3c72; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid #1e3c72; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h3>VISION D'AIGLES</h3>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a></li>
                <li><a href="sermons.php"><i class="fas fa-microphone"></i> Prédications</a></li>
                <li><a href="add_sermon.php"><i class="fas fa-plus"></i> Ajouter prédication</a></li>
                <li><a href="events.php"><i class="fas fa-calendar"></i> Événements</a></li>
                <li><a href="pastors.php"><i class="fas fa-users"></i> Pasteurs</a></li>
                <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> Paramètres</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out"></i> Déconnexion</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h2>Paramètres du site</h2>
            </div>
            
            <div class="form-container">
                <?php if($message): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="section-title">Informations générales</div>
                    
                    <div class="form-group">
                        <label>Titre du site</label>
                        <input type="text" name="site_title" value="<?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Description du site</label>
                        <textarea name="site_description"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Versets clés</label>
                        <input type="text" name="main_verse" value="<?php echo htmlspecialchars($settings['main_verse'] ?? ''); ?>">
                    </div>
                    
                    <div class="section-title">Coordonnées</div>
                    
                    <div class="form-group">
                        <label>Adresse</label>
                        <textarea name="address"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Horaires des cultes</label>
                        <textarea name="worship_times"><?php echo htmlspecialchars($settings['worship_times'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="section-title">Réseaux sociaux</div>
                    
                    <div class="form-group">
                        <label>Facebook (URL complète)</label>
                        <input type="url" name="facebook_url" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Twitter (URL complète)</label>
                        <input type="url" name="twitter_url" value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Instagram (URL complète)</label>
                        <input type="url" name="instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>YouTube (URL complète)</label>
                        <input type="url" name="youtube_url" value="<?php echo htmlspecialchars($settings['youtube_url'] ?? ''); ?>">
                    </div>
                    
                    <button type="submit" name="submit" class="btn-save">Enregistrer les paramètres</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>