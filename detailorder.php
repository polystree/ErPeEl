<?php

require "session.php";
require "koneksi.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['loginbtn']) || $_SESSION['loginbtn'] == false) {
  header("Location: login.php");
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

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

$query = "
    SELECT 
        o.id AS order_id,
        o.created_at AS tanggal_pesanan,
        oi.quantity AS jumlah,
        oi.price AS harga_per_unit,
        p.nama AS nama_produk,
        p.foto AS gambar_produk,
        o.total_amount AS total_harga,
        o.ongkir AS ongkos_kirim,
        u.username AS user_name
    FROM 
        orders o
    JOIN 
        order_items oi ON o.id = oi.order_id
    JOIN 
        produk p ON oi.produk_id = p.id
    JOIN
        users u ON o.user_id = u.id
    WHERE 
        o.id = $order_id
";

$result = mysqli_query($con, $query);

$order_details = [];
if ($result && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $order_details[] = $row;
  }
} else {
  echo "Pesanan tidak ditemukan.";
}

if (isset($_POST['cancel_order'])) {
  $cancel_query = "DELETE FROM orders WHERE id = '$order_id' AND user_id = '$user_id'";
  $cancel_result = mysqli_query($con, $cancel_query);

  if ($cancel_result) {
    header("Location: dashboard.php"); 
  } else {
    echo "Gagal membatalkan pesanan.";
  }
}

$query_status = "SELECT status_order FROM orders WHERE id = '$order_id' AND user_id = '$user_id'";
$result_status = mysqli_query($con, $query_status);

