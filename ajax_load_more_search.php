<?php
require "session.php";
require "koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['loginbtn']) || $_SESSION['loginbtn'] == false) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Rating function
function getAverageRating($con, $produk_id) {
    $rating_query = mysqli_query($con, "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM rating WHERE produk_id = '$produk_id'");
    $rating_data = mysqli_fetch_assoc($rating_query);
    return [
        'average' => $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0,
        'total' => $rating_data['total_reviews']
    ];
}

function generateStarRating($avg_rating) {
    $rounded_rating = round($avg_rating);
    $full_stars = $rounded_rating;
    $empty_stars = 5 - $full_stars;
    
    return str_repeat('★', $full_stars) . 
           str_repeat('☆', $empty_stars);
}

// Get parameters
$search_query = isset($_GET['query']) ? mysqli_real_escape_string($con, $_GET['query']) : '';
$selected_categories = isset($_GET['category']) ? $_GET['category'] : [];
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'terbaru';
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = 20; // Load 20 more items at a time

$sql_condition = "WHERE 1=1";

if ($search_query != '') {
    $sql_condition .= " AND (p.nama LIKE '%$search_query%' OR p.pengembang LIKE '%$search_query%')";
}

if (!empty($selected_categories)) {
    $category_ids_str = implode(",", array_map('intval', $selected_categories));
    $sql_condition .= " AND p.kategori_id IN ($category_ids_str)";
}

// Order by mapping
$order_by = "p.id DESC"; // Default
switch ($sort_by) {
    case 'terlaris':
        $order_by = "(SELECT COUNT(*) FROM rating r WHERE r.produk_id = p.id) DESC";
        break;
    case 'harga_terendah':
        $order_by = "(CASE WHEN p.harga_diskon > 0 THEN p.harga_diskon ELSE p.harga END) ASC";
        break;
    case 'harga_tertinggi':
        $order_by = "(CASE WHEN p.harga_diskon > 0 THEN p.harga_diskon ELSE p.harga END) DESC";
        break;
}

// Query untuk mengambil data produk dengan offset
$sql = "SELECT p.* FROM produk p $sql_condition ORDER BY $order_by LIMIT $limit OFFSET $offset";
$select_produk = mysqli_query($con, $sql) or die('Query failed: ' . mysqli_error($con));

// Get cart and wishlist data for current user
$cart_query = mysqli_query($con, "SELECT produk_id FROM `cart` WHERE user_id = '$user_id'");
$cart_items = [];
while ($cart_row = mysqli_fetch_assoc($cart_query)) {
    $cart_items[] = $cart_row['produk_id'];
}

$wishlist_query = mysqli_query($con, "SELECT produk_id FROM `wishlist` WHERE user_id = '$user_id'");
$wishlist_ids = [];
while ($wishlist_row = mysqli_fetch_assoc($wishlist_query)) {
    $wishlist_ids[] = $wishlist_row['produk_id'];
}

// Check if there are more results
$total_sql = "SELECT COUNT(*) as total FROM produk p $sql_condition";
$total_result = mysqli_query($con, $total_sql);
$total_count = mysqli_fetch_assoc($total_result)['total'];
$has_more = ($offset + $limit) < $total_count;

$html = '';
if (mysqli_num_rows($select_produk) > 0) {
    while ($fetch_produk = mysqli_fetch_assoc($select_produk)) {
        $rating_data = getAverageRating($con, $fetch_produk['id']);
        $is_in_wishlist = in_array($fetch_produk['id'], $wishlist_ids);
        $is_in_cart = in_array($fetch_produk['id'], $cart_items);
        
        $html .= '<article class="game-card" role="listitem" 
                         onclick="window.location.href=\'detail.php?produk_id=' . $fetch_produk['id'] . '\'"
                         onkeydown="if(event.key===\'Enter\'||event.key===\' \') window.location.href=\'detail.php?produk_id=' . $fetch_produk['id'] . '\'"
                         tabindex="0">
                    <div class="game-cover-container">
                        <img class="game-cover" 
                             src="image/' . $fetch_produk['foto'] . '"
                             alt="' . htmlspecialchars($fetch_produk['nama']) . ' cover" 
                             loading="lazy" />
                        <button type="button" 
                                class="wishlist-btn ' . ($is_in_wishlist ? 'active' : '') . '" 
                                data-produk-id="' . $fetch_produk['id'] . '"
                                data-in-wishlist="' . ($is_in_wishlist ? 'true' : 'false') . '"
                                onclick="event.stopPropagation(); toggleWishlist(this);"
                                aria-label="' . ($is_in_wishlist ? 'Remove from wishlist' : 'Add to wishlist') . '">
                            <svg class="heart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="game-info">
                        <div class="game-price">';
        
        if ($fetch_produk['harga_diskon'] != NULL && $fetch_produk['harga_diskon'] > 0 && $fetch_produk['harga_diskon'] < $fetch_produk['harga']) {
            $html .= '<div class="discount-badge">-' . round((($fetch_produk['harga'] - $fetch_produk['harga_diskon']) / $fetch_produk['harga']) * 100) . '%</div>
                      <span class="original-price">$' . number_format($fetch_produk['harga'], 2) . '</span>
                      <span class="discounted-price">$' . number_format($fetch_produk['harga_diskon'], 2) . '</span>';
        } else {
            $html .= '<span class="current-price">$' . number_format($fetch_produk['harga'], 2) . '</span>';
        }
        
        $html .= '</div>
                        <h3 class="game-title">' . htmlspecialchars($fetch_produk['nama']) . '</h3>
                        <p class="game-developer">' . htmlspecialchars($fetch_produk['pengembang']) . '</p>
                        <div class="game-rating">
                            <span class="stars">' . generateStarRating($rating_data['average']) . '</span>
                            <span class="rating-text">' . ($rating_data['total'] > 0 ? $rating_data['average'] . '/5 (' . $rating_data['total'] . ')' : 'No ratings') . '</span>
                        </div>
                        <button class="add-to-cart-btn ' . ($is_in_cart ? 'added' : '') . '" 
                                data-product-id="' . $fetch_produk['id'] . '"
                                data-in-cart="' . ($is_in_cart ? 'true' : 'false') . '"
                                onclick="event.stopPropagation(); toggleCart(this);">
                            ' . ($is_in_cart ? 'In Cart' : 'Add to Cart') . '
                        </button>
                    </div>
                </article>';
    }
}

echo json_encode([
    'success' => true,
    'html' => $html,
    'hasMore' => $has_more,
    'newOffset' => $offset + $limit
]);
?>
