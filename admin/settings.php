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
            case 'update_profile':
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $userId = $_SESSION['user_id'];
                
                if (!empty($username) && !empty($email)) {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                    if ($stmt->execute([$username, $email, $userId])) {
                        $_SESSION['username'] = $username;
                        $success = "Profile updated successfully!";
                    } else {
                        $error = "Failed to update profile.";
                    }
                }
                break;
                
            case 'change_password':
                $currentPassword = $_POST['current_password'];
                $newPassword = $_POST['new_password'];
                $confirmPassword = $_POST['confirm_password'];
                $userId = $_SESSION['user_id'];
                
                if ($newPassword !== $confirmPassword) {
                    $error = "New passwords do not match.";
                } elseif (strlen($newPassword) < 6) {
                    $error = "Password must be at least 6 characters long.";
                } else {
                    // Verify current password
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch();
                    
                    if (password_verify($currentPassword, $user['password'])) {
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        if ($stmt->execute([$hashedPassword, $userId])) {
                            $success = "Password changed successfully!";
                        } else {
                            $error = "Failed to change password.";
                        }
                    } else {
                        $error = "Current password is incorrect.";
                    }
                }
                break;
        }
    }
}

// Get current user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

// Get system statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$totalProducts = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$totalOrders = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM categories");
$totalCategories = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
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
                    <li><a href="categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <h1><i class="fas fa-cog"></i> Settings</h1>
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

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <!-- Profile Settings -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-user"></i> Profile Settings</h3>
                        </div>
                        <div class="card-content">
                            <form method="POST" class="form">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" required 
                                           value="<?= htmlspecialchars($currentUser['username']) ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" required 
                                           value="<?= htmlspecialchars($currentUser['email']) ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Role</label>
                                    <input type="text" value="Administrator" readonly class="readonly">
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Change Password -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-lock"></i> Change Password</h3>
                        </div>
                        <div class="card-content">
                            <form method="POST" class="form">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" required 
                                           minlength="6" placeholder="At least 6 characters">
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-key"></i> Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="card" style="margin-top: 1.5rem;">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> System Information</h3>
                    </div>
                    <div class="card-content">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <div class="info-item">
                                <div class="info-label">Total Users</div>
                                <div class="info-value"><?= $totalUsers ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Total Products</div>
                                <div class="info-value"><?= $totalProducts ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Total Orders</div>
                                <div class="info-value"><?= $totalOrders ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Total Categories</div>
                                <div class="info-value"><?= $totalCategories ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">PHP Version</div>
                                <div class="info-value"><?= PHP_VERSION ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Server Software</div>
                                <div class="info-value"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card" style="margin-top: 1.5rem;">
                    <div class="card-header">
                        <h3><i class="fas fa-tools"></i> Quick Actions</h3>
                    </div>
                    <div class="card-content">
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <a href="../index.php" class="btn btn-outline" target="_blank">
                                <i class="fas fa-external-link-alt"></i> View Store Frontend
                            </a>
                            <a href="products.php" class="btn btn-primary">
                                <i class="fas fa-box"></i> Manage Products
                            </a>
                            <a href="orders.php" class="btn btn-success">
                                <i class="fas fa-shopping-cart"></i> View Orders
                            </a>
                            <a href="users.php" class="btn btn-warning">
                                <i class="fas fa-users"></i> Manage Users
                            </a>
                        </div>
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

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>

    <style>
        .info-item {
            padding: 1rem;
            background: var(--admin-bg-secondary);
            border-radius: 8px;
            text-align: center;
        }
        
        .info-label {
            font-size: 0.875rem;
            color: var(--admin-text-light);
            margin-bottom: 0.5rem;
        }
        
        .info-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--admin-text);
        }
        
        .readonly {
            background-color: var(--admin-bg-secondary);
            cursor: not-allowed;
        }
    </style>
</body>
</html>
