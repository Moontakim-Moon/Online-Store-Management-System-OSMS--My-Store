<?php
// Enable error reporting
error_reporting(E_ALL);
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('display_startup_errors', 1);

// Test if we can write to the logs directory
$log_file = __DIR__ . '/logs/php_errors.log';

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Test file writing
$test_write = @file_put_contents($log_file, "Test write at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Get current error log settings
$current_error_log = ini_get('error_log');
$current_display_errors = ini_get('display_errors');
$current_error_reporting = error_reporting();
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Error Check</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f4f4f4; padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>PHP Error Configuration Check</h1>
    
    <h2>Current Settings</h2>
    <ul>
        <li>PHP Version: <?= phpversion() ?></li>
        <li>Error Log: <?= $current_error_log ?: '<span class="error">Not set</span>' ?></li>
        <li>Display Errors: <?= $current_display_errors ? 'On' : 'Off' ?></li>
        <li>Error Reporting: <?= $current_error_reporting ?></li>
    </ul>
    
    <h2>File System Check</h2>
    <ul>
        <li>Log directory exists: <?= is_dir(__DIR__ . '/logs') ? '<span class="success">Yes</span>' : '<span class="error">No</span>' ?></li>
        <li>Can write to log file: <?= $test_write ? '<span class="success">Yes</span>' : '<span class="error">No</span>' ?></li>
    </ul>
    
    <h2>Test Error Logging</h2>
    <p>The following test error was generated:</p>
    <?php
    // Generate a test error
    $undefined_var = $non_existent_array['test'];
    
    // Force error log
    error_log("This is a test error message from check_errors.php");
    ?>
    
    <h2>Check Error Log</h2>
    <p>Contents of <?= htmlspecialchars($log_file) ?>:</p>
    <pre><?= file_exists($log_file) ? htmlspecialchars(file_get_contents($log_file)) : 'Log file does not exist or is not readable'; ?></pre>
    
    <h2>PHP Info</h2>
    <p><a href="phpinfo.php">View phpinfo()</a></p>
    
    <h2>Test Database Connection</h2>
    <?php
    try {
        require_once 'includes/db.php';
        $stmt = $pdo->query('SELECT 1');
        echo '<p class="success">✅ Database connection successful!</p>';
        
        // Test user query
        $stmt = $pdo->query("SELECT * FROM users WHERE username = 'labonysur'");
        $user = $stmt->fetch();
        
        if ($user) {
            echo '<p class="success">✅ Test user found in database!</p>';
            echo '<pre>User data: ' . print_r($user, true) . '</pre>';
        } else {
            echo '<p class="error">❌ Test user not found in database.</p>';
        }
        
    } catch (PDOException $e) {
        echo '<p class="error">❌ Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    ?>
</body>
</html>
