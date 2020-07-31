<?php
session_start();
// pdo
// DATE D AUJOURD HUI
$today = date("Y-m-j");

// VARIABLE POUR ERREURS
$alert = null;
echo $alert;


if(isset($_GET['id'])){
    // RECUP DONNEES USER
    $getid = intval($_GET['id']);
    $displayUser = $db->prepare("SELECT * FROM user WHERE id = ?");
    $displayUser->execute(array($getid));
    $userInfo = $displayUser->fetch();

    //RECUP DONNES EVENT USER
    $displayUserEvent = $db->prepare("SELECT event.title, date, hour, username, category.title, event.id,image  FROM event, user, category WHERE user.id = event.author_id AND user.id = ? AND event.category_id = category.id ORDER BY date DESC");
    $displayUserEvent->execute(array($_GET['id']));
    
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
$gravatar_profile = $userInfo['email'];
    $size_profile = 100;
    $grav_url_profile = "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $gravatar_profile ) ) ). "&s=" . $size_profile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?php echo $userInfo['username'];?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" integrity="sha384-HzLeBuhoNPvSl5KYnjx0BT+WB0QEEqLprO+NBkkk5gbc67FTaL7XIGa2w1L0Xbgc" crossorigin="anonymous">
    <!-- <link rel="stylesheet" href="../assets/css/style.css"> -->
</head>
<body>
    <?php 
        include("header.php");
    ?>




    <div class="container">
        <div class="jumbotron mt-5">
            <img class="rounded-circle d-block mx-auto" width="150" height="150" src="<?php echo $grav_url_profile ?>" alt="user profile image">
            <h4 class="text-center mt-3">Username</h4>
            <h2 class="text-success text-center" > <?php echo $userInfo['username'];?></h2>
            <?php 
                if(isset($_SESSION['id']) && $_SESSION['id'] == $userInfo['id']){
                    ?>
                    <h4 class="text-center">E-Mail</h4>
                    <h4 class="text-info text-center"> <?php echo $userInfo['email'];?></h4>
                    
                    <?php
                }
            ?>
        </div>
        <?php 
        if(isset($_SESSION['id'])){
            
        }
            if(isset($_SESSION['id']) && $_SESSION['id'] === $userInfo['id']){
                ?>
                
                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-warning d-block mx-auto mt-3" data-toggle="modal" data-target="#exampleModal">
                    Edit Profile
                    </button>

                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                                    <input type="text" class="form-control" name="newUN" id="newUN" placeholder="<?php echo $userInfo['username']?>">
                                    
                            </div>
                            <div class="form-group">
                                    <label for="currentPWD">Current password</label>
                                    <input type="password" class="form-control" name="currentPWD" id="currentPWD" placeholder="Your current password here">
                            </div>
                            <div class="form-group">
                                    <label for="newPWD">New password</label>
                                    <input type="password" class="form-control" name="newPWD" id="newPWD" placeholder="Your new password here">
                            </div>
                            <div class="form-group">
                                    <label for="confirmNewPWD">Confirm new password</label>
                                    <input type="password" name="confirmNewPWD" class="form-control" id="confirmNewPWD" placeholder="Confirm the new password here">
                            </div>
                            <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <input class="btn btn-warning" type="submit" name="setNewInfo" id="submitEdit" value="SET CHANGES">
                        </div>
                                    
                                    
                        </form>
                        </div>
                        
                        </div>
                    </div>
                    </div>
             
                



                <!-- JS POUR DELETE UN USER -->
                <script type="text/javascript">
                    function ConfirmDelete()
                    {
                            if (confirm("Delete Account?"))
                                location.href="<?php echo "delete_user.php?id=".$_SESSION['id']; ?>";
                    }
                </script>
                <input type="button" class="btn btn-danger d-block mx-auto mt-3" onclick="ConfirmDelete()" value="DELETE ACCOUNT">


                <?php 
                
                
                    if(isset($alert)){
                        echo "<p>".$alert."</p>";
                    }
                ?>
            </div>
            <?php
            }
        ?>
        

        <div class="eventUser">
            <h2 class="text-center mt-5"><?php echo $userInfo['username'];?>'s events</h2>
            <div class="tabEvent">
                <table class="table table-hover text-center">
                            <thead>
                                <tr>
                                <th scope="col"></th>
                                <th scope="col">Event title</th>
                                <th scope="col">Date</th>
                                <th scope="col">Hour</th>
                                <th scope="col">Event Type</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>
                            <tbody>
                        
                <?php 
                    while($userEvent = $displayUserEvent->fetch()){
                        
                        if($userEvent['date']>$today){
                            ?>
                            <tr height="80" class="table-success">
                                <td><img width="80" height="50" src="https://mifato.s3.eu-west-3.amazonaws.com/<?php echo $userEvent['image']?>" alt=""></td>
                                <th scope="row"><a class="text-white" href="<?php echo 'event.php?id='.$userEvent['id'] ?>"><?php echo $userEvent['0']?></a></th>
                                <td><?php echo $userEvent['date']?></td>
                                <td><?php echo $userEvent['hour']?></td>
                                <td><?php echo $userEvent['title']?></td>
                                <td> To come </td>
                            </tr>
                            <?php

                        }else if($userEvent['date'] == $today){
                            ?>
                            <tr height="80" class="table-warning">
                                <td><img width="80" height="50" src="https://mifato.s3.eu-west-3.amazonaws.com/<?php echo $userEvent['image']?>" alt=""></td>
                                <th scope="row"><a class="text-white" href="<?php echo 'event.php?id='.$userEvent['id'] ?>"><?php echo $userEvent['0']?></a></th>
                                <td><?php echo $userEvent['date']?></td>
                                <td><?php echo $userEvent['hour']?></td>
                                <td><?php echo $userEvent['title']?></td>
                                <td> Today </td>
                            </tr>
                        <?php
                            
                        }else{
                            ?>
                            <tr height="80" class="table-danger">
                            <td><img width="80" height="50" src="https://mifato.s3.eu-west-3.amazonaws.com/<?php echo $userEvent['image']?>" alt=""></td>
                            <th scope="row"><a class="text-white" href="<?php echo 'event.php?id='.$userEvent['id'] ?>"><?php echo $userEvent['0']?></a></th>
                            <td><?php echo $userEvent['date']?></td>
                            <td><?php echo $userEvent['hour']?></td>
                            <td><?php echo $userEvent['title']?></td>
                            <td> Already past </td>
                        </tr>
                        <?php
                            
                        }
                        ?>
                        
                        </div>
                        
                        <?php
                    }
                ?>
                </tbody>
                </table>        
                        
                       
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous">
    </script>
</body>
</html>