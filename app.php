<?php
session_start();
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Récupérer les paramètres du site
$settings = [];
$query = "SELECT * FROM settings";
$stmt = $db->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Récupérer la dernière version
$latest = $db->query("SELECT * FROM app_versions ORDER BY version_code DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Récupérer toutes les versions
$versions = $db->query("SELECT * FROM app_versions ORDER BY version_code DESC")->fetchAll(PDO::FETCH_ASSOC);

// Incrémenter le compteur de téléchargements
if(isset($_GET['download']) && $latest) {
    $stmt = $db->prepare("UPDATE app_versions SET download_count = download_count + 1 WHERE id = ?");
    $stmt->execute([$latest['id']]);
    
    // Enregistrer le téléchargement
    $ip = $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'];
    $stmt = $db->prepare("INSERT INTO app_downloads (version_id, ip_address, user_agent) VALUES (?, ?, ?)");
    $stmt->execute([$latest['id'], $ip, $ua]);
    
    // Rediriger vers le fichier
    header('Location: ../uploads/apps/' . $latest['file_name']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Télécharger l'application - <?php echo htmlspecialchars($settings['site_title'] ?? 'VISION D\'AIGLES'); ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .download-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
            margin-top: 80px;
        }
        
        .download-header h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .download-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .download-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .latest-version {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 40px;
            text-align: center;
        }
        
        .version-badge {
            display: inline-block;
            background: #ffd700;
            color: #1e3c72;
            padding: 5px 15px;
            border-radius: 25px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .app-icon {
            font-size: 5rem;
            color: #1e3c72;
            margin-bottom: 20px;
        }
        
        .download-btn {
            display: inline-block;
            background: #1e3c72;
            color: white;
            padding: 20px 50px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 1.3rem;
            font-weight: bold;
            margin: 20px 0;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .download-btn:hover {
            background: #2a5298;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(30,60,114,0.3);
            border-color: #ffd700;
        }
        
        .download-btn i {
            margin-right: 10px;
        }
        
        .app-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }
        
        .info-item strong {
            display: block;
            color: #1e3c72;
            margin-bottom: 5px;
        }
        
        .features {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        
        .features h2 {
            color: #1e3c72;
            margin-bottom: 30px;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .feature-item {
            text-align: center;
        }
        
        .feature-item i {
            font-size: 2.5rem;
            color: #ffd700;
            margin-bottom: 15px;
        }
        
        .versions-list {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .versions-list h2 {
            color: #1e3c72;
            margin-bottom: 30px;
        }
        
        .version-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .version-item:last-child {
            border-bottom: none;
        }
        
        .version-info h3 {
            color: #1e3c72;
            margin-bottom: 5px;
        }
        
        .version-meta {
            color: #666;
            font-size: 0.9rem;
        }
        
        .version-notes {
            margin-top: 5px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .btn-small {
            padding: 8px 20px;
            background: #1e3c72;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .btn-small:hover {
            background: #2a5298;
        }
        
        .qr-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
            border-radius: 20px;
            margin: 40px 0;
        }
        
        .qr-section h2 {
            color: white;
            margin-bottom: 30px;
        }
        
        .qr-code {
            display: inline-block;
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
        }
        
        .qr-code img {
            width: 200px;
            height: 200px;
        }
        
        .qr-section p {
            margin-top: 20px;
            font-size: 1.1rem;
        }
        
        @media (max-width: 768px) {
            .download-btn {
                padding: 15px 30px;
                font-size: 1.1rem;
            }
            
            .version-item {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <img src="../images/logo.png" alt="Logo">
                <div>
                    <h1>VISION D'AIGLES</h1>
                    <span>Tabernacle</span>
                </div>
            </div>
            <div class="menu-toggle"><i class="fas fa-bars"></i></div>
            <ul class="nav-links">
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="../index.php#apropos">À propos</a></li>
                <li><a href="../index.php#sermons">Prédications</a></li>
                <li><a href="../gallery.php">Galerie</a></li>
                <li><a href="../faq.php">FAQ</a></li>
                <li><a href="../bible.php">Bible</a></li>
                <li><a href="app.php" class="active">Application</a></li>
            </ul>
        </nav>
    </header>
    
    <div class="download-header">
        <h1><i class="fas fa-mobile-alt"></i> Application mobile</h1>
        <p>Restez connecté à VISION D'AIGLES où que vous soyez</p>
    </div>
    
    <div class="download-container">
        <?php if($latest): ?>
        <!-- Dernière version -->
        <div class="latest-version">
            <span class="version-badge">Dernière version</span>
            <div class="app-icon">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <h2 style="color: #1e3c72;">VISION D'AIGLES v<?php echo $latest['version_name']; ?></h2>
            <p style="color: #666; margin: 10px 0;"><?php echo $latest['release_notes']; ?></p>
            
            <a href="?download=1" class="download-btn">
                <i class="fas fa-download"></i> Télécharger (<?php echo $latest['file_size']; ?>)
            </a>
            
            <div class="app-info">
                <div class="info-item">
                    <strong>Version</strong>
                    <?php echo $latest['version_name']; ?>
                </div>
                <div class="info-item">
                    <strong>Date</strong>
                    <?php echo date('d/m/Y', strtotime($latest['created_at'])); ?>
                </div>
                <div class="info-item">
                    <strong>Android minimum</strong>
                    <?php echo $latest['min_android_version']; ?>
                </div>
                <div class="info-item">
                    <strong>Téléchargements</strong>
                    <?php echo $latest['download_count']; ?>
                </div>
            </div>
            
            <?php if($latest['is_mandatory']): ?>
            <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 10px; margin-top: 20px;">
                <i class="fas fa-exclamation-triangle"></i> 
                Cette mise à jour est obligatoire pour continuer à utiliser l'application.
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="latest-version">
            <div class="app-icon">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <h2 style="color: #1e3c72;">Application bientôt disponible</h2>
            <p style="color: #666;">L'application mobile de VISION D'AIGLES sera bientôt disponible au téléchargement.</p>
        </div>
        <?php endif; ?>
        
        <!-- Fonctionnalités -->
        <div class="features">
            <h2><i class="fas fa-star"></i> Fonctionnalités</h2>
            <div class="features-grid">
                <div class="feature-item">
                    <i class="fas fa-video"></i>
                    <h3>Prédications</h3>
                    <p>Écoutez les derniers sermons en ligne</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-calendar"></i>
                    <h3>Événements</h3>
                    <p>Restez informé des activités de l'église</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-pray"></i>
                    <h3>Requêtes</h3>
                    <p>Envoyez vos demandes de prière</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-bible"></i>
                    <h3>Bible</h3>
                    <p>Lisez la parole de Dieu</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-bell"></i>
                    <h3>Notifications</h3>
                    <p>Recevez des alertes en temps réel</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-wifi"></i>
                    <h3>Mode hors-ligne</h3>
                    <p>Écoutez sans connexion internet</p>
                </div>
            </div>
        </div>
        
        <!-- QR Code -->
        <div class="qr-section">
            <h2><i class="fas fa-qrcode"></i> Téléchargement rapide</h2>
            <p>Scannez ce QR code avec votre téléphone</p>
            <div class="qr-code">
                <?php 
                $url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/downloads/app.php?download=1';
                ?>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($url); ?>" 
                     alt="QR Code">
            </div>
            <p>Ou flashez ce code pour télécharger directement</p>
        </div>
        
        <!-- Anciennes versions -->
        <?php if(count($versions) > 1): ?>
        <div class="versions-list">
            <h2><i class="fas fa-history"></i> Anciennes versions</h2>
            <?php foreach($versions as $v): 
                if($v['id'] == $latest['id']) continue;
            ?>
            <div class="version-item">
                <div class="version-info">
                    <h3>Version <?php echo $v['version_name']; ?></h3>
                    <div class="version-meta">
                        <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($v['created_at'])); ?></span>
                        <span><i class="fas fa-database"></i> <?php echo $v['file_size']; ?></span>
                    </div>
                    <?php if($v['release_notes']): ?>
                    <div class="version-notes">
                        <i class="fas fa-info-circle"></i> <?php echo substr($v['release_notes'], 0, 100); ?>...
                    </div>
                    <?php endif; ?>
                </div>
                <a href="../uploads/apps/<?php echo $v['file_name']; ?>" class="btn-small" download>
                    <i class="fas fa-download"></i> Télécharger
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <footer>
        <div class="footer-links">
            <a href="../index.php">Accueil</a>
            <a href="../index.php#apropos">À propos</a>
            <a href="../index.php#sermons">Prédications</a>
            <a href="../gallery.php">Galerie</a>
            <a href="../faq.php">FAQ</a>
            <a href="../bible.php">Bible</a>
            <a href="app.php">Application</a>
        </div>
        <div class="social-links">
            <a href="https://wa.me/242066293093" target="_blank"><i class="fab fa-whatsapp"></i></a>
            <a href="https://chat.whatsapp.com/HCikWDquIvw4qNfDGjErRC" target="_blank"><i class="fab fa-whatsapp"></i></a>
            <a href="https://www.youtube.com/@AyezFoienDieu" target="_blank"><i class="fab fa-youtube"></i></a>
        </div>
        <p>&copy; <?php echo date('Y'); ?> VISION D'AIGLES Tabernacle</p>
    </footer>
    
    <script>
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });
    </script>
</body>
</html>