<?php
require "koneksi.php";
require "session.php";


if (isset($_POST['logout-btn'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
if (isset($_POST['add-product-btn'])){
  header("tambah-buku-php");
}

$sql = "SELECT * FROM produk";
$result = $con->query($sql);
$jumlahproduk = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin</title>
  <link rel="stylesheet" href="admin.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
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

  <div class="lower-nav">
      
  </div>
</nav>

<div class="admin-container">
    <div class="header">
        <div class="profile">
            <div class="avatar"></div>
            <span class="username">Admin</span>
        </div>

        
        <form method="post" style="display: inline;">
            <button type="submit" class="logout-btn" name="logout-btn">Log Out</button>
        </form>
    </div>
</div>

<div class="container">
    <h1 class="title">Kelola Buku</h1>
    <table class="book-table">
      <thead>
        <tr>
          <th>Gambar</th>
          <th>Nama Produk</th>
          <th>Pengarang</th>
          <th>Stok</th>
          <th>Harga Satuan</th>
          <th>Edit</th>
        </tr>
      </thead>
      <tbody>
        <?php
          if($jumlahproduk == 0){
              echo "<tr><td colspan='6'>Tidak ada produk tersedia.</td></tr>";
          } else {
              while($data = $result->fetch_assoc()){
        ?>  
        <tr>
          <td><img class="book-image" src="image/<?php echo $data['foto']; ?>" alt="Gambar Produk" /></td>
          <td><?php echo $data['nama']?></td>         
          <td><?php echo $data['pengarang']?></td>
          <td class="available"><?php echo $data['stok']?></td>
          <td class="price"><?php echo $data['harga']?></td>      
          <td><a href="edit-buku.php?id=<?php echo $data['id']; ?>" class="status edit">Edit</a></td>
        </tr>
        <?php
              }
          }
        ?>
      </tbody>
    </table>
    <div class="actions-2">
        <button type="submit" class="add-product-btn"><a href="tambah-buku.php">Tambahkan Produk</a></button>
    </div>
</div>

<footer>
    <p>&copy; Kelompok 1 | PPW | <a href="#" class="privacy-policy">Privacy Policy</a></p>
</footer>
</body>
</html>
