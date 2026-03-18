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

// Message de confirmation de suppression
$deleted_message = '';
if(isset($_GET['deleted'])) {
    $deleted_message = "Événement supprimé avec succès !";
}

$events = $db->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Événements</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
            margin-left: 280px;
            padding: 30px;
            flex: 1;
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
        
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .event-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .event-card h3 { color: #1e3c72; margin-bottom: 10px; }
        .event-date { color: #ffd700; font-weight: bold; margin-bottom: 5px; }
        
        .event-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #333;
            padding: 5px 15px;
            text-decoration: none;
            border-radius: 3px;
            display: inline-block;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 5px 15px;
            text-decoration: none;
            border-radius: 3px;
            display: inline-block;
        }
        
        .btn-edit:hover, .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-calendar"></i> Gestion des événements</h1>
                <a href="add_event.php" class="btn-add"><i class="fas fa-plus"></i> Nouvel événement</a>
            </div>
            
            <?php if($deleted_message): ?>
                <div class="message"><?php echo $deleted_message; ?></div>
            <?php endif; ?>
            
            <?php if(empty($events)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 20px;"></i>
                    <h3>Aucun événement</h3>
                    <p>Commencez par ajouter votre premier événement.</p>
                    <a href="add_event.php" class="btn-add" style="display: inline-block; margin-top: 20px;">Ajouter un événement</a>
                </div>
            <?php else: ?>
                <div class="events-grid">
                    <?php foreach($events as $e): ?>
                    <div class="event-card">
                        <div class="event-date">
                            <?php echo date('d/m/Y', strtotime($e['event_date'])); ?>
                            <?php if($e['event_time']): ?>
                                à <?php echo $e['event_time']; ?>
                            <?php endif; ?>
                        </div>
                        <h3><?php echo htmlspecialchars($e['title']); ?></h3>
                        <?php if($e['location']): ?>
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($e['location']); ?></p>
                        <?php endif; ?>
                        <p><?php echo htmlspecialchars(substr($e['description'] ?? '', 0, 100)); ?>...</p>
                        
                        <div class="event-actions">
                            <a href="edit_event.php?id=<?php echo $e['id']; ?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="delete_event.php?id=<?php echo $e['id']; ?>" class="btn-delete" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                                <i class="fas fa-trash"></i> Supprimer
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>