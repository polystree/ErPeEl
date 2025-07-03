<?php
require "koneksi.php"; 
require "session.php";

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

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
    $image_url = htmlspecialchars($_POST['image_url']);

    if (empty($nama) || empty($pengembang) || empty($harga) || empty($image_url)) {
        $error_message = "Game name, developer, price, and image URL are required.";
    } elseif ($harga_diskon !== null && floatval($harga_diskon) >= floatval($harga)) {
        $error_message = "Discount price must be lower than the regular price.";
    } elseif (!filter_var($image_url, FILTER_VALIDATE_URL)) {
        $error_message = "Please enter a valid URL for the image.";
    } else {
        $sql_tambah = "INSERT INTO produk (nama, detail, pengembang, kategori_id, harga, harga_diskon, foto) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $con->prepare($sql_tambah);
        $stmt_insert->bind_param('sssidds', $nama, $detail, $pengembang, $kategori_id, $harga, $harga_diskon, $image_url);

        if ($stmt_insert->execute()) {
            $success_message = "Game added successfully!";
            header("Location: admin.php"); 
            exit(); 
        } else {
            $error_message = "Error adding game.";
        }
        $stmt_insert->close();
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
                <a href="admin.php" aria-label="Back to home">Vault</a>
            </div>
            
            <button class="mobile-menu-toggle" aria-label="Toggle mobile menu" aria-expanded="false" onclick="toggleMobileMenu()">
                <span class="hamburger-icon">☰</span>
            </button>

            <div class="menu" role="menubar" id="mobile-menu">
                <a href="admin.php" class="menu-item" role="menuitem">Manage Games</a>
                <a href="order.php" class="menu-item" role="menuitem">Order Management</a>
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

                <form class="game-form" method="post">
                    <!-- Image URL -->
                    <div class="form-group">
                        <label for="image_url">Game Image URL</label>
                        <input type="url" class="form-input" name="image_url" id="image_url" placeholder="https://example.com/image.jpg" required>
                        <div class="form-hint">Enter a direct URL to the game image</div>
                        
                        <!-- Image Preview Container -->
                        <div class="image-preview-container" id="image-preview-container" style="display: none;">
                            <div class="image-preview-header">
                                <span class="preview-title">Image Preview</span>
                                <button type="button" class="remove-image-btn" onclick="clearImagePreview()">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="image-preview-wrapper">
                                <img id="image-preview" src="" alt="Preview" class="image-preview">
                            </div>
                            <div class="image-info">
                                <span id="image-url-display"></span>
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

        // Image preview functionality for URL
        function previewImageFromUrl() {
            const urlInput = document.getElementById('image_url');
            const url = urlInput.value.trim();
            const previewContainer = document.getElementById('image-preview-container');
            const previewImg = document.getElementById('image-preview');
            const imageUrlDisplay = document.getElementById('image-url-display');

            if (url && isValidImageUrl(url)) {
                // Test if the image can be loaded
                const testImg = new Image();
                testImg.onload = function() {
                    previewImg.src = url;
                    imageUrlDisplay.textContent = url;
                    previewContainer.style.display = 'block';
                };
                testImg.onerror = function() {
                    clearImagePreview();
                    urlInput.setCustomValidity('Unable to load image from this URL');
                };
                testImg.src = url;
            } else {
                clearImagePreview();
                if (url) {
                    urlInput.setCustomValidity('Please enter a valid image URL');
                } else {
                    urlInput.setCustomValidity('');
                }
            }
        }

        function isValidImageUrl(url) {
            try {
                const urlObj = new URL(url);
                const pathname = urlObj.pathname.toLowerCase();
                return pathname.match(/\.(jpg|jpeg|png|gif|webp|avif|bmp|svg)$/i);
            } catch {
                return false;
            }
        }

        function clearImagePreview() {
            const previewContainer = document.getElementById('image-preview-container');
            const previewImg = document.getElementById('image-preview');
            const urlInput = document.getElementById('image_url');
            
            previewImg.src = '';
            previewContainer.style.display = 'none';
            urlInput.setCustomValidity('');
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
            const imageUrlInput = document.getElementById('image_url');
            const gameForm = document.querySelector('.game-form');
            
            // Validate on input change
            regularPriceInput.addEventListener('input', validatePrices);
            discountPriceInput.addEventListener('input', validatePrices);
            
            // Add image URL validation
            imageUrlInput.addEventListener('input', previewImageFromUrl);
            imageUrlInput.addEventListener('blur', previewImageFromUrl);
            
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
