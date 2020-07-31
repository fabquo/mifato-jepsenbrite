<?php
session_start();
// VARIABLE POUR MESSAGE D ERREUR
$error = null;
$good = null;
// pdo

require('../vendor/autoload.php');

// SI LE BOUTON CREATE EST CLIQUER
if(isset($_POST['createEvent'])){
    // CONVERSION DES VARIABLE POUR SECURISER BD
        $title = htmlspecialchars($_POST['title']);
        $date = htmlspecialchars($_POST['date']);
        $hour = htmlspecialchars($_POST['time']);
        $desc = htmlspecialchars($_POST['description']);
        $cat = $_POST['category'];
        $autID = $_SESSION['id'];

        // $newName = uploadImage();

        $s3 = new Aws\S3\S3Client([
            'version'  => 'latest',
            'region'   => 'eu-west-3',
        ]);
        $bucket = getenv('S3_BUCKET')?: die('No "S3_BUCKET" config var in found in env!');

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


        
        if(!empty($_POST['title']) AND !empty($_POST['date']) AND !empty($_POST['time']) AND !empty($_POST['category']) AND isset($_SESSION['id']) AND !isset($error)){
            $addDB = $db->prepare("INSERT INTO event (title, author_id, date, hour, image, description, category_id) VALUES 
            ( :title, :author_id, :date, :hour, :image, :description, :category_id)"); 
            $addDB->bindParam('title',$title);
            $addDB->bindParam('author_id', $autID);
            $addDB->bindParam('date', $date);
            $addDB->bindParam('hour', $hour);
            $addDB->bindParam('image', $newName);
            $addDB->bindParam('description', $desc);
            $addDB->bindParam('category_id', $cat);
            $addDB->execute();
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
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</head>

<body>
    <?php include('header.php')?>

    <div class="container text-left">
        <form enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF']?>" method="POST">
            <label class="col-form-label" for="title">Event's title :</label><br />
            <input class="form-control" type="text" name="title" id="title"><br />
            <label class="col-form-label" for="date">Date :</label>
            <input class="form-control" type="date" name="date" id="date"><br />
            <label class="col-form-label" for="time">Hour :</label>
            <input class="form-control" type="time" name="time" id="time"><br />
            <label class="col-form-label" for="image">Image <small>(must be .jpeg, .jpg, .gif, .png and smaller than
                    500kb)</small> : </label>
            <input name="image" type="file" class="form-control-file" id="image"><br />
            <label class="col-form-label" for="description">Description (markdown and smiley both accepted)
                :</label><br />
            <textarea class="form-control" rows="10" name="description" id="description"></textarea><br />
            <label class="col-form-label" for="category">Select category :</label><br />
            <select class="custom-select" name="category" id="category"><br />
                <?php
                $listRequest = $db->query("SELECT * FROM category ORDER BY title ASC");
                
                $listMenu = $listRequest;

                while($listMenu = $listRequest->fetch()){

                    echo '<option value ="' . $listMenu['id'] . '" name ="' . $listMenu['title'] . '" id ="' . $listMenu['title'] . '">' . $listMenu['title'] . '</option>';
                }
                
            ?>
            </select>
            <?php 
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
            <br /><br />
            <div class="text-center">
                <input class="btn btn-success col-4 mb-5" type="submit" name="createEvent" value="Create">
            </div>
        </form>
    </div>
</body>

</html>