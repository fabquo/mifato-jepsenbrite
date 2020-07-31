<?php
    session_start();

    include 'vendor/autoload.php';    
    
    if(isset($_SESSION['id'])){
        $gravatar = $_SESSION['email'];
        $size = 100;
        $grav_url = "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $gravatar ) ) ). "&s=" . $size;
    }
    $Parsedown = new Parsedown();
    $Parsedown->setSafeMode(true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content ="Display all event's coming for the Jepsen BeCode Promo and allow to create your own !">
    <title>Jepsen Brite</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" integrity="sha384-HzLeBuhoNPvSl5KYnjx0BT+WB0QEEqLprO+NBkkk5gbc67FTaL7XIGa2w1L0Xbgc" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
    



</head>

<body>
<header>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
<?php 
    if(isset($_SESSION['id'])){
        ?>
        
        <a class="navbar-brand flex-row text-success" href="<?php echo "pages/profile.php?id=".$_SESSION['id'];?>">
            <img class="rounded-circle" width="30" height="30" src="<?php echo $grav_url ?>" alt="USER PICTURE">
            <?php echo $_SESSION['username']; ?>
        </a>
        <?php
    }else{
        ?>
            <a class="navbar-brand flex-row text-success" href="pages/login.php">
                
                Log In
            </a>
        <?php

    }
?>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarColor02" aria-controls="navbarColor02" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarColor02">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="index.php">Home <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="pages/past.php">Past Events</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="pages/category.php">Events Category</a>
      </li>
        <?php
                        if(isset($_SESSION['id'])){
                    ?>
                            <li class="nav-item">
                                <a class="nav-link" href="pages/create_event.php">Create Event</a>
                            </li>
                    <?php       
                        }
                    ?>
        <?php 
                if(isset($_SESSION['id'])){
                    ?>
                    <li>
                        <a class="nav-link text-danger" href="pages/disconnect.php">Log out</a>
                    </li>
                    <?php
                }else{
                    ?>
                        <a class="nav-link text-warning" href="pages/create_user.php">Sign in</a>
                    <?php
                }
            ?>
    </ul>
</nav>
    </header>
    <div class="container-fluid mt-5 ">

    <a href="#top" class="btn btn-light position-fixed text-black rounded-circle" style="bottom:10px; right: 10px; z-index:20"> ^ </a>
    <?php

        $eventDisplayLimite = 5;
        $countEvents = $db->query('SELECT * FROM event INNER JOIN category on event.category_id = category.id where event.date > curdate() or(event.date = curdate() and event.hour > (current_time() + interval 2 HOUR));');

        
        $selectAll = 'SELECT *,
                        YEAR(date), 
                        MONTHNAME(date), 
                        DAY(date), 
                        DAYNAME(date), 
                        HOUR(hour), 
                        MINUTE(hour) 
                        FROM event 
                        INNER JOIN category on event.category_id = category.id
                        where event.date > curdate() or (event.date = curdate() and event.hour > (current_time() + interval 2 HOUR)) 
                        ORDER BY event.date, event.hour 
                        LIMIT :limite 
                        OFFSET :start';

        if (isset($_POST['submit'])){
            
            $search = strtoupper($_POST['search']);
            $selectAll = "SELECT *,
                            YEAR(date), 
                            MONTHNAME(date), 
                            DAY(date), 
                            DAYNAME(date), 
                            HOUR(hour), 
                            MINUTE(hour) 
                            FROM event 
                            INNER JOIN category on event.category_id = category.id
                            where UPPER(event.title) LIKE '%$search%' && event.date > curdate() or (UPPER(event.title) LIKE '%$search%' and event.date = curdate() and event.hour > (current_time() + interval 2 HOUR)) 
                            ORDER BY event.date, event.hour 
                            LIMIT :limite 
                            OFFSET :start";
            
            $countEvents = $db->query("SELECT * FROM event INNER JOIN category on event.category_id = category.id where UPPER(event.title) LIKE '%$search%' && event.date > curdate() or(UPPER(event.title) LIKE '%$search%' && event.date = curdate() and event.hour > (current_time() + interval 2 HOUR));");

        }

        $nbEVents = $countEvents->rowCount();
        $nbPages = ceil($nbEVents / $eventDisplayLimite);
        $page = (!empty($_GET['page']) ? $_GET['page'] : 1);
        $start = ($page - 1) * $eventDisplayLimite;

        $selectAll = $db->prepare($selectAll);



        $selectAll->bindValue(
            'limite',
            $eventDisplayLimite,
            PDO::PARAM_INT
        );

        $selectAll->bindValue(
            'start',
            $start,
            PDO::PARAM_INT
        );
        
        $selectAll->execute();

        ?>
                <div class="row bg-dark justify-content-center mt">
                    <h1 class="mt-1">Jepsen-Brite</h1>
                </div>

                <div class="row d-flex mt-5 justify-content-center">
                    <form class="form-inline mx-auto my-2 my-lg-0" method="post">
                            <input name="search" class="form-control w-100" type="text" placeholder="Search">
                            <button class="btn btn-secondary my-2 my-sm-0 w-100" type="submit" name="submit">Search</button>
                    </form>
                </div>
        <div class="row d-flex mt-5">
            <ul class="pagination mx-auto">
            <?php
                if($page > 1):
            ?>
            <li class="page-item"><a class="page-link" href="?page=<?php echo $page -1; ?>">Page précédente</a></li>
            <?php
                endif;
                for ($i = 1; $i <= $nbPages; $i++):
            ?>
            <li class="page-item"><a class="page-link" href="?page=<?php echo $i; ?>"> <?php echo $i.' ' ;?> </a></li>
            <?php
                endfor;
                if ($page < $nbPages):
            ?>
            <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1;?>">Page suivante</a></li>
            <?php
                endif;
            ?>
            </ul>
        </div>

        </div>
        <div class="container mt-5">
        <?php
        $i=0;

        while($elem = $selectAll->fetch()){

            $commentrow = 0;
            $commentCount = "SELECT comments.event_id FROM comments where event_id = $elem[0]";
            $commenttest= $db->query($commentCount);
            $commentrow = $commenttest->rowCount();
            
            if($elem['15'] ==0){
                $minToShow = '00';
            } else {
                $minToShow = $elem['15'];
            }

            if($i===0 && $page == 1){
                echo '<div class="row card mb-3 mt-5 col-12 flex-row pt-4 pb-4 mx-auto bg-secondary">';
                            echo '<div class="col-12 col-lg-6 card-body rounded-0 flex-column text-center">';
                                echo '<h1 ><a class="card-title text-warning" href="pages/event.php?id='. $elem['0'] . '">' . $elem['1'] . '</a></h1>';
                                echo '<p class="card-text small text-muted">' . $elem['13'] . ' '. $elem['12'] . ' ' . $elem['11'] . ' ' . $elem['10'] . '   -   ' . $elem['14'] . ':' . $minToShow .'</p>';
                                echo '<p class="card-text small text-muted">' . $elem['title'] . ' | <i class="far fa-comments"></i> : '. $commentrow .'</p>';
                                echo '<div class="card-text overflow-auto mt-3 text-left">' . $Parsedown->text($elem['description']) .'</div>';
                        echo '</div>';
                        echo '<div class="col-12 col-lg-6 align-self-center justify-content-center">';
                                if($elem['image']){
                                    echo '<img src="https://mifato.s3.eu-west-3.amazonaws.com/' . $elem['image'] . '" class="img-fluid" alt="event image not found"/>';
                                } else {
                                    echo '<img src="https://mifato.s3.eu-west-3.amazonaws.com/no-image.png" class="img-fluid" alt="event image not found"/>';
                                }
                        echo '</div>';
                echo '</div>';
            $i ++;
            } else {
            echo '<div class="row card mb-3 col-12 flex-row pt-4 pb-4 mx-auto mt-5">';
                    echo '<div class="col-12 col-lg-6 card-body flex-column text-center">';
                        echo '<h2><a class="card-title" href="pages/event.php?id='. $elem['0'] . '">' . $elem['1'] . '</a></h2>';
                        echo '<p class="card-text small text-muted">' . $elem['13'] . ' '. $elem['12'] . ' ' . $elem['11'] . ' ' . $elem['10'] . '   -   ' . $elem['14'] . ':' . $minToShow .'</p>';
                        echo '<p class="card-text small text-muted">' . $elem['title'] . ' | <i class="far fa-comments"></i> : '. $commentrow .'</p>';
                        echo '<div class="card-text overflow-auto mt-3 text-left">' . $Parsedown->text($elem['description']) .'</div>';
                    echo '</div>';
                    echo '<div class="col-12 col-lg-6 align-self-center justify-content-center">';
                        if($elem['image']){
                            echo '<img src="https://mifato.s3.eu-west-3.amazonaws.com/' . $elem['image'] . '" class="img-fluid" alt="event image not found"/>';
                        } else {
                            echo '<img src="https://mifato.s3.eu-west-3.amazonaws.com/no-image.png" class="img-fluid" alt="event image not found"/>';
                        }
                    echo '</div>';
            echo '</div>';
            }
        }

    
?>
<div class="container-fluid">
<div class="row d-flex">
<ul class="pagination mx-auto">
    <?php
        if($page > 1):
            ?><li class="page-item"><a class="page-link" href="?page=<?php echo $page -1; ?>">Page précédente</a></li><?php
        endif;

        for ($i = 1; $i <= $nbPages; $i++):
            ?><li class="page-item"><a class="page-link" href="?page=<?php echo $i; ?>"> <?php echo $i.' ' ;?> </a></li><?php
        endfor;

        if ($page < $nbPages):
            ?><li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1;?>">Page suivante</a></li><?php
        endif;
    ?>
        </ul>
    </div>
    </div>
</div>
</body>
</html>

