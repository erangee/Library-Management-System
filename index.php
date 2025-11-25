<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eLib - E-book Library Management System</title>
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #2ecc71;
            --secondary-dark: #27ae60;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --gray: #95a5a6;
            --danger: #e74c3c;
            --warning: #f39c12;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header Styles */
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo h1 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-left: 10px;
        }
        
        .logo-icon {
            font-size: 2rem;
            color: var(--primary);
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 25px;
        }
        
        nav ul li a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            transition: color 0.3s;
        }
        
        nav ul li a:hover {
            color: var(--primary);
        }
        
        .auth-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }
        
        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .hero h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 30px;
        }
        
        /* Features Section */
        .features {
            padding: 80px 0;
            background-color: white;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h2 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 15px;
        }
        
        .section-title p {
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .feature-card {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .feature-card h3 {
            margin-bottom: 15px;
            color: var(--dark);
        }
        
        /* Subscription Plans */
        .subscription {
            padding: 80px 0;
            background-color: #f5f7fa;
        }
        
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        
        .plan-card {
            background-color: white;
            border-radius: 10px;
            padding: 40px 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: center;
            position: relative;
            transition: transform 0.3s;
        }
        
        .plan-card:hover {
            transform: scale(1.03);
        }
        
        .plan-card.popular {
            border: 2px solid var(--secondary);
        }
        
        .popular-badge {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--secondary);
            color: white;
            padding: 5px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .plan-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin: 20px 0;
        }
        
        .plan-price span {
            font-size: 1rem;
            color: var(--gray);
        }
        
        .plan-features {
            list-style: none;
            margin: 30px 0;
            text-align: left;
        }
        
        .plan-features li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .plan-features li:last-child {
            border-bottom: none;
        }
        
        .plan-features li::before {
            content: "✓";
            color: var(--secondary);
            font-weight: bold;
            margin-right: 10px;
        }
        
        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 60px 0 20px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-column h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: var(--light);
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: var(--gray);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            nav ul {
                margin: 20px 0;
                justify-content: center;
            }
            
            .hero h2 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <span class="logo-icon">📚</span>
                    <h1>eLib</h1>
                </div>
                
                <nav>
                    <ul>
                        <li><a href="#">Home</a></li>
                        <li><a href="#">Browse</a></li>
                        <li><a href="#">Categories</a></li>
                        <li><a href="#">For Publishers</a></li>
                        <li><a href="#">About</a></li>
                    </ul>
                </nav>
                
                <div class="auth-buttons">
                    <a href="#" class="btn btn-outline">Login</a>
                    <a href="#" class="btn btn-primary">Sign Up</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h2>Your Digital Library, Anytime, Anywhere</h2>
            <p>Access thousands of e-books from various genres. Read online or download for offline reading. Subscribe today and unlock unlimited access to our premium collection.</p>
            <a href="#" class="btn btn-primary">Browse Collection</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose eLib?</h2>
                <p>Our platform offers a comprehensive solution for readers and publishers alike</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📖</div>
                    <h3>Extensive Collection</h3>
                    <p>Access thousands of e-books across multiple categories and genres, from bestsellers to niche topics.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Read Anywhere</h3>
                    <p>Our responsive web reader works perfectly on all devices - desktop, tablet, or mobile.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Instant Access</h3>
                    <p>Start reading immediately after subscription. No waiting, no shipping fees.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>For Publishers</h3>
                    <p>Reach a wider audience, manage your e-books, and track performance with our publisher dashboard.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Secure Platform</h3>
                    <p>Your data and payments are protected with industry-standard security measures.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h3>Community Reviews</h3>
                    <p>Read reviews from other readers and share your own thoughts on books you've read.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Subscription Plans -->
    <section class="subscription">
        <div class="container">
            <div class="section-title">
                <h2>Subscription Plans</h2>
                <p>Choose the plan that works best for you and unlock unlimited access to our premium e-book collection</p>
            </div>
            
            <div class="plans-grid">
                <div class="plan-card">
                    <h3>Monthly Subscription</h3>
                    <div class="plan-price">Rs. 500<span>/month</span></div>
                    <ul class="plan-features">
                        <li>Unlimited access to all paid content</li>
                        <li>30-day validity</li>
                        <li>Read on any device</li>
                        <li>Add to favorites</li>
                        <li>Track reading progress</li>
                    </ul>
                    <a href="#" class="btn btn-primary">Subscribe Now</a>
                </div>
                
                <div class="plan-card popular">
                    <div class="popular-badge">Most Popular</div>
                    <h3>Yearly Subscription</h3>
                    <div class="plan-price">Rs. 5000<span>/year</span></div>
                    <ul class="plan-features">
                        <li>Unlimited access to all paid content</li>
                        <li>365-day validity</li>
                        <li>Save Rs. 1000 compared to monthly</li>
                        <li>Read on any device</li>
                        <li>Add to favorites</li>
                        <li>Track reading progress</li>
                    </ul>
                    <a href="#" class="btn btn-primary">Subscribe Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>eLib</h3>
                    <p>Your digital library for accessing thousands of e-books anytime, anywhere. Subscribe today and expand your reading horizons.</p>
                </div>
                
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#">Home</a></li>
                        <li><a href="#">Browse Books</a></li>
                        <li><a href="#">Categories</a></li>
                        <li><a href="#">Publisher Portal</a></li>
                        <li><a href="#">Admin Login</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Support</h3>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Contact Info</h3>
                    <ul class="footer-links">
                        <li>Email: support@elib.com</li>
                        <li>Phone: +94 11 234 5678</li>
                        <li>Address: 123 Library Street, Colombo, Sri Lanka</li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                &copy; 2025 eLib - E-book Library Management System. All rights reserved.
            </div>
        </div>
    </footer>
</body>
</html>