<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Complete Database Setup</h1>";

// Try different MySQL configurations
$configs = [
    ['localhost', 'root', ''],
    ['localhost', 'root', '232-15-473@Labony'],
    ['127.0.0.1', 'root', ''],
    ['127.0.0.1', 'root', '232-15-473@Labony']
];

$pdo = null;
$working_config = null;

foreach ($configs as $i => $config) {
    try {
        echo "Testing connection " . ($i + 1) . "...<br>";
        $pdo = new PDO("mysql:host={$config[0]}", $config[1], $config[2]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Connected with config " . ($i + 1) . "<br>";
        $working_config = $config;
        break;
    } catch(PDOException $e) {
        echo "❌ Config " . ($i + 1) . " failed<br>";
    }
}

if (!$pdo) {
    die("❌ All database connections failed");
}

echo "<h2>Creating Complete Database</h2>";

// Execute complete database setup
try {
    // Drop and recreate database
    $pdo->exec("DROP DATABASE IF EXISTS store");
    $pdo->exec("CREATE DATABASE store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database 'store' created<br>";
    
    // Connect to new database
    $pdo = new PDO("mysql:host={$working_config[0]};dbname=store", $working_config[1], $working_config[2]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table
    $pdo->exec("
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        is_admin BOOLEAN DEFAULT FALSE,
        email_verified BOOLEAN DEFAULT FALSE,
        email_otp VARCHAR(10) DEFAULT NULL,
        email_otp_expires DATETIME DEFAULT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        full_name VARCHAR(100) DEFAULT NULL,
        address TEXT DEFAULT NULL,
        city VARCHAR(50) DEFAULT NULL,
        postal_code VARCHAR(20) DEFAULT NULL,
        country VARCHAR(50) DEFAULT 'Bangladesh',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✅ Users table created<br>";
    
    // Create categories table
    $pdo->exec("
    CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        parent_id INT DEFAULT NULL,
        description TEXT DEFAULT NULL,
        image VARCHAR(255) DEFAULT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_name_parent (name, parent_id),
        FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
    )");
    echo "✅ Categories table created<br>";
    
    // Create products table
    $pdo->exec("
    CREATE TABLE products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        short_description VARCHAR(500),
        price DECIMAL(10,2) NOT NULL,
        sale_price DECIMAL(10,2) DEFAULT NULL,
        stock INT DEFAULT 0,
        sku VARCHAR(100) UNIQUE,
        weight DECIMAL(8,2) DEFAULT NULL,
        dimensions VARCHAR(100) DEFAULT NULL,
        image VARCHAR(255),
        gallery TEXT,
        status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
        featured BOOLEAN DEFAULT FALSE,
        meta_title VARCHAR(255),
        meta_description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )");
    echo "✅ Products table created<br>";
    
    // Create orders table
    $pdo->exec("
    CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        tax_amount DECIMAL(10,2) DEFAULT 0,
        shipping_amount DECIMAL(10,2) DEFAULT 0,
        discount_amount DECIMAL(10,2) DEFAULT 0,
        status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
        payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
        payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
        shipping_address TEXT,
        billing_address TEXT,
        customer_notes TEXT,
        admin_notes TEXT,
        tracking_number VARCHAR(100),
        shipped_at DATETIME NULL,
        delivered_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "✅ Orders table created<br>";
    
    // Create cart items table
    $pdo->exec("
    CREATE TABLE cart_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        attributes JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_product (user_id, product_id)
    )");
    echo "✅ Cart items table created<br>";
    
    // Insert admin user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin, email_verified, full_name, created_at) VALUES (?, ?, ?, TRUE, TRUE, ?, NOW())");
    $stmt->execute(['labonysur', 'labonysur6@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User']);
    echo "✅ Admin user created<br>";
    
    // Insert sample categories
    $pdo->exec("
    INSERT INTO categories (name, parent_id, description, is_active, sort_order) VALUES 
    ('Men', NULL, 'Men\\'s fashion and accessories', TRUE, 1),
    ('Women', NULL, 'Women\\'s fashion and accessories', TRUE, 2),
    ('Electronics', NULL, 'Electronic devices and gadgets', TRUE, 3)
    ");
    echo "✅ Sample categories created<br>";
    
    // Update db.php with working configuration
    $db_content = "<?php
\$host = \"{$working_config[0]}\";
\$dbname = \"store\";
\$username = \"{$working_config[1]}\";
\$password = \"{$working_config[2]}\";

try {
    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname\", \$username, \$password);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException \$e) {
    die(\"Connection failed: \" . \$e->getMessage());
}
?>";
    
    file_put_contents('includes/db.php', $db_content);
    echo "✅ Updated db.php with working configuration<br>";
    
    // Test admin login
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'labonysur'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify('password', $admin['password'])) {
        echo "<div style='background: #d4edda; padding: 20px; border: 2px solid #28a745; margin: 20px 0; border-radius: 5px;'>";
        echo "<h2>✅ SETUP COMPLETE!</h2>";
        echo "<strong>Admin Login Details:</strong><br>";
        echo "Username: <code>labonysur</code><br>";
        echo "Password: <code>password</code><br>";
        echo "Email: <code>labonysur6@gmail.com</code><br>";
        echo "<br><strong>Database:</strong> store<br>";
        echo "<strong>Host:</strong> {$working_config[0]}<br>";
        echo "<strong>User:</strong> {$working_config[1]}<br>";
        echo "</div>";
    } else {
        echo "❌ Admin user verification failed<br>";
    }
    
} catch(PDOException $e) {
    echo "❌ Database setup error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo '<div style="text-align: center; margin: 20px;">';
echo '<a href="pages/login.php" style="background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 18px;">🚀 LOGIN NOW</a>';
echo '</div>';
?>
