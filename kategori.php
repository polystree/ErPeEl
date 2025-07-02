<?php
require "session.php";
require "koneksi.php";

if (isset($_GET['kategori'])) {
    $kategori_id = intval($_GET['kategori']); 

    $select_produk = mysqli_query($con, "SELECT * FROM `produk` WHERE `kategori_id` = $kategori_id") or die('Query failed');
} else {
    echo "<p>Kategori tidak ditemukan.</p>";
}

if (!isset($_SESSION['loginbtn']) || $_SESSION['loginbtn'] == false) {
    header("Location: login.php");
    exit();
}
$message = "";
$user_id = $_SESSION['user_id'];
if (isset($_POST['produk_id'])) {
    $produk_id = intval($_POST['produk_id']); 
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
$wishlist_ids = [];
$get_wishlist = mysqli_query($con, "SELECT produk_id FROM wishlist WHERE user_id = '$user_id'");
while ($row = mysqli_fetch_assoc($get_wishlist)) {
    $wishlist_ids[] = $row['produk_id'];
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
    http_response_code(200); // OK
    echo "Wishlist updated successfully.";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Games - Vault Digital Store</title>
    <link rel="icon" type="image/png" href="image/favicon.png">
    <link rel="stylesheet" href="kategori.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Navigation -->
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
                                <li><a href="kategori.php?kategori=1" role="menuitem">Shooter</a></li>
                                <li><a href="kategori.php?kategori=2" role="menuitem">Fighting</a></li>
                                <li><a href="kategori.php?kategori=3" role="menuitem">Platformer</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>Adventure</h4>
                            <ul role="none">
                                <li><a href="kategori.php?kategori=4" role="menuitem">RPG</a></li>
                                <li><a href="kategori.php?kategori=5" role="menuitem">Open World</a></li>
                                <li><a href="kategori.php?kategori=6" role="menuitem">Survival</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>Strategy</h4>
                            <ul role="none">
                                <li><a href="kategori.php?kategori=7" role="menuitem">RTS</a></li>
                                <li><a href="kategori.php?kategori=8" role="menuitem">Turn-Based</a></li>
                                <li><a href="kategori.php?kategori=9" role="menuitem">Tower Defense</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
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
                        <span class="cart-badge" id="cart-badge" style="display: none;">0</span>
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

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <?php
                if (isset($_GET['kategori'])) {
                    $kategori_id = intval($_GET['kategori']);
                    $kategori_query = mysqli_query($con, "SELECT `nama` FROM `kategori` WHERE `id` = $kategori_id") or die('Query failed');
                    if (mysqli_num_rows($kategori_query) > 0) {
                        $kategori = mysqli_fetch_assoc($kategori_query);
                        echo htmlspecialchars($kategori['nama']) . " Games";
                    } else {
                        echo "Category Not Found";
                    }
                } else {
                    echo "All Games";
                }
                ?>
            </h1>
            <p class="page-subtitle">Discover amazing digital games in this category</p>
        </div>

        <!-- Games Grid -->
        <div class="games-container">
            <?php
            if (mysqli_num_rows($select_produk) > 0) {
                while ($fetch_produk = mysqli_fetch_assoc($select_produk)) {
            ?>
                    <div class="game-card">
                        <div class="game-image">
                            <a href="detail.php?produk_id=<?php echo $fetch_produk['id']; ?>">
                                <img src="image/<?php echo htmlspecialchars($fetch_produk['foto']); ?>" 
                                     alt="<?php echo htmlspecialchars($fetch_produk['nama']); ?>" 
                                     class="game-cover">
                            </a>
                            <button type="button" class="wishlist-btn" 
                                    data-produk-id="<?php echo $fetch_produk['id']; ?>"
                                    data-in-wishlist="<?php echo in_array($fetch_produk['id'], $wishlist_ids) ? 'true' : 'false'; ?>"
                                    onclick="toggleWishlist(this);" 
                                    aria-label="Add to wishlist">
                                <svg class="heart-icon heart-empty" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                                     style="display:<?php echo in_array($fetch_produk['id'], $wishlist_ids) ? 'none' : 'block'; ?>;">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                                <svg class="heart-icon heart-filled" viewBox="0 0 24 24" fill="currentColor" 
                                     style="display:<?php echo in_array($fetch_produk['id'], $wishlist_ids) ? 'block' : 'none'; ?>;">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="game-info">
                            <h3 class="game-title">
                                <a href="detail.php?produk_id=<?php echo $fetch_produk['id']; ?>">
                                    <?php echo htmlspecialchars($fetch_produk['nama']); ?>
                                </a>
                            </h3>
                            <p class="game-developer"><?php echo htmlspecialchars($fetch_produk['pengembang'] ?? $fetch_produk['pengarang'] ?? 'Unknown Developer'); ?></p>
                            <div class="game-pricing">
                                <?php if (!empty($fetch_produk['harga_diskon'])): ?>
                                    <span class="original-price">$<?php echo number_format($fetch_produk['harga'], 2); ?></span>
                                    <span class="current-price">$<?php echo number_format($fetch_produk['harga_diskon'], 2); ?></span>
                                    <span class="discount-badge">
                                        -<?php echo round((($fetch_produk['harga'] - $fetch_produk['harga_diskon']) / $fetch_produk['harga']) * 100); ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="current-price">$<?php echo number_format($fetch_produk['harga'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
            <?php
                }
            } else {
            ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🎮</div>
                    <h3>No Games Found</h3>
                    <p>No games are available in this category at the moment.</p>
                    <a href="semua.php" class="btn-primary">Browse All Games</a>
                </div>
            <?php } ?>
        </div>
    </main>

    <div id="wishlist-notification" class="wishlist-notification">
        <img src="image/heart yellow.svg" class="heart-icon heart-yellow">
        <span class="wishlist-message">Berhasil dimasukkan ke Wishlist!</span>
    </div>

    <footer>
        <p>&copy; Kelompok 1 | PPW | <a href="#" class="privacy-policy">Privacy Policy</a></p>
    </footer>

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            menu.classList.toggle('active');
            const isExpanded = menu.classList.contains('active');
            toggle.setAttribute('aria-expanded', isExpanded);
        }

        // Initialize wishlist button states
        document.querySelectorAll(".wishlist-btn").forEach(button => {
            const produkId = button.dataset.produkId;
            const inWishlist = button.getAttribute("data-in-wishlist") === "true";

            const emptyIcon = button.querySelector(".heart-empty");
            const filledIcon = button.querySelector(".heart-filled");

            if (inWishlist) {
                emptyIcon.style.display = "none";
                filledIcon.style.display = "block";
            } else {
                emptyIcon.style.display = "block";
                filledIcon.style.display = "none";
            }
        });

        // Show wishlist notification
        function showWishlistNotification(message) {
            const notification = document.getElementById("wishlist-notification");
            if (notification) {
                const messageSpan = notification.querySelector('.wishlist-message');
                if (messageSpan) {
                    messageSpan.textContent = message;
                }
                notification.classList.add("show");

                setTimeout(() => {
                    notification.classList.remove("show");
                }, 3000);
            }
        }

        // Toggle wishlist
        function toggleWishlist(button) {
            const produkId = button.dataset.produkId;
            const inWishlist = button.getAttribute("data-in-wishlist") === "true";

            const emptyIcon = button.querySelector(".heart-empty");
            const filledIcon = button.querySelector(".heart-filled");

            fetch("dashboard.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `toggle_wishlist=true&produk_id=${produkId}`
            })
                .then(response => {
                    if (response.ok) {
                        button.setAttribute("data-in-wishlist", inWishlist ? "false" : "true");

                        if (inWishlist) {
                            emptyIcon.style.display = "block";
                            filledIcon.style.display = "none";
                            showWishlistNotification("Game removed from wishlist");
                        } else {
                            emptyIcon.style.display = "none";
                            filledIcon.style.display = "block";
                            showWishlistNotification("Game added to wishlist");
                        }
                    } else {
                        alert("Failed to update wishlist.");
                    }
                })
                .catch(() => {
                    alert("Network error occurred.");
                });
        }

        // Update cart badge
        function updateCartBadge() {
            fetch('ajax_get_cart.php')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('cart-badge');
                    if (badge) {
                        badge.remove();
                    }
                    
                    if (data.count > 0) {
                        const cartIcon = document.querySelector('.nav-icon a[href="cart.php"]');
                        const newBadge = document.createElement('span');
                        newBadge.className = 'cart-badge animate-badge';
                        newBadge.id = 'cart-badge';
                        newBadge.textContent = data.count;
                        cartIcon.appendChild(newBadge);
                        
                        setTimeout(() => {
                            newBadge.classList.remove('animate-badge');
                        }, 300);
                    }
                })
                .catch(error => console.error('Error updating cart badge:', error));
        }

        // Initialize cart badge on page load
        document.addEventListener('DOMContentLoaded', updateCartBadge);
    </script>



</body>


</html>