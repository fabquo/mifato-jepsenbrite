<?php
session_start();
// PDO CONNEXION HERE
// DATE D AUJOURD HUI
$today = date("Y-m-j");

// VARIABLE POUR ERREURS
$alert = null;
// echo $alert;
require('../vendor/autoload.php');

$Parsedown = new Parsedown();
$Parsedown->setSafeMode(true);

$idExist = 0;
$idevent = $_GET['id'];
  $events = $db ->prepare('SELECT *,
                                  YEAR(date), 
                                  MONTHNAME(date), 
                                  DAY(date), 
                                  DAYNAME(date), 
                                  HOUR(hour), 
                                  MINUTE(hour) 
                                  FROM event 
                                  WHERE id= ?');

  $events -> execute(array($idevent));
  $event = $events-> fetch();
  $admin = false;
if(isset($_SESSION['id'])){
$displayUser = $db->query("SELECT * FROM user WHERE id = " .$_SESSION['id']);
  $user= $displayUser->fetch();

//   echo $user['id'];
//   echo $_SESSION['id'];
  

if($user['is_admin'] == 1){
    $admin = true;
  }
}

if(isset($_GET['id'])){
    // echo $_GET['id'];
    // RECUP DONNEES USER
    $getid = intval($_GET['id']);
    $displayUser = $db->prepare("SELECT * FROM user WHERE id = ?");
    $displayUser->execute(array($getid));
    $userInfo = $displayUser->fetch();

    //RECUP DONNES EVENT USER
    $displayUserEvent = $db->prepare("SELECT event.title, date, hour, username, category.title, event.id,image  FROM event, user, category WHERE user.id = event.author_id AND user.id = ? AND event.category_id = category.id ORDER BY date DESC");
    $displayUserEvent->execute(array($_GET['id']));
    
    if(isset($userInfo['id'])){
        $idExist = 1;
    }

    // echo $idExist;

} 

if(isset($_POST['setNewInfo'])){
    $newUsername = htmlspecialchars($_POST['newUN']);
    
    $newPWD = password_hash($_POST['newPWD'], PASSWORD_BCRYPT);
    $currentPWD = htmlspecialchars($_POST['currentPWD']);
    $ConfirmNewPWD = htmlspecialchars($_POST['confirmNewPWD']);
    

    $newUsernameLength = strlen($newUsername);


    // VERIF NOUVEAU USERNAME
    if(!empty($newUsername)){
        if($newUsername == $userInfo['username']){
            $alert = "You're already using this username...";
        }
        else{
            // VERIF SI PSEUDO DISPO
            $verifNewUsername = $db->prepare("SELECT * FROM user WHERE username = ?");
            $verifNewUsername->execute(array($newUsername));
            $newUsernameExist = $verifNewUsername->rowCount();
            if($newUsernameExist == 0){
                if($newUsernameLength < 255){
                    $insertNewUsername = $db->prepare("UPDATE user SET username = ? WHERE id = ?");
                    $insertNewUsername -> execute(array($newUsername,$_SESSION['id']));
                    header("location: profile.php?id=".$_SESSION['id']);
                    exit();
                }
                else{
                    $alert = "Username too long";
                }
            }
            else{
                $alert = "Username already used";
            }
        }
    }
    else{
        
    }


    // VERIF PASSWORD
    if(!empty($currentPWD)){
        if(password_verify($currentPWD, $userInfo['password'])){
            if(password_verify($ConfirmNewPWD, $newPWD)){
                    $insertNewPWD = $db->prepare("UPDATE user SET password = ? WHERE id = ?");
                    $insertNewPWD -> execute(array($newPWD,$_SESSION['id']));
            }else{
                $alert = "the 2 passwords are not matching";
            }
        }else{
            $alert = "The current password is not the right one...";
        }
    }
    
    if($alert == null){
        header("location: profile.php?id=".$_SESSION['id']);
        exit();
    }
    
}



// RECUP GRAVATAR DU PROFIL CONSULTER 
if(isset($userInfo['id'])){
$gravatar_profile = $userInfo['email'];
    $size_profile = 100;
    $grav_url_profile = "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $gravatar_profile ) ) ). "&s=" . $size_profile;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?php echo $userInfo['username'];?></title>
    <link href="../assets/css/theme.css" rel="stylesheet" media="screen" title="main">
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css"
        integrity="sha384-HzLeBuhoNPvSl5KYnjx0BT+WB0QEEqLprO+NBkkk5gbc67FTaL7XIGa2w1L0Xbgc" crossorigin="anonymous">
    <!-- <link rel="stylesheet" href="../assets/css/style.css"> -->
</head>

<body>
    <?php 
        include("header.php");

if($idExist == 1){
    ?>

    <div class="container">
        <div class="jumbotron mt-5">
            <img class="rounded-circle d-block mx-auto" width="150" height="150" src="<?php echo $grav_url_profile ?>"
                alt="user profile image">
            <h5 class="text-center mt-3">Username</h5>
            <p class="text-success text-center"> <?php echo $userInfo['username'];?></p>
            <?php 
                if(isset($_SESSION['id']) && $_SESSION['id'] == $userInfo['id'] || $admin == true ){
                    ?>
            <h5 class="text-center">E-Mail</h5>
            <p class="text-info text-center"> <?php echo $userInfo['email'];?></p>

            <?php
                }
            ?>
        </div>


        <?php 


            if(isset($_SESSION['id']) && $_SESSION['id'] === $userInfo['id'] || $admin ==true ){
                ?>

        <!-- Button trigger modal -->
        <div class="row justify-content-around">
        <button type="button" class="btn btn-warning d-block mt-3" data-toggle="modal"
            data-target="#exampleModal">
            Edit Profile
        </button>

        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Edit Profile</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST">
                            <div class="form-group">
                                <label for="newUN">New Username</label>
                                <input type="text" class="form-control" name="newUN" id="newUN"
                                    placeholder="<?php echo $userInfo['username']?>">

                            </div>
                            <div class="form-group">
                                <label for="currentPWD">Current password</label>
                                <input type="password" class="form-control" name="currentPWD" id="currentPWD"
                                    placeholder="Your current password here">
                            </div>
                            <div class="form-group">
                                <label for="newPWD">New password</label>
                                <input type="password" class="form-control" name="newPWD" id="newPWD"
                                    placeholder="Your new password here">
                            </div>
                            <div class="form-group">
                                <label for="confirmNewPWD">Confirm new password</label>
                                <input type="password" name="confirmNewPWD" class="form-control" id="confirmNewPWD"
                                    placeholder="Confirm the new password here">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <input class="btn btn-warning" type="submit" name="setNewInfo" id="submitEdit"
                                    value="SET CHANGES">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>



        <!-- JS POUR DELETE UN USER -->
        <script type="text/javascript">
            function ConfirmDelete() {
                if (confirm("Delete Account?")){
                    location.href = "<?php echo "delete_user.php ? id = ".$_GET['id']; ?>";
                }};
        </script>
        <input type="button" class="btn btn-danger d-block mt-3" onclick="ConfirmDelete()"
            value="DELETE ACCOUNT">
            </Div>

        <?php 
                
                
                    if(isset($alert)){
                        echo "<p>".$alert."</p>";
                    }
            }
                     // Partie modal Affichage Event User 

                $useridparti = $db ->query("SELECT * FROM participant, event where participant.User_id = '$idevent' && event.id = participant.Event_id && event.date > curdate() or ( participant.User_id = '$idevent' && event.id = participant.Event_id && event.date = curdate() and event.hour > (current_time() + interval 2 HOUR)) ORDER BY event.date, event.hour");
                $testcount = $useridparti->rowCount();
                // echo $testcount;
                
                if ($testcount == 0 ) {
                                                    

                    echo '<p class="text-center" id="participation">No participation in an Event</p>'; 
                } else {                         
                    ?>
        <div class="row justify-content-around mt-3">
        <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#participation"
            value="Event's Participation">Event's Participation</button>
        <? } ?>
                </div>
        <div class="modal fade " id="participation" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-l" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title mx-3">

                            Event Participation

                            <ul class="modal-text mx-auto list-group-item" id="exampleModalLabel">


                                <?php 
                                                
                                                while ($Eventid = $useridparti ->fetch()){?>

                                <? if($Eventid['date'] == date("Y-m-d")){?>
                                <p class="text-center mx-auto"><a href="event.php?id=<? echo ($Eventid['id']); ?>"
                                        class="list-group btn btn-danger btn-block pl-4"><?php echo ($Eventid['title']); ?></a>
                                <? } else { ?>
                                <p class="text-center mx-auto"><a href="event.php?id=<? echo ($Eventid['id']); ?>"
                                class="list-group btn btn-primary btn-block pl-4"><?php echo ($Eventid['title']); ?></a>        
                                <? }
                                ?>
                                        </p>

                                <? } ?>

                            </ul>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Fin modal participation -->



        <div class="eventUser">
            <h2 class="text-center mt-5"><?php echo $userInfo['username'];?>'s events</h2>


            <div class="tabEvent">
                <table class="table table-hover">
                    <thead>
                        <tr class="text-center">
                            <!-- <th scope="col"></th> -->
                            <th scope="col">Title</th>
                            <th scope="col" class="d-none d-xl-table-cell d-md-table-cell">Date</th>
                            <th scope="col" class="d-none d-xl-table-cell d-md-table-cell">Hour</th>
                            <th scope="col">Category</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php 
// $eventdeleteid = '';
if (isset($_POST['deleteadmin'])) { 
    // var_dump($_POST);}
    $eventdeleteid = $_POST['idevent'];
    $deleteEvent = $db ->prepare("DELETE FROM event WHERE event.id=?");
    $deleteComments = $db->prepare("DELETE FROM comments where event_id=?");
    $deleteParticipant = $db->prepare("DELETE FROM participant where Event_id =?");
    $deleteSubCat = $db->prepare("DELETE FROM event_souscategorie where Event_id = ?");
    $deleteComments -> execute(array($eventdeleteid));
    $deleteParticipant -> execute(array($eventdeleteid));
    $deleteSubCat -> execute(array($eventdeleteid));
    $deleteEvent ->execute(array($eventdeleteid));
    echo "<meta http-equiv='refresh' content='0'>";
    }

                            while($userEvent = $displayUserEvent->fetch()){

                                if($userEvent['date'] > $today){
                            ?>
                        <tr class="table-light">
                            <td scope="row" class="py-1"><a class="text-primary"
                                    href="<?php echo 'event.php?id='.$userEvent['id'] ?>"><?php echo $userEvent['0']?></a>
                                </th>
                            <td class="text-center py-1 d-none d-xl-table-cell d-md-table-cell">
                                <small><?php echo $userEvent['date']?><small></td>
                            <td class="text-center py-1 d-none d-xl-table-cell d-md-table-cell">
                                <?php echo $userEvent['hour']?></td>
                            <td class="text-center py-1"><?php echo $userEvent['title']?></td>
                            <?php if ($admin == true || $_SESSION['id'] == $_GET['id']){ ?>
                            <td class="py-1 text-right d-none d-xl-table-cell d-md-table-cell">
                                <form method="POST" class="">
                                    <input type="radio" name="idevent" class="invisible w-0 d-none" checked=""
                                        value="<?php echo $userEvent['id']; ?> "></input>
                                    <input id="<?$userEvent['id']?>" type="submit" name="deleteadmin"
                                        class="btn btn-danger py-0" value="Delete Event">
                                </form>
                            </td>
                            <? } ?>
                        </tr>
                        <?php

                        }else if($userEvent['date'] == $today){
                            ?>
                        <tr class="table-active">
                            <td scope="row" class="py-1"><a class="text-primary"
                                    href="<?php echo 'event.php?id='.$userEvent['id'] ?>"><?php echo $userEvent['0']?></a>
                                </th>
                            <td class="text-center py-1 d-none d-xl-table-cell d-md-table-cell">
                                <small><?php echo $userEvent['date']?><small></td>
                            <td class="text-center py-1 d-none d-xl-table-cell d-md-table-cell">
                                <?php echo $userEvent['hour']?></td>
                            <td class="text-center py-1"><?php echo $userEvent['title']?></td>
                            <?php if ($admin == true || $_SESSION['id'] == $_GET['id']){ ?>
                            <td class="py-1 text-right d-none d-xl-table-cell d-md-table-cell">
                                <form method="POST" class="">
                                    <input type="radio" name="idevent" class="invisible w-0 d-none" checked=""
                                        value="<?php echo $userEvent['id']; ?> "></input>
                                    <input id="<?$userEvent['id']?>" type="submit" name="deleteadmin"
                                        class="btn btn-danger py-0" value="Delete Event">
                                </form>
                            </td>
                            <? } ?>
                        </tr>
                        <?php
                            
                        }else{
                            ?>
                        <tr class="table-danger">
                            <td scope="row" class="py-1"><a class="text-primary"
                                    href="<?php echo 'event.php?id='.$userEvent['id'] ?>"><?php echo $userEvent['0']?></a>
                                </th>
                            <td class="text-center py-1 d-none d-xl-table-cell d-md-table-cell">
                                <small><?php echo $userEvent['date']?><small></td>
                            <td class="text-center py-1 d-none d-xl-table-cell d-md-table-cell">
                                <?php echo $userEvent['hour']?></td>
                            <td class="text-center py-1"><?php echo $userEvent['title']?></td>
                            <?php if ($admin == true || $_SESSION['id'] == $_GET['id']){ ?>
                            <td class="py-1 text-right d-none d-xl-table-cell d-md-table-cell">
                                <form method="POST" class="">
                                    <input type="radio" name="idevent" class="invisible w-0 d-none" checked=""
                                        value="<?php echo $userEvent['id']; ?> "></input>
                                    <input id="<?$userEvent['id']?>" type="submit" name="deleteadmin"
                                        class="btn btn-danger py-0" value="Delete Event">
                                </form>
                            </td>
                            <? } ?>
                        </tr>
                        <?php
                            
                            }
                        }
                        ?>
                    </tbody>
                </table>


            </div>
        </div>
    </div>
    <? } else { ?>

    <div class="container">
        <div class="jumbotron mt-5">
            <h4 class="text-center mt-3">Username doesn't exist !</h4>
        </div>
    </div>
    <? } ?>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous">
    </script>
    <?  ?>
</body>

</html>