if ($result_status && mysqli_num_rows($result_status) > 0) {
  $row_status = mysqli_fetch_assoc($result_status);
  $status_orders = $row_status['status_order'];
} else {
  $status_orders = null; 
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Order</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="detailorder.css">
</head>

<body>
  <nav class="navbar">
    <div class="upper-nav">
      <div class="logo">
        <a href="dashboard.php"> <img src="image/logo white.png" alt="Logo"> </a>
      </div>

      <div class="menu">
        <div class="dropdown">

          <a href="semua.php" class="menu-item dropdown-toggle">Semua Buku</a>
        </div>
        <div class="dropdown">
          <a href="#" class="menu-item dropdown-toggle">Kategori</a>
          <div class="dropdown-menu">
            <div class="dropdown-section">
              <h4>Fiksi</a></h4>
              <ul>
                <li><a href="kategori.php?kategori=1">Novel</a></li>
                <li><a href="kategori.php?kategori=2">Cerita Pendek</a></li>
                <li><a href="kategori.php?kategori=3">Komik</a></li>
              </ul>
            </div>
            <div class="dropdown-section">
              <h4>Non-Fiksi</a></h4>
              <ul>
                <li><a href="kategori.php?kategori=4">Biografi</a></li>
                <li><a href="kategori.php?kategori=5">Sejarah</a></li>
                <li><a href="kategori.php?kategori=6">Psikologi</a></li>
              </ul>
            </div>
            <div class="dropdown-section">
              <h4>Sains & Teknologi</a></h4>
              <ul>
                <li><a href="kategori.php?kategori=7">Ilmu Komputer</a></li>
                <li><a href="kategori.php?kategori=8">Fisika</a></li>
                <li><a href="kategori.php?kategori=9">Biologi</a></li>
              </ul>
            </div>
            <div class="dropdown-section">
              <h4>Bisnis & Ekonomi</a></h4>
              <ul>
                <li><a href="kategori.php?kategori=10">Kewirausahaan</a></li>
                <li><a href="kategori.php?kategori=11">Investasi</a></li>
                <li><a href="kategori.php?kategori=12">Manajemen</a></li>
              </ul>
            </div>
            <div class="dropdown-section">
              <h4>
                <a">Pengembangan Diri</a>
              </h4>
              <ul>
                <li><a href="kategori.php?kategori=Motivasi">Motivasi</a></li>
                <li><a href="kategori.php?kategori=Kesehatan">Kesehatan</a></li>
                <li><a href="kategori.php?kategori=Spiritual">Spiritual</a></li>
              </ul>
            </div>
          </div>
        </div>

      </div>

      <div class="search-bar">
        <form method="GET" action="search.php" style="width: 100%; display: flex; align-items: center;">
          <input type="text" name="query" placeholder="Cari Buku" class="search-input">
          <button type="submit" class="search-icon">
            <img src="image/search-btn.svg" class="search-img" alt="Search Icon">
          </button>
        </form>
      </div>

      <div class="nav-icons">
        <div class="nav-icon">
          <a href="cart.php">
            <img src="image/cart-btn.svg" class="icon-img white-icon" alt="Cart Icon">
            <img src="image/cart-btno.svg" class="icon-img orange-icon" alt="Cart Icon">
          </a>
        </div>

        <div class="nav-icon profile">
          <a href="profile.php">
            <img src="image/profile white.svg" class="icon-img white-icon" alt="Profile Icon">
            <img src="image/profile orange.svg" class="icon-img orange-icon" alt="Profile Icon">
          </a>
        </div>
      </div>
    </div>
  </nav>

  <div class="order-container">
    <div class="order-header">
      <h2>Detail Pesanan</h2>

      <form action="" method="POST">
        <?php if ($status_orders != 4) { ?>
          <button type="submit" name="cancel_order" class="button delete">Cancel Order</button>
        <?php } ?>
      </form>
    </div>
    <?php if (!empty($order_details)) { ?>
      <div class="order-info">
        <p>Tanggal Pembelian: <span
            class="purchase-date"><?php echo date("d F Y, H:i", strtotime($order_details[0]['tanggal_pesanan'])); ?></span>
        </p>
      </div>
      <div class="order-product">
        <div class="product-header">
          <h3>Detail Produk</h3>
        </div>
        <?php foreach ($order_details as $detail) { ?>
          <div class="product-detail">
            <div class="product-image">
              <img src="image/<?php echo $detail['gambar_produk']; ?>" alt="Product Image">
            </div>
            <div class="product-info">
              <p><?php echo $detail['nama_produk']; ?></p>
              <p><?php echo $detail['jumlah']; ?>x Rp<?php echo number_format($detail['harga_per_unit'], 0, ',', '.'); ?>
              </p>
            </div>
            <div class="product-price">
              <p>Total Harga</p>
              <p class="total-price">
                Rp<?php echo number_format($detail['jumlah'] * $detail['harga_per_unit'], 0, ',', '.'); ?></p>
            </div>
          </div>
        <?php } ?>
      </div>
      <div class="shipping-info">
        <h3>Info Pengiriman</h3>
        <div class="shipping-details">
          <div class="shipping-row">
            <strong>Nama:</strong>
            <span><?php echo $order_details[0]['user_name']; ?></span>
          </div>

          <div class="shipping-row">
            <strong>Alamat:</strong>
            <div class="address-details">
              <p>Alamat pengiriman dikonfirmasi melalui WhatsApp</p>
            </div>
          </div>
        </div>
      </div>
      <div class="payment-details">
        <h3>Rincian Pembayaran</h3>
        <div class="payment-row">
          <span>Total Harga Barang (<?php echo count($order_details); ?> Barang):</span>
          <span>Rp<?php
          $total_harga_barang = 0;
          foreach ($order_details as $detail) {
            $total_harga_barang += ($detail['jumlah'] * $detail['harga_per_unit']);
          }
          echo number_format($total_harga_barang, 0, ',', '.');
          ?></span>
        </div>
        <div class="payment-row">
          <span>Total Ongkos Kirim:</span>
          <span>Rp<?php echo number_format($order_details[0]['ongkos_kirim'], 0, ',', '.'); ?></span>
        </div>
        <div class="payment-row">
          <span>Total Asuransi Pengiriman:</span>
          <span>Rp4.000</span>
        </div>
        <div class="payment-row">
          <span>Biaya Jasa Aplikasi:</span>
          <span>Rp1.000</span>
        </div>
        <div class="payment-row">
          <span>Total Harga:</span>
          <span>Rp<?php echo number_format($order_details[0]['total_harga'], 0, ',', '.'); ?></span>
        </div>
      </div>
    <?php } else { ?>
      <p>Pesanan tidak ditemukan.</p>
    <?php } ?>
  </div>

  <footer>
    <p>&copy; Kelompok 1 | PPW | <a href="#" class="privacy-policy">Privacy Policy</a></p>
  </footer>
</body>

</html>