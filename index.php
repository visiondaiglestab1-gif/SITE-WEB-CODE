<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Récupérer les paramètres
$settings = [];
$query = "SELECT * FROM settings";
$stmt = $db->query($query);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Récupérer les derniers sermons
$query = "SELECT * FROM sermons ORDER BY date DESC LIMIT 6";
$sermons = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les événements
$query = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date LIMIT 4";
$events = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les pasteurs
$query = "SELECT * FROM pastors ORDER BY display_order";
$pastors = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire de requête
$request_message = '';
$request_error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_request'])) {
    $name = trim($_POST['request_name'] ?? '');
    $email = trim($_POST['request_email'] ?? '');
    $phone = trim($_POST['request_phone'] ?? '');
    $request_type = $_POST['request_type'] ?? 'autre';
    $message = trim($_POST['request_message'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    if(empty($name) || empty($email) || empty($message)) {
        $request_error = "Veuillez remplir tous les champs obligatoires";
    } else {
        try {
            $sql = "INSERT INTO user_requests (name, email, phone, request_type, message, ip_address) 
                    VALUES (:name, :email, :phone, :request_type, :message, :ip)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':request_type' => $request_type,
                ':message' => $message,
                ':ip' => $ip
            ]);
            $request_message = "Votre requête a été envoyée avec succès ! Nous vous répondrons dans les plus brefs délais.";
        } catch(PDOException $e) {
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
    <title><?php echo htmlspecialchars($settings['site_title'] ?? 'VISION D\'AIGLES Tabernacle'); ?></title>
    
    <!-- Meta tags -->
    <meta name="description" content="<?php echo htmlspecialchars($settings['site_description'] ?? 'VISION D\'AIGLES Tabernacle - Une église de foi et de puissance'); ?>">
    <meta name="keywords" content="église, vision d'aigles, tabernacle, culte, prédications, bible, galerie, FAQ">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/">
    <meta property="og:title" content="<?php echo htmlspecialchars($settings['site_title'] ?? 'VISION D\'AIGLES Tabernacle'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($settings['site_description'] ?? 'VISION D\'AIGLES Tabernacle - Une église de foi et de puissance'); ?>">
    <meta property="og:image" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/images/logo.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="images/logo.png">
    
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
        }

        /* ===== RESET ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

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

        header:hover {
            box-shadow: var(--shadow-hover);
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
            transition: transform 0.3s;
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

        .admin-link {
            background: var(--accent-color);
            color: var(--primary-color) !important;
            padding: 8px 15px !important;
            border-radius: 5px;
        }

        .admin-link:hover {
            background: var(--white);
            transform: translateY(-2px) !important;
        }

        .admin-link::after {
            display: none;
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

        /* ===== HERO ===== */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://raw.githubusercontent.com/yourhopecg-dev/IMA/main/about.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--white);
            margin-top: 80px;
        }

        .hero-content {
            max-width: 800px;
            padding: 0 20px;
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
            animation: fadeIn 1s;
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
            animation: slideInLeft 1s;
        }

        .about-text p {
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .about-image {
            flex: 1;
            animation: slideInRight 1s;
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

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* ===== CARTES DE LIENS ===== */
        .quick-links {
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

        .quick-link-card {
            background: translateY(20px);
            padding: 0px 0px;
            border-radius: 20px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s;
            text-decoration: none;
            color: var(--text-color);
        }

        .quick-link-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
            border-color: var(--white);
        }

        .quick-link-card i {
            background: transparent;
            border: 0px solid var(--accent-color);
            color: var(--white);
        }

        .quick-link-card h3 {
            background: var(--accent-color);
            color: var(--primary-color);
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

        .sermon-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .sermon-card:hover img {
            transform: scale(1.1);
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

        /* ===== PASTEURS ===== */
        .pastors {
            background: var(--white);
        }

        .pastors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
        }

        .pastor-card {
            text-align: center;
            padding: 30px;
            background: var(--gray-light);
            border-radius: 10px;
            transition: all 0.3s;
        }

        .pastor-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .pastor-card img {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--accent-color);
            margin-bottom: 20px;
            transition: all 0.5s;
        }

        .pastor-card:hover img {
            transform: rotate(360deg) scale(1.1);
        }

        .pastor-card h3 {
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .pastor-title {
            color: var(--accent-color);
            font-weight: 600;
        }

        .pastor-social {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .pastor-social a {
            color: var(--primary-color);
            font-size: 1.2rem;
            transition: all 0.3s;
        }

        .pastor-social a:hover {
            color: var(--accent-color);
            transform: translateY(-3px);
        }

        /* ===== REQUÊTES ===== */
        .request-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            padding: 60px 20px;
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
        }

        /* ===== CARTE ===== */
        .map-section {
            padding: 60px 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
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
        }

        /* ===== FOOTER ===== */
        footer {
            background: var(--primary-dark);
            color: var(--white);
            text-align: center;
            padding: 40px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: var(--white);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
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

            .about-content,
            .map-container {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <img src="images/logo.png" alt="Logo VISION D'AIGLES">
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
                <li><a href="#pasteurs">Pasteurs</a></li>
                <li><a href="gallery.php">Galerie</a></li>
                <li><a href="faq.php">FAQ</a></li>
                <li><a href="bible.php">Bible</a></li>
                <li><a href="#requetes">Requêtes</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="downloads/app.php">Application</a></li>
                <?php if(isset($_SESSION['admin_id'])): ?>
                    <li><a href="admin/dashboard.php" class="admin-link"><i class="fas fa-cog"></i> Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <section id="accueil" class="hero">
        <div class="hero-content">
            <h2><?php echo htmlspecialchars($settings['site_title'] ?? 'VISION D\'AIGLES Tabernacle'); ?></h2>
            <p>"<?php echo htmlspecialchars($settings['main_verse'] ?? 'Ésaïe 40:31'); ?>"</p>
            <div>
                <a href="#apropos" class="btn">Découvrir</a>
                <a href="#requetes" class="btn btn-outline">Faire une requête</a>
                <a href="downloads/app.php" class="quick-link-card">
    			<i class="fas fa-mobile-alt"></i>
   				<h3>Cliquez ici pour Télécharger notre Application mobile</h3>
				</a>
            </div>
        </div>
    </section>

    <section id="apropos" class="about">
        <div class="container">
            <h2 class="section-title">À propos de nous</h2>
            <div class="about-content">
                <div class="about-text">
                    <p><?php echo htmlspecialchars($settings['site_description'] ?? 'Bienvenue à VISION D\'AIGLES Tabernacle, un lieu de rencontre avec Dieu où chaque fidèle est appelé à prendre son envol spirituel.'); ?></p>
                    <p>Notre église est fondée sur la parole de Dieu et la puissance du Saint-Esprit. Nous croyons en une foi vivante et dynamique qui transforme des vies et impacte notre communauté.</p>
                    <p>Notre vision est de voir chaque membre s'élever comme un aigle, au-dessus des tempêtes de la vie, pour vivre la plénitude de la bénédiction divine.</p>
                </div>
                <div class="about-image">
                    <img src="images/about.jpeg" alt="Notre église VISION D'AIGLES Tabernacle">
                </div>
            </div>
        </div>
    </section>

    <!-- Liens rapides vers les nouvelles pages -->
    <div class="container">
        <div class="quick-links">
            <a href="gallery.php" class="quick-link-card">
                <i class="fas fa-images"></i>
                <h3>Galerie</h3>
                <p>Photos et vidéos de nos événements</p>
            </a>
            <a href="faq.php" class="quick-link-card">
                <i class="fas fa-question-circle"></i>
                <h3>FAQ</h3>
                <p>Réponses à vos questions</p>
            </a>
            <a href="bible.php" class="quick-link-card">
                <i class="fas fa-bible"></i>
                <h3>Bible en ligne</h3>
                <p>Lisez la parole de Dieu</p>
            </a>
        </div>
    </div>

    <!-- Section Prédications -->
    <section id="sermons" class="sermons">
        <div class="container">
            <h2 class="section-title">Dernières prédications</h2>
            
            <?php if (empty($sermons)): ?>
                <div class="no-content-message">
                    <i class="fas fa-microphone-alt"></i>
                    <p>Aucune prédication disponible pour le moment.</p>
                    <?php if(isset($_SESSION['admin_id'])): ?>
                        <a href="admin/add_sermon.php" class="btn btn-primary">Ajouter une prédication</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="sermons-grid">
                    <?php foreach($sermons as $sermon): ?>
                    <div class="sermon-card" data-id="<?php echo $sermon['id']; ?>">
                        <?php if(!empty($sermon['image_file']) && file_exists('uploads/' . $sermon['image_file'])): ?>
                        <img src="uploads/<?php echo $sermon['image_file']; ?>" alt="<?php echo htmlspecialchars($sermon['title']); ?>" loading="lazy">
                        <?php endif; ?>
                        
                        <div class="sermon-content">
                            <h3><?php echo htmlspecialchars($sermon['title']); ?></h3>
                            
                            <div class="sermon-meta">
                                <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($sermon['preacher']); ?></p>
                                <p><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($sermon['date'])); ?></p>
                                <?php if(!empty($sermon['bible_verse'])): ?>
                                <p><i class="fas fa-bible"></i> <?php echo htmlspecialchars($sermon['bible_verse']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if(!empty($sermon['audio_file']) && file_exists('uploads/sermons/' . $sermon['audio_file'])): ?>
                            <div class="audio-player">
                                <audio controls preload="none" class="plyr">
                                    <source src="uploads/sermons/<?php echo $sermon['audio_file']; ?>" type="audio/mpeg">
                                    Votre navigateur ne supporte pas l'audio.
                                </audio>
                            </div>
                            
                            <div class="sermon-footer">
                                <span class="play-count">
                                    <i class="fas fa-headphones"></i> <?php echo $sermon['plays'] ?? 0; ?> écoutes
                                </span>
                                <a href="uploads/sermons/<?php echo $sermon['audio_file']; ?>" 
                                   class="btn btn-primary" 
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
                
                <?php if(count($sermons) >= 6): ?>
                <div class="text-center">
                    <a href="sermons.php" class="btn btn-primary">Voir toutes les prédications</a>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section Événements -->
    <section id="evenements" class="events">
        <div class="container">
            <h2 class="section-title">Événements à venir</h2>
            
            <?php if (empty($events)): ?>
                <div class="no-content-message light">
                    <i class="fas fa-calendar-times"></i>
                    <p>Aucun événement planifié pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="events-grid">
                    <?php foreach($events as $event): ?>
                    <div class="event-card">
                        <div class="event-date">
                            <?php echo strtoupper(date('d M', strtotime($event['event_date']))); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                        <?php if(!empty($event['event_time'])): ?>
                        <p><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($event['event_time'])); ?></p>
                        <?php endif; ?>
                        <?php if(!empty($event['description'])): ?>
                        <p><?php echo htmlspecialchars($event['description']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section Pasteurs -->
    <section id="pasteurs" class="pastors">
        <div class="container">
            <h2 class="section-title">Nos pasteurs</h2>
            
            <?php if (empty($pastors)): ?>
                <div class="no-content-message">
                    <i class="fas fa-users"></i>
                    <p>La liste des pasteurs sera bientôt disponible.</p>
                </div>
            <?php else: ?>
                <div class="pastors-grid">
                    <?php foreach($pastors as $pastor): ?>
                    <div class="pastor-card">
                        <?php if(!empty($pastor['image_file']) && file_exists('uploads/' . $pastor['image_file'])): ?>
                        <img src="uploads/<?php echo $pastor['image_file']; ?>" alt="<?php echo htmlspecialchars($pastor['name']); ?>" loading="lazy">
                        <?php else: ?>
                        <img src="images/pastor-placeholder.jpg" alt="<?php echo htmlspecialchars($pastor['name']); ?>">
                        <?php endif; ?>
                        
                        <h3><?php echo htmlspecialchars($pastor['name']); ?></h3>
                        <p class="pastor-title"><?php echo htmlspecialchars($pastor['title'] ?? 'Pasteur'); ?></p>
                        
                        <?php if(!empty($pastor['bio'])): ?>
                        <p class="bio"><?php echo htmlspecialchars(substr($pastor['bio'], 0, 100)); ?>...</p>
                        <?php endif; ?>
                        
                        <div class="pastor-social">
                            <?php if(!empty($pastor['facebook'])): ?>
                            <a href="<?php echo $pastor['facebook']; ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                            <?php endif; ?>
                            <?php if(!empty($pastor['twitter'])): ?>
                            <a href="<?php echo $pastor['twitter']; ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                            <?php endif; ?>
                            <?php if(!empty($pastor['instagram'])): ?>
                            <a href="<?php echo $pastor['instagram']; ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Section Newsletter avec choix email/WhatsApp -->
<section class="newsletter-section" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 60px 20px; text-align: center;">
    <div class="container">
        <h2 style="font-size: 2.5rem; margin-bottom: 20px;">
            <i class="fas fa-envelope-open-text"></i> Restez connecté
        </h2>
        <p style="font-size: 1.2rem; margin-bottom: 30px; opacity: 0.9;">
            Recevez les dernières nouvelles, prédications et événements de notre église<br>
            <small>Choisissez votre moyen de contact préféré</small>
        </p>
        
        <div id="newsletterMessage"></div>
        
        <form id="newsletterForm" style="max-width: 500px; margin: 0 auto;" onsubmit="subscribeNewsletter(event)">
            <div style="background: rgba(255,255,255,0.1); padding: 30px; border-radius: 15px; backdrop-filter: blur(10px);">
                
                <!-- Nom (optionnel) -->
                <div style="margin-bottom: 20px; text-align: left;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                        <i class="fas fa-user"></i> Votre prénom (optionnel)
                    </label>
                    <input type="text" name="name" placeholder="Ex: Jean" 
                           style="width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 1rem;">
                </div>
                
                <!-- Choix du mode de contact -->
                <div style="margin-bottom: 20px; text-align: left;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                        <i class="fas fa-address-card"></i> Comment souhaitez-vous être contacté ?
                    </label>
                    <div style="display: flex; gap: 20px; background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="radio" name="preferred" value="email" checked onclick="toggleContactField('email')">
                            <i class="fas fa-envelope" style="font-size: 1.2rem;"></i> Email
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="radio" name="preferred" value="whatsapp" onclick="toggleContactField('whatsapp')">
                            <i class="fab fa-whatsapp" style="font-size: 1.2rem;"></i> WhatsApp
                        </label>
                    </div>
                </div>
                
                <!-- Champ Email -->
                <div id="emailField" style="margin-bottom: 20px; text-align: left;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                        <i class="fas fa-envelope"></i> Votre adresse email
                    </label>
                    <input type="email" name="email" placeholder="exemple@email.com" 
                           style="width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 1rem;">
                </div>
                
                <!-- Champ WhatsApp -->
                <div id="whatsappField" style="margin-bottom: 20px; text-align: left; display: none;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                        <i class="fab fa-whatsapp"></i> Votre numéro WhatsApp
                    </label>
                    <input type="tel" name="phone" placeholder="+242 XX XXX XXXX" 
                           style="width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 1rem;">
                    <small style="display: block; margin-top: 5px; opacity: 0.8;">
                        Format international : +242061234567
                    </small>
                </div>
                
                <button type="submit" 
                        style="width: 100%; padding: 15px; background: #ffd700; color: #1e3c72; border: none; border-radius: 8px; font-weight: bold; font-size: 1.1rem; cursor: pointer; transition: all 0.3s;">
                    <i class="fas fa-paper-plane"></i> S'abonner à la newsletter
                </button>
                
                <p style="margin-top: 20px; font-size: 0.85rem; opacity: 0.8;">
                    <i class="fas fa-lock"></i> Vos informations sont confidentielles. Désabonnement possible à tout moment.
                </p>
            </div>
        </form>
    </div>
</section>

<script>
function toggleContactField(type) {
    const emailField = document.getElementById('emailField');
    const whatsappField = document.getElementById('whatsappField');
    
    if (type === 'email') {
        emailField.style.display = 'block';
        whatsappField.style.display = 'none';
        document.querySelector('input[name="email"]').required = true;
        document.querySelector('input[name="phone"]').required = false;
    } else {
        emailField.style.display = 'none';
        whatsappField.style.display = 'block';
        document.querySelector('input[name="email"]').required = false;
        document.querySelector('input[name="phone"]').required = true;
    }
}

function subscribeNewsletter(event) {
    event.preventDefault();
    
    const form = document.getElementById('newsletterForm');
    const formData = new FormData(form);
    const messageDiv = document.getElementById('newsletterMessage');
    
    // Déterminer quel champ est requis
    const preferred = formData.get('preferred');
    const email = formData.get('email');
    const phone = formData.get('phone');
    
    if (preferred === 'email' && !email) {
        alert('Veuillez entrer votre adresse email');
        return;
    }
    
    if (preferred === 'whatsapp' && !phone) {
        alert('Veuillez entrer votre numéro WhatsApp');
        return;
    }
    
    // Afficher le chargement
    messageDiv.innerHTML = '<div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px; margin-bottom: 20px;">' +
        '<i class="fas fa-spinner fa-spin"></i> Inscription en cours...</div>';
    
    // Envoyer la requête
    fetch('api/subscribe_newsletter.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            name: formData.get('name'),
            email: email,
            phone: phone,
            preferred: preferred
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.innerHTML = '<div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">' +
                '<i class="fas fa-check-circle"></i> ' + data.message + '</div>';
            form.reset();
        } else {
            messageDiv.innerHTML = '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">' +
                '<i class="fas fa-exclamation-circle"></i> ' + data.error + '</div>';
        }
    })
    .catch(error => {
        messageDiv.innerHTML = '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">' +
            '<i class="fas fa-exclamation-circle"></i> Une erreur est survenue. Veuillez réessayer.</div>';
    });
}
</script>

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
                            <option value="prière">🙏 Demande de prière</option>
                            <option value="conseil">💭 Conseil spirituel</option>
                            <option value="visite">🏠 Demande de visite</option>
                            <option value="témoignage">✨ Témoignage</option>
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
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($settings['address'] ?? 'Adresse à venir'); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($settings['contact_phone'] ?? '+242 06 629 30 93'); ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($settings['contact_email'] ?? 'contact@visiondaigles.org'); ?></p>
                    <p><i class="fas fa-clock"></i> Horaires : <?php echo htmlspecialchars($settings['worship_times'] ?? 'Dimanche 10h'); ?></p>
                </div>
                
                <div class="contact-info">
                    <h3>Suivez-nous</h3>
                    
                    <div class="social-buttons">
                        <!-- WhatsApp Direct -->
                        <a href="https://wa.me/242066293093?text=Bonjour%2C%20je%20souhaite%20avoir%20des%20informations" 
                           class="social-btn whatsapp" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                            <span>WhatsApp Direct</span>
                        </a>
                        
                        <!-- Groupe WhatsApp -->
                        <a href="https://chat.whatsapp.com/HCikWDquIvw4qNfDGjErRC" 
                           class="social-btn whatsapp-group" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                            <span>Groupe WhatsApp</span>
                        </a>
                        
                        <!-- YouTube -->
                        <a href="https://www.youtube.com/@AyezFoienDieu" 
                           class="social-btn youtube" target="_blank">
                            <i class="fab fa-youtube"></i>
                            <span>Chaîne YouTube</span>
                        </a>
                    </div>
                    
                    <!-- Radio en ligne -->
                    <div class="radio-container">
                        <h4><i class="fas fa-radio"></i> Radio en ligne</h4>
                        <audio controls class="radio-player">
                            <source src="https://vateglise.ismyradio.com/stream" type="audio/mpeg">
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
                    
                    <p><i class="fas fa-clock" style="color: var(--accent-color);"></i> <?php echo htmlspecialchars($settings['worship_times'] ?? 'Dimanche 10h00 - Mercredi 18h30'); ?></p>
                    
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
                        src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d3979.9123456789!2d11.90328!3d-4.79233!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zNMKwNDcnMzIuNCJTIDExwrA1NCcxOS4wIkU!5e0!3m2!1sfr!2scg!4v1234567890"
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-links">
            <a href="#accueil">Accueil</a>
            <a href="#apropos">À propos</a>
            <a href="#sermons">Prédications</a>
            <a href="#evenements">Événements</a>
            <a href="#pasteurs">Pasteurs</a>
            <a href="gallery.php">Galerie</a>
            <a href="faq.php">FAQ</a>
            <a href="bible.php">Bible</a>
            <a href="#requetes">Requêtes</a>
            <a href="#contact">Contact</a>
        </div>
        <div class="social-links">
            <a href="https://wa.me/242066293093" target="_blank"><i class="fab fa-whatsapp"></i></a>
            <a href="https://chat.whatsapp.com/HCikWDquIvw4qNfDGjErRC" target="_blank"><i class="fab fa-whatsapp"></i></a>
            <a href="https://www.youtube.com/@AyezFoienDieu" target="_blank"><i class="fab fa-youtube"></i></a>
            <a href="https://vateglise.ismyradio.com/player" target="_blank"><i class="fas fa-radio"></i></a>
        </div>
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_title'] ?? 'VISION D\'AIGLES Tabernacle'); ?>. Tous droits réservés.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    <script>
        // Menu mobile
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('nav-links').classList.toggle('active');
        });

        // Smooth scroll pour les ancres internes
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                    // Fermer le menu mobile
                    document.getElementById('nav-links').classList.remove('active');
                }
            });
        });

        // Initialiser les lecteurs audio
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

        // Fonction pour l'itinéraire
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

        // Marquer le lien actif
        window.addEventListener('scroll', function() {
            let current = '';
            const sections = document.querySelectorAll('section');
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
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