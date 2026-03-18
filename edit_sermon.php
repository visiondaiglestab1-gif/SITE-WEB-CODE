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

// Vérifier si l'ID est fourni
if(!isset($_GET['id'])) {
    header('Location: sermons.php');
    exit();
}

$id = $_GET['id'];

// Récupérer les informations de la prédication
$query = "SELECT * FROM sermons WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$sermon = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$sermon) {
    header('Location: sermons.php');
    exit();
}

// Traitement du formulaire de modification
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $preacher = $_POST['preacher'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    $bible_verse = $_POST['bible_verse'];
    $category = $_POST['category'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Gestion du nouveau fichier audio
    $audio_file = $sermon['audio_file'];
    if(isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] == 0) {
        $allowed = ['mp3', 'wav', 'ogg', 'm4a'];
        $filename = $_FILES['audio_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            // Supprimer l'ancien fichier
            if($sermon['audio_file'] && file_exists('../uploads/sermons/' . $sermon['audio_file'])) {
                unlink('../uploads/sermons/' . $sermon['audio_file']);
            }
            
            $new_filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $title) . '.' . $ext;
            $upload_path = '../uploads/sermons/' . $new_filename;
            
            if(move_uploaded_file($_FILES['audio_file']['tmp_name'], $upload_path)) {
                $audio_file = $new_filename;
            }
        } else {
            $error = 'Format de fichier non autorisé. Utilisez MP3, WAV, OGG ou M4A.';
        }
    }
    
    // Gestion de la nouvelle image
    $image_file = $sermon['image_file'];
    if(isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            // Supprimer l'ancienne image
            if($sermon['image_file'] && file_exists('../uploads/' . $sermon['image_file'])) {
                unlink('../uploads/' . $sermon['image_file']);
            }
            
            $new_filename = uniqid() . '_image.' . $ext;
            $upload_path = '../uploads/' . $new_filename;
            
            if(move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_path)) {
                $image_file = $new_filename;
            }
        }
    }
    
    if(empty($error)) {
        $query = "UPDATE sermons SET 
                  title = :title, 
                  preacher = :preacher, 
                  date = :date, 
                  description = :description, 
                  bible_verse = :bible_verse, 
                  category = :category, 
                  featured = :featured, 
                  audio_file = :audio_file, 
                  image_file = :image_file 
                  WHERE id = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':preacher', $preacher);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':bible_verse', $bible_verse);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':featured', $featured);
        $stmt->bindParam(':audio_file', $audio_file);
        $stmt->bindParam(':image_file', $image_file);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            $message = 'Prédication modifiée avec succès !';
            // Rafraîchir les données
            $stmt = $db->prepare("SELECT * FROM sermons WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $sermon = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = 'Erreur lors de la modification.';
        }
    }
}

// Récupérer la liste des prédicateurs
$query = "SELECT DISTINCT preacher FROM sermons UNION SELECT name FROM pastors";
$preachers = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une prédication</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Styles identiques à add_sermon.php */
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
        .checkbox-group { display: flex; align-items: center; }
        .checkbox-group input { width: auto; margin-right: 10px; }
        .btn-save { background: #4CAF50; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-delete { background: #f44336; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; }
        .message { padding: 10px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .current-file { margin-top: 5px; padding: 5px; background: #f0f0f0; border-radius: 3px; }
        .button-group { display: flex; gap: 10px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h3>VISION D'AIGLES</h3>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a></li>
                <li><a href="sermons.php" class="active"><i class="fas fa-microphone"></i> Prédications</a></li>
                <li><a href="add_sermon.php"><i class="fas fa-plus"></i> Ajouter prédication</a></li>
                <li><a href="events.php"><i class="fas fa-calendar"></i> Événements</a></li>
                <li><a href="pastors.php"><i class="fas fa-users"></i> Pasteurs</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Paramètres</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out"></i> Déconnexion</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h2>Modifier la prédication</h2>
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
                        <label>Titre de la prédication *</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($sermon['title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Prédicateur *</label>
                        <select name="preacher" required>
                            <option value="">Sélectionner un prédicateur</option>
                            <?php foreach($preachers as $p): 
                                $preacher_name = $p['preacher'] ?? $p['name'];
                            ?>
                                <option value="<?php echo htmlspecialchars($preacher_name); ?>" 
                                    <?php echo ($sermon['preacher'] == $preacher_name) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($preacher_name); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="autre">Autre (à préciser)</option>
                        </select>
                        <input type="text" name="other_preacher" placeholder="Nom du prédicateur" 
                               style="margin-top: 10px; display: none;" id="other_preacher">
                    </div>
                    
                    <div class="form-group">
                        <label>Date de la prédication *</label>
                        <input type="date" name="date" value="<?php echo $sermon['date']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description"><?php echo htmlspecialchars($sermon['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Référence biblique</label>
                        <input type="text" name="bible_verse" value="<?php echo htmlspecialchars($sermon['bible_verse']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select name="category">
                            <option value="Dimanche" <?php echo ($sermon['category'] == 'Dimanche') ? 'selected' : ''; ?>>Culte du Dimanche</option>
                            <option value="Mercredi" <?php echo ($sermon['category'] == 'Mercredi') ? 'selected' : ''; ?>>Étude biblique</option>
                            <option value="Conférence" <?php echo ($sermon['category'] == 'Conférence') ? 'selected' : ''; ?>>Conférence</option>
                            <option value="Jeûne" <?php echo ($sermon['category'] == 'Jeûne') ? 'selected' : ''; ?>>Jeûne et prière</option>
                            <option value="Spécial" <?php echo ($sermon['category'] == 'Spécial') ? 'selected' : ''; ?>>Spécial</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Fichier audio (MP3, WAV, OGG, M4A)</label>
                        <?php if($sermon['audio_file']): ?>
                            <div class="current-file">
                                Fichier actuel : <?php echo $sermon['audio_file']; ?>
                                <audio controls style="width: 100%; margin-top: 10px;">
                                    <source src="../uploads/sermons/<?php echo $sermon['audio_file']; ?>" type="audio/mpeg">
                                </audio>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="audio_file" accept="audio/*">
                        <small>Laissez vide pour conserver le fichier actuel</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Image d'illustration</label>
                        <?php if($sermon['image_file']): ?>
                            <div class="current-file">
                                Image actuelle :<br>
                                <img src="../uploads/<?php echo $sermon['image_file']; ?>" style="max-width: 200px; margin-top: 10px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image_file" accept="image/*">
                        <small>Laissez vide pour conserver l'image actuelle</small>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" name="featured" id="featured" <?php echo $sermon['featured'] ? 'checked' : ''; ?>>
                        <label for="featured">Mettre en avant sur la page d'accueil</label>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" class="btn-save">Enregistrer les modifications</button>
                        <a href="delete_sermon.php?id=<?php echo $id; ?>" class="btn-delete" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette prédication ?')">
                            Supprimer
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Afficher le champ "Autre prédicateur" si sélectionné
        const preacherSelect = document.querySelector('select[name="preacher"]');
        const otherField = document.getElementById('other_preacher');
        
        preacherSelect.addEventListener('change', function() {
            if(this.value === 'autre') {
                otherField.style.display = 'block';
                otherField.required = true;
            } else {
                otherField.style.display = 'none';
                otherField.required = false;
            }
        });
        
        // Vérifier si la valeur actuelle est "autre"
        if(preacherSelect.value === 'autre') {
            otherField.style.display = 'block';
        }
    </script>
</body>
</html>