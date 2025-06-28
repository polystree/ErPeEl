<?php
require "session.php";
require "koneksi.php";

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['loginbtn']) || $_SESSION['loginbtn'] == false) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get cart items
$select_cart = mysqli_query($con, "SELECT c.*, p.nama, p.pengembang, p.foto, p.harga, p.harga_diskon FROM `cart` c JOIN `produk` p ON c.produk_id = p.id WHERE c.user_id = '$user_id'") or die('Query failed');

$cartItems = [];
$total_price = 0;
$cartItemsHtml = '';
$orderSummaryHtml = '';

if (mysqli_num_rows($select_cart) > 0) {
    while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
        $cartItems[] = $fetch_cart;
        
        $price = ($fetch_cart['harga_diskon'] !== null && $fetch_cart['harga_diskon'] > 0) 
                ? $fetch_cart['harga_diskon'] 
                : $fetch_cart['harga'];
        $total_price += $price;
        
        // Generate cart item HTML
        $cartItemsHtml .= '<article class="game-card cart-item-card">
            <div class="cart-item-layout">
                <!-- Game cover - uses full height -->
                <div class="cart-item-image">
                    <img class="game-cover" 
                         src="image/' . htmlspecialchars($fetch_cart['foto']) . '" 
                         alt="' . htmlspecialchars($fetch_cart['nama']) . ' cover" />
                </div>
                
                <!-- Content area -->
                <div class="cart-item-content">
                    <!-- Top section: Remove button (far right) -->
                    <div class="cart-item-top">
                        <form method="post" action="" class="remove-form">
                            <input type="hidden" name="produk_id" value="' . $fetch_cart['produk_id'] . '">
                            <button class="remove-btn" name="hapus" aria-label="Remove ' . htmlspecialchars($fetch_cart['nama']) . ' from cart">
                                ×
                            </button>
                        </form>
                    </div>
                    
                    <!-- Top right: Digital Games tag and title -->
                    <div class="cart-item-header">
                        <div class="game-type">Digital Game</div>
                        <h3 class="game-title">' . htmlspecialchars($fetch_cart['nama']) . '</h3>
                    </div>
                    
                    <!-- Bottom section: Developer name and Price horizontally -->
                    <div class="cart-item-bottom">
                        <p class="game-developer">by ' . htmlspecialchars($fetch_cart['pengembang']) . '</p>
                        <div class="game-price">';
        
        if ($fetch_cart['harga_diskon'] !== null && $fetch_cart['harga_diskon'] > 0 && $fetch_cart['harga_diskon'] < $fetch_cart['harga']) {
            $discount_percent = round((($fetch_cart['harga'] - $fetch_cart['harga_diskon']) / $fetch_cart['harga']) * 100);
            $cartItemsHtml .= '<div class="discount-badge">-' . $discount_percent . '%</div>
                <span class="original-price">$' . number_format($fetch_cart['harga'], 2) . '</span>
                <span class="discounted-price">$' . number_format($fetch_cart['harga_diskon'], 2) . '</span>';
        } else {
            $cartItemsHtml .= '<span class="current-price">$' . number_format($fetch_cart['harga'], 2) . '</span>';
        }
        
        $cartItemsHtml .= '</div>
                    </div>
                </div>
            </div>
        </article>';
    }
    
    // Generate order summary HTML
    $orderSummaryHtml = '<div class="summary-header">
        <h3 class="game-title">Order Summary</h3>
    </div>
    <div class="summary-content">
        <div class="summary-row">
            <span>Subtotal</span>
            <span>$' . number_format($total_price, 2) . '</span>
        </div>
        <div class="summary-row">
            <span>Tax</span>
            <span>$0.00</span>
        </div>
        <div class="summary-row total-row">
            <span>Total</span>
            <span>$' . number_format($total_price, 2) . '</span>
        </div>
        
        <div class="checkout-actions">
            <button class="add-to-cart-btn special" onclick="window.location.href=\'payment.php\';">
                Proceed to Checkout
            </button>
        </div>
    </div>';
}

echo json_encode([
    'success' => true,
    'cartItems' => $cartItems,
    'cartItemsHtml' => $cartItemsHtml,
    'orderSummaryHtml' => $orderSummaryHtml,
    'totalPrice' => $total_price,
    'itemCount' => count($cartItems)
]);
?>
