<?php
session_start();
require_once '../includes/functions.php';
require_once '../includes/db.php';

// Get filter parameters
$main_category_id = $_GET['main_category_id'] ?? null;
$subcategory_id = $_GET['subcategory_id'] ?? null;
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'latest';
$page = intval($_GET['page'] ?? 1);
$limit = 12;
$offset = ($page - 1) * $limit;

// Build query conditions
$where_conditions = [];
$params = [];

if ($subcategory_id) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $subcategory_id;
} elseif ($main_category_id) {
    $where_conditions[] = "p.category_id IN (SELECT id FROM categories WHERE id = ? OR parent_id = ?)";
    $params[] = $main_category_id;
    $params[] = $main_category_id;
}

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_conditions[] = "p.status = 'active'";

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Get sort order
$order_clause = "ORDER BY ";
switch ($sort) {
    case 'price_low':
        $order_clause .= "p.price ASC";
        break;
    case 'price_high':
        $order_clause .= "p.price DESC";
        break;
    case 'name':
        $order_clause .= "p.name ASC";
        break;
    case 'featured':
        $order_clause .= "p.featured DESC, p.created_at DESC";
        break;
    default:
        $order_clause .= "p.created_at DESC";
}

// Get products with pagination
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    $where_clause 
    $order_clause 
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get total count for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM products p $where_clause");
$count_stmt->execute($params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

$main_categories = getMainCategories();
$subcategories = $main_category_id ? getSubcategories($main_category_id) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Our Store</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/global-theme.css" rel="stylesheet">
    <style>
        .products-hero {
            background: var(--gradient-header);
            color: var(--text-primary);
            padding: var(--spacing-4xl) 0;
            text-align: center;
        }
        
        .products-hero h1 {
            font-family: var(--font-heading);
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: var(--spacing-lg);
        }
        
        .products-hero p {
            font-family: var(--font-accent);
            font-size: 1.3rem;
            color: var(--text-secondary);
            max-width: 700px;
            margin: 0 auto;
        }
        
        .filters-section {
            background: var(--light-gray);
            padding: var(--spacing-2xl) 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .filters-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-lg);
            align-items: end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            font-family: var(--font-accent);
            font-weight: 600;
            margin-bottom: var(--spacing-sm);
            color: var(--text-primary);
        }
        
        .filter-group select,
        .filter-group input {
            padding: var(--spacing-md);
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-family: var(--font-accent);
            font-size: 1rem;
            background: var(--white);
            color: var(--text-primary);
            transition: var(--transition);
        }
        
        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(244, 208, 63, 0.2);
            transform: translateY(-1px);
        }
        
        .filter-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-filter {
            padding: var(--spacing-md) var(--spacing-xl);
            background: var(--gradient-button);
            color: var(--text-primary);
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-family: var(--font-accent);
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            box-shadow: var(--shadow-sm);
        }
        
        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-clear {
            padding: var(--spacing-md) var(--spacing-xl);
            background: var(--white);
            color: var(--primary-dark);
            border: 2px solid var(--primary);
            border-radius: var(--border-radius);
            cursor: pointer;
            font-family: var(--font-accent);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }
        
        .btn-clear:hover {
            background: var(--primary);
            color: var(--text-primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .products-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-3xl) var(--spacing-lg);
        }
        
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-2xl);
        }
        
        .products-count {
            color: var(--text-secondary);
            font-family: var(--font-accent);
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: var(--spacing-2xl);
            margin-bottom: var(--spacing-3xl);
        }
        
        .product-card {
            background: var(--bg-card);
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            position: relative;
            border: 1px solid var(--border-color);
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }
        
        .product-image {
            position: relative;
            overflow: hidden;
            height: 280px;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.08);
        }
        
        .product-badge {
            position: absolute;
            top: var(--spacing-lg);
            left: var(--spacing-lg);
            background: #28a745;
            color: white;
            padding: var(--spacing-xs) var(--spacing-md);
            border-radius: 20px;
            font-family: var(--font-accent);
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: var(--shadow-sm);
        }
        
        .product-badge.featured {
            background: var(--primary);
            color: var(--text-primary);
        }
        
        .product-badge.sale {
            background: #dc3545;
        }
        
        .product-content {
            padding: var(--spacing-xl);
        }
        
        .product-category {
            color: var(--primary-dark);
            font-family: var(--font-accent);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: var(--spacing-sm);
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        
        .product-title {
            font-family: var(--font-heading);
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: var(--spacing-md);
            color: var(--text-primary);
            line-height: 1.4;
        }
        
        .product-description {
            color: var(--text-secondary);
            font-family: var(--font-body);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: var(--spacing-lg);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
        }
        
        .current-price {
            font-family: var(--font-heading);
            font-size: 1.4rem;
            font-weight: 700;
            color: #28a745;
        }
        
        .original-price {
            font-family: var(--font-accent);
            font-size: 1rem;
            color: var(--text-secondary);
            text-decoration: line-through;
        }
        
        .product-actions {
            display: flex;
            gap: var(--spacing-sm);
            align-items: center;
        }
        
        .quantity-input {
            width: 70px;
            padding: var(--spacing-sm);
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            text-align: center;
            font-family: var(--font-accent);
            font-weight: 600;
            background: var(--white);
            color: var(--text-primary);
            transition: var(--transition);
        }
        
        .quantity-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(244, 208, 63, 0.2);
        }
        
        .btn-add-cart {
            flex: 1;
            padding: var(--spacing-md) var(--spacing-lg);
            background: var(--gradient-button);
            color: var(--text-primary);
            border: none;
            border-radius: var(--border-radius);
            font-family: var(--font-accent);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }
        
        .btn-add-cart::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }
        
        .btn-add-cart:hover::before {
            left: 100%;
        }
        
        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .empty-state {
            text-align: center;
            padding: var(--spacing-4xl) var(--spacing-2xl);
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 5rem;
            margin-bottom: var(--spacing-lg);
            color: var(--primary-light);
        }
        
        .empty-state h3 {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            color: var(--text-primary);
            margin-bottom: var(--spacing-md);
        }
        
        .empty-state p {
            font-family: var(--font-accent);
            font-size: 1.1rem;
            margin-bottom: var(--spacing-xl);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: var(--spacing-lg);
            margin-top: var(--spacing-3xl);
        }
        
        .pagination a,
        .pagination span {
            padding: var(--spacing-md) var(--spacing-lg);
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            text-decoration: none;
            color: var(--text-secondary);
            font-family: var(--font-accent);
            font-weight: 500;
            transition: var(--transition);
            background: var(--white);
        }
        
        .pagination a:hover {
            background: var(--primary);
            color: var(--text-primary);
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        
        .pagination .current {
            background: var(--gradient-button);
            color: var(--text-primary);
            border-color: var(--primary);
            box-shadow: var(--shadow-sm);
        }
        
        @media (max-width: 768px) {
            .products-hero h1 {
                font-size: 2.5rem;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: var(--spacing-xl);
            }
            
            .filter-actions {
                flex-direction: column;
            }
            
            .products-container {
                padding: var(--spacing-2xl) var(--spacing-md);
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="products-hero">
        <div class="container">
            <h1>Discover Our Products</h1>
            <p>Explore our carefully curated collection of high-quality products designed to meet your needs</p>
        </div>
    </section>
    
    <!-- Filters Section -->
    <section class="filters-section">
        <div class="filters-container">
            <form method="GET" action="products.php">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="search">Search Products</label>
                        <input type="text" id="search" name="search" placeholder="Search by name..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="main_category">Category</label>
                        <select name="main_category_id" id="main_category">
                            <option value="">Select Category</option>
                            <option value="1">Women</option>
                            <option value="2">Men</option>
                        </select>
                    </div>
                    
                    <?php if (!empty($subcategories)): ?>
                        <div class="filter-group">
                            <label for="subcategory">Subcategory</label>
                            <select name="subcategory_id" id="subcategory">
                                <option value="">All Subcategories</option>
                                <?php foreach ($subcategories as $subcat): ?>
                                    <option value="<?= $subcat['id'] ?>" <?= ($subcategory_id == $subcat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($subcat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div class="filter-group">
                        <label for="sort">Sort By</label>
                        <select name="sort" id="sort">
                            <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Latest</option>
                            <option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>Featured</option>
                            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name A-Z</option>
                            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        </select>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn-filter">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="products.php" class="btn-clear">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>
    
    <!-- Products Section -->
    <section class="products-container">
        <div class="products-header">
            <div class="products-count">
                <?php if ($total_products > 0): ?>
                    Showing <?= (($page - 1) * $limit) + 1 ?>-<?= min($page * $limit, $total_products) ?> of <?= $total_products ?> products
                <?php else: ?>
                    No products found
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No products found</h3>
                <p>Try adjusting your search criteria or browse all categories</p>
                <a href="products.php" class="btn-filter" style="margin-top: 1rem;">
                    <i class="fas fa-arrow-left"></i> View All Products
                </a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <a href="product_detail.php?id=<?= $product['id'] ?>">
                                <?php
                                    $imageSrc = $product['image'] ?? '../assets/images/default.png';
                                    $imgUrl = preg_match('/^https?:\/\//', $imageSrc) 
                                        ? $imageSrc 
                                        : (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($imageSrc, '/')) 
                                            ? '/' . ltrim($imageSrc, '/') 
                                            : '../assets/images/default.png');
                                ?>
                                <img src="<?= htmlspecialchars($imgUrl) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                     loading="lazy">
                            </a>
                            
                            <?php if ($product['featured']): ?>
                                <div class="product-badge featured">
                                    <i class="fas fa-star"></i> Featured
                                </div>
                            <?php elseif ($product['sale_price']): ?>
                                <div class="product-badge sale">
                                    <i class="fas fa-tag"></i> Sale
                                </div>
                            <?php elseif ($product['stock'] <= 5): ?>
                                <div class="product-badge">
                                    <i class="fas fa-exclamation"></i> Low Stock
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-content">
                            <div class="product-category">
                                <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                            </div>
                            
                            <h3 class="product-title">
                                <a href="product_detail.php?id=<?= $product['id'] ?>" style="text-decoration: none; color: inherit;">
                                    <?= htmlspecialchars($product['name']) ?>
                                </a>
                            </h3>
                            
                            <?php if ($product['short_description']): ?>
                                <p class="product-description">
                                    <?= htmlspecialchars($product['short_description']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="product-price">
                                <?php if ($product['sale_price']): ?>
                                    <span class="current-price">$<?= number_format($product['sale_price'], 2) ?></span>
                                    <span class="original-price">$<?= number_format($product['price'], 2) ?></span>
                                <?php else: ?>
                                    <span class="current-price">$<?= number_format($product['price'], 2) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <form method="post" action="../pages/cart.php?action=add&id=<?= $product['id'] ?>" class="product-actions">
                                <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" 
                                       class="quantity-input" required>
                                <button type="submit" class="btn-add-cart" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="fas fa-shopping-cart"></i>
                                    <?= $product['stock'] <= 0 ? 'Out of Stock' : 'Add to Cart' ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&main_category_id=<?= urlencode($main_category_id) ?>&subcategory_id=<?= urlencode($subcategory_id) ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <span class="current">Page <?= $page ?> of <?= $total_pages ?></span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>&main_category_id=<?= urlencode($main_category_id) ?>&subcategory_id=<?= urlencode($subcategory_id) ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Auto-submit form when category changes
        document.getElementById('main_category').addEventListener('change', function() {
            this.form.submit();
        });
        
        document.getElementById('subcategory')?.addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html>
