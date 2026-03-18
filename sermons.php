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

// Récupérer toutes les prédications
$query = "SELECT * FROM sermons ORDER BY date DESC";
$sermons = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des prédications</title>
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
        }
        
        .sidebar-header {
            padding: 0 20px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .sidebar-header h3 { font-size: 1.3rem; color: #ffd700; }
        
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
        
        .btn-add {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        
        .table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table th {
            background: #1e3c72;
            color: white;
            padding: 15px;
            text-align: left;
        }
        
        .table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .btn-edit { background: #ffc107; color: #333; padding: 5px 10px; text-decoration: none; border-radius: 3px; }
        .btn-delete { background: #dc3545; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h2>Gestion des prédications</h2>
                <a href="add_sermon.php" class="btn-add"><i class="fas fa-plus"></i> Nouvelle prédication</a>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Prédicateur</th>
                        <th>Date</th>
                        <th>Écoutes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($sermons as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['title']); ?></td>
                        <td><?php echo htmlspecialchars($s['preacher']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($s['date'])); ?></td>
                        <td><?php echo $s['plays'] ?? 0; ?></td>
                        <td>
                            <a href="edit_sermon.php?id=<?php echo $s['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i></a>
                            <a href="delete_sermon.php?id=<?php echo $s['id']; ?>" class="btn-delete" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>