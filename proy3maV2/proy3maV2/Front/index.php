<?php
    session_start();

    // Definir los idiomas permitidos
    $idiomas_permitidos = array('es', 'en');

    // Determinar el idioma seleccionado o por defecto
    // Si se ha seleccionado un idioma a través de la URL, lo guardamos en la sesión
    if (isset($_GET['lang']) && in_array($_GET['lang'], $idiomas_permitidos)) {
        $_SESSION['lang'] = $_GET['lang']; // Guardamos el idioma seleccionado en la sesión
    } elseif (!isset($_SESSION['lang'])) {
        $_SESSION['lang'] = 'es'; // Idioma por defecto
    }

    // Cargar el archivo de idioma correspondiente
    $lang_file = $_SESSION['lang'] . '.php';
    if (file_exists("lang/" . $lang_file)) {
        include("lang/" . $lang_file); // Incluimos el archivo de idioma
    } else {
        echo "Error: Archivo de idioma no encontrado.";
        exit;
    }

    // Función para obtener la cadena traducida
    function traducir($clave) {
        global $lang; // Aseguramos que la variable $lang esté disponible
        return isset($lang[$clave]) ? $lang[$clave] : $clave; // Retorna la clave si no se encuentra la traducción
    }
?> 

<!DOCTYPE html>
<html lang="es">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tekoá - Gestión de Cooperativas</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        
        .floating-element {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-element:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .floating-element:nth-child(2) {
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .floating-element:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .navbar {
            transition: all 0.3s ease;
            padding: 1rem 0;
        }
        
        .navbar-scrolled {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 700;
            color: white !important;
        }
        
        .navbar-scrolled .navbar-brand {
            color: #333 !important;
        }
        
        .nav-link {
            color: white !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .navbar-scrolled .nav-link {
            color: #333 !important;
        }
        
        .nav-link:hover {
            color: #ffd700 !important;
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-outline-light {
            border: 2px solid white;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-left: 1rem;
        }
        
        .btn-outline-light:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3);
        }
        
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: none;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 1.8rem;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 3rem;
            text-align: center;
        }
        
        .section-subtitle {
            font-size: 1.1rem;
            color: #666;
            text-align: center;
            max-width: 600px;
            margin: 0 auto 4rem;
        }
        
        .about-section {
            padding: 100px 0;
            background: #f8f9fa;
        }
        
        .features-section {
            padding: 100px 0;
        }
        
        .cta-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
        }
        
        .footer {
            background: #333;
            color: white;
            padding: 50px 0 20px;
        }
        
        .footer-brand {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.5rem;
        }
        
        .footer-links a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: #667eea;
        }
        
        .social-links {
            margin-top: 1rem;
        }
        
        .social-links a {
            color: white;
            font-size: 1.5rem;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            color: #667eea;
            transform: translateY(-3px);
        }
        
        .stats-section {
            padding: 80px 0;
            background: #667eea;
            color: white;
        }
        
        .stat-item {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            display: block;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .btn-outline-light {
                margin-left: 0;
                margin-top: 1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top" style="background: transparent;">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-users"></i> Tekoá</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                 <ul class="navbar-nav mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-light" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-translate" viewBox="0 0 16 16">
                            <path d="M4.545 6.714 4.11 8H3l1.862-5h1.284L8 8H6.833l-.435-1.286zm1.634-.736L5.5 3.956h-.049l-.679 2.022z"/>
                            <path d="M0 2a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v3h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-3H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zm7.138 9.995q.289.451.63.846c-.748.575-1.673 1.001-2.768 1.292.178.217.451.635.555.867 1.125-.359 2.08-.844 2.886-1.494.777.665 1.739 1.165 2.93 1.472.133-.254.414-.673.629-.89-1.125-.253-2.057-.694-2.82-1.284.681-.747 1.222-1.651 1.621-2.757H14V8h-3v1.047h.765c-.318.844-.74 1.546-1.272 2.13a6 6 0 0 1-.415-.492 2 2 0 0 1-.94.31"/>
                        </svg>
                        <?php echo traducir('navbar_link6'); ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <li>
                            <a class="dropdown-item" href="?lang=es">
                                <img width="30" src="https://vectorflags.s3.amazonaws.com/flags/es-circle-01.png" alt="spanish flag">
                                Español
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="?lang=en">
                                <img width="30" src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/13/United-kingdom_flag_icon_round.svg/1200px-United-kingdom_flag_icon_round.svg.png" alt="english flag">
                                English
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../Backoffice/index.php">
                    <i class="fas fa-user-shield"></i> Admin
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#inicio"><?php echo traducir('navbar_link1'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about"><?php echo traducir('navbar_link2'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features"><?php echo traducir('navbar_link3'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact"><?php echo traducir('navbar_link4'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.html"><?php echo traducir('navbar_link5'); ?></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="inicio" class="hero-section">
        <div class="floating-elements">
            <div class="floating-element">
                <i class="fas fa-handshake" style="font-size: 5rem;"></i>
            </div>
            <div class="floating-element">
                <i class="fas fa-users" style="font-size: 4rem;"></i>
            </div>
            <div class="floating-element">
                <i class="fas fa-chart-line" style="font-size: 4.5rem;"></i>
            </div>
        </div>
        
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">Tekoá</h1>
                        <p class="hero-subtitle"><?php echo traducir('hero_text1'); ?></p>
                        <p class="mb-4"><?php echo traducir('hero_text2'); ?></p>
                        <div class="d-flex flex-column flex-md-row">
                            <button class="btn btn-primary" onclick="window.location.href='registro.html'"><?php echo traducir('hero_text3'); ?></button>
                            <button class="btn btn-outline-light" onclick="window.location.href='login.html'"><?php echo traducir('hero_text4'); ?></button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-building" style="font-size: 15rem; color: rgba(255,255,255,0.3);"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <span class="stat-label"><?php echo traducir('hero_text5');?></span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <span class="stat-number">10K+</span>
                        <span class="stat-label"><?php echo traducir('hero_text6');?></span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <span class="stat-number">99%</span>
                        <span class="stat-label"><?php echo traducir('hero_text7');?></span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <span class="stat-number">24hs</span>
                        <span class="stat-label"><?php echo traducir('hero_text8');?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="section-title text-start"><?php echo traducir('hero_text9');?></h2>
                    <p class="mb-4"><?php echo traducir('hero_text10');?></p>
                    <p class="mb-4"><?php echo traducir('hero_text11');?></p>
                    <div class="row">
                        <div class="col-6">
                            <h5><i class="fas fa-check-circle text-success"></i><?php echo traducir('hero_text12');?> </h5>
                            <p><?php echo traducir('hero_text13');?></p>
                        </div>
                        <div class="col-6">
                            <h5><i class="fas fa-shield-alt text-success"></i><?php echo traducir('hero_text14');?></h5>
                            <p><?php echo traducir('hero_text15');?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-globe-americas" style="font-size: 12rem; color: #667eea; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <h2 class="section-title"><?php echo traducir('hero_text16');?></h2>
            <p class="section-subtitle"><?php echo traducir('hero_text17');?></p>
            
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h4 class="text-center mb-3"><?php echo traducir('hero_text18');?></h4>
                        <p class="text-center"><?php echo traducir('hero_text19');?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h4 class="text-center mb-3"><?php echo traducir('hero_text20');?></h4>
                        <p class="text-center"><?php echo traducir('hero_text21');?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-vote-yea"></i>
                        </div>
                        <h4 class="text-center mb-3"><?php echo traducir('hero_text22');?></h4>
                        <p class="text-center"><?php echo traducir('hero_text23');?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h4 class="text-center mb-3"><?php echo traducir('hero_text24');?></h4>
                        <p class="text-center"><?php echo traducir('hero_text25');?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h4 class="text-center mb-3"><?php echo traducir('hero_text26');?></h4>
                        <p class="text-center"><?php echo traducir('hero_text27');?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4 class="text-center mb-3"><?php echo traducir('hero_text28');?></h4>
                        <p class="text-center"><?php echo traducir('hero_text29');?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="mb-4"><?php echo traducir('hero_text30');?></h2>
            <p class="mb-4" style="font-size: 1.2rem;"><?php echo traducir('hero_text31');?></p>
            <button class="btn btn-light btn-lg" onclick="window.location.href='registro.html'" style="color: #667eea; font-weight: 600;"><?php echo traducir('hero_text32');?></button>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="footer-brand">
                        <i class="fas fa-users"></i> Tekoá by Incode
                    </div>
                    <p><?php echo traducir('footer_text1');?></p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2">
                    <h5><?php echo traducir('footer_text2');?></h5>
                    <ul class="footer-links">
                        <li><a href="#features"><?php echo traducir('footer_text3');?></a></li>
                        <li><a href="#"><?php echo traducir('footer_text4');?></a></li>
                        <li><a href="#"><?php echo traducir('footer_text5');?></a></li>
                        <li><a href="#"><?php echo traducir('footer_text6');?></a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5><?php echo traducir('footer_text7');?></h5>
                    <ul class="footer-links">
                        <li><a href="#about"><?php echo traducir('footer_text8');?></a></li>
                        <li><a href="#"><?php echo traducir('footer_text9');?></a></li>
                        <li><a href="#"><?php echo traducir('footer_text10');?></a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5>Soporte</h5>
                    <ul class="footer-links">
                        <li><a href="#"><?php echo traducir('footer_text12');?></a></li>
                        <li><a href="#"><?php echo traducir('footer_text13');?></a></li>
                        <li><a href="#"><?php echo traducir('footer_text14');?></a></li>
                        <li><a href="#"><?php echo traducir('footer_text15');?></a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5>Legal</h5>
                    <ul class="footer-links">
                        <li><a href="#"><?php echo traducir('footer_text16');?></a></li>
                        <li><a href="#"><?php echo traducir('footer_text17');?></a></li>
                        <li><a href="#"><?php echo traducir('footer_text18');?></a></li>
                        <li><a href="#"><?php echo traducir('footer_text19');?></a></li>
                    </ul>
                </div>
            </div>
            <hr style="margin: 2rem 0; border-color: #555;">
            <div class="row">
                <div class="col-12 text-center">
                    <p>&copy;<?php echo traducir('footer_text20');?> </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Counter animation
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
                const increment = target / 100;
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    const suffix = counter.textContent.replace(/[\d]/g, '');
                    counter.textContent = Math.floor(current) + suffix;
                }, 20);
            });
        }

        // Intersection Observer for counter animation
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        });

        const statsSection = document.querySelector('.stats-section');
        if (statsSection) {
            observer.observe(statsSection);
        }
    </script>
</body>
</html>