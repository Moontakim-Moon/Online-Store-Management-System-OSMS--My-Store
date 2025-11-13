<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $category_id = $_POST['subcategory_id'] ?? $_POST['category_id'] ?? 0;

    // Handle file upload
    $upload_dir = '../assets/images/products/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $image_url = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);

        if (in_array($mime_type, $allowed_types) && $_FILES['image']['size'] <= $max_size) {
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $new_filename = date('Ymd_His') . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = 'assets/images/products/' . $new_filename;
            } else {
                $error = "Failed to upload image. Please check folder permissions.";
            }
        } else {
            $error = "Invalid image format or size too large. Allowed formats: JPG, PNG, GIF, WEBP. Max size: 5MB";
        }
    }

    if (!$error) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, category_id, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category_id, $description, $price, $stock, $image_url]);
            $message = "Product added successfully!";
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch categories for dropdown
try {
    $categories = $pdo->query("SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
    $error = "Could not load categories: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Product - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f4d03f 0%, #f7dc6f 50%, #fcf3cf 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .btn-primary {
            background: linear-gradient(135deg, #f4d03f, #d68910);
            border: none;
            color: #333;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #d68910, #f4d03f);
            color: #333;
        }
        .form-control:focus, .form-select:focus {
            border-color: #f4d03f;
            box-shadow: 0 0 0 0.2rem rgba(244, 208, 63, 0.25);
        }
        .drag-drop-zone {
            border: 2px dashed #f4d03f;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            background: linear-gradient(135deg, #fcf3cf, #fef9e7);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .drag-drop-zone:hover {
            background: linear-gradient(135deg, #f7dc6f, #fcf3cf);
            border-color: #d68910;
        }
        .image-preview {
            max-width: 300px;
            margin: 15px auto;
            border: 3px solid #f4d03f;
            border-radius: 15px;
            padding: 10px;
            display: none;
            background: white;
        }
        .image-preview img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #f4d03f, #d68910); color: #333;">
                        <h2 class="mb-0"><i class="fas fa-plus-circle"></i> Upload New Product</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="upload_product_fixed.php" enctype="multipart/form-data" id="productForm">
                            <div class="mb-3">
                                <label for="name" class="form-label"><i class="fas fa-tag"></i> Product Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label"><i class="fas fa-list"></i> Main Category</label>
                                    <select class="form-select" id="category" name="category_id" required>
                                        <option value="">Select Main Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="subcategory" class="form-label"><i class="fas fa-sitemap"></i> Subcategory</label>
                                    <select class="form-select" id="subcategory" name="subcategory_id" required disabled>
                                        <option value="">Select Main Category First</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label"><i class="fas fa-align-left"></i> Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label"><i class="fas fa-dollar-sign"></i> Price ($)</label>
                                    <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="stock" class="form-label"><i class="fas fa-boxes"></i> Stock</label>
                                    <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-image"></i> Product Image</label>
                                <div class="drag-drop-zone" id="dropZone">
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required hidden>
                                    <div class="text-center">
                                        <i class="fas fa-cloud-upload-alt fa-3x mb-2" style="color: #d68910;"></i>
                                        <p class="mb-0">Drag and drop your image here or</p>
                                        <button type="button" class="btn btn-outline-warning mt-2" onclick="document.getElementById('image').click()">
                                            <i class="fas fa-folder-open"></i> Choose File
                                        </button>
                                        <p class="text-muted mt-2">
                                            <small>Supported formats: JPG, PNG, GIF, WEBP<br>Maximum file size: 5MB</small>
                                        </p>
                                    </div>
                                </div>
                                <div class="image-preview mt-3" id="imagePreview">
                                    <img src="" alt="Preview" id="preview">
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-upload"></i> Upload Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category');
        const subcategorySelect = document.getElementById('subcategory');
        const dropZone = document.getElementById('dropZone');
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('preview');
        
        // Handle category change
        categorySelect.addEventListener('change', function() {
            const parentId = this.value;
            subcategorySelect.disabled = !parentId;
            
            if (parentId) {
                fetch(`get_subcategories_ajax.php?parent_id=${parentId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.subcategories) {
                            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                            data.subcategories.forEach(sub => {
                                subcategorySelect.innerHTML += `
                                    <option value="${sub.id}">${sub.name}</option>
                                `;
                            });
                        } else {
                            subcategorySelect.innerHTML = '<option value="">No subcategories found</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        subcategorySelect.innerHTML = '<option value="">Error loading subcategories</option>';
                    });
            } else {
                subcategorySelect.innerHTML = '<option value="">Select Main Category First</option>';
            }
        });

        // Handle drag and drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('dragover');
        }

        function unhighlight(e) {
            dropZone.classList.remove('dragover');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            imageInput.files = files;
            handleFiles(files);
        }

        imageInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        function handleFiles(files) {
            if (files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            }
        }
    });
    </script>
</body>
</html>
