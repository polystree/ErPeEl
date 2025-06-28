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
    <title>Login Page</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.png">
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
 

        
</head>

<body>
    <header>
        <div class="logo">
        <a href="#">
            <img src="image/logo white.png" alt="Logo" />
        </a>
        </div>
        <nav>
            <a href="dashboard.php" class="nav-link">Home</a>
            <a href="register.php" class="nav-link register-button">Register</a>
        </nav>
    </header>
    
    <main>
        <div class="login-container">
            <h2>Login</h2>
            <p>Belum punya akun? <a href="register.php" class="register-link">Register</a></p>

           
            <?php if ($loginError != ''): ?>
                <div class="error-message-box">
                    <?= htmlspecialchars($loginError); ?>
                </div>
            <?php endif; ?>
            
            
            <?php if ($captchaError != ''): ?>
                <div class="error-message-box">
                    <?= htmlspecialchars($captchaError); ?>
                </div>
            <?php endif; ?>

            <form action="#" method="POST">
                <label for="Usernameemail">Username/Email</label>
                <input type="text" id="Usernameemail" name="usernameemail" required>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

               
                <label for="captcha-code" innert>Captcha</label>
                <div class="captcha-box"><?php echo $rand; ?></div>

                <label for="captcha">Masukkan Captcha</label>
                <input type="text" id="captcha" name="captcha" required>
                <input type="hidden" id="captcharandom" name="captcharandom" value="<?php echo $rand; ?>">

                <button type="submit" class="sign-in-button" name="loginbtn">Login</button>

                <div class="or-divider">
                <span>atau login dengan</span>
                </div>


                <div> 
                    <a href="<?php echo $client->createAuthUrl(); ?>" class="google-button">
                        <img src="https://cdn1.iconfinder.com/data/icons/google-s-logo/150/Google_Icons-09-512.png" alt="Google Logo" />
                        <span>Google</span>
                    </a>
                </div>     
                
            </form>
        </div>
    </main>
</body>

    <footer>
        <p>&copy; Kelompok 1 | PPW | <a href="#" class="privacy-policy">Privacy Policy</a></p>
    </footer>
</html>
