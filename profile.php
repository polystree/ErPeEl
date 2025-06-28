<?php
require "session.php";
require "koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$select_profile = mysqli_query($con, "SELECT username, email, foto FROM users WHERE id = '$user_id'") or die(mysqli_error($con));

if (mysqli_num_rows($select_profile) > 0) {
    $fetch_profile = mysqli_fetch_assoc($select_profile);
    $username = $fetch_profile['username'];
    $email = $fetch_profile['email'];
    $foto = $fetch_profile['foto'];
} else {
    $username = $email = $foto = "";
}

if (isset($_POST['simpan'])) {
    $new_username = mysqli_real_escape_string($con, $_POST['username']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $tmp_name = $_FILES['image']['tmp_name'];

        $valid_extensions = ['jpg', 'jpeg', 'png', 'avif'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_extension, $valid_extensions)) {
            echo "<script>alert('Ekstensi gambar tidak valid.');</script>";
        } elseif ($file_size > 10000000) {
            echo "<script>alert('Ukuran file terlalu besar. Maksimal 10 MB.');</script>";
        } else {
            $new_image_name = uniqid() . '.' . $file_extension;
            $upload_path = 'image/' . $new_image_name;

            if (move_uploaded_file($tmp_name, $upload_path)) {
                $queryupdate = "UPDATE users SET username='$new_username', foto='$new_image_name' WHERE id='$user_id'";
            } else {
                echo "<script>alert('Gagal mengunggah gambar.');</script>";
            }
        }
    } else {
        $queryupdate = "UPDATE users SET username='$new_username' WHERE id='$user_id'";
    }

    mysqli_query($con, $queryupdate) or die(mysqli_error($con));
    header("Location: profile.php");
    exit();
}

