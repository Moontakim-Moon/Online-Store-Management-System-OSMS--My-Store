<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Complete Database Setup</h1>";

// Try different connection configurations
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
        exit;
    }
}

// Step 2: Create database
echo "<h2>Step 2: Creating Database</h2>";
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS store_db");
    echo "<div style='color: green;'>✅ Database 'store_db' created/verified</div>";
} catch(PDOException $e) {
    echo "<div style='color: red;'>❌ Database creation failed: " . $e->getMessage() . "</div>";
}

// Step 3: Connect to database
echo "<h2>Step 3: Connecting to Database</h2>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=store_db", "root", "232-15-473@Labony");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div style='color: green;'>✅ Connected to store_db database</div>";
} catch(PDOException $e) {
    echo "<div style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</div>";
    exit;
}

// Step 4: Create essential tables
echo "<h2>Step 4: Creating Tables</h2>";

// Users table
try {
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        is_admin BOOLEAN DEFAULT FALSE,
        email_verified BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "<div style='color: green;'>✅ Users table created</div>";
} catch(PDOException $e) {
    echo "<div style='color: red;'>❌ Users table error: " . $e->getMessage() . "</div>";
}

// Categories table
try {
    $sql = "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        parent_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "<div style='color: green;'>✅ Categories table created</div>";
} catch(PDOException $e) {
    echo "<div style='color: red;'>❌ Categories table error: " . $e->getMessage() . "</div>";
}

// Products table
try {
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        category_id INT,
        image VARCHAR(255),
        stock_quantity INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql);
    echo "<div style='color: green;'>✅ Products table created</div>";
} catch(PDOException $e) {
    echo "<div style='color: red;'>❌ Products table error: " . $e->getMessage() . "</div>";
}

// Step 5: Create admin user
echo "<h2>Step 5: Setting Up Admin User</h2>";
try {
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'labonysur'");
    $stmt->execute();
    
    if($stmt->rowCount() == 0) {
        $password_hash = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin, email_verified) VALUES (?, ?, ?, 1, 1)");
        $stmt->execute(['labonysur', 'labonysur6@gmail.com', $password_hash]);
        echo "<div style='color: green;'>✅ Admin user created</div>";
    } else {
        // Update existing admin
        $password_hash = password_hash('password', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, is_admin = 1, email_verified = 1 WHERE username = 'labonysur'");
        $stmt->execute([$password_hash]);
        echo "<div style='color: green;'>✅ Admin user updated</div>";
    }
} catch(PDOException $e) {
    echo "<div style='color: red;'>❌ Admin setup error: " . $e->getMessage() . "</div>";
}

// Step 6: Add sample categories
echo "<h2>Step 6: Adding Sample Data</h2>";
try {
    // Check if categories exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    $count = $stmt->fetchColumn();
    
    if($count == 0) {
        $categories = [
            "Men's Clothing",
            "Women's Clothing", 
            "Electronics",
            "Books",
            "Home & Garden"
        ];
        
        foreach($categories as $cat) {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$cat]);
        }
        echo "<div style='color: green;'>✅ Sample categories added</div>";
    } else {
        echo "<div style='color: blue;'>📝 Categories already exist ($count found)</div>";
    }
} catch(PDOException $e) {
    echo "<div style='color: red;'>❌ Categories error: " . $e->getMessage() . "</div>";
}

// Step 7: Add sample products
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $count = $stmt->fetchColumn();
    
    if($count == 0) {
        $products = [
            ["Classic Blue Suit", "Premium quality blue suit for formal occasions", 299.99, 1],
            ["Cotton T-Shirt", "Comfortable cotton t-shirt", 19.99, 1],
            ["Summer Dress", "Light and airy summer dress", 49.99, 2],
            ["Wireless Headphones", "High-quality wireless headphones", 89.99, 3],
            ["Programming Book", "Learn programming fundamentals", 39.99, 4]
        ];
        
        foreach($products as $product) {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id) VALUES (?, ?, ?, ?)");
            $stmt->execute($product);
        }
        echo "<div style='color: green;'>✅ Sample products added</div>";
    } else {
        echo "<div style='color: blue;'>📝 Products already exist ($count found)</div>";
    }
} catch(PDOException $e) {
    echo "<div style='color: red;'>❌ Products error: " . $e->getMessage() . "</div>";
}

// Step 8: Test login function
echo "<h2>Step 7: Testing Authentication</h2>";

function testAuth($pdo, $username, $password) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    } catch(Exception $e) {
        echo "Auth error: " . $e->getMessage();
        return false;
    }
}

$admin_test = testAuth($pdo, 'labonysur', 'password');
if($admin_test) {
    echo "<div style='color: green;'>✅ Admin authentication working</div>";
} else {
    echo "<div style='color: red;'>❌ Admin authentication failed</div>";
}

echo "<h2>🎉 Database Setup Complete!</h2>";
echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>✅ Setup Summary:</h3>";
echo "<ul>";
echo "<li>Database 'store_db' created</li>";
echo "<li>Essential tables created (users, categories, products)</li>";
echo "<li>Admin user setup: labonysur / password</li>";
echo "<li>Sample data added</li>";
echo "<li>Authentication tested</li>";
echo "</ul>";

echo "<h3>🚀 Test Your System:</h3>";
echo "<a href='pages/products.php' target='_blank' style='margin: 5px; padding: 10px 15px; background: #007cba; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>🛍️ Products Page</a>";
echo "<a href='pages/login.php' target='_blank' style='margin: 5px; padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>🔐 Login Page</a>";
echo "<a href='admin/admin_login.php' target='_blank' style='margin: 5px; padding: 10px 15px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>👨‍💼 Admin Login</a>";
echo "</div>";
?>
