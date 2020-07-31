<?php 
session_start();
  try
  {
    // pdo
  } 
  catch (Exeption $e)
  {
    die('erreur :' .$e ->getMessage());
} 
if (isset($_GET['id'])) {
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

}
if(isset($_SESSION['id'])){
if(isset($_POST['sendComment'])){
  $addComment = $db->prepare("INSERT INTO comments (text, date_comment, author_id, event_id) VALUES (:text ,DATE_ADD(NOW(), interval +2 HOUR), :author, :event)");
  $addComment->bindParam('text',$_POST['userComment']);
  $addComment->bindParam('author',$_SESSION['id']);
  $addComment->bindParam('event',$idevent);
  $addComment->execute();
  header("location: event.php?id=".$event['id']);
  
  exit();
 }                
if ($_SESSION['id'] === $event['author_id'] ) {
  
  if (isset($_POST['edit'])) {
                  
    $newtitle = htmlspecialchars($_POST['newTitle']);    
    $editTitle = $db ->prepare('UPDATE event SET title=? WHERE id=?' );
    $editTitle -> execute(array($newtitle, $idevent));

    // TODO: Modification image
    // $editnewimage = $_POST['newImage'];
    // $editImage = $db ->prepare ('UPDATE event SET image=? WHERE id=?');
    // $editImage ->execute(array($editnewimage, $idevent));
                  
    $newdate = htmlspecialchars($_POST['newDate']);
    $editdate = $db -> prepare('UPDATE event SET date=? WHERE id=?');
    $editdate ->execute(array($newdate, $idevent));

    $newtime=htmlspecialchars($_POST['newHour']);
    $edittime= $db ->prepare('UPDATE event SET hour=? WHERE id=?');
    $edittime ->execute(array($newtime, $idevent));

    $newdescription=htmlspecialchars($_POST['newDescription']);
    $editdescription =$db -> prepare('UPDATE event SET description=? WHERE id=?');
    $editdescription ->execute(array($newdescription, $idevent));

    header("location: event.php?id=".$event['id']);

  }

    
  if (isset($_POST['delete'])) { 
    
    $deleteEvent = $db ->prepare("DELETE FROM event   WHERE id = ?" );
    $deleteEvent ->execute(array($idevent));

    $deletecomments = $db ->prepare ("DELETE FROM comments WHERE event_id ='$idevent'");
    $deletecomments -> execute(array($idevent));
    
    

    

    header("location: ../index.php");
    exit();
             
  }
}
}

   

?>
<!DOCTYPE html>
<html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Event Page</title>
      <link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" integrity="sha384-HzLeBuhoNPvSl5KYnjx0BT+WB0QEEqLprO+NBkkk5gbc67FTaL7XIGa2w1L0Xbgc" crossorigin="anonymous">
      <!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous"> -->
      <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
      <!-- <link rel="stylesheet" href="../assets/css/style.css"> -->
      <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
      <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
    </head>

  <body>
  
      <?php 
        include 'header.php';
        if($event['13'] ==0){
          $minToShow = '00';
      } else {
          $minToShow = $event['13'];
      }
      ?>

    <div class="container mt-5 pl-0 pr-0">
      <div class="card text-white bg-secondary mb-3 border-secondary">
        <div class="card-header d-flex text-warning justify-content-between h1">
          <?php  echo $event['title']; 
                  if (isset ($_SESSION['id'])) {
                    if ($_SESSION['id'] === $event['author_id'] ) {
                      ?>
                      <span class="card-text text-muted small align-self-center" style="font-size:18px"><?php echo $event['11'] . ' ' . $event['10'] . ' ' . $event['9'] . ' ' . $event['8'] . ' - ' . $event['12'] . ':' . $minToShow?> </span>
                      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalCenter">
                      Edit Event
                  </button>
          
                    <?php }} ?>
        </div>
        <div class="card-body">
                                <?php
                                if($event['image']){
                                    echo '<img src="https://mifato.s3.eu-west-3.amazonaws.com/' . $event['image'] . '" class="img-fluid w-100" alt="event image not found"/>';
                                } else {
                                    echo '<img src="https://mifato.s3.eu-west-3.amazonaws.com/no-image.png" class="img-fluid w-100" alt="event image not found"/>';
                                }
                                ?>
          <p class="card-text mt-5"><?php  echo $event['description']; ?></p>
        </div>
        </div>

        <?php include 'comments.php'; ?>
    


      



    <!-- Modal -->

    <form class ="formedit " method="POST">
      
      <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        
        <div class="modal-dialog-center modal-dialog modal-xl" role="document">
          
          <div class="modal-content" >
            
            <div class="modal-header">
              <h5 class="modal-title"> Edit Event </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
            </div>
              
            <div class="modal-body">
                <p> Edit Title :</p>
                <input class="modal-title form-control w-100" type="text" id="exampleModalCenterTitle"  name="newTitle" value="<?php echo $event['title'];?>">        
                <br>
              <p> Edit Date & Hour:</p>
                <div class="modal-text d-flex justify-content-left">
    
                <input class="form-control w-25" type="date"  name="newDate" value="<?php echo $event['date'];?>">
                <input class="form-control w-25" type="time" name="newHour" value="<?php echo $event['hour'];?>">
                <br>
              </div>
              
              <div class="modal-text">
                <br>
                <p>Edit description</p>
                <textarea class="form-control" rows="10" name="newDescription" type="text" data-emojiable="true" data-emoji-input="unicode"><?php echo  $event['description'];?></textarea>
              </div>
                
              <div class="modal-footer">
                  <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <input type="submit" name="edit" value="Save Changes" class="btn btn-primary">
                  <input type="submit" name="delete" value="Delete" class="btn btn-primary">
              </div>
                    </div>
                    </div>
                    </div>
          
        
      
    </form>
  </body>
</html>
