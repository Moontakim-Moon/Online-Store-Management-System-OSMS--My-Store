<?php
session_start();
include "../includes/db.php";
include "../includes/functions.php";

// Check if user is admin
if (!isset($_SESSION["user_id"]) || !$_SESSION["is_admin"]) {
    header("Location: admin_login.php");
    exit;
}

$success = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$product_id = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $short_description = trim($_POST['short_description']);
        $price = floatval($_POST['price']);
        $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
        $category_id = intval($_POST['category_id']);
        $stock = intval($_POST['stock']);
        $sku = trim($_POST['sku']);
        $image = trim($_POST['image']);
        $status = $_POST['status'];
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, short_description, price, sale_price, category_id, stock, sku, image, status, featured, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            if ($stmt->execute([$name, $description, $short_description, $price, $sale_price, $category_id, $stock, $sku, $image, $status, $featured])) {
                $success = "Product added successfully!";
                $action = 'list';
            }
        } catch (PDOException $e) {
            $error = "Failed to add product: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['update_product'])) {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $short_description = trim($_POST['short_description']);
        $price = floatval($_POST['price']);
        $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
        $category_id = intval($_POST['category_id']);
        $stock = intval($_POST['stock']);
        $sku = trim($_POST['sku']);
        $image = trim($_POST['image']);
        $status = $_POST['status'];
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        try {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, short_description = ?, price = ?, sale_price = ?, category_id = ?, stock = ?, sku = ?, image = ?, status = ?, featured = ?, updated_at = NOW() WHERE id = ?");
            if ($stmt->execute([$name, $description, $short_description, $price, $sale_price, $category_id, $stock, $sku, $image, $status, $featured, $id])) {
                $success = "Product updated successfully!";
                $action = 'list';
            }
        } catch (PDOException $e) {
            $error = "Failed to update product: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['delete_product'])) {
        $id = intval($_POST['product_id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success = "Product deleted successfully!";
            }
        } catch (PDOException $e) {
            $error = "Failed to delete product: " . $e->getMessage();
        }
    }
}

// Get product for editing
$product = null;
if ($action === 'edit' && $product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    if (!$product) {
        $error = "Product not found!";
        $action = 'list';
    }
}

