<?php
require "session.php";
require "koneksi.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

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
$select_cart_query = "
  SELECT c.*, p.nama, p.pengarang, p.foto, p.harga, p.harga_diskon, p.stok 
  FROM `cart` c 
  JOIN `produk` p ON c.produk_id = p.id 
  WHERE c.user_id = '$user_id'
";
$select_cart = mysqli_query($con, $select_cart_query);

if (!$select_cart) {
  die("Error: Query cart gagal. " . mysqli_error($con));
}

$cart_items = [];
$total_harga = 0;
$total_quantity = 0;

if (mysqli_num_rows($select_cart) > 0) {
  while ($row = mysqli_fetch_assoc($select_cart)) {
    $cart_items[] = $row;
  }
}

foreach ($cart_items as $item) {
  $harga = $item['harga_diskon'] !== null ? $item['harga_diskon'] : $item['harga'];
  $sub_total = $harga * $item['quantity'];
  $total_harga += $sub_total;
  $total_quantity += $item['quantity'];
}

if (isset($_POST['payment_form'])) {
  $ongkir = isset($_POST['ongkir']) ? (int)$_POST['ongkir'] : 0;

  $asuransi = 4000;
  $app_fee = 1000;
  $total_belanja = $total_harga + $ongkir + $asuransi + $app_fee;

  $query_order = "
    INSERT INTO orders (user_id, total_amount, ongkir, created_at, updated_at) 
    VALUES ('$user_id', '$total_belanja', '$ongkir', NOW(), NOW())
  ";

  if (!mysqli_query($con, $query_order)) {
    die("Error: Gagal membuat pesanan. " . mysqli_error($con));
  }

  $order_id = mysqli_insert_id($con);
  if (!$order_id) {
    die("Error: Gagal mendapatkan ID pesanan.");
  }

  foreach ($cart_items as $item) {
    $produk_id = $item['produk_id'];
    $quantity = $item['quantity'];
    $harga = $item['harga_diskon'] !== null ? $item['harga_diskon'] : $item['harga'];

    $query_order_item = "
      INSERT INTO order_items (order_id, produk_id, quantity, price) 
      VALUES ('$order_id', '$produk_id', '$quantity', '$harga')
    ";
    if (!mysqli_query($con, $query_order_item)) {
      die("Error: Gagal memasukkan detail pesanan. " . mysqli_error($con));
    }
  }

  $delete_cart = mysqli_query($con, "DELETE FROM cart WHERE user_id = '$user_id'");
  if (!$delete_cart) {
    die("Error: Gagal menghapus keranjang. " . mysqli_error($con));
  }

  header('Location: dashboard.php');
  exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pengiriman Page</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="image/favicon.png">
  <link rel="stylesheet" href="payment.css">
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
            <img src="image/cart-btn orange.svg" class="icon-img orange-icon" alt="Cart Icon">
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

  <div class="container">
    <div class="address-section">
      <h2>Alamat Pengiriman</h2>
      <p><strong>Rumah - <?php echo htmlspecialchars($username); ?></strong></p>
      <p><?php echo htmlspecialchars($alamat); ?></p>
      <button class="btn" onclick="window.location.href='profile.php'">Ganti Alamat</button>
    </div>

    <div class="order-section">
      <h2>Pesanan Anda</h2>
      <?php foreach ($cart_items as $item): ?>
        <div class="order-details">
          <img src="image/<?php echo $item['foto']; ?>" alt="Product Image" class="product-img" />
          <div class="details">
            <h3><?php echo htmlspecialchars($item['nama']); ?></h3>
            <p><?php echo htmlspecialchars($item['pengarang']); ?></p>
            <p>Jumlah: <?php echo $item['quantity']; ?></p>
            <p><strong>Rp.
                <?php echo number_format(($item['harga_diskon'] ?? $item['harga']) * $item['quantity'], 0, ',', '.'); ?></strong>
            </p>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="delivery-info">
        <h4>Pilih Pengiriman</h4>
        <select class="delivery-dropdown" id="delivery-dropdown">
          <option value="same-day">Same Day 8 Jam (Rp61.000)</option>
          <option value="next-day">Next Day Delivery (Rp30.000)</option>
          <option value="regular">Regular Delivery (Rp15.000)</option>
          <option value="pickup">Pickup in Store (Gratis)</option>
        </select>

        <p><strong>Dilindungi Asuransi Pengiriman (Rp3.400)</strong></p>
      </div>
    </div>

    <div class="summary-section">
      <h2>Ringkasan Belanja</h2>
      <p>Total Harga (<?php echo $total_quantity; ?> Barang): Rp <?php echo number_format($total_harga, 0, ',', '.'); ?>
      </p>
      <p>Total Ongkos Kirim: <span id="total-shipping">Rp 61.000</span></p>
      <p>Total Asuransi Pengiriman: Rp 4.000</p>
      <p>Biaya Jasa Aplikasi: Rp 1.000</p>
      <h3>Total Belanja: <span
          id="total-amount">Rp<?php echo number_format($total_harga + 61000 + 4000 + 1000, 0, ',', '.'); ?></span></h3>




      <form action="payment.php" method="POST" id="payment-form">
        <input type="hidden" name="payment_form" value="example_method">
        <input type="hidden" id="selected-shipping" name="ongkir" value="61000"> <!-- Default ke Same Day -->
        <button class="btn confirm-btn" type="submit">Konfirmasi Pembayaran</button>
      </form>


      <p>Dengan melanjutkan, kamu menyetujui <a href="#">S&K Asuransi & Proteksi</a>.</p>
    </div>
  </div>

  <footer>
    <p>&copy; Kelompok 1 | PPW | <a href="#" class="privacy-policy">Privacy Policy</a></p>
  </footer>

  <script>
    function togglePaymentOptions() {
      const paymentOptions = document.querySelector('.payment-options');
      paymentOptions.classList.toggle('hidden');

    }
    document.getElementById('delivery-dropdown').addEventListener('change', function () {
      const shippingPrices = {
        'same-day': 61000,
        'next-day': 30000,
        'regular': 15000,
        'pickup': 0
      };

      const selectedOption = this.value;
      const shippingCost = shippingPrices[selectedOption] || 0;

      // Update nilai ongkir di input tersembunyi
      document.getElementById('selected-shipping').value = shippingCost;

      // Update tampilan total belanja
      const basePrice = <?php echo $total_harga; ?>;
      const insurance = 4000;
      const appFee = 1000;

      const totalShipping = document.getElementById('total-shipping');
      const totalAmount = document.getElementById('total-amount');

      totalShipping.textContent = `Rp${shippingCost.toLocaleString('id-ID')}`;
      const finalAmount = basePrice + shippingCost + insurance + appFee;
      totalAmount.textContent = `Rp${finalAmount.toLocaleString('id-ID')}`;
    });

  </script>
</body>

</html>