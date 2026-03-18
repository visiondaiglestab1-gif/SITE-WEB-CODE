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

// Créer la table des inscriptions
$db->exec("CREATE TABLE IF NOT EXISTS event_registrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_email VARCHAR(100) NOT NULL,
    user_phone VARCHAR(50),
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('confirmé', 'en attente', 'annulé') DEFAULT 'confirmé',
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
)");

$events = $db->query("SELECT e.*, COUNT(r.id) as registrations FROM events e LEFT JOIN event_registrations r ON e.id = r.event_id GROUP BY e.id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Calendrier - Administration</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales/fr.global.min.js'></script>
    <style>
        .admin-container { display: flex; }
        .sidebar { width: 280px; background: linear-gradient(180deg, #1e3c72 0%, #0a1a2f 100%); color: white; padding: 20px 0; position: fixed; height: 100vh; }
        .main-content { margin-left: 280px; padding: 30px; flex: 1; }
        #calendar { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .event-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #1e3c72; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header" style="padding: 0 20px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.2);">
                <h3 style="color: #ffd700;">VISION D'AIGLES</h3>
                <p>Espace administration</p>
            </div>
            <ul class="sidebar-menu" style="list-style: none; padding: 0;">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="stats.php"><i class="fas fa-chart-bar"></i> Statistiques</a></li>
                <li><a href="requests.php"><i class="fas fa-inbox"></i> Requêtes <?php if($unread_requests > 0): ?><span class="badge"><?php echo $unread_requests; ?></span><?php endif; ?></a></li>
                <li><a href="sermons.php"><i class="fas fa-microphone"></i> Prédications</a></li>
                <li><a href="add_sermon.php"><i class="fas fa-plus-circle"></i> Ajouter prédication</a></li>
                <li><a href="events.php"><i class="fas fa-calendar"></i> Événements</a></li>
                <li><a href="calendar.php" class="active"><i class="fas fa-calendar-alt"></i> Calendrier</a></li>
                <li><a href="pastors.php"><i class="fas fa-users"></i> Pasteurs</a></li>
                <li><a href="newsletter.php"><i class="fas fa-envelope-open-text"></i> Newsletter</a></li>
                <li><a href="gallery.php"><i class="fas fa-images"></i> Galerie</a></li>
                <li><a href="faq.php"><i class="fas fa-question-circle"></i> FAQ</a></li>
                <li><a href="bible.php"><i class="fas fa-bible"></i> Bible</a></li>
                <li><a href="push_notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <h1><i class="fas fa-calendar-alt"></i> Calendrier interactif</h1>
            
            <div class="event-stats">
                <div class="stat-card">
                    <h3>Total événements</h3>
                    <div class="stat-number"><?php echo count($events); ?></div>
                </div>
                <div class="stat-card">
                    <h3>À venir</h3>
                    <div class="stat-number"><?php echo count(array_filter($events, fn($e) => $e['event_date'] >= date('Y-m-d'))); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Inscriptions</h3>
                    <div class="stat-number"><?php echo array_sum(array_column($events, 'registrations')); ?></div>
                </div>
            </div>
            
            <div id="calendar"></div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const calendarEl = document.getElementById('calendar');
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        locale: 'fr',
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,listWeek'
                        },
                        events: [
                            <?php foreach($events as $e): ?>
                            {
                                title: '<?php echo addslashes($e['title']); ?>',
                                start: '<?php echo $e['event_date']; ?>',
                                extendedProps: {
                                    description: '<?php echo addslashes($e['description']); ?>',
                                    registrations: <?php echo $e['registrations']; ?>
                                }
                            },
                            <?php endforeach; ?>
                        ],
                        eventClick: function(info) {
                            alert('Événement: ' + info.event.title + '\n' +
                                  'Description: ' + info.event.extendedProps.description + '\n' +
                                  'Inscrits: ' + info.event.extendedProps.registrations);
                        }
                    });
                    calendar.render();
                });
            </script>
        </div>
    </div>
</body>
</html>