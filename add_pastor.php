<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $title = $_POST['title'];
    $bio = $_POST['bio'];
    $email = $_POST['email'];
    $facebook = $_POST['facebook'];
    $twitter = $_POST['twitter'];
    $instagram = $_POST['instagram'];
    $display_order = $_POST['display_order'];
    
    // Gestion de l'image
    $image_file = '';
    if(isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $new_filename = uniqid() . '_pastor.' . $ext;
            $upload_path = '../uploads/' . $new_filename;
            
            if(move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_path)) {
                $image_file = $new_filename;
            }
        }
    }
    
    $query = "INSERT INTO pastors (name, title, bio, email, facebook, twitter, instagram, image_file, display_order) 
              VALUES (:name, :title, :bio, :email, :facebook, :twitter, :instagram, :image_file, :display_order)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':bio', $bio);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':facebook', $facebook);
    $stmt->bindParam(':twitter', $twitter);
    $stmt->bindParam(':instagram', $instagram);
    $stmt->bindParam(':image_file', $image_file);
    $stmt->bindParam(':display_order', $display_order);
    
    if($stmt->execute()) {
        $message = 'Pasteur ajouté avec succès !';
    } else {
        $error = 'Erreur lors de l\'ajout.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un pasteur</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #1e3c72; color: white; padding: 20px; }
        .main-content { flex: 1; background: #f5f5f5; padding: 20px; }
        .form-container { background: white; padding: 30px; border-radius: 5px; max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
        }
        .form-group textarea { height: 150px; }
        .btn-save { background: #4CAF50; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; }
        .message { padding: 10px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
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
                <li><a href="pastors.php" class="active"><i class="fas fa-users"></i> Pasteurs</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Paramètres</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out"></i> Déconnexion</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h2>Ajouter un pasteur</h2>
            </div>
            
            <div class="form-container">
                <?php if($message): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if($error): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nom complet *</label>
                        <input type="text" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Titre / Fonction</label>
                        <input type="text" name="title" placeholder="Ex: Pasteur Principal">
                    </div>
                    
                    <div class="form-group">
                        <label>Biographie</label>
                        <textarea name="bio"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email">
                    </div>
                    
                    <div class="form-group">
                        <label>Facebook (URL complète)</label>
                        <input type="url" name="facebook">
                    </div>
                    
                    <div class="form-group">
                        <label>Twitter (URL complète)</label>
                        <input type="url" name="twitter">
                    </div>
                    
                    <div class="form-group">
                        <label>Instagram (URL complète)</label>
                        <input type="url" name="instagram">
                    </div>
                    
                    <div class="form-group">
                        <label>Photo</label>
                        <input type="file" name="image_file" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label>Ordre d'affichage</label>
                        <input type="number" name="display_order" value="0">
                    </div>
                    
                    <button type="submit" class="btn-save">Ajouter le pasteur</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>