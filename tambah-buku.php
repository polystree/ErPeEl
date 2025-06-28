<?php
require "koneksi.php"; 
require "session.php";

if (isset($_POST['submit'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $pengarang = htmlspecialchars($_POST['pengarang']);
    $stok = htmlspecialchars($_POST['stok']);
    $harga = htmlspecialchars($_POST['harga']);

    $target_dir = "image/";
    $nama_file = basename($_FILES["foto"]["name"]);
    $target_file = $target_dir . $nama_file; 
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $img_size = $_FILES["foto"]["size"];

    
    if (empty($nama) || empty($pengarang) || empty($harga)) {
        $error_message = "Nama, pengarang, dan harga wajib diisi.";
    } elseif ($img_size > 500000) { 
        $error_message = "File tidak boleh lebih dari 500KB.";
    } elseif (!in_array($imageFileType, ['avif', 'png', 'jpg', 'jpeg'])) {
        $error_message = "File wajib bertipe AVIF, PNG, JPG, atau JPEG.";
    } else {
        // Upload file
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            
            $sql_tambah = "INSERT INTO produk (nama, deskripsi, pengarang, stok, harga, foto) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = $con->prepare($sql_tambah);
            $stmt_insert->bind_param('ssssss', $nama, $deskripsi, $pengarang, $stok, $harga, $nama_file);

            if ($stmt_insert->execute()) {
                $success_message = "Buku berhasil ditambahkan!";
                header("Location: admin.php"); 
                exit(); 
            } else {
                $error_message = "Terjadi kesalahan saat menambahkan buku.";
            }
            $stmt_insert->close();
        } else {
            $error_message = "Terjadi kesalahan saat mengupload foto.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk</title>
    <link rel="stylesheet" href="tambah-buku.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>

   
   <nav class="navbar">
       <div class="upper-nav">
           <div class="logo">
               <a href="dashboard.php"><img src="image/logowhite.png" alt="Logo"></a>
           </div>
           <div class="search-bar">
               <input type="text" placeholder="Cari Buku" class="search-input">
               <button type="submit" class="search-icon">
                   <img src="image/search-btn.svg" class="search-img" alt="Search Icon">
               </button>
           </div>
           <div class="nav-icons">
               <div class="nav-icon"></div>
               <div class="nav-icon profile"></div>
           </div>
       </div>
       <div class="lower-nav">
           
       </div>
   </nav>

  
   <div class="container">
   <div class="button-back">
        <a href="admin.php" class="back-button">
            <img src="image/back purple.svg" class="back-icon" alt="Back Icon">
            Kembali
        </a>
    </div>
    <h1 class="detail-buku">Tambah Buku</h1>
       <div class="content">
           <div class="info-container">
               <h3 class="section-title">Informasi Buku</h3>
               <form class="book-info" method="post" enctype="multipart/form-data">
                   <div class="header-buttons">
                       <button type="submit" name="submit" class="button upload">Upload</button>
                   </div>
                   <div class="nama">
                   <label for="nama">Nama Buku
                       <input type="text" class="name-input" name="nama" autocomplete="off" required>
                   </label>
                    </div>
                   <label for="deskripsi">Deskripsi
                       <textarea name="deskripsi" rows="4"></textarea>
                   </label>
                   <label for="pengarang">Pengarang
                       <input type="text" class="author-input" name="pengarang" required>
                   </label>
                   <div class="stok">
                       <label for="stock">Stok</label>
                       <div class="stock-controls">
                           <input type="text" name="stok" placeholder="0">
                       </div>
                   </div>
                   <div class="price">
                       <label for="harga">Harga </label>
                       <div class="price-details">
                           <input type="text" name="harga" placeholder="Rp." required>
                       </div>
                   </div>
                   <div class="price">
                       <label for="harga">Harga Diskon</label>
                       <div class="price-details">
                           <input type="text" name="harga" placeholder="Rp." required>
                       </div>
                   </div>
                   <div class="foto">
                       <label for="foto">Foto</label>
                       <input type="file" name="foto" id="foto" accept=".jpg, .jpeg, .png, .avif">
                   </div>
               </form>
               <?php if (isset($error_message)): ?>
                   <div style="color:red;"><?= $error_message ?></div>
               <?php elseif (isset($success_message)): ?>
                   <div style="color:green;"><?= $success_message ?></div>
               <?php endif; ?>
           </div>
       </div>
   </div>
  
   <footer>
       <p>&copy; Kelompok 1 | PPW | <a href="#" class="privacy-policy">Privacy Policy</a></p>
   </footer>
</body>
</html>
