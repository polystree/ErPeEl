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

// Handle wishlist toggle
if (isset($_POST['action']) && $_POST['action'] === 'toggle_wishlist') {
    if (!isset($_POST['produk_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit();
    }
    
    $produk_id = mysqli_real_escape_string($con, $_POST['produk_id']);
    
    // Check if product exists
    $product_check = mysqli_query($con, "SELECT id FROM produk WHERE id = '$produk_id'");
    if (mysqli_num_rows($product_check) == 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit();
    }
    
    // Check if already in wishlist
    $check_wishlist = mysqli_query($con, "SELECT id FROM wishlist WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
    
    if (mysqli_num_rows($check_wishlist) > 0) {
        // Remove from wishlist
        $delete_query = mysqli_query($con, "DELETE FROM wishlist WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
        if ($delete_query) {
            echo json_encode([
                'success' => true, 
                'action' => 'removed',
                'message' => 'Game removed from wishlist!'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist']);
        }
    } else {
        // Add to wishlist
        $insert_query = mysqli_query($con, "INSERT INTO wishlist (produk_id, user_id) VALUES ('$produk_id', '$user_id')");
        if ($insert_query) {
            echo json_encode([
                'success' => true, 
                'action' => 'added',
                'message' => 'Game added to wishlist!'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
        }
    }
    exit();
}

// Handle add to cart
if (isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    if (!isset($_POST['produk_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit();
    }
    
    $produk_id = mysqli_real_escape_string($con, $_POST['produk_id']);
    
    // Check if product exists
    $product_check = mysqli_query($con, "SELECT id, nama FROM produk WHERE id = '$produk_id'");
    if (mysqli_num_rows($product_check) == 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit();
    }
    
    $product = mysqli_fetch_assoc($product_check);
    
    // Check if already in cart
    $check_cart = mysqli_query($con, "SELECT id FROM cart WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
    
    if (mysqli_num_rows($check_cart) > 0) {
        // Remove from cart if already exists (digital games - only one copy needed)
        $delete_query = mysqli_query($con, "DELETE FROM cart WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
        
        if ($delete_query) {
            echo json_encode([
                'success' => true, 
                'action' => 'removed',
                'message' => 'Game removed from cart!',
                'product_name' => $product['nama'],
                'in_cart' => false
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to remove from cart']);
        }
    } else {
        // Add to cart with quantity 1 (digital games)
        $insert_query = mysqli_query($con, "INSERT INTO cart (produk_id, user_id, quantity) VALUES ('$produk_id', '$user_id', 1)");
        
        if ($insert_query) {
            echo json_encode([
                'success' => true, 
                'action' => 'added',
                'message' => 'Game added to cart!',
                'product_name' => $product['nama'],
                'in_cart' => true
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
        }
    }
    exit();
}

// Handle get cart count
if (isset($_GET['action']) && $_GET['action'] === 'get_cart_count') {
    $cart_count_query = mysqli_query($con, "SELECT SUM(quantity) as total_count FROM cart WHERE user_id = '$user_id'");
    $cart_count = mysqli_fetch_assoc($cart_count_query);
    
    echo json_encode([
        'success' => true,
        'count' => $cart_count['total_count'] ?? 0
    ]);
    exit();
}

// Handle get order items
if (isset($_POST['action']) && $_POST['action'] === 'get_order_items') {
    if (!isset($_POST['order_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order ID required']);
        exit();
    }
    
    $order_id = mysqli_real_escape_string($con, $_POST['order_id']);
    
    // Verify order belongs to user
    $order_check = mysqli_query($con, "SELECT id FROM orders WHERE id = '$order_id' AND user_id = '$user_id'");
    if (mysqli_num_rows($order_check) == 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }
    
    // Get order items with product details and review status
    $items_query = mysqli_query($con, "
        SELECT oi.produk_id, oi.order_id, oi.price, oi.steam_key,
            p.nama, p.foto, p.pengembang, p.harga, p.harga_diskon,
            r.id AS review_id, r.rating, r.comment AS review_comment, r.created_at AS review_date
        FROM order_items oi
        JOIN produk p ON oi.produk_id = p.id
        LEFT JOIN rating r ON r.produk_id = p.id AND r.user_id = '$user_id'
        WHERE oi.order_id = '$order_id'
        ORDER BY p.nama
    ");
    
    if (mysqli_num_rows($items_query) > 0) {
        $items = [];
        while ($item = mysqli_fetch_assoc($items_query)) {
            $items[] = $item;
        }
        
        echo json_encode([
            'success' => true,
            'items' => $items
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No items found for this order'
        ]);
    }
    exit();
}

// If no valid action
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
