<?php
session_start();

// pdo

if(isset($_GET['id'])){
    if($_GET['id'] === $_SESSION['id']){
        $deleteUser = $db->prepare("DELETE FROM user WHERE id = ?");
        $deleteUser->execute(array($_SESSION['id']));
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
