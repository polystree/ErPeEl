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
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate password change if passwords are provided
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Current password is required to change password.']);
            exit();
        }
        
        if (empty($new_password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'New password cannot be empty.']);
            exit();
        }
        
        if (strlen($new_password) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters long.']);
            exit();
        }
        
        if ($new_password !== $confirm_password) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'New password and confirmation do not match.']);
            exit();
        }
        
        // Verify current password
        $check_password = mysqli_query($con, "SELECT password FROM users WHERE id = '$user_id'");
        if ($check_password && mysqli_num_rows($check_password) > 0) {
            $user_data = mysqli_fetch_assoc($check_password);
            if (!password_verify($current_password, $user_data['password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
                exit();
            }
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error verifying current password.']);
            exit();
        }
        
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $tmp_name = $_FILES['image']['tmp_name'];

        $valid_extensions = ['jpg', 'jpeg', 'png', 'avif'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_extension, $valid_extensions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid image extension.']);
            exit();
        } elseif ($file_size > 10000000) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File size too large. Maximum 10 MB.']);
            exit();
        } else {
            $new_image_name = uniqid() . '.' . $file_extension;
            $upload_path = 'image/' . $new_image_name;

            if (move_uploaded_file($tmp_name, $upload_path)) {
                if (isset($hashed_password)) {
                    $queryupdate = "UPDATE users SET username='$new_username', foto='$new_image_name', password='$hashed_password' WHERE id='$user_id'";
                } else {
                    $queryupdate = "UPDATE users SET username='$new_username', foto='$new_image_name' WHERE id='$user_id'";
                }
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
                exit();
            }
        }
    } else {
        if (isset($hashed_password)) {
            $queryupdate = "UPDATE users SET username='$new_username', password='$hashed_password' WHERE id='$user_id'";
        } else {
            $queryupdate = "UPDATE users SET username='$new_username' WHERE id='$user_id'";
        }
    }

    if (isset($queryupdate)) {
        if (mysqli_query($con, $queryupdate)) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
            exit();
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($con)]);
            exit();
        }
    }
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
                                <div class="profile-horizontal-layout" style="display: flex; gap: var(--space-2xl); align-items: flex-start;">
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
                                        <div class="profile-form">
                                            <input type="file" accept="image/*" class="file-input" id="photo-input" style="display: none;">
                                            
                                            <div class="profile-form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-lg); margin-bottom: var(--space-xl);">
                                                <div>
                                                    <label style="display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--space-sm); font-weight: 500;">Username</label>
                                                    <input type="text" id="username-input"
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
                                                    <label style="display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--space-sm); font-weight: 500;">Current Password</label>
                                                    <input type="password" id="current-password-input"
                                                           placeholder="Enter current password" 
                                                           class="search-input" style="width: 100%; margin: 0;">
                                                    <small style="color: var(--text-secondary); font-size: 0.8rem; margin-top: var(--space-xs); display: block;">Leave empty to keep current password</small>
                                                </div>
                                                
                                                <div>
                                                    <label style="display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--space-sm); font-weight: 500;">New Password</label>
                                                    <input type="password" id="new-password-input"
                                                           placeholder="Enter new password" 
                                                           class="search-input" style="width: 100%; margin: 0;">
                                                    <small style="color: var(--text-secondary); font-size: 0.8rem; margin-top: var(--space-xs); display: block;">Minimum 6 characters</small>
                                                </div>
                                                
                                                <div>
                                                    <label style="display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--space-sm); font-weight: 500;">Confirm New Password</label>
                                                    <input type="password" id="confirm-password-input"
                                                           placeholder="Confirm new password" 
                                                           class="search-input" style="width: 100%; margin: 0;">
                                                    <small style="color: var(--text-secondary); font-size: 0.8rem; margin-top: var(--space-xs); display: block;">Must match new password</small>
                                                </div>
                                            </div>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Save Button - Bottom of Card -->
                                <form method="POST" enctype="multipart/form-data" id="profile-form" style="margin-top: var(--space-xl); padding-top: var(--space-lg); border-top: 1px solid var(--glass-border);">
                                    <input type="file" name="image" accept="image/*" style="display: none;" id="photo-input-submit">
                                    <input type="hidden" name="username" id="username-hidden" value="<?php echo htmlspecialchars($username); ?>">
                                    <input type="hidden" name="current_password" id="current-password-hidden">
                                    <input type="hidden" name="new_password" id="new-password-hidden">
                                    <input type="hidden" name="confirm_password" id="confirm-password-hidden">
                                    <input type="hidden" name="simpan" value="1">
                                    <button type="submit" class="add-to-cart-btn" id="save-btn" style="width: 100%; margin: 0; padding: var(--space-md) var(--space-xl); justify-content: center;">
                                        <svg style="width: 16px; height: 16px; margin-right: var(--space-sm);" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"/>
                                        </svg>
                                        Save Changes
                                    </button>
                                </form>
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
                            <?php while ($fetch_orders = mysqli_fetch_assoc($select_orders)): 
                                // Get item count for this order
                                $order_id = $fetch_orders['id'];
                                $count_query = mysqli_query($con, "SELECT COUNT(*) as item_count FROM order_items WHERE order_id = '$order_id'");
                                $item_count = mysqli_fetch_assoc($count_query)['item_count'];
                            ?>
                                <div class="game-card" style="cursor: pointer; transition: all 0.3s ease;" 
                                     onclick="toggleOrderDetails(<?php echo $order_id; ?>)"
                                     onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(139, 92, 246, 0.2)'"
                                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow=''">
                                    <div class="game-info" style="padding: var(--space-xl);">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-md);">
                                            <div>
                                                <h3 class="game-title" style="margin-bottom: var(--space-xs);">
                                                    Order #<?php echo $order_id; ?>
                                                </h3>
                                                <p class="game-developer" style="margin-bottom: var(--space-sm);">
                                                    <?php echo date("M d, Y", strtotime($fetch_orders['created_at'])); ?> at 
                                                    <?php echo date("H:i", strtotime($fetch_orders['created_at'])); ?>
                                                </p>
                                                <div style="display: flex; align-items: center; gap: var(--space-md);">
                                                    <span style="color: var(--text-secondary); font-size: 0.9rem;">
                                                        <?php echo $item_count; ?> 
                                                        <?php echo $item_count == 1 ? 'item' : 'items'; ?>
                                                    </span>
                                                    <span style="background: var(--success); color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500;">Completed</span>
                                                </div>
                                            </div>
                                            <div style="text-align: right;">
                                                <div class="game-price" style="margin-bottom: var(--space-sm);">
                                                    <span class="current-price" style="font-size: 1.2rem;">
                                                        $<?php echo number_format($fetch_orders['total'], 2); ?>
                                                    </span>
                                                </div>
                                                <button class="add-to-cart-btn" style="padding: var(--space-sm) var(--space-md); font-size: 0.85rem;"
                                                        onclick="event.stopPropagation(); toggleOrderDetails(<?php echo $order_id; ?>)">
                                                    <span id="btn-text-<?php echo $order_id; ?>">View Details</span>
                                                    <svg id="btn-icon-<?php echo $order_id; ?>" style="width: 12px; height: 12px; margin-left: var(--space-xs); transition: transform 0.3s ease;" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M7,10L12,15L17,10H7Z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Order Details (Initially Hidden) -->
                                        <div id="order-details-<?php echo $order_id; ?>" 
                                             style="display: none; margin-top: var(--space-lg); padding-top: var(--space-lg); border-top: 1px solid var(--glass-border);">
                                            <div style="margin-bottom: var(--space-md);">
                                                <h4 style="color: var(--primary); margin-bottom: var(--space-sm); font-size: 1rem;">Order Items:</h4>
                                                <div id="order-items-<?php echo $order_id; ?>" style="display: grid; gap: var(--space-sm);">
                                                    <!-- Items will be loaded here -->
                                                    <div style="text-align: center; padding: var(--space-md); color: var(--text-secondary);">
                                                        <div class="loading-spinner" style="display: inline-block; width: 20px; height: 20px; border: 2px solid var(--glass-border); border-top: 2px solid var(--primary); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                                                        <span style="margin-left: var(--space-sm);">Loading items...</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div style="display: flex; gap: var(--space-md); justify-content: flex-end;">
                                                <button class="add-to-cart-btn" style="background: var(--bg-glass); color: var(--text-primary); padding: var(--space-sm) var(--space-md); font-size: 0.85rem;"
                                                        onclick="event.stopPropagation(); window.open('mailto:support@vault.com?subject=Order Support - Order #<?php echo $order_id; ?>', '_blank')">
                                                    📧 Contact Support
                                                </button>
                                                <button class="add-to-cart-btn" style="padding: var(--space-sm) var(--space-md); font-size: 0.85rem;"
                                                        onclick="event.stopPropagation(); window.location.href='semua.php'">
                                                    🎮 Buy More Games
                                                </button>
                                            </div>
                                        </div>
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
    
    <div id="wishlist-notification" class="wishlist-notification" role="alert" aria-live="polite">
        <svg class="heart-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
        </svg>
        <span class="wishlist-message"></span>
    </div>
    
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
        const photoInputSubmit = document.getElementById('photo-input-submit');
        const previewImage = document.getElementById('preview-image');
        const usernameInput = document.getElementById('username-input');
        const usernameHidden = document.getElementById('username-hidden');
        const currentPasswordInput = document.getElementById('current-password-input');
        const currentPasswordHidden = document.getElementById('current-password-hidden');
        const newPasswordInput = document.getElementById('new-password-input');
        const newPasswordHidden = document.getElementById('new-password-hidden');
        const confirmPasswordInput = document.getElementById('confirm-password-input');
        const confirmPasswordHidden = document.getElementById('confirm-password-hidden');

        // Sync form inputs with hidden fields
        if (usernameInput && usernameHidden) {
            usernameInput.addEventListener('input', function() {
                usernameHidden.value = this.value;
            });
        }

        if (currentPasswordInput && currentPasswordHidden) {
            currentPasswordInput.addEventListener('input', function() {
                currentPasswordHidden.value = this.value;
            });
        }

        if (newPasswordInput && newPasswordHidden) {
            newPasswordInput.addEventListener('input', function() {
                newPasswordHidden.value = this.value;
            });
        }

        if (confirmPasswordInput && confirmPasswordHidden) {
            confirmPasswordInput.addEventListener('input', function() {
                confirmPasswordHidden.value = this.value;
            });
        }

        // Password validation
        if (newPasswordInput && confirmPasswordInput) {
            function validatePasswords() {
                const newPass = newPasswordInput.value;
                const confirmPass = confirmPasswordInput.value;
                
                if (newPass.length > 0 && newPass.length < 6) {
                    newPasswordInput.style.borderColor = 'var(--danger)';
                } else {
                    newPasswordInput.style.borderColor = '';
                }
                
                if (confirmPass.length > 0 && newPass !== confirmPass) {
                    confirmPasswordInput.style.borderColor = 'var(--danger)';
                } else {
                    confirmPasswordInput.style.borderColor = '';
                }
            }
            
            newPasswordInput.addEventListener('input', validatePasswords);
            confirmPasswordInput.addEventListener('input', validatePasswords);
        }

        if (photoInput && previewImage) {
            photoInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    // Create a new DataTransfer to copy the file
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    if (photoInputSubmit) {
                        photoInputSubmit.files = dataTransfer.files;
                    }
                    
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

        // Profile form submission
        const profileForm = document.getElementById('profile-form');
        const saveBtn = document.getElementById('save-btn');

        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Disable button and show loading state
                saveBtn.disabled = true;
                const originalContent = saveBtn.innerHTML;
                saveBtn.innerHTML = `
                    <svg style="width: 16px; height: 16px; margin-right: var(--space-sm); animation: spin 1s linear infinite;" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,4V2A10,10 0 0,0 2,12H4A8,8 0 0,1 12,4Z"/>
                    </svg>
                    Saving...
                `;

                // Create FormData from the form
                const formData = new FormData(this);

                // Send AJAX request
                fetch('profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        // Clear password fields after successful update
                        if (currentPasswordInput) currentPasswordInput.value = '';
                        if (newPasswordInput) newPasswordInput.value = '';
                        if (confirmPasswordInput) confirmPasswordInput.value = '';
                        if (currentPasswordHidden) currentPasswordHidden.value = '';
                        if (newPasswordHidden) newPasswordHidden.value = '';
                        if (confirmPasswordHidden) confirmPasswordHidden.value = '';
                        
                        // Update sidebar username if changed
                        const sidebarUsername = document.querySelector('.user-name');
                        if (sidebarUsername && usernameInput) {
                            sidebarUsername.textContent = usernameInput.value;
                        }
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Profile update error:', error);
                    showNotification('Network error. Please try again.', 'error');
                })
                .finally(() => {
                    // Restore button state
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalContent;
                });
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

            // Send AJAX request using dashboard pattern
            fetch("ajax_handler.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `action=toggle_wishlist&produk_id=${produkId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
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
                } else {
                    // Revert UI changes on error
                    button.setAttribute("data-in-wishlist", inWishlist.toString());
                    button.classList.toggle('active', inWishlist);
                    showNotification(data.message || "Failed to update wishlist", "error");
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

        // Order details functionality
        function toggleOrderDetails(orderId) {
            const detailsDiv = document.getElementById(`order-details-${orderId}`);
            const btnText = document.getElementById(`btn-text-${orderId}`);
            const btnIcon = document.getElementById(`btn-icon-${orderId}`);
            const itemsDiv = document.getElementById(`order-items-${orderId}`);
            
            if (detailsDiv.style.display === 'none' || detailsDiv.style.display === '') {
                // Show details
                detailsDiv.style.display = 'block';
                btnText.textContent = 'Hide Details';
                btnIcon.style.transform = 'rotate(180deg)';
                
                // Load order items if not already loaded
                if (itemsDiv.innerHTML.includes('Loading items...')) {
                    loadOrderItems(orderId);
                }
            } else {
                // Hide details
                detailsDiv.style.display = 'none';
                btnText.textContent = 'View Details';
                btnIcon.style.transform = 'rotate(0deg)';
            }
        }
        
        function loadOrderItems(orderId) {
            const itemsDiv = document.getElementById(`order-items-${orderId}`);
            
            // Send AJAX request to load order items
            fetch('ajax_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_order_items&order_id=${orderId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.items) {
                    let itemsHtml = '';
                    data.items.forEach(item => {
                        const hasDiscount = item.harga_diskon && item.harga_diskon < item.harga;
                        itemsHtml += `
                            <div style="display: flex; align-items: center; gap: var(--space-md); padding: var(--space-md); background: var(--bg-glass); border-radius: var(--radius-md); border: 1px solid var(--glass-border);">
                                <img src="image/${item.foto}" alt="${item.nama}" 
                                     style="width: 48px; height: 64px; object-fit: cover; border-radius: var(--radius-sm); flex-shrink: 0;">
                                <div style="flex: 1; min-width: 0;">
                                    <h5 style="color: var(--text-primary); margin: 0 0 var(--space-xs) 0; font-weight: 600;">${item.nama}</h5>
                                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.85rem;">${item.pengembang}</p>
                                </div>
                                <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: var(--space-sm);">
                                    <span style="color: var(--primary); font-weight: 600;">$${parseFloat(item.price).toFixed(2)}</span>
                                    <button onclick="event.stopPropagation(); showSteamKey('${item.steam_key}', '${item.nama.replace(/'/g, "\\'")}');" 
                                            style="background: var(--primary); color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3);"
                                            onmouseover="this.style.background='var(--primary-hover)'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(139, 92, 246, 0.4)'" 
                                            onmouseout="this.style.background='var(--primary)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(139, 92, 246, 0.3)'">
                                        🔑 See Code
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    itemsDiv.innerHTML = itemsHtml;
                } else {
                    itemsDiv.innerHTML = `
                        <div style="text-align: center; padding: var(--space-md); color: var(--text-secondary);">
                            <span>⚠️ Unable to load order items</span>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading order items:', error);
                itemsDiv.innerHTML = `
                    <div style="text-align: center; padding: var(--space-md); color: var(--danger);">
                        <span>❌ Error loading items. Please try again.</span>
                    </div>
                `;
            });
        }

        // Function to show Steam key in a modal
        function showSteamKey(steamKey, gameName) {
            // Create modal backdrop
            const modalBackdrop = document.createElement('div');
            modalBackdrop.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 2000;
                animation: modalFadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            `;
            
            // Create modal content
            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                background: rgba(13, 13, 13, 0.95);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(139, 92, 246, 0.3);
                border-radius: 20px;
                padding: 2rem;
                max-width: 520px;
                width: 90%;
                text-align: center;
                box-shadow: 
                    0 25px 50px rgba(0, 0, 0, 0.5),
                    0 0 0 1px rgba(255, 255, 255, 0.05),
                    inset 0 1px 0 rgba(255, 255, 255, 0.1);
                animation: modalSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
                position: relative;
                overflow: hidden;
            `;
            
            modalContent.innerHTML = `
                <!-- Gradient overlay for glassmorphism effect -->
                <div style="
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 1px;
                    background: linear-gradient(90deg, transparent, rgba(139, 92, 246, 0.5), transparent);
                "></div>
                
                <div style="margin-bottom: 1.5rem; position: relative; z-index: 1;">
                    <div style="
                        width: 80px; 
                        height: 80px; 
                        background: linear-gradient(135deg, #8b5cf6, #a855f7);
                        border-radius: 50%; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center; 
                        margin: 0 auto 1rem auto;
                        box-shadow: 
                            0 8px 32px rgba(139, 92, 246, 0.3),
                            inset 0 1px 0 rgba(255, 255, 255, 0.2);
                        position: relative;
                        overflow: hidden;
                    ">
                        <div style="
                            position: absolute;
                            top: -50%;
                            left: -50%;
                            width: 200%;
                            height: 200%;
                            background: conic-gradient(from 0deg, transparent, rgba(255, 255, 255, 0.1), transparent);
                            animation: rotate 3s linear infinite;
                        "></div>
                        <svg style="width: 40px; height: 40px; color: white; position: relative; z-index: 1;" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7,14A3,3 0 0,1 10,17A3,3 0 0,1 7,20A3,3 0 0,1 4,17A3,3 0 0,1 7,14M12.65,10C11.83,7.67 9.61,6 7,6A6,6 0 0,0 1,12A6,6 0 0,0 7,18C9.61,18 11.83,16.33 12.65,14H17V18A2,2 0 0,0 19,20H20V22H24V18A6,6 0 0,0 18,12A6,6 0 0,0 12.65,10Z"/>
                        </svg>
                    </div>
                    <h3 style="
                        color: #ffffff; 
                        margin: 0 0 0.5rem 0; 
                        font-size: 1.5rem; 
                        font-weight: 700;
                        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
                    ">Steam Activation Key</h3>
                    <p style="
                        color: rgba(255, 255, 255, 0.7); 
                        margin: 0 0 1.5rem 0; 
                        font-size: 1rem;
                        font-weight: 500;
                    ">${gameName}</p>
                </div>
                
                <div style="
                    background: rgba(255, 255, 255, 0.05);
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    border-radius: 16px;
                    padding: 1.5rem;
                    margin-bottom: 1.5rem;
                    position: relative;
                    overflow: hidden;
                ">
                    <div style="
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        height: 1px;
                        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
                    "></div>
                    
                    <label style="
                        display: block;
                        color: rgba(255, 255, 255, 0.8);
                        font-size: 0.85rem;
                        font-weight: 600;
                        margin-bottom: 0.75rem;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    ">Your Activation Code</label>
                    
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <input type="text" value="${steamKey}" readonly 
                               style="
                                   flex: 1;
                                   background: rgba(0, 0, 0, 0.3);
                                   border: 1px solid rgba(255, 255, 255, 0.2);
                                   border-radius: 12px;
                                   padding: 0.875rem 1rem;
                                   color: #ffffff;
                                   font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
                                   font-size: 1rem;
                                   font-weight: 600;
                                   text-align: center;
                                   letter-spacing: 1px;
                                   backdrop-filter: blur(5px);
                                   transition: all 0.3s ease;
                                   outline: none;
                               "
                               id="steam-key-input"
                               onfocus="this.select()"
                               onmouseover="this.style.borderColor='rgba(139, 92, 246, 0.4)'"
                               onmouseout="this.style.borderColor='rgba(255, 255, 255, 0.2)'">
                        <button onclick="copyToClipboard('${steamKey}')" 
                                style="
                                    background: linear-gradient(135deg, #8b5cf6, #a855f7);
                                    color: white;
                                    border: none;
                                    padding: 0.875rem 1.25rem;
                                    border-radius: 12px;
                                    cursor: pointer;
                                    font-size: 0.9rem;
                                    font-weight: 600;
                                    transition: all 0.3s ease;
                                    box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
                                    display: flex;
                                    align-items: center;
                                    gap: 0.5rem;
                                "
                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(139, 92, 246, 0.6)'"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(139, 92, 246, 0.4)'">
                            <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z"/>
                            </svg>
                            Copy
                        </button>
                    </div>
                </div>
                
                <div style="
                    background: rgba(255, 255, 255, 0.03);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    border-radius: 12px;
                    padding: 1.25rem;
                    margin-bottom: 1.5rem;
                    text-align: left;
                ">
                    <h4 style="
                        color: #ffffff;
                        margin: 0 0 0.75rem 0;
                        font-size: 1rem;
                        font-weight: 600;
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    ">
                        <svg style="width: 18px; height: 18px; color: #8b5cf6;" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11,15H13V17H11V15M11,7H13V13H11V7M12,2C6.47,2 2,6.5 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20Z"/>
                        </svg>
                        How to Redeem
                    </h4>
                    <ol style="
                        color: rgba(255, 255, 255, 0.8);
                        margin: 0;
                        padding-left: 1.25rem;
                        line-height: 1.6;
                        font-size: 0.9rem;
                    ">
                        <li style="margin-bottom: 0.5rem;">Open Steam and log into your account</li>
                        <li style="margin-bottom: 0.5rem;">Click <strong>"Games"</strong> → <strong>"Activate a Product on Steam"</strong></li>
                        <li>Enter the code above and follow the instructions</li>
                    </ol>
                </div>
                
                <button onclick="closeModal()" 
                        style="
                            background: rgba(255, 255, 255, 0.1);
                            backdrop-filter: blur(10px);
                            color: #ffffff;
                            border: 1px solid rgba(255, 255, 255, 0.2);
                            padding: 0.875rem 2rem;
                            border-radius: 12px;
                            cursor: pointer;
                            font-size: 1rem;
                            font-weight: 600;
                            transition: all 0.3s ease;
                            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                        "
                        onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.transform='translateY(-1px)'"
                        onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.transform='translateY(0)'">
                    Close
                </button>
            `;
            
            modalBackdrop.appendChild(modalContent);
            document.body.appendChild(modalBackdrop);
            
            // Prevent body scroll when modal is open
            document.body.style.overflow = 'hidden';
            
            // Close modal function
            window.closeModal = function() {
                modalBackdrop.style.animation = 'modalFadeOut 0.3s ease-out';
                modalContent.style.animation = 'modalSlideOut 0.3s ease-out';
                setTimeout(() => {
                    if (document.body.contains(modalBackdrop)) {
                        document.body.removeChild(modalBackdrop);
                    }
                    document.body.style.overflow = 'auto';
                    delete window.closeModal;
                    delete window.copyToClipboard;
                }, 300);
            };
            
            // Copy to clipboard function
            window.copyToClipboard = function(text) {
                navigator.clipboard.writeText(text).then(() => {
                    showNotification('Steam key copied to clipboard! 🎮', 'success');
                    
                    // Visual feedback on copy button
                    const copyBtn = event.target.closest('button');
                    const originalHTML = copyBtn.innerHTML;
                    copyBtn.innerHTML = `
                        <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"/>
                        </svg>
                        Copied!
                    `;
                    copyBtn.style.background = 'linear-gradient(135deg, #22c55e, #16a34a)';
                    
                    setTimeout(() => {
                        copyBtn.innerHTML = originalHTML;
                        copyBtn.style.background = 'linear-gradient(135deg, #8b5cf6, #a855f7)';
                    }, 1500);
                }).catch(() => {
                    // Fallback for older browsers
                    const input = document.getElementById('steam-key-input');
                    input.select();
                    input.setSelectionRange(0, 99999); // For mobile devices
                    document.execCommand('copy');
                    showNotification('Steam key copied to clipboard! 🎮', 'success');
                });
            };
            
            // Close on backdrop click (but not on content click)
            modalBackdrop.addEventListener('click', (e) => {
                if (e.target === modalBackdrop) {
                    closeModal();
                }
            });
            
            // Prevent content clicks from closing modal
            modalContent.addEventListener('click', (e) => {
                e.stopPropagation();
            });
            
            // Close on Escape key
            const escapeHandler = function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                    document.removeEventListener('keydown', escapeHandler);
                }
            };
            document.addEventListener('keydown', escapeHandler);
        }

        // Simple notification system - matching dashboard pattern
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('wishlist-notification');
            const messageEl = notification.querySelector('.wishlist-message');
            
            messageEl.textContent = message;
            notification.className = `wishlist-notification ${type} show`;
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // CSS for notifications and animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; transform: translateY(0); }
                to { opacity: 0; transform: translateY(-20px); }
            }
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            @keyframes rotate {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            @keyframes modalFadeIn {
                from { 
                    opacity: 0; 
                    backdrop-filter: blur(0px);
                    -webkit-backdrop-filter: blur(0px);
                }
                to { 
                    opacity: 1; 
                    backdrop-filter: blur(8px);
                    -webkit-backdrop-filter: blur(8px);
                }
            }
            @keyframes modalFadeOut {
                from { 
                    opacity: 1; 
                    backdrop-filter: blur(8px);
                    -webkit-backdrop-filter: blur(8px);
                }
                to { 
                    opacity: 0; 
                    backdrop-filter: blur(0px);
                    -webkit-backdrop-filter: blur(0px);
                }
            }
            @keyframes modalSlideIn {
                from { 
                    opacity: 0; 
                    transform: translateY(-30px) scale(0.95);
                    backdrop-filter: blur(5px);
                }
                to { 
                    opacity: 1; 
                    transform: translateY(0) scale(1);
                    backdrop-filter: blur(20px);
                }
            }
            @keyframes modalSlideOut {
                from { 
                    opacity: 1; 
                    transform: translateY(0) scale(1);
                }
                to { 
                    opacity: 0; 
                    transform: translateY(-30px) scale(0.95);
                }
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