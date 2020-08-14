<?php
session_start();

// PDO CONNEXION HERE

  

$displayUser = $db->query("SELECT * FROM user WHERE id = " .$_SESSION['id']);
$user= $displayUser->fetch();


$admin = false;

  if($user['is_admin'] == 1){
    $admin = true;
  }
if(isset($_GET['id'])){

    // $userToDelete = $_GET['id'];

    if($_GET['id'] === $_SESSION['id'] || $admin == true){
        $deleteUser = $db->prepare("DELETE FROM user WHERE id = ? ");
        $deleteParticipant = $db -> prepare("DELETE FROM participant where User_id = ?");
        $deleteParticipant->execute(array($_GET['id']));
        $deleteUser->execute(array($_GET['id']));
        echo "<h1> ACCOUNT DELETED </h1>";
        $_SESSION = array();
        session_destroy();
        header("location: ../index.php");
    }else{
        echo "<h1> ACCES DENIED </h1>";
    }
}else{
    echo "<h1> ACCES DENIED </h1>";
}
