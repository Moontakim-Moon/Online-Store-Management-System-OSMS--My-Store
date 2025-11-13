<?php
session_start();
include "../includes/db.php";
include "../includes/functions.php";

if (!isset($_SESSION["user_id"]) || !$_SESSION["is_admin"]) {
    header("Location: admin_login.php");
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                
                if (!empty($name)) {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                    if ($stmt->execute([$name, $description])) {
                        $success = "Category added successfully!";
                    } else {
                        $error = "Failed to add category.";
                    }
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                
                if (!empty($name) && $id > 0) {
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
                    if ($stmt->execute([$name, $description, $id])) {
                        $success = "Category updated successfully!";
                    } else {
                        $error = "Failed to update category.";
                    }
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                if ($id > 0) {
                    // Check if category has products
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                    $stmt->execute([$id]);
                    $productCount = $stmt->fetchColumn();
                    
                    if ($productCount > 0) {
                        $error = "Cannot delete category. It has $productCount products associated with it.";
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                        if ($stmt->execute([$id])) {
                            $success = "Category deleted successfully!";
                        } else {
                            $error = "Failed to delete category.";
                        }
                    }
                }
                break;
        }
    }
}

// Fetch categories
$stmt = $pdo->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name");
$categories = $stmt->fetchAll();

// Get category for editing
$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$editId]);
    $editCategory = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-store"></i> My Store Admin</h2>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="categories.php" class="active"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <h1><i class="fas fa-tags"></i> Categories</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="showAddForm()">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                </div>
            </header>

            <div class="content-wrapper">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Form -->
                <div class="card" id="categoryForm" style="<?= !$editCategory ? 'display: none;' : '' ?>">
                    <div class="card-header">
                        <h3><?= $editCategory ? 'Edit Category' : 'Add New Category' ?></h3>
                        <button class="btn btn-outline btn-sm" onclick="hideForm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                    <div class="card-content">
                        <form method="POST" class="form">
                            <input type="hidden" name="action" value="<?= $editCategory ? 'edit' : 'add' ?>">
                            <?php if ($editCategory): ?>
                                <input type="hidden" name="id" value="<?= $editCategory['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="name">Category Name *</label>
                                <input type="text" id="name" name="name" required 
                                       value="<?= $editCategory ? htmlspecialchars($editCategory['name']) : '' ?>"
                                       placeholder="Enter category name">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="3" 
                                          placeholder="Enter category description"><?= $editCategory ? htmlspecialchars($editCategory['description']) : '' ?></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    <?= $editCategory ? 'Update Category' : 'Add Category' ?>
                                </button>
                                <button type="button" class="btn btn-outline" onclick="hideForm()">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Categories List -->
                <div class="card">
                    <div class="card-header">
                        <h3>All Categories</h3>
                        <span class="text-muted"><?= count($categories) ?> total</span>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Products</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; color: var(--admin-text-light);">
                                            No categories found. <a href="#" onclick="showAddForm()">Add the first category</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?= $category['id'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($category['name']) ?></strong>
                                            </td>
                                            <td><?= htmlspecialchars($category['description']) ?: '-' ?></td>
                                            <td>
                                                <span class="badge <?= $category['product_count'] > 0 ? 'confirmed' : 'pending' ?>">
                                                    <?= $category['product_count'] ?> products
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="?edit=<?= $category['id'] ?>" class="action-btn edit" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($category['product_count'] == 0): ?>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this category?')">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                                            <button type="submit" class="action-btn delete" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="action-btn disabled" title="Cannot delete - has products">
                                                            <i class="fas fa-trash"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sidebar toggle functionality
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
        });

        // Mobile responsive
        if (window.innerWidth <= 768) {
            document.getElementById('sidebar').classList.add('collapsed');
        }

        function showAddForm() {
            document.getElementById('categoryForm').style.display = 'block';
            document.getElementById('name').focus();
        }

        function hideForm() {
            window.location.href = 'categories.php';
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>
