<?php
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - My Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                <a href="index.php" class="site-logo">
                    <i class="fas fa-store-alt"></i>
                    <span class="logo-text">My Store</span>
                </a>
            </h1>
            <nav>
                <ul class="nav-list">
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="sample_products.php" class="active"><i class="fas fa-shopping-bag"></i> Products</a></li>
                    <li><a href="pages/cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                    <li><a href="pages/about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
                    <li><a href="pages/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="pages/register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                </ul>
            </nav>
            <div id="dark-mode-container">
                <button id="dark-mode-toggle" aria-label="Toggle dark mode">
                    <i class="fas fa-moon" id="moon-icon"></i>
                    <i class="fas fa-sun" id="sun-icon"></i>
                </button>
            </div>
        </div>
    </header>

    <main id="main-content">
        <h1>Our Products</h1>
        <p class="lead">Discover our carefully curated collection of premium products</p>
        
        <!-- Product Grid -->
        <div class="product-grid">
            <!-- Product 1 -->
            <div class="product-card">
                <div class="product-image-container">
                    <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=300&fit=crop" 
                         alt="Wireless Headphones" 
                         class="product-image">
                    <div class="product-image-overlay"></div>
                    <div class="product-badge new">New</div>
                    <div class="product-quick-actions">
                        <button class="quick-action-btn" title="Add to Wishlist">
                            <i class="fas fa-heart"></i>
                        </button>
                        <button class="quick-action-btn" title="Quick View">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <div class="product-category">Electronics</div>
                    <h3 class="product-title">Premium Wireless Headphones</h3>
                    <p class="product-description">Experience crystal-clear sound with our premium wireless headphones. Features noise cancellation, long battery life, and premium comfort.</p>
                    <div class="product-meta">
                        <div class="product-price">$199.99</div>
                        <div class="product-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="rating-text">(4.9)</span>
                        </div>
                    </div>
                    <div class="product-actions">
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </a>
                        <a href="#" class="btn btn-secondary">
                            <i class="fas fa-info-circle"></i> Details
                        </a>
                    </div>
                </div>
            </div>

            <!-- Product 2 -->
            <div class="product-card">
                <div class="product-image-container">
                    <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=300&fit=crop" 
                         alt="Smart Watch" 
                         class="product-image">
                    <div class="product-image-overlay"></div>
                    <div class="product-badge sale">Sale</div>
                    <div class="product-quick-actions">
                        <button class="quick-action-btn" title="Add to Wishlist">
                            <i class="fas fa-heart"></i>
                        </button>
                        <button class="quick-action-btn" title="Quick View">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <div class="product-category">Electronics</div>
                    <h3 class="product-title">Smart Fitness Watch</h3>
                    <p class="product-description">Track your fitness goals with our advanced smart watch. Monitor heart rate, steps, sleep, and stay connected with notifications.</p>
                    <div class="product-meta">
                        <div class="product-price">
                            <span class="original-price">$299.99</span>
                            $249.99
                        </div>
                        <div class="product-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <span class="rating-text">(4.2)</span>
                        </div>
                    </div>
                    <div class="product-actions">
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </a>
                        <a href="#" class="btn btn-secondary">
                            <i class="fas fa-info-circle"></i> Details
                        </a>
                    </div>
                </div>
            </div>

            <!-- Product 3 -->
            <div class="product-card">
                <div class="product-image-container">
                    <img src="https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&h=300&fit=crop" 
                         alt="Cotton T-Shirt" 
                         class="product-image">
                    <div class="product-image-overlay"></div>
                    <div class="product-quick-actions">
                        <button class="quick-action-btn" title="Add to Wishlist">
                            <i class="fas fa-heart"></i>
                        </button>
                        <button class="quick-action-btn" title="Quick View">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <div class="product-category">Clothing</div>
                    <h3 class="product-title">Premium Cotton T-Shirt</h3>
                    <p class="product-description">Ultra-soft premium cotton t-shirt with a modern fit. Available in multiple colors and sizes for everyday comfort and style.</p>
                    <div class="product-meta">
                        <div class="product-price">$24.99</div>
                        <div class="product-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="rating-text">(4.8)</span>
                        </div>
                    </div>
                    <div class="product-actions">
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </a>
                        <a href="#" class="btn btn-secondary">
                            <i class="fas fa-info-circle"></i> Details
                        </a>
                    </div>
                </div>
            </div>

            <!-- Product 4 -->
            <div class="product-card">
                <div class="product-image-container">
                    <img src="https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=400&h=300&fit=crop" 
                         alt="Programming Book" 
                         class="product-image">
                    <div class="product-image-overlay"></div>
                    <div class="product-badge">Bestseller</div>
                    <div class="product-quick-actions">
                        <button class="quick-action-btn" title="Add to Wishlist">
                            <i class="fas fa-heart"></i>
                        </button>
                        <button class="quick-action-btn" title="Quick View">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <div class="product-category">Books</div>
                    <h3 class="product-title">Advanced Programming Guide</h3>
                    <p class="product-description">Master advanced programming concepts with our comprehensive guide. Perfect for developers looking to enhance their skills.</p>
                    <div class="product-meta">
                        <div class="product-price">$49.99</div>
                        <div class="product-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="rating-text">(4.9)</span>
                        </div>
                    </div>
                    <div class="product-actions">
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </a>
                        <a href="#" class="btn btn-secondary">
                            <i class="fas fa-info-circle"></i> Details
                        </a>
                    </div>
                </div>
            </div>

            <!-- Product 5 -->
            <div class="product-card">
                <div class="product-image-container">
                    <img src="https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=400&h=300&fit=crop" 
                         alt="Laptop" 
                         class="product-image">
                    <div class="product-image-overlay"></div>
                    <div class="product-badge new">New</div>
                    <div class="product-quick-actions">
                        <button class="quick-action-btn" title="Add to Wishlist">
                            <i class="fas fa-heart"></i>
                        </button>
                        <button class="quick-action-btn" title="Quick View">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <div class="product-category">Electronics</div>
                    <h3 class="product-title">Ultra-Slim Laptop</h3>
                    <p class="product-description">Powerful performance in an ultra-slim design. Perfect for work, gaming, and creative projects with stunning display quality.</p>
                    <div class="product-meta">
                        <div class="product-price">$1,299.99</div>
                        <div class="product-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <span class="rating-text">(4.6)</span>
                        </div>
                    </div>
                    <div class="product-actions">
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </a>
                        <a href="#" class="btn btn-secondary">
                            <i class="fas fa-info-circle"></i> Details
                        </a>
                    </div>
                </div>
            </div>

            <!-- Product 6 -->
            <div class="product-card">
                <div class="product-image-container">
                    <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&h=300&fit=crop" 
                         alt="Coffee Mug" 
                         class="product-image">
                    <div class="product-image-overlay"></div>
                    <div class="product-quick-actions">
                        <button class="quick-action-btn" title="Add to Wishlist">
                            <i class="fas fa-heart"></i>
                        </button>
                        <button class="quick-action-btn" title="Quick View">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <div class="product-category">Home & Living</div>
                    <h3 class="product-title">Ceramic Coffee Mug Set</h3>
                    <p class="product-description">Beautiful ceramic coffee mugs perfect for your morning routine. Microwave safe and dishwasher friendly.</p>
                    <div class="product-meta">
                        <div class="product-price">$19.99</div>
                        <div class="product-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="rating-text">(4.7)</span>
                        </div>
                    </div>
                    <div class="product-actions">
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </a>
                        <a href="#" class="btn btn-secondary">
                            <i class="fas fa-info-circle"></i> Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 My Store. All rights reserved.</p>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
