<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "koneksi.php";
require "session.php";

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$message = "";
$user_id = $_SESSION['user_id'];

$select_profile = mysqli_query($con, "SELECT username, email, foto FROM users WHERE id = '$user_id'") or die(mysqli_error($con));
if (mysqli_num_rows($select_profile) > 0) {
    $fetch_profile = mysqli_fetch_assoc($select_profile);
    $username = $fetch_profile['username'];
    $email = $fetch_profile['email'];
    $foto = $fetch_profile['foto'];
} else {
    $username = $email = $foto = "";
}

if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    $cancel_query = "DELETE FROM orders WHERE id = '$order_id' AND user_id = '$user_id'";
    $cancel_result = mysqli_query($con, $cancel_query);
    if ($cancel_result) {
        header("Location: order.php");
        exit();
    } else {
        echo "Failed to cancel the order.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status'])) {
    // Status update functionality disabled for basic orders table
    // Would need to add status_order column to orders table to enable this feature
}

$query = "SELECT o.id AS order_id, o.created_at AS tanggal_pesanan, COUNT(oi.produk_id) AS jumlah, oi.price AS harga_per_unit,
                 p.nama AS nama_produk, p.foto AS gambar_produk, o.total AS total_harga,
                 u.username AS user_name, u.id AS user_id
          FROM orders o
          JOIN order_items oi ON o.id = oi.order_id
          JOIN produk p ON oi.produk_id = p.id
          JOIN users u ON o.user_id = u.id
          GROUP BY o.id, oi.produk_id, p.nama, p.foto, u.username, u.id, o.created_at, o.total, oi.price
          ORDER BY o.created_at DESC";
$result = mysqli_query($con, $query);
$order_details = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $order_details[$row['order_id']][] = $row; 
    }
} else {
    echo "No orders found.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Vault Digital Store</title>
    <link rel="stylesheet" href="order.css">
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
                <a href="order.php" class="menu-item active" role="menuitem">Order Management</a>
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
                    </a>
                </div>

                <div class="nav-icon profile">
                    <a href="profile.php" aria-label="View user profile">
                        <?php if (!empty($foto) && file_exists("image/" . $foto)): ?>
                            <img src="image/<?php echo htmlspecialchars($foto); ?>" class="icon-img profile-avatar" alt="" width="44" height="44" style="border-radius: 50%; object-fit: cover; filter: none; width: 44px; height: 44px;">
                        <?php else: ?>
                            <img src="image/profile white.svg" class="icon-img" alt="" width="20" height="20">
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Order Management</h1>
            <p class="page-subtitle">Manage all customer orders and delivery status</p>
        </div>

        <div class="order-container">
            <?php if (!empty($order_details)) { ?>
                <?php foreach ($order_details as $order_id => $details) { ?>
                    <div class="order-card"> 
                        <div class="order-header">
                            <div class="order-status-info">
                                <h3 class="order-id">Order #<?php echo $order_id; ?></h3>
                                <span class="order-status status-1">
                                    Processing
                                </span>
                            </div>
                            <div class="order-actions">
                                <!-- Status update functionality disabled for basic orders table -->
                                <span class="text-muted">Status management requires database upgrade</span>
                            </div>
                        </div>

                        <div class="order-info">
                            <div class="order-meta">
                                <span class="order-date">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M19,3H18V1H16V3H8V1H6V3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5A2,2 0 0,0 19,3M19,19H5V8H19V19Z"/>
                                    </svg>
                                    <?php echo date("M d, Y • H:i", strtotime($details[0]['tanggal_pesanan'])); ?>
                                </span>
                                <span class="customer-name">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z"/>
                                    </svg>
                                    <?php echo htmlspecialchars($details[0]['user_name']); ?>
                                </span>
                            </div>
                        </div>

                    <div class="order-products">
                        <h4 class="section-title">Order Items</h4>
                        <?php foreach ($details as $detail) { ?>
                            <div class="product-item">
                                <div class="product-image">
                                    <img src="image/<?php echo htmlspecialchars($detail['gambar_produk']); ?>" alt="<?php echo htmlspecialchars($detail['nama_produk']); ?>">
                                </div>
                                <div class="product-details">
                                    <h5 class="product-name"><?php echo htmlspecialchars($detail['nama_produk']); ?></h5>
                                    <div class="product-quantity">
                                        <span class="qty-label">Quantity:</span>
                                        <span class="qty-value"><?php echo $detail['jumlah']; ?>x</span>
                                        <span class="unit-price">$<?php echo number_format($detail['harga_per_unit'], 2); ?></span>
                                    </div>
                                </div>
                                <div class="product-total">
                                    <span class="total-label">Total</span>
                                    <span class="total-amount">$<?php echo number_format($detail['jumlah'] * $detail['harga_per_unit'], 2); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="shipping-section">
                        <h4 class="section-title">Shipping Information</h4>
                        <div class="shipping-card">
                            <div class="shipping-item">
                                <span class="shipping-label">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z"/>
                                    </svg>
                                    Customer Name
                                </span>
                                <span class="shipping-value"><?php echo htmlspecialchars($details[0]['user_name']); ?></span>
                            </div>
                            <div class="shipping-item">
                                <span class="shipping-label">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z"/>
                                    </svg>
                                    Delivery Address
                                </span>
                                <span class="shipping-value">Address information requires profile update</span>
                            </div>
                        </div>
                    </div>

                    <div class="payment-section">
                        <h4 class="section-title">Payment Summary</h4>
                        <div class="payment-card">
                            <div class="payment-item">
                                <span>Subtotal (<?php echo count($details); ?> items)</span>
                                <span>$<?php
                                $total_item_price = 0;
                                foreach ($details as $detail) {
                                    $total_item_price += ($detail['jumlah'] * $detail['harga_per_unit']);
                                }
                                echo number_format($total_item_price, 2);
                                ?></span>
                            </div>
                            <div class="payment-item">
                                <span>Processing Fee</span>
                                <span>$2.99</span>
                            </div>
                            <div class="payment-total">
                                <span>Total Amount</span>
                                <span>$<?php echo number_format($details[0]['total_harga'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div> 
            <?php } ?>
        <?php } else { ?>
            <div class="empty-state">
                <div class="empty-state-icon">📦</div>
                <h3>No Orders Found</h3>
                <p>There are currently no orders to manage.</p>
            </div>
        <?php } ?>
        </div>
    </main>

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

        document.addEventListener('click', function (e) {
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            dropdownToggles.forEach(function (toggle) {
                const dropdownId = toggle.id.replace('dropdown-toggle-', '');
                const dropdownMenu = document.getElementById('dropdown-menu-' + dropdownId);

                if (e.target === toggle || toggle.contains(e.target)) {
                    dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
                } else {
                    dropdownMenu.style.display = 'none';
                }
            });
        });
    </script>

</body>

</html>
