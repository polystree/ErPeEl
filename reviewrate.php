<?php
require "session.php";
require "koneksi.php";
require "image_helper.php";

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

// Get order ID from URL
// Get order ID and optional product filter from URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$filter_produk_id = isset($_GET['produk_id']) ? intval($_GET['produk_id']) : 0;

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_reviews'])) {
    $produk_ids = $_POST['produk_id'];
    $ratings = $_POST['rating'];
    $comments = $_POST['comment'];
    
    $success_count = 0;
    foreach ($produk_ids as $index => $produk_id) {
        $rating = isset($ratings[$index]) ? intval($ratings[$index]) : 0;
        $comment = isset($comments[$index]) ? mysqli_real_escape_string($con, $comments[$index]) : '';
        
        if ($produk_id > 0 && $rating > 0 && $rating <= 5) {
            $check_existing = mysqli_query($con, "SELECT id FROM rating WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
            
            if (mysqli_num_rows($check_existing) == 0) {
                $query = "INSERT INTO rating (produk_id, user_id, rating, comment, created_at) VALUES ('$produk_id', '$user_id', '$rating', '$comment', NOW())";
                if (mysqli_query($con, $query)) {
                    $success_count++;
                }
            }
        }
    }
    
    if ($success_count > 0) {
        echo json_encode(['success' => true, 'message' => "$success_count review(s) submitted successfully"]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit reviews']);
        exit();
    }
}

// Get order details for review
$order_details = [];
if ($order_id > 0) {
    // Build query to load items for review (product-level) with existing review check
    $where = "o.id = '$order_id' AND o.user_id = '$user_id'";
    if ($filter_produk_id > 0) {
        $where .= " AND oi.produk_id = '$filter_produk_id'";
    }
    $query = "SELECT
                o.id AS order_id,
                o.created_at AS tanggal_pesanan,
                p.id AS produk_id,
                p.nama AS nama_produk,
                p.foto AS gambar_produk,
                p.pengembang,
                p.harga AS harga_per_unit,
                r.id AS existing_review_id,
                r.rating AS existing_rating,
                r.comment AS existing_comment,
                r.created_at AS review_date
              FROM orders o
              JOIN order_items oi ON o.id = oi.order_id
              JOIN produk p ON oi.produk_id = p.id
              LEFT JOIN rating r ON r.produk_id = p.id AND r.user_id = '$user_id'
              WHERE $where";
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $order_details[] = $row;
        }
    }
}

