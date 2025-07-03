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
                 p.nama AS nama_produk, p.foto AS gambar_produk, o.total AS total_harga, p.harga AS base_price,
                 u.username AS user_name, u.id AS user_id, p.pengembang AS pengembang
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
                <a href="admin.php" aria-label="Back to home">Vault</a>
            </div>
            
            <button class="mobile-menu-toggle" aria-label="Toggle mobile menu" aria-expanded="false" onclick="toggleMobileMenu()">
                <span class="hamburger-icon">☰</span>
            </button>

            <div class="menu" role="menubar" id="mobile-menu">
                <a href="admin.php" class="menu-item" role="menuitem">Manage Games</a>
                <a href="order.php" class="menu-item" role="menuitem">Order Management</a>
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
                            </div>
                            <div class="order-actions">
                                <span class="order-status status-1">
                                    Completed
                                </span>
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
                                        <div class="product-pengembang">
                                            <span class="unit-pengembang">by <?php echo htmlspecialchars($detail['pengembang'] ?? ''); ?></span>
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
                        <h4 class="section-title">Order Information</h4>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" width="800px" height="800px" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M4 7.00005L10.2 11.65C11.2667 12.45 12.7333 12.45 13.8 11.65L20 7" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    Email Address
                                </span>
                                <span class="shipping-value">
                                    <?php
                                    // Fetch email for this user (if not already available)
                                    $user_email = '';
                                    $user_id_for_order = $details[0]['user_id'];
                                    $email_query = mysqli_query($con, "SELECT email FROM users WHERE id = '$user_id_for_order' LIMIT 1");
                                    if ($email_query && mysqli_num_rows($email_query) > 0) {
                                        $user_email_row = mysqli_fetch_assoc($email_query);
                                        $user_email = $user_email_row['email'];
                                    }
                                    echo htmlspecialchars($user_email);
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="payment-section">
                        <h4 class="section-title">Payment Summary</h4>
                        <div class="payment-card">
                            <div class="payment-item">
                                <span>Subtotal (<?php echo count($details); ?> items) + 2.99 Fee</span>
                                <span>
                                    $<?php
                                    $total_item_price = 0;
                                    foreach ($details as $detail) {
                                        $total_item_price += $detail['harga_per_unit'];
                                    }
                                    echo number_format($total_item_price + 2.99, 2);
                                    ?>
                                </span>
                            </div>
                            <div class="payment-item">
                                <span>Tax (11%)</span>
                                <span>
                                    $<?php
                                    $tax = ($total_item_price + 2.99) * 0.11;
                                    echo number_format($tax, 2);
                                    ?>
                                </span>
                            </div>
                            <div class="payment-total">
                                <span>Total Amount</span>
                                <span>
                                    $<?php
                                    $total_amount = ($total_item_price + 2.99) * 1.11;
                                    echo number_format($total_amount, 2);
                                    ?>
                                </span>
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
