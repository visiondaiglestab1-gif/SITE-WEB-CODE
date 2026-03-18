<?php
session_start();
require_once '../config/database.php';

// Vérifier la connexion admin
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Compter les requêtes non lues
$unread_requests = $db->query("SELECT COUNT(*) FROM user_requests WHERE status = 'non lu'")->fetchColumn();

// Version simplifiée - Pas de chargement de fichier JSON pour éviter les erreurs
$bible_status = "En attente de configuration";
$total_verses = 0;
$total_books = 66; // Nombre standard de livres dans la Bible
$version_name = "Louis Segond 1910";

// Vérifier si le dossier data existe
$data_folder = '../data/';
$bible_file = $data_folder . 'bible.json';
$file_exists = file_exists($bible_file);
$file_size = $file_exists ? round(filesize($bible_file) / 1024 / 1024, 2) . ' MB' : 'Fichier non trouvé';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bible - Administration</title>
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
        
        .info-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .info-card h2 {
            color: #1e3c72;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ffd700;
        }
        
        .status-box {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .status-success {
            background: #d4edda;
            border: 1px solid #28a745;
            color: #155724;
        }
        
        .status-warning {
            background: #fff3cd;
            border: 1px solid #ffd700;
            color: #856404;
        }
        
        .status-error {
            background: #f8d7da;
            border: 1px solid #dc3545;
            color: #721c24;
        }
        
        .file-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-family: monospace;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #1e3c72;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #2a5298;
        }
        
        .instructions {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .instructions ol {
            margin-left: 20px;
            margin-top: 10px;
        }
        
        .instructions li {
            margin-bottom: 10px;
        }
        
        code {
            background: #f8f9fa;
            padding: 2px 5px;
            border-radius: 3px;
            color: #dc3545;
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
                <li><a href="events.php"><i class="fas fa-calendar"></i> Événements</a></li>
                <li><a href="calendar.php"><i class="fas fa-calendar-alt"></i> Calendrier</a></li>
                <li><a href="pastors.php"><i class="fas fa-users"></i> Pasteurs</a></li>
                <li><a href="newsletter.php"><i class="fas fa-envelope-open-text"></i> Newsletter</a></li>
                <li><a href="gallery.php"><i class="fas fa-images"></i> Galerie</a></li>
                <li><a href="faq.php"><i class="fas fa-question-circle"></i> FAQ</a></li>
                <li><a href="bible.php" class="active"><i class="fas fa-bible"></i> Bible</a></li>
                <li><a href="push_notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-bible"></i> Administration de la Bible</h1>
                <div><?php echo date('d/m/Y H:i'); ?></div>
            </div>
            
            <!-- Statistiques simples -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Version</h3>
                    <div class="stat-number">LSG</div>
                </div>
                <div class="stat-card">
                    <h3>Livres</h3>
                    <div class="stat-number">66</div>
                </div>
                <div class="stat-card">
                    <h3>Status</h3>
                    <div class="stat-number" style="font-size: 1.5rem;"><?php echo $file_exists ? '✅' : '❌'; ?></div>
                </div>
            </div>
            
            <!-- Statut du fichier -->
            <div class="info-card">
                <h2><i class="fas fa-file-json"></i> Fichier Bible</h2>
                
                <div class="file-info">
                    <strong>Chemin complet :</strong> <?php echo realpath($bible_file) ?: $bible_file; ?><br>
                    <strong>Dossier data :</strong> <?php echo realpath($data_folder) ?: $data_folder; ?><br>
                    <strong>Fichier existe :</strong> <?php echo $file_exists ? 'Oui' : 'Non'; ?><br>
                    <strong>Taille :</strong> <?php echo $file_size; ?><br>
                </div>
                
                <?php if($file_exists): ?>
                    <div class="status-box status-success">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Fichier trouvé !</strong> La Bible est prête à être utilisée.
                    </div>
                <?php else: ?>
                    <div class="status-box status-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Fichier non trouvé</strong>
                    </div>
                    
                    <div class="instructions">
                        <h3><i class="fas fa-cog"></i> Configuration requise</h3>
                        <ol>
                            <li>Créez le dossier <strong>data</strong> à la racine de votre site</li>
                            <li>Placez votre fichier <strong>bible.json</strong> dans ce dossier</li>
                            <li>Le chemin complet doit être : <code><?php echo $bible_file; ?></code></li>
                        </ol>
                        
                        <h3 style="margin-top: 20px;">Commandes à exécuter (via FTP)</h3>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                            <code>mkdir /home/vol10_2/infinityfree.com/if0_41372313/htdocs/data</code><br>
                            <code>chmod 755 /home/vol10_2/infinityfree.com/if0_41372313/htdocs/data</code>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Test de chargement simple -->
            <div class="info-card">
                <h2><i class="fas fa-flask"></i> Test de chargement</h2>
                
                <?php if($file_exists): ?>
                    <?php
                    // Tentative de chargement sécurisée
                    try {
                        $test_content = file_get_contents($bible_file, false, null, 0, 1000); // Lire seulement les 1000 premiers caractères
                        if($test_content !== false) {
                            $test_json = json_decode($test_content, true);
                            if($test_json !== null) {
                                echo '<div class="status-box status-success">';
                                echo '<i class="fas fa-check-circle"></i> Fichier JSON valide (premiers caractères lus avec succès)';
                                echo '</div>';
                                
                                if(isset($test_json['metadata'])) {
                                    echo '<p><strong>Version :</strong> ' . htmlspecialchars($test_json['metadata']['name'] ?? 'Inconnue') . '</p>';
                                }
                            } else {
                                echo '<div class="status-box status-error">';
                                echo '<i class="fas fa-exclamation-circle"></i> Erreur JSON : ' . json_last_error_msg();
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="status-box status-error">';
                            echo '<i class="fas fa-exclamation-circle"></i> Impossible de lire le fichier (vérifiez les permissions)';
                            echo '</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="status-box status-error">';
                        echo '<i class="fas fa-exclamation-circle"></i> Exception : ' . $e->getMessage();
                        echo '</div>';
                    }
                    ?>
                <?php else: ?>
                    <div class="status-box status-warning">
                        <i class="fas fa-info-circle"></i> 
                        Le test de chargement sera disponible après avoir placé le fichier.
                    </div>
                <?php endif; ?>
                
                <!-- Bouton pour tester la page publique -->
                <a href="../bible.php" target="_blank" class="btn">
                    <i class="fas fa-external-link-alt"></i> Voir la Bible publique
                </a>
            </div>
            
            <!-- Informations système -->
            <div class="info-card">
                <h2><i class="fas fa-server"></i> Informations système</h2>
                <table class="table" style="width:100%;">
                    <tr>
                        <td><strong>Limite mémoire PHP :</strong></td>
                        <td><?php echo ini_get('memory_limit'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Temps d'exécution max :</strong></td>
                        <td><?php echo ini_get('max_execution_time'); ?> secondes</td>
                    </tr>
                    <tr>
                        <td><strong>Taille max upload :</strong></td>
                        <td><?php echo ini_get('upload_max_filesize'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Dossier temporaire :</strong></td>
                        <td><?php echo sys_get_temp_dir(); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>