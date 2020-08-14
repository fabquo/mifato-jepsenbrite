<?php
session_start();
// VARIABLE POUR MESSAGE D ERREUR
$error = null;
$good = null;
// PDO CONNEXION HERE

require('../vendor/autoload.php');


$newName = null;
// SI LE BOUTON CREATE EST CLIQUER
if(isset($_POST['createEvent'])){
    // CONVERSION DES VARIABLE POUR SECURISER BD
    if($_POST['optionsRadios'] == 'video'){
        if($_POST['video'] != null){
        if(strpos($_POST['video'], 'youtube') !== false){

            $videoSplit = explode('=', $_POST['video']);
            $videoURL = end($videoSplit);
            // var_dump($videoURL);
        
        } else {
        
            $error = 'Your video link is not a youtube video ! ';
            // echo $error;
        
        }
    }
}

        $title = htmlspecialchars($_POST['title']);
        $date = htmlspecialchars($_POST['date']);
        $hour = htmlspecialchars($_POST['time']);
        $desc = htmlspecialchars($_POST['description']);
        $address = htmlspecialchars($_POST['address']);
        $pc = htmlspecialchars($_POST['postal']);
        $city = htmlspecialchars($_POST['city']);
        $country = htmlspecialchars($_POST['country']);
        $cat = $_POST['category'];
        $sCatID = $_POST['subCat'];
        $autID = $_SESSION['id'];


        
        // var_dump($_FILES['image']);

        if($_POST['optionsRadios'] == 'image'){


            $s3 = new Aws\S3\S3Client([
                'version'  => 'latest',
                'region'   => 'eu-west-3',
            ]);
    
            $bucket = getenv('S3_BUCKET')?: die('No "S3_BUCKET" config var in found in env!');

            $imageSet = basename($_FILES['image']['name']);

        if($imageSet != null){
        $uploadImage = 1;
        $checkImage = getimagesize($_FILES['image']['tmp_name']);
        $targetFile = basename($_FILES['image']['name']);
        $imageType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));

        if($checkImage !== false){
            $uploadImage = 1;
        } else {
            $error = 'File is not an image. ';
            $uploadImage = 0;
        }

        if($_FILES['image']['size'] > 500000){
            $error = 'Sorry, your file is too large (must be smaller than ~500kb). ';
            $uploadImage = 0;
        }

        if(isset($imageType) && $imageType != "jpg" && $imageType != "png" && $imageType != "jpeg" && $imageType != "gif" ) {
            $error = "Only JPG, JPEG, PNG & GIF files are allowed. ";
            $uploadOk = 0;
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['image']['tmp_name']) && $uploadImage == 1) {
            try {
                $newName = htmlspecialchars(md5(session_id() . microtime()).'.'.$imageType);
                $upload = $s3->upload($bucket, $newName, fopen($_FILES['image']['tmp_name'], 'rb'), 'public-read'); 

            } catch(Exception $e) {

                ?><p>Upload error :(.</p><?php
            
            }
        }
        }
    }

        
        if(!empty($_POST['address']) && !empty($_POST['category']) && !empty($_POST['postal']) && !empty($_POST['city']) && !empty($_POST['country']) && !empty($_POST['title']) AND !empty($_POST['date']) AND !empty($_POST['time']) AND !empty($_POST['category']) AND isset($_SESSION['id']) AND !isset($error)){
            
            $addDB = $db->prepare("INSERT INTO event (title, author_id, date, hour, image, description, category_id, address, pc, city, country) VALUES 
            ( :title, :author_id, :date, :hour, :image, :description, :category_id, :address, :pc, :city, :country)"); 
            $addDB->bindParam('title',$title);
            $addDB->bindParam('author_id', $autID);
            $addDB->bindParam('date', $date);
            $addDB->bindParam('hour', $hour);
            $addDB->bindParam('address', $address);
            $addDB->bindParam('pc', $pc);
            $addDB->bindParam('city', $city);
            $addDB->bindParam('country', $country);
            if($newName){
            $addDB->bindParam('image', $newName);
            } else {
                $addDB->bindParam('image', $videoURL);
            }
            $addDB->bindParam('description', $desc);
            $addDB->bindParam('category_id', $cat);
            $addDB->execute();
            

            $last_id = $db->lastInsertId();


            $checkSubCat = $db -> query("SELECT * FROM sous_categorie WHERE Categorie_id = '$cat'");

            while($CheckSC = $checkSubCat->fetch()){
            
                
                    foreach($sCatID as $subcat){
                
                        if($CheckSC['Categorie_id'] == $cat && $CheckSC['id'] == $subcat){

                        $addSubCat = $db->query("INSERT INTO event_souscategorie (Sous_Categorie_id, Event_id) VALUES ('$subcat', '$last_id')");
            }
            }
        }

            $good = "Event created";
            }else{
            $error .= "Event not created.";
            }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" integrity="sha384-HzLeBuhoNPvSl5KYnjx0BT+WB0QEEqLprO+NBkkk5gbc67FTaL7XIGa2w1L0Xbgc" crossorigin="anonymous">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link href="../assets/css/theme.css" rel="stylesheet" media="screen" title="main">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
    <!-- <script src="..assets/js/option.js"></script> -->
</head>

<body>
    <?php include('header.php')?>

    <div class="container text-left mt-5">
        <form class="form-group" enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF']?>" method="POST">
            <div class="row px-3 justify-content-between">
            <label class="col-form-label px-0 col-xl-1 mt-3" for="title" >Title</label>
            <input class="form-control col-xl-11 mt-3" type="text" name="title" id="title">
            </div>
            <div class="row px-3 justify-content-between mt-3">
            <label class="col-form-label px-0 col-xl-1" for="address" >Address</label>
            <input class="form-control col-xl-11" type="text" name="address" id="address">
            </div>
            <div class="row px-3 mt-3 justify-content-between">
            <label class="col-form-label px-0 col-xl-1" for="postal">Postcode </label>
            <input class="form-control col-xl-3" type="text" name="postal" id="postal">
            <label class="col-form-label col-xl-1" for="city">City</label>
            <input class="form-control col-xl-3" type="text" name="city" id="city">
            <label class="col-form-label col-xl-1" for="country">Country</label>
            <input class="form-control col-xl-3" type="text" name="country" id="country">
            </div>
            <div class="row px-3 mt-3 justify-content-between">
            <label class="col-form-label px-0 col-xl-1" for="date">Date</label>
            <input class="form-control col-xl-5" type="date" name="date" id="date">
            <label class="col-form-label col-xl-1" for="time">Hour</label>
            <input class="form-control col-xl-5" type="time" name="time" id="time">
            </div>
            <label class="col-form-label mt-3">Make a choice</label>
            <fieldset class="form-group">
                <div class="form-check">
                
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios1" value="image" checked="">
                        Upload an image
                        </label>
                </div>
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="optionsRadios" id="optionsRadios2" value="video">
                        Link to Youtube video
                        </label>
                </div>
            </fieldset>
            <label class="col-form-label" for="image">Image <small>(must be .jpeg, .jpg, .gif, .png and smaller than
                    500kb)</small> : </label>
            <input class="col-xl-12 pl-0" name="image" type="file" class="form-control-file" id="image">
            <div class="mt-3">
            <label class="col-form-label" for="video" style="min-width:10%">Youtube video. <small>(past here the entire URL of your video. Example : https://www.youtube.com/watch?v=u0yHr6sdf7Q)</small></label>
            <input class="form-control align-self-center col-xl-12 col-sm-12" type="text" name="video" id="video">
            </div>
            <label class="col-form-label mt-3" for="description">Description (markdown and smiley both accepted)
                :</label>
            <textarea class="form-control" rows="10" name="description" id="description"></textarea>
            <!-- <label class="col-form-label mt-3" for="category">Select category :</label> -->
            <!-- <select class="custom-select" name="category" id="category"> -->
                <?php
                $listRequest = $db->query("SELECT * FROM category ORDER BY title asc;");
                
                $listMenu = $listRequest;
                
                echo '<label class="mt-3"> Select a category </label>';
                echo '<div class="btn-group btn-group-toggle w-100 row mx-auto" data-toggle="buttons">';
                    
                while($listMenu = $listRequest->fetch()){

                    echo '<label class="btn btn-light rounded-pill mx-2 my-2 " data-toggle="modal" data-target ="#modal'.$listMenu['id'] .'">';
                    echo '<input type="radio" name ="category" value ="'.$listMenu['id'] . '" autocomplete="off"</input>'. $listMenu['title'] .'</label>';
                }

                echo '</div>';

                $sublistrequest = $db->query("SELECT * FROM category ORDER BY title asc;");
                
                $subListMenu = $sublistrequest;
                
                while($ssCatMenu = $subListMenu->fetch()){

                    $catidsl = $ssCatMenu['id'];
                    $souscat = $db->query("SELECT * FROM sous_categorie inner join category on sous_categorie.Categorie_id = category.id where category.id = '$catidsl' ORDER BY sous_categorie.nom_ssc ASC");
                       
                    
                    echo '<div class="modal fade" id="modal'. $ssCatMenu['id'] . '" tabindex="-1">';
                    echo '<div class="modal-dialog modal-dialog-centered" role="document">';
                              echo '<div class="modal-content">';
                                echo '<div class="modal-header">';
                                  echo '<h5 class="modal-title">Pick sub-category</h5>';
                                echo '</div>';
                                echo' <div class="modal-body">';

                        while($listsscat = $souscat->fetch()){

                                echo '<div class="custom-control custom-checkbox">';
                                echo '<input type="checkbox" class="custom-control-input" name ="subCat[]" id="'. $listsscat[0].'" value="'.$listsscat[0].'">';
                                echo '<label class="custom-control-label" for="'. $listsscat[0] .'">'. $listsscat['nom_ssc'] . '</label>';
                                echo '</div>';

                                // var_dump($listsscat);
                                  }
                                echo '</div>';
                                echo '<div class="modal-footer">';
                                
                                  echo '<button type="button" class="btn btn-primary" data-dismiss="modal">Save changes</button></div>';
                              echo '</div>';
                            echo '</div>';
                          echo '</div>';
                        //   echo '</div>';
                                }
                        echo '</div>';

                        echo '</div>';
                       
            // }

                if(isset($error)){
                    ?>
            <div class="alert alert-dismissible alert-danger">
                <?php echo $error ?>
            </div>
            <?php
                }
            ?>
            <?php 
                if(isset($good)){
                    ?>

            <div class="alert alert-dismissible alert-succes">
                <?php echo $good ?>
            </div>
            <?php
                }
            ?>
            <div class="text-center mt-3">
                <input class="btn btn-success col-4 mb-5 mt-3" type="submit" name="createEvent" value="Create">
            </div>
        </form>
    </div>
</body>

</html>