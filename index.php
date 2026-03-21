<?php
require_once 'config/database.php';

// Récupérer les paramètres du site
$settings = getAllSettings();

// Récupérer les derniers sermons
$sermons = [];
try {
    $stmt = $pdo->query("SELECT * FROM sermons ORDER BY sermon_date DESC LIMIT 6");
    $sermons = $stmt->fetchAll();
} catch (Exception $e) {
    $sermons = [];
}

// Récupérer les événements à venir
$events = [];
try {
    $stmt = $pdo->query("SELECT * FROM evenements WHERE event_date >= NOW() ORDER BY event_date LIMIT 4");
    $events = $stmt->fetchAll();
} catch (Exception $e) {
    $events = [];
}

// Récupérer les témoignages approuvés
$testimonials = [];
try {
    $stmt = $pdo->query("SELECT * FROM testimonials WHERE approved = 1 ORDER BY created_at DESC LIMIT 3");
    $testimonials = $stmt->fetchAll();
} catch (Exception $e) {
    $testimonials = [];
}

// Récupérer les statistiques
$stats = [];
try {
    $stmt = $pdo->query("SELECT stat_key, stat_value FROM stats");
    while ($row = $stmt->fetch()) {
        $stats[$row['stat_key']] = $row['stat_value'];
    }
} catch (Exception $e) {
    $stats = [];
}

// Récupérer les derniers articles de blog
$blog_posts = [];
try {
    $stmt = $pdo->query("SELECT * FROM blog_posts WHERE published = 1 ORDER BY created_at DESC LIMIT 3");
    $blog_posts = $stmt->fetchAll();
} catch (Exception $e) {
    $blog_posts = [];
}

// Récupérer les paramètres
$site_title = $settings['site_title'] ?? 'VISION D\'AIGLES Tabernacle';
$site_description = $settings['site_description'] ?? 'Une église de foi et de puissance à Brazzaville';
$main_verse = $settings['main_verse'] ?? 'Ésaïe 40:31';
$address = $settings['address'] ?? 'Brazzaville, République du Congo';
$contact_phone = $settings['contact_phone'] ?? '+242 06 629 30 93';
$contact_email = $settings['contact_email'] ?? 'contact@visiondaigles.org';
$worship_times = $settings['worship_times'] ?? 'Dimanche 10h - Mercredi 18h';
$youtube_url = $settings['youtube_url'] ?? 'https://www.youtube.com/@AyezFoienDieu';
$whatsapp_group = $settings['whatsapp_group'] ?? 'https://chat.whatsapp.com/HCikWDquIvw4qNfDGjErRC';
$whatsapp_direct = $settings['whatsapp_direct'] ?? 'https://wa.me/242066293093';
$radio_url = $settings['radio_url'] ?? 'https://vateglise.ismyradio.com/player';
$pastor_name = $settings['pastor_name'] ?? 'Pasteur Rubiel Jabien';
$pastor_title = $settings['pastor_title'] ?? 'Pasteur Fondateur';
$pastor_bio = $settings['pastor_bio'] ?? 'Appelé à réveiller la vision prophétique dans le corps de Christ.';
$pastor_photo = $settings['pastor_photo'] ?? 'assets/images/pastor-placeholder.jpg';

