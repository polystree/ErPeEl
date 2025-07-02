<?php
require "session.php";
require "koneksi.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['loginbtn']) || $_SESSION['loginbtn'] == false) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user info for navbar
$user_query = mysqli_query($con, "SELECT foto FROM `users` WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);
$user_foto = $user_data ? $user_data['foto'] : null;

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

// Sorting options for new releases page
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'newest';

// Order by mapping for new releases page
$order_by = "p.id DESC"; // Default newest first
switch ($sort_by) {
    case 'newest':
        $order_by = "p.id DESC";
        break;
    case 'popular':
        $order_by = "(SELECT COUNT(*) FROM rating r WHERE r.produk_id = p.id) DESC";
        break;
    case 'terlaris':
        $order_by = "(SELECT COUNT(*) FROM rating r WHERE r.produk_id = p.id) DESC";
        break;
    case 'harga_terendah':
        $order_by = "(CASE WHEN p.harga_diskon > 0 THEN p.harga_diskon ELSE p.harga END) ASC";
        break;
    case 'harga_tertinggi':
        $order_by = "(CASE WHEN p.harga_diskon > 0 THEN p.harga_diskon ELSE p.harga END) DESC";
        break;
}

// Query for new releases - get the latest 30 games (adjust as needed)
$sql = "SELECT p.* FROM produk p ORDER BY $order_by LIMIT 20";
$select_produk = mysqli_query($con, $sql) or die('Query failed: ' . mysqli_error($con));

// Get cart and wishlist data for current user
$cart_query = mysqli_query($con, "SELECT produk_id FROM `cart` WHERE user_id = '$user_id'");
$cart_items = [];
while ($cart_row = mysqli_fetch_assoc($cart_query)) {
    $cart_items[] = $cart_row['produk_id'];
}

$wishlist_query = mysqli_query($con, "SELECT produk_id FROM `wishlist` WHERE user_id = '$user_id'");
$wishlist_ids = [];
while ($wishlist_row = mysqli_fetch_assoc($wishlist_query)) {
    $wishlist_ids[] = $wishlist_row['produk_id'];
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Latest new releases and fresh games at Vault - Your digital game store">
    <title>New Releases - Vault Digital Store</title>
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
                <div class="dropdown">
                    <a href="#" class="menu-item dropdown-toggle" role="menuitem" aria-haspopup="true" aria-expanded="false">Categories</a>
                    <div class="dropdown-menu" role="menu">
                        <div class="dropdown-section">
                            <h4>Action</h4>
                            <ul role="none">
                                <li><a href="kategori.php?kategori=1" role="menuitem">FPS Shooters</a></li>
                                <li><a href="kategori.php?kategori=2" role="menuitem">Action RPG</a></li>
                                <li><a href="kategori.php?kategori=3" role="menuitem">Battle Royale</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>Adventure</h4>
                            <ul role="none">
                                <li><a href="kategori.php?kategori=4" role="menuitem">Platformer</a></li>
                                <li><a href="kategori.php?kategori=5" role="menuitem">Strategy</a></li>
                                <li><a href="kategori.php?kategori=6" role="menuitem">City Builder</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>Strategy</h4>
                            <ul role="none">
                                <li><a href="kategori.php?kategori=7" role="menuitem">Turn-Based</a></li>
                                <li><a href="kategori.php?kategori=8" role="menuitem">Life Simulation</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <a href="baru.php" class="menu-item active" role="menuitem">New Releases</a>
                <a href="promo.php" class="menu-item" role="menuitem">Special Offers</a>
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
                <div class="category-section">
                    <h4 class="category-main">✨ New Releases</h4>
                    <ul class="category-list">
                        <li><span style="color: #8b5cf6;">🎮 Latest games</span></li>
                        <li><span style="color: #f59e0b;">🆕 Just released</span></li>
                        <li><span style="color: #10b981;">🚀 Trending now</span></li>
                        <li><a href="promo.php">Special Offers</a></li>
                    </ul>
                </div>
                <div class="category-section">
                    <h4 class="category-main">📅 Release Timeline</h4>
                    <ul class="category-list">
                        <li><span style="color: #ef4444;">This week</span></li>
                        <li><span style="color: #f97316;">This month</span></li>
                        <li><span style="color: #eab308;">Last 30 days</span></li>
                        <li><span style="color: #22c55e;">Recent additions</span></li>
                    </ul>
                </div>
            </aside>
            
            <div class="main-section">
                <div class="search-header">
                    <div class="search-title-row">
                        <h1 class="section-title">
                            ✨ New Releases & Latest Games
                        </h1>
                        <div class="search-filters">
                            <form method="GET" action="baru.php" class="sort-form">
                                <select name="sort_by" class="sort-select" onchange="this.form.submit();">
                                    <option value="newest" <?php echo $sort_by == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="popular" <?php echo $sort_by == 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                                    <option value="terlaris" <?php echo $sort_by == 'terlaris' ? 'selected' : ''; ?>>Best Rated</option>
                                    <option value="harga_terendah" <?php echo $sort_by == 'harga_terendah' ? 'selected' : ''; ?>>Lowest Price</option>
                                    <option value="harga_tertinggi" <?php echo $sort_by == 'harga_tertinggi' ? 'selected' : ''; ?>>Highest Price</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

                <section class="game-sections" aria-labelledby="new-releases-title">
                    <div class="game-container" role="list" aria-label="New release games" id="new-releases-container">
                        <?php
                        if (mysqli_num_rows($select_produk) > 0) {
                            $count = 0;
                            while ($fetch_produk = mysqli_fetch_assoc($select_produk)) {
                                $count++;
                                $rating_data = getAverageRating($con, $fetch_produk['id']);
                                $is_in_wishlist = in_array($fetch_produk['id'], $wishlist_ids);
                                $is_in_cart = in_array($fetch_produk['id'], $cart_items);
                                
                                // Determine if this is a "new" game (first 10 results are considered newest)
                                $is_new = $count <= 10;
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
                                        <?php if ($is_new): ?>
                                            <div class="new-badge">✨ NEW</div>
                                        <?php endif; ?>
                                        <?php if ($fetch_produk['harga_diskon'] != NULL && $fetch_produk['harga_diskon'] > 0 && $fetch_produk['harga_diskon'] < $fetch_produk['harga']): ?>
                                            <div class="discount-badge">-<?php echo round((($fetch_produk['harga'] - $fetch_produk['harga_diskon']) / $fetch_produk['harga']) * 100); ?>%</div>
                                        <?php endif; ?>
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
                                            <?php if ($fetch_produk['harga_diskon'] != NULL && $fetch_produk['harga_diskon'] > 0 && $fetch_produk['harga_diskon'] < $fetch_produk['harga']) { ?>
                                                <span class="original-price">$<?php echo number_format($fetch_produk['harga'], 2); ?></span>
                                                <span class="discounted-price">$<?php echo number_format($fetch_produk['harga_diskon'], 2); ?></span>
                                            <?php } else { ?>
                                                <span class="current-price">$<?php echo number_format($fetch_produk['harga'], 2); ?></span>
                                            <?php } ?>
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
                                    <h3>No new releases available</h3>
                                    <p>Check back soon for the latest games!</p>
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

    <!-- Notification Toast -->
    <div id="notification-toast" class="notification-toast">
        <span id="notification-message"></span>
    </div>

    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Vault Digital Store</h4>
                <p>Your premium destination for digital games</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="dashboard.php">Home</a></li>
                    <li><a href="semua.php">All Games</a></li>
                    <li><a href="baru.php">New Releases</a></li>
                    <li><a href="promo.php">Special Offers</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Account</h4>
                <ul>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="cart.php">My Cart</a></li>
                    <li><a href="order.php">Order History</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Vault Digital Store. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Wishlist functionality
        function toggleWishlist(button) {
            const produkId = button.dataset.produkId;
            const inWishlist = button.getAttribute("data-in-wishlist") === "true";

            fetch("dashboard.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `toggle_wishlist=true&produk_id=${produkId}`
            })
            .then(response => {
                if (response.ok) {
                    button.setAttribute("data-in-wishlist", inWishlist ? "false" : "true");
                    button.classList.toggle("active");
                    
                    const message = inWishlist ? "Removed from wishlist" : "Added to wishlist";
                    showNotification(message, 'success');
                } else {
                    showNotification("Failed to update wishlist", 'error');
                }
            })
            .catch(() => {
                showNotification("Network error occurred", 'error');
            });
        }

        // Cart functionality
        function toggleCart(button) {
            const productId = button.dataset.productId;
            const inCart = button.getAttribute("data-in-cart") === "true";

            if (inCart) {
                showNotification("Item already in cart", 'info');
                return;
            }

            fetch("dashboard.php", {
                method: "POST", 
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `add_to_cart=true&produk_id=${productId}&quantity=1`
            })
            .then(response => {
                if (response.ok) {
                    button.setAttribute("data-in-cart", "true");
                    button.classList.add("added");
                    button.textContent = "In Cart";
                    
                    // Update cart count in navbar
                    const cartBadge = document.querySelector('.cart-badge');
                    if (cartBadge) {
                        const currentCount = parseInt(cartBadge.textContent) || 0;
                        cartBadge.textContent = currentCount + 1;
                    }
                    
                    showNotification("Added to cart successfully!", 'success');
                } else {
                    showNotification("Failed to add to cart", 'error');
                }
            })
            .catch(() => {
                showNotification("Network error occurred", 'error');
            });
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const toast = document.getElementById('notification-toast');
            const messageElement = document.getElementById('notification-message');
            
            messageElement.textContent = message;
            toast.className = `notification-toast ${type}`;
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
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
