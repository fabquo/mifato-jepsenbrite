<?php
session_start();
// VARIABLE POUR MESSAGE D ERREUR
$error = null;
$good = null;

include '../vendor/erusev/parsedown/Parsedown.php';
// pdo

function eventListCreator($db, $cat){

$eventRequest = $db->query("SELECT  event.*,
                                    category.*,
                                    YEAR(date), 
                                    MONTHNAME(date), 
                                    DAY(date), 
                                    DAYNAME(date), 
                                    HOUR(hour),
                                    MINUTE(hour)
                                    FROM 
                                    event, 
                                    category
                                    WHERE 
                                    event.category_id = category.id &&
                                    event.date > curdate() or
                                    (event.category_id = category.id &&
                                    event.date = curdate() && event.hour > (current_time() + interval 2 HOUR))
                                    ORDER BY date ASC");


echo '<div class="container row text-center justify-content-around">';
while($listEvent = $eventRequest->fetch()){

        $commentrow = 0;
        $commentCount = "SELECT comments.event_id FROM comments where event_id = $listEvent[0]";
        $commenttest= $db->query($commentCount);
        $commentrow = $commenttest->rowCount();
        
        if($listEvent['15'] ==0){
            $minToShow = '00';
        } else {
            $minToShow = $listEvent['15'];
        }

    if($cat == 'all'){
        echo '<div class="card mb-5 ml-2 mr-2 pt-2 col-12 col-md-4 col-lg-3 col-xl-3" >';
        echo '<div id="image" style="height: 100px;" class="mb-3">';
        echo '<figure class="mt-5">';
        if($listEvent['image']){
            echo '<img src="https://mifato.s3.eu-west-3.amazonaws.com/' . $listEvent['image'] . '" class="card-img-top rounded" style="width: 75%; height: auto"/>';
        }else{
            echo '<img src="https://mifato.s3.eu-west-3.amazonaws.com/no-image.png" class="card-img-top rounded" style="width: 50%;"/>';
        }
            echo '</figure></div>';
            echo '<div class="card-body module mt-5">';
            echo '<h4 style="min-height: 100px"><a class="card-title" style="min-height: 80px" href="event.php?id='. $listEvent['0'] . '">' . $listEvent['1'] . '</a></h4>';
            echo '<p class="card-text"><small class"text-muted>' . $listEvent['13'] . ' '. $listEvent['12'] . ' ' . $listEvent['11'] . ' ' . $listEvent['10'] . '  -  ' . $listEvent['14'] . ':' . $minToShow . '</small></p>';
            echo '<p class="card-text"><small class"text-muted>' . $listEvent['title'] . ' | <i class="far fa-comments"></i> : '. $commentrow .'</small></p>';
            echo '<p class="card-text overflow-hidden text-light line-coverage clamptext" style="height: 200px;">' . $listEvent['description'] .'</p>';
            echo '</div>';
        echo '</div>';

    } elseif ($cat == $listEvent['category_id']){

        echo '<div class="card mb-5 ml-2 mr-2 pt-2 col-12 col-md-4 col-lg-3 col-xl-3 " style="width= 24rem">';
        echo '<div id="image" style="height: 100px;" class="mb-3">';
        echo '<figure class="mt-5">';
        if($listEvent['image']){
            echo '<img src="https://mifato.s3.eu-west-3.amazonaws.com/' . $listEvent['image'] . '" class="card-img-top rounded" style="width: 75%; height: auto"/>';
        }else{
            echo '<img src="https://mifato.s3.eu-west-3.amazonaws.com/no-image.png" class="card-img-top rounded" style="width: 50%;"/>';
        }
        echo '</figure></div>';
        echo '<div class="card-body module mt-5">';
        echo '<h4 style="min-height: 100px"><a class="card-title" style="min-height: 80px" href="event.php?id='. $listEvent['0'] . '">' . $listEvent['1'] . '</a></h4>';
        echo '<p class="card-text"><small class"text-muted>' . $listEvent['13'] . ' '. $listEvent['12'] . ' ' . $listEvent['11'] . ' ' . $listEvent['10'] . '  -  ' . $listEvent['14'] . ':' . $minToShow . '</small></p>';
        echo '<p class="card-text"><small class"text-muted>' . $listEvent['title'] . ' | <i class="far fa-comments"></i> : '. $commentrow .'</small></p>';
        echo '<p class="card-text overflow-hidden text-light line-coverage clamptext" style="height: 200px;">' . $listEvent['description'] .'</p>';
        echo '</div>';
    echo '</div>';
    }

}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content ="Display all event's coming for the Jepsen BeCode Promo and allow to create your own !">
    <title>Jepsen Brite</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" integrity="sha384-HzLeBuhoNPvSl5KYnjx0BT+WB0QEEqLprO+NBkkk5gbc67FTaL7XIGa2w1L0Xbgc" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</head>
<body>

    <?php include('header.php')?>
    <div class="container">
    <form method ='get' action='#' name="eventlist">
    <label for="category">Please select a category :</label>


            <select class="custom-select" name="category" id="category" onchange="eventlist.submit();">
            <option> --> Pick a category here <-- </option>
            <option value="all" name="all" id="all">All</option>
            <?php
                $listRequest = $db->query("SELECT * FROM category ORDER BY title ASC");
                
                $listMenu = $listRequest;

                while($listMenu = $listRequest->fetch()){

                    echo '<option value ="' . $listMenu['id'] . '" name ="' . $listMenu['title'] . '" id ="' . $listMenu['title'] . '">' . $listMenu['title'] . '</p>';
                }
                
            ?> 
            </select>
    </form>
    <br/>

    <div class="container-fluid row text-center justify-content-between ml-n1">
        <?php

            if(isset($_GET['category'])){
                $cat=$_GET['category'];
                eventListCreator($db,$cat);
            
            } else {
                eventListCreator($db,"all");
            }
?>
        </div>
        </div>
</body>
</html>