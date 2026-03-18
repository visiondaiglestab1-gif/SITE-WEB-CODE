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

$pastors = $db->query("SELECT * FROM pastors ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pasteurs</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { display: flex; }
        .sidebar { width: 280px; background: linear-gradient(180deg, #1e3c72 0%, #0a1a2f 100%); color: white; padding: 20px 0; position: fixed; height: 100vh; }
        .main-content { margin-left: 280px; padding: 30px; flex: 1; }
        .pastors-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .pastor-card { background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .pastor-card img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; }
        .btn-add { background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        .btn-edit { background: #ffc107; color: #333; padding: 5px 15px; text-decoration: none; border-radius: 3px; }
        .btn-delete { background: #dc3545; color: white; padding: 5px 15px; text-decoration: none; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">[Menu sidebar identique]</div>
        
        <div class="main-content">
            <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
                <h1><i class="fas fa-users"></i> Gestion des pasteurs</h1>
                <a href="add_pastor.php" class="btn-add"><i class="fas fa-plus"></i> Ajouter un pasteur</a>
            </div>
            
            <div class="pastors-grid">
                <?php foreach($pastors as $p): ?>
                <div class="pastor-card">
                    <img src="<?php echo $p['image_file'] ? '../uploads/'.$p['image_file'] : '../images/pastor-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                    <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                    <p style="color: #ffd700;"><?php echo htmlspecialchars($p['title'] ?? 'Pasteur'); ?></p>
                    <div style="margin-top: 15px;">
                        <a href="edit_pastor.php?id=<?php echo $p['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i></a>
                        <a href="delete_pastor.php?id=<?php echo $p['id']; ?>" class="btn-delete" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>