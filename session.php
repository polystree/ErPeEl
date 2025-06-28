<?php
    session_start();
    if($_SESSION['loginbtn'] == false){
        header('location: login.php');
        exit();
    }

?>