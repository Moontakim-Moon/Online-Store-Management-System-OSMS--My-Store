<?php
include '../includes/header.php';
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --primary: #F4D03F;
        --primary-dark: #F1C40F;
        --secondary: #8E44AD;
        --accent: #E67E22;
        --bg-primary: #FFFBF0;
        --bg-card: rgba(255, 255, 255, 0.95);
        --text-primary: #2C3E50;
        --text-secondary: #7F8C8D;
        --shadow-soft: 0 10px 40px rgba(244, 208, 63, 0.15);
        --shadow-hover: 0 20px 60px rgba(244, 208, 63, 0.25);
        --gradient-bg: linear-gradient(135deg, #FFFBF0 0%, #F7DC6F 100%);
        --gradient-card: linear-gradient(145deg, rgba(255,255,255,0.9) 0%, rgba(247,220,111,0.1) 100%);
        --font-heading: 'Playfair Display', serif;
        --font-body: 'Inter', sans-serif;
        --font-accent: 'Poppins', sans-serif;
        --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body, html {
        margin: 0;
        padding: 0;
        font-family: var(--font-body);
        background: var(--gradient-bg);
        color: var(--text-primary);
        min-height: 100vh;
        overflow-x: hidden;
        scroll-behavior: smooth;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 20% 80%, rgba(244, 208, 63, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(142, 68, 173, 0.08) 0%, transparent 50%),
            radial-gradient(circle at 40% 40%, rgba(230, 126, 34, 0.06) 0%, transparent 50%);
        pointer-events: none;
        z-index: -1;
        animation: float 20s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        33% { transform: translateY(-20px) rotate(1deg); }
        66% { transform: translateY(10px) rotate(-1deg); }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-40px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(40px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .about-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
        animation: fadeInUp 0.8s ease-out;
    }

    .hero-section {
        text-align: center;
        padding: 60px 0;
        background: var(--gradient-card);
        border-radius: 24px;
        margin-bottom: 60px;
        box-shadow: var(--shadow-soft);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(244, 208, 63, 0.2);
        position: relative;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: conic-gradient(from 0deg, transparent, rgba(244, 208, 63, 0.1), transparent);
        animation: rotate 20s linear infinite;
        pointer-events: none;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .hero-section h1 {
        font-family: var(--font-heading);
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 20px;
        position: relative;
        z-index: 1;
    }

    .hero-subtitle {
        font-family: var(--font-accent);
        font-size: clamp(1.1rem, 2vw, 1.4rem);
        color: var(--text-secondary);
        font-weight: 500;
        margin-bottom: 40px;
        position: relative;
        z-index: 1;
    }

    .about-content {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 60px;
        align-items: center;
        margin-bottom: 80px;
    }

    .about-left {
        animation: slideInLeft 0.8s ease-out 0.2s both;
    }

    .about-image {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: 20px;
        box-shadow: var(--shadow-soft);
        transition: var(--transition);
        border: 3px solid rgba(244, 208, 63, 0.3);
    }

    .about-image:hover {
        transform: scale(1.05) rotate(2deg);
        box-shadow: var(--shadow-hover);
        border-color: var(--primary);
    }

    .about-right {
        animation: slideInRight 0.8s ease-out 0.4s both;
    }

    .about-text {
        font-size: 1.1rem;
        line-height: 1.8;
        color: var(--text-primary);
        background: var(--bg-card);
        padding: 40px;
        border-radius: 20px;
        box-shadow: var(--shadow-soft);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(244, 208, 63, 0.2);
        position: relative;
    }

    .about-text::before {
        content: '"';
        position: absolute;
        top: -10px;
        left: 20px;
        font-size: 4rem;
        color: var(--primary);
        font-family: var(--font-heading);
        opacity: 0.3;
    }

    .contact-section {
        background: var(--gradient-card);
        padding: 60px 40px;
        border-radius: 24px;
        box-shadow: var(--shadow-soft);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(244, 208, 63, 0.2);
        margin-bottom: 60px;
        animation: fadeInUp 0.8s ease-out 0.6s both;
    }

    .contact-section h3 {
        font-family: var(--font-heading);
        font-size: 2.5rem;
        text-align: center;
        color: var(--primary-dark);
        margin-bottom: 40px;
        position: relative;
    }

    .contact-section h3::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        border-radius: 2px;
    }

    .contact-form {
        max-width: 600px;
        margin: 0 auto;
        display: grid;
        gap: 25px;
    }

    .form-group {
        position: relative;
    }

    .form-group label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
        font-family: var(--font-accent);
    }

    .form-group i {
        color: var(--primary);
        width: 20px;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 15px 20px;
        border: 2px solid rgba(244, 208, 63, 0.3);
        border-radius: 12px;
        font-size: 1rem;
        font-family: var(--font-body);
        background: rgba(255, 255, 255, 0.8);
        color: var(--text-primary);
        transition: var(--transition);
        backdrop-filter: blur(10px);
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(244, 208, 63, 0.2);
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.95);
    }

    .submit-btn {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        padding: 18px 40px;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 600;
        font-family: var(--font-accent);
        cursor: pointer;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-soft);
    }

    .submit-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: var(--transition);
    }

    .submit-btn:hover::before {
        left: 100%;
    }

    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-hover);
    }

    .developers-section {
        background: var(--gradient-card);
        padding: 40px;
        border-radius: 24px;
        text-align: center;
        box-shadow: var(--shadow-soft);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(244, 208, 63, 0.2);
        animation: fadeInUp 0.8s ease-out 0.8s both;
    }

    .developers-section h3 {
        font-family: var(--font-heading);
        font-size: 2rem;
        color: var(--primary-dark);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }

    .developers-section h3 i {
        color: var(--secondary);
    }

    .developers-text {
        font-size: 1.1rem;
        line-height: 1.7;
        color: var(--text-secondary);
        font-style: italic;
        font-family: var(--font-accent);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .about-content {
            grid-template-columns: 1fr;
            gap: 40px;
        }
        
        .hero-section {
            padding: 40px 20px;
            margin-bottom: 40px;
        }
        
        .contact-section,
        .developers-section {
            padding: 40px 20px;
            margin-bottom: 40px;
        }
        
        .about-image {
            height: 300px;
        }
        
        .about-text {
            padding: 30px 20px;
        }
    }

    @media (max-width: 480px) {
        .about-container {
            padding: 0 15px;
        }
        
        .hero-section {
            padding: 30px 15px;
        }
        
        .contact-section,
        .developers-section {
            padding: 30px 15px;
        }
    }
