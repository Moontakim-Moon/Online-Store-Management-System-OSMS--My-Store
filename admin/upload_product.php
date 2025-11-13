<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']))                                     <label for="price" class="form-label">Price ($)</label>
                                    <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="stock" class="form-label">Stock</label>
                                    <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Product Image</label>
                                <div class="drag-drop-zone" id="dropZone">
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required hidden>
                                    <div class="text-center">
                                        <i class="fas fa-cloud-upload-alt fa-3x mb-2"></i>
                                        <p class="mb-0">Drag and drop your image here or</p>
                                        <button type="button" class="btn btn-outline-primary mt-2" onclick="document.getElementById('image').click()">
                                            Choose File
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
                                <button type="submit" class="btn btn-primary btn-lg">Upload Product</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-kit-code.js" crossorigin="anonymous"></script>
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
                        }
                    })
                    .catch(error => console.error('Error:', error));
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
</html>tion: login.php");
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
            // Generate unique filename with timestamp
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $new_filename = date('Ymd_His') . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            // Create thumbnail version
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Resize image to standard size if needed
                list($width, $height) = getimagesize($upload_path);
                if ($width > 800 || $height > 800) {
                    $image = imagecreatefromstring(file_get_contents($upload_path));
                    $new_width = $width;
                    $new_height = $height;
                    
                    if ($width > $height) {
                        $new_width = 800;
                        $new_height = round(800 * $height / $width);
                    } else {
                        $new_height = 800;
                        $new_width = round(800 * $width / $height);
                    }
                    
                    $tmp = imagecreatetruecolor($new_width, $new_height);
                    imagealphablending($tmp, false);
                    imagesavealpha($tmp, true);
                    imagecopyresampled($tmp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    
                    switch($file_extension) {
                        case 'jpg':
                        case 'jpeg':
                            imagejpeg($tmp, $upload_path, 90);
                            break;
                        case 'png':
                            imagepng($tmp, $upload_path, 9);
                            break;
                        case 'gif':
                            imagegif($tmp, $upload_path);
                            break;
                        case 'webp':
                            imagewebp($tmp, $upload_path, 90);
                            break;
                    }
                    
                    imagedestroy($tmp);
                    imagedestroy($image);
                }
                
                $image_url = '/assets/images/products/' . $new_filename;
            } else {
                $error = "Failed to upload image. Please check folder permissions.";
            }
        } else {
            $error = "Invalid image format or size too large. Allowed formats: JPG, PNG, GIF, WEBP. Max size: 5MB";
        }
    }

    if (!$error) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO products (name, category_id, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category_id, $description, $price, $stock, $image_url]);
        $message = "Product added successfully!";
    }
}

// Fetch categories for dropdown
global $pdo;
$categories = $pdo->query("SELECT id, name FROM categories WHERE parent_id IS NULL")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .image-preview {
            max-width: 300px;
            max-height: 300px;
            margin-top: 10px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 10px;
            display: none;
        }
        .image-preview img {
            width: 100%;
            height: auto;
            border-radius: 4px;
        }
        .drag-drop-zone {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background-color: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .drag-drop-zone:hover {
            background-color: #e9ecef;
            border-color: #6c757d;
        }
        .drag-drop-zone.dragover {
            background-color: #e2e6ea;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Upload New Product</h2>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= $message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="upload_product.php" enctype="multipart/form-data" id="productForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">Main Category</label>
                                    <select class="form-select" id="category" name="category_id" required>
                                        <option value="">Select Main Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="subcategory" class="form-label">Subcategory</label>
                                    <select class="form-select" id="subcategory" name="subcategory_id" required disabled>
                                        <option value="">Select Main Category First</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Price ($)</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
            </div>
            
            <div class="mb-3">
                <label for="stock" class="form-label">Stock</label>
                <input type="number" class="form-control" id="stock" name="stock" required>
            </div>
            
            <div class="mb-3">
                <label for="image" class="form-label">Product Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Upload Product</button>
        </form>
    </div>
</body>
</html>
