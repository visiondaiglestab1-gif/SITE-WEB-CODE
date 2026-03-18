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

$message = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $preacher = $_POST['preacher'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    $bible_verse = $_POST['bible_verse'];
    $category = $_POST['category'];
    
    // Gestion du fichier audio
    $audio_file = '';
    if(isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] == 0) {
        $upload_dir = '../uploads/sermons/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $filename = $_FILES['audio_file']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $new_filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $title) . '.' . $ext;
        
        if(move_uploaded_file($_FILES['audio_file']['tmp_name'], $upload_dir . $new_filename)) {
            $audio_file = $new_filename;
        }
    }
    
    // Gestion de l'image
    $image_file = '';
    if(isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $filename = $_FILES['image_file']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $new_filename = uniqid() . '_image.' . $ext;
        
        if(move_uploaded_file($_FILES['image_file']['tmp_name'], '../uploads/' . $new_filename)) {
            $image_file = $new_filename;
        }
    }
    
    $query = "INSERT INTO sermons (title, preacher, date, description, bible_verse, category, audio_file, image_file) 
              VALUES (:title, :preacher, :date, :description, :bible_verse, :category, :audio_file, :image_file)";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':title' => $title,
        ':preacher' => $preacher,
        ':date' => $date,
        ':description' => $description,
        ':bible_verse' => $bible_verse,
        ':category' => $category,
        ':audio_file' => $audio_file,
        ':image_file' => $image_file
    ]);
    
    $message = "Prédication ajoutée avec succès !";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ajouter une prédication</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { display: flex; }
        .sidebar { width: 280px; background: linear-gradient(180deg, #1e3c72 0%, #0a1a2f 100%); color: white; padding: 20px 0; position: fixed; height: 100vh; }
        .main-content { margin-left: 280px; padding: 30px; flex: 1; }
        .form-container { background: white; padding: 30px; border-radius: 10px; max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .btn-save { background: #28a745; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">[Menu sidebar identique]</div>
        
        <div class="main-content">
            <h1><i class="fas fa-plus-circle"></i> Ajouter une prédication</h1>
            
            <?php if($message): ?><div style="background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px;"><?php echo $message; ?></div><?php endif; ?>
            
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Titre</label>
                        <input type="text" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Prédicateur</label>
                        <input type="text" name="preacher" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Référence biblique</label>
                        <input type="text" name="bible_verse" placeholder="Ex: Ésaïe 40:31">
                    </div>
                    
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select name="category">
                            <option value="Dimanche">Dimanche</option>
                            <option value="Mercredi">Mercredi</option>
                            <option value="Conférence">Conférence</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Fichier audio (MP3)</label>
                        <input type="file" name="audio_file" accept=".mp3,.wav,.ogg" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Image d'illustration</label>
                        <input type="file" name="image_file" accept="image/*">
                    </div>
                    
                    <button type="submit" class="btn-save">Ajouter la prédication</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>