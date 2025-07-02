<?php   
    require  "koneksi.php";

$usernameError = '';    
$emailError = '';
$passwordError = '';
$successMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['regisbtn'])) {
    $username        = $_POST['username'];
    $email           = $_POST['email'];
    $password        = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];

   
    $sqlCheck = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $con->prepare($sqlCheck);
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $isUsernameTaken = false;
    $isEmailTaken = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['username'] == $username) {
            $isUsernameTaken = true;
        }
        if ($row['email'] == $email) {
            $isEmailTaken = true;
        }
    }

    if ($isUsernameTaken) {
        $usernameError = 'Username telah terdaftar, silahkan masukkan username lain.';
    } elseif ($isEmailTaken) {
        $emailError = 'Email telah terdaftar, silahkan masukkan email lain.';
    } elseif ($password !== $confirmPassword) {
        $passwordError = 'Password tidak cocok, silahkan ulangi.';
    } else {
        
        $sqlInsert = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmtInsert = $con->prepare($sqlInsert);
        $role = 'user';
        $stmtInsert->bind_param('ssss', $username, $email, $password, $role);
        
        if ($stmtInsert->execute()) {
            $successMessage = 'Akun berhasil di registrasi, silahkan login.';
            
        } else {
            echo "Error: " . $stmtInsert->error;
        }

        $stmtInsert->close();  
    }

    $stmt->close(); 
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Vault Digital Store</title>
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
            
            <div class="nav-icons">
                <div class="nav-icon">
                    <a href="login.php" class="auth-btn">
                        Sign In
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="main-content" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: var(--space-xl);">
        <div class="register-container" style="width: 100%; max-width: 480px;">
            <div class="game-card" style="cursor: default; transform: none;">
                <div class="game-info" style="padding: var(--space-2xl); text-align: center;">
                    <div style="margin-bottom: var(--space-2xl);">
                        <h1 style="color: var(--text-primary); font-size: 2rem; font-weight: 700; margin-bottom: var(--space-sm);">Create Account</h1>
                        <p style="color: var(--text-secondary); font-size: 1rem;">Join Vault Digital Store today</p>
                    </div>

                    <?php if ($successMessage != ''): ?>
                        <div class="notification success-notification" style="position: relative; transform: none; margin-bottom: var(--space-lg); background: var(--success); color: white; padding: var(--space-md); border-radius: var(--radius-lg); display: flex; align-items: center; gap: var(--space-sm);">
                            <svg style="width: 20px; height: 20px; flex-shrink: 0;" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"/>
                            </svg>
                            <span><?= htmlspecialchars($successMessage); ?></span>
                            <meta http-equiv="refresh" content="3; url=login.php"/>
                        </div>
                    <?php endif; ?>

                    <form action="#" method="POST" style="text-align: left;">
                        <div style="margin-bottom: var(--space-lg);">
                            <label style="display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--space-sm); font-weight: 500;">Username</label>
                            <input type="text" id="username" name="username" 
                                   class="search-input" style="width: 100%; margin: 0;" 
                                   placeholder="Choose a username" required>
                            
                            <?php if ($usernameError != ''): ?>
                                <div class="error-message" style="margin-top: var(--space-xs); padding: var(--space-sm); background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); border-radius: var(--radius-sm); color: var(--danger); font-size: 0.85rem; display: flex; align-items: center; gap: var(--space-xs);">
                                    <svg style="width: 16px; height: 16px; flex-shrink: 0;" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12,2C17.53,2 22,6.47 22,12C22,17.53 17.53,22 12,22C6.47,22 2,17.53 2,12C2,6.47 6.47,2 12,2M15.59,7L12,10.59L8.41,7L7,8.41L10.59,12L7,15.59L8.41,17L12,13.41L15.59,17L17,15.59L13.41,12L17,8.41L15.59,7Z"/>
                                    </svg>
                                    <span><?= htmlspecialchars($usernameError); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="margin-bottom: var(--space-lg);">
                            <label style="display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--space-sm); font-weight: 500;">Email Address</label>
                            <input type="email" id="email" name="email" 
                                   class="search-input" style="width: 100%; margin: 0;" 
                                   placeholder="Enter your email address" required>
                            
                            <?php if ($emailError != ''): ?>
                                <div class="error-message" style="margin-top: var(--space-xs); padding: var(--space-sm); background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); border-radius: var(--radius-sm); color: var(--danger); font-size: 0.85rem; display: flex; align-items: center; gap: var(--space-xs);">
                                    <svg style="width: 16px; height: 16px; flex-shrink: 0;" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12,2C17.53,2 22,6.47 22,12C22,17.53 17.53,22 12,22C6.47,22 2,17.53 2,12C2,6.47 6.47,2 12,2M15.59,7L12,10.59L8.41,7L7,8.41L10.59,12L7,15.59L8.41,17L12,13.41L15.59,17L17,15.59L13.41,12L17,8.41L15.59,7Z"/>
                                    </svg>
                                    <span><?= htmlspecialchars($emailError); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="margin-bottom: var(--space-lg);">
                            <label style="display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--space-sm); font-weight: 500;">Password</label>
                            <input type="password" id="password" name="password" 
                                   class="search-input" style="width: 100%; margin: 0;" 
                                   placeholder="Create a strong password" required>
                        </div>

                        <div style="margin-bottom: var(--space-xl);">
                            <label style="display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--space-sm); font-weight: 500;">Confirm Password</label>
                            <input type="password" id="confirm-password" name="confirm-password" 
                                   class="search-input" style="width: 100%; margin: 0;" 
                                   placeholder="Confirm your password" required>
                            
                            <?php if ($passwordError != ''): ?>
                                <div class="error-message" style="margin-top: var(--space-xs); padding: var(--space-sm); background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); border-radius: var(--radius-sm); color: var(--danger); font-size: 0.85rem; display: flex; align-items: center; gap: var(--space-xs);">
                                    <svg style="width: 16px; height: 16px; flex-shrink: 0;" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12,2C17.53,2 22,6.47 22,12C22,17.53 17.53,22 12,22C6.47,22 2,17.53 2,12C2,6.47 6.47,2 12,2M15.59,7L12,10.59L8.41,7L7,8.41L10.59,12L7,15.59L8.41,17L12,13.41L15.59,17L17,15.59L13.41,12L17,8.41L15.59,7Z"/>
                                    </svg>
                                    <span><?= htmlspecialchars($passwordError); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" name="regisbtn" class="auth-submit-btn" style="width: 100%; margin-bottom: var(--space-lg);">
                            <svg style="width: 16px; height: 16px; margin-right: var(--space-sm);" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                            </svg>
                            Create Account
                        </button>

                        <div style="text-align: center;">
                            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                                Already have an account? <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 500;">Sign in</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer role="contentinfo" style="background: var(--bg-glass); border-top: 1px solid var(--glass-border); padding: var(--space-lg) 0; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 var(--space-lg);">
            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                &copy; 2025 Vault | Developed by Group 4 RPL | 
                <a href="#" style="color: var(--primary); text-decoration: none;">Privacy Policy</a>
            </p>
        </div>
    </footer>

    <style>
        .success-notification,
        .error-message {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Password strength indicator */
        #password:focus + .password-strength {
            display: block;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .main-content {
                padding: var(--space-lg) var(--space-md);
            }
            
            .game-info {
                padding: var(--space-xl) !important;
            }
        }
    </style>
</body>
</html>
