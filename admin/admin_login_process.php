<?php
session_start();
require_once "../includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";
    
    // Use the loginUser function to verify credentials
    $user = loginUser($username, $password);
    
    if ($user && $user['is_admin']) {
        // Set admin session
        $_SESSION["user_id"] = $user['id'];
        $_SESSION["username"] = $user['username'];
        $_SESSION["is_admin"] = true;
        
        // Redirect to admin dashboard
        header("Location: index.php");
        exit();
    } else {
        // Invalid credentials or not an admin
        header("Location: admin_login.php?error=1");
        exit();
    }
} else {
    header("Location: admin_login.php");
    exit();
}
?>