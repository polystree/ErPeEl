<?php
require "session.php";
require "koneksi.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    
    $sql = "SELECT * FROM produk WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();

   
    if ($result->num_rows > 0) {
        $buku = $result->fetch_assoc();
    } else {
        die("Buku tidak ditemukan.");
    }
}
if (isset($_POST['del'])) {
    $sql_delete = "DELETE FROM produk WHERE id = ?";
    $stmt_delete = $con->prepare($sql_delete);
    $stmt_delete->bind_param('s', $id);

    if ($stmt_delete->execute()) {
        header("Location: admin.php"); 
        exit;
    } else {
        $error_message = "Terjadi kesalahan saat menghapus buku.";
    }
}


if (isset($_POST['submit'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $pengarang = htmlspecialchars($_POST['pengarang']);
    $stok = htmlspecialchars($_POST['stok']);
    $harga = htmlspecialchars($_POST['harga']);
    $uploaded_file = $buku['foto']; 

   
    if ($_FILES["image"]["error"] === 0) {
        $file_name = $_FILES["image"]["name"];
        $file_size = $_FILES["image"]["size"];
        $tmpname = $_FILES["image"]["tmp_name"];

        $validimage = ['jpg', 'jpeg', 'png', 'avif'];
        $image_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($image_ext, $validimage)) {
            echo "Ekstensi gambar tidak valid";
        } elseif ($file_size > 10000000) {
            echo "Ukuran file terlalu besar";
        } else {
            $newimagename = uniqid() . '.' . $image_ext;
            $upload_path = 'image/' . $newimagename;

            
            if (move_uploaded_file($tmpname, $upload_path)) {
                $uploaded_file = $newimagename; 
            } else {
                echo "Gagal mengunggah gambar";
            }
        }
    }
    

    $sql_update = "UPDATE produk SET nama = ?, deskripsi = ?, pengarang = ?, stok = ?, harga = ?, foto = ? WHERE id = ?";
    $stmt_update = $con->prepare($sql_update);
    $stmt_update->bind_param('ssssssi', $nama, $deskripsi, $pengarang, $stok, $harga, $uploaded_file, $id);
    
    if ($stmt_update->execute()) {
        $success_message = "Buku berhasil diperbarui!";
        header("Location: admin.php");
        exit;
    } else {
        $error_message = "Terjadi kesalahan saat memperbarui buku.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Buku</title>
  <link rel="stylesheet" href="edit-buku.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>


   <nav class="navbar">
    <div class="upper-nav">
        <div class="logo">
            <a href="dashboard.php"> <img src="image/logo white.png" alt="Logo"> </a>
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

  <div class="header-container">
    <div class="button-back">
        <a href="admin.php" class="back-button">
            <img src="image/back purple.svg" class="back-icon" alt="Back Icon">
            Kembali
        </a>
    </div>
      <h1 class="detail-buku">Edit Buku</h1>
  </div>
  
  <div class="container">
    <div class="content">
        <div class="info-container">
            <h3 class="section-title">Informasi Buku</h3>
            <form class="book-info" method="post" enctype="multipart/form-data">
                <label>Foto Buku</label>
                <div class="photo-container">
                    <img src="image/<?= htmlspecialchars($buku['foto']) ?>" alt="Foto Buku" style="width: 100px; height: auto;">
                    <input type="file" name="image" id="image" accept=".jpg, .jpeg, .png, .avif">
                </div>
                <label>Nama Buku
                    <input type="text" class="name-input" name="nama" value="<?= htmlspecialchars($buku['nama']) ?>" required>
                </label>
                <label>Deskripsi
                    <textarea name="deskripsi" rows="4" required><?= htmlspecialchars($buku['deskripsi']) ?></textarea>
                </label>
                <label>Pengarang
                    <input type="text" class="author-input" name="pengarang" value="<?= htmlspecialchars($buku['pengarang']) ?>" required>
                </label>
                <label>Stok
                    <input type="text" class="stock-input" name="stok" value="<?= htmlspecialchars($buku['stok']) ?>" required>
                </label>
                <label >Harga
                    <input style="margin-left:17px" type="text" class="price-input" name="harga" value="<?= htmlspecialchars($buku['harga']) ?>" required>
                </label>
                <label >Harga Diskon
                    <input style="margin-left:17px" type="text" class="price-input" name="harga-diskon" value="<?= htmlspecialchars($buku['harga_diskon']) ?>" required>
                </label>
                <div class="action-buttons">
                    <button type="submit" name="submit" class="button upload">Simpan</button>
                    <button type="submit" name="del" class="button remove">Hapus</button>
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
