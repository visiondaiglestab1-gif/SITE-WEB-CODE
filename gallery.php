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

// Créer les tables
$db->exec("CREATE TABLE IF NOT EXISTS gallery_albums (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    cover_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS gallery_media (
    id INT PRIMARY KEY AUTO_INCREMENT,
    album_id INT NOT NULL,
    title VARCHAR(200),
    file_path VARCHAR(255) NOT NULL,
    file_type ENUM('image', 'video') DEFAULT 'image',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (album_id) REFERENCES gallery_albums(id) ON DELETE CASCADE
)");

// Créer le dossier uploads
$upload_dir = '../uploads/gallery/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$message = '';

// Créer un album
if(isset($_POST['create_album'])) {
    $title = $_POST['album_title'];
    $description = $_POST['album_description'];
    
    $stmt = $db->prepare("INSERT INTO gallery_albums (title, description) VALUES (?, ?)");
    $stmt->execute([$title, $description]);
    $message = "Album créé avec succès !";
}

// Uploader un média
if(isset($_POST['upload_media'])) {
    $album_id = $_POST['album_id'];
    $title = $_POST['media_title'];
    
    $filename = $_FILES['media_file']['name'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $new_filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $title) . '.' . $ext;
    
    if(move_uploaded_file($_FILES['media_file']['tmp_name'], $upload_dir . $new_filename)) {
        $type = in_array(strtolower($ext), ['mp4', 'webm', 'mov']) ? 'video' : 'image';
        
        $stmt = $db->prepare("INSERT INTO gallery_media (album_id, title, file_path, file_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$album_id, $title, $new_filename, $type]);
        $message = "Média uploadé avec succès !";
    }
}

// Supprimer un album
if(isset($_GET['delete_album'])) {
    $stmt = $db->prepare("DELETE FROM gallery_albums WHERE id = ?");
    $stmt->execute([$_GET['delete_album']]);
    header('Location: gallery.php');
    exit();
}

// Supprimer un média
if(isset($_GET['delete_media'])) {
    $stmt = $db->prepare("SELECT file_path FROM gallery_media WHERE id = ?");
    $stmt->execute([$_GET['delete_media']]);
    $file = $stmt->fetchColumn();
    
    if($file && file_exists($upload_dir . $file)) {
        unlink($upload_dir . $file);
    }
    
    $stmt = $db->prepare("DELETE FROM gallery_media WHERE id = ?");
    $stmt->execute([$_GET['delete_media']]);
    header('Location: gallery.php');
    exit();
}

$albums = $db->query("SELECT * FROM gallery_albums ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Galerie - Administration</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { display: flex; }
        .sidebar { width: 280px; background: linear-gradient(180deg, #1e3c72 0%, #0a1a2f 100%); color: white; padding: 20px 0; position: fixed; height: 100vh; }
        .main-content { margin-left: 280px; padding: 30px; flex: 1; }
        .card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .album-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .album-card { background: #f8f9fa; border-radius: 10px; overflow: hidden; }
        .album-header { background: #1e3c72; color: white; padding: 15px; }
        .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; padding: 15px; }
        .media-item { position: relative; border-radius: 5px; overflow: hidden; }
        .media-item img { width: 100%; height: 100px; object-fit: cover; }
        .media-item video { width: 100%; height: 100px; object-fit: cover; }
        .delete-media {
            position: absolute; top: 5px; right: 5px; background: rgba(220,53,69,0.8);
            color: white; width: 25px; height: 25px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none; opacity: 0; transition: opacity 0.3s;
        }
        .media-item:hover .delete-media { opacity: 1; }
        .btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-success { background: #28a745; color: white; }
        .btn-primary { background: #1e3c72; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .form-group { margin-bottom: 15px; }
        .form-group input, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
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
                <li><a href="gallery.php" class="active"><i class="fas fa-images"></i> Galerie</a></li>
                <li><a href="faq.php"><i class="fas fa-question-circle"></i> FAQ</a></li>
                <li><a href="bible.php"><i class="fas fa-bible"></i> Bible</a></li>
                <li><a href="push_notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <h1><i class="fas fa-images"></i> Gestion de la galerie</h1>
            
            <?php if($message): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <h2>Créer un album</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Titre de l'album</label>
                        <input type="text" name="album_title" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="album_description" rows="3"></textarea>
                    </div>
                    <button type="submit" name="create_album" class="btn btn-success">Créer l'album</button>
                </form>
            </div>
            
            <?php foreach($albums as $album): 
                $media = $db->prepare("SELECT * FROM gallery_media WHERE album_id = ?");
                $media->execute([$album['id']]);
                $items = $media->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2><?php echo htmlspecialchars($album['title']); ?></h2>
                    <div>
                        <a href="?delete_album=<?php echo $album['id']; ?>" class="btn btn-danger" onclick="return confirm('Supprimer cet album et toutes ses photos ?')">Supprimer l'album</a>
                    </div>
                </div>
                <p><?php echo htmlspecialchars($album['description']); ?></p>
                
                <form method="POST" enctype="multipart/form-data" style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <input type="hidden" name="album_id" value="<?php echo $album['id']; ?>">
                    <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px;">
                        <input type="text" name="media_title" placeholder="Titre du média" required>
                        <input type="file" name="media_file" accept="image/*,video/*" required>
                        <button type="submit" name="upload_media" class="btn btn-primary">Uploader</button>
                    </div>
                </form>
                
                <div class="media-grid">
                    <?php foreach($items as $item): ?>
                    <div class="media-item">
                        <?php if($item['file_type'] == 'image'): ?>
                            <img src="../uploads/gallery/<?php echo $item['file_path']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <?php else: ?>
                            <video src="../uploads/gallery/<?php echo $item['file_path']; ?>" style="width:100%; height:100px; object-fit:cover;"></video>
                        <?php endif; ?>
                        <a href="?delete_media=<?php echo $item['id']; ?>" class="delete-media" onclick="return confirm('Supprimer ce média ?')"><i class="fas fa-times"></i></a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>