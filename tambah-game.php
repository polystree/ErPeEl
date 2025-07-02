<?php
require "koneksi.php"; 
require "session.php";

// Get user info
$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($con, "SELECT foto FROM `users` WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);
$user_foto = $user_data ? $user_data['foto'] : null;

if (isset($_POST['submit'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $detail = htmlspecialchars($_POST['detail']);
    $pengembang = htmlspecialchars($_POST['pengembang']);
    $kategori_id = htmlspecialchars($_POST['kategori_id']);
    $harga = htmlspecialchars($_POST['harga']);
    $harga_diskon = !empty($_POST['harga_diskon']) ? htmlspecialchars($_POST['harga_diskon']) : null;

    $target_dir = "image/";
    $nama_file = basename($_FILES["foto"]["name"]);
    $target_file = $target_dir . $nama_file; 
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $img_size = $_FILES["foto"]["size"];

    if (empty($nama) || empty($pengembang) || empty($harga)) {
        $error_message = "Game name, developer, and price are required.";
    } elseif ($harga_diskon !== null && floatval($harga_diskon) >= floatval($harga)) {
        $error_message = "Discount price must be lower than the regular price.";
    } elseif ($img_size > 10000000) { 
        $error_message = "File must not be larger than 10MB.";
    } elseif (!in_array($imageFileType, ['avif', 'png', 'jpg', 'jpeg'])) {
        $error_message = "File must be AVIF, PNG, JPG, or JPEG format.";
    } else {
        // Upload file
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            $sql_tambah = "INSERT INTO produk (nama, detail, pengembang, kategori_id, harga, harga_diskon, foto) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $con->prepare($sql_tambah);
            $stmt_insert->bind_param('sssidds', $nama, $detail, $pengembang, $kategori_id, $harga, $harga_diskon, $nama_file);

            if ($stmt_insert->execute()) {
                $success_message = "Game added successfully!";
                header("Location: admin.php"); 
                exit(); 
            } else {
                $error_message = "Error adding game.";
            }
            $stmt_insert->close();
        } else {
            $error_message = "Error uploading image.";
        }
    }
}

// Get categories for dropdown
$categories_query = "SELECT id, nama FROM kategori ORDER BY nama";
$categories_result = mysqli_query($con, $categories_query);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Game - Vault Digital Store</title>
    <link rel="stylesheet" href="tambah-game.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/favicon.png">
</head>
<body>

   
    <!-- Navigation -->
    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <div class="upper-nav">
            <div class="logo">
                <a href="dashboard.php" aria-label="Back to home">Vault</a>
            </div>
            
            <button class="mobile-menu-toggle" aria-label="Toggle mobile menu" aria-expanded="false" onclick="toggleMobileMenu()">
                <span class="hamburger-icon">☰</span>
            </button>

            <div class="menu" role="menubar" id="mobile-menu">
                <a href="admin.php" class="menu-item" role="menuitem">Manage Games</a>
                <a href="order.php" class="menu-item" role="menuitem">Order Management</a>
            </div>

            <div class="search-bar" role="search">
                <form method="GET" action="search.php">
                    <input type="text" name="query" placeholder="Search Games" class="search-input" aria-label="Enter game search keywords">
                    <button type="submit" class="search-icon" aria-label="Start search">
                        <img src="image/search-btn.svg" class="search-img" alt="" width="16" height="16">
                    </button>
                </form>
            </div>

            <div class="nav-icons">
                <div class="nav-icon">
                    <a href="cart.php" aria-label="View shopping cart">
                        <img src="image/cart-btn.svg" class="icon-img" alt="" width="20" height="20">
                    </a>
                </div>

                <div class="nav-icon profile">
                    <a href="profile.php" aria-label="View user profile">
                        <?php if ($user_foto): ?>
                            <img src="image/<?php echo $user_foto; ?>" class="icon-img profile-avatar" alt="" width="44" height="44" style="border-radius: 50%; object-fit: cover; filter: none; width: 44px; height: 44px;">
                        <?php else: ?>
                            <img src="image/profile white.svg" class="icon-img" alt="" width="20" height="20">
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>

  
    <!-- Main Content -->
    <main class="main-content">
        <div class="game-form-container">
            <!-- Back Button -->
            <div class="back-section">
                <a href="admin.php" class="back-button">
                    <img src="image/back purple.svg" class="back-icon" alt="">
                    Back to Admin
                </a>
            </div>

            <!-- Page Title -->
            <h1 class="page-title">Add New Game</h1>

            <!-- Form Container -->
            <div class="form-container">
                <h2 class="form-title">Game Information</h2>

                <form class="game-form" method="post" enctype="multipart/form-data">
                    <!-- Image Upload -->
                    <div class="form-group">
                        <label for="foto">Game Image</label>
                        <div class="file-upload-container">
                            <label for="foto" class="file-upload-label" id="file-upload-label">
                                <svg class="file-upload-icon" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                </svg>
                                <span class="file-upload-text">Click to upload image</span>
                                <span class="file-upload-hint">AVIF, PNG, JPG, JPEG up to 10MB</span>
                            </label>
                            <input type="file" name="foto" id="foto" class="file-input" accept=".jpg,.jpeg,.png,.avif" required onchange="previewImage(this)">
                            
                            <!-- Image Preview Container -->
                            <div class="image-preview-container" id="image-preview-container" style="display: none;">
                                <div class="image-preview-header">
                                    <span class="preview-title">Image Preview</span>
                                    <button type="button" class="remove-image-btn" onclick="removeImage()">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="image-preview-wrapper">
                                    <img id="image-preview" src="" alt="Preview" class="image-preview">
                                </div>
                                <div class="image-info">
                                    <span id="image-name"></span>
                                    <span id="image-size"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Game Name -->
                    <div class="form-group">
                        <label for="nama">Game Name</label>
                        <input type="text" class="form-input" name="nama" id="nama" autocomplete="off" required>
                    </div>

                    <!-- Game Description -->
                    <div class="form-group">
                        <label for="detail">Game Description</label>
                        <textarea name="detail" id="detail" class="form-textarea" rows="4"></textarea>
                    </div>

                    <!-- Developer -->
                    <div class="form-group">
                        <label for="pengembang">Developer</label>
                        <input type="text" class="form-input" name="pengembang" id="pengembang" required>
                    </div>

                    <!-- Category -->
                    <div class="form-group">
                        <label for="kategori_id">Category</label>
                        <select name="kategori_id" id="kategori_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php while($category = mysqli_fetch_assoc($categories_result)): ?>
                                <option value="<?= $category['id'] ?>">
                                    <?= htmlspecialchars($category['nama']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Price Fields -->
                    <div class="price-grid">
                        <div class="form-group">
                            <label for="harga">Price (USD)</label>
                            <input type="number" step="0.01" class="form-input" name="harga" id="harga" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label for="harga_diskon">Discount Price (USD)</label>
                            <input type="number" step="0.01" class="form-input" name="harga_diskon" id="harga_diskon" placeholder="0.00">
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" name="submit" class="btn-primary">
                            <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"/>
                            </svg>
                            Add Game
                        </button>
                    </div>
                </form>

                <!-- Messages -->
                <?php if (isset($error_message)): ?>
                    <div class="message message-error"><?= htmlspecialchars($error_message) ?></div>
                <?php elseif (isset($success_message)): ?>
                    <div class="message message-success"><?= htmlspecialchars($success_message) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </main>
  
    <!-- Footer -->
    <footer role="contentinfo">
        <div class="footer-content">
            <div class="footer-brand">
                <h3>Vault</h3>
                <p>Your ultimate destination for digital games</p>
            </div>
            <div class="footer-links">
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Community</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#" class="privacy-policy">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Refund Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Vault | Developed by Group 4 RPL</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            if (menu.style.display === 'flex') {
                menu.style.display = 'none';
                toggle.setAttribute('aria-expanded', 'false');
            } else {
                menu.style.display = 'flex';
                toggle.setAttribute('aria-expanded', 'true');
            }
        }

        // Image preview functionality
        function previewImage(input) {
            const file = input.files[0];
            const previewContainer = document.getElementById('image-preview-container');
            const uploadLabel = document.getElementById('file-upload-label');
            const previewImg = document.getElementById('image-preview');
            const imageName = document.getElementById('image-name');
            const imageSize = document.getElementById('image-size');

            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/avif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, JPG, PNG, AVIF)');
                    input.value = '';
                    return;
                }

                // Validate file size (10MB = 10 * 1024 * 1024 bytes)
                if (file.size > 10 * 1024 * 1024) {
                    alert('File size must not exceed 10MB');
                    input.value = '';
                    return;
                }

                // Create FileReader to read the file
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imageName.textContent = file.name;
                    imageSize.textContent = formatFileSize(file.size);
                    
                    // Hide upload label and show preview
                    uploadLabel.style.display = 'none';
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        function removeImage() {
            const input = document.getElementById('foto');
            const previewContainer = document.getElementById('image-preview-container');
            const uploadLabel = document.getElementById('file-upload-label');
            const previewImg = document.getElementById('image-preview');

            // Clear the input
            input.value = '';
            
            // Reset preview
            previewImg.src = '';
            
            // Show upload label and hide preview
            uploadLabel.style.display = 'flex';
            previewContainer.style.display = 'none';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Price validation
        function validatePrices() {
            const regularPrice = parseFloat(document.getElementById('harga').value) || 0;
            const discountPrice = parseFloat(document.getElementById('harga_diskon').value) || 0;
            const discountInput = document.getElementById('harga_diskon');
            
            // Remove any existing error styling
            discountInput.classList.remove('error');
            
            // Remove any existing error message
            const existingError = document.querySelector('.price-error-message');
            if (existingError) {
                existingError.remove();
            }
            
            if (discountPrice > 0 && discountPrice >= regularPrice) {
                // Add error styling
                discountInput.classList.add('error');
                
                // Add error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'price-error-message';
                errorDiv.style.color = 'var(--danger)';
                errorDiv.style.fontSize = '0.875rem';
                errorDiv.style.marginTop = '0.5rem';
                errorDiv.textContent = 'Discount price must be lower than regular price';
                
                discountInput.parentNode.appendChild(errorDiv);
                return false;
            }
            return true;
        }

        // Add event listeners when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const regularPriceInput = document.getElementById('harga');
            const discountPriceInput = document.getElementById('harga_diskon');
            const gameForm = document.querySelector('.game-form');
            
            // Validate on input change
            regularPriceInput.addEventListener('input', validatePrices);
            discountPriceInput.addEventListener('input', validatePrices);
            
            // Validate on form submit
            gameForm.addEventListener('submit', function(e) {
                if (!validatePrices()) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</body>
</html>
