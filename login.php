<?php
session_start();
if (isset($_GET['logout'])) {
    unset($_SESSION['loggedin']);
    unset($_SESSION['containers']);
    unset($_SESSION['id']);
    header("Location:index.html");
    exit;
}
if (isset($_POST['email']) && $_POST['password']) {
    $email = htmlspecialchars($_POST['email']);
    $password = md5($_POST['password']);
    include_once 'db_connect.php';
    $result = mysql_query("SELECT * from users where email='$email'");
    $row = mysql_fetch_assoc($result);
    
    if (mysql_num_rows($result) > 0) {
        //user is registered
        if ($row['password'] == "$password") {
            $_SESSION['loggedin'] = $email;
            $_SESSION['id'] = $row['Id'];
            $containers = array();
            $cont = mysql_query("SELECT * from containers where user_id=".$row['Id']);
            if (mysql_num_rows($cont) > 0) {
                while ($c = mysql_fetch_assoc($cont)) {
                    $containers[] = $c;
                }
            }
            $_SESSION['containers'] = $containers;
            mysql_close($link);
            header("Location:home.php");
            exit;
        } else {
            $error="wrong password! Try again.".$row['password'];
        }
    } else {
         //user not registered
            mysql_query("INSERT INTO users (email, password) values('$email', '$password')");
            $_SESSION['loggedin'] = $email;
            $_SESSION['containers'] = array();
            $resid = mysql_query("SELECT Id from users where email='$email'");
            $_SESSION['id'] = mysql_fetch_assoc($resid)['Id'];
            mysql_close($link);
            header("Location:home.php");
            exit;
    }
    mysql_close($link);
    header("Location:index.html?err=$error");
} else {
    header("Location:home.php");
}
?>
