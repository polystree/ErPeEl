<?php
session_start();
require "koneksi.php";
require "image_helper.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Check if user is logged in
$is_logged_in = isset($_SESSION['loginbtn']) && $_SESSION['loginbtn'] == true;
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

// Get user info for navbar (only if logged in)
$foto = null;
if ($is_logged_in) {
    $user_query = mysqli_query($con, "SELECT foto FROM `users` WHERE id = '$user_id'");
    $user_data = mysqli_fetch_assoc($user_query);
    $foto = $user_data ? $user_data['foto'] : null;
}

// Check for payment success notification (only for logged in users)
$payment_success = false;
$order_id = null;
if ($is_logged_in && isset($_SESSION['payment_success']) && $_SESSION['payment_success'] === true) {
    $payment_success = true;
    $order_id = $_SESSION['order_id'] ?? null;
    // Clear the session variables
    unset($_SESSION['payment_success']);
    unset($_SESSION['order_id']);
}

// Get user's wishlist items (only if logged in)
$wishlist_ids = [];
if ($is_logged_in) {
    $get_wishlist = mysqli_query($con, "SELECT produk_id FROM wishlist WHERE user_id = '$user_id'");
    while ($row = mysqli_fetch_assoc($get_wishlist)) {
        $wishlist_ids[] = $row['produk_id'];
    }
}

