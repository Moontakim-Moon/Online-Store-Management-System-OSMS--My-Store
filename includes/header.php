<?php
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Store</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<!-- Skip to content link for accessibility -->
<a href="#main-content" class="skip-link">Skip to main content</a>

<!-- Floating background elements -->
<div class="floating-element"></div>
<div class="floating-element"></div>
<div class="floating-element"></div>

<header>
    <div class="header-content">
        <h1>
            <a href="../index.php" class="site-logo">
                <i class="fas fa-store-alt"></i>
                <span class="logo-text">My Store</span>
            </a>
        </h1>
        
        <nav>
            <ul class="nav-list">
                <li><a href="../pages/products.php"><i class="fas fa-shopping-bag"></i> Products</a></li>
                <li><a href="../pages/cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                <li><a href="../pages/about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="../pages/user_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><span><i class="fas fa-user"></i> Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span></li>
                    <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php if ($_SESSION['is_admin']): ?>
                        <li><a href="../admin/index.php"><i class="fas fa-cog"></i> Admin Panel</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="../pages/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="../pages/register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <div id="dark-mode-container">
            <button id="dark-mode-toggle" aria-label="Toggle dark mode" title="Toggle dark mode">
                <span id="moon-icon" class="icon"><i class="fas fa-moon"></i></span>
                <span id="sun-icon" class="icon" style="display: none;"><i class="fas fa-sun"></i></span>
            </button>
        </div>
    </div>
</header>

<main id="main-content">
