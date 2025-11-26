<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Readify - Your Digital Bookshelf</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        :root {
            --bg-primary: #0a0e27;
            --bg-secondary: #151937;
            --bg-card: #1a1f3a;
            --accent-primary: #6366f1;
            --accent-secondary: #8b5cf6;
            --accent-tertiary: #ec4899;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.4);
            --shadow-glow: 0 0 40px rgba(99, 102, 241, 0.3);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 40% 20%, rgba(236, 72, 153, 0.1) 0%, transparent 50%);
            animation: drift 20s ease-in-out infinite alternate;
        }

        @keyframes drift {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(-50px, 50px) rotate(180deg); }
        }
        
        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }
        
        /* Header */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(10, 14, 39, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        header.scrolled {
            background: rgba(10, 14, 39, 0.95);
            box-shadow: var(--shadow-lg);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.75rem;
            font-weight: 800;
            background: var(--gradient-3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .logo-icon {
            font-size: 2rem;
            filter: drop-shadow(0 0 10px rgba(79, 172, 254, 0.5));
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 40px;
            align-items: center;
        }
        
        nav ul li a {
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            position: relative;
        }

        nav ul li a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gradient-3);
            transition: width 0.3s ease;
        }
        
        nav ul li a:hover {
            color: var(--text-primary);
        }

        nav ul li a:hover::after {
            width: 100%;
        }
        
        .auth-buttons {
            display: flex;
            gap: 12px;
        }
        
        .btn {
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-primary {
            background: var(--gradient-1);
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.6);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--text-primary);
            border: 2px solid rgba(99, 102, 241, 0.5);
        }
        
        .btn-outline:hover {
            border-color: var(--accent-primary);
            background: rgba(99, 102, 241, 0.1);
        }
        
        /* Hero Section */
        .hero {
            padding: 180px 0 120px;
            text-align: center;
            position: relative;
        }

        .hero-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.2) 0%, transparent 70%);
            filter: blur(80px);
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.5; transform: translate(-50%, -50%) scale(1); }
            50% { opacity: 0.8; transform: translate(-50%, -50%) scale(1.1); }
        }
        
        .hero h2 {
            font-size: 4.5rem;
            font-weight: 800;
            margin-bottom: 24px;
            line-height: 1.1;
            letter-spacing: -2px;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            z-index: 1;
        }

        .hero .gradient-text {
            background: var(--gradient-3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero p {
            font-size: 1.25rem;
            color: var(--text-secondary);
            max-width: 700px;
            margin: 0 auto 40px;
            line-height: 1.8;
            position: relative;
            z-index: 1;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        .btn-large {
            padding: 18px 40px;
            font-size: 1.1rem;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        /* Features Section */
        .features {
            padding: 120px 0;
            background: linear-gradient(180deg, transparent 0%, rgba(21, 25, 55, 0.5) 100%);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 80px;
        }
        
        .section-title h2 {
            font-size: 3rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 16px;
            letter-spacing: -1px;
        }
        
        .section-title p {
            color: var(--text-secondary);
            font-size: 1.15rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 32px;
        }
        
        .feature-card {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-1);
            transform: translateX(-100%);
            transition: transform 0.4s ease;
        }

        .feature-card:hover::before {
            transform: translateX(0);
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: var(--shadow-glow);
        }
        
        .feature-icon {
            font-size: 3.5rem;
            margin-bottom: 24px;
            display: inline-block;
            filter: drop-shadow(0 4px 10px rgba(99, 102, 241, 0.4));
            transition: transform 0.4s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .feature-card h3 {
            margin-bottom: 16px;
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 700;
        }

        .feature-card p {
            color: var(--text-secondary);
            line-height: 1.7;
        }
        
        /* Subscription Plans */
        .subscription {
            padding: 120px 0;
            position: relative;
        }
        
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
            gap: 40px;
            margin-top: 60px;
        }
        
        .plan-card {
            background: var(--bg-card);
            border-radius: 32px;
            padding: 50px 40px;
            border: 2px solid rgba(255, 255, 255, 0.05);
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .plan-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .plan-card:hover::after {
            opacity: 1;
        }
        
        .plan-card:hover {
            transform: scale(1.03);
            border-color: rgba(99, 102, 241, 0.5);
            box-shadow: var(--shadow-glow);
        }
        
        .plan-card.popular {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            border: 2px solid var(--accent-primary);
        }
        
        .popular-badge {
            position: absolute;
            right: 40px;
            background: var(--gradient-2);
            color: white;
            padding: 8px 24px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
        }

        .plan-card h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }
        
        .plan-price {
            font-size: 3.5rem;
            font-weight: 800;
            background: var(--gradient-3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 24px 0;
            position: relative;
            z-index: 1;
        }
        
        .plan-price span {
            font-size: 1.1rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        
        .plan-features {
            list-style: none;
            margin: 36px 0;
            position: relative;
            z-index: 1;
        }
        
        .plan-features li {
            padding: 14px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 12px;
            transition: color 0.3s ease;
        }

        .plan-card:hover .plan-features li {
            color: var(--text-primary);
        }
        
        .plan-features li:last-child {
            border-bottom: none;
        }
        
        .plan-features li::before {
            content: "✓";
            color: var(--success);
            font-weight: bold;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        /* Footer */
        footer {
            background: var(--bg-secondary);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding: 80px 0 30px;
            margin-top: 120px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 50px;
            margin-bottom: 50px;
        }
        
        .footer-column h3 {
            font-size: 1.25rem;
            margin-bottom: 24px;
            color: var(--text-primary);
            font-weight: 700;
        }

        .footer-column p {
            color: var(--text-muted);
            line-height: 1.8;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .footer-links a:hover {
            color: var(--accent-primary);
            transform: translateX(4px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
            font-size: 0.95rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
            }
            
            nav ul {
                flex-direction: column;
                gap: 15px;
            }
            
            .hero h2 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .section-title h2 {
                font-size: 2rem;
            }

            .features-grid,
            .plans-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>

    <!-- Header -->
    <header id="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <span class="logo-icon">📚</span>
                    <span>Readify</span>
                </div>
                
                <nav>
                    <ul>
                        <li><a href="#">Home</a></li>
                        <li><a href="#">All Books</a></li>
                        <li><a href="#">Bestsellers</a></li>
                        <li><a href="#">New Releases</a></li>
                        <li><a href="#">Genres</a></li>
                    </ul>
                </nav>
                
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-outline">Sign In</a>
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-glow"></div>
        <div class="container">
            <h2>Your Personal Library,<br><span class="gradient-text">Unlimited Books</span></h2>
            <p>Discover, read, and collect thousands of e-books from bestsellers to hidden gems. Build your digital bookshelf and read on any device, anytime.</p>
            <div class="hero-buttons">
                <a href="#" class="btn btn-primary btn-large">Browse Books</a>
                <a href="#" class="btn btn-secondary btn-large">See Bestsellers</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Readers Love Folio</h2>
                <p>Everything you need for the ultimate e-book reading experience</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📚</div>
                    <h3>Massive Book Collection</h3>
                    <p>Access over 50,000 e-books across all genres - from romance and thrillers to business and self-help. New books added weekly.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3>Personal Bookshelf</h3>
                    <p>Organize your reading with custom collections, track your progress, and pick up right where you left off on any device.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📖</div>
                    <h3>Read Your Way</h3>
                    <p>Customize fonts, adjust brightness, highlight passages, and take notes. Your perfect reading experience awaits.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">⭐</div>
                    <h3>Bestsellers & New Releases</h3>
                    <p>Get early access to bestsellers, new releases, and exclusive titles from your favorite authors before anyone else.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💾</div>
                    <h3>Offline Reading</h3>
                    <p>Download books to read offline during your commute, flights, or anywhere without internet. Your library goes where you go.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Book Community</h3>
                    <p>Join book clubs, read and write reviews, share your favorite quotes, and discover your next great read from fellow booklovers.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Subscription Plans -->
    <section class="subscription">
        <div class="container">
            <div class="section-title">
                <h2>Simple, Affordable Plans</h2>
                <p>Get unlimited access to thousands of books for less than the cost of one paperback</p>
            </div>
            
            <div class="plans-grid">
                <div class="plan-card">
                    <h3>Monthly Reader</h3>
                    <div class="plan-price">Rs. 499<span>/month</span></div>
                    <ul class="plan-features">
                        <li>Read unlimited books</li>
                        <li>Access to entire catalog</li>
                        <li>Read on any device</li>
                        <li>Download up to 10 books</li>
                        <li>Personalized recommendations</li>
                        <li>Cancel anytime</li>
                    </ul>
                    <a href="#" class="btn btn-primary btn-large">Start Reading</a>
                </div>
                
                <div class="plan-card popular">
                    <div class="popular-badge">Most Popular</div>
                    <h3>Annual Reader</h3>
                    <div class="plan-price">Rs. 4,999<span>/year</span></div>
                    <ul class="plan-features">
                        <li>All Monthly Reader benefits</li>
                        <li>Save Rs. 1,000 per year</li>
                        <li>Download up to 50 books</li>
                        <li>Early access to new releases</li>
                        <li>Exclusive author Q&As</li>
                        <li>Priority customer support</li>
                        <li>Ad-free reading experience</li>
                    </ul>
                    <a href="#" class="btn btn-primary btn-large">Get Annual Plan</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Readify</h3>
                    <p>Your digital bookshelf for discovering and reading thousands of e-books. Start building your collection today.</p>
                </div>
                
                <div class="footer-column">
                    <h3>Browse</h3>
                    <ul class="footer-links">
                        <li><a href="#">All Books</a></li>
                        <li><a href="#">Bestsellers</a></li>
                        <li><a href="#">New Releases</a></li>
                        <li><a href="#">Genres</a></li>
                        <li><a href="#">Authors</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Support</h3>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">How to Read</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">For Publishers</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Get in Touch</h3>
                    <ul class="footer-links">
                        <li><a href="#">support@folio.lk</a></li>
                        <li><a href="#">+94 77 123 4567</a></li>
                        <li><a href="#">Colombo, Sri Lanka</a></li>
                        <li><a href="#">Join Our Team</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                © 2025 Readify. Your digital bookshelf for unlimited reading.
            </div>
        </div>
    </footer>

    <script>
        // Header scroll effect
        window.addEventListener('scroll', () => {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>