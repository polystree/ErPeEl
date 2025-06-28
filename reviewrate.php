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


$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;


$query = "
    SELECT 
        o.id AS order_id,
        o.created_at AS tanggal_pesanan,
        oi.quantity AS jumlah,
        oi.price AS harga_per_unit,
        oi.review AS rating,
        p.id AS produk_id,
        p.nama AS nama_produk,
        p.foto AS gambar_produk,
        p.pengarang AS nama_pengarang,
        o.total_amount AS total_harga,
        o.ongkir AS ongkos_kirim,
        u.username AS user_name,
        u.alamat AS user_alamat
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $user_ids = $_POST['user_id'];
  $produk_ids = $_POST['produk_id'];
  $ratings = $_POST['rating'];
  $comments = $_POST['comment'];

  foreach ($produk_ids as $index => $produk_id) {
      $user_id = $user_ids[$index];
      $rating = $ratings[$index];
      $comment = $comments[$index];

      if ($produk_id > 0 && $user_id > 0 && $rating > 0 && $rating <= 5) {
          $query = "INSERT INTO rating (produk_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
          $stmt = $con->prepare($query);
          $stmt->bind_param("iiis", $produk_id, $user_id, $rating, $comment);

          if (!$stmt->execute()) {
              echo "<script>alert('Gagal menyimpan ulasan.');</script>";
          }

          $stmt->close();
      }
  }

  $update_status_query = "UPDATE orders SET status_order = 4 WHERE id = ? AND user_id = ?";
  $update_stmt = $con->prepare($update_status_query);
  $update_stmt->bind_param("ii", $order_id, $user_id);

  if ($update_stmt->execute()) {
    header("Location: profile.php"); 
  } else {
    echo "<script>alert('Gagal memperbarui status pesanan.');</script>";
  }

  $update_stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>rev</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="reviewrate.css">
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
                            <h4>Fiksi</h4>
                            <ul>
                                <li><a href="kategori.php?kategori=1">Novel</a></li>
                                <li><a href="kategori.php?kategori=2">Cerita Pendek</a></li>
                                <li><a href="kategori.php?kategori=3">Komik</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>Non-Fiksi</h4>
                            <ul>
                                <li><a href="kategori.php?kategori=4">Biografi</a></li>
                                <li><a href="kategori.php?kategori=5">Sejarah</a></li>
                                <li><a href="kategori.php?kategori=6">Psikologi</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>Sains & Teknologi</h4>
                            <ul>
                                <li><a href="kategori.php?kategori=7">Ilmu Komputer</a></li>
                                <li><a href="kategori.php?kategori=8">Fisika</a></li>
                                <li><a href="kategori.php?kategori=9">Biologi</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>Bisnis & Ekonomi</h4>
                            <ul>
                                <li><a href="kategori.php?kategori=10">Kewirausahaan</a></li>
                                <li><a href="kategori.php?kategori=11">Investasi</a></li>
                                <li><a href="kategori.php?kategori=12">Manajemen</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>Pengembangan Diri</h4>
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
                <input type="text" placeholder="Cari Buku" class="search-input">
                <button type="submit" class="search-icon">
                    <img src="image/search-btn.svg" class="search-img" alt="Search Icon">
                </button>
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
    <div class="order-header">Beri Ulasan</div>
    
    <form method="POST" action="">
      <?php foreach ($order_details as $detail) {
        if (empty($detail['rating'])) {
        ?>

        <div class="order-product">
          <div class="product-detail">
            <div class="product-image">
              <img src="image/<?php echo $detail['gambar_produk']; ?>" alt="Product Image">
            </div>
            <div class="product-info">
              <p><?php echo $detail['nama_produk']; ?></p>
              <p><?php echo $detail['nama_pengarang']; ?></p>
              <p><?php echo $detail['jumlah']; ?>x Rp<?php echo number_format($detail['harga_per_unit'], 0, ',', '.'); ?></p>
            </div>
            <div class="product-price">
              <p>Total Harga</p>
              <p class="total-price">
                Rp<?php echo number_format($detail['jumlah'] * $detail['harga_per_unit'], 0, ',', '.'); ?>
              </p>
            </div>
          </div>

          
          <input type="hidden" name="produk_id[]" value="<?php echo $detail['produk_id']; ?>">
          <input type="hidden" name="user_id[]" value="<?php echo $user_id; ?>">
          <input type="hidden" name="rating[]" id="rating-input-<?php echo $detail['produk_id']; ?>" value="">

          <div class="stars" data-id="<?php echo $detail['produk_id']; ?>">
            <span class="star" data-value="1">★</span>
            <span class="star" data-value="2">★</span>
            <span class="star" data-value="3">★</span>
            <span class="star" data-value="4">★</span>
            <span class="star" data-value="5">★</span>
          </div>

          <textarea name="comment[]" placeholder="Tulis ulasan Anda..." rows="3" style="width: 100%; margin-top: 10px;"></textarea>

        </div>
        <?php }
      } ?>

      
      <button class="button dikemas" type="submit">Kirim Ulasan</button>
    </form>

  </div>

  <footer>
    <p>&copy; Kelompok 1 | PPW | <a href="#" class="privacy-policy">Privacy Policy</a></p>
  </footer>

  <script>
    
    document.querySelectorAll('.stars').forEach(starsContainer => {
      const stars = starsContainer.querySelectorAll('.star');
      const ratingInput = starsContainer.parentElement.querySelector('input[name="rating[]"]');  // Updated selector

      stars.forEach((star, index) => {
        star.addEventListener('mouseenter', () => {
          stars.forEach((s, i) => {
            if (i <= index) s.classList.add('hovered');
            else s.classList.remove('hovered');
          });
        });

        star.addEventListener('mouseleave', () => {
          stars.forEach(s => s.classList.remove('hovered'));
        });

        star.addEventListener('click', () => {
          const ratingValue = index + 1;
          ratingInput.value = ratingValue;

          stars.forEach((s, i) => {
            if (i <= index) s.classList.add('selected');
            else s.classList.remove('selected');
          });
        });
      });
    });
  </script>

</body>

</html>
