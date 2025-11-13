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
$user_id = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetchColumn() > 0) {
            $error = "Username or email already exists!";
        } else {
            try {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin, email_verified, created_at) VALUES (?, ?, ?, ?, 1, NOW())");
                if ($stmt->execute([$username, $email, $password_hash, $is_admin])) {
                    $success = "User added successfully!";
                    $action = 'list';
                }
            } catch (PDOException $e) {
                $error = "Failed to add user: " . $e->getMessage();
            }
        }
    }
    
    if (isset($_POST['update_user'])) {
        $id = intval($_POST['id']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, is_admin = ? WHERE id = ?");
            if ($stmt->execute([$username, $email, $is_admin, $id])) {
                $success = "User updated successfully!";
                $action = 'list';
            }
        } catch (PDOException $e) {
            $error = "Failed to update user: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['delete_user'])) {
        $id = intval($_POST['user_id']);
        if ($id != $_SESSION['user_id']) {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $success = "User deleted successfully!";
                }
            } catch (PDOException $e) {
                $error = "Failed to delete user: " . $e->getMessage();
            }
        } else {
            $error = "You cannot delete your own account!";
        }
    }
}

// Get user for editing
$user = null;
if ($action === 'edit' && $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user) {
        $error = "User not found!";
        $action = 'list';
    }
}

// Get all users with pagination
$page = intval($_GET['page'] ?? 1);
$limit = 15;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(username LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role_filter !== '') {
    $where_conditions[] = "is_admin = ?";
    $params[] = $role_filter;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$stmt = $pdo->prepare("SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get total count for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM users $where_clause");
$count_stmt->execute($params);
$total_users = $count_stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
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
                    <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <h1><i class="fas fa-users"></i> User Management</h1>
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
                    <!-- Add/Edit User Form -->
                    <div class="card">
                        <div class="card-header">
                            <h2><?= $action === 'edit' ? 'Edit User' : 'Add New User' ?></h2>
                            <a href="users.php" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Back to Users
                            </a>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="user-form">
                                <?php if ($action === 'edit'): ?>
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="update_user" value="1">
                                <?php else: ?>
                                    <input type="hidden" name="add_user" value="1">
                                <?php endif; ?>

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="username">Username *</label>
                                        <input type="text" id="username" name="username" required 
                                               value="<?= $action === 'edit' ? htmlspecialchars($user['username']) : '' ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Email *</label>
                                        <input type="email" id="email" name="email" required 
                                               value="<?= $action === 'edit' ? htmlspecialchars($user['email']) : '' ?>">
                                    </div>

                                    <?php if ($action === 'add'): ?>
                                        <div class="form-group">
                                            <label for="password">Password *</label>
                                            <input type="password" id="password" name="password" required minlength="6">
                                        </div>
                                    <?php endif; ?>

                                    <div class="form-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="is_admin" 
                                                   <?= ($action === 'edit' && $user['is_admin']) ? 'checked' : '' ?>>
                                            <span class="checkmark"></span>
                                            Administrator
                                        </label>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> <?= $action === 'edit' ? 'Update User' : 'Add User' ?>
                                    </button>
                                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Users List -->
                    <div class="card">
                        <div class="card-header">
                            <h2>Users</h2>
                            <a href="users.php?action=add" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add User
                            </a>
                        </div>
                        <div class="card-body">
                            <!-- Search and Filter -->
                            <div class="filters">
                                <form method="GET" class="filter-form">
                                    <div class="filter-group">
                                        <input type="text" name="search" placeholder="Search users..." 
                                               value="<?= htmlspecialchars($search) ?>">
                                    </div>
                                    <div class="filter-group">
                                        <select name="role">
                                            <option value="">All Roles</option>
                                            <option value="1" <?= $role_filter === '1' ? 'selected' : '' ?>>Administrators</option>
                                            <option value="0" <?= $role_filter === '0' ? 'selected' : '' ?>>Regular Users</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-outline">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="users.php" class="btn btn-secondary">Clear</a>
                                </form>
                            </div>

                            <!-- Users Table -->
                            <?php if (empty($users)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <h3>No users found</h3>
                                    <p>No users match your search criteria.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user_item): ?>
                                                <tr>
                                                    <td><?= $user_item['id'] ?></td>
                                                    <td>
                                                        <div class="user-info">
                                                            <strong><?= htmlspecialchars($user_item['username']) ?></strong>
                                                            <?php if ($user_item['id'] == $_SESSION['user_id']): ?>
                                                                <span class="badge badge-info">You</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td><?= htmlspecialchars($user_item['email']) ?></td>
                                                    <td>
                                                        <span class="badge badge-<?= $user_item['is_admin'] ? 'admin' : 'user' ?>">
                                                            <?= $user_item['is_admin'] ? 'Administrator' : 'User' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?= $user_item['email_verified'] ? 'active' : 'inactive' ?>">
                                                            <?= $user_item['email_verified'] ? 'Verified' : 'Unverified' ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('M j, Y', strtotime($user_item['created_at'])) ?></td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <a href="users.php?action=edit&id=<?= $user_item['id'] ?>" 
                                                               class="btn btn-sm btn-outline" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php if ($user_item['id'] != $_SESSION['user_id']): ?>
                                                                <form method="POST" style="display: inline;" 
                                                                      onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                                    <input type="hidden" name="delete_user" value="1">
                                                                    <input type="hidden" name="user_id" value="<?= $user_item['id'] ?>">
                                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
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
                                            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role_filter) ?>" 
                                               class="btn btn-outline">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        <?php endif; ?>
                                        
                                        <span class="page-info">
                                            Page <?= $page ?> of <?= $total_pages ?> (<?= $total_users ?> users)
                                        </span>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role_filter) ?>" 
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
    </script>
</body>
</html>
