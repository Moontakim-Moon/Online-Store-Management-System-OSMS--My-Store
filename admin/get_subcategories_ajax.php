<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$parent_id = $_GET['parent_id'] ?? 0;
if (!$parent_id) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid parent category']);
    exit();
}

$subcategories = getSubcategories($parent_id);

header('Content-Type: application/json');
echo json_encode(['subcategories' => $subcategories]);
