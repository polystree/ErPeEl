<?php
session_start();
require "koneksi.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Check if user is logged in
$is_logged_in = isset($_SESSION['loginbtn']) && $_SESSION['loginbtn'] == true;
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

// Handle add to cart (only for logged-in users)
if (isset($_POST['add_to_cart']) && $is_logged_in) {
    $produk_id = $_POST['produk_id'];
    
    // Check if item already exists in cart
    $check_cart = mysqli_query($con, "SELECT * FROM `cart` WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
    if (mysqli_num_rows($check_cart) == 0) {
        // Insert new item
        $insert_cart = mysqli_query($con, "INSERT INTO `cart` (user_id, produk_id, quantity) VALUES ('$user_id', '$produk_id', 1)");
    }
    exit();
}

// Handle remove from cart (only for logged-in users)
if (isset($_POST['remove_from_cart']) && $is_logged_in) {
    $produk_id = $_POST['produk_id'];
    
    // Remove item from cart
    $delete_cart = mysqli_query($con, "DELETE FROM `cart` WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
    exit();
}

// Handle wishlist toggle (only for logged-in users)
if (isset($_POST['toggle_wishlist']) && $is_logged_in) {
    $produk_id = $_POST['produk_id'];
    
    // Check if item exists in wishlist
    $check_wishlist = mysqli_query($con, "SELECT * FROM `wishlist` WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
    if (mysqli_num_rows($check_wishlist) > 0) {
        // Remove from wishlist
        $delete_wishlist = mysqli_query($con, "DELETE FROM `wishlist` WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
    } else {
        // Add to wishlist
        $insert_wishlist = mysqli_query($con, "INSERT INTO `wishlist` (user_id, produk_id) VALUES ('$user_id', '$produk_id')");
    }
    exit();
}

// Get user info for navbar (only if logged in)
$foto = null;
if ($is_logged_in) {
    $user_query = mysqli_query($con, "SELECT foto FROM `users` WHERE id = '$user_id'");
    $user_data = mysqli_fetch_assoc($user_query);
    $foto = $user_data ? $user_data['foto'] : null;
}

// Rating function
function getAverageRating($con, $produk_id) {
    $rating_query = mysqli_query($con, "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM rating WHERE produk_id = '$produk_id'");
    $rating_data = mysqli_fetch_assoc($rating_query);
    return [
        'average' => $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0,
        'total' => $rating_data['total_reviews']
    ];
}

function generateStarRating($avg_rating) {
    $rounded_rating = round($avg_rating);
    $full_stars = $rounded_rating;
    $empty_stars = 5 - $full_stars;
    
    return str_repeat('★', $full_stars) . 
           str_repeat('☆', $empty_stars);
}

// Sorting options for promo page
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'discount_desc';

// Order by mapping for promo page
$order_by = "p.id DESC"; // Default
switch ($sort_by) {
    case 'discount_desc':
        $order_by = "((p.harga - p.harga_diskon) / p.harga) DESC";
        break;
    case 'discount_asc':
        $order_by = "((p.harga - p.harga_diskon) / p.harga) ASC";
        break;
    case 'terlaris':
        $order_by = "(SELECT COUNT(*) FROM rating r WHERE r.produk_id = p.id) DESC";
        break;
    case 'harga_terendah':
        $order_by = "p.harga_diskon ASC";
        break;
    case 'harga_tertinggi':
        $order_by = "p.harga_diskon DESC";
        break;
}

// Query for promo games - only those with valid discounts
$sql = "SELECT p.* FROM produk p WHERE p.harga_diskon IS NOT NULL AND p.harga_diskon > 0 AND p.harga_diskon < p.harga ORDER BY $order_by LIMIT 20";
$select_produk = mysqli_query($con, $sql) or die('Query failed: ' . mysqli_error($con));

// Get cart and wishlist data for current user (only if logged in)
$cart_items = [];
$wishlist_ids = [];

if ($is_logged_in) {
    $cart_query = mysqli_query($con, "SELECT produk_id FROM `cart` WHERE user_id = '$user_id'");
    while ($cart_row = mysqli_fetch_assoc($cart_query)) {
        $cart_items[] = $cart_row['produk_id'];
    }

    $wishlist_query = mysqli_query($con, "SELECT produk_id FROM `wishlist` WHERE user_id = '$user_id'");
    while ($wishlist_row = mysqli_fetch_assoc($wishlist_query)) {
        $wishlist_ids[] = $wishlist_row['produk_id'];
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Special offers and discounted games at Vault - Your digital game store">
    <title>Special Offers - Vault Digital Store</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.png">
    <link rel="stylesheet" href="search.css">
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
                <a href="baru.php" class="menu-item" role="menuitem">New Releases</a>
                <a href="promo.php" class="menu-item active" role="menuitem">Special Offers</a>
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
                    <?php if ($is_logged_in): ?>
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
                    <?php else: ?>
                        <a href="login.php" aria-label="Login to access cart">
                            <img src="image/cart-btn.svg" class="icon-img" alt="" width="20" height="20">
                        </a>
                    <?php endif; ?>
                </div>

                <div class="nav-icon profile">
                    <?php if ($is_logged_in): ?>
                        <a href="profile.php" aria-label="View user profile">
                            <?php if ($foto): ?>
                                <img src="image/<?php echo $foto; ?>" class="icon-img profile-avatar" alt="" width="44" height="44" style="border-radius: 50%; object-fit: cover; filter: none; width: 44px; height: 44px;">
                            <?php else: ?>
                                <img src="image/profile white.svg" class="icon-img" alt="" width="20" height="20">
                            <?php endif; ?>
                        </a>
                    <?php else: ?>
                        <a href="login.php" aria-label="Login to access profile">
                            <img src="image/profile white.svg" class="icon-img" alt="" width="20" height="20">
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="main-content" id="main-content">
        <div class="content-layout">
            <aside class="categories-sidebar">
                <div class="category-section">
                    <h4 class="category-main">Quick Actions</h4>
                    <ul class="category-list">
                        <li><a href="dashboard.php">Back to Dashboard</a></li>
                        <li><a href="semua.php">Browse All Games</a></li>
                        <li><a href="cart.php">My Cart</a></li>
                        <li><a href="profile.php">My Profile</a></li>
                    </ul>
                </div>
            </aside>
            
            <div class="main-section">
                <div class="search-header">
                    <div class="search-title-row">
                        <h1 class="section-title">
                            Special Offers & Discounts
                        </h1>
                        <div class="search-filters">
                            <form method="GET" action="promo.php" class="sort-form">
                                <select name="sort_by" class="sort-select" onchange="this.form.submit();">
                                    <option value="discount_desc" <?php echo $sort_by == 'discount_desc' ? 'selected' : ''; ?>>Highest Discount</option>
                                    <option value="discount_asc" <?php echo $sort_by == 'discount_asc' ? 'selected' : ''; ?>>Lowest Discount</option>
                                    <option value="terlaris" <?php echo $sort_by == 'terlaris' ? 'selected' : ''; ?>>Most Popular</option>
                                    <option value="harga_terendah" <?php echo $sort_by == 'harga_terendah' ? 'selected' : ''; ?>>Lowest Price</option>
                                    <option value="harga_tertinggi" <?php echo $sort_by == 'harga_tertinggi' ? 'selected' : ''; ?>>Highest Price</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

                <section class="game-sections" aria-labelledby="promo-results-title">
                    <div class="game-container" role="list" aria-label="Discounted games" id="promo-results-container">
                        <?php
                        if (mysqli_num_rows($select_produk) > 0) {
                            while ($fetch_produk = mysqli_fetch_assoc($select_produk)) {
                                $rating_data = getAverageRating($con, $fetch_produk['id']);
                                $is_in_wishlist = in_array($fetch_produk['id'], $wishlist_ids);
                                $is_in_cart = in_array($fetch_produk['id'], $cart_items);
                                $discount_percentage = round((($fetch_produk['harga'] - $fetch_produk['harga_diskon']) / $fetch_produk['harga']) * 100);
                                ?>
                                <article class="game-card" role="listitem" 
                                         onclick="window.location.href='detail.php?produk_id=<?php echo $fetch_produk['id']; ?>'"
                                         onkeydown="if(event.key==='Enter'||event.key===' ') window.location.href='detail.php?produk_id=<?php echo $fetch_produk['id']; ?>'"
                                         tabindex="0">
                                    <div class="game-cover-container">
                                        <img class="game-cover" 
                                             src="image/<?php echo $fetch_produk['foto']; ?>"
                                             alt="<?php echo htmlspecialchars($fetch_produk['nama']); ?> cover" 
                                             loading="lazy" />
                                        <div class="discount-badge">-<?php echo $discount_percentage; ?>%</div>
                                        <div class="promo-badge">🔥 PROMO</div>
                                        <button type="button" 
                                                class="wishlist-btn <?php echo $is_in_wishlist ? 'active' : ''; ?>" 
                                                data-produk-id="<?php echo $fetch_produk['id']; ?>"
                                                data-in-wishlist="<?php echo $is_in_wishlist ? 'true' : 'false'; ?>"
                                                onclick="event.stopPropagation(); toggleWishlist(this);"
                                                aria-label="<?php echo $is_in_wishlist ? 'Remove from wishlist' : 'Add to wishlist'; ?>">
                                            <svg class="heart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="game-info">
                                        <div class="game-price">
                                            <span class="original-price">$<?php echo number_format($fetch_produk['harga'], 2); ?></span>
                                            <span class="discounted-price">$<?php echo number_format($fetch_produk['harga_diskon'], 2); ?></span>
                                        </div>
                                        <h3 class="game-title"><?php echo htmlspecialchars($fetch_produk['nama']); ?></h3>
                                        <p class="game-developer"><?php echo htmlspecialchars($fetch_produk['pengembang']); ?></p>
                                        <div class="game-rating">
                                            <span class="stars">
                                                <?php echo generateStarRating($rating_data['average']); ?>
                                            </span>
                                            <span class="rating-text">
                                                <?php echo $rating_data['total'] > 0 
                                                      ? $rating_data['average'] . '/5 (' . $rating_data['total'] . ')' 
                                                      : 'No ratings'; ?>
                                            </span>
                                        </div>
                                        <button class="add-to-cart-btn <?php echo $is_in_cart ? 'added' : ''; ?>" 
                                                data-product-id="<?php echo $fetch_produk['id']; ?>"
                                                data-in-cart="<?php echo $is_in_cart ? 'true' : 'false'; ?>"
                                                onclick="event.stopPropagation(); toggleCart(this);">
                                            <?php echo $is_in_cart ? 'In Cart' : 'Add to Cart'; ?>
                                        </button>
                                    </div>
                                </article>
                                <?php
                            }
                        } else {
                            ?>
                            <div class="empty-message">
                                <div class="empty-content">
                                    <h3>No special offers available</h3>
                                    <p>Check back later for amazing discounts!</p>
                                    <a href="semua.php" class="empty-action-btn">Browse All Games</a>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <!-- Wishlist Notification -->
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
        // Check if user is logged in (passed from PHP)
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

        // Redirect to login function
        function redirectToLogin() {
            window.location.href = 'login.php';
        }

        // Wishlist functionality
        function toggleWishlist(button) {
            if (!isLoggedIn) {
                showNotification('Please log in to add items to your wishlist', 'warning');
                return;
            }

            const produkId = button.dataset.produkId;
            const inWishlist = button.getAttribute("data-in-wishlist") === "true";

            fetch("promo.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `toggle_wishlist=true&produk_id=${produkId}`
            })
            .then(response => {
                if (response.ok) {
                    button.setAttribute("data-in-wishlist", inWishlist ? "false" : "true");
                    button.classList.toggle("active");
                    button.setAttribute("aria-label", inWishlist ? "Add to wishlist" : "Remove from wishlist");
                    
                    const message = inWishlist ? "Removed from wishlist" : "Added to wishlist";
                    showWishlistNotification(message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showWishlistNotification("Failed to update wishlist. Please try again.");
            });
        }

        // Cart functionality
        function toggleCart(button) {
            if (!isLoggedIn) {
                showNotification('Please log in to add items to your cart', 'warning');
                return;
            }

            const productId = button.dataset.productId;
            const inCart = button.getAttribute("data-in-cart") === "true";
            const action = inCart ? 'remove_from_cart' : 'add_to_cart';

            fetch("promo.php", {
                method: "POST", 
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `${action}=true&produk_id=${productId}`
            })
            .then(response => {
                if (response.ok) {
                    button.setAttribute("data-in-cart", inCart ? "false" : "true");
                    button.classList.toggle("added");
                    button.textContent = inCart ? "Add to Cart" : "In Cart";
                    
                    // Update cart count in navbar
                    updateCartCount();
                    
                    const message = inCart ? "Removed from cart" : "Added to cart successfully!";
                    showWishlistNotification(message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showWishlistNotification("Failed to update cart. Please try again.");
            });
        }

        // Update cart count function
        function updateCartCount() {
            fetch('ajax_handler.php?action=get_cart_count')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cartBadge = document.querySelector('.cart-badge');
                        if (data.count > 0) {
                            if (cartBadge) {
                                cartBadge.textContent = data.count > 99 ? '99+' : data.count;
                            } else {
                                // Create badge if it doesn't exist
                                const cartLink = document.querySelector('.nav-icon a[href="cart.php"]');
                                if (cartLink) {
                                    const badge = document.createElement('span');
                                    badge.className = 'cart-badge';
                                    badge.textContent = data.count > 99 ? '99+' : data.count;
                                    cartLink.appendChild(badge);
                                }
                            }
                        } else if (cartBadge) {
                            cartBadge.remove();
                        }
                    }
                });
        }

        // Show wishlist notification
        function showWishlistNotification(message) {
            showNotification(message, 'success');
        }

        // Simple notification system (matches dashboard)
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('wishlist-notification');
            const messageEl = notification.querySelector('.wishlist-message');
            
            messageEl.textContent = message;
            notification.className = `wishlist-notification ${type} show`;
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            menu.classList.toggle('active');
            const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', !isExpanded);
        }

        // Dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownToggle = document.querySelector('.dropdown-toggle');
            const dropdownMenu = document.querySelector('.dropdown-menu');
            
            if (dropdownToggle && dropdownMenu) {
                dropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    dropdownMenu.classList.toggle('show');
                });
                
                document.addEventListener('click', function(e) {
                    if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                    }
                });
            }
        });
    </script>

</body>

</html>