// Get user's cart items (only if logged in)
$cart_ids = [];
if ($is_logged_in) {
    $get_cart = mysqli_query($con, "SELECT produk_id FROM cart WHERE user_id = '$user_id'");
    while ($row = mysqli_fetch_assoc($get_cart)) {
        $cart_ids[] = $row['produk_id'];
    }
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

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Vault - Discover the best games at unbeatable prices">
    <title>Home - Vault Digital Store</title>
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
                        <a href="login.php" aria-label="Login to view cart" onclick="showGuestMessage('cart')">
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
        <?php if ($payment_success): ?>
            <div class="payment-success-banner" style="
                position: relative;
                background: linear-gradient(135deg, #10b981, #059669);
                color: white;
                padding: var(--space-xl);
                margin-bottom: var(--space-lg);
                border-radius: var(--radius-lg);
                box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
                border: 1px solid rgba(255, 255, 255, 0.2);
                animation: slideInFromTop 0.6s ease-out;
                overflow: hidden;
            ">
                <!-- Decorative background pattern -->
                <div style="
                    position: absolute;
                    top: 0;
                    right: 0;
                    width: 200px;
                    height: 200px;
                    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
                    background-size: 20px 20px;
                    opacity: 0.3;
                    pointer-events: none;
                "></div>
                
                <!-- Close button -->
                <button onclick="closeBanner()" style="
                    position: absolute;
                    top: var(--space-md);
                    right: var(--space-md);
                    background: rgba(255, 255, 255, 0.2);
                    border: none;
                    color: white;
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: background 0.3s ease;
                    font-size: 18px;
                    line-height: 1;
                " onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'">
                    ×
                </button>

                <div style="display: flex; align-items: center; justify-content: center; gap: var(--space-lg); position: relative, z-index: 1;">
                    <!-- Success icon with animation -->
                    <div style="
                        width: 60px;
                        height: 60px;
                        background: rgba(255, 255, 255, 0.2);
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        animation: pulse 2s ease-in-out infinite;
                    ">
                        <svg style="width: 32px; height: 32px;" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"/>
                        </svg>
                    </div>
                    
                    <div style="text-align: left;">
                        <h3 style="margin: 0 0 var(--space-sm) 0; font-size: 1.4rem; font-weight: 700;">
                            🎉 Payment Successful!
                        </h3>
                        <p style="margin: 0 0 var(--space-sm) 0; opacity: 0.9; font-size: 1rem; line-height: 1.5;">
                            <?php if ($order_id): ?>
                                Your order <strong>#<?php echo $order_id; ?></strong> has been confirmed.<br>
                                Steam keys are being sent to your email now!
                            <?php else: ?>
                                Your order has been confirmed. Thank you for your purchase!<br>
                                Steam keys are being sent to your email now!
                            <?php endif; ?>
                        </p>
                        <div style="display: flex; gap: var(--space-md); margin-top: var(--space-md);">
                            <a href="profile.php#orders" style="
                                background: rgba(255, 255, 255, 0.2);
                                color: white;
                                padding: var(--space-sm) var(--space-md);
                                border-radius: var(--radius-md);
                                text-decoration: none;
                                font-size: 0.9rem;
                                font-weight: 500;
                                transition: all 0.3s ease;
                                border: 1px solid rgba(255, 255, 255, 0.3);
                            " onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'">
                                📋 View Order History
                            </a>
                            <a href="semua.php" style="
                                background: rgba(255, 255, 255, 0.2);
                                color: white;
                                padding: var(--space-sm) var(--space-md);
                                border-radius: var(--radius-md);
                                text-decoration: none;
                                font-size: 0.9rem;
                                font-weight: 500;
                                transition: all 0.3s ease;
                                border: 1px solid rgba(255, 255, 255, 0.3);
                            " onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'">
                                🎮 Browse More Games
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Progress bar for auto-hide -->
                <div style="
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    height: 3px;
                    background: rgba(255, 255, 255, 0.4);
                    animation: shrinkWidth 8s linear forwards;
                "></div>
            </div>
        <?php endif; ?>
        
        <div class="content-layout">
            <aside class="categories-sidebar">
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
        <?php
        // Get 5 random featured games from the database
        $featured_games_query = mysqli_query($con, "SELECT * FROM `produk` ORDER BY RAND() LIMIT 5");
        if (mysqli_num_rows($featured_games_query) > 0) {
            $featured_games = [];
            while ($row = mysqli_fetch_assoc($featured_games_query)) {
                $featured_games[] = $row;
            }
        ?>
        
        <div class="featured-game" role="region" aria-label="Featured games">
            <div class="featured-game-container">
                <div class="featured-game-track">
                    <?php foreach ($featured_games as $index => $featured_game): 
                        $has_discount = !empty($featured_game['harga_diskon']) && $featured_game['harga_diskon'] > 0;
                        $original_price = $featured_game['harga'];
                        $discounted_price = $has_discount ? $featured_game['harga_diskon'] : $original_price;
                        $discount_percentage = $has_discount ? round((($original_price - $discounted_price) / $original_price) * 100) : 0;
                    ?>
                    <div class="featured-game-slide" data-index="<?php echo $index; ?>">
                        <a href="detail.php?produk_id=<?php echo $featured_game['id']; ?>" class="featured-game-link">
                            <div class="featured-game-image">
                                <img src="<?php echo getImageSrc(htmlspecialchars($featured_game['foto'])); ?>"
                                     alt="<?php echo htmlspecialchars($featured_game['nama']); ?> - Featured Game" 
                                     loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                                <div class="featured-overlay">
                                    <div class="featured-content">
                                        <div class="featured-badge">Featured Game</div>
                                        <h2 class="featured-title"><?php echo htmlspecialchars($featured_game['nama']); ?></h2>
                                        
                                        <div class="featured-price-section">
                                            <?php if ($has_discount): ?>
                                                <span class="featured-discount-badge">-<?php echo $discount_percentage; ?>%</span>
                                                <div class="featured-price-container">
                                                    <span class="featured-price-original">$<?php echo number_format($original_price, 2); ?></span>
                                                    <span class="featured-price">$<?php echo number_format($discounted_price, 2); ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="featured-price">$<?php echo number_format($original_price, 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <?php } ?>        <section class="game-sections" aria-labelledby="recommended-title">
            <div class="section-header">
                <h2 class="section-title" id="recommended-title">Recommended Games</h2>
                <button class="view-all-btn" onclick="window.location.href='semua.php';" aria-label="View all recommended games">
                    View All
                </button>
            </div>

            <div class="game-container" role="list" aria-label="Recommended games list">
                <?php
                $select_produk = mysqli_query($con, "SELECT * FROM `produk` ORDER BY RAND() LIMIT 12") or die('Query failed');

                if (mysqli_num_rows($select_produk) > 0) {
                    while ($fetch_produk = mysqli_fetch_assoc($select_produk)) {
                        $is_in_wishlist = in_array($fetch_produk['id'], $wishlist_ids);
                        $is_in_cart = in_array($fetch_produk['id'], $cart_ids);
                        $average_rating = getAverageRating($con, $fetch_produk['id']);
                        ?>
                        <article class="game-card" role="listitem" 
                                 onclick="window.location.href='detail.php?produk_id=<?php echo $fetch_produk['id']; ?>'"
                                 onkeydown="if(event.key==='Enter'||event.key===' ') window.location.href='detail.php?produk_id=<?php echo $fetch_produk['id']; ?>'"
                                 tabindex="0">
                            <div class="game-cover-container">
                                <img class="game-cover" 
                                     src="<?php echo getImageSrc($fetch_produk['foto']); ?>"
                                     alt="<?php echo htmlspecialchars($fetch_produk['nama']); ?> cover" 
                                     loading="lazy" />
                                <button type="button" 
                                        class="wishlist-btn <?php echo $is_in_wishlist ? 'active' : ''; ?>" 
                                        data-produk-id="<?php echo $fetch_produk['id']; ?>"
                                        data-in-wishlist="<?php echo $is_in_wishlist ? 'true' : 'false'; ?>"
                                        onclick="event.stopPropagation(); <?php echo $is_logged_in ? 'toggleWishlist(this)' : 'redirectToLogin()'; ?>;"
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
                                        <?php echo generateStarRating($average_rating['avg']); ?>
                                    </span>
                                    <span class="rating-text">
                                        <?php echo $average_rating['count'] > 0 
                                              ? $average_rating['avg'] . '/5 (' . $average_rating['count'] . ')' 
                                              : 'No ratings'; ?>
                                    </span>
                                </div>
                                <button class="add-to-cart-btn <?php echo $is_in_cart ? 'added' : ''; ?>" 
                                        data-product-id="<?php echo $fetch_produk['id']; ?>"
                                        data-in-cart="<?php echo $is_in_cart ? 'true' : 'false'; ?>"
                                        onclick="event.stopPropagation(); <?php echo $is_logged_in ? 'addToCart(' . $fetch_produk['id'] . ')' : 'redirectToLogin()'; ?>;">
                                    <?php echo $is_in_cart ? 'Added' : 'Add to Cart'; ?>
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
        </section>        <section class="game-sections" aria-labelledby="new-games-title">
            <div class="section-header">
                <h2 class="section-title" id="new-games-title">New Releases</h2>
                <button class="view-all-btn" onclick="window.location.href='baru.php';" aria-label="View all new games">
                    View All
                </button>
            </div>

            <div class="game-container" role="list" aria-label="New games list">
                <?php
                $select_produk = mysqli_query($con, "SELECT * FROM `produk` ORDER BY `id` DESC LIMIT 12") or die('Query failed');

                if (mysqli_num_rows($select_produk) > 0) {
                    while ($fetch_produk = mysqli_fetch_assoc($select_produk)) {
                        $is_in_wishlist = in_array($fetch_produk['id'], $wishlist_ids);
                        $is_in_cart = in_array($fetch_produk['id'], $cart_ids);
                        $average_rating = getAverageRating($con, $fetch_produk['id']);
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
                                <div class="new-badge">NEW</div>
                                <button type="button" 
                                        class="wishlist-btn <?php echo $is_in_wishlist ? 'active' : ''; ?>" 
                                        data-produk-id="<?php echo $fetch_produk['id']; ?>"
                                        data-in-wishlist="<?php echo $is_in_wishlist ? 'true' : 'false'; ?>"
                                        onclick="event.stopPropagation(); <?php echo $is_logged_in ? 'toggleWishlist(this)' : 'redirectToLogin()'; ?>;"
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
                                        <?php echo generateStarRating($average_rating['avg']); ?>
                                    </span>
                                    <span class="rating-text">
                                        <?php echo $average_rating['count'] > 0 
                                              ? $average_rating['avg'] . '/5 (' . $average_rating['count'] . ')' 
                                              : 'No ratings'; ?>
                                    </span>
                                </div>
                                <button class="add-to-cart-btn <?php echo $is_in_cart ? 'added' : ''; ?>" 
                                        data-product-id="<?php echo $fetch_produk['id']; ?>"
                                        data-in-cart="<?php echo $is_in_cart ? 'true' : 'false'; ?>"
                                        onclick="event.stopPropagation(); <?php echo $is_logged_in ? 'addToCart(' . $fetch_produk['id'] . ')' : 'redirectToLogin()'; ?>;">
                                    <?php echo $is_in_cart ? 'Added' : 'Add to Cart'; ?>
                                </button>
                            </div>
                        </article>
                        <?php
                    }
                } else {
                    echo '<p class="empty-message">No new games available yet.</p>';
                }
                ?>
            </div>
        </section>        <section class="game-sections" aria-labelledby="special-offers-title">
            <div class="section-header">
                <h2 class="section-title" id="special-offers-title">Special Offers</h2>
                <button class="view-all-btn" onclick="window.location.href='promo.php';" aria-label="View all special offers">
                    View All
                </button>
            </div>

            <div class="game-container" role="list" aria-label="Special offers list">
                <?php
                $select_produk = mysqli_query($con, "SELECT * FROM `produk` WHERE `harga_diskon` IS NOT NULL AND `harga_diskon` > 0 LIMIT 12") or die('Query failed');

                if (mysqli_num_rows($select_produk) > 0) {
                    while ($fetch_produk = mysqli_fetch_assoc($select_produk)) {
                        $is_in_wishlist = in_array($fetch_produk['id'], $wishlist_ids);
                        $is_in_cart = in_array($fetch_produk['id'], $cart_ids);
                        $discount_percent = round((($fetch_produk['harga'] - $fetch_produk['harga_diskon']) / $fetch_produk['harga']) * 100);
                        $average_rating = getAverageRating($con, $fetch_produk['id']);
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
                                        onclick="event.stopPropagation(); <?php echo $is_logged_in ? 'toggleWishlist(this)' : 'redirectToLogin()'; ?>;"
                                        aria-label="<?php echo $is_in_wishlist ? 'Remove from wishlist' : 'Add to wishlist'; ?>">
                                    <svg class="heart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="game-info">
                                <div class="game-price">
                                    <div class="sale-badge">-<?php echo $discount_percent; ?>%</div>
                                    <span class="original-price">$<?php echo number_format($fetch_produk['harga'], 2); ?></span>
                                    <span class="discounted-price">$<?php echo number_format($fetch_produk['harga_diskon'], 2); ?></span>
                                </div>
                                <h3 class="game-title"><?php echo htmlspecialchars($fetch_produk['nama']); ?></h3>
                                <p class="game-developer"><?php echo htmlspecialchars($fetch_produk['pengembang']); ?></p>
                                <?php
                                // Get the actual average rating
                                $rating_data = getAverageRating($con, $fetch_produk['id']);
                                $avg_rating = $rating_data['avg'];
                                $rating_count = $rating_data['count'];
                                ?>
                                <div class="game-rating">
                                    <span class="stars"><?php echo generateStarRating($avg_rating); ?></span>
                                    <span class="rating-text">
                                        <?php echo $rating_count > 0 
                                              ? $avg_rating . '/5 (' . $rating_count . ')' 
                                              : 'No ratings'; ?>
                                    </span>
                                </div>
                                <button class="add-to-cart-btn special <?php echo $is_in_cart ? 'added' : ''; ?>" 
                                        data-product-id="<?php echo $fetch_produk['id']; ?>"
                                        data-in-cart="<?php echo $is_in_cart ? 'true' : 'false'; ?>"
                                        onclick="event.stopPropagation(); <?php echo $is_logged_in ? 'addToCart(' . $fetch_produk['id'] . ')' : 'redirectToLogin()'; ?>;">
                                    <?php echo $is_in_cart ? 'Added' : 'Buy Now'; ?>
                                </button>
                            </div>
                        </article>
                        <?php
                    }
                } else {
                    echo '<p class="empty-message">No special offers available at the moment.</p>';
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
        // Ensure all functions are in global scope
        const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        
        console.log('Dashboard script loaded. User logged in:', isLoggedIn);

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

        // Notification system
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('wishlist-notification');
            if (!notification) {
                console.error('Notification element not found');
                return;
            }
            
            const messageEl = notification.querySelector('.wishlist-message');
            if (!messageEl) {
                console.error('Message element not found');
                return;
            }
            
            messageEl.textContent = message;
            notification.className = `wishlist-notification ${type} show`;
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Redirect to login function
        function redirectToLogin() {
            showNotification("Please log in to use this feature", "warning");
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 1500);
        }

        // Add to cart function
        function addToCart(gameId) {
            console.log('addToCart called with gameId:', gameId);
            
            if (!isLoggedIn) {
                redirectToLogin();
                return;
            }

            // Find the button that was clicked
            let button = null;
            const buttons = document.querySelectorAll(`[data-product-id="${gameId}"]`);
            for (let btn of buttons) {
                if (btn.classList.contains('add-to-cart-btn')) {
                    button = btn;
                    break;
                }
            }
            
            if (!button) {
                console.error('Could not find add-to-cart button for gameId:', gameId);
                return;
            }

            const originalText = button.textContent;
            const isInCart = button.getAttribute("data-in-cart") === "true";
            const isBuyNowButton = originalText.toLowerCase().includes('buy now');
            
            // Update button state immediately
            button.disabled = true;
            button.textContent = isInCart ? 'Removing...' : 'Adding...';

            fetch("ajax_handler.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `action=add_to_cart&produk_id=${gameId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    updateCartBadge();
                    
                    if (data.action === 'added') {
                        button.textContent = 'Added';
                        button.classList.add('added');
                        button.setAttribute("data-in-cart", "true");
                        
                        if (isBuyNowButton) {
                            setTimeout(() => {
                                window.location.href = 'cart.php';
                            }, 500);
                        }
                    } else if (data.action === 'removed') {
                        button.textContent = isBuyNowButton ? 'Buy Now' : 'Add to Cart';
                        button.classList.remove('added');
                        button.setAttribute("data-in-cart", "false");
                    }
                } else {
                    button.textContent = originalText;
                    showNotification(data.message || "Failed to update cart", "error");
                }
            })
            .catch((error) => {
                console.error("Cart error:", error);
                button.textContent = originalText;
                showNotification("Network error. Please try again.", "error");
            })
            .finally(() => {
                button.disabled = false;
            });
        }

        // Toggle wishlist function
        function toggleWishlist(button) {
            console.log('toggleWishlist called with:', button);
            
            if (!isLoggedIn) {
                redirectToLogin();
                return;
            }

            const productId = button.getAttribute('data-produk-id');
            const isInWishlist = button.getAttribute('data-in-wishlist') === 'true';
            const newStatus = !isInWishlist;
            
            // Update UI immediately
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

        // Close banner function
        function closeBanner() {
            const banner = document.querySelector('.payment-success-banner');
            if (banner) {
                banner.style.animation = 'slideUp 0.5s ease-out forwards';
                setTimeout(() => banner.remove(), 500);
            }
        }

        // Initialize everything
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM Content Loaded - Functions available:', {
                addToCart: typeof addToCart,
                toggleWishlist: typeof toggleWishlist,
                showNotification: typeof showNotification
            });
            
            // Auto-hide payment success banner after 8 seconds
            const paymentBanner = document.querySelector('.payment-success-banner');
            if (paymentBanner) {
                setTimeout(() => {
                    closeBanner();
                }, 8000);
            }
            
            // Add scroll to top
            addScrollToTop();
            
            // Load initial cart count
            updateCartBadge();
            
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