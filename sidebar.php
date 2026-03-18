<?php
// admin/includes/sidebar.php
// À inclure dans tous vos fichiers admin
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h3>VISION D'AIGLES</h3>
        <p>Espace administration</p>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a></li>
        <li><a href="stats.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'stats.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i> Statistiques
        </a></li>
        <li><a href="requests.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'requests.php' ? 'active' : ''; ?>">
            <i class="fas fa-inbox"></i> Requêtes 
            <?php 
            global $unread_requests;
            if(isset($unread_requests) && $unread_requests > 0): 
            ?>
                <span class="badge"><?php echo $unread_requests; ?></span>
            <?php endif; ?>
        </a></li>
        <li><a href="sermons.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sermons.php' ? 'active' : ''; ?>">
            <i class="fas fa-microphone"></i> Prédications
        </a></li>
        <li><a href="add_sermon.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_sermon.php' ? 'active' : ''; ?>">
            <i class="fas fa-plus-circle"></i> Ajouter prédication
        </a></li>
        <li><a href="events.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar"></i> Événements
        </a></li>
        <li><a href="calendar.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'calendar.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> Calendrier
        </a></li>
        <li><a href="pastors.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pastors.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Pasteurs
        </a></li>
        <li><a href="newsletter.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'newsletter.php' ? 'active' : ''; ?>">
            <i class="fas fa-envelope-open-text"></i> Newsletter
        </a></li>
        <li><a href="gallery.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : ''; ?>">
            <i class="fas fa-images"></i> Galerie
        </a></li>
        <li><a href="faq.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'faq.php' ? 'active' : ''; ?>">
            <i class="fas fa-question-circle"></i> FAQ
        </a></li>
        <li><a href="bible.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'bible.php' ? 'active' : ''; ?>">
            <i class="fas fa-bible"></i> Bible
        </a></li>
        <li><a href="app_manager.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'app_manager.php' ? 'active' : ''; ?>">
            <i class="fas fa-mobile-alt"></i> Application
        </a></li>
        <li><a href="push_notifications.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'push_notifications.php' ? 'active' : ''; ?>">
            <i class="fas fa-bell"></i> Notifications
        </a></li>
        <li><a href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a></li>
    </ul>
</div>