<?php
require "session.php";
require "koneksi.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Get user profile including alamat (address)
$select_profile = mysqli_query($con, "SELECT username, email, foto FROM users WHERE id = '$user_id'") or die(mysqli_error($con));

if (mysqli_num_rows($select_profile) > 0) {
    $fetch_profile = mysqli_fetch_assoc($select_profile);
    $username = $fetch_profile['username'];
    $email = $fetch_profile['email'];
    $foto = $fetch_profile['foto'];
} else {
    $username = $email = $foto = "";
}

// Get cart items with correct column names
$select_cart_query = "
  SELECT c.*, p.nama, p.pengembang, p.foto, p.harga, p.harga_diskon 
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

// Redirect if cart is empty
if (empty($cart_items)) {
  header('Location: cart.php');
  exit();
}

if (isset($_POST['payment_form'])) {
  // Calculate total with processing fee only (no shipping for digital games)
  $processing_fee = 2.99; // Digital processing fee in dollars
  $total_belanja = $total_harga + $processing_fee;

  // Create order with correct column names based on SQL schema
  $query_order = "
    INSERT INTO orders (user_id, total, created_at) 
    VALUES ('$user_id', '$total_belanja', NOW())
  ";

  if (!mysqli_query($con, $query_order)) {
    die("Error: Gagal membuat pesanan. " . mysqli_error($con));
  }

  $order_id = mysqli_insert_id($con);
  if (!$order_id) {
    die("Error: Gagal mendapatkan ID pesanan.");
  }

  // Insert order items
  foreach ($cart_items as $item) {
    $produk_id = $item['produk_id'];
    $harga = $item['harga_diskon'] !== null ? $item['harga_diskon'] : $item['harga'];
    
    // Generate a unique Steam key for this item
    $steam_key = 'VAULT-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 5)) . '-' . 
                 strtoupper(substr(md5(uniqid(rand(), true)), 0, 5)) . '-' . 
                 strtoupper(substr(md5(uniqid(rand(), true)), 0, 5));

    $query_order_item = "
      INSERT INTO order_items (order_id, produk_id, price, steam_key) 
      VALUES ('$order_id', '$produk_id', '$harga', '$steam_key')
    ";
    if (!mysqli_query($con, $query_order_item)) {
      die("Error: Gagal memasukkan detail pesanan. " . mysqli_error($con));
    }

    // Update the sold count for this product
    $update_sold = "UPDATE produk SET sold = sold + 1 WHERE id = '$produk_id'";
    if (!mysqli_query($con, $update_sold)) {
      die("Error: Gagal mengupdate jumlah terjual. " . mysqli_error($con));
    }
  }

  // Clear cart
  $delete_cart = mysqli_query($con, "DELETE FROM cart WHERE user_id = '$user_id'");
  if (!$delete_cart) {
    die("Error: Gagal menghapus keranjang. " . mysqli_error($con));
  }

  // Redirect with success message
  $_SESSION['payment_success'] = true;
  $_SESSION['order_id'] = $order_id;
  header('Location: dashboard.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Payment - Vault Digital Store">
  <title>Payment - Vault Digital Store</title>
  <link rel="icon" type="image/x-icon" href="image/favicon.png">
  <link rel="stylesheet" href="dashboard.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Orbitron:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>
    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <div class="upper-nav">
            <div class="logo">
                <a href="dashboard.php" aria-label="Back to home">Vault</a>
            </div>
            
            <button class="mobile-menu-toggle" aria-label="Toggle mobile menu" aria-expanded="false" onclick="toggleMobileMenu()">
                <span class="hamburger-icon">☰</span>
            </button>

            <div class="menu" role="menubar" id="mobile-menu">
                <a href="semua.php" class="menu-item" role="menuitem">All Games</a>
            </div>

            <div class="search-bar" role="search">
                <form method="GET" action="search.php">
                    <input type="text" name="query" placeholder="Search Games" class="search-input" 
                           aria-label="Enter game search keywords">
                    <button type="submit" class="search-icon" aria-label="Start search">
                        <img src="image/search-btn.svg" class="search-img" alt="" width="16" height="16">
                    </button>
                </form>
            </div>

            <div class="nav-icons">
                <div class="nav-icon">
                    <a href="cart.php" aria-label="View shopping cart">
                        <img src="image/cart-btn.svg" class="icon-img" alt="" width="20" height="20">
                    </a>
                </div>

                <div class="nav-icon profile">
                    <a href="profile.php" aria-label="View user profile">
                        <?php if ($foto): ?>
                            <img src="image/<?php echo $foto; ?>" class="icon-img profile-avatar" alt="" width="44" height="44" style="border-radius: 50%; object-fit: cover; filter: none; width: 44px; height: 44px;">
                        <?php else: ?>
                            <img src="image/profile white.svg" class="icon-img" alt="" width="20" height="20">
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="main-content" role="main" aria-label="Payment section">
        <div class="payment-layout" style="max-width: 1200px; margin: 0 auto; padding: 0 var(--space-lg);">
            
            <!-- Full Width Content -->
            <div class="payment-main">
                <!-- Breadcrumb -->
                <div class="breadcrumb" style="margin-bottom: var(--space-xl);">  
                    <a href="cart.php" style="color: var(--text-secondary); text-decoration: none;">Cart</a>
                    <span style="color: var(--text-secondary); margin: 0 var(--space-sm);">→</span>
                    <span style="color: var(--primary); font-weight: 600;">Payment</span>
                </div>

                <!-- Order Items Section -->
                <section class="game-sections" style="margin-bottom: var(--space-xl);">
                    <div class="section-header">
                        <h2 class="section-title">Your Order (<?php echo $total_quantity; ?> items)</h2>
                    </div>
                    
                    <!-- Order Items and Summary Side by Side -->
                    <div style="display: grid; grid-template-columns: 1fr 400px; gap: var(--space-2xl); align-items: start;">
                        <!-- Order Items List -->
                        <div class="order-items-container">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="game-card" style="cursor: default; transform: none; margin-bottom: var(--space-md);">
                                    <div class="game-info" style="padding: var(--space-lg);">
                                        <div style="display: flex; gap: var(--space-lg); align-items: center;">
                                            <img src="image/<?php echo $item['foto']; ?>" 
                                                 alt="<?php echo htmlspecialchars($item['nama']); ?>"
                                                 style="width: 80px; height: 106px; border-radius: var(--radius-md); object-fit: cover; flex-shrink: 0;">
                                            
                                            <div style="flex: 1; min-width: 0;">
                                                <h3 class="game-title" style="margin-bottom: var(--space-xs);"><?php echo htmlspecialchars($item['nama']); ?></h3>
                                                <p class="game-developer" style="margin-bottom: var(--space-sm);"><?php echo htmlspecialchars($item['pengembang']); ?></p>
                                                <div class="game-price">
                                                    <?php if ($item['harga_diskon'] && $item['harga_diskon'] < $item['harga']): ?>
                                                        <span class="original-price">$<?php echo number_format($item['harga'], 2); ?></span>
                                                        <span class="discounted-price">$<?php echo number_format($item['harga_diskon'], 2); ?></span>
                                                    <?php else: ?>
                                                        <span class="current-price">$<?php echo number_format($item['harga'], 2); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Order Summary (moved here) -->
                        <div class="payment-sidebar" style="position: sticky; top: calc(var(--space-2xl) + var(--space-2xl)); margin-bottom: 20px;">
                            <div class="game-card" style="cursor: default; transform: none;">
                                <div class="game-info" style="padding: var(--space-xl);">
                                    <h3 style="color: var(--text-primary); margin-bottom: var(--space-lg); font-size: 1.2rem;">Order Summary</h3>
                                    
                                    <div class="summary-line" style="display: flex; justify-content: space-between; margin-bottom: var(--space-sm);">
                                        <span style="color: var(--text-secondary);">Subtotal (<?php echo $total_quantity; ?> items)</span>
                                        <span style="color: var(--text-primary); font-weight: 500;">$<?php echo number_format($total_harga, 2); ?></span>
                                    </div>
                                    
                                    <div class="summary-line" style="display: flex; justify-content: space-between; margin-bottom: var(--space-sm);">
                                        <span style="color: var(--text-secondary);">Processing Fee</span>
                                        <span style="color: var(--text-primary); font-weight: 500;">$2.99</span>
                                    </div>
                                    
                                    <div class="summary-line" style="display: flex; justify-content: space-between; margin-bottom: var(--space-sm);">
                                        <span style="color: var(--text-secondary);">Tax (11%)</span>
                                        <span style="color: var(--text-primary); font-weight: 500;">$<?php echo number_format(($total_harga + 2.99) * 0.11, 2); ?></span>
                                    </div>
                                    
                                    <div style="border-top: 1px solid var(--glass-border); padding-top: var(--space-lg); margin-bottom: var(--space-xl);">
                                        <div class="summary-line" style="display: flex; justify-content: space-between;">
                                            <span style="color: var(--text-primary); font-weight: 600; font-size: 1.1rem;">Total</span>
                                            <span style="color: var(--primary); font-weight: 700; font-size: 1.2rem;">$<?php echo number_format(($total_harga + 2.99) * 1.11, 2); ?></span>
                                        </div>
                                    </div>
                                    
                                    <form action="payment.php" method="POST" id="payment-form">
                                        <input type="hidden" name="payment_form" value="1">
                                        
                                        <button type="button" id="completePurchase" class="add-to-cart-btn" style="width: 100%; margin-bottom: var(--space-lg); justify-content: center;">
                                            <svg style="width: 16px; height: 16px; margin-right: var(--space-sm);" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"/>
                                            </svg>
                                            Complete Purchase
                                        </button>
                                    </form>
                                    
                                    <form action="payment.php" method="POST" id="payment-form" style="display: none;">
                                        <input type="hidden" name="payment_form" value="1">
                                    </form>
                                    
                                    <p style="color: var(--text-secondary); font-size: 0.85rem; text-align: center; line-height: 1.4;">
                                        By continuing, you agree to our 
                                        <a href="#" style="color: var(--primary); text-decoration: none;">Terms of Service</a> 
                                        and 
                                        <a href="#" style="color: var(--primary); text-decoration: none;">Privacy Policy</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Download Information -->
                <section class="game-sections" style="margin-top: var(--space-xl);">
                    <div class="section-header">
                        <h2 class="section-title">How It Works</h2>
                    </div>
                    
                    <div class="game-card" style="cursor: default; transform: none;">
                        <div class="game-info" style="padding: var(--space-lg);">
                            <div style="display: flex; align-items: center; gap: var(--space-md); padding: var(--space-lg); background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); border-radius: var(--radius-md);">
                                <div style="width: 48px; height: 48px; background: var(--success); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg style="width: 24px; height: 24px; color: white;" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M3,3H5V5H19V3H21V5C21,6.11 20.11,7 19,7H5C3.89,7 3,6.11 3,5V3M19,8V20H5V8H19M8,10V18H16V10H8Z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 style="color: var(--success); margin: 0 0 var(--space-xs); font-weight: 600;">Steam Key Delivery</h4>
                                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">Your Steam activation keys will be emailed instantly after purchase completion.</p>
                                </div>
                            </div>
                            
                            <div style="margin-top: var(--space-lg); padding: var(--space-md); background: rgba(139, 92, 246, 0.05); border: 1px solid rgba(139, 92, 246, 0.1); border-radius: var(--radius-md);">
                                <h5 style="color: var(--primary); margin: 0 0 var(--space-sm); font-weight: 600;">What you'll receive:</h5>
                                <ul style="margin: 0; padding-left: var(--space-lg); color: var(--text-secondary); font-size: 0.9rem; line-height: 1.6;">
                                    <li>Steam activation keys via email</li>
                                    <li>Step-by-step redemption instructions</li>
                                    <li>Access to your order history on your profile page</li>
                                    <li>Permanent ownership of the games in your Steam library</li>
                                </ul>
                            </div>
                            
                            <div style="margin-top: var(--space-lg); padding: var(--space-md); background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.1); border-radius: var(--radius-md);">
                                <h5 style="color: #3b82f6; margin: 0 0 var(--space-sm); font-weight: 600;">How to redeem on Steam:</h5>
                                <ol style="margin: 0; padding-left: var(--space-lg); color: var(--text-secondary); font-size: 0.9rem; line-height: 1.6;">
                                    <li>Open Steam and log into your account</li>
                                    <li>Click "Games" → "Activate a Product on Steam"</li>
                                    <li>Enter the activation key from your email</li>
                                    <li>Download and enjoy your new game!</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <div id="wishlist-notification" class="wishlist-notification" role="alert" aria-live="polite">
        <svg class="heart-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
        </svg>
        <span class="wishlist-message"></span>
    </div>

    <!-- Payment Confirmation Modal -->
    <div id="confirmationModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Your Purchase</h3>
                <div id="countdownTimer" class="countdown-timer">Time remaining: 30s</div>
            </div>
            
            <div class="modal-body">
                <p style="color: var(--text-secondary); margin-bottom: var(--space-lg); line-height: 1.5;">
                    Please review and confirm the following before completing your purchase:
                </p>
                
                <div class="confirmation-checkboxes">
                    <label class="checkbox-label">
                        <input type="checkbox" id="confirmCorrect" class="confirmation-checkbox">
                        <span class="checkmark"></span>
                        I confirm this is the correct game, edition, and that it is for the Steam platform.
                    </label>
                    
                    <label class="checkbox-label">
                        <input type="checkbox" id="confirmRequirements" class="confirmation-checkbox">
                        <span class="checkmark"></span>
                        I have verified the game's system requirements and that its region is compatible with my account.
                    </label>
                    
                    <label class="checkbox-label">
                        <input type="checkbox" id="confirmRefund" class="confirmation-checkbox">
                        <span class="checkmark"></span>
                        I understand and agree that this digital key is non-refundable once it has been sent.
                    </label>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" id="cancelPurchaseBtn" class="btn-secondary">Cancel</button>
                <button type="button" id="confirmPurchaseBtn" class="add-to-cart-btn" disabled>Confirm Purchase</button>
            </div>
        </div>
    </div>

    <!-- Payment Notification -->
    <div id="paymentNotification" class="payment-notification" style="display: none;">
        <span class="notification-message"></span>
    </div>

    <footer role="contentinfo">
        <div class="footer-content">
            <div class="footer-brand">
                <h3>Vault</h3>
                <p>Your ultimate destination for digital games</p>
            </div>
            <div class="footer-links">
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Community</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#" class="privacy-policy">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Refund Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Vault | Developed by Group 4 RPL</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            const toggle = document.querySelector('.mobile-menu-toggle');
            const hamburgerIcon = toggle.querySelector('.hamburger-icon');
            const isOpen = mobileMenu.classList.contains('mobile-open');
            
            if (isOpen) {
                mobileMenu.classList.remove('mobile-open');
                hamburgerIcon.innerHTML = '☰';
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = 'auto';
            } else {
                mobileMenu.classList.add('mobile-open');
                hamburgerIcon.innerHTML = '✕';
                toggle.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }
        }

        // Payment form handling
        document.addEventListener('DOMContentLoaded', function() {
            const paymentForm = document.getElementById('payment-form');
            const completePurchaseBtn = document.getElementById('completePurchase');
            const confirmationModal = document.getElementById('confirmationModal');
            const confirmPurchaseBtn = document.getElementById('confirmPurchaseBtn');
            const cancelPurchaseBtn = document.getElementById('cancelPurchaseBtn');
            const countdownTimer = document.getElementById('countdownTimer');
            const paymentNotification = document.getElementById('paymentNotification');
            const checkboxes = document.querySelectorAll('.confirmation-checkbox');
            
            let countdownInterval;
            let timeRemaining = 30;

            // Show modal function
            function showModal() {
                confirmationModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                startCountdown();
            }

            // Hide modal function
            function hideModal() {
                confirmationModal.style.display = 'none';
                document.body.style.overflow = 'auto';
                stopCountdown();
                resetModal();
            }

            // Start countdown timer
            function startCountdown() {
                timeRemaining = 30;
                updateCountdownDisplay();
                
                countdownInterval = setInterval(() => {
                    timeRemaining--;
                    updateCountdownDisplay();
                    
                    if (timeRemaining <= 0) {
                        hideModal();
                        showNotification('Payment failed');
                    }
                }, 1000);
            }

            // Stop countdown timer
            function stopCountdown() {
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                }
            }

            // Update countdown display
            function updateCountdownDisplay() {
                countdownTimer.textContent = `Time remaining: ${timeRemaining}s`;
                
                // Change color when time is running low
                if (timeRemaining <= 10) {
                    countdownTimer.style.color = '#ef4444';
                } else {
                    countdownTimer.style.color = 'var(--primary)';
                }
            }

            // Reset modal state
            function resetModal() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                confirmPurchaseBtn.disabled = true;
                timeRemaining = 30;
                countdownTimer.style.color = 'var(--primary)';
            }

            // Check if all checkboxes are checked
            function validateCheckboxes() {
                const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
                confirmPurchaseBtn.disabled = !allChecked;
            }

            // Show notification
            function showNotification(message) {
                const notificationMessage = paymentNotification.querySelector('.notification-message');
                notificationMessage.textContent = message;
                paymentNotification.style.display = 'block';
                
                // Auto-hide after 3 seconds
                setTimeout(() => {
                    paymentNotification.style.animation = 'fadeOut 0.3s ease-out';
                    setTimeout(() => {
                        paymentNotification.style.display = 'none';
                        paymentNotification.style.animation = '';
                    }, 300);
                }, 3000);
            }

            // Event listeners
            completePurchaseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                showModal();
            });

            cancelPurchaseBtn.addEventListener('click', function() {
                hideModal();
            });

            confirmPurchaseBtn.addEventListener('click', function() {
                if (!this.disabled) {
                    stopCountdown();
                    paymentForm.submit();
                }
            });

            // Checkbox validation
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', validateCheckboxes);
            });

            // Close modal when clicking outside
            confirmationModal.addEventListener('click', function(e) {
                if (e.target === confirmationModal) {
                    hideModal();
                }
            });

            // Keyboard support
            document.addEventListener('keydown', function(e) {
                if (confirmationModal.style.display === 'flex') {
                    if (e.key === 'Escape') {
                        hideModal();
                    }
                }
            });
            
            // Payment form submission with loading state (legacy support)
            paymentForm.addEventListener('submit', function(e) {
                const submitBtn = confirmPurchaseBtn;
                const originalContent = submitBtn.innerHTML;
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg style="width: 16px; height: 16px; margin-right: var(--space-sm); animation: spin 1s linear infinite;" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,4V2A10,10 0 0,0 2,12H4A8,8 0 0,1 12,4Z"/>
                    </svg>
                    Processing...
                `;
                
                // If there's an error, restore the button (this is just a fallback, normally PHP handles the redirect)
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalContent;
                }, 10000);
            });
        });
    </script>

    <style>
        /* Payment-specific responsive styles */
        @media (max-width: 768px) {
            .payment-layout {
                padding: 0 var(--space-md) !important;
            }
            
            /* Stack order items and summary vertically on mobile */
            div[style*="display: grid; grid-template-columns: 1fr 400px"] {
                display: block !important;
            }
            
            .payment-sidebar {
                position: static !important;
                margin-top: var(--space-lg) !important;
            }
            
            .breadcrumb {
                font-size: 0.9rem;
            }
            
            .game-info div[style*="display: flex; gap: var(--space-lg)"] {
                flex-direction: column !important;
                gap: var(--space-md) !important;
            }
        }

        @media (max-width: 480px) {
            .game-info div[style*="display: flex; gap: var(--space-lg)"] {
                flex-direction: column !important;
                gap: var(--space-md) !important;
            }
            
            .game-info img[style*="width: 80px"] {
                width: 60px !important;
                height: 80px !important;
                align-self: flex-start !important;
            }
            
            /* Ensure order summary is full width on small screens */
            div[style*="display: grid; grid-template-columns: 1fr 400px"] {
                display: block !important;
            }
        }

        /* Custom payment styles */
        .breadcrumb {
            font-size: 0.95rem;
            font-weight: 500;
        }

        .summary-line {
            font-size: 0.95rem;
        }

        .summary-line:last-child span {
            font-size: 1.1rem;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow: hidden;
            animation: modalSlideIn 0.3s ease-out;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: var(--space-lg) var(--space-xl);
            border-bottom: 1px solid var(--glass-border);
            text-align: center;
            flex-shrink: 0;
        }

        .modal-header h3 {
            color: var(--text-primary);
            margin: 0 0 var(--space-sm) 0;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .countdown-timer {
            color: var(--primary);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .modal-body {
            padding: var(--space-lg) var(--space-xl);
            flex: 1;
            overflow: hidden;
        }

        .modal-body p {
            color: var(--text-secondary);
            margin-bottom: var(--space-md);
            line-height: 1.4;
            font-size: 0.9rem;
        }

        .confirmation-checkboxes {
            display: flex;
            flex-direction: column;
            gap: var(--space-md);
        }

        .checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: var(--space-sm);
            cursor: pointer;
            color: var(--text-primary);
            line-height: 1.4;
            font-size: 0.9rem;
        }

        .checkbox-label input[type="checkbox"] {
            display: none;
        }

        .checkmark {
            width: 18px;
            height: 18px;
            border: 2px solid var(--glass-border);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s ease;
            margin-top: 2px;
        }

        .checkbox-label input[type="checkbox"]:checked + .checkmark {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .checkbox-label input[type="checkbox"]:checked + .checkmark::after {
            content: '✓';
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .modal-footer {
            padding: var(--space-lg) var(--space-xl);
            border-top: 1px solid var(--glass-border);
            display: flex;
            gap: var(--space-md);
            justify-content: flex-end;
            flex-shrink: 0;
        }

        .btn-secondary {
            padding: 12px 24px;
            background: transparent;
            border: 1px solid var(--glass-border);
            color: var(--text-secondary);
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
            min-width: 140px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
        }

        .modal-footer .add-to-cart-btn {
            min-width: 140px;
            height: 44px;
            transform: translateY(-16px);
        }

        .payment-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ef4444;
            border: 1px solid #dc2626;
            border-radius: var(--radius-md);
            padding: var(--space-lg);
            color: white;
            font-weight: 500;
            z-index: 1001;
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        /* Animations */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Empty cart state */
        .empty-cart {
            text-align: center;
            padding: var(--space-2xl);
            color: var(--text-secondary);
        }

        .empty-cart svg {
            width: 64px;
            height: 64px;
            margin-bottom: var(--space-lg);
            opacity: 0.5;
        }
    </style>
</body>

</html>