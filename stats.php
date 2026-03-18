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

// Créer la table des visites si elle n'existe pas
$db->exec("CREATE TABLE IF NOT EXISTS visits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    visit_date DATE UNIQUE,
    visitor_count INT DEFAULT 0,
    page_views INT DEFAULT 0,
    sermons_played INT DEFAULT 0
)");

// Statistiques générales
$total_sermons = $db->query("SELECT COUNT(*) FROM sermons")->fetchColumn();
$total_events = $db->query("SELECT COUNT(*) FROM events")->fetchColumn();
$total_pastors = $db->query("SELECT COUNT(*) FROM pastors")->fetchColumn();
$total_requests = $db->query("SELECT COUNT(*) FROM user_requests")->fetchColumn();

// Top prédications
$top_sermons = $db->query("SELECT title, preacher, plays FROM sermons ORDER BY plays DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Visites des 7 derniers jours
$visits = $db->query("SELECT visit_date, visitor_count FROM visits ORDER BY visit_date DESC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Administration</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .header h1 { color: #1e3c72; }
        .header h1 i { color: #ffd700; margin-right: 10px; }
        
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
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #1e3c72;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .top-list {
            background: white;
            padding: 20px;
            border-radius: 15px;
        }
        
        .top-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .rank {
            width: 30px;
            height: 30px;
            background: #ffd700;
            color: #1e3c72;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-chart-bar"></i> Statistiques</h1>
                <div>Dernière mise à jour : <?php echo date('d/m/Y H:i'); ?></div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Prédications</h3>
                    <div class="stat-number"><?php echo $total_sermons; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Événements</h3>
                    <div class="stat-number"><?php echo $total_events; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Pasteurs</h3>
                    <div class="stat-number"><?php echo $total_pastors; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Requêtes</h3>
                    <div class="stat-number"><?php echo $total_requests; ?></div>
                </div>
            </div>
            
            <div class="charts-grid">
                <div class="chart-container">
                    <h3>Fréquentation (7 derniers jours)</h3>
                    <canvas id="visitsChart"></canvas>
                </div>
                
                <div class="top-list">
                    <h3>Top 5 prédications</h3>
                    <?php foreach($top_sermons as $index => $s): ?>
                    <div class="top-item">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span class="rank"><?php echo $index + 1; ?></span>
                            <div>
                                <strong><?php echo htmlspecialchars($s['title']); ?></strong>
                                <br><small><?php echo htmlspecialchars($s['preacher']); ?></small>
                            </div>
                        </div>
                        <span style="font-weight: bold;"><?php echo $s['plays']; ?> écoutes</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const ctx = document.getElementById('visitsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column(array_reverse($visits), 'visit_date')); ?>,
                datasets: [{
                    label: 'Visiteurs',
                    data: <?php echo json_encode(array_column(array_reverse($visits), 'visitor_count')); ?>,
                    borderColor: '#1e3c72',
                    backgroundColor: 'rgba(30,60,114,0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>