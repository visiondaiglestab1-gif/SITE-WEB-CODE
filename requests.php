<?php
// Activer l'affichage des erreurs (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Vérifier la connexion admin
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Connexion BDD
$host = 'sql103.infinityfree.com';
$dbname = 'if0_41372313_visiondaigles';
$username = 'if0_41372313';
$password = 'OnctionProph';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Traitement des actions
$message = '';

// Marquer comme lu
if(isset($_GET['mark_read'])) {
    $id = (int)$_GET['mark_read'];
    $stmt = $pdo->prepare("UPDATE user_requests SET status = 'lu' WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: requests.php');
    exit();
}

// Marquer comme non lu
if(isset($_GET['mark_unread'])) {
    $id = (int)$_GET['mark_unread'];
    $stmt = $pdo->prepare("UPDATE user_requests SET status = 'non lu' WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: requests.php');
    exit();
}

// Marquer comme traité
if(isset($_GET['mark_done'])) {
    $id = (int)$_GET['mark_done'];
    $stmt = $pdo->prepare("UPDATE user_requests SET status = 'traité' WHERE id = ?");
    $stmt->execute([$id]);
    $message = "Requête marquée comme traitée.";
}

// Supprimer une requête
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM user_requests WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: requests.php');
    exit();
}

// Voir le détail d'une requête
$show_detail = false;
$detail = null;
if(isset($_GET['view'])) {
    $id = (int)$_GET['view'];
    $stmt = $pdo->prepare("SELECT * FROM user_requests WHERE id = ?");
    $stmt->execute([$id]);
    $detail = $stmt->fetch(PDO::FETCH_ASSOC);
    if($detail) {
        $show_detail = true;
        // Marquer automatiquement comme lu quand on voit le détail
        if($detail['status'] == 'non lu') {
            $stmt = $pdo->prepare("UPDATE user_requests SET status = 'lu' WHERE id = ?");
            $stmt->execute([$id]);
            $detail['status'] = 'lu';
        }
    }
}

// Récupérer toutes les requêtes
$query = "SELECT * FROM user_requests ORDER BY 
          CASE status 
              WHEN 'non lu' THEN 1 
              WHEN 'lu' THEN 2 
              WHEN 'traité' THEN 3 
          END, created_at DESC";
$requests = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Compter par statut
$stats = [
    'non_lu' => 0,
    'lu' => 0,
    'traité' => 0,
    'total' => count($requests)
];

foreach($requests as $r) {
    if($r['status'] == 'non lu') $stats['non_lu']++;
    elseif($r['status'] == 'lu') $stats['lu']++;
    elseif($r['status'] == 'traité') $stats['traité']++;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des requêtes - VISION D'AIGLES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 25px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 0 20px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 20px;
        }
        
        .sidebar-header h3 {
            font-size: 1.3rem;
            margin-bottom: 5px;
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
            margin-bottom: 5px;
        }
        
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
        
        .sidebar-menu a i {
            width: 25px;
            font-size: 1.1rem;
            margin-right: 10px;
        }
        
        .badge {
            background: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            margin-left: auto;
        }
        
        /* Main content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }
        
        .header {
            background: white;
            padding: 25px 30px;
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
        
        .header h1 i {
            color: #ffd700;
        }
        
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
        
        .stat-card h4 {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .stat-card.non-lu .number { color: #dc3545; }
        .stat-card.lu .number { color: #ffc107; }
        .stat-card.traite .number { color: #28a745; }
        
        /* Vue liste */
        .requests-grid {
            display: grid;
            gap: 15px;
        }
        
        .request-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-left: 5px solid #ddd;
            transition: transform 0.2s;
        }
        
        .request-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .request-card.non-lu { border-left-color: #dc3545; }
        .request-card.lu { border-left-color: #ffc107; }
        .request-card.traité { border-left-color: #28a745; }
        
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .request-info h3 {
            color: #1e3c72;
            margin-bottom: 5px;
        }
        
        .request-meta {
            color: #666;
            font-size: 0.9rem;
        }
        
        .request-meta i {
            color: #ffd700;
            width: 16px;
            margin-right: 5px;
        }
        
        .status-badge {
            padding: 3px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-badge.non-lu {
            background: #dc3545;
            color: white;
        }
        
        .status-badge.lu {
            background: #ffc107;
            color: #333;
        }
        
        .status-badge.traité {
            background: #28a745;
            color: white;
        }
        
        .request-preview {
            color: #666;
            margin: 10px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .request-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        .btn-view { background: #1e3c72; color: white; }
        .btn-read { background: #ffc107; color: #333; }
        .btn-unread { background: #6c757d; color: white; }
        .btn-done { background: #28a745; color: white; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-email { background: #17a2b8; color: white; }
        .btn-whatsapp { background: #25D366; color: white; }
        .btn-back { background: #6c757d; color: white; }
        
        /* Vue détail */
        .detail-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        
        .detail-header h2 {
            color: #1e3c72;
        }
        
        .detail-section {
            margin-bottom: 25px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #1e3c72;
            margin-bottom: 5px;
        }
        
        .detail-value {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            line-height: 1.6;
        }
        
        .contact-buttons {
            display: flex;
            gap: 15px;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>VISION D'AIGLES</h3>
                <p>Espace administration</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="requests.php" class="active"><i class="fas fa-inbox"></i> Requêtes 
                    <?php if($stats['non_lu'] > 0): ?>
                        <span class="badge"><?php echo $stats['non_lu']; ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="sermons.php"><i class="fas fa-microphone"></i> Prédications</a></li>
                <li><a href="add_sermon.php"><i class="fas fa-plus-circle"></i> Ajouter prédication</a></li>
                <li><a href="events.php"><i class="fas fa-calendar"></i> Événements</a></li>
                <li><a href="pastors.php"><i class="fas fa-users"></i> Pasteurs</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>
                    <i class="fas fa-inbox"></i>
                    Gestion des requêtes
                </h1>
                <div>
                    Total: <strong><?php echo $stats['total']; ?></strong> message(s)
                </div>
            </div>
            
            <?php if($message): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <!-- Statistiques -->
            <div class="stats-container">
                <div class="stat-card non-lu">
                    <h4>Non lues</h4>
                    <div class="number"><?php echo $stats['non_lu']; ?></div>
                </div>
                <div class="stat-card lu">
                    <h4>Lues</h4>
                    <div class="number"><?php echo $stats['lu']; ?></div>
                </div>
                <div class="stat-card traite">
                    <h4>Traitées</h4>
                    <div class="number"><?php echo $stats['traité']; ?></div>
                </div>
            </div>
            
            <?php if($show_detail && $detail): ?>
                <!-- Vue détaillée d'une requête -->
                <div class="detail-container">
                    <div class="detail-header">
                        <h2><i class="fas fa-envelope-open-text"></i> Détail de la requête</h2>
                        <a href="requests.php" class="btn btn-back">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                    
                    <div class="detail-section">
                        <div class="detail-label">Expéditeur</div>
                        <div class="detail-value">
                            <strong><?php echo htmlspecialchars($detail['name']); ?></strong><br>
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($detail['email']); ?><br>
                            <?php if($detail['phone']): ?>
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($detail['phone']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <div class="detail-label">Informations</div>
                        <div class="detail-value">
                            <strong>Type :</strong> <?php echo $detail['request_type']; ?><br>
                            <strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($detail['created_at'])); ?><br>
                            <strong>Statut :</strong> 
                            <span class="status-badge <?php echo $detail['status']; ?>">
                                <?php echo $detail['status']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <div class="detail-label">Message</div>
                        <div class="detail-value">
                            <?php echo nl2br(htmlspecialchars($detail['message'])); ?>
                        </div>
                    </div>
                    
                    <div class="contact-buttons">
                        <a href="mailto:<?php echo $detail['email']; ?>?subject=Réponse à votre requête - VISION D'AIGLES&body=Bonjour <?php echo urlencode($detail['name']); ?>,%0D%0A%0D%0ANous avons bien reçu votre requête.%0D%0A%0D%0A[Votre réponse]%0D%0A%0D%0ACordialement,%0D%0AL'équipe pastorale" 
                           class="btn btn-email" target="_blank">
                            <i class="fas fa-envelope"></i> Répondre par email
                        </a>
                        
                        <?php if($detail['phone']): 
                            $phone = preg_replace('/[^0-9]/', '', $detail['phone']);
                            if(substr($phone, 0, 1) != '0') $phone = '242' . $phone;
                        ?>
                            <a href="https://wa.me/<?php echo $phone; ?>?text=Bonjour%20<?php echo urlencode($detail['name']); ?>%2C%20nous%20avons%20bien%20re%C3%A7u%20votre%20requ%C3%AAte." 
                               class="btn btn-whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i> Répondre sur WhatsApp
                            </a>
                        <?php endif; ?>
                        
                        <?php if($detail['status'] != 'traité'): ?>
                            <a href="?mark_done=<?php echo $detail['id']; ?>" class="btn btn-done">
                                <i class="fas fa-check-double"></i> Marquer comme traité
                            </a>
                        <?php endif; ?>
                        
                        <a href="?delete=<?php echo $detail['id']; ?>" class="btn btn-delete" 
                           onclick="return confirm('Supprimer cette requête ?')">
                            <i class="fas fa-trash"></i> Supprimer
                        </a>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Liste des requêtes -->
                <?php if(empty($requests)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Aucune requête pour le moment</h3>
                        <p>Les messages des visiteurs apparaîtront ici.</p>
                    </div>
                <?php else: ?>
                    <div class="requests-grid">
                        <?php foreach($requests as $r): ?>
                        <div class="request-card <?php echo $r['status']; ?>">
                            <div class="request-header">
                                <div class="request-info">
                                    <h3><?php echo htmlspecialchars($r['name']); ?></h3>
                                    <div class="request-meta">
                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($r['email']); ?> • 
                                        <i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?>
                                    </div>
                                </div>
                                <div>
                                    <span class="status-badge <?php echo $r['status']; ?>">
                                        <?php echo $r['status']; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="request-preview" onclick="window.location='?view=<?php echo $r['id']; ?>'">
                                <strong><?php echo $r['request_type']; ?> :</strong> 
                                <?php echo htmlspecialchars(substr($r['message'], 0, 100)); ?>...
                            </div>
                            
                            <div class="request-actions">
                                <a href="?view=<?php echo $r['id']; ?>" class="btn btn-view">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                                
                                <?php if($r['status'] == 'non lu'): ?>
                                    <a href="?mark_read=<?php echo $r['id']; ?>" class="btn btn-read">
                                        <i class="fas fa-check"></i> Lu
                                    </a>
                                <?php else: ?>
                                    <a href="?mark_unread=<?php echo $r['id']; ?>" class="btn btn-unread">
                                        <i class="fas fa-envelope"></i> Non lu
                                    </a>
                                <?php endif; ?>
                                
                                <?php if($r['status'] != 'traité'): ?>
                                    <a href="?mark_done=<?php echo $r['id']; ?>" class="btn btn-done">
                                        <i class="fas fa-check-double"></i> Traité
                                    </a>
                                <?php endif; ?>
                                
                                <a href="mailto:<?php echo $r['email']; ?>" class="btn btn-email">
                                    <i class="fas fa-reply"></i>
                                </a>
                                
                                <a href="?delete=<?php echo $r['id']; ?>" class="btn btn-delete" 
                                   onclick="return confirm('Supprimer cette requête ?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>