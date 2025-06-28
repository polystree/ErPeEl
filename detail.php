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

if (isset($_GET['produk_id'])) {
    $produk_id = intval($_GET['produk_id']); 

    // Get product with category name
    $select_produk = mysqli_query($con, "
        SELECT p.*, k.nama as kategori_nama 
        FROM `produk` p 
        LEFT JOIN `kategori` k ON p.kategori_id = k.id 
        WHERE p.`id` = $produk_id
    ") or die('Query failed');

    if (mysqli_num_rows($select_produk) > 0) {
        $fetch_produk = mysqli_fetch_assoc($select_produk);
    } else {
        header("Location: dashboard.php");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}

// Function to get average rating for a product
function getAverageRating($con, $produk_id) {
    $rating_query = mysqli_query($con, "SELECT AVG(rating) as avg_rating, COUNT(*) as rating_count FROM rating WHERE produk_id = '$produk_id'");
    $rating_data = mysqli_fetch_assoc($rating_query);
    
    if ($rating_data['rating_count'] > 0) {
        return [
            'avg' => round($rating_data['avg_rating'], 1),
            'count' => $rating_data['rating_count']
        ];
    } else {
        return [
            'avg' => 0,
            'count' => 0
        ];
    }
}

// Function to generate star rating HTML
function generateStarRating($avg_rating) {
    $rounded_rating = round($avg_rating);
    $full_stars = $rounded_rating;
    $empty_stars = 5 - $full_stars;
    
    return str_repeat('★', $full_stars) . 
           str_repeat('☆', $empty_stars);
}

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

// Check if product is in user's cart/wishlist
$is_in_wishlist = in_array($fetch_produk['id'], $wishlist_ids);
$is_in_cart = in_array($fetch_produk['id'], $cart_items);

// Get average rating for this product
$rating_data = getAverageRating($con, $fetch_produk['id']);

// Handle old form submission (for backward compatibility)
if (isset($_POST['add'])) {
    if (isset($_POST['produk_id']) && !empty($_POST['produk_id'])) {
        $produk_id = $_POST['produk_id'];
        
        $stmt = $con->prepare("SELECT * FROM `cart` WHERE produk_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $produk_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $stmt = $con->prepare("INSERT INTO `cart` (user_id, produk_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $produk_id);

            if ($stmt->execute()) {
                header("location:cart.php");
                exit();
            }
        } else {
            header("location:cart.php");
            exit();
        }
    }
}

// Get reviews for this product
$query_reviews = "
    SELECT r.*, u.username, u.foto
    FROM rating r
    JOIN users u ON r.user_id = u.id
    WHERE r.produk_id = ?
    ORDER BY r.created_at DESC
";

$stmt = $con->prepare($query_reviews);
$stmt->bind_param("i", $produk_id);
$stmt->execute();
$result_reviews = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($fetch_produk['nama']); ?> - Game details at Vault">
    <title><?php echo htmlspecialchars($fetch_produk['nama']); ?> - Vault Digital Store</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.png">
    <link rel="stylesheet" href="detail.css">
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
                        <img src="image/profile white.svg" class="icon-img" alt="" width="20" height="20">
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
                        <li><a href="dashboard.php">Back to Home</a></li>
                        <li><a href="semua.php">Browse All Games</a></li>
                        <li><a href="cart.php">My Cart</a></li>
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
                <div class="detail-header">
                    <h1 class="section-title"><?php echo htmlspecialchars($fetch_produk['nama']); ?></h1>
                    <p class="detail-subtitle">Game Details & Information</p>
                </div>

                <div class="game-sections">
                    <div class="detail-container">
                        <div class="detail-layout">
                            <!-- Left Side: Game Cover + Price + Buttons -->
                            <div class="detail-left-section">
                                <div class="game-card detail-image-card">
                                    <div class="game-cover-container detail-cover">
                                        <img class="detail-cover-img" 
                                             src="image/<?php echo $fetch_produk['foto']; ?>" 
                                             alt="<?php echo htmlspecialchars($fetch_produk['nama']); ?> cover" />
                                    </div>
                                    
                                    <!-- Price Section Below Cover -->
                                    <div class="detail-price-section">
                                        <div class="game-price detail-price">
                                            <?php if ($fetch_produk['harga_diskon'] != NULL && $fetch_produk['harga_diskon'] > 0 && $fetch_produk['harga_diskon'] < $fetch_produk['harga']) { ?>
                                                <div class="detail-price-main">
                                                    <span class="original-price">$<?php echo number_format($fetch_produk['harga'], 2); ?></span>
                                                    <span class="discounted-price">$<?php echo number_format($fetch_produk['harga_diskon'], 2); ?></span>
                                                </div>
                                                <div class="discount-badge">-<?php echo round((($fetch_produk['harga'] - $fetch_produk['harga_diskon']) / $fetch_produk['harga']) * 100); ?>%</div>
                                            <?php } else { ?>
                                                <div class="detail-price-main">
                                                    <span class="current-price">$<?php echo number_format($fetch_produk['harga'], 2); ?></span>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons Below Price -->
                                    <div class="detail-actions">
                                        <button class="add-to-cart-btn detail-cart-btn <?php echo $is_in_cart ? 'added' : ''; ?>" 
                                                data-product-id="<?php echo $fetch_produk['id']; ?>"
                                                data-in-cart="<?php echo $is_in_cart ? 'true' : 'false'; ?>"
                                                onclick="toggleCart(this);">
                                            <?php echo $is_in_cart ? 'In Cart' : 'Add to Cart'; ?>
                                        </button>
                                        
                                        <button type="button" 
                                                class="detail-page-wishlist-btn <?php echo $is_in_wishlist ? 'active' : ''; ?>" 
                                                data-produk-id="<?php echo $fetch_produk['id']; ?>"
                                                data-in-wishlist="<?php echo $is_in_wishlist ? 'true' : 'false'; ?>"
                                                onclick="toggleWishlist(this);"
                                                aria-label="<?php echo $is_in_wishlist ? 'Remove from wishlist' : 'Add to wishlist'; ?>">
                                            <svg class="heart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side: Game Info + Description + Rating + Recent Reviews -->
                            <div class="detail-right-section">
                                <div class="game-card detail-purchase-card">
                                    <!-- Game Info -->
                                    <div class="detail-game-info">
                                        <h2 class="detail-title"><?php echo htmlspecialchars($fetch_produk['nama']); ?></h2>
                                        <p class="detail-developer">by <?php echo htmlspecialchars($fetch_produk['pengembang']); ?></p>
                                        <?php if (!empty($fetch_produk['kategori_nama'])) { ?>
                                        <p class="detail-category"><?php echo htmlspecialchars($fetch_produk['kategori_nama']); ?></p>
                                        <?php } ?>
                                    </div>
                                    
                                    <!-- Game Description -->
                                    <div class="detail-description">
                                        <h3>About This Game</h3>
                                        <div class="description-content" id="description-content">
                                            <?php 
                                            $description = !empty($fetch_produk['detail']) ? $fetch_produk['detail'] : 'No description available for this game.';
                                            echo nl2br(htmlspecialchars($description)); 
                                            ?>
                                        </div>
                                        <button type="button" class="read-more-btn" id="read-more-btn" onclick="toggleDescription()" style="display: none;">
                                            Read More
                                        </button>
                                    </div>
                                    
                                    <!-- Rating Section -->
                                    <div class="detail-rating">
                                        <h4>User Rating</h4>
                                        <div class="game-rating">
                                            <span class="stars">
                                                <?php echo generateStarRating($rating_data['avg']); ?>
                                            </span>
                                            <span class="rating-text">
                                                <?php echo $rating_data['count'] > 0 
                                                      ? $rating_data['avg'] . '/5 (' . $rating_data['count'] . ' reviews)' 
                                                      : 'No ratings yet'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Most Recent Review Preview -->
                                    <?php
                                    // Get 1 most recent review for this product
                                    $recent_review_query = "SELECT r.*, u.username, u.foto 
                                                          FROM rating r 
                                                          JOIN users u ON r.user_id = u.id 
                                                          WHERE r.produk_id = ? 
                                                          ORDER BY r.created_at DESC 
                                                          LIMIT 1";
                                    $stmt_recent = $con->prepare($recent_review_query);
                                    $stmt_recent->bind_param("i", $produk_id);
                                    $stmt_recent->execute();
                                    $result_recent_review = $stmt_recent->get_result();
                                    
                                    if (mysqli_num_rows($result_recent_review) > 0) {
                                        $recent_review = mysqli_fetch_assoc($result_recent_review);
                                        ?>
                                        <div class="detail-recent-review">
                                            <h4 class="recent-review-title">Recent Review</h4>
                                            <div class="recent-review-preview">
                                                <div class="recent-review-header">
                                                    <span class="recent-reviewer-name"><?php echo htmlspecialchars($recent_review['username']); ?></span>
                                                    <div class="recent-review-rating">
                                                        <span class="stars">
                                                            <?php echo generateStarRating($recent_review['rating']); ?>
                                                        </span>
                                                        <span class="rating-value"><?php echo number_format($recent_review['rating'], 1); ?></span>
                                                    </div>
                                                </div>
                                                <p class="recent-review-comment"><?php echo htmlspecialchars(mb_substr($recent_review['comment'], 0, 80) . (strlen($recent_review['comment']) > 80 ? '...' : '')); ?></p>
                                                <a href="#reviews-title" class="view-all-reviews-btn">View All Reviews</a>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviews Section -->
                <section class="game-sections reviews-section" aria-labelledby="reviews-title">
                    <div class="section-header">
                        <h2 class="section-title" id="reviews-title">User Reviews</h2>
                        <span class="review-count"><?php echo mysqli_num_rows($result_reviews); ?> reviews</span>
                    </div>

                    <div class="reviews-container">
                        <?php
                        if (mysqli_num_rows($result_reviews) > 0) {
                            while ($review = mysqli_fetch_assoc($result_reviews)) {
                                ?>
                                <div class="game-card review-card">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <img src="image/<?php echo !empty($review['foto']) ? $review['foto'] : 'avatar.png'; ?>"
                                                 alt="<?php echo htmlspecialchars($review['username']); ?> avatar" 
                                                 class="reviewer-avatar" />
                                            <div class="reviewer-details">
                                                <span class="reviewer-name"><?php echo htmlspecialchars($review['username']); ?></span>
                                                <span class="review-date"><?php echo date("F j, Y", strtotime($review['created_at'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="review-rating">
                                            <span class="stars">
                                                <?php echo generateStarRating($review['rating']); ?>
                                            </span>
                                            <span class="rating-value"><?php echo number_format($review['rating'], 1); ?>/5</span>
                                        </div>
                                    </div>
                                    <div class="review-content">
                                        <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            ?>
                            <div class="empty-message">
                                <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <h3>No reviews yet</h3>
                                <p>Be the first to review this game!</p>
                            </div>
                            <?php
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

        // Toggle Cart function
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
                        
                        // Redirect to cart for detail page
                        setTimeout(() => {
                            window.location.href = 'cart.php';
                        }, 1000);
                    } else if (data.action === 'removed') {
                        button.textContent = 'Add to Cart';
                        button.classList.remove('added');
                        button.dataset.inCart = 'false';
                    }
                    
                    // Update cart badge by fetching current count
                    updateCartBadge();
                    
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

        // Toggle Wishlist function
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

        // Simple notification system
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('wishlist-notification');
            const messageEl = notification.querySelector('.wishlist-message');
            
            messageEl.textContent = message;
            notification.className = `wishlist-notification ${type} show`;
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Toggle description function
        function toggleDescription() {
            const content = document.getElementById('description-content');
            const button = document.getElementById('read-more-btn');
            
            if (content.classList.contains('expanded')) {
                content.classList.remove('expanded');
                button.textContent = 'Read More';
            } else {
                content.classList.add('expanded');
                button.textContent = 'Read Less';
            }
        }

        // Check if description needs read more button
        function checkDescriptionLength() {
            const content = document.getElementById('description-content');
            const button = document.getElementById('read-more-btn');
            
            if (content.scrollHeight > content.clientHeight) {
                button.style.display = 'inline-block';
            }
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
            
            // Check description length and show read more if needed
            checkDescriptionLength();
            
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