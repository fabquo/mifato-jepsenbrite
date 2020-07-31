<?php

require('../vendor/autoload.php');

    function uploadImage(){

        $s3 = new Aws\S3\S3Client([
            'version'  => 'latest',
            'region'   => 'eu-west-3',
        ]);
        $bucket = getenv('S3_BUCKET')?: die('No "S3_BUCKET" config var in found in env!');

        $uploadImage = 1;
        $checkImage = getimagesize($_FILES['image']['tmp_name']);
        $targetFile = basename($_FILES['image']['name']);
        $imageType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));
        $error='';



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

                return $newName ;

            } catch(Exception $e) {

                ?><p>Upload error :(.</p><?php
            
            }
        }

    }

?>