<?php
require "koneksi.php";
require "session.php";

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Handle logout
if (isset($_POST['logout-btn'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Get products with category information
$sql = "SELECT p.*, k.nama as kategori_nama 
        FROM produk p 
        LEFT JOIN kategori k ON p.kategori_id = k.id 
        ORDER BY p.id DESC";
$result = $con->query($sql);
$jumlahproduk = $result->num_rows;

// Get user info
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $con->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Vault Digital Store</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/favicon.png">
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
                <a href="admin.php" class="menu-item" role="menuitem">Manage Games</a>
                <a href="order.php" class="menu-item" role="menuitem">Order Management</a>
            </div>

            <div class="search-bar" role="search">
                <form method="GET" action="search.php">
                    <input type="text" name="query" id="search-input" placeholder="Search Games" class="search-input" aria-label="Enter game search keywords">
                    <button type="submit" class="search-icon" aria-label="Start search">
                        <img src="image/search-btn.svg" class="search-img" alt="" width="16" height="16">
                    </button>
                </form>
            </div>

            <div class="nav-icons">
                <div class="nav-icon">
                    <a href="cart.php" aria-label="View shopping cart">
                        <img src="image/cart-btn.svg" class="icon-img" alt="" width="20" height="20">
                    </a>
                </div>

                <div class="nav-icon profile">
                    <a href="profile.php" aria-label="View user profile">
                        <?php if (!empty($user_data['foto']) && file_exists("image/" . $user_data['foto'])): ?>
                            <img src="image/<?php echo htmlspecialchars($user_data['foto']); ?>" class="icon-img profile-avatar" alt="" width="44" height="44" style="border-radius: 50%; object-fit: cover; filter: none; width: 44px; height: 44px;">
                        <?php else: ?>
                            <img src="image/profile white.svg" class="icon-img" alt="" width="20" height="20">
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Admin Header -->
        <div class="admin-header">
            <div class="admin-info">
                <div class="admin-avatar">
                    <?php echo strtoupper(substr($user_data['username'] ?? 'A', 0, 1)); ?>
                </div>
                <div class="admin-details">
                    <h2><?php echo htmlspecialchars($user_data['username'] ?? 'Admin'); ?></h2>
                    <p>Administrator Dashboard</p>
                </div>
            </div>
            <form method="post" style="display: inline;">
                <button type="submit" class="logout-btn" name="logout-btn">
                    Logout
                </button>
            </form>
        </div>

        <!-- Page Title -->
        <h1 class="admin-title">Manage Games <span class="games-count"><?php echo $jumlahproduk; ?></span></h1>

        <!-- Products Table -->
        <div class="products-container">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Game Name</th>
                        <th>Developer</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($jumlahproduk == 0): ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <div class="empty-state-icon">🎮</div>
                                <p>No games available.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while($data = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img class="product-image" 
                                     src="image/<?php echo htmlspecialchars($data['foto']); ?>" 
                                     alt="<?php echo htmlspecialchars($data['nama']); ?>" />
                            </td>
                            <td class="product-name">
                                <?php echo htmlspecialchars($data['nama']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($data['pengembang']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($data['kategori_nama'] ?? 'None'); ?>
                            </td>
                            <td class="product-price">
                                $<?php 
                                    if (!empty($data['harga_diskon'])) {
                                        echo number_format($data['harga_diskon'], 2);
                                    } else {
                                        echo number_format($data['harga'], 2);
                                    }
                                ?>
                            </td>
                            <td>
                                <a href="edit-game.php?id=<?php echo $data['id']; ?>" class="edit-btn">
                                    Edit
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Product Button -->
        <div class="add-product-section">
            <button type="button" class="add-product-btn">
                <a href="tambah-game.php">Add New Game</a>
            </button>
        </div>
    </div>
    
    <!-- Footer -->
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
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            if (menu.style.display === 'flex') {
                menu.style.display = 'none';
                toggle.setAttribute('aria-expanded', 'false');
            } else {
                menu.style.display = 'flex';
                toggle.setAttribute('aria-expanded', 'true');
            }
        }
    </script>
</body>
</html>