<?php
session_start();
// PDO CONNEXION HERE
$admin = false;
$displayUser = $db->query("SELECT * FROM user WHERE id = " .$_SESSION['id']);
$user= $displayUser->fetch();

if($user['is_admin'] == 1){
  $admin = true;
}

if(isset($_GET['id'])){
    if($_GET['idauthor'] === $_SESSION['id'] || $admin == true  ){
        $deleteCom = $db->prepare("DELETE FROM comments WHERE id = ?");
        $deleteCom->execute(array($_GET['id']));
        echo "<h1> COMMENT DELETED </h1>";
        header("location: event.php?id=".$_GET['eventid']);
    }else{
        echo "<h1> ACCES DENIED </h1>";
    }
}else{
    echo "<h1> ACCES DENIED </h1>";
}
?>