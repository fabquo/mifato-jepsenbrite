<?php
session_start();
// pdo
if(isset($_GET['id'])){
    if($_GET['idauthor'] === $_SESSION['id']){
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