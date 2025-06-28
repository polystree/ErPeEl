<?php  
   session_start();
   require "koneksi.php";     
   require_once 'vendor/autoload.php';
   

   $clientID = '1031284088121-ognjp4jh2u43henjbunau110tt8q79ho.apps.googleusercontent.com';
   $clientSecret = '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';
   $redirectUri = 'http://localhost/ppw/login.php';
   
   $client = new Google_Client();
   $client->setClientId($clientID);
   $client->setClientSecret($clientSecret);
   $client->setRedirectUri($redirectUri);
   $client->addScope("email");
   $client->addScope("profile");
   
   if (isset($_GET['code'])) {
       $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
       $client->setAccessToken($token['access_token']);
   
        
       $google_oauth = new Google_Service_Oauth2($client);
       $google_account_info = $google_oauth->userinfo->get();
       $email = $google_account_info->email;
       $name = $google_account_info->name;
   
       
       $sql_check = "SELECT * FROM users WHERE email=?";
       $stmt_check = $con->prepare($sql_check);
       $stmt_check->bind_param("s", $email);
       $stmt_check->execute();
       $result_check = $stmt_check->get_result();
       $count = $result_check->num_rows;
   
       if ($count == 0) {
           
           $sql_insert = "INSERT INTO users (username, email, password) VALUES (?, ?, '')"; 
           $stmt_insert = $con->prepare($sql_insert);
           $stmt_insert->bind_param("ss", $name, $email);
       
           if ($stmt_insert->execute()) {
               
               $user_id = $stmt_insert->insert_id; 
           } else {
               die("Insert failed: " . $stmt_insert->error);
           }
       } else {
           
           $row = $result_check->fetch_assoc(); 
           $user_id = $row['id']; 
           $name = $row['username'];  
       }
   
       $_SESSION['user_id'] = $user_id; 
       $_SESSION['loginbtn'] = true;
       $_SESSION['username'] = $name;
       $_SESSION['email'] = $email;
   
       
       header("Location: dashboard.php");
       exit();
   }       
    
        


    $loginError = '';
    $captchaError = '';
    $rand = rand(1000, 9999); 
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['loginbtn'])) {
        $usernameOrEmail = $_POST['usernameemail'];
        $password = $_POST['password'];
        $captcha = $_POST['captcha'];
        $captcharandom = $_POST['captcharandom'];
        $isUserValid = false;
    
        $sql = "SELECT id, username, email, password FROM users WHERE username = ? OR email = ?";
        $stmt = $con->prepare($sql);
        
        if ($stmt === false) {
            die('Prepare failed: ' . $con->error);
        }
    
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result === false) {
            die('Execute failed: ' . $stmt->error);
        }
    
        if ($row = $result->fetch_assoc()) {
            
            if (($password == $row['password'])) {
                if ($captcha == $captcharandom) {
                    $isUserValid = true;
                } else {
                    $captchaError = 'Captcha salah, silakan coba lagi.';
                }
            }
        }
        $stmt->close();
    
        if ($isUserValid) {
            $_SESSION['user_id'] = $row['id']; 
            $_SESSION['loginbtn'] = true;
            $_SESSION['username'] = $row['username']; 
            $_SESSION['email'] = $row['email'];
    
    
            if ($row['username'] == 'admin' || $row['email'] == 'admin@gmail.com') {
                header("Location: admin.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            if (empty($captchaError)) {
                $loginError = 'Username/email atau password tidak valid.';
            }
        }
    }
    
    
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vault Digital Store</title>
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
                    <a href="register.php" class="auth-btn">
                        Register
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="main-content" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: var(--space-xl);">
        <div class="login-container" style="width: 100%; max-width: 480px;">
            <div class="game-card" style="cursor: default; transform: none;">
                <div class="game-info" style="padding: var(--space-2xl); text-align: center;">
                    <div style="margin-bottom: var(--space-2xl);">
                        <h1 style="color: var(--text-primary); font-size: 2rem; font-weight: 700; margin-bottom: var(--space-sm);">Welcome Back</h1>
                        <p style="color: var(--text-secondary); font-size: 1rem;">Sign in to your Vault account</p>
                    </div>

                    <?php if ($loginError != ''): ?>
                        <div class="notification error-notification" style="position: relative; transform: none; margin-bottom: var(--space-lg); background: var(--danger); color: white; padding: var(--space-md); border-radius: var(--radius-lg); display: flex; align-items: center; gap: var(--space-sm);">
                            <svg style="width: 20px; height: 20px; flex-shrink: 0;" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,2C17.53,2 22,6.47 22,12C22,17.53 17.53,22 12,22C6.47,22 2,17.53 2,12C2,6.47 6.47,2 12,2M15.59,7L12,10.59L8.41,7L7,8.41L10.59,12L7,15.59L8.41,17L12,13.41L15.59,17L17,15.59L13.41,12L17,8.41L15.59,7Z"/>
                            </svg>
                            <span><?= htmlspecialchars($loginError); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($captchaError != ''): ?>
                        <div class="notification error-notification" style="position: relative; transform: none; margin-bottom: var(--space-lg); background: var(--danger); color: white; padding: var(--space-md); border-radius: var(--radius-lg); display: flex; align-items: center; gap: var(--space-sm);">
                            <svg style="width: 20px; height: 20px; flex-shrink: 0;" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,2C17.53,2 22,6.47 22,12C22,17.53 17.53,22 12,22C6.47,22 2,17.53 2,12C2,6.47 6.47,2 12,2M15.59,7L12,10.59L8.41,7L7,8.41L10.59,12L7,15.59L8.41,17L12,13.41L15.59,17L17,15.59L13.41,12L17,8.41L15.59,7Z"/>
                            </svg>
                            <span><?= htmlspecialchars($captchaError); ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="#" method="POST" style="text-align: left;">
                        <div style="margin-bottom: var(--space-lg);">
                            <label style="display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--space-sm); font-weight: 500;">Username or Email</label>
                            <input type="text" id="Usernameemail" name="usernameemail" 
                                   class="search-input" style="width: 100%; margin: 0;" 
                                   placeholder="Enter your username or email" required>
                        </div>

                        <div style="margin-bottom: var(--space-lg);">
                            <label style="display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--space-sm); font-weight: 500;">Password</label>
                            <input type="password" id="password" name="password" 
                                   class="search-input" style="width: 100%; margin: 0;" 
                                   placeholder="Enter your password" required>
                        </div>

                        <div style="margin-bottom: var(--space-lg);">
                            <label style="display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--space-sm); font-weight: 500;">Security Code</label>
                            <div style="display: flex; gap: var(--space-md); align-items: center;">
                                <div class="captcha-display" style="background: linear-gradient(135deg, var(--bg-glass), rgba(139, 92, 246, 0.1)); border: 2px solid var(--glass-border); border-radius: var(--radius-md); padding: var(--space-md); text-align: center; font-family: 'Orbitron', monospace; font-size: 1.2rem; font-weight: 700; color: var(--primary); letter-spacing: 4px; position: relative; overflow: hidden; user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; pointer-events: none; cursor: not-allowed; min-width: 120px; flex-shrink: 0;">
                                    <!-- Multiple overlay patterns to obscure the code -->
                                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: repeating-linear-gradient(45deg, transparent, transparent 2px, rgba(139, 92, 246, 0.15) 2px, rgba(139, 92, 246, 0.15) 4px); pointer-events: none; z-index: 1;"></div>
                                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: repeating-linear-gradient(-45deg, transparent, transparent 1px, rgba(6, 214, 160, 0.1) 1px, rgba(6, 214, 160, 0.1) 2px); pointer-events: none; z-index: 2;"></div>
                                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: radial-gradient(circle at 30% 40%, rgba(255, 255, 255, 0.05) 1px, transparent 1px), radial-gradient(circle at 70% 60%, rgba(255, 255, 255, 0.05) 1px, transparent 1px); background-size: 15px 15px; pointer-events: none; z-index: 3;"></div>
                                    <span style="position: relative; z-index: 4; text-shadow: 0 0 10px rgba(139, 92, 246, 0.7), 0 0 20px rgba(139, 92, 246, 0.3); transform: skew(-3deg); display: inline-block;"><?php echo $rand; ?></span>
                                    <!-- Noise overlay -->
                                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg viewBox=\'0 0 100 100\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cfilter id=\'noiseFilter\'%3E%3CfeTurbulence type=\'fractalNoise\' baseFrequency=\'0.9\' numOctaves=\'1\' stitchTiles=\'stitch\'/%3E%3C/filter%3E%3Crect width=\'100%25\' height=\'100%25\' filter=\'url(%23noiseFilter)\' opacity=\'0.1\'/%3E%3C/svg%3E'); pointer-events: none; z-index: 5;"></div>
                                </div>
                                <input type="text" id="captcha" name="captcha" 
                                       class="search-input" style="flex: 1; margin: 0;" 
                                       placeholder="Enter the security code" required>
                            </div>
                            <input type="hidden" id="captcharandom" name="captcharandom" value="<?php echo $rand; ?>">
                        </div>

                        <button type="submit" name="loginbtn" class="auth-submit-btn" style="width: 100%; margin-bottom: var(--space-lg);">
                            <svg style="width: 16px; height: 16px; margin-right: var(--space-sm);" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M10,17V14H3V10H10V7L15,12L10,17M10,2H19A2,2 0 0,1 21,4V20A2,2 0 0,1 19,22H10A2,2 0 0,1 8,20V18H10V20H19V4H10V6H8V4A2,2 0 0,1 10,2Z"/>
                            </svg>
                            Sign In
                        </button>

                        <div style="text-align: center; margin-bottom: var(--space-lg);">
                            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                                Don't have an account? <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 500;">Create one</a>
                            </p>
                        </div>

                        <div style="text-align: center; margin: var(--space-lg) 0; position: relative;">
                            <div style="height: 1px; background: var(--glass-border); margin: var(--space-md) 0;"></div>
                            <span style="background: var(--bg-primary); padding: 0 var(--space-md); color: var(--text-secondary); font-size: 0.9rem; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">or continue with</span>
                        </div>

                        <a href="<?php echo $client->createAuthUrl(); ?>" class="google-button" style="display: flex; align-items: center; justify-content: center; gap: var(--space-sm); width: 100%; padding: var(--space-md); border: 2px solid var(--glass-border); border-radius: var(--radius-md); background: var(--bg-glass); color: var(--text-primary); text-decoration: none; font-weight: 500; transition: var(--transition-fast); backdrop-filter: var(--glass-blur);">
                            <img src="https://cdn1.iconfinder.com/data/icons/google-s-logo/150/Google_Icons-09-512.png" alt="Google Logo" style="width: 20px; height: 20px;" />
                            <span>Continue with Google</span>
                        </a>
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
        .google-button:hover {
            background: var(--bg-glass-hover);
            border-color: var(--primary);
            transform: translateY(-2px);
        }
        
        .error-notification {
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
        
        /* Enhanced Captcha Security */
        .captcha-display {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
            pointer-events: none !important;
            cursor: not-allowed !important;
            position: relative;
        }
        
        .captcha-display::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, 
                transparent 0%, 
                rgba(139, 92, 246, 0.1) 25%, 
                transparent 50%, 
                rgba(6, 214, 160, 0.1) 75%, 
                transparent 100%);
            animation: shimmer 2s infinite;
            z-index: 6;
            pointer-events: none;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .captcha-display span {
            position: relative;
            z-index: 4;
            display: inline-block;
            animation: subtle-glow 3s ease-in-out infinite alternate;
        }
        
        @keyframes subtle-glow {
            0% { 
                text-shadow: 0 0 10px rgba(139, 92, 246, 0.7), 0 0 20px rgba(139, 92, 246, 0.3);
                filter: hue-rotate(0deg);
            }
            100% { 
                text-shadow: 0 0 15px rgba(139, 92, 246, 0.9), 0 0 25px rgba(139, 92, 246, 0.5);
                filter: hue-rotate(8deg);
            }
        }
        
        /* Prevent right-click context menu on captcha */
        .captcha-display {
            -webkit-context-menu: none;
            -moz-context-menu: none;
            context-menu: none;
        }
        
        /* Disable drag and drop */
        .captcha-display * {
            -webkit-user-drag: none;
            -khtml-user-drag: none;
            -moz-user-drag: none;
            -o-user-drag: none;
            user-drag: none;
            pointer-events: none;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .main-content {
                padding: var(--space-lg) var(--space-md);
            }
            
            .game-info {
                padding: var(--space-xl) !important;
            }
            
            .captcha-display {
                font-size: 1rem !important;
                letter-spacing: 3px !important;
                min-width: 100px !important;
            }
            
            /* Stack captcha vertically on mobile */
            div[style*="display: flex; gap: var(--space-md); align-items: center;"] {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: var(--space-sm) !important;
            }
            
            .captcha-display {
                align-self: center !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const captchaDisplay = document.querySelector('.captcha-display');
            
            if (captchaDisplay) {
                // Prevent various copying methods
                captchaDisplay.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    return false;
                });
                
                captchaDisplay.addEventListener('selectstart', function(e) {
                    e.preventDefault();
                    return false;
                });
                
                captchaDisplay.addEventListener('dragstart', function(e) {
                    e.preventDefault();
                    return false;
                });
                
                // Prevent keyboard shortcuts
                captchaDisplay.addEventListener('keydown', function(e) {
                    if (e.ctrlKey && (e.key === 'c' || e.key === 'a' || e.key === 'x' || e.key === 'v')) {
                        e.preventDefault();
                        return false;
                    }
                    if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
                        e.preventDefault();
                        return false;
                    }
                });
                
                // Additional protection against selection
                setInterval(function() {
                    if (window.getSelection) {
                        const selection = window.getSelection();
                        if (selection.rangeCount > 0) {
                            const range = selection.getRangeAt(0);
                            if (captchaDisplay.contains(range.commonAncestorContainer)) {
                                selection.removeAllRanges();
                            }
                        }
                    }
                }, 100);
            }
        });
    </script>
</body>
</html>
