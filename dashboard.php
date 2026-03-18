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

// Statistiques
$stats = [];

// Nombre de sermons
$query = "SELECT COUNT(*) as count FROM sermons";
$stats['sermons'] = $db->query($query)->fetch(PDO::FETCH_ASSOC)['count'];

// Nombre d'événements à venir
$query = "SELECT COUNT(*) as count FROM events WHERE event_date >= CURDATE()";
$stats['upcoming_events'] = $db->query($query)->fetch(PDO::FETCH_ASSOC)['count'];

// Nombre de pasteurs
$query = "SELECT COUNT(*) as count FROM pastors";
$stats['pastors'] = $db->query($query)->fetch(PDO::FETCH_ASSOC)['count'];

// Nombre total de requêtes
$query = "SELECT COUNT(*) as count FROM user_requests";
$stats['total_requests'] = $db->query($query)->fetch(PDO::FETCH_ASSOC)['count'];

// Nombre d'abonnés newsletter
$db->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (id INT PRIMARY KEY AUTO_INCREMENT, email VARCHAR(100) UNIQUE, name VARCHAR(100), phone VARCHAR(50), preferred_contact ENUM('email','whatsapp') DEFAULT 'email', subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, status ENUM('actif','désabonné') DEFAULT 'actif', unsubscribe_token VARCHAR(64))");
$query = "SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'actif'";
$stats['newsletter'] = $db->query($query)->fetch(PDO::FETCH_ASSOC)['count'];

// Derniers sermons
$query = "SELECT * FROM sermons ORDER BY created_at DESC LIMIT 5";
$recent_sermons = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Dernières requêtes
$query = "SELECT * FROM user_requests ORDER BY created_at DESC LIMIT 5";
$recent_requests = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administration VISION D'AIGLES</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        
        .admin-container { display: flex; min-height: 100vh; }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1e3c72 0%, #0a1a2f 100%);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 0 20px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .sidebar-header h3 {
            font-size: 1.3rem;
            margin-bottom: 5px;
            color: #ffd700;
        }
        
        .sidebar-header p {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 2px;
        }
        
        .sidebar-menu a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 20px;
            transition: all 0.3s;
            border-left: 4px solid transparent;
            font-size: 0.95rem;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            border-left-color: #ffd700;
        }
        
        .sidebar-menu a i {
            width: 25px;
            font-size: 1.1rem;
            margin-right: 10px;
            color: #ffd700;
        }
        
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #1e3c72;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .header h1 i { color: #ffd700; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #1e3c72;
        }
        
        .recent-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .recent-section h3 {
            color: #1e3c72;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ffd700;
        }
        
        .recent-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .recent-item:last-child { border-bottom: none; }
        
        .item-info h4 { margin-bottom: 5px; color: #333; }
        .item-info p { color: #666; font-size: 0.9rem; }
        .item-info i { color: #ffd700; width: 16px; margin-right: 5px; }
        
        .status-badge {
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-non-lu { background: #dc3545; color: white; }
        .status-lu { background: #ffc107; color: #333; }
        .status-traite { background: #28a745; color: white; }
        
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-tachometer-alt"></i> Tableau de bord</h1>
                <div>Bienvenue, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Administrateur'); ?></div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Requêtes non lues</h3>
                    <div class="stat-number"><?php echo $unread_requests; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Prédications</h3>
                    <div class="stat-number"><?php echo $stats['sermons']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Événements à venir</h3>
                    <div class="stat-number"><?php echo $stats['upcoming_events']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Pasteurs</h3>
                    <div class="stat-number"><?php echo $stats['pastors']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Abonnés newsletter</h3>
                    <div class="stat-number"><?php echo $stats['newsletter']; ?></div>
                </div>
            </div>
            
            <div class="recent-section">
                <h3><i class="fas fa-inbox"></i> Dernières requêtes</h3>
                <?php if(empty($recent_requests)): ?>
                    <p style="text-align: center; color: #999; padding: 20px;">Aucune requête pour le moment</p>
                <?php else: ?>
                    <?php foreach($recent_requests as $req): ?>
                    <div class="recent-item">
                        <div class="item-info">
                            <h4><?php echo htmlspecialchars($req['name']); ?></h4>
                            <p>
                                <i class="fas fa-tag"></i> <?php echo $req['request_type']; ?> • 
                                <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($req['created_at'])); ?>
                            </p>
                        </div>
                        <div>
                            <span class="status-badge status-<?php echo str_replace(' ', '-', $req['status']); ?>">
                                <?php echo $req['status']; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="recent-section">
                <h3><i class="fas fa-microphone"></i> Dernières prédications</h3>
                <?php if(empty($recent_sermons)): ?>
                    <p style="text-align: center; color: #999; padding: 20px;">Aucune prédication pour le moment</p>
                <?php else: ?>
                    <?php foreach($recent_sermons as $sermon): ?>
                    <div class="recent-item">
                        <div class="item-info">
                            <h4><?php echo htmlspecialchars($sermon['title']); ?></h4>
                            <p>
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($sermon['preacher']); ?> • 
                                <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($sermon['date'])); ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>