if (isset($_POST['toggle_wishlist']) && isset($_POST['produk_id'])) {
    $produk_id = mysqli_real_escape_string($con, $_POST['produk_id']);

    $check_wishlist = mysqli_query($con, "SELECT * FROM wishlist WHERE produk_id = '$produk_id' AND user_id = '$user_id'");

    if (mysqli_num_rows($check_wishlist) > 0) {
        $delete_wishlist = mysqli_query($con, "DELETE FROM wishlist WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
        if (!$delete_wishlist) {
            http_response_code(500);
            echo "Error removing item from wishlist.";

            exit();
        }
    } else {

        $add_wishlist = mysqli_query($con, "INSERT INTO wishlist (produk_id, user_id) VALUES ('$produk_id', '$user_id')");
        if (!$add_wishlist) {
            http_response_code(500);
            echo "Error adding item to wishlist.";
            exit();
        }
    }
    http_response_code(200);
    echo "Wishlist updated successfully.";
}

$select_wishlist = mysqli_query($con, "SELECT c.*, p.nama, p.pengembang, p.foto, p.harga, p.harga_diskon FROM wishlist c JOIN produk p ON c.produk_id = p.id WHERE c.user_id = '$user_id'") or die('Query failed');


if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="User Profile - Vault Digital Store">
    <title>Profile - Vault Digital Store</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.png">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Orbitron:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>
    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <div class="upper-nav">
            <div class="logo">
                <a href="dashboard.php" aria-label="Back to home">Vault</a>
            </div>
            
            <button class="mobile-menu-toggle" aria-label="Toggle mobile menu" aria-expanded="false" onclick="toggleMobileMenu()">
                <span class="hamburger-icon">☰</span>
            </button>

            <div class="menu" role="menubar" id="mobile-menu">
                <a href="semua.php" class="menu-item" role="menuitem">All Games</a>
            </div>

            <div class="search-bar" role="search">
                <form method="GET" action="search.php">
                    <input type="text" name="query" placeholder="Search Games" class="search-input" 
                           aria-label="Enter game search keywords">
                    <button type="submit" class="search-icon" aria-label="Start search">
                        <img src="image/search-btn.svg" class="search-img" alt="" width="16" height="16">
                    </button>
                </form>
            </div>

            <div class="nav-icons">
                <div class="nav-icon">
                    <a href="cart.php" aria-label="View shopping cart">
                        <img src="image/cart-btn.svg" class="icon-img" alt="" width="20" height="20">
                        <?php
                        $cart_count_query = mysqli_query($con, "SELECT COUNT(*) as count FROM `cart` WHERE user_id = '$user_id'");
                        $cart_count = mysqli_fetch_assoc($cart_count_query)['count'];
                        if ($cart_count > 0) {
                            echo '<span class="cart-badge">' . ($cart_count > 99 ? '99+' : $cart_count) . '</span>';
                        }
                        ?>
                    </a>
                </div>

                <div class="nav-icon profile">
                    <a href="profile.php" aria-label="View user profile">
                        <img src="image/profile white.svg" class="icon-img" alt="" width="20" height="20">
                    </a>
                </div>
            </div>
        </div>
    </nav>


    <main class="main-content" id="main-content">
        <div class="content-layout">
            <aside class="categories-sidebar">
                <div class="sidebar-header">
                    <div class="user-avatar">
                        <img src="image/<?php echo $foto ? $foto : 'avatar.png'; ?>" 
                             alt="Profile Picture" class="avatar-image">
                    </div>
                    <div class="user-info">
                        <h3 class="user-name"><?php echo htmlspecialchars($username); ?></h3>
                        <p class="user-id">ID: #<?php echo htmlspecialchars($user_id); ?></p>
                    </div>
                </div>
                
                <div class="category-section">
                    <h4 class="category-main">Account</h4>
                    <ul class="category-list">
                        <li><a href="#" class="category-link active" data-section="profile">Profile Settings</a></li>
                        <li><a href="#" class="category-link" data-section="wishlist">My Wishlist</a></li>
                        <li><a href="#" class="category-link" data-section="orders">Order History</a></li>
                    </ul>
                </div>
                
                <div class="category-section">
                    <h4 class="category-main">Actions</h4>
                    <ul class="category-list">
                        <li>
                            <form method="POST" style="margin: 0;">
                                <button type="submit" name="logout" class="logout-link">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </aside>
            
            <div class="main-section">
                <!-- Profile Settings Section -->
                <section id="profile" class="game-sections active">
                    <div class="section-header">
                        <h2 class="section-title">Profile Settings</h2>
                        <button class="view-all-btn" onclick="document.getElementById('photo-input').click();">
                            Change Photo
                        </button>
                    </div>
                    
                    <div class="game-container" style="grid-template-columns: 1fr; max-width: 1600px;">
                        <div class="game-card" style="cursor: default; transform: none;">
                            <div class="game-info" style="padding: var(--space-xl);">
                                <!-- Horizontal Layout: Photo on Left, Info on Right -->
                                <div class="profile-horizontal-layout" style="display: flex; gap: var(--space-2xl); align-items: flex-start; margin-bottom: var(--space-xl);">
                                    <!-- Profile Photo Section -->
                                    <div class="profile-photo-section" style="flex-shrink: 0;">
                                        <div style="text-align: center;">
                                            <img src="image/<?php echo $foto ? $foto : 'avatar.png'; ?>" 
                                                 alt="Profile Photo" class="preview-image" id="preview-image"
                                                 style="width: 160px; height: 160px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary); margin-bottom: var(--space-md); box-shadow: 0 8px 25px rgba(139, 92, 246, 0.3);">
                                            <p style="color: var(--text-secondary); font-size: 0.85rem; max-width: 160px;">JPG, PNG or AVIF<br>Max size 10MB</p>
                                        </div>
                                    </div>
                                    
                                    <!-- User Information Section -->
                                    <div class="profile-info-section" style="flex: 1; min-width: 0;">
                                        <form method="POST" enctype="multipart/form-data" class="profile-form">
                                            <input type="file" name="image" accept="image/*" 
                                                   class="file-input" id="photo-input" style="display: none;">
                                            
                                            <div class="profile-form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-lg); margin-bottom: var(--space-xl);">
                                                <div>
                                                    <label style="display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--space-sm); font-weight: 500;">Username</label>
                                                    <input type="text" name="username" 
                                                           value="<?php echo htmlspecialchars($username); ?>" 
                                                           class="search-input" style="width: 100%; margin: 0;"
                                                           required>
                                                </div>
                                                
                                                <div>
                                                    <label style="display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--space-sm); font-weight: 500;">Email Address</label>
                                                    <input type="email" 
                                                           value="<?php echo htmlspecialchars($email); ?>" 
                                                           class="search-input" style="width: 100%; margin: 0; opacity: 0.6;"
                                                           disabled readonly>
                                                </div>
                                                
                                                <div>
                                                    <label style="display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--space-sm); font-weight: 500;">User ID</label>
                                                    <input type="text" 
                                                           value="#<?php echo htmlspecialchars($user_id); ?>" 
                                                           class="search-input" style="width: 100%; margin: 0; opacity: 0.6;"
                                                           disabled readonly>
                                                </div>
                                            </div>
                                            
                                            <!-- Save Button - Bottom Right -->
                                            <div style="display: flex; justify-content: flex-end;">
                                                <button type="submit" name="simpan" class="add-to-cart-btn" style="width: auto; margin: 0; padding: var(--space-md) var(--space-xl);">
                                                    <svg style="width: 16px; height: 16px; margin-right: var(--space-sm);" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"/>
                                                    </svg>
                                                    Save Changes
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Wishlist Section -->
                <section id="wishlist" class="game-sections">
                    <div class="section-header">
                        <h2 class="section-title">My Wishlist</h2>
                        <button class="view-all-btn" onclick="window.location.href='semua.php';">
                            Browse Games
                        </button>
                    </div>
                    
                    <?php if (mysqli_num_rows($select_wishlist) > 0): ?>
                        <div class="game-container">
                            <?php while ($fetch_wishlist = mysqli_fetch_assoc($select_wishlist)):
                                $produk_id = $fetch_wishlist['produk_id'];
                                ?>
                                <div class="game-card" onclick="window.location.href='detail.php?produk_id=<?php echo $produk_id; ?>'">
                                    <div class="game-cover-container">
                                        <img class="game-cover"
                                             src="image/<?php echo htmlspecialchars($fetch_wishlist['foto']); ?>"
                                             alt="<?php echo htmlspecialchars($fetch_wishlist['nama']); ?> cover"
                                             loading="lazy">
                                        <button type="button" class="wishlist-btn active"
                                            onclick="event.stopPropagation(); toggleWishlist(this);"
                                            data-produk-id="<?php echo $produk_id; ?>"
                                            data-in-wishlist="true"
                                            aria-label="Remove from wishlist">
                                            <svg class="heart-icon" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="game-info">
                                        <div class="game-price">
                                            <?php if ($fetch_wishlist['harga_diskon'] != NULL && $fetch_wishlist['harga_diskon'] > 0 && $fetch_wishlist['harga_diskon'] < $fetch_wishlist['harga']): ?>
                                                <span class="original-price">$<?php echo number_format($fetch_wishlist['harga'], 2); ?></span>
                                                <span class="discounted-price">$<?php echo number_format($fetch_wishlist['harga_diskon'], 2); ?></span>
                                            <?php else: ?>
                                                <span class="current-price">$<?php echo number_format($fetch_wishlist['harga'], 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <h3 class="game-title"><?php echo htmlspecialchars($fetch_wishlist['nama']); ?></h3>
                                        <p class="game-developer"><?php echo htmlspecialchars($fetch_wishlist['pengembang']); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-message" style="text-align: center; padding: var(--space-2xl);">
                            <svg style="width: 64px; height: 64px; margin-bottom: var(--space-lg); opacity: 0.5;" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                            <h3 style="margin-bottom: var(--space-md);">Your wishlist is empty</h3>
                            <p style="margin-bottom: var(--space-lg);">Browse our collection and add games to your wishlist</p>
                            <button class="add-to-cart-btn" onclick="window.location.href='semua.php';">Browse Games</button>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Orders Section -->
                <section id="orders" class="game-sections">
                    <div class="section-header">
                        <h2 class="section-title">Order History</h2>
                        <button class="view-all-btn" onclick="window.location.href='semua.php';">
                            Shop Now
                        </button>
                    </div>
                    
                    <?php 
                    $select_orders = mysqli_query($con, "SELECT id, user_id, total, created_at FROM orders WHERE user_id = '$user_id' ORDER BY created_at DESC");
                    if (mysqli_num_rows($select_orders) > 0): ?>
                        <div class="game-container" style="grid-template-columns: 1fr;">
                            <?php while ($fetch_orders = mysqli_fetch_assoc($select_orders)): ?>
                                <div class="game-card" style="cursor: pointer;" onclick="window.location.href='detailorder.php?order_id=<?php echo $fetch_orders['id']; ?>'">
                                    <div class="game-info">
                                        <div class="game-price">
                                            <span class="current-price">$<?php echo number_format($fetch_orders['total'], 2); ?></span>
                                            <span class="new-badge">Completed</span>
                                        </div>
                                        <h3 class="game-title">Order #<?php echo $fetch_orders['id']; ?></h3>
                                        <p class="game-developer">
                                            <?php echo date("M d, Y", strtotime($fetch_orders['created_at'])); ?> at 
                                            <?php echo date("H:i", strtotime($fetch_orders['created_at'])); ?>
                                        </p>
                                        <button class="add-to-cart-btn" onclick="event.stopPropagation(); window.location.href='detailorder.php?order_id=<?php echo $fetch_orders['id']; ?>'">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-message" style="text-align: center; padding: var(--space-2xl);">
                            <svg style="width: 64px; height: 64px; margin-bottom: var(--space-lg); opacity: 0.5;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14,2 14,8 20,8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10,9 9,9 8,9"/>
                            </svg>
                            <h3 style="margin-bottom: var(--space-md);">No orders yet</h3>
                            <p style="margin-bottom: var(--space-lg);">Your purchase history will appear here once you make your first order</p>
                            <button class="add-to-cart-btn" onclick="window.location.href='semua.php';">Start Shopping</button>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </main>
    
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
        // Navigation functionality - using dashboard style
        const categoryLinks = document.querySelectorAll('.category-link');
        const sections = document.querySelectorAll('.game-sections');

        categoryLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Remove active class from all links and sections
                categoryLinks.forEach(l => l.classList.remove('active'));
                sections.forEach(section => section.classList.remove('active'));

                // Add active class to clicked link and corresponding section
                link.classList.add('active');
                const targetSection = link.getAttribute('data-section');
                const section = document.getElementById(targetSection);
                if (section) {
                    section.classList.add('active');
                }
            });
        });

        // Photo upload functionality
        const photoInput = document.getElementById('photo-input');
        const previewImage = document.getElementById('preview-image');

        if (photoInput && previewImage) {
            photoInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImage.src = e.target.result;
                        // Update sidebar avatar too
                        const sidebarAvatar = document.querySelector('.avatar-image');
                        if (sidebarAvatar) {
                            sidebarAvatar.src = e.target.result;
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Mobile menu toggle
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            const toggle = document.querySelector('.mobile-menu-toggle');
            const hamburgerIcon = toggle.querySelector('.hamburger-icon');
            const isOpen = mobileMenu.classList.contains('mobile-open');
            
            if (isOpen) {
                mobileMenu.classList.remove('mobile-open');
                hamburgerIcon.innerHTML = '☰';
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = 'auto';
            } else {
                mobileMenu.classList.add('mobile-open');
                hamburgerIcon.innerHTML = '✕';
                toggle.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }
        }

        // Wishlist functionality - using dashboard pattern
        function toggleWishlist(button) {
            const produkId = button.getAttribute("data-produk-id");
            const inWishlist = button.getAttribute("data-in-wishlist") === "true";
            const newStatus = !inWishlist;
            
            // Update UI immediately for better UX
            button.setAttribute("data-in-wishlist", newStatus.toString());
            button.classList.toggle('active', newStatus);
            button.disabled = true;

            // Send AJAX request
            fetch("profile.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `toggle_wishlist=true&produk_id=${produkId}`
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes('successfully')) {
                    if (inWishlist) {
                        // Remove the card from the wishlist view
                        const gameCard = button.closest('.game-card');
                        gameCard.style.animation = 'fadeOut 0.3s ease-out';
                        setTimeout(() => {
                            gameCard.remove();
                            // Check if wishlist is now empty
                            const remainingCards = document.querySelectorAll('#wishlist .game-card');
                            if (remainingCards.length === 0) {
                                location.reload(); // Reload to show empty state
                            }
                        }, 300);
                    }
                    showNotification(inWishlist ? 'Removed from wishlist' : 'Added to wishlist', 'success');
                } else {
                    // Revert UI changes on error
                    button.setAttribute("data-in-wishlist", inWishlist.toString());
                    button.classList.toggle('active', inWishlist);
                    showNotification("Failed to update wishlist", "error");
                }
            })
            .catch((error) => {
                console.error("Wishlist error:", error);
                // Revert UI changes on error
                button.setAttribute("data-in-wishlist", inWishlist.toString());
                button.classList.toggle('active', inWishlist);
                showNotification("Network error. Please try again.", "error");
            })
            .finally(() => {
                button.disabled = false;
            });
        }

        // Simple notification system
        function showNotification(message, type = 'success') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(n => n.remove());

            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <svg class="heart-icon" viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px; margin-right: 8px;">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <span>${message}</span>
            `;
            
            document.body.appendChild(notification);
            
            // Trigger animation
            setTimeout(() => notification.classList.add('show'), 100);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // CSS for notifications and animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; transform: translateY(0); }
                to { opacity: 0; transform: translateY(-20px); }
            }
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: var(--radius-lg);
                color: white;
                font-weight: 500;
                z-index: 1000;
                transform: translateX(100%);
                transition: transform var(--transition-normal);
                display: flex;
                align-items: center;
                backdrop-filter: var(--glass-blur);
                box-shadow: var(--glass-shadow);
            }
            .notification.show {
                transform: translateX(0);
            }
            .notification.success {
                background: var(--success);
            }
            .notification.error {
                background: var(--danger);
            }
            .sidebar-header {
                text-align: center;
                padding: var(--space-xl) var(--space-lg);
                border-bottom: 1px solid var(--glass-border);
                margin-bottom: var(--space-lg);
            }
            .avatar-image {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                object-fit: cover;
                border: 3px solid var(--primary);
                margin-bottom: var(--space-md);
            }
            .user-name {
                color: var(--text-primary);
                font-size: 1.2rem;
                font-weight: 600;
                margin-bottom: var(--space-xs);
            }
            .user-id {
                color: var(--text-secondary);
                font-size: 0.9rem;
            }
            .logout-link {
                color: var(--text-secondary);
                text-decoration: none;
                padding: var(--space-xs) var(--space-sm);
                border-radius: var(--radius-sm);
                display: block;
                transition: var(--transition-fast);
                font-size: 0.85rem;
                background: none;
                border: none;
                cursor: pointer;
                width: 100%;
                text-align: left;
            }
            .logout-link:hover {
                background: var(--bg-glass-hover);
                color: var(--danger);
                transform: translateX(4px);
            }
            .game-sections {
                display: none;
            }
            .game-sections.active {
                display: block;
            }
            
            /* Responsive Profile Layout */
            @media (max-width: 768px) {
                .profile-horizontal-layout {
                    flex-direction: column !important;
                    text-align: center;
                }
                .profile-photo-section {
                    align-self: center;
                }
                .profile-info-section {
                    margin-top: var(--space-lg);
                }
                .profile-form-grid {
                    grid-template-columns: 1fr !important;
                }
            }
        `;
        document.head.appendChild(style);

        // Initialize the first section as active
        document.addEventListener('DOMContentLoaded', () => {
            const firstSection = document.getElementById('profile');
            if (firstSection) {
                firstSection.classList.add('active');
            }
        });
    </script>
</body>

</html>