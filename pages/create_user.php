<?php
session_start();
// VARIABLE POUR MESSAGE D ERREUR
$error = null;
$good = null;
// PDO CONNEXION HERE
use PHPMailer\PHPMailer\PHPMailer;

require('../vendor/autoload.php');

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
            // echo 'userna = ' . $usernameExist;
            if($usernameExist === 0){
                if($email === $email2){
                    if(filter_var($email,FILTER_VALIDATE_EMAIL)){
                        // VERIF SI EMAIL DEJA UTILISER
                            $verifMail = $db->prepare("SELECT * FROM user WHERE email = ?");
                            $verifMail->execute(array($email));
                            $mailExist = $verifMail->rowCount();
                            if($mailExist === 0){
                                if(password_verify($pwd2, $pwd)){
                                    $addDB = $db->prepare("INSERT INTO user (username, email, password) VALUES ( :username, :email, :pwd)"); 
                                    $addDB->bindParam('username',$username);
                                    $addDB->bindParam('email', $email);
                                    $addDB->bindParam('pwd', $pwd);
                                    $addDB->execute();
                                    //header("location: login.php");
                                    //SEND EMAIL ************************
                                    
                                    
                                    $mail = new PHPMailer();
                                    $mail->IsSMTP();
                                    
                                    $mail->SMTPDebug  = 0;  
                                    $mail->SMTPAuth   = TRUE;
                                    $mail->SMTPSecure = "ssl";
                                    $mail->Port       = 465;
                                    $mail->Host       = "smtp.gmail.com";
                                    $mail->Username = "username@gmail.com";
                                    $mail->Password = "password";
                                    
                                    $mail->IsHTML(true);
                                    $mail->addAddress($email);
                                    $mail->setFrom("jepsenbritemifato@gmail.com","Jepsen-Brite");
                                    $mail->Subject = 'Inscription to Jepsen-Brite successfull';
                                    $content = '<h1>Hi ' .$username .',</h1><br>
                                    <p>Welcome to the Jepsen-brite website of the Mifato team !</p>
                                    <p>Enjoy our project and don\'t forget to report the bugs !</p><br><br>

                                    <p>Mifato team</p>';
                                    
                                    $mail->MsgHTML($content); 
                                    if(!$mail->Send()) {
                                      $error = "Error while sending Email. ";
                                    } else {
                                      $good = "Email sent successfully. ";
                                    }
                                    // ********************************
                                    $good = "Account created";
                                }else{
                                    $error .= "Passwords doesn't match";
                                }
                            }else{
                                $error .= "E-Mail already used";
                            }
                    }else{
                        $error .= "Invalid E-Mail adress";
                    }
                }else{
                    $error .= "E-Mails doesn't match";
                }
            }else{
                $error .=  "Username already used";
            }
        }else{
            $error .= "Tous les champs ne sont pas remplis ...";
        }
}
       echo $error;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account</title>
    <link href="../assets/css/theme.css" rel="stylesheet" media="screen" title="main">
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" integrity="sha384-HzLeBuhoNPvSl5KYnjx0BT+WB0QEEqLprO+NBkkk5gbc67FTaL7XIGa2w1L0Xbgc" crossorigin="anonymous">
    <!-- <link rel="stylesheet" href="../assets/css/style.css"> -->
</head>
<body>
    <?php include('header.php')?>
    <div class="container mt-5">
        <h2 class="text-primary">Create an account</h2>
        <div class="jumbotron">

        
            <form method="POST">
            <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control bg-white" name="username" id="username">
            </div>
            <div class="form-group">
                <label for="email">E-Mail <small>(Don't forget to check your spam !)</small></label>
                <input type="email" class="form-control bg-white" name="email" id="email">
            </div>
            <div class="form-group">
                <label for="confirmEmail">Confirm E-Mail</label>
                <input type="email" class="form-control bg-white" name="confirmEmail" id="confirmEmail">
            </div>
            <div class="form-group">
                <label for="pwd">Create a Password</label>
                <input type="password" class="form-control bg-white" name="pwd" id="pwd">
            </div>
            <div class="form-group">
                <label for="confirmPWD">Confirm password</label>
                <input type="password" class="form-control bg-white" name="confirmPWD" id="confirmPWD">
            </div>    
            <div class="text-right">
                <input type="submit" class="btn btn-primary" name="createUser" value="Create your account">
            </div>
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