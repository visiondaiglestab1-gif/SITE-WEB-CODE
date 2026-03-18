<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Compter les requêtes non lues
$unread_requests = $db->query("SELECT COUNT(*) FROM user_requests WHERE status = 'non lu'")->fetchColumn();

$message = '';
$error = '';

// Vérifier si l'ID est fourni
if(!isset($_GET['id'])) {
    header('Location: events.php');
    exit();
}

$id = $_GET['id'];

// Récupérer les informations de l'événement
$query = "SELECT * FROM events WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$event) {
    header('Location: events.php');
    exit();
}

// Traitement du formulaire de modification
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = $_POST['location'];
    $category = $_POST['category'];
    
    // Validation
    $errors = [];
    if(empty($title)) $errors[] = "Le titre est obligatoire";
    if(empty($event_date)) $errors[] = "La date est obligatoire";
    
    // Gestion de l'image
    $image_file = $event['image_file'];
    if(isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            // Supprimer l'ancienne image
            if($event['image_file'] && file_exists('../uploads/' . $event['image_file'])) {
                unlink('../uploads/' . $event['image_file']);
            }
            
            $new_filename = uniqid() . '_event.' . $ext;
            $upload_path = '../uploads/' . $new_filename;
            
            if(move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_path)) {
                $image_file = $new_filename;
            }
        } else {
            $errors[] = "Format d'image non autorisé";
        }
    }
    
    if(empty($errors)) {
        try {
            $query = "UPDATE events SET 
                      title = :title, 
                      description = :description, 
                      event_date = :event_date, 
                      event_time = :event_time, 
                      location = :location, 
                      category = :category,
                      image_file = :image_file
                      WHERE id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':event_date' => $event_date,
                ':event_time' => $event_time,
                ':location' => $location,
                ':category' => $category,
                ':image_file' => $image_file,
                ':id' => $id
            ]);
            
            $message = "Événement modifié avec succès !";
            
            // Rafraîchir les données
            $stmt = $db->prepare("SELECT * FROM events WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            $error = "Erreur lors de la modification : " . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'événement - Administration</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        
        .admin-container { display: flex; }
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1e3c72 0%, #0a1a2f 100%);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 20px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .sidebar-header h3 { font-size: 1.3rem; color: #ffd700; }
        .sidebar-header p { font-size: 0.9rem; opacity: 0.8; }
        
        .sidebar-menu { list-style: none; padding: 0; }
        .sidebar-menu li { margin-bottom: 2px; }
        .sidebar-menu a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 20px;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            border-left-color: #ffd700;
        }
        .sidebar-menu a i { width: 25px; margin-right: 10px; color: #ffd700; }
        
        .badge {
            background: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            margin-left: auto;
        }
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }
        
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 { color: #1e3c72; }
        
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .form-group textarea { height: 150px; resize: vertical; }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .current-image {
            margin: 10px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .current-image img {
            max-width: 200px;
            border-radius: 5px;
            margin-top: 10px;
        }
        
        .btn-save {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-save:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }
        
        .btn-cancel:hover {
            background: #5a6268;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #28a745;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #dc3545;
        }
        
        .button-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>VISION D'AIGLES</h3>
                <p>Espace administration</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="stats.php"><i class="fas fa-chart-bar"></i> Statistiques</a></li>
                <li><a href="requests.php"><i class="fas fa-inbox"></i> Requêtes 
                    <?php if($unread_requests > 0): ?>
                        <span class="badge"><?php echo $unread_requests; ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="sermons.php"><i class="fas fa-microphone"></i> Prédications</a></li>
                <li><a href="add_sermon.php"><i class="fas fa-plus-circle"></i> Ajouter prédication</a></li>
                <li><a href="events.php" class="active"><i class="fas fa-calendar"></i> Événements</a></li>
                <li><a href="calendar.php"><i class="fas fa-calendar-alt"></i> Calendrier</a></li>
                <li><a href="pastors.php"><i class="fas fa-users"></i> Pasteurs</a></li>
                <li><a href="newsletter.php"><i class="fas fa-envelope-open-text"></i> Newsletter</a></li>
                <li><a href="gallery.php"><i class="fas fa-images"></i> Galerie</a></li>
                <li><a href="faq.php"><i class="fas fa-question-circle"></i> FAQ</a></li>
                <li><a href="bible.php"><i class="fas fa-bible"></i> Bible</a></li>
                <li><a href="app_manager.php"><i class="fas fa-mobile-alt"></i> Application</a></li>
                <li><a href="push_notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-edit"></i> Modifier l'événement</h1>
                <a href="events.php" class="btn-cancel"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>
            
            <div class="form-container">
                <?php if($message): ?>
                    <div class="message success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if($error): ?>
                    <div class="message error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Titre de l'événement *</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description"><?php echo htmlspecialchars($event['description']); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date *</label>
                            <input type="date" name="event_date" value="<?php echo $event['event_date']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Heure</label>
                            <input type="time" name="event_time" value="<?php echo $event['event_time']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Lieu</label>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($event['location']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Catégorie</label>
                            <select name="category">
                                <option value="Culte" <?php echo $event['category'] == 'Culte' ? 'selected' : ''; ?>>Culte</option>
                                <option value="Conférence" <?php echo $event['category'] == 'Conférence' ? 'selected' : ''; ?>>Conférence</option>
                                <option value="Jeûne" <?php echo $event['category'] == 'Jeûne' ? 'selected' : ''; ?>>Jeûne et prière</option>
                                <option value="Étude" <?php echo $event['category'] == 'Étude' ? 'selected' : ''; ?>>Étude biblique</option>
                                <option value="Spécial" <?php echo $event['category'] == 'Spécial' ? 'selected' : ''; ?>>Spécial</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Image d'illustration</label>
                        <?php if($event['image_file']): ?>
                            <div class="current-image">
                                Image actuelle :<br>
                                <img src="../uploads/<?php echo $event['image_file']; ?>" alt="Image actuelle">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image_file" accept="image/*">
                        <small>Laissez vide pour conserver l'image actuelle</small>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                        <a href="delete_event.php?id=<?php echo $event['id']; ?>" 
                           class="btn-cancel" 
                           style="background: #dc3545;"
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ? Cette action est irréversible.')">
                            <i class="fas fa-trash"></i> Supprimer l'événement
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>