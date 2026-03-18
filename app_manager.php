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
$db->exec("CREATE TABLE IF NOT EXISTS app_versions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    version_name VARCHAR(50) NOT NULL,
    version_code INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size VARCHAR(20),
    release_notes TEXT,
    min_android_version VARCHAR(20),
    is_mandatory BOOLEAN DEFAULT FALSE,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS app_downloads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    version_id INT,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (version_id) REFERENCES app_versions(id)
)");

$upload_dir = '../uploads/apps/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$message = '';
$error = '';

// Upload d'une nouvelle version
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_app'])) {
    $version_name = $_POST['version_name'];
    $version_code = $_POST['version_code'];
    $release_notes = $_POST['release_notes'];
    $min_android = $_POST['min_android'];
    $is_mandatory = isset($_POST['is_mandatory']) ? 1 : 0;
    
    if(isset($_FILES['app_file']) && $_FILES['app_file']['error'] == 0) {
        $allowed = ['apk', 'aab'];
        $filename = $_FILES['app_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $file_size = $_FILES['app_file']['size'];
        
        if(in_array($ext, $allowed)) {
            $new_filename = 'visiondaigles_v' . $version_name . '_' . date('Ymd') . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            // Vérifier si le code version existe déjà
            $check = $db->prepare("SELECT id FROM app_versions WHERE version_code = ?");
            $check->execute([$version_code]);
            
            if($check->fetch()) {
                $error = "Ce code de version existe déjà. Utilisez un numéro plus grand.";
            } else {
                if(move_uploaded_file($_FILES['app_file']['tmp_name'], $upload_path)) {
                    // Taille formatée
                    if($file_size < 1048576) {
                        $size_formatted = round($file_size / 1024, 2) . ' KB';
                    } else {
                        $size_formatted = round($file_size / 1048576, 2) . ' MB';
                    }
                    
                    $stmt = $db->prepare("INSERT INTO app_versions (version_name, version_code, file_name, file_size, release_notes, min_android_version, is_mandatory) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$version_name, $version_code, $new_filename, $size_formatted, $release_notes, $min_android, $is_mandatory]);
                    $message = "Application uploadée avec succès !";
                } else {
                    $error = "Erreur lors de l'upload.";
                }
            }
        } else {
            $error = "Format non autorisé. Utilisez .apk ou .aab";
        }
    } else {
        $error = "Veuillez sélectionner un fichier.";
    }
}

// Supprimer une version
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $db->prepare("SELECT file_name FROM app_versions WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetchColumn();
    
    if($file && file_exists($upload_dir . $file)) {
        unlink($upload_dir . $file);
    }
    
    $stmt = $db->prepare("DELETE FROM app_versions WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: app_manager.php');
    exit();
}

$versions = $db->query("SELECT * FROM app_versions ORDER BY version_code DESC")->fetchAll(PDO::FETCH_ASSOC);
$total_downloads = $db->query("SELECT SUM(download_count) FROM app_versions")->fetchColumn();
$latest_version = !empty($versions) ? $versions[0] : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de l'application</title>
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
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #1e3c72;
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .btn-primary { background: #1e3c72; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #1e3c72;
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .mandatory-badge {
            background: #dc3545;
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
        }
        
        .optional-badge {
            background: #28a745;
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
        }
        
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-mobile-alt"></i> Gestion de l'application</h1>
                <div><?php echo date('d/m/Y H:i'); ?></div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Versions</h3>
                    <div class="stat-number"><?php echo count($versions); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Téléchargements</h3>
                    <div class="stat-number"><?php echo $total_downloads ?: 0; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Dernière version</h3>
                    <div class="stat-number"><?php echo $latest_version ? 'v' . $latest_version['version_name'] : '-'; ?></div>
                </div>
            </div>
            
            <?php if($message): ?>
                <div class="success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <h2><i class="fas fa-upload"></i> Publier une nouvelle version</h2>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Version (ex: 1.0.0)</label>
                            <input type="text" name="version_name" required>
                        </div>
                        <div class="form-group">
                            <label>Code de version</label>
                            <input type="number" name="version_code" required min="1">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Notes de version</label>
                        <textarea name="release_notes" rows="4"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Android minimum</label>
                            <select name="min_android">
                                <option value="4.4">Android 4.4</option>
                                <option value="5.0">Android 5.0</option>
                                <option value="6.0">Android 6.0</option>
                                <option value="7.0">Android 7.0</option>
                                <option value="8.0">Android 8.0</option>
                                <option value="9.0">Android 9.0</option>
                                <option value="10.0" selected>Android 10</option>
                                <option value="11.0">Android 11</option>
                                <option value="12.0">Android 12</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fichier APK/AAB</label>
                            <input type="file" name="app_file" accept=".apk,.aab" required>
                        </div>
                    </div>
                    
                    <div style="margin: 15px 0;">
                        <label>
                            <input type="checkbox" name="is_mandatory"> Mise à jour obligatoire
                        </label>
                    </div>
                    
                    <button type="submit" name="upload_app" class="btn btn-primary">
                        <i class="fas fa-cloud-upload-alt"></i> Publier
                    </button>
                </form>
            </div>
            
            <div class="card">
                <h2><i class="fas fa-history"></i> Historique des versions</h2>
                
                <?php if(empty($versions)): ?>
                    <p>Aucune version publiée.</p>
                <?php else: ?>
                    <table>
                        <tr>
                            <th>Version</th>
                            <th>Code</th>
                            <th>Date</th>
                            <th>Taille</th>
                            <th>Téléchargements</th>
                            <th>Obligatoire</th>
                            <th>Actions</th>
                        </tr>
                        <?php foreach($versions as $v): ?>
                        <tr>
                            <td><strong>v<?php echo $v['version_name']; ?></strong></td>
                            <td><?php echo $v['version_code']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($v['created_at'])); ?></td>
                            <td><?php echo $v['file_size']; ?></td>
                            <td><?php echo $v['download_count']; ?></td>
                            <td>
                                <?php if($v['is_mandatory']): ?>
                                    <span class="mandatory-badge">Obligatoire</span>
                                <?php else: ?>
                                    <span class="optional-badge">Optionnel</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="../uploads/apps/<?php echo $v['file_name']; ?>" class="btn btn-success btn-sm" download>Télécharger</a>
                                <a href="?delete=<?php echo $v['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')">Supprimer</a>
                            </td>
                        </tr>
                        <?php if($v['release_notes']): ?>
                        <tr>
                            <td colspan="7" style="background: #f8f9fa;">
                                <small><?php echo nl2br(htmlspecialchars($v['release_notes'])); ?></small>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>
            
            <?php if($latest_version): ?>
            <div class="card">
                <h2><i class="fas fa-link"></i> Lien public</h2>
                <p>Les visiteurs peuvent télécharger l'application ici :</p>
                <code><?php echo (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/downloads/app.php'; ?></code>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>