<?php
session_start();
$error = null;
// pdo
if(isset($_SESSION['id'])){

}else{
    if(isset($_POST['submitLogin'])){
        $usernameConnect = htmlspecialchars($_POST['usernameLogin']);
        $pwdConnect = $_POST['pwdLogin'];
    
        if(!empty($_POST['usernameLogin']) && !empty($_POST['pwdLogin'])){
            $checkBDuser = $db->prepare("SELECT * FROM user WHERE username = ?");
            $checkBDuser->execute(array($usernameConnect));
            $checkExist = $checkBDuser->rowCount();
            if($checkExist === 1){
                $userInfo = $checkBDuser->fetch();
                if(password_verify($pwdConnect, $userInfo['password'])){
                    $_SESSION['username'] = $userInfo['username'];
                    $_SESSION['id'] = $userInfo['id'];
                    $_SESSION['email'] = $userInfo['email'];
                    $_SESSION['pwd'] = $userInfo['password'];
                    header("location: ../index.php");
                    exit();
                }else{
                    $error = "wrong password";
                }
                
            }else{
                $error = "this username don't exist";
            }
        }else{
            $error = "empty field";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOG IN</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" integrity="sha384-HzLeBuhoNPvSl5KYnjx0BT+WB0QEEqLprO+NBkkk5gbc67FTaL7XIGa2w1L0Xbgc" crossorigin="anonymous">
    <!-- <link rel="stylesheet" href="../assets/css/style.css"> -->
</head>
<body>
    <?php 
        include("header.php");
    ?>
    <?php 
        if(isset($_SESSION['id'])){
            ?>
            <p>You're already log in <?php echo $_SESSION['username']?> ...</p>
            <?php
        }else{
            ?>
            <div class="container mt-5">
                <h2 class="text-info">Log into your account</h2>
                <div class="jumbotron">
                    <form method="POST">
                    <div class="form-group">
                        <label for="usernameLogin">Username</label>
                        <input type="text" class="form-control" name="usernameLogin" placeholder="Your username">
                    </div>
                    <div class="form-group">
                        <label for="usernameLogin">Password</label>
                        <input type="password" class="form-control" name="pwdLogin" placeholder="Your password">
                    </div>
                    <input type="submit" class="btn btn-info" class="form-control" name="submitLogin" value="log in" id="loginSubmit">
                        
                    </form>
            </div>
        <?php 
                if(isset($error)){
                    ?>
                    <div class="alert alert-dismissible alert-danger">
                            <strong>Oh snap!</strong> <?php echo $error?>
                    </div>

                    <?php
                }
            ?>
    </div>
            <?php
        }
    ?>
    
</body>
</html>