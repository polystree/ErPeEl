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

$selected_categories = isset($_GET['category']) ? $_GET['category'] : [];
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'terbaru';

$sql_condition = "WHERE 1=1";

if (!empty($selected_categories)) {
    $category_ids_str = implode(",", array_map('intval', $selected_categories));
    $sql_condition .= " AND p.kategori_id IN ($category_ids_str)";
}

// Order by mapping
$order_by = "p.id DESC"; // Default
switch ($sort_by) {
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

// Query untuk mengambil data produk
$sql = "SELECT p.* FROM produk p $sql_condition ORDER BY $order_by LIMIT 20";
$select_produk = mysqli_query($con, $sql) or die('Query failed: ' . mysqli_error($con));

// Get ALL available categories
$available_categories = [];
$category_sql = "SELECT DISTINCT p.kategori_id FROM produk p WHERE 1=1";
$category_result = mysqli_query($con, $category_sql);
while ($row = mysqli_fetch_assoc($category_result)) {
    $available_categories[] = $row['kategori_id'];
}

// Get category names for the available categories
$category_names = [];
if (!empty($available_categories)) {
    $category_ids_str = implode(',', array_map('intval', $available_categories));
    $category_query = mysqli_query($con, "SELECT id, nama FROM kategori WHERE id IN ($category_ids_str) ORDER BY nama");
    while ($cat_row = mysqli_fetch_assoc($category_query)) {
        $category_names[$cat_row['id']] = $cat_row['nama'];
    }
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

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse all games at Vault - Your digital game store">
    <title>All Games - Vault Digital Store</title>
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
                <div class="category-section">
                    <h4 class="category-main">Quick Actions</h4>
                    <ul class="category-list">
                        <li><a href="dashboard.php">Back to Dashboard</a></li>
                        <li><a href="cart.php">My Cart</a></li>
                        <li><a href="profile.php">My Profile</a></li>
                    </ul>
                </div>
                <div class="category-section">
                    <h4 class="category-main">Filter by Category</h4>
                    <form method="GET" action="semua.php" id="categoryFilter">
                        <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by); ?>">
                        <ul class="category-list">
                            <?php if (!empty($category_names)): ?>
                                <?php foreach ($category_names as $cat_id => $cat_name): ?>
                                    <li>
                                        <label>
                                            <input type="checkbox" name="category[]" value="<?php echo $cat_id; ?>" 
                                                   <?php echo in_array((string)$cat_id, $selected_categories) ? 'checked' : ''; ?> 
                                                   onchange="submitCategoryFilter()">
                                            <?php echo htmlspecialchars($cat_name); ?>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="no-categories">No categories available</li>
                            <?php endif; ?>
                        </ul>
                    </form>
                </div>
            </aside>
            
            <div class="main-section">
                <div class="search-header">
                    <div class="search-title-row">
                        <h1 class="section-title">All Games</h1>
                        <div class="search-filters">
                            <form method="GET" action="semua.php" class="sort-form">
                                <?php foreach ($selected_categories as $category) { ?>
                                    <input type="hidden" name="category[]" value="<?php echo $category; ?>">
                                <?php } ?>
                                <select name="sort_by" class="sort-select" onchange="this.form.submit();">
                                    <option value="terbaru" <?php echo $sort_by == 'terbaru' ? 'selected' : ''; ?>>Newest</option>
                                    <option value="terlaris" <?php echo $sort_by == 'terlaris' ? 'selected' : ''; ?>>Most Popular</option>
                                    <option value="harga_terendah" <?php echo $sort_by == 'harga_terendah' ? 'selected' : ''; ?>>Lowest Price</option>
                                    <option value="harga_tertinggi" <?php echo $sort_by == 'harga_tertinggi' ? 'selected' : ''; ?>>Highest Price</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

                <section class="game-sections" aria-labelledby="all-games-title">
                    <div class="game-container" role="list" aria-label="All games list" id="all-games-container">
                        <?php
                        if (mysqli_num_rows($select_produk) > 0) {
                            while ($fetch_produk = mysqli_fetch_assoc($select_produk)) {
                                $rating_data = getAverageRating($con, $fetch_produk['id']);
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
                                <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                                <h2>No games found</h2>
                                <p>Try adjusting your filters or browse our full catalog</p>
                                <button class="add-to-cart-btn" onclick="window.location.href='dashboard.php';">
                                    Browse All Games
                                </button>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    
                    <?php
                    // Check if there are more results to show "See More" button
                    $total_sql = "SELECT COUNT(*) as total FROM produk p $sql_condition";
                    $total_result = mysqli_query($con, $total_sql);
                    $total_count = mysqli_fetch_assoc($total_result)['total'];
                    $has_more_results = $total_count > 20; // Initial load is 20 items
                    
                    if ($has_more_results && mysqli_num_rows($select_produk) > 0):
                    ?>
                    <div class="see-more-container" id="see-more-container">
                        <button class="see-more-btn" id="see-more-btn" onclick="loadMoreResults()">
                            <span class="see-more-text">See More</span>
                            <span class="see-more-loading" style="display: none;">Loading...</span>
                        </button>
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

        // Category filter submission
        function submitCategoryFilter() {
            document.getElementById('categoryFilter').submit();
        }

        // Toggle Cart function
        function toggleCart(button) {
            const productId = button.dataset.productId;
            const inCart = button.dataset.inCart === 'true';
            const newStatus = !inCart;
            
            // Update UI immediately for better UX
            button.dataset.inCart = newStatus.toString();
            button.classList.toggle('added', newStatus);
            button.textContent = newStatus ? 'In Cart' : 'Add to Cart';
            button.disabled = true;

            // AJAX request to toggle cart
            fetch('ajax_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add_to_cart&produk_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                button.disabled = false;
                if (data.success) {
                    // Update cart badge
                    updateCartBadge();
                    showNotification(`Game ${newStatus ? 'added to' : 'removed from'} cart!`, 'success');
                    
                    // Update button text based on action
                    if (data.action === 'added') {
                        button.textContent = 'In Cart';
                        button.classList.add('added');
                        button.dataset.inCart = 'true';
                    } else if (data.action === 'removed') {
                        button.textContent = 'Add to Cart';
                        button.classList.remove('added');
                        button.dataset.inCart = 'false';
                    }
                } else {
                    // Revert UI changes if request failed
                    button.dataset.inCart = inCart.toString();
                    button.classList.toggle('added', inCart);
                    button.textContent = inCart ? 'In Cart' : 'Add to Cart';
                    showNotification('Failed to update cart. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.disabled = false;
                // Revert UI changes if request failed
                button.dataset.inCart = inCart.toString();
                button.classList.toggle('added', inCart);
                button.textContent = inCart ? 'In Cart' : 'Add to Cart';
                showNotification('Network error. Please check your connection.', 'error');
            });
        }

        // Toggle Wishlist function
        function toggleWishlist(button) {
            const produkId = button.dataset.produkId;
            const inWishlist = button.dataset.inWishlist === 'true';
            const newStatus = !inWishlist;
            
            // Update UI immediately for better UX
            button.dataset.inWishlist = newStatus.toString();
            button.classList.toggle('active', newStatus);
            button.disabled = true;

            // AJAX request to toggle wishlist
            fetch('ajax_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=toggle_wishlist&produk_id=${produkId}`
            })
            .then(response => response.json())
            .then(data => {
                button.disabled = false;
                if (data.success) {
                    showWishlistNotification(newStatus ? 'Added to wishlist!' : 'Removed from wishlist!');
                } else {
                    // Revert UI changes if request failed
                    button.dataset.inWishlist = inWishlist.toString();
                    button.classList.toggle('active', inWishlist);
                    showWishlistNotification('Failed to update wishlist. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.disabled = false;
                // Revert UI changes if request failed
                button.dataset.inWishlist = inWishlist.toString();
                button.classList.toggle('active', inWishlist);
                showWishlistNotification('Network error. Please check your connection.');
            });
        }

        // Show wishlist notification
        function showWishlistNotification(message) {
            const notification = document.getElementById('wishlist-notification');
            const messageSpan = notification.querySelector('.wishlist-message');
            
            messageSpan.textContent = message;
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Show general notification
        function showNotification(message, type = 'success') {
            // Create notification element if it doesn't exist
            let notification = document.getElementById('general-notification');
            if (!notification) {
                notification = document.createElement('div');
                notification.id = 'general-notification';
                notification.className = 'wishlist-notification';
                notification.innerHTML = `
                    <svg class="heart-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="wishlist-message"></span>
                `;
                document.body.appendChild(notification);
            }
            
            const messageSpan = notification.querySelector('.wishlist-message');
            messageSpan.textContent = message;
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Update cart badge
        function updateCartBadge() {
            fetch('ajax_get_cart.php')
            .then(response => response.json())
            .then(data => {
                const cartBadge = document.querySelector('.cart-badge');
                const cartIcon = document.querySelector('.nav-icon a[href="cart.php"]');
                
                if (data.count > 0) {
                    if (cartBadge) {
                        cartBadge.textContent = data.count > 99 ? '99+' : data.count;
                    } else {
                        const badge = document.createElement('span');
                        badge.className = 'cart-badge';
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                        cartIcon.appendChild(badge);
                    }
                } else {
                    if (cartBadge) {
                        cartBadge.remove();
                    }
                }
            })
            .catch(error => {
                console.error('Error updating cart badge:', error);
            });
        }

        // Load more results functionality
        let currentOffset = 20; // Start after initial 20 items
        let isLoading = false;

        function loadMoreResults() {
            if (isLoading) return;
            
            isLoading = true;
            const btn = document.getElementById('see-more-btn');
            const btnText = btn.querySelector('.see-more-text');
            const btnLoading = btn.querySelector('.see-more-loading');
            
            btn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';

            // Get current filter parameters
            const urlParams = new URLSearchParams(window.location.search);
            const formData = new FormData();
            formData.append('offset', currentOffset);
            
            // Add current filters
            if (urlParams.get('sort_by')) {
                formData.append('sort_by', urlParams.get('sort_by'));
            }
            
            // Add selected categories
            const categoryInputs = document.querySelectorAll('input[name="category[]"]:checked');
            categoryInputs.forEach(input => {
                formData.append('category[]', input.value);
            });

            fetch('ajax_load_more_semua.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.html) {
                    // Append new games to container
                    const container = document.getElementById('all-games-container');
                    container.insertAdjacentHTML('beforeend', data.html);
                    
                    currentOffset += 20;
                    
                    // Hide button if no more results
                    if (!data.hasMore) {
                        document.getElementById('see-more-container').style.display = 'none';
                    }
                } else {
                    showNotification('Failed to load more games', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading more games:', error);
                showNotification('Network error while loading games', 'error');
            })
            .finally(() => {
                isLoading = false;
                btn.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
            });
        }
    </script>

</body>

</html>