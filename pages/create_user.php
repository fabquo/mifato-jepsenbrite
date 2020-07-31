<?php
session_start();
// VARIABLE POUR MESSAGE D ERREUR
$error = null;
$good = null;
// pdo


// SI LE BOUTON CREATEUSER EST CLIQUER
if(isset($_POST['createUser'])){
    // CONVERSION DES VARIABLE POUR SECURISER BD
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);
        $email2 = htmlspecialchars($_POST['confirmEmail']);
        $pwd = password_hash($_POST['pwd'], PASSWORD_BCRYPT);
        $pwd2 = $_POST['confirmPWD'];
        // password_verify($pwd2, $pwd)


        if(!empty($_POST['username']) AND !empty($_POST['email']) AND !empty($_POST['pwd'])){
            //VERIF SI PSEUDO DEJA UTILISER
            $verifUsername = $db->prepare("SELECT * FROM user WHERE username = ?");
            $verifUsername->execute(array($username));
            $usernameExist = $verifUsername->rowCount();
            if($usernameExist === 0){
                if($email === $email2){
                    if(filter_var($email,FILTER_VALIDATE_EMAIL)){
                        // VERIF SI EMAIL DEJA UTILISER
                            $verifMail = $db->prepare("SELECT * FROM user WHERE mail = ?");
                            $verifMail->execute(array($email));
                            $mailExist = $verifMail->rowCount();
                            if($mailExist === 0){
                                if(password_verify($pwd2, $pwd)){
                                    $addDB = $db->prepare("INSERT INTO user (username, email, password) VALUES ( :username, :email, :pwd)"); 
                                    $addDB->bindParam('username',$username);
                                    $addDB->bindParam('email', $email);
                                    $addDB->bindParam('pwd', $pwd);
                                    $addDB->execute();
                                    header("location: login.php");
                                    //SEND EMAIL ************************
                                    // $from = new SendGrid\Email(null, "marino.michael.1990@gmail.com");
                                    // $subject = "Hello ".$username."! Welcome to JepsenBrite !";
                                    // $to = new SendGrid\Email(null, $email);
                                    // $content = new SendGrid\Content("text/plain", "Hello, you're now offcialy a member of the JepsenBrite community!");
                                    // $mail = new SendGrid\Mail($from, $subject, $to, $content);

                                    // $apiKey = getenv('SENDGRID_API_KEY');
                                    // $sg = new \SendGrid($apiKey);

                                    // $response = $sg->client->mail()->send()->post($mail);
                                    // echo $response->statusCode();
                                    // echo $response->headers();
                                    // echo $response->body();
                                    // ********************************
                                    $good = "account created";
                                }else{
                                    $error = "Passwords doesn't match";
                                }
                            }else{
                                $error = "E-Mail already used";
                            }
                    }else{
                        $error = "Invalid E-Mail adress";
                    }
                }else{
                    $error = "E-Mails doesn't match";
                }
            }else{
                $error =  "Username already used";
            }
        }else{
            $error = "Tous les champs ne sont pas remplis ...";
        }
}
       
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" integrity="sha384-HzLeBuhoNPvSl5KYnjx0BT+WB0QEEqLprO+NBkkk5gbc67FTaL7XIGa2w1L0Xbgc" crossorigin="anonymous">
    <!-- <link rel="stylesheet" href="../assets/css/style.css"> -->
</head>
<body>
    <?php include('header.php')?>
    <div class="container mt-5">
        <h2 class="text-warning">Create an account</h2>
        <div class="jumbotron">

        
            <form method="POST">
            <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" name="username" id="username">
            </div>
            <div class="form-group">
                <label for="email">E-Mail</label>
                <input type="email" class="form-control" name="email" id="email">
            </div>
            <div class="form-group">
                <label for="confirmEmail">Confirm E-Mail</label>
                <input type="email" class="form-control" name="confirmEmail" id="confirmEmail">
            </div>
            <div class="form-group">
                <label for="pwd">Create a Password</label>
                <input type="password" class="form-control" name="pwd" id="pwd">
            </div>
            <div class="form-group">
                <label for="confirmPWD">Confirm password</label>
                <input type="password" class="form-control" name="confirmPWD" id="confirmPWD">
            </div>    

                <input type="submit" class="btn btn-warning" name="createUser" value="Create your account">
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

</body>
</html>