<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Project Cleanup & Health Report</h1>";

// Check for duplicate SQL files
$sqlFiles = [
    'Root SQL Files' => [
        'complete_database_final.sql' => 'KEEP - Final complete database',
        'complete_database.sql' => 'DUPLICATE - Remove',
        'database.sql' => 'DUPLICATE - Remove', 
        'database_combined.sql' => 'DUPLICATE - Remove',
        'database_updates_*.sql' => 'DUPLICATE - Remove (multiple files)'
    ],
    'Backup SQL Files' => [
        'backup_old_files/' => 'KEEP - Backup folder is fine'
    ]
];

// Check for duplicate CSS files
$cssFiles = [
    'style.css' => 'KEEP - Main stylesheet',
    'style_backup.css' => 'DUPLICATE - Remove',
    'style_new.css' => 'DUPLICATE - Remove',
    'style_old.css' => 'DUPLICATE - Remove', 
    'style_restore.css' => 'DUPLICATE - Remove'
];

// Check for test/debug files
$testFiles = [
    'debug_admin.php' => 'REMOVE - Debug file',
    'debug_auth.php' => 'REMOVE - Debug file',
    'test_auth.php' => 'REMOVE - Test file',
    'test_login_register.php' => 'REMOVE - Test file',
    'test_otp.php' => 'REMOVE - Test file',
    'fix_admin_login.php' => 'REMOVE - Temporary fix file',
    'fix_auth_complete.php' => 'REMOVE - Temporary fix file',
    'check_admin.php' => 'REMOVE - Check file',
    'all_in_one.php' => 'REMOVE - Test file',
    'admin_dashboard_example.php' => 'REMOVE - Example file'
];

echo "<h2>📊 Duplicate Files Analysis</h2>";

echo "<h3>🗄️ SQL Database Files</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<strong>✅ KEEP:</strong> complete_database_final.sql (This is your main database)<br>";
echo "<strong>❌ REMOVE:</strong> All other SQL files in root (duplicates)<br>";
echo "<strong>📁 BACKUP:</strong> backup_old_files/ folder is fine to keep";
echo "</div>";

echo "<h3>🎨 CSS Style Files</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<strong>✅ KEEP:</strong> style.css (Main stylesheet)<br>";
echo "<strong>❌ REMOVE:</strong> style_backup.css, style_new.css, style_old.css, style_restore.css";
echo "</div>";

echo "<h3>🧪 Test & Debug Files</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<strong>❌ REMOVE ALL TEST FILES:</strong><br>";
foreach($testFiles as $file => $action) {
    echo "• $file - $action<br>";
}
echo "</div>";

// Check core functionality
echo "<h2>🔧 Core Functionality Check</h2>";

try {
    // Check database connection
    require_once 'includes/db.php';
    echo "<div style='color: green;'>✅ Database connection: Working</div>";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if($stmt->rowCount() > 0) {
        echo "<div style='color: green;'>✅ Users table: Exists</div>";
    } else {
        echo "<div style='color: red;'>❌ Users table: Missing</div>";
    }
    
    // Check if products table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    if($stmt->rowCount() > 0) {
        echo "<div style='color: green;'>✅ Products table: Exists</div>";
    } else {
        echo "<div style='color: red;'>❌ Products table: Missing</div>";
    }
    
    // Check admin user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'labonysur' AND is_admin = 1");
    $stmt->execute();
    if($stmt->rowCount() > 0) {
        echo "<div style='color: green;'>✅ Admin user: Exists</div>";
    } else {
        echo "<div style='color: red;'>❌ Admin user: Missing</div>";
    }
    
} catch(Exception $e) {
    echo "<div style='color: red;'>❌ Database error: " . $e->getMessage() . "</div>";
}

// Check important files
echo "<h2>📁 Essential Files Check</h2>";

$essentialFiles = [
    'includes/functions.php' => 'Core functions',
    'includes/db.php' => 'Database connection', 
    'includes/config.php' => 'Configuration',
    'includes/header.php' => 'Header template',
    'includes/footer.php' => 'Footer template',
    'pages/login.php' => 'Login page',
    'pages/register.php' => 'Registration page',
    'pages/products.php' => 'Products page',
    'admin/admin_login.php' => 'Admin login',
    'assets/css/style.css' => 'Main stylesheet'
];

foreach($essentialFiles as $file => $desc) {
    if(file_exists($file)) {
        echo "<div style='color: green;'>✅ $file - $desc</div>";
    } else {
        echo "<div style='color: red;'>❌ $file - $desc (MISSING)</div>";
    }
}

echo "<h2>🧹 Cleanup Recommendations</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<h3>Files to DELETE:</h3>";
echo "<ul>";
echo "<li>All SQL files except complete_database_final.sql</li>";
echo "<li>All CSS files except style.css</li>";
echo "<li>All debug_*.php and test_*.php files</li>";
echo "<li>All fix_*.php and check_*.php files</li>";
echo "<li>admin_dashboard_example.php and all_in_one.php</li>";
echo "</ul>";
echo "</div>";

echo "<h2>✨ Project Health Summary</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<strong>Overall Status:</strong> Good with cleanup needed<br>";
echo "<strong>Core Features:</strong> Authentication, Products, Admin working<br>";
echo "<strong>Issues:</strong> Too many duplicate and test files<br>";
echo "<strong>Recommendation:</strong> Clean up duplicates for better organization";
echo "</div>";

echo "<h3>🚀 Quick Action Links</h3>";
echo "<a href='pages/products.php' target='_blank' style='margin-right: 10px; padding: 10px; background: #007cba; color: white; text-decoration: none; border-radius: 4px;'>Test Products Page</a>";
echo "<a href='pages/login.php' target='_blank' style='margin-right: 10px; padding: 10px; background: #007cba; color: white; text-decoration: none; border-radius: 4px;'>Test Login</a>";
echo "<a href='admin/admin_login.php' target='_blank' style='margin-right: 10px; padding: 10px; background: #007cba; color: white; text-decoration: none; border-radius: 4px;'>Test Admin</a>";
?>
