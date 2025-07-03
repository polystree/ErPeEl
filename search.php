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
$foto = $user_data ? $user_data['foto'] : null;

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

$search_query = isset($_GET['query']) ? mysqli_real_escape_string($con, $_GET['query']) : '';
$selected_categories = isset($_GET['category']) ? $_GET['category'] : [];
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'terbaru';

$sql_condition = "WHERE 1=1";

if ($search_query != '') {
    $sql_condition .= " AND (p.nama LIKE '%$search_query%' OR p.pengembang LIKE '%$search_query%')";
}

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

// Get ALL available categories that match the search query (not just filtered results)
// This allows users to see and select multiple categories
$base_sql_condition = "WHERE 1=1";
if ($search_query != '') {
    $base_sql_condition .= " AND (p.nama LIKE '%$search_query%' OR p.pengembang LIKE '%$search_query%')";
}

$available_categories = [];
$category_sql = "SELECT DISTINCT p.kategori_id FROM produk p $base_sql_condition";
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
    <meta name="description" content="Search games at Vault - Your digital game store">
    <title>Search Results - Vault Digital Store</title>
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
                        <?php if ($foto): ?>
                            <img src="image/<?php echo $foto; ?>" class="icon-img profile-avatar" alt="" width="44" height="44" style="border-radius: 50%; object-fit: cover; filter: none; width: 44px; height: 44px;">
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
                    <h4 class="category-main">Filter by Category</h4>
                    <form method="GET" action="search.php" id="categoryFilter">
                        <input type="hidden" name="query" value="<?php echo htmlspecialchars($search_query); ?>">
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
                                <li class="no-categories">No categories available for current search</li>
                            <?php endif; ?>
                        </ul>
                    </form>
                </div>
            </aside>
            
            <div class="main-section">
                <div class="search-header">
                    <div class="search-title-row">
                        <h1 class="section-title">
                            <?php if ($search_query): ?>
                                Search Results for "<?php echo htmlspecialchars($search_query); ?>"
                            <?php else: ?>
                                Browse Games
                            <?php endif; ?>
                        </h1>
                        <div class="search-filters">
                            <form method="GET" action="search.php" class="sort-form">
                                <input type="hidden" name="query" value="<?php echo htmlspecialchars($search_query); ?>">
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

                <section class="game-sections" aria-labelledby="search-results-title">
                    <div class="game-container" role="list" aria-label="Search results" id="search-results-container">
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
                                <p><?php echo $search_query ? 'Try adjusting your search terms or filters' : 'Start searching for games'; ?></p>
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

        // Load more results functionality
        let currentOffset = 20; // Initial load was 20 items
        
        function loadMoreResults() {
            const seeMoreBtn = document.getElementById('see-more-btn');
            const seeMoreText = seeMoreBtn.querySelector('.see-more-text');
            const seeMoreLoading = seeMoreBtn.querySelector('.see-more-loading');
            
            // Show loading state
            seeMoreBtn.disabled = true;
            seeMoreText.style.display = 'none';
            seeMoreLoading.style.display = 'inline';
            
            // Get current search parameters
            const urlParams = new URLSearchParams(window.location.search);
            const query = urlParams.get('query') || '';
            const sortBy = urlParams.get('sort_by') || 'terbaru';
            const categories = urlParams.getAll('category');
            
            // Build request URL
            let requestUrl = `ajax_load_more_search.php?offset=${currentOffset}&query=${encodeURIComponent(query)}&sort_by=${sortBy}`;
            categories.forEach(cat => {
                requestUrl += `&category[]=${cat}`;
            });
            
            fetch(requestUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.html) {
                    // Append new results to the container
                    const container = document.getElementById('search-results-container');
                    container.insertAdjacentHTML('beforeend', data.html);
                    
                    // Update offset for next load
                    currentOffset = data.newOffset;
                    
                    // Hide "See More" button if no more results
                    if (!data.hasMore) {
                        document.getElementById('see-more-container').style.display = 'none';
                    }
                    
                    // Smooth scroll to new content
                    const newCards = container.querySelectorAll('.game-card');
                    if (newCards.length > 0) {
                        const lastOldCard = newCards[newCards.length - 21]; // Scroll to start of new content (20 new cards + 1)
                        if (lastOldCard) {
                            lastOldCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                    
                    showNotification('More games loaded!', 'success');
                } else {
                    showNotification('Failed to load more games', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading more results:', error);
                showNotification('Network error. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                seeMoreBtn.disabled = false;
                seeMoreText.style.display = 'inline';
                seeMoreLoading.style.display = 'none';
            });
        }
    </script>

</body>

</html>