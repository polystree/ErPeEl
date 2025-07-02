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

if (isset($_POST['hapus'])) {
    $produk_id = $_POST['produk_id'];
    $delete_query = mysqli_query($con, "DELETE FROM `cart` WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $produk_id = $_POST['produk_id'];
    
    // Check if item already exists in cart
    $check_cart = mysqli_query($con, "SELECT * FROM `cart` WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
    if (mysqli_num_rows($check_cart) == 0) {
        // Insert new item
        $insert_cart = mysqli_query($con, "INSERT INTO `cart` (user_id, produk_id, quantity) VALUES ('$user_id', '$produk_id', 1)");
    }
    exit();
}

// Handle wishlist toggle
if (isset($_POST['add_to_wishlist']) || isset($_POST['remove_from_wishlist'])) {
    $produk_id = $_POST['produk_id'];
    
    if (isset($_POST['add_to_wishlist'])) {
        // Add to wishlist
        $check_wishlist = mysqli_query($con, "SELECT * FROM `wishlist` WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
        if (mysqli_num_rows($check_wishlist) == 0) {
            $insert_wishlist = mysqli_query($con, "INSERT INTO `wishlist` (user_id, produk_id) VALUES ('$user_id', '$produk_id')");
        }
    } else {
        // Remove from wishlist
        $delete_wishlist = mysqli_query($con, "DELETE FROM `wishlist` WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Vault - Your shopping cart">
    <title>Shopping Cart - Vault Digital Store</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.png">
    <link rel="stylesheet" href="cart.css">
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
                        <li><a href="dashboard.php">Continue Shopping</a></li>
                        <li><a href="semua.php">Browse All Games</a></li>
                        <li><a href="profile.php">My Profile</a></li>
                    </ul>
                </div>
                <div class="category-section">
                    <h4 class="category-main">Action & Adventure</h4>
                    <ul class="category-list">
                        <li><a href="kategori.php?kategori=1">FPS Shooters</a></li>
                        <li><a href="kategori.php?kategori=2">Action RPG</a></li>
                        <li><a href="kategori.php?kategori=3">Battle Royale</a></li>
                        <li><a href="kategori.php?kategori=4">Platformers</a></li>
                    </ul>
                </div>
                <div class="category-section">
                    <h4 class="category-main">Strategy & Simulation</h4>
                    <ul class="category-list">
                        <li><a href="kategori.php?kategori=5">RTS Games</a></li>
                        <li><a href="kategori.php?kategori=6">City Builders</a></li>
                        <li><a href="kategori.php?kategori=7">Turn-Based</a></li>
                        <li><a href="kategori.php?kategori=8">Life Simulation</a></li>
                    </ul>
                </div>
                <div class="category-section">
                    <h4 class="category-main">RPG & MMO</h4>
                    <ul class="category-list">
                        <li><a href="kategori.php?kategori=9">MMORPG</a></li>
                        <li><a href="kategori.php?kategori=10">JRPG</a></li>
                        <li><a href="kategori.php?kategori=11">Indie RPG</a></li>
                        <li><a href="kategori.php?kategori=12">Tactical RPG</a></li>
                    </ul>
                </div>
                <div class="category-section">
                    <h4 class="category-main">Sports & Racing</h4>
                    <ul class="category-list">
                        <li><a href="kategori.php?kategori=13">Racing Sims</a></li>
                        <li><a href="kategori.php?kategori=14">Sports Games</a></li>
                        <li><a href="kategori.php?kategori=15">Arcade Racing</a></li>
                        <li><a href="kategori.php?kategori=16">Fighting Games</a></li>
                    </ul>
                </div>
                <div class="category-section">
                    <h4 class="category-main">Indie & Casual</h4>
                    <ul class="category-list">
                        <li><a href="kategori.php?kategori=17">Indie Games</a></li>
                        <li><a href="kategori.php?kategori=18">Puzzle Games</a></li>
                        <li><a href="kategori.php?kategori=19">Casual Games</a></li>
                        <li><a href="kategori.php?kategori=20">Horror Games</a></li>
                    </ul>
                </div>
            </aside>
            
            <div class="main-section">
                <div class="cart-header">
                    <h1 class="section-title">Shopping Cart</h1>
                    <p class="cart-subtitle">Review your selected games before checkout</p>
                </div>

                <?php
                $select_cart = mysqli_query($con, "SELECT c.*, p.nama, p.pengembang, p.foto, p.harga, p.harga_diskon FROM `cart` c JOIN `produk` p ON c.produk_id = p.id WHERE c.user_id = '$user_id'") or die('Query failed');

                if (mysqli_num_rows($select_cart) > 0) {
                    $total_price = 0;
                    ?>
                    <div class="game-sections">
                        <div class="game-container cart-container" id="cart-items-container">
                            <?php while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                                $price = ($fetch_cart['harga_diskon'] !== null && $fetch_cart['harga_diskon'] > 0) 
                                        ? $fetch_cart['harga_diskon'] 
                                        : $fetch_cart['harga'];
                                $total_price += $price;
                                ?>
                                <article class="game-card cart-item-card" onclick="window.location.href='detail.php?produk_id=<?php echo $fetch_cart['produk_id']; ?>'" style="cursor: pointer;">
                                    <div class="cart-item-layout">
                                        <!-- Game cover - uses full height -->
                                        <div class="cart-item-image">
                                            <img class="game-cover" 
                                                 src="image/<?php echo $fetch_cart['foto']; ?>" 
                                                 alt="<?php echo htmlspecialchars($fetch_cart['nama']); ?> cover" />
                                        </div>
                                        
                                        <!-- Content area -->
                                        <div class="cart-item-content">
                                            <!-- Top section: Remove button (far right) -->
                                            <div class="cart-item-top">
                                                <form method="post" action="" class="remove-form">
                                                    <input type="hidden" name="produk_id" value="<?php echo $fetch_cart['produk_id']; ?>">
                                                    <button class="remove-btn" name="hapus" aria-label="Remove <?php echo htmlspecialchars($fetch_cart['nama']); ?> from cart" onclick="event.stopPropagation();">
                                                        ×
                                                    </button>
                                                </form>
                                            </div>
                                            
                                            <!-- Top right: Digital Games tag and title -->
                                            <div class="cart-item-header">
                                                <div class="game-type">Digital Game</div>
                                                <h3 class="game-title"><?php echo htmlspecialchars($fetch_cart['nama']); ?></h3>
                                            </div>
                                            
                                            <!-- Bottom section: Developer name and Price horizontally -->
                                            <div class="cart-item-bottom">
                                                <p class="game-developer">by <?php echo htmlspecialchars($fetch_cart['pengembang']); ?></p>
                                                <div class="game-price">
                                                    <?php if ($fetch_cart['harga_diskon'] !== null && $fetch_cart['harga_diskon'] > 0 && $fetch_cart['harga_diskon'] < $fetch_cart['harga']) { ?>
                                                        <div class="discount-badge">-<?php echo round((($fetch_cart['harga'] - $fetch_cart['harga_diskon']) / $fetch_cart['harga']) * 100); ?>%</div>
                                                        <span class="original-price">$<?php echo number_format($fetch_cart['harga'], 2); ?></span>
                                                        <span class="discounted-price">$<?php echo number_format($fetch_cart['harga_diskon'], 2); ?></span>
                                                    <?php } else { ?>
                                                        <span class="current-price">$<?php echo number_format($fetch_cart['harga'], 2); ?></span>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            <?php } ?>
                        </div>
                        
                        <div class="cart-summary-section">
                            <div class="game-card cart-summary-card" id="order-summary">
                                <div class="summary-header">
                                    <h3 class="game-title">Order Summary</h3>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-row">
                                        <span>Subtotal</span>
                                        <span>$<?php echo number_format($total_price, 2); ?></span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Processing Fee</span>
                                        <span>$2.99</span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Tax (11%)</span>
                                        <span>$<?php echo number_format(($total_price + 2.99) * 0.11, 2); ?></span>
                                    </div>
                                    <div class="summary-row total-row">
                                        <span>Total</span>
                                        <span>$<?php echo number_format(($total_price + 2.99) * 1.11, 2); ?></span>
                                    </div>
                                    
                                    <div class="checkout-actions">
                                        <button class="add-to-cart-btn special" onclick="window.location.href='payment.php';">
                                            Proceed to Checkout
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="game-sections">
                        <div class="empty-message">
                            <svg class="empty-cart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                            <h2>Your cart is empty</h2>
                            <p>Discover amazing games and add them to your cart</p>
                            <button class="add-to-cart-btn" onclick="window.location.href='dashboard.php';">
                                Browse Games
                            </button>
                        </div>
                    </div>
                    <?php
                }
                ?>
                
                <!-- Recommendations Section -->
                <section class="game-sections recommendations-section" aria-labelledby="cart-recommendations-title">
                    <div class="section-header">
                        <h2 class="section-title" id="cart-recommendations-title">You Might Also Like</h2>
                        <button class="view-all-btn" onclick="window.location.href='semua.php';" aria-label="View all recommended games">
                            View All
                        </button>
                    </div>

                    <div class="game-container" role="list" aria-label="Recommended games list">
                        <?php
                        // Get user's cart items to exclude from recommendations
                        $cart_query = mysqli_query($con, "SELECT produk_id FROM `cart` WHERE user_id = '$user_id'");
                        $cart_items = [];
                        while ($cart_row = mysqli_fetch_assoc($cart_query)) {
                            $cart_items[] = $cart_row['produk_id'];
                        }
                        
                        // Create exclusion clause
                        $exclude_clause = "";
                        if (!empty($cart_items)) {
                            $exclude_ids = implode(',', array_map('intval', $cart_items));
                            $exclude_clause = " AND id NOT IN ($exclude_ids)";
                        }
                        
                        // Get wishlist items for display
                        $wishlist_query = mysqli_query($con, "SELECT produk_id FROM `wishlist` WHERE user_id = '$user_id'");
                        $wishlist_ids = [];
                        while ($wishlist_row = mysqli_fetch_assoc($wishlist_query)) {
                            $wishlist_ids[] = $wishlist_row['produk_id'];
                        }
                        
                        $select_produk = mysqli_query($con, "SELECT * FROM `produk` WHERE 1=1 $exclude_clause ORDER BY RAND() LIMIT 8") or die('Query failed');

                        if (mysqli_num_rows($select_produk) > 0) {
                            while ($fetch_produk = mysqli_fetch_assoc($select_produk)) {
                                $is_in_wishlist = in_array($fetch_produk['id'], $wishlist_ids);
                                $is_in_cart = in_array($fetch_produk['id'], $cart_items);
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
                                                <div class="discount-badge">-<?php echo round((($fetch_produk['harga'] - $fetch_produk['harga_diskon']) / $fetch_produk['harga']) * 100); ?>%</div>
                                                <span class="original-price">$<?php echo number_format($fetch_produk['harga'], 2); ?></span>
                                                <span class="discounted-price">$<?php echo number_format($fetch_produk['harga_diskon'], 2); ?></span>
                                            <?php } else { ?>
                                                <span class="current-price">$<?php echo number_format($fetch_produk['harga'], 2); ?></span>
                                            <?php } ?>
                                        </div>
                                        <h3 class="game-title"><?php echo htmlspecialchars($fetch_produk['nama']); ?></h3>
                                        <p class="game-developer"><?php echo htmlspecialchars($fetch_produk['pengembang']); ?></p>
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
                            echo '<p class="empty-message">No recommended games available yet.</p>';
                        }
                        ?>
                    </div>
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
        // Mobile Menu Toggle
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

        // Function to refresh cart content dynamically
        function refreshCartContent() {
            fetch('ajax_get_cart.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Find the main cart section (first .game-sections after .cart-header)
                    const cartHeader = document.querySelector('.cart-header');
                    const gameSection = cartHeader.nextElementSibling;
                    
                    if (data.cartItems && data.cartItems.length > 0) {
                        // Cart has items - create/update the full cart structure
                        gameSection.innerHTML = `
                            <div class="game-container cart-container" id="cart-items-container">
                                ${data.cartItemsHtml}
                            </div>
                            
                            <div class="cart-summary-section">
                                <div class="game-card cart-summary-card" id="order-summary">
                                    ${data.orderSummaryHtml}
                                </div>
                            </div>
                        `;
                        
                        // Ensure the section is displayed properly
                        gameSection.style.display = 'grid';
                        gameSection.className = 'game-sections';
                    } else {
                        // Cart is empty - show empty message
                        gameSection.innerHTML = `
                            <div class="empty-message">
                                <svg class="empty-cart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                                <h2>Your cart is empty</h2>
                                <p>Discover amazing games and add them to your cart</p>
                                <button class="add-to-cart-btn" onclick="window.location.href='dashboard.php';">
                                    Browse Games
                                </button>
                            </div>
                        `;
                        gameSection.className = 'game-sections';
                    }
                }
            })
            .catch(error => {
                console.error('Error refreshing cart:', error);
            });
        }

        // Toggle Cart function for recommendations
        function toggleCart(button) {
            const productId = button.dataset.productId;
            const isInCart = button.dataset.inCart === 'true';
            const originalText = button.textContent;
            
            // Update UI immediately for better UX
            button.disabled = true;
            button.textContent = isInCart ? 'Removing...' : 'Adding...';
            
            // Add to cart via AJAX
            fetch('ajax_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add_to_cart&produk_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'added') {
                        button.textContent = 'In Cart';
                        button.classList.add('added');
                        button.dataset.inCart = 'true';
                    } else if (data.action === 'removed') {
                        button.textContent = 'Add to Cart';
                        button.classList.remove('added');
                        button.dataset.inCart = 'false';
                    }
                    
                    // Update cart badge by fetching current count
                    updateCartBadge();
                    
                    // Refresh cart content dynamically
                    refreshCartContent();
                    
                    showNotification(data.message || 'Cart updated!', 'success');
                } else {
                    // Revert UI changes on error
                    button.textContent = originalText;
                    showNotification(data.message || 'Failed to update cart', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert UI changes on error
                button.textContent = originalText;
                showNotification('Network error. Please try again.', 'error');
            })
            .finally(() => {
                button.disabled = false;
            });
        }

        // Update cart badge with current count
        function updateCartBadge() {
            fetch('ajax_handler.php?action=get_cart_count')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cartIcon = document.querySelector('.nav-icon a[href="cart.php"]');
                    if (cartIcon) {
                        // Remove existing badge
                        const existingBadge = cartIcon.querySelector('.cart-badge');
                        if (existingBadge) existingBadge.remove();
                        
                        // Add new badge if count > 0
                        if (data.count > 0) {
                            const badge = document.createElement('span');
                            badge.className = 'cart-badge';
                            badge.textContent = data.count > 99 ? '99+' : data.count;
                            cartIcon.style.position = 'relative';
                            cartIcon.appendChild(badge);
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error updating cart badge:', error);
            });
        }

        // Toggle Wishlist function for recommendations
        function toggleWishlist(button) {
            const productId = button.dataset.produkId;
            const isInWishlist = button.dataset.inWishlist === 'true';
            const newStatus = !isInWishlist;
            
            // Update UI immediately for better UX
            button.setAttribute("data-in-wishlist", newStatus.toString());
            button.classList.toggle('active', newStatus);
            button.disabled = true;
            
            fetch('ajax_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=toggle_wishlist&produk_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                } else {
                    // Revert UI changes on error
                    button.setAttribute("data-in-wishlist", isInWishlist.toString());
                    button.classList.toggle('active', isInWishlist);
                    showNotification(data.message || 'Failed to update wishlist', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert UI changes on error
                button.setAttribute("data-in-wishlist", isInWishlist.toString());
                button.classList.toggle('active', isInWishlist);
                showNotification('Network error. Please try again.', 'error');
            })
            .finally(() => {
                button.disabled = false;
            });
        }

        // Simple notification system - same as dashboard
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('wishlist-notification');
            const messageEl = notification.querySelector('.wishlist-message');
            
            messageEl.textContent = message;
            notification.className = `wishlist-notification ${type} show`;
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Scroll to top button
        function addScrollToTop() {
            const scrollBtn = document.createElement('button');
            scrollBtn.innerHTML = '↑';
            scrollBtn.className = 'scroll-to-top';
            scrollBtn.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });
            document.body.appendChild(scrollBtn);

            window.addEventListener('scroll', () => {
                scrollBtn.classList.toggle('visible', window.scrollY > 300);
            });
        }

        // Initialize everything
        document.addEventListener('DOMContentLoaded', () => {
            // Add scroll to top
            addScrollToTop();
            
            // Add event listener for dynamic remove buttons
            document.addEventListener('click', function(e) {
                if (e.target.matches('.remove-btn')) {
                    e.preventDefault();
                    const form = e.target.closest('.remove-form');
                    const produkId = form.querySelector('input[name="produk_id"]').value;
                    
                    // Remove item via AJAX
                    fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `hapus=1&produk_id=${produkId}`
                    })
                    .then(() => {
                        // Refresh cart content
                        refreshCartContent();
                        updateCartBadge();
                        showNotification('Game removed from cart!', 'success');
                    })
                    .catch(error => {
                        console.error('Error removing item:', error);
                        showNotification('Failed to remove item', 'error');
                    });
                }
            });
            
            // Close mobile menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.navbar') && document.getElementById('mobile-menu').classList.contains('mobile-open')) {
                    toggleMobileMenu();
                }
            });
            
            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (document.getElementById('mobile-menu').classList.contains('mobile-open')) {
                        toggleMobileMenu();
                    }
                }
            });
        });
    </script>

</body>

</html>