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
$db->exec("CREATE TABLE IF NOT EXISTS faq_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(50) DEFAULT 'question-circle',
    display_order INT DEFAULT 0
)");

$db->exec("CREATE TABLE IF NOT EXISTS faq_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    video_url VARCHAR(255),
    views INT DEFAULT 0,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES faq_categories(id)
)");

// Insérer des catégories par défaut si vide
if($db->query("SELECT COUNT(*) FROM faq_categories")->fetchColumn() == 0) {
    $db->exec("INSERT INTO faq_categories (name, icon, display_order) VALUES 
        ('Général', 'church', 1),
        ('Cultes', 'clock', 2),
        ('Dons', 'hand-holding-heart', 3),
        ('Jeunes', 'child', 4)
    ");
}

$message = '';

// Ajouter une question
if(isset($_POST['add_question'])) {
    $stmt = $db->prepare("INSERT INTO faq_questions (category_id, question, answer, video_url) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['category_id'], $_POST['question'], $_POST['answer'], $_POST['video_url']]);
    $message = "Question ajoutée avec succès !";
}

// Supprimer une question
if(isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM faq_questions WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: faq.php');
    exit();
}

$categories = $db->query("SELECT * FROM faq_categories ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);
$questions = $db->query("SELECT q.*, c.name as category_name FROM faq_questions q LEFT JOIN faq_categories c ON q.category_id = c.id ORDER BY q.views DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>FAQ - Administration</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { display: flex; }
        .sidebar { width: 280px; background: linear-gradient(180deg, #1e3c72 0%, #0a1a2f 100%); color: white; padding: 20px 0; position: fixed; height: 100vh; }
        .main-content { margin-left: 280px; padding: 30px; flex: 1; }
        .card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-success { background: #28a745; color: white; }
        .btn-primary { background: #1e3c72; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-edit { background: #ffc107; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3c72; color: white; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .category-badge {
            display: inline-block;
            padding: 3px 10px;
            background: #e9ecef;
            border-radius: 15px;
            font-size: 0.8rem;
        }
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
                <li><a href="faq.php" class="active"><i class="fas fa-question-circle"></i> FAQ</a></li>
                <li><a href="bible.php"><i class="fas fa-bible"></i> Bible</a></li>
                <li><a href="push_notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <h1><i class="fas fa-question-circle"></i> Gestion de la FAQ</h1>
            
            <?php if($message): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <h2>Ajouter une question</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Catégorie</label>
                        <select name="category_id">
                            <?php foreach($categories as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Question</label>
                        <input type="text" name="question" required>
                    </div>
                    <div class="form-group">
                        <label>Réponse</label>
                        <textarea name="answer" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>URL Vidéo (YouTube, optionnel)</label>
                        <input type="url" name="video_url" placeholder="https://youtube.com/watch?v=...">
                    </div>
                    <button type="submit" name="add_question" class="btn btn-success">Ajouter</button>
                </form>
            </div>
            
            <div class="card">
                <h2>Liste des questions (<?php echo count($questions); ?>)</h2>
                <table>
                    <tr>
                        <th>Question</th>
                        <th>Catégorie</th>
                        <th>Vues</th>
                        <th>Utile</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach($questions as $q): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(substr($q['question'], 0, 50)); ?>...</td>
                        <td><span class="category-badge"><?php echo $q['category_name']; ?></span></td>
                        <td><?php echo $q['views']; ?></td>
                        <td><?php echo $q['helpful_count']; ?></td>
                        <td>
                            <a href="edit_faq.php?id=<?php echo $q['id']; ?>" class="btn btn-edit btn-sm"><i class="fas fa-edit"></i></a>
                            <a href="?delete=<?php echo $q['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>