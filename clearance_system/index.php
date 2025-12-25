<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Clearance System - University Clearance Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #28a745;
            --warning: #ffc107;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 100px 0;
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: bottom;
        }
        
        .feature-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }
        
        .step-card {
            position: relative;
            padding: 30px;
            border-radius: 15px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .step-number {
            position: absolute;
            top: -20px;
            left: -20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .stats-number {
            font-size: 3rem;
            font-weight: bold;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        
        .login-box {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            transition: color 0.3s;
        }
        
        .nav-link:hover {
            color: white !important;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .btn-gradient:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        .section-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 40px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 2px;
        }
        
        footer {
            background: var(--dark);
            color: white;
            padding: 60px 0 20px;
        }
        
        .footer-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background: rgba(0,0,0,0.3); backdrop-filter: blur(10px);">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap"></i> Smart Clearance
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="login.php" class="btn btn-gradient">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4 animate__animated animate__fadeInUp">
                        Smart Clearance System
                    </h1>
                    <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                        Streamlining university clearance processes for students, staff, and administrators. 
                        Experience seamless, efficient, and transparent clearance management.
                    </p>
                    <div class="animate__animated animate__fadeInUp animate__delay-2s">
                        <a href="login.php" class="btn btn-gradient btn-lg me-3">
                            <i class="fas fa-sign-in-alt"></i> Get Started
                        </a>
                        <a href="#how-it-works" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-play-circle"></i> How It Works
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="floating-animation animate__animated animate__fadeIn">
                        <img src="https://cdn.pixabay.com/photo/2018/09/27/09/22/artificial-intelligence-3706562_1280.png" 
                             alt="Clearance System Illustration" 
                             class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5" style="background: #f8f9fa;">
        <div class="container py-5">
            <h2 class="text-center section-title">Key Features</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-bolt fa-2x"></i>
                            </div>
                            <h4 class="card-title">Fast Processing</h4>
                            <p class="card-text">
                                Reduce clearance processing time from weeks to just hours. 
                                Automated workflows ensure speedy approvals.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt fa-2x"></i>
                            </div>
                            <h4 class="card-title">Secure & Reliable</h4>
                            <p class="card-text">
                                Enterprise-grade security with role-based access control, 
                                audit trails, and data encryption.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                            <h4 class="card-title">Real-time Tracking</h4>
                            <p class="card-text">
                                Monitor clearance status in real-time. Get instant notifications 
                                for updates and approvals.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-mobile-alt fa-2x"></i>
                            </div>
                            <h4 class="card-title">Mobile Friendly</h4>
                            <p class="card-text">
                                Access the system from any device. Responsive design works 
                                perfectly on smartphones and tablets.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-file-pdf fa-2x"></i>
                            </div>
                            <h4 class="card-title">Digital Reports</h4>
                            <p class="card-text">
                                Generate and download clearance certificates in PDF format. 
                                Complete digital documentation.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-headset fa-2x"></i>
                            </div>
                            <h4 class="card-title">24/7 Support</h4>
                            <p class="card-text">
                                Round-the-clock technical support and comprehensive 
                                documentation for all users.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-5">
        <div class="container py-5">
            <h2 class="text-center section-title">How It Works</h2>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="step-card h-100">
                        <div class="step-number">1</div>
                        <div class="text-center">
                            <i class="fas fa-user-plus fa-3x mb-3 text-primary"></i>
                            <h4>Login</h4>
                            <p>Students, staff, and administrators login with their credentials</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="step-card h-100">
                        <div class="step-number">2</div>
                        <div class="text-center">
                            <i class="fas fa-paper-plane fa-3x mb-3 text-primary"></i>
                            <h4>Request</h4>
                            <p>Students submit clearance requests to relevant departments</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="step-card h-100">
                        <div class="step-number">3</div>
                        <div class="text-center">
                            <i class="fas fa-check-circle fa-3x mb-3 text-primary"></i>
                            <h4>Approve</h4>
                            <p>Department staff review and approve clearance requests</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="step-card h-100">
                        <div class="step-number">4</div>
                        <div class="text-center">
                            <i class="fas fa-certificate fa-3x mb-3 text-primary"></i>
                            <h4>Complete</h4>
                            <p>Download clearance certificate after all approvals</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5" style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white;">
        <div class="container py-5">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <div class="stats-card" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                        <div class="stats-number">5000+</div>
                        <h5>Students Served</h5>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stats-card" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                        <div class="stats-number">95%</div>
                        <h5>Faster Processing</h5>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stats-card" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                        <div class="stats-number">100%</div>
                        <h5>Digital Process</h5>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stats-card" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                        <div class="stats-number">24/7</div>
                        <h5>System Availability</h5>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Box Section -->
    <section id="login" class="py-5">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="login-box">
                        <h3 class="text-center mb-4">Ready to Get Started?</h3>
                        <p class="text-center mb-4 text-muted">
                            Login to access the Smart Clearance System
                        </p>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <a href="login.php" class="btn btn-outline-primary w-100 py-3">
                                    <i class="fas fa-user-graduate"></i><br>
                                    Student Login
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="login.php" class="btn btn-outline-success w-100 py-3">
                                    <i class="fas fa-user-tie"></i><br>
                                    Staff Login
                                </a>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <a href="login.php" class="btn btn-gradient btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Go to Login Page
                            </a>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="text-muted">
                                Need help? <a href="#contact">Contact support</a><br>
                                <small>First time users should contact their department for credentials</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h4 class="mb-4">
                        <i class="fas fa-graduation-cap"></i> Smart Clearance
                    </h4>
                    <p>
                        A comprehensive digital solution for managing university clearance processes.
                        Making administrative tasks simpler, faster, and more efficient.
                    </p>
                    <div class="mt-4">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="mb-4">Quick Links</h5>
                    <div class="footer-links">
                        <p><a href="#home">Home</a></p>
                        <p><a href="#features">Features</a></p>
                        <p><a href="#how-it-works">How It Works</a></p>
                        <p><a href="#about">About</a></p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="mb-4">User Types</h5>
                    <div class="footer-links">
                        <p><a href="login.php">Student Portal</a></p>
                        <p><a href="login.php">Staff Portal</a></p>
                        <p><a href="login.php">Admin Portal</a></p>
                    </div>
                </div>
                
                <div class="col-lg-3 mb-4">
                    <h5 class="mb-4">Contact Us</h5>
                    <div class="footer-links">
                        <p><i class="fas fa-map-marker-alt me-2"></i> University Campus</p>
                        <p><i class="fas fa-phone me-2"></i> +1 (555) 123-4567</p>
                        <p><i class="fas fa-envelope me-2"></i> support@university.edu</p>
                        <p><i class="fas fa-clock me-2"></i> Support: 24/7</p>
                    </div>
                </div>
            </div>
            
            <hr style="border-color: rgba(255,255,255,0.1);">
            
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Smart Clearance System. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <a href="#" class="text-white me-3">Privacy Policy</a>
                        <a href="#" class="text-white">Terms of Service</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Navbar background change on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(0,0,0,0.9)';
                navbar.style.backdropFilter = 'blur(10px)';
            } else {
                navbar.style.background = 'rgba(0,0,0,0.3)';
                navbar.style.backdropFilter = 'blur(10px)';
            }
        });
        
        // Animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                }
            });
        }, observerOptions);
        
        // Observe all feature cards and step cards
        document.querySelectorAll('.feature-card, .step-card').forEach(card => {
            observer.observe(card);
        });
        
        // Counter animation for stats
        function animateCounter(element, target, duration = 2000) {
            let start = 0;
            const increment = target / (duration / 16);
            const timer = setInterval(() => {
                start += increment;
                if (start >= target) {
                    element.textContent = target + (element.textContent.includes('%') ? '%' : '+');
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(start) + (element.textContent.includes('%') ? '%' : '+');
                }
            }, 16);
        }
        
        // Start counters when stats section is in view
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.stats-number');
                    counters.forEach(counter => {
                        const target = parseInt(counter.textContent);
                        animateCounter(counter, target);
                    });
                    statsObserver.unobserve(entry.target);
                }
            });
        });
        
        const statsSection = document.querySelector('section.py-5:nth-of-type(4)');
        if (statsSection) {
            statsObserver.observe(statsSection);
        }
    </script>
</body>
</html>