// Get all user's orders if no specific order ID
if (empty($order_details)) {
    $query = "SELECT DISTINCT
                o.id AS order_id,
                o.created_at AS tanggal_pesanan,
                o.total AS total_harga,
                COUNT(oi.id) as item_count
              FROM orders o
              JOIN order_items oi ON o.id = oi.order_id
              WHERE o.user_id = '$user_id'
              GROUP BY o.id
              ORDER BY o.created_at DESC";
              
    $recent_orders = [];
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $recent_orders[] = $row;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Rate and review your purchased games at Vault - Your digital game store">
    <title>Rate & Review - Vault Digital Store</title>
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
                        <li><a href="profile.php">My Profile</a></li>
                        <li><a href="order.php">Order History</a></li>
                        <li><a href="cart.php">My Cart</a></li>
                    </ul>
                </div>
            </aside>
            
            <div class="main-section">
                <div class="search-header">
                    <div class="search-title-row">
                        <h1 class="section-title">
                            Order reviews
                        </h1>
                    </div>
                </div>

                <section class="review-sections" aria-labelledby="review-title">
                    <?php if (!empty($order_details)): ?>
                        <!-- Review Form for Specific Order -->
                        <form id="review-form" class="review-form">
                            <div class="order-info-header">
                                <h2>Order #<?php echo $order_details[0]['order_id']; ?></h2>
                                <p>Purchased on <?php echo date('F j, Y', strtotime($order_details[0]['tanggal_pesanan'])); ?></p>
                            </div>

                            <div class="review-container">
                                <?php foreach ($order_details as $index => $item): ?>
                                    <div class="review-item-card">
                                        <div class="game-info-section">
                                            <div class="game-image">
                                                <img src="<?php echo getImageSrc($item['gambar_produk']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['nama_produk']); ?>" 
                                                     loading="lazy">
                                            </div>
                                            <div class="game-details">
                                                <h3><?php echo htmlspecialchars($item['nama_produk']); ?></h3>
                                                <p class="developer"><?php echo htmlspecialchars($item['pengembang']); ?></p>
                                                <p class="price">$<?php echo number_format($item['harga_per_unit'], 2); ?></p>
                                            </div>
                                        </div>

                                        <?php if ($item['existing_review_id']): ?>
                                            <!-- Already Reviewed Section -->
                                            <div class="already-reviewed-section">
                                                <div class="review-status">
                                                    <h4 class="status-title">✅ Already Reviewed</h4>
                                                    <p class="review-date">Reviewed on <?php echo date('F j, Y', strtotime($item['review_date'])); ?></p>
                                                </div>
                                                
                                                <div class="existing-review">
                                                    <div class="existing-rating">
                                                        <label class="rating-label">Your Rating:</label>
                                                        <div class="star-display">
                                                            <?php 
                                                            $rating = (int)$item['existing_rating'];
                                                            for ($i = 1; $i <= 5; $i++) {
                                                                echo $i <= $rating ? '★' : '☆';
                                                            }
                                                            ?>
                                                            <span class="rating-value"><?php echo $item['existing_rating']; ?>/5</span>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if (!empty($item['existing_comment'])): ?>
                                                        <div class="existing-comment">
                                                            <label class="comment-label">Your Review:</label>
                                                            <div class="comment-display">
                                                                <?php echo nl2br(htmlspecialchars($item['existing_comment'])); ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <!-- Review Form Section -->
                                            <div class="rating-section">
                                                <label class="rating-label">Your Rating:</label>
                                                <div class="star-rating" data-index="<?php echo $index; ?>">
                                                    <span class="star" data-rating="1">☆</span>
                                                    <span class="star" data-rating="2">☆</span>
                                                    <span class="star" data-rating="3">☆</span>
                                                    <span class="star" data-rating="4">☆</span>
                                                    <span class="star" data-rating="5">☆</span>
                                                </div>
                                                <input type="hidden" name="rating[]" value="0" id="rating-<?php echo $index; ?>">
                                                <input type="hidden" name="produk_id[]" value="<?php echo $item['produk_id']; ?>">
                                            </div>

                                            <div class="comment-section">
                                                <label for="comment-<?php echo $index; ?>" class="comment-label">Your Review:</label>
                                                <textarea 
                                                    name="comment[]" 
                                                    id="comment-<?php echo $index; ?>" 
                                                    class="comment-textarea"
                                                    placeholder="Share your thoughts about this game... (optional)"
                                                    rows="3"></textarea>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn-secondary" onclick="window.location.href='profile.php#orders'">Cancel</button>
                                <?php 
                                // Check if there are any unreviewed games
                                $has_unreviewed = false;
                                foreach ($order_details as $item) {
                                    if (!$item['existing_review_id']) {
                                        $has_unreviewed = true;
                                        break;
                                    }
                                }
                                ?>
                                
                                <?php if ($has_unreviewed): ?>
                                    <button type="submit" class="btn-primary">Submit Reviews</button>
                                <?php else: ?>
                                    <div class="all-reviewed-message">
                                        <span>✅ You've already reviewed this game</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </form>

                    <?php else: ?>
                        <!-- Order Selection -->
                        <div class="order-selection">
                            <?php if (!empty($recent_orders)): ?>
                                <div class="orders-list">
                                    <h2>Select an order to review:</h2>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <div class="order-option">
                                            <div class="order-details">
                                                <h3>Order #<?php echo $order['order_id']; ?></h3>
                                                <p><?php echo date('F j, Y', strtotime($order['tanggal_pesanan'])); ?></p>
                                                <p><?php echo $order['item_count']; ?> item(s) • $<?php echo number_format($order['total_harga'], 2); ?></p>
                                            </div>
                                            <a href="reviewrate.php?order_id=<?php echo $order['order_id']; ?>" class="review-order-btn">
                                                Write Review
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-message">
                                    <div class="empty-content">
                                        <h3>No orders to review</h3>
                                        <p>You need to purchase games before you can leave reviews!</p>
                                        <a href="semua.php" class="empty-action-btn">Browse Games</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
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
        // Star rating functionality
        document.querySelectorAll('.star-rating').forEach(ratingContainer => {
            const stars = ratingContainer.querySelectorAll('.star');
            const index = ratingContainer.dataset.index;
            const hiddenInput = document.getElementById(`rating-${index}`);
            
            stars.forEach((star, starIndex) => {
                star.addEventListener('click', () => {
                    const rating = starIndex + 1;
                    hiddenInput.value = rating;
                    
                    // Update star display
                    stars.forEach((s, i) => {
                        s.textContent = i < rating ? '★' : '☆';
                        s.classList.toggle('active', i < rating);
                    });
                });
                
                star.addEventListener('mouseover', () => {
                    stars.forEach((s, i) => {
                        s.textContent = i <= starIndex ? '★' : '☆';
                    });
                });
            });
            
            ratingContainer.addEventListener('mouseleave', () => {
                const currentRating = parseInt(hiddenInput.value) || 0;
                stars.forEach((s, i) => {
                    s.textContent = i < currentRating ? '★' : '☆';
                });
            });
        });

        // Form submission
        document.getElementById('review-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('submit_reviews', 'true');
            
            // Validate that at least one rating is given
            const ratings = formData.getAll('rating[]');
            const hasValidRating = ratings.some(rating => parseInt(rating) > 0);
            
            if (!hasValidRating) {
                showNotification('Please pick the rating stars first!', 'error');
                return;
            }
            
            fetch('reviewrate.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'profile.php#orders';
                    }, 2000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(() => {
                showNotification('Network error occurred', 'error');
            });
        });

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
    </script>

</body>

</html>
