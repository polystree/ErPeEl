<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "koneksi.php";
require "session.php";

$message = "";
$user_id = $_SESSION['user_id'];

$select_profile = mysqli_query($con, "SELECT username, email, alamat, foto FROM users WHERE id = '$user_id'") or die(mysqli_error($con));
if (mysqli_num_rows($select_profile) > 0) {
    $fetch_profile = mysqli_fetch_assoc($select_profile);
    $username = $fetch_profile['username'];
    $email = $fetch_profile['email'];
    $alamat = $fetch_profile['alamat'];
    $foto = $fetch_profile['foto'];
} else {
    $username = $email = $alamat = $foto = "";
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
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    $status_map = [
        'Dikemas' => 1,
        'Dikirim' => 2,
        'Ulasan' => 3,
        'Selesai' => 4
    ];

    if (isset($status_map[$new_status])) {
        $status_value = $status_map[$new_status];
        $stmt = $con->prepare("UPDATE orders SET status_order = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ii", $status_value, $order_id);

        if ($stmt->execute()) {
        } 
        $stmt->close();
    } 
}

$query = "SELECT o.id AS order_id, o.created_at AS tanggal_pesanan, oi.quantity AS jumlah, oi.price AS harga_per_unit,
                 p.nama AS nama_produk, p.foto AS gambar_produk, o.total_amount AS total_harga, o.ongkir AS ongkos_kirim,
                 u.username AS user_name, u.alamat AS user_alamat, o.status_order, u.id AS user_id
          FROM orders o
          JOIN order_items oi ON o.id = oi.order_id
          JOIN produk p ON oi.produk_id = p.id
          JOIN users u ON o.user_id = u.id
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
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" href="image/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="order.css">
</head>

<body>
    <nav class="navbar">
        <div class="upper-nav">
            <a href="admin.php" class="nav-link">Kelola Buku</a>
            <div class="logo">
                <a href="#"> <img src="image/logo white.png" alt="Logo"> </a>
            </div>
            <a href="order.php" class="nav-link">Order Manajemen</a>
        </div>
    </nav>

    <div class="order-container">
        <?php if (!empty($order_details)) { ?>
            <?php foreach ($order_details as $order_id => $details) { ?>
                <div class="order-box"> 
                    <div class="order-header">
                        <h2 id="order-status">
                            <?php
                            switch ($details[0]['status_order']) {
                                case 1:
                                    echo "Pesanan Dikemas";
                                    break;
                                case 2:
                                    echo "Pesanan Dikirim";
                                    break;
                                case 3:
                                    echo "Menunggu Ulasan";
                                    break;
                                case 4:
                                    echo "Selesai";
                                    break;
                                default:
                                    echo "Status Tidak Dikenal";
                                    break;
                            }
                            ?>
                        </h2>
                        <div class="button-group">
                            <?php
                            if ($details[0]['status_order'] <= 3) {
                            ?>
                                <div class="status-dropdown">
                                    <button class="button dropdown-toggle"
                                        id="dropdown-toggle-<?php echo $order_id; ?>">Status</button>
                                    <div class="dropdown-menu" id="dropdown-menu-<?php echo $order_id; ?>">
                                        <form action="" method="POST">
                                            <button type="submit" name="status" value="Dikemas"
                                                class="dropdown-item">Dikemas</button>
                                            <button type="submit" name="status" value="Dikirim"
                                                class="dropdown-item">Dikirim</button>
                                            <button type="submit" name="status" value="Ulasan"
                                                class="dropdown-item">Menunggu Ulasan</button>
                                            <button type="submit" name="status" value="Selesai"
                                                class="dropdown-item">Selesai</button>
                                            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                        </form>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                            <form action="" method="POST">
                                <?php if ($details[0]['status_order'] < 3) { ?>
                                    <button type="submit" name="cancel_order" class="button delete">Batalkan Pesanan</button>
                                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                <?php } ?>
                            </form>
                        </div>
                    </div>

                    <div class="order-info">
                        <p>Tanggal Pembelian: <span
                                class="purchase-date"><?php echo date("d F Y, H:i", strtotime($details[0]['tanggal_pesanan'])); ?></span>
                        </p>
                    </div>

                    <div class="order-product">
                        <?php foreach ($details as $detail) { ?>
                            <div class="product-detail">
                                <div class="product-image">
                                    <img src="image/<?php echo $detail['gambar_produk']; ?>" alt="Gambar Produk">
                                </div>
                                <div class="product-info">
                                    <p><?php echo $detail['nama_produk']; ?></p>
                                    <p><?php echo $detail['jumlah']; ?>x
                                        Rp<?php echo number_format($detail['harga_per_unit'], 0, ',', '.'); ?></p>
                                </div>
                                <div class="product-price">
                                    <p>Total Harga</p>
                                    <p class="total-price">
                                        Rp<?php echo number_format($detail['jumlah'] * $detail['harga_per_unit'], 0, ',', '.'); ?>
                                    </p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="shipping-info">
                        <h3>Informasi Pengiriman</h3>
                        <div class="shipping-details">
                            <div class="shipping-row">
                                <strong>Nama:</strong> <span><strong><?php echo $details[0]['user_name']; ?></strong></span>
                            </div>
                            <div class="shipping-row">
                                <strong>Alamat:</strong>
                                <div class="address-details">
                                    <p><?php echo nl2br($details[0]['user_alamat']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="payment-details">
                        <h3>Detail Pembayaran</h3>
                        <div class="payment-row">
                            <span>Total Harga (<?php echo count($details); ?> Barang):</span>
                            <span>Rp<?php
                            $total_item_price = 0;
                            foreach ($details as $detail) {
                                $total_item_price += ($detail['jumlah'] * $detail['harga_per_unit']);
                            }
                            echo number_format($total_item_price, 0, ',', '.');
                            ?></span>
                        </div>
                        <div class="payment-row">
                            <span>Biaya Pengiriman:</span>
                            <span>Rp<?php echo number_format($details[0]['ongkos_kirim'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="payment-row">
                            <span>Asuransi Pengiriman:</span>
                            <span>Rp4,000</span>
                        </div>
                        <div class="payment-row">
                            <span>Biaya Layanan Aplikasi:</span>
                            <span>Rp1,000</span>
                        </div>
                        <div class="payment-total">
                            <span>Total Belanja:</span>
                            <span>Rp<?php echo number_format($details[0]['total_harga'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div> 
            <?php } ?>
        <?php } else { ?>
            <p>Tidak ada pesanan ditemukan.</p>
        <?php } ?>
    </div>

    <footer>
        <p>&copy; Kelompok 1 | PPW | <a href="#" class="privacy-policy">Kebijakan Privasi</a></p>
    </footer>

    <script>
        document.addEventListener('click', function (e) {
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            dropdownToggles.forEach(function (toggle) {
                const dropdownId = toggle.id.replace('dropdown-toggle-', '');
                const dropdownMenu = document.getElementById('dropdown-menu-' + dropdownId);

                if (e.target === toggle) {
                    dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
                } else {
                    dropdownMenu.style.display = 'none';
                }
            });
        });
    </script>

</body>

</html>
