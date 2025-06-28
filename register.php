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
        
        $sqlInsert = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmtInsert = $con->prepare($sqlInsert);
        $stmtInsert->bind_param('sss', $username, $email, $password);
        
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
    <title>Register Page</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.png">
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    
</head>
<body>
    <header>
        <a href="#" class="logo">
            <img src="image/logo white.png" alt="Logo" />
        </a>
        <nav>
            <a href="#" class="nav-link">Home</a>
            <a href="login.php" class="nav-link register-button">Login</a>
        </nav>
    </header>
    
    <main>
        <div class="login-container">
            <h2>Register</h2>
            <p>Sudah punya akun? <a href="login.php" class="register-link">Login</a></p>

           
            <?php if ($successMessage != ''): ?>
                <div class="message-box success-message-box">
                    <?= htmlspecialchars($successMessage); ?>
                    <meta http-equiv="refresh" content ="2; url=login.php"/>
                </div>
            <?php endif; ?>

            <form action="#" method="POST">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
                
                <?php if ($usernameError != ''): ?>
                    <div class="message-box error-message-box">
                        <?= htmlspecialchars($usernameError); ?>
                    </div>
                <?php endif; ?>
                
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
               
                <?php if ($emailError != ''): ?>
                    <div class="message-box error-message-box">
                        <?= htmlspecialchars($emailError); ?>
                    </div>
                <?php endif; ?>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm-password" required>
               
                <?php if ($passwordError != ''): ?>
                    <div class="message-box error-message-box">
                        <?= htmlspecialchars($passwordError); ?>
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="sign-in-button" name="regisbtn">Register</button>
            </form>
        </div>
    </main>
    
    <footer>
        <p>&copy; Kelompok 1 | PPW | <a href="#" class="privacy-policy">Privacy Policy</a></p>
    </footer>
</body>
</html>
