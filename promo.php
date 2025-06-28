<?php
require "session.php";
require "koneksi.php";

if (!isset($_SESSION['loginbtn']) || $_SESSION['loginbtn'] == false) {
    header("Location: login.php");
    exit();
}
$message = "";
$user_id = $_SESSION['user_id'];
if (isset($_POST['produk_id'])) {
    $produk_id = intval($_POST['produk_id']);
   
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
$wishlist_ids = [];
$get_wishlist = mysqli_query($con, "SELECT produk_id FROM wishlist WHERE user_id = '$user_id'");
while ($row = mysqli_fetch_assoc($get_wishlist)) {
    $wishlist_ids[] = $row['produk_id'];
}


if (isset($_POST['toggle_wishlist']) && isset($_POST['produk_id'])) {
    $produk_id = mysqli_real_escape_string($con, $_POST['produk_id']);

    
    $check_wishlist = mysqli_query($con, "SELECT * FROM wishlist WHERE produk_id = '$produk_id' AND user_id = '$user_id'");

    if (mysqli_num_rows($check_wishlist) > 0) {
        
        $delete_wishlist = mysqli_query($con, "DELETE FROM wishlist WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
        if (!$delete_wishlist) {
            http_response_code(500); 
            echo "Error removing item from wishlist.";
            exit();
        }
    } else {
        
        $add_wishlist = mysqli_query($con, "INSERT INTO wishlist (produk_id, user_id) VALUES ('$produk_id', '$user_id')");
        if (!$add_wishlist) {
            http_response_code(500); 
            echo "Error adding item to wishlist.";
            exit();
        }
    }
    http_response_code(200); 
    echo "Wishlist updated successfully.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Promo</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.png">
    <link rel="stylesheet" href="semua.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
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

    <main class="main-content">

        <section class="book-sections">
            <div class="section-header">
                <h2 class="section-title">Buku Promo</h2>

            </div>

            <div class="container">
                <?php
                $select_produk = mysqli_query($con, "SELECT * FROM `produk` WHERE `harga_diskon` IS NOT NULL LIMIT 30") or die('Query failed');

                if (mysqli_num_rows($select_produk) > 0) {
                    while ($fetch_produk = mysqli_fetch_assoc($select_produk)) {
                        ?>
                 
                        <form class="book-card" action="detail.php" method="get">
                            <div class="book-image">
                                <a href="detail.php?produk_id=<?php echo $fetch_produk['id']; ?>">
                                    <div class="book-cover">
                                        <img class="book-cover" src="image/<?php echo $fetch_produk['foto']; ?>"
                                            alt="Gambar Produk" />
                                    </div>
                                </a>
                                <button type="button" class="wishlist" data-produk-id="<?php echo $fetch_produk['id']; ?>"
                                    data-in-wishlist="<?php echo in_array($fetch_produk['id'], $wishlist_ids) ? 'true' : 'false'; ?>"
                                    onclick="toggleWishlist(this);">
                                    <img src="image/heart_grey.svg" class="heart-icon heart-grey" alt="Grey Heart Icon"
                                        style="display:<?php echo in_array($fetch_produk['id'], $wishlist_ids) ? 'none' : 'inline'; ?>;" />
                                    <img src="image/heart.svg" class="heart-icon heart-red" alt="Red Heart Icon"
                                        style="display:<?php echo in_array($fetch_produk['id'], $wishlist_ids) ? 'inline' : 'none'; ?>;" />
                                </button>
                            </div>
                            <div class="book-details">
                                <a href="detail.php?produk_id=<?php echo $fetch_produk['id']; ?>" class="no-decoration">
                                    <h3 class="book-title"><?php echo $fetch_produk['nama']; ?></h3>
                                    <p class="book-author"><?php echo $fetch_produk['pengarang']; ?></p>
                                    <p class="stock-status">
                                        <?php echo ($fetch_produk['stok'] > 0) ? ' Tersedia' : 'Stok Habis'; ?>
                                    </p>

                                    <?php if ($fetch_produk['harga_diskon'] != NULL) { ?>
                                        <p class="book-price normal-price">Rp.
                                            <?php echo number_format($fetch_produk['harga'], 0, ',', '.'); ?>
                                        </p>
                                        <p class="book-price discount-price">Rp.
                                            <?php echo number_format($fetch_produk['harga_diskon'], 0, ',', '.'); ?>
                                        </p>
                                    <?php } else { ?>
                                        <p class="book-price discount-price">Rp.
                                            <?php echo number_format($fetch_produk['harga'], 0, ',', '.'); ?>
                                        </p>
                                    <?php } ?>
                            </div>
                        </form>
                        <?php
                    }
                }
                ?>
            </div>


        </section>

    </main>

    <div id="wishlist-notification" class="wishlist-notification">
        <img src="image/heart yellow.svg" class="heart-icon heart-yellow">
        <span class="wishlist-message">Berhasil dimasukkan ke Wishlist!</span>
    </div>

    <footer>
        <p>&copy; Kelompok 1 | PPW | <a href="#" class="privacy-policy">Privacy Policy</a></p>
    </footer>

    <script>
        
document.querySelectorAll(".wishlist").forEach(button => {
    const produkId = button.dataset.produkId;
    const inWishlist = button.getAttribute("data-in-wishlist") === "true";

    const greyIcon = button.querySelector(".heart-grey");
    const redIcon = button.querySelector(".heart-red");

    if (inWishlist) {
        greyIcon.style.display = "none";
        redIcon.style.display = "inline";
    } else {
        greyIcon.style.display = "inline";
        redIcon.style.display = "none";
    }
});

function showWishlistNotification(message) {
    const notification = document.getElementById("wishlist-notification");
    if (notification) {
        notification.textContent = message; 
        notification.classList.add("show"); 

        setTimeout(() => {
            notification.classList.remove("show");  
        }, 3000); 
    }
}


function toggleWishlist(button) {
    const produkId = button.dataset.produkId;
    const inWishlist = button.getAttribute("data-in-wishlist") === "true";

    const greyIcon = button.querySelector(".heart-grey");
    const redIcon = button.querySelector(".heart-red");

    fetch("dashboard.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `toggle_wishlist=true&produk_id=${produkId}`
    })
    .then(response => {
        if (response.ok) {
            
            button.setAttribute("data-in-wishlist", inWishlist ? "false" : "true");

            if (inWishlist) {
                greyIcon.style.display = "inline";
                redIcon.style.display = "none";
                
                showWishlistNotification("Item berhasil dihapus dari wishlist.");
            } else {
                greyIcon.style.display = "none";
                redIcon.style.display = "inline";
               
                showWishlistNotification("Item berhasil dimasukkan ke wishlist.");
            }
        } else {
            alert("Gagal memperbarui wishlist.");
        }
    })
    .catch(() => {
        alert("Terjadi kesalahan jaringan.");
    });
}


        const toggleButton = document.querySelector('.dropdown-toggle2');
        const dropdownMenu = document.querySelector('.dropdown-menu2');
        const filterItems = document.querySelectorAll('.filter-menu2');
        const activeFilterDisplay = document.querySelector('.filter-menu.active');

        
        toggleButton.addEventListener('click', () => {
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });

       
        filterItems.forEach(item => {
            item.addEventListener('click', () => {
               
                document.querySelector('.filter-menu2.active').classList.remove('active');
                item.classList.add('active');

                
                const activeText = item.textContent;
                toggleButton.querySelector('.filter-menu').textContent = activeText;

               
                console.log(`Selected filter: ${activeText}`);

                dropdownMenu.style.display = 'none';
            });
        });

        document.addEventListener('click', (event) => {
            if (!toggleButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.style.display = 'none';
            }
        });

        const tabs = document.querySelectorAll('.sidebar ul.menu li');
        const tabContents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelector('.sidebar ul.menu li.active').classList.remove('active');
                tab.classList.add('active');

                const targetId = tab.getAttribute('data-tab');
                tabContents.forEach(content => {
                    if (content.id === targetId) {
                        content.classList.add('active');
                    } else {
                        content.classList.remove('active');
                    }
                });

                console.log(`Switched to tab: ${targetId}`);
            });
        });

    </script>



</body>


</html>