// Traitement du formulaire de requête
$request_message = '';
$request_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_request'])) {
    $name = trim($_POST['request_name'] ?? '');
    $email = trim($_POST['request_email'] ?? '');
    $phone = trim($_POST['request_phone'] ?? '');
    $request_type = $_POST['request_type'] ?? 'autre';
    $message = trim($_POST['request_message'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        $request_error = "Veuillez remplir tous les champs obligatoires";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO requetes (name, email, phone, request_type, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $request_type, $message]);
            $request_message = "Votre requête a été envoyée avec succès ! Nous vous répondrons dans les plus brefs délais.";
        } catch (Exception $e) {
            $request_error = "Erreur lors de l'envoi. Veuillez réessayer.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_title); ?></title>
    
    <!-- Meta tags SEO -->
    <meta name="description" content="<?php echo htmlspecialchars($site_description); ?>">
    <meta name="keywords" content="église, vision d'aigles, tabernacle, culte, prédications, bible, galerie, témoignages">
    <meta name="author" content="VISION D'AIGLES Tabernacle">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/">
    <meta property="og:title" content="<?php echo htmlspecialchars($site_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($site_description); ?>">
    <meta property="og:image" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/assets/images/logo.png">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($site_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($site_description); ?>">
    <meta name="twitter:image" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/assets/images/logo.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="manifest" href="/manifest.json">
    
    <style>
        /* ===== VARIABLES ===== */
        :root {
            --primary-color: #1e3c72;
            --primary-dark: #0a1a2f;
            --primary-light: #2a5298;
            --accent-color: #ffd700;
            --accent-hover: #ffed4a;
            --text-color: #333;
            --text-light: #666;
            --white: #ffffff;
            --gray-light: #f5f5f5;
            --shadow: 0 5px 15px rgba(0,0,0,0.1);
            --shadow-hover: 0 10px 25px rgba(0,0,0,0.2);
            --whatsapp: #25D366;
            --youtube: #FF0000;
            --radio: #4CAF50;
            --bg-body: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        body.dark-mode {
            --primary-color: #2c3e50;
            --primary-dark: #1a2a3a;
            --primary-light: #34495e;
            --text-color: #ecf0f1;
            --text-light: #bdc3c7;
            --white: #2c3e50;
            --gray-light: #34495e;
            --bg-body: #1a1a2e;
            --shadow: 0 5px 15px rgba(0,0,0,0.3);
            --shadow-hover: 0 10px 25px rgba(0,0,0,0.4);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background: var(--bg-body);
            transition: background 0.3s, color 0.3s;
        }
        
        /* Mode sombre toggle */
        .dark-mode-toggle {
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 50%;
            transition: all 0.3s;
        }
        
        .dark-mode-toggle:hover {
            background: rgba(255,255,255,0.2);
            transform: rotate(15deg);
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* ===== HEADER ===== */
        header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: var(--white);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid var(--accent-color);
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .logo img:hover {
            transform: rotate(360deg) scale(1.1);
        }
        
        .logo h1 {
            font-size: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
            transition: color 0.3s;
        }
        
        .logo:hover h1 {
            color: var(--accent-color);
        }
        
        .logo span {
            color: var(--accent-color);
            font-size: 0.9rem;
            display: block;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 25px;
            align-items: center;
        }
        
        .nav-links a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
            padding: 5px 0;
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-color);
            transition: width 0.3s;
        }
        
        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }
        
        .nav-links a:hover {
            color: var(--accent-color);
            transform: translateY(-2px);
        }
        
        .nav-links a.active {
            color: var(--accent-color);
        }
        
        .menu-toggle {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .menu-toggle:hover {
            color: var(--accent-color);
        }
        
        /* ===== BOUTONS SUIVEZ-NOUS ===== */
        .top-social-bar {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            padding: 12px 0;
            position: relative;
            z-index: 1001;
            margin-top: 70px;
        }
        
        .top-social-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .top-social-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .top-social-btn:hover {
            transform: translateY(-3px);
            filter: brightness(1.1);
        }
        
        .top-social-btn.youtube {
            background: var(--youtube);
            color: white;
        }
        
        .top-social-btn.radio {
            background: var(--radio);
            color: white;
        }
        
        .top-social-btn.whatsapp {
            background: var(--whatsapp);
            color: white;
        }
        
        .top-social-btn.pasteur {
            background: var(--accent-color);
            color: var(--primary-color);
        }
        
        .top-social-btn.app {
            background: #2c3e50;
            color: white;
        }
        
        /* ===== HERO ===== */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('assets/images/about1.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--white);
        }
        
        .hero-content {
            max-width: 800px;
            padding: 0 20px;
        }
        
        .hero-logo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 4px solid var(--accent-color);
            margin-bottom: 30px;
            object-fit: cover;
            animation: pulse 2s infinite ease-in-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255,215,0,0.7); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 20px rgba(255,215,0,0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255,215,0,0); }
        }
        
        .hero-content h2 {
            font-size: 3rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            animation: fadeInDown 1s;
        }
        
        .hero-content p {
            font-size: 1.5rem;
            margin-bottom: 30px;
            font-style: italic;
            animation: fadeInUp 1s;
        }
        
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: var(--accent-color);
            color: var(--primary-color);
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: all 0.3s;
            margin: 10px;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.3);
            transition: left 0.3s;
            z-index: -1;
        }
        
        .btn:hover::before {
            left: 0;
        }
        
        .btn:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
            border-color: var(--white);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--accent-color);
            color: var(--white);
        }
        
        .btn-outline:hover {
            background: var(--accent-color);
            color: var(--primary-color);
        }
        
        /* ===== SECTIONS ===== */
        section {
            padding: 80px 20px;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 50px;
            color: var(--primary-color);
            position: relative;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 100px;
            height: 3px;
            background: var(--accent-color);
            margin: 20px auto;
            transition: width 0.3s;
        }
        
        .section-title:hover::after {
            width: 150px;
        }
        
        /* ===== STATISTIQUES ===== */
        .stats-section {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: var(--white);
            text-align: center;
        }
        
        .stats-section .section-title {
            color: var(--white);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-top: 40px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: var(--accent-color);
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        /* ===== TÉMOIGNAGES ===== */
        .testimonials-section {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }
        
        body.dark-mode .testimonials-section {
            background: linear-gradient(135deg, #2c3e50, #34495e);
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .testimonial-card {
            background: var(--white);
            padding: 30px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }
        
        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }
        
        .testimonial-card i {
            color: var(--accent-color);
            font-size: 2rem;
            margin-bottom: 15px;
            display: block;
        }
        
        .testimonial-card p {
            font-style: italic;
            margin-bottom: 20px;
        }
        
        .testimonial-card h4 {
            color: var(--primary-color);
        }
        
        /* ===== BLOG ===== */
        .blog-section {
            background: var(--white);
        }
        
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .blog-card {
            background: var(--gray-light);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }
        
        .blog-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }
        
        .blog-image {
            height: 200px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--accent-color);
        }
        
        .blog-content {
            padding: 25px;
        }
        
        .blog-content h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .blog-meta {
            color: var(--text-light);
            font-size: 0.85rem;
            margin-bottom: 15px;
        }
        
        .blog-meta i {
            color: var(--accent-color);
        }
        
        .blog-excerpt {
            color: var(--text-light);
            margin-bottom: 20px;
        }
        
        /* ===== NOTIFICATIONS PUSH ===== */
        .push-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary-color);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: var(--shadow-hover);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .push-notification button {
            background: var(--accent-color);
            color: var(--primary-color);
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .push-notification .close {
            cursor: pointer;
            font-size: 1.2rem;
        }
        
        /* ===== À PROPOS ===== */
        .about {
            background: var(--white);
        }
        
        .about-content {
            display: flex;
            gap: 50px;
            align-items: center;
        }
        
        .about-text {
            flex: 1;
        }
        
        .about-text p {
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .about-image {
            flex: 1;
        }
        
        .about-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: var(--shadow);
            transition: all 0.5s;
        }
        
        .about-image img:hover {
            transform: scale(1.05) rotate(2deg);
            box-shadow: var(--shadow-hover);
        }
        
        /* ===== LIENS RAPIDES ===== */
        .quick-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            padding: 40px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            flex-wrap: wrap;
        }
        
        body.dark-mode .quick-links {
            background: linear-gradient(135deg, #2c3e50, #34495e);
        }
        
        .quick-link-card {
            background: var(--white);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s;
            text-decoration: none;
            color: var(--text-color);
            flex: 1;
            min-width: 200px;
            max-width: 250px;
        }
        
        .quick-link-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }
        
        .quick-link-card i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .quick-link-card h3 {
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        
        .quick-link-card p {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        /* ===== PRÉDICATIONS ===== */
        .sermons {
            background: var(--gray-light);
        }
        
        .sermons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .sermon-card {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }
        
        .sermon-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }
        
        .sermon-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--accent-color);
        }
        
        .sermon-content {
            padding: 25px;
        }
        
        .sermon-content h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .sermon-meta {
            color: var(--text-light);
            margin-bottom: 15px;
        }
        
        .sermon-meta i {
            color: var(--accent-color);
            width: 20px;
        }
        
        .audio-player {
            margin: 20px 0;
        }
        
        .plyr {
            border-radius: 8px;
            --plyr-color-main: var(--primary-color);
        }
        
        .sermon-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .play-count i {
            color: var(--accent-color);
        }
        
        /* ===== ÉVÉNEMENTS ===== */
        .events {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--white);
        }
        
        .events .section-title {
            color: var(--white);
        }
        
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .event-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 30px;
            transition: all 0.3s;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .event-card:hover {
            transform: translateY(-15px);
            background: rgba(255,255,255,0.2);
            border-color: var(--accent-color);
        }
        
        .event-date {
            font-size: 2rem;
            font-weight: bold;
            color: var(--accent-color);
            margin-bottom: 15px;
        }
        
        /* ===== LE PASTEUR ===== */
        .pastor-section {
            background: var(--white);
        }
        
        .pastor-card {
            max-width: 900px;
            margin: 0 auto;
            background: var(--gray-light);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 40px;
        }
        
        .pastor-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }
        
        .pastor-card img {
            width: 250px;
            height: 250px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid var(--accent-color);
            margin-bottom: 25px;
            transition: all 0.5s;
        }
        
        .pastor-card:hover img {
            transform: scale(1.05);
        }
        
        .pastor-card h3 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .pastor-title {
            color: var(--accent-color);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        
        .pastor-bio {
            max-width: 600px;
            margin: 0 auto;
            color: var(--text-light);
            line-height: 1.8;
        }
        
        /* ===== REQUÊTES ===== */
        .request-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            padding: 60px 20px;
        }
        
        body.dark-mode .request-section {
            background: linear-gradient(135deg, #2c3e50, #34495e);
        }
        
        .request-container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--white);
            padding: 40px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }
        
        .request-container:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-5px);
        }
        
        .request-container h2 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(30,60,114,0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-submit {
            background: var(--primary-color);
            color: var(--white);
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background: var(--primary-light);
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        /* ===== CONTACT ===== */
        .contact {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: var(--white);
        }
        
        .contact .section-title {
            color: var(--white);
        }
        
        .contact-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 50px;
        }
        
        .contact-info {
            background: rgba(255,255,255,0.1);
            padding: 40px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
        }
        
        .contact-info:hover {
            transform: translateY(-10px);
            background: rgba(255,255,255,0.2);
        }
        
        .contact-info h3 {
            color: var(--accent-color);
            margin-bottom: 20px;
        }
        
        .contact-info p {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .contact-info i {
            width: 20px;
        }
        
        .social-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 30px;
        }
        
        .social-btn {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            border-radius: 10px;
            text-decoration: none;
            color: var(--white);
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .social-btn i {
            font-size: 1.8rem;
        }
        
        .social-btn:hover {
            transform: translateX(10px);
            border-color: var(--white);
        }
        
        .social-btn.whatsapp { background: var(--whatsapp); }
        .social-btn.whatsapp-group { background: var(--whatsapp); opacity: 0.9; }
        .social-btn.youtube { background: var(--youtube); }
        .social-btn.radio { background: var(--radio); }
        
        .radio-container {
            margin-top: 30px;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }
        
        .radio-player {
            width: 100%;
            margin-top: 15px;
            border-radius: 8px;
        }
        
        /* ===== CARTE ===== */
        .map-section {
            padding: 60px 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
        }
        
        body.dark-mode .map-section {
            background: linear-gradient(135deg, #2c3e50, #34495e);
        }
        
        .map-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }
        
        .map-info {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }
        
        body.dark-mode .map-info {
            background: var(--white);
            color: var(--text-color);
        }
        
        .map-info h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .coordinates {
            display: flex;
            gap: 15px;
            margin: 20px 0;
        }
        
        .coord-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            flex: 1;
        }
        
        body.dark-mode .coord-box {
            background: var(--gray-light);
        }
        
        .coord-box strong {
            display: block;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .map-frame {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow-hover);
            height: 400px;
        }
        
        .map-frame iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        /* ===== FOOTER ===== */
        footer {
            background: var(--primary-dark);
            color: var(--white);
            padding: 60px 0 20px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-section h3 {
            color: var(--accent-color);
            margin-bottom: 20px;
            font-size: 1.2rem;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-section h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background: var(--accent-color);
        }
        
        .footer-section p {
            color: rgba(255,255,255,0.8);
            margin-bottom: 10px;
        }
        
        .footer-section a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-section a:hover {
            color: var(--accent-color);
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .social-links a {
            color: var(--white);
            font-size: 2rem;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            color: var(--accent-color);
            transform: translateY(-5px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.9rem;
            opacity: 0.7;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: var(--primary-color);
                flex-direction: column;
                padding: 20px;
                text-align: center;
                gap: 15px;
            }
            
            .nav-links.active {
                display: flex;
            }
            
            .hero-content h2 {
                font-size: 2rem;
            }
            
            .hero-content p {
                font-size: 1.2rem;
            }
            
            .hero-logo {
                width: 120px;
                height: 120px;
            }
            
            .about-content,
            .map-container {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .quick-links {
                flex-direction: column;
                align-items: center;
            }
            
            .quick-link-card {
                max-width: 100%;
                width: 100%;
            }
            
            .top-social-container {
                justify-content: center;
                gap: 10px;
            }
            
            .top-social-btn {
                padding: 6px 12px;
                font-size: 0.75rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            .hero-content h2 {
                font-size: 1.5rem;
            }
            
            .section-title {
                font-size: 1.8rem;
            }
            
            .top-social-btn span {
                display: none;
            }
            
            .top-social-btn i {
                font-size: 1.2rem;
            }
            
            .top-social-btn {
                padding: 8px 12px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <img src="assets/images/logo.png" alt="Logo VISION D'AIGLES" onerror="this.src='https://via.placeholder.com/60x60/1e3c72/ffd700?text=VAT'">
                <div>
                    <h1>VISION D'AIGLES</h1>
                    <span>Tabernacle</span>
                </div>
            </div>
            <div class="menu-toggle" id="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
            <ul class="nav-links" id="nav-links">
                <li><a href="#accueil" class="active">Accueil</a></li>
                <li><a href="#apropos">À propos</a></li>
                <li><a href="#sermons">Prédications</a></li>
                <li><a href="#evenements">Événements</a></li>
                <li><a href="#pasteur">Le Pasteur</a></li>
                <li><a href="galerie.php">Galerie</a></li>
                <li><a href="bible.php">Bible</a></li>
                <li><a href="blog.php">Blog</a></li>
                <li><a href="faq.php">FAQ</a></li>
                <li><a href="#requetes">Requêtes</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><button id="darkModeToggle" class="dark-mode-toggle"><i class="fas fa-moon"></i></button></li>
            </ul>
        </nav>
    </header>
    
    <!-- BOUTONS SUIVEZ-NOUS -->
    <div class="top-social-bar">
        <div class="top-social-container">
            <a href="<?php echo htmlspecialchars($youtube_url); ?>" target="_blank" class="top-social-btn youtube">
                <i class="fab fa-youtube"></i> <span>YouTube</span>
            </a>
            <a href="<?php echo htmlspecialchars($radio_url); ?>" target="_blank" class="top-social-btn radio">
                <i class="fas fa-radio"></i> <span>Radio</span>
            </a>
            <a href="<?php echo htmlspecialchars($whatsapp_group); ?>" target="_blank" class="top-social-btn whatsapp">
                <i class="fab fa-whatsapp"></i> <span>Groupe</span>
            </a>
            <a href="<?php echo htmlspecialchars($whatsapp_direct); ?>" target="_blank" class="top-social-btn pasteur">
                <i class="fas fa-user-pastor"></i> <span>Pasteur</span>
            </a>
            <a href="downloads/app.php" class="top-social-btn app">
                <i class="fas fa-mobile-alt"></i> <span>Application</span>
            </a>
        </div>
    </div>
    
    <section id="accueil" class="hero">
        <div class="hero-content">
            <img src="assets/images/logo.png" alt="VISION D'AIGLES" class="hero-logo" onerror="this.src='https://via.placeholder.com/150x150/1e3c72/ffd700?text=VAT'">
            <h2><?php echo htmlspecialchars($site_title); ?></h2>
            <p>"<?php echo htmlspecialchars($main_verse); ?>"</p>
            <div>
                <a href="#apropos" class="btn">Découvrir</a>
                <a href="#requetes" class="btn btn-outline">Faire une requête</a>
            </div>
        </div>
    </section>
    
    <!-- Section Statistiques -->
    <section class="stats-section">
        <div class="container">
            <h2 class="section-title">Dieu est fidèle</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($stats['total_sermons_played'] ?? 0); ?></div>
                    <div class="stat-label">Prédications écoutées</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($stats['total_downloads'] ?? 0); ?></div>
                    <div class="stat-label">Téléchargements</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($stats['total_baptisms'] ?? 0); ?></div>
                    <div class="stat-label">Personnes baptisées</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($stats['total_members'] ?? 0); ?></div>
                    <div class="stat-label">Membres actifs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($stats['total_lives_touched'] ?? 0); ?></div>
                    <div class="stat-label">Vies touchées</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($stats['total_events'] ?? 0); ?></div>
                    <div class="stat-label">Événements organisés</div>
                </div>
            </div>
        </div>
    </section>
    
    <section id="apropos" class="about">
        <div class="container">
            <h2 class="section-title">À propos de nous</h2>
            <div class="about-content">
                <div class="about-text">
                    <p>Bienvenue à VISION D'AIGLES Tabernacle, un lieu de rencontre avec Dieu où chaque fidèle est appelé à prendre son envol spirituel.</p>
                    <p>Notre église est fondée sur la parole de Dieu et la puissance du Saint-Esprit. Nous croyons en une foi vivante et dynamique qui transforme des vies et impacte notre communauté.</p>
                    <p>Notre vision est de voir chaque membre s'élever comme un aigle, au-dessus des tempêtes de la vie, pour vivre la plénitude de la bénédiction divine.</p>
                </div>
                <div class="about-image">
                    <img src="assets/images/about1.jpeg" alt="Notre église VISION D'AIGLES Tabernacle" onerror="this.src='https://via.placeholder.com/500x400/1e3c72/ffd700?text=VISION+D\'AIGLES'">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Liens rapides -->
    <div class="quick-links">
        <a href="galerie.php" class="quick-link-card">
            <i class="fas fa-images"></i>
            <h3>Galerie</h3>
            <p>Photos et vidéos de nos événements</p>
        </a>
        <a href="bible.php" class="quick-link-card">
            <i class="fas fa-bible"></i>
            <h3>Bible en ligne</h3>
            <p>Lisez la parole de Dieu</p>
        </a>
        <a href="faq.php" class="quick-link-card">
            <i class="fas fa-question-circle"></i>
            <h3>FAQ</h3>
            <p>Réponses à vos questions</p>
        </a>
        <a href="blog.php" class="quick-link-card">
            <i class="fas fa-blog"></i>
            <h3>Blog</h3>
            <p>Articles et réflexions</p>
        </a>
    </div>
    
    <!-- Section Prédications -->
    <section id="sermons" class="sermons">
        <div class="container">
            <h2 class="section-title">Dernières prédications</h2>
            
            <?php if (empty($sermons)): ?>
                <div class="no-content-message" style="text-align: center; padding: 40px;">
                    <i class="fas fa-microphone-alt" style="font-size: 48px; color: var(--accent-color);"></i>
                    <p style="margin-top: 20px;">Aucune prédication disponible pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="sermons-grid">
                    <?php foreach($sermons as $sermon): ?>
                    <div class="sermon-card">
                        <div class="sermon-image">
                            <i class="fas fa-headphones"></i>
                        </div>
                        <div class="sermon-content">
                            <h3><?php echo htmlspecialchars($sermon['title']); ?></h3>
                            
                            <div class="sermon-meta">
                                <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($sermon['preacher']); ?></p>
                                <p><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($sermon['sermon_date'])); ?></p>
                                <p><i class="fas fa-heart"></i> <?php echo $sermon['likes'] ?? 0; ?></p>
                            </div>
                            
                            <?php if(!empty($sermon['file_path'])): ?>
                            <div class="audio-player">
                                <audio controls preload="none" class="plyr">
                                    <source src="<?php echo htmlspecialchars($sermon['file_path']); ?>" type="audio/mpeg">
                                    Votre navigateur ne supporte pas l'audio.
                                </audio>
                            </div>
                            
                            <div class="sermon-footer">
                                <span class="play-count">
                                    <i class="fas fa-headphones"></i> <?php echo $sermon['views'] ?? 0; ?> écoutes
                                </span>
                                <a href="<?php echo htmlspecialchars($sermon['file_path']); ?>" 
                                   class="btn" 
                                   download
                                   style="padding: 8px 15px; font-size: 0.9rem;">
                                    <i class="fas fa-download"></i> Télécharger
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center" style="text-align: center;">
                    <a href="sermons.php" class="btn">Voir toutes les prédications</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Section Événements -->
    <section id="evenements" class="events">
        <div class="container">
            <h2 class="section-title">Événements à venir</h2>
            
            <?php if (empty($events)): ?>
                <div class="no-content-message" style="text-align: center; padding: 40px;">
                    <i class="fas fa-calendar-times" style="font-size: 48px; color: var(--accent-color);"></i>
                    <p style="margin-top: 20px;">Aucun événement planifié pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="events-grid">
                    <?php foreach($events as $event): ?>
                    <div class="event-card">
                        <div class="event-date">
                            <?php echo strtoupper(date('d M', strtotime($event['event_date']))); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                        <p><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($event['event_date'])); ?></p>
                        <p><?php echo htmlspecialchars(substr($event['description'], 0, 100)); ?>...</p>
                        <a href="event.php?id=<?php echo $event['id']; ?>" class="btn" style="margin-top: 15px; padding: 8px 20px;">S'inscrire</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Section Témoignages -->
    <?php if (!empty($testimonials)): ?>
    <section class="testimonials-section">
        <div class="container">
            <h2 class="section-title">Ils témoignent</h2>
            <div class="testimonials-grid">
                <?php foreach ($testimonials as $t): ?>
                <div class="testimonial-card">
                    <i class="fas fa-quote-left"></i>
                    <p>"<?php echo htmlspecialchars($t['content']); ?>"</p>
                    <h4>- <?php echo htmlspecialchars($t['name']); ?></h4>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: center; margin-top: 30px;">
                <a href="temoignages.php" class="btn">Partager mon témoignage</a>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Section Blog -->
    <?php if (!empty($blog_posts)): ?>
    <section class="blog-section">
        <div class="container">
            <h2 class="section-title">Derniers articles</h2>
            <div class="blog-grid">
                <?php foreach ($blog_posts as $post): ?>
                <div class="blog-card">
                    <div class="blog-image">
                        <i class="fas fa-blog"></i>
                    </div>
                    <div class="blog-content">
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <div class="blog-meta">
                            <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($post['created_at'])); ?></span>
                            <span><i class="fas fa-eye"></i> <?php echo $post['views']; ?> vues</span>
                        </div>
                        <p class="blog-excerpt"><?php echo htmlspecialchars(substr($post['excerpt'] ?? strip_tags($post['content']), 0, 120)); ?>...</p>
                        <a href="article.php?slug=<?php echo $post['slug']; ?>" class="btn" style="padding: 8px 20px;">Lire la suite</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: center; margin-top: 30px;">
                <a href="blog.php" class="btn">Voir tous les articles</a>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Section LE PASTEUR -->
    <section id="pasteur" class="pastor-section">
        <div class="container">
            <h2 class="section-title">Le Pasteur</h2>
            <div class="pastor-card">
                <img src="<?php echo htmlspecialchars($pastor_photo); ?>" alt="<?php echo htmlspecialchars($pastor_name); ?>" onerror="this.src='assets/images/pastor-placeholder.jpg'">
                <h3><?php echo htmlspecialchars($pastor_name); ?></h3>
                <p class="pastor-title"><?php echo htmlspecialchars($pastor_title); ?></p>
                <p class="pastor-bio"><?php echo nl2br(htmlspecialchars($pastor_bio)); ?></p>
            </div>
        </div>
    </section>
    
    <!-- Section Requêtes -->
    <section id="requetes" class="request-section">
        <div class="request-container">
            <h2><i class="fas fa-praying-hands"></i> Envoyez votre requête</h2>
            <p style="text-align: center; margin-bottom: 30px;">Que ce soit pour une prière, un conseil, une visite ou un témoignage, notre équipe pastorale vous répondra personnellement.</p>
            
            <?php if($request_message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $request_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if($request_error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $request_error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="#requetes">
                <div class="form-row">
                    <div class="form-group">
                        <label>Votre nom *</label>
                        <input type="text" name="request_name" required placeholder="Ex: Jean Dupont">
                    </div>
                    
                    <div class="form-group">
                        <label>Votre email *</label>
                        <input type="email" name="request_email" required placeholder="exemple@email.com">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Téléphone (optionnel)</label>
                        <input type="tel" name="request_phone" placeholder="+242 XX XXX XXXX">
                    </div>
                    
                    <div class="form-group">
                        <label>Type de requête *</label>
                        <select name="request_type" required>
                            <option value="priere">🙏 Demande de prière</option>
                            <option value="conseil">💭 Conseil spirituel</option>
                            <option value="visite">🏠 Demande de visite</option>
                            <option value="temoignage">✨ Témoignage</option>
                            <option value="autre">📝 Autre</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Votre message *</label>
                    <textarea name="request_message" rows="5" required placeholder="Décrivez votre requête en toute confiance..."></textarea>
                </div>
                
                <button type="submit" name="submit_request" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Envoyer ma requête
                </button>
                
                <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: #999;">
                    <i class="fas fa-lock"></i> Vos informations restent confidentielles
                </p>
            </form>
        </div>
    </section>
    
    <!-- Section Contact et Réseaux Sociaux -->
    <section id="contact" class="contact">
        <div class="container">
            <h2 class="section-title">Contact et médias</h2>
            
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Nos coordonnées</h3>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($address); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($contact_phone); ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contact_email); ?></p>
                    <p><i class="fas fa-clock"></i> <?php echo htmlspecialchars($worship_times); ?></p>
                </div>
                
                <div class="contact-info">
                    <h3>Suivez-nous</h3>
                    
                    <div class="social-buttons">
                        <a href="<?php echo htmlspecialchars($whatsapp_direct); ?>" class="social-btn whatsapp" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                            <span>WhatsApp Direct</span>
                        </a>
                        
                        <a href="<?php echo htmlspecialchars($whatsapp_group); ?>" class="social-btn whatsapp-group" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                            <span>Groupe WhatsApp</span>
                        </a>
                        
                        <a href="<?php echo htmlspecialchars($youtube_url); ?>" class="social-btn youtube" target="_blank">
                            <i class="fab fa-youtube"></i>
                            <span>Chaîne YouTube</span>
                        </a>
                    </div>
                    
                    <div class="radio-container">
                        <h4><i class="fas fa-radio"></i> Radio en ligne</h4>
                        <audio controls class="radio-player">
                            <source src="<?php echo htmlspecialchars($radio_url); ?>" type="audio/mpeg">
                            Votre navigateur ne supporte pas la radio.
                        </audio>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Section Carte Interactive -->
    <section class="map-section">
        <div class="container">
            <h2 class="section-title">Nous trouver</h2>
            
            <div class="map-container">
                <div class="map-info">
                    <h3><i class="fas fa-map-marker-alt" style="color: var(--accent-color);"></i> VISION D'AIGLES Tabernacle</h3>
                    
                    <div class="coordinates">
                        <div class="coord-box">
                            <strong>Latitude</strong>
                            <span>-4.79233</span>
                        </div>
                        <div class="coord-box">
                            <strong>Longitude</strong>
                            <span>11.90528</span>
                        </div>
                    </div>
                    
                    <p><i class="fas fa-clock"></i> <?php echo htmlspecialchars($worship_times); ?></p>
                    
                    <div style="display: flex; gap: 15px; margin-top: 25px;">
                        <button onclick="getDirections()" class="btn" style="flex: 1;">
                            <i class="fas fa-directions"></i> Itinéraire
                        </button>
                        <a href="https://www.google.com/maps?q=-4.79233,11.90528" 
                           target="_blank" 
                           class="btn btn-outline" 
                           style="flex: 1;">
                            <i class="fas fa-external-link-alt"></i> Google Maps
                        </a>
                    </div>
                </div>
                
                <div class="map-frame">
                    <iframe 
                        src="https://www.openstreetmap.org/export/embed.html?bbox=11.87528%2C-4.82233%2C11.93528%2C-4.76233&amp;layer=mapnik&amp;marker=-4.79233%2C11.90528"
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Notifications Push -->
    <div id="pushNotification" class="push-notification" style="display: none;">
        <i class="fas fa-bell"></i>
        <span>Recevez les notifications des nouvelles prédications</span>
        <button id="subscribePush">Activer</button>
        <span class="close" onclick="document.getElementById('pushNotification').style.display='none'">&times;</span>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- Scripts -->
    <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    <script>
        // Menu mobile
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('nav-links').classList.toggle('active');
        });
        
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                    document.getElementById('nav-links').classList.remove('active');
                }
            });
        });
        
        // Lecteurs audio
        const players = Plyr.setup('.plyr', {
            controls: ['play', 'progress', 'current-time', 'mute', 'volume'],
            settings: ['speed'],
            speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] }
        });
        
        // Animation du header au scroll
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.background = 'linear-gradient(135deg, #0a1a2f 0%, #1e3c72 100%)';
                header.style.padding = '0.5rem 0';
            } else {
                header.style.background = 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)';
                header.style.padding = '1rem 0';
            }
        });
        
        // Itinéraire
        function getDirections() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;
                    const churchLat = -4.79233;
                    const churchLng = 11.90528;
                    window.open(`https://www.google.com/maps/dir/${userLat},${userLng}/${churchLat},${churchLng}`, '_blank');
                }, function() {
                    window.open('https://www.google.com/maps?q=-4.79233,11.90528', '_blank');
                });
            } else {
                window.open('https://www.google.com/maps?q=-4.79233,11.90528', '_blank');
            }
        }
        
        // Mode sombre
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
        
        darkModeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            if (document.body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
                darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                localStorage.setItem('darkMode', 'disabled');
                darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            }
        });
        
        // Notifications Push
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            navigator.serviceWorker.register('/service-worker.js')
                .then(function(registration) {
                    console.log('Service Worker enregistré');
                    
                    // Vérifier si déjà abonné
                    registration.pushManager.getSubscription()
                        .then(function(subscription) {
                            if (!subscription) {
                                setTimeout(function() {
                                    document.getElementById('pushNotification').style.display = 'flex';
                                }, 5000);
                            }
                        });
                });
        }
        
        document.getElementById('subscribePush')?.addEventListener('click', function() {
            navigator.serviceWorker.ready.then(function(registration) {
                registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array('VOTRE_CLE_PUBLIQUE')
                }).then(function(subscription) {
                    fetch('/api/subscribe_push.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(subscription)
                    }).then(function() {
                        document.getElementById('pushNotification').style.display = 'none';
                        alert('Notifications activées !');
                    });
                });
            });
        });
        
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
        
        // Marquer le lien actif
        window.addEventListener('scroll', function() {
            let current = '';
            const sections = document.querySelectorAll('section');
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= sectionTop - 200) {
                    current = section.getAttribute('id');
                }
            });
            
            document.querySelectorAll('.nav-links a[href^="#"]').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>