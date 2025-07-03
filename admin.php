<?php
require "koneksi.php";
require "session.php";
require "image_helper.php";

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

$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($con, "SELECT foto FROM `users` WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);
$foto = $user_data ? $user_data['foto'] : null;
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
    <div class="main-content">
        <!-- Admin Header -->
        <div class="admin-header">
            <div class="admin-info">
                <div class="admin-avatar">
                    <?php if ($foto): ?>
                        <img src="<?php echo $foto; ?>" class="icon-img profile-avatar" alt="" width="44" height="44" style="border-radius: 50%; object-fit: cover; filter: none; width: 44px; height: 44px;">
                    <?php else: ?>
                        <img src="image/profile white.svg" class="icon-img" alt="" width="20" height="20">
                    <?php endif; ?>
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
        <h1 class="admin-title">
            <div> Manage Games 
            <span class="games-count"><?php echo $jumlahproduk; ?></span> </div>
            <button type="button" class="add-product-btn">
                <a href="tambah-game.php">Add New Game</a>
            </button>
        </h1>

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
                        <th>Sold</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($jumlahproduk == 0): ?>
                        <tr>
                            <td colspan="7" class="empty-state">
                                <div class="empty-state-icon">🎮</div>
                                <p>No games available.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while($data = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php 
                                // Debug: Show what's in the foto field
                                $foto_value = $data['foto'];
                                $image_src = getImageSrc($foto_value);
                                // echo "<!-- Debug: foto field = " . htmlspecialchars($foto_value) . ", image_src = " . htmlspecialchars($image_src) . " -->";
                                ?>
                                <img class="product-image" 
                                     src="<?php echo $image_src; ?>" 
                                     alt="<?php echo htmlspecialchars($data['nama']); ?>" 
                                     onerror="this.src='image/avatar.png'" />
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
                                    if (!empty($data['harga_diskon']) && $data['harga_diskon'] !== null) {
                                        echo number_format((float)$data['harga_diskon'], 2);
                                    } else {
                                        echo number_format((float)($data['harga'] ?? 0), 2);
                                    }
                                ?>
                            </td>
                            <td class="sold-count">
                                <?php echo number_format((int)($data['sold'] ?? 0)); ?>
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
    </div>

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