</style>

<div class="about-container">
    <div class="hero-section">
        <h1><i class="fas fa-crown"></i> About My Store</h1>
        <p class="hero-subtitle">Where elegance meets everyday charm</p>
    </div>

    <div class="about-content">
        <div class="about-left">
            <img src="https://i.pinimg.com/736x/8f/83/4c/8f834cfd55d24e18082fbee2013f3108.jpg" alt="About Us Image" class="about-image" />
        </div>
        <div class="about-right">
            <div class="about-text">
                Welcome to <strong>My Store</strong>, where elegance meets everyday charm. Born from a love for classic femininity and timeless beauty, My Store is more than just an online boutique—it's a curated collection of dreamlike fashion and accessories designed to make you feel like royalty. Our vision is simple: to help you embrace your inner princess through fashion that celebrates grace, softness, and individuality. From flowy floral dresses and romantic lace bows to delicately crafted bags and hair accessories, every item in our collection is handpicked with care, ensuring high quality, comfort, and a fairytale aesthetic. We believe that beauty lies in the details, which is why each piece is chosen for its unique design, soft color palette, and whimsical flair. Whether you're dressing up for a special moment or simply adding a touch of magic to your daily life, My Store is here to make your wardrobe as enchanting as your imagination. Thank you for being a part of our story. Your support means the world to us.
            </div>
        </div>
    </div>

    <div class="contact-section">
        <h3><i class="fas fa-envelope"></i> Get In Touch</h3>
        <form action="https://formspree.io/f/xkgbojdq" method="POST" class="contact-form">
            <div class="form-group">
                <label for="name">
                    <i class="fas fa-user"></i>
                    Your Name
                </label>
                <input type="text" id="name" name="name" required placeholder="Enter your full name">
            </div>

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i>
                    Email Address
                </label>
                <input type="email" id="email" name="_replyto" required placeholder="Enter your email address">
            </div>

            <div class="form-group">
                <label for="message">
                    <i class="fas fa-message"></i>
                    Your Message
                </label>
                <textarea id="message" name="message" rows="6" required placeholder="Tell us what's on your mind..."></textarea>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i>
                Send Message
            </button>
        </form>
    </div>

    <div class="developers-section">
        <h3><i class="fas fa-code"></i> Crafted With Love</h3>
        <p class="developers-text">
            Developed by Labony Sur, Aupurba Sarker, Sajeed Awal Sarif, Moontakim Moon and Muntasir Chowdhury — a passionate team of developers dedicated to bringing elegant, user-centered design and functionality to life through thoughtful digital craftsmanship.
        </p>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