// Get all products with pagination
$page = intval($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category_filter)) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $where_clause ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get total count for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM products p $where_clause");
$count_stmt->execute($params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

// Get categories for dropdown
$stmt = $pdo->query("SELECT * FROM categories WHERE parent_id IS NOT NULL ORDER BY name");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-store"></i> Admin Panel</h2>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="products.php" class="active"><i class="fas fa-box"></i> Products</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <h1><i class="fas fa-box"></i> Product Management</h1>
                <div class="header-actions">
                    <a href="../index.php" class="btn btn-outline" target="_blank">
                        <i class="fas fa-external-link-alt"></i> View Store
                    </a>
                </div>
            </header>

            <div class="content-wrapper">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <!-- Add/Edit Product Form -->
                    <div class="card">
                        <div class="card-header">
                            <h2><?= $action === 'edit' ? 'Edit Product' : 'Add New Product' ?></h2>
                            <a href="products.php" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Back to Products
                            </a>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="product-form">
                                <?php if ($action === 'edit'): ?>
                                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="update_product" value="1">
                                <?php else: ?>
                                    <input type="hidden" name="add_product" value="1">
                                <?php endif; ?>

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="name">Product Name *</label>
                                        <input type="text" id="name" name="name" required 
                                               value="<?= $action === 'edit' ? htmlspecialchars($product['name']) : '' ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="sku">SKU</label>
                                        <input type="text" id="sku" name="sku" 
                                               value="<?= $action === 'edit' ? htmlspecialchars($product['sku']) : '' ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="category_id">Category *</label>
                                        <select id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category['id'] ?>" 
                                                        <?= ($action === 'edit' && $product['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="price">Price *</label>
                                        <input type="number" id="price" name="price" step="0.01" min="0" required 
                                               value="<?= $action === 'edit' ? $product['price'] : '' ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="sale_price">Sale Price</label>
                                        <input type="number" id="sale_price" name="sale_price" step="0.01" min="0" 
                                               value="<?= $action === 'edit' ? $product['sale_price'] : '' ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="stock">Stock Quantity *</label>
                                        <input type="number" id="stock" name="stock" min="0" required 
                                               value="<?= $action === 'edit' ? $product['stock'] : '' ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select id="status" name="status">
                                            <option value="active" <?= ($action === 'edit' && $product['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= ($action === 'edit' && $product['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="image">Image URL</label>
                                        <input type="url" id="image" name="image" 
                                               value="<?= $action === 'edit' ? htmlspecialchars($product['image']) : '' ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="short_description">Short Description</label>
                                    <textarea id="short_description" name="short_description" rows="2"><?= $action === 'edit' ? htmlspecialchars($product['short_description']) : '' ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="description">Full Description</label>
                                    <textarea id="description" name="description" rows="4"><?= $action === 'edit' ? htmlspecialchars($product['description']) : '' ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="featured" <?= ($action === 'edit' && $product['featured']) ? 'checked' : '' ?>>
                                        <span class="checkmark"></span>
                                        Featured Product
                                    </label>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> <?= $action === 'edit' ? 'Update Product' : 'Add Product' ?>
                                    </button>
                                    <a href="products.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Products List -->
                    <div class="card">
                        <div class="card-header">
                            <h2>Products</h2>
                            <a href="products.php?action=add" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Product
                            </a>
                        </div>
                        <div class="card-body">
                            <!-- Search and Filter -->
                            <div class="filters">
                                <form method="GET" class="filter-form">
                                    <div class="filter-group">
                                        <input type="text" name="search" placeholder="Search products..." 
                                               value="<?= htmlspecialchars($search) ?>">
                                    </div>
                                    <div class="filter-group">
                                        <select name="category">
                                            <option value="">All Categories</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category['id'] ?>" 
                                                        <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-outline">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="products.php" class="btn btn-secondary">Clear</a>
                                </form>
                            </div>

                            <!-- Products Table -->
                            <?php if (empty($products)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-box-open"></i>
                                    <h3>No products found</h3>
                                    <p>Start by adding your first product to the store.</p>
                                    <a href="products.php?action=add" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Product
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Name</th>
                                                <th>SKU</th>
                                                <th>Category</th>
                                                <th>Price</th>
                                                <th>Stock</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($products as $product): ?>
                                                <tr>
                                                    <td>
                                                        <?php if ($product['image']): ?>
                                                            <img src="<?= htmlspecialchars($product['image']) ?>" 
                                                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                                                 class="product-thumbnail">
                                                        <?php else: ?>
                                                            <div class="no-image">
                                                                <i class="fas fa-image"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="product-info">
                                                            <strong><?= htmlspecialchars($product['name']) ?></strong>
                                                            <?php if ($product['featured']): ?>
                                                                <span class="badge badge-featured">Featured</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td><?= htmlspecialchars($product['sku']) ?></td>
                                                    <td><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></td>
                                                    <td>
                                                        <?php if ($product['sale_price']): ?>
                                                            <span class="price-sale">$<?= number_format($product['sale_price'], 2) ?></span>
                                                            <span class="price-original">$<?= number_format($product['price'], 2) ?></span>
                                                        <?php else: ?>
                                                            $<?= number_format($product['price'], 2) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="stock-badge <?= $product['stock'] <= 5 ? 'low-stock' : '' ?>">
                                                            <?= $product['stock'] ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?= $product['status'] ?>">
                                                            <?= ucfirst($product['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <a href="products.php?action=edit&id=<?= $product['id'] ?>" 
                                                               class="btn btn-sm btn-outline" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form method="POST" style="display: inline;" 
                                                                  onsubmit="return confirm('Are you sure you want to delete this product?')">
                                                                <input type="hidden" name="delete_product" value="1">
                                                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($total_pages > 1): ?>
                                    <div class="pagination">
                                        <?php if ($page > 1): ?>
                                            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>" 
                                               class="btn btn-outline">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        <?php endif; ?>
                                        
                                        <span class="page-info">
                                            Page <?= $page ?> of <?= $total_pages ?> (<?= $total_products ?> products)
                                        </span>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>" 
                                               class="btn btn-outline">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Sidebar toggle functionality
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });

        // Auto-generate SKU from product name
        document.getElementById('name')?.addEventListener('input', function() {
            const skuField = document.getElementById('sku');
            if (skuField && !skuField.value) {
                const sku = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 10);
                skuField.value = sku;
            }
        });
    </script>
</body>
</html>
