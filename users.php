<?php
session_start();
require_once '../config/database.php';

if(!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 'super_admin') {
    header('Location: dashboard.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';

// Ajout d'un utilisateur
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $full_name = $_POST['full_name'];
    $role = $_POST['role'];
    
    $query = "INSERT INTO admins (username, password, email, full_name, role) 
              VALUES (:username, :password, :email, :full_name, :role)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':role', $role);
    
    if($stmt->execute()) {
        $message = 'Utilisateur ajouté avec succès !';
    }
}

// Suppression d'un utilisateur
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if($id != $_SESSION['admin_id']) { // Empêcher de se supprimer soi-même
        $query = "DELETE FROM admins WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        header('Location: users.php?deleted=1');
        exit();
    }
}

// Récupérer tous les utilisateurs
$query = "SELECT * FROM admins ORDER BY role, username";
$users = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #1e3c72; color: white; padding: 20px; }
        .main-content { flex: 1; background: #f5f5f5; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; display: flex; justify-content: space-between; }
        .btn-add { background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; cursor: pointer; }
        .users-table { width: 100%; background: white; border-radius: 5px; overflow: hidden; }
        .users-table th { background: #1e3c72; color: white; padding: 12px; text-align: left; }
        .users-table td { padding: 12px; border-bottom: 1px solid #eee; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 0.8rem; }
        .badge-super { background: #ffd700; color: #1e3c72; }
        .badge-editor { background: #4CAF50; color: white; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; width: 90%; max-width: 500px; margin: 50px auto; padding: 30px; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
        .btn-save { background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h3>VISION D'AIGLES</h3>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a></li>
                <li><a href="sermons.php"><i class="fas fa-microphone"></i> Prédications</a></li>
                <li><a href="add_sermon.php"><i class="fas fa-plus"></i> Ajouter prédication</a></li>
                <li><a href="events.php"><i class="fas fa-calendar"></i> Événements</a></li>
                <li><a href="pastors.php"><i class="fas fa-users"></i> Pasteurs</a></li>
                <li><a href="users.php" class="active"><i class="fas fa-user-cog"></i> Utilisateurs</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Paramètres</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out"></i> Déconnexion</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h2>Gestion des utilisateurs</h2>
                <button onclick="showAddModal()" class="btn-add"><i class="fas fa-plus"></i> Ajouter un utilisateur</button>
            </div>
            
            <?php if(isset($_GET['deleted'])): ?>
                <div class="message" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    Utilisateur supprimé avec succès !
                </div>
            <?php endif; ?>
            
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Nom d'utilisateur</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Date d'inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $user['role'] == 'super_admin' ? 'badge-super' : 'badge-editor'; ?>">
                                <?php echo $user['role'] == 'super_admin' ? 'Super Admin' : 'Éditeur'; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if($user['id'] != $_SESSION['admin_id']): ?>
                            <a href="?delete=<?php echo $user['id']; ?>" 
                               onclick="return confirm('Supprimer cet utilisateur ?')"
                               style="color: #f44336;">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal d'ajout d'utilisateur -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-bottom: 20px;">Ajouter un utilisateur</h3>
            <?php if($message): ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Nom d'utilisateur *</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Mot de passe *</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Nom complet</label>
                    <input type="text" name="full_name">
                </div>
                <div class="form-group">
                    <label>Rôle</label>
                    <select name="role">
                        <option value="editor">Éditeur</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <button type="submit" name="add_user" class="btn-save">Ajouter</button>
                <button type="button" onclick="hideAddModal()" style="float: right; padding: 10px 20px;">Annuler</button>
            </form>
        </div>
    </div>
    
    <script>
        function showAddModal() {
            document.getElementById('addUserModal').style.display = 'block';
        }
        
        function hideAddModal() {
            document.getElementById('addUserModal').style.display = 'none';
        }
        
        // Fermer le modal si on clique en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('addUserModal');
            if(event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>