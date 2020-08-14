<?php
session_start();
	try{

		// PDO CONNEXION HERE
	} 

	catch (Exeption $e){
	
		die('erreur :' .$e ->getMessage());
	}
	use PHPMailer\PHPMailer\PHPMailer;
require('../vendor/autoload.php');


$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
// echo $actual_link;

$Parsedown = new Parsedown();
$Parsedown->setSafeMode(true);

$userGo = null;
$error = null;
$good = null;
$eventExist = null;
if(isset($_SESSION['id'])){
$sessionID = $_SESSION['id'];
}

	if(isset($_GET['id'])){

		$eventId = $_GET['id'];
		$resultat = $db->prepare("select user_id, event_id from participant where user_id=:author and event_id=:event");
        $resultat->bindParam('author', $_SESSION['id']);
        $resultat->bindParam('event', $eventId);
		$resultat->execute();
		$userGo = $resultat->rowCount();

	}

		function letsgo($event, $connexion){
			$db = $connexion;
			$inscript = $db->prepare("INSERT INTO participant (user_id, event_id) VALUES (:author, :event)");
			$inscript->bindParam('author',$_SESSION['id']);
			$inscript->bindParam('event',$event);
			$inscript->execute();

			header("location: event.php?id=".$event);
	}

		function unsub($eventtw, $usertw, $connexion){
			$db = $connexion;
			$unsub = $db->prepare("DELETE FROM participant where User_id= '$usertw' && Event_id = '$eventtw'");
			$unsub->execute();
			header("location: event.php?id=" .$eventtw);			

		}

	if(isset($_POST['letsgobtn'])){
		if($userGo == 0){
			letsgo($_GET['id'], $db);
		}
	}

	if(isset($_POST['unsubscribe'])){
		if($userGo != 0){
			unsub($_GET['id'],$_SESSION['id'],$db);
		}
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

		if($event != null){
			$eventExist = $event['id'];
		}

		if(isset($_SESSION['id'])){
		$displayUser = $db->query("SELECT * FROM user WHERE id = " .$_SESSION['id']);
		$user= $displayUser->fetch();
		$admin = false;

			if($user['is_admin'] == 1){
				$admin = true;
			}
		}
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
		if($event != null){
			if ($_SESSION['id'] === $event['author_id'] || $admin ==true) {
				if (isset($_POST['edit'])) {
					// var_dump($_POST);

					if($_POST['newVideo'] != null && $_POST['optionsRadios'] == 'video'){

						if(strpos($_POST['newVideo'], 'youtube') !== false){

							$videoSplit = explode('=', $_POST['newVideo']);
							$videoURL = end($videoSplit);
						
						} else {

							$error = 'Your video link is not a youtube video ! ';
							echo $error;

						}
					}
	
				$newName = null;
				
				if($_POST['optionsRadios'] == 'image' && isset($_FILES['newImage'])){
					// Connexion aux AWS
					$s3 = new Aws\S3\S3Client([
						'version'  => 'latest',
						'region'   => 'eu-west-3',
					]);
	
					$bucket = getenv('S3_BUCKET')?: die('No "S3_BUCKET" config var in found in env!');

					$imageSet = basename($_FILES['newImage']['name']);

					if($imageSet != null){

						$uploadImage = 1;
						$checkImage = getimagesize($_FILES['newImage']['tmp_name']);
						$targetFile = basename($_FILES['newImage']['name']);
						$imageType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));

							if($checkImage !== false){
		
								$uploadImage = 1;
	
							} else {
		
								$error = 'File is not an image. ';
								$uploadImage = 0;
							}

							if($_FILES['newImage']['size'] > 500000){
		
								$error = 'Sorry, your file is too large (must be smaller than ~500kb). ';
								$uploadImage = 0;
							}

							if(isset($imageType) && $imageType != "jpg" && $imageType != "png" && $imageType != "jpeg" && $imageType != "gif" ) {
		
								$error = "Only JPG, JPEG, PNG & GIF files are allowed. ";
								$uploadOk = 0;
							}

							if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['newImage']) && $_FILES['newImage']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['newImage']['tmp_name']) && $uploadImage == 1) {
		
								try {
			  						$newName = htmlspecialchars(md5(session_id() . microtime()).'.'.$imageType);
									$upload = $s3->upload($bucket, $newName, fopen($_FILES['newImage']['tmp_name'], 'rb'), 'public-read'); 

								} catch(Exception $e) {
									echo '<p>Upload error :(.</p>';
								}
							}
						}
					}
				

	// Fin du test image
				  
					$newtitle = htmlspecialchars($_POST['newTitle']);    
					$editTitle = $db ->prepare('UPDATE event SET title=? WHERE id=?' );
					$editTitle -> execute(array($newtitle, $idevent));

					$newaddress = htmlspecialchars($_POST['newAddress']);
					$editaddress = $db -> prepare('UPDATE event SET address=? WHERE id=?');
					$editaddress ->execute(array($newaddress, $idevent));
					
					$newpostcode = htmlspecialchars($_POST['newPostCode']);
					$editpostcode = $db -> prepare('UPDATE event SET pc=? WHERE id=?');
					$editpostcode ->execute(array($newpostcode, $idevent));

					$newcity = htmlspecialchars($_POST['newCity']);
					$editcity = $db -> prepare('UPDATE event SET city=? WHERE id=?');
					$editcity ->execute(array($newcity, $idevent));

					$newcountry = htmlspecialchars($_POST['newCountry']);
					$editcountry = $db -> prepare('UPDATE event SET country=? WHERE id=?');
					$editcountry ->execute(array($newcountry, $idevent));

					if($_POST['optionsRadios'] == 'image' && $newName != null && isset($_FILES['newImage'])) {
						echo $newName;
						$editImage = $db->prepare('UPDATE event SET image=? WHERE id=?');
						$editImage->execute(array($newName, $idevent));

					} 
					
					if($_POST['newVideo'] != null && $_POST['optionsRadios'] == 'video' && $videoURL != null) {
						
						$video = $_POST['newVideo'];
						// echo $video;
						$editImage = $db->prepare('UPDATE event SET image=? where id =?');
						$editImage->execute(array($videoURL, $idevent));
					}

					$newdate = htmlspecialchars($_POST['newDate']);
					$editdate = $db -> prepare('UPDATE event SET date=? WHERE id=?');
					$editdate ->execute(array($newdate, $idevent));

					$newtime=htmlspecialchars($_POST['newHour']);
					$edittime= $db ->prepare('UPDATE event SET hour=? WHERE id=?');
					$edittime ->execute(array($newtime, $idevent));

					$newdescription=htmlspecialchars($_POST['newDescription']);
					$editdescription =$db -> prepare('UPDATE event SET description=? WHERE id=?');
					$editdescription ->execute(array($newdescription, $idevent));

					$mailingList = "SELECT * FROM participant, event WHERE participant.Event_id = '$idevent' && event.id = '$idevent'";
					$mailing = $db -> query($mailingList);
					
					while($participant = $mailing->fetch()){

						$participantId = $participant['User_id'];
						$mailAddress = "SELECT * FROM user WHERE id = '$participantId'";
						$mAddress = $db -> query($mailAddress);
						
						while($email = $mAddress->fetch()){

						$aMail = $email['email'];
						$username = $email['username'];

                        $mail = new PHPMailer();
                        $mail->IsSMTP();

                        $mail->SMTPDebug  = 0;
                        $mail->SMTPAuth   = TRUE;
                        $mail->SMTPSecure = "ssl";
                        $mail->Port       = 465;
                        $mail->Host       = "smtp.gmail.com";
                        $mail->Username = "username@gmail.com";
                        $mail->Password = "password";

                        $mail->IsHTML(true);
                        $mail->addAddress($aMail);
                        $mail->setFrom("jepsenbritemifato@gmail.com","Jepsen-Brite");
                        $mail->Subject = 'Event modification - Take care !';
                        $content = '<h1>Hi ' .$username .',</h1><br>
                                    It seems the event <a color="black" href="http://becode.local/jepsen-brite/pages/event.php?id=' . $participant['Event_id'] .'" > ' . $participant['title'] .' </a> has been modified !"
                                    <p>Don\'t forget to check about that !</p><br><br>

                                    <p>Mifato team</p>';

						$mail->MsgHTML($content);
						$mail->send();
					}
				}

				header("location: ../pages/event.php?id=".$idevent);

			}
		}
	}

		if (isset($_POST['delete']) || isset($_POST['deleteadmin'])) { 

			$deleteEvent = $db ->prepare("DELETE FROM event   WHERE id = ?" );
			$deleteEvent ->execute(array($idevent));
			$deletecomments = $db ->prepare ("DELETE FROM comments WHERE event_id = ?");
			$deletecomments -> execute(array($idevent));
			$deleteParticipant = $db->prepare("DELETE FROM participant where Event_id =?");
			$deleteSubCat = $db->prepare("DELETE FROM event_souscategorie where Event_id = ?");
			$deleteParticipant->execute(array($idevent));
			$deleteSubCat ->execute(array($idevent));

			header("location: ../index.php");
			exit();

		}

	}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>
		<? echo $event['title']; ?>
	</title>
	<link href="https://fonts.googleapis.com/css2?family=Itim&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" integrity="sha384-HzLeBuhoNPvSl5KYnjx0BT+WB0QEEqLprO+NBkkk5gbc67FTaL7XIGa2w1L0Xbgc" crossorigin="anonymous">
	<link href="../assets/css/darkly.css" rel="stylesheet" media="screen" title="main">
	<link rel="stylesheet" href="../assets/css/theme.css">
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</head>

<body style="overflow-x: hidden">

<a href="#top" class="btn btn-light position-fixed text-black rounded-circle" style="bottom:10px; right: 10px; z-index:20"> ^ </a>
	<?php
		include 'header.php';
	
		if($eventExist != null){

			if($event['17'] ==0){
		  		$minToShow = '00';
	  		} else {
		  		$minToShow = $event['17'];
	  		}
	  
			$imageCheck = explode('.', $event['image']);
			$ext = end($imageCheck);
			$isImage = $ext;

			if($ext == 'jpeg' || $ext == 'jpg' || $ext == 'gif' || $ext == 'png'){
				$isImage = 1;
			}

			$getid = $_GET['id'];
			$countparticipant = $db ->query("SELECT *  from participant, user Where Event_id= '$getid' && participant.User_id = user.id"); 
			$rowcountpart = $countparticipant->rowCount();
			?>

			<div class="container-fluid mt-5 pl-0 pr-0">
				<div class="card text-white bg-secondary mb-3 border-secondary mx-auto">
					<div class="row card-header my-auto w-100 mx-auto">
						<div class="col-xl-6 my-auto pl-0 text-dark">
						<? 
										$authorTemp = $event['author_id'];
										$userAuthor = $db -> query("SELECT * FROM USER Where ID = $authorTemp");
									  	$userAuthor = $userAuthor -> fetch();
									  
									  	$commentrow = 0;
										$commentCount = "SELECT comments.event_id FROM comments where event_id = '$getid'";
										$commenttest= $db->query($commentCount);
										$commentrow = $commenttest->rowCount();
                  // echo $userAuthor['id'];
								  ?>
						<p class="my-auto h1 text-warning pl-0"><?php  echo $event['title'];?> </p>
						Posted by <a href="profile.php?id=<?echo $userAuthor['id'];?>" class="text-dark h5 text-decoration-none"><? echo $userAuthor['username']?></a>
						</div>
						<div class="col-xl-6 col-12 my-auto pr-0 text-right pt-3">
						<form method="post" class="text-right my-auto">
						<?php
							if (isset ($_SESSION['id'])) {
								if ($admin == true){
								?>
									<input type="submit" class="btn btn-danger p-1" name="deleteadmin" value="Delete">
								<?php }
							}
							
							if (isset ($_SESSION['id'])) {
								if ($_SESSION['id'] === $event['author_id'] ) {
								?>
									<button type="button" class="btn btn-primary ml-2 p-1" data-toggle="modal" data-target="#exampleModalCenter">
										Edit Event
									</button>
								<?php 
								}
							
							if($userGo == 0){
							?>
									<input type="submit" class="btn btn-success ml-2 p-1" name="letsgobtn" value="Subscribe">
									</input>
							<?php } else { ?>
									<input type="submit" class="btn btn-dark ml-2 p-1" name="unsubscribe" value="Unsubscribe"> 
									</input>
							<?php }
							} ?>
							</form>
						</div>
						</div>
					<div class="row card-header mx-0">
						<div class="card-text text-muted small col-xl-4 col-md-4 col-lg-4 col-12 pl-0 text-center" style="font-size:16px">
							<?php 
								echo $event['15'] . ' ' . $event['14'] . ' ' . $event['13'] . ' ' . $event['12'] . ' - ' . $event['16'] . ':' . $minToShow
							?>
						</div>
						<div class="card-text text-muted small  col-xl-4 col-md-4 col-lg-4 col-12  pl-0 text-center" style="font-size:16px">
							<? 
							
							echo '<p class="card-text small text-muted"><a href="#comments" class="far fa-comments btn p-0"></a> :'. $commentrow .' | <i class="fas fa-users btn p-0" data-toggle="modal" data-target="#participant"></i> : '. $rowcountpart . '</p>';
							
							?>
						</div>
						<div class="card-text text-muted small align-self-center  col-xl-4 col-md-4 col-lg-4 col-12  text-center pr-0" style="font-size:16px">
							<?php 
								echo $event['8'] . ', ' . $event['9'] . ' - ' . $event['10'] . ' - ' . $event['11']
							?>
						</div>
					</div>
					<div class="card-body row mx-auto w-100 px-0">
						<div class="col-xl-6">
							<div class="card-text text-justify text-primary"><?php  echo $Parsedown->text($event['description']); ?></div>
						</div>
					<div class="col-xl-6 text-center">
						<?php
							if($isImage){
								if($isImage == 1){
									echo '<img src="https://mifato.s3.eu-west-3.amazonaws.com/' . $event['image'] . '" class="img-fluid w-100" alt="event image not found"/>';
								} else {
									echo '<div class="embed-responsive embed-responsive-16by9">';
									echo '<iframe class="embed-responsive-item" src="https://www.youtube.com/embed/' .$event['image']. '" allowfullscreen></iframe>';
									echo '</div>';
								} 
							} else {
								echo '<img src="https://mifato.s3.eu-west-3.amazonaws.com/no-image.png" class="img-fluid w-50" alt="event image not found"/>';
							}?>
						<div id="map-container-google-1" class="z-depth-1-half map-container w-100 justify-content-center mt-5">
									<iframe class="w-100" src="https://google.com/maps/embed/v1/search?key=APIGOOGLEKEY&q=<?php echo $event['8'] .'+' .$event['9']. '+' . $event['10'] . '+' . $event['11'];?>" frameborder="0" style="border:0" height="400px" allowfullscreen></iframe>
						</div>
						<div class="text-center mt-3" height="50px">
						<?php
							echo '<iframe height="50px" width="95px" src="https://www.facebook.com/plugins/share_button.php?href='.$actual_link.'&layout=button&size=large&mobile_iframe=true&width=83&height=28&appId"  style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>';
						?>
						</div>
					</div>
				</div>
			</div>
  <!-- Modal Participants-->
  <? include 'comments.php'; ?>
  <div class="modal fade" id="participant" tabindex="-1" role="dialog"
			aria-labelledby="exampleModalCenterTitle" aria-hidden="true">

			<div class="modal-dialog-center modal-dialog modal-m" role="document">

				<div class="modal-content">

					<div class="modal-header">
            <h5 class="modal-title text-center"> Participants </h5>
          </div>
            
              <?php 
               while ($participant = $countparticipant->fetch()) {
              //  var_dump($participant['User_id']);
              
              echo '<a href="profile.php?id='.$participant['User_id'] .'"   class="modal-text text-center btn btn-success btn-md btn-block mb-1">'.$participant['username'] . '</a>'; } ?>
						<button type="button" class="close mb-3 mt-3" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
            </button>
  
        </div>
      </div>   
</div>

  
<!-- Modal Edit -->

	<form class="formedit " enctype="multipart/form-data" action="<?=$_SERVER['PHP_SELF'].'?id='.$event['id']?>"
		method="POST">

		<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
			aria-labelledby="exampleModalCenterTitle" aria-hidden="true">

			<div class="modal-dialog-center modal-dialog modal-xl" role="document">

				<div class="modal-content">

					<div class="modal-header">
						<div class="w-100">
						<h5 class="modal-title"> Edit Event </h5>
						<small class="my-auto text-danger">You can not modify category or sub category here ! Contact admins for support !</small>
			   			</div>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						</div>

					<div class="modal-body">
					<div class="row px-3 justify-content-between">
            				<label class="col-form-label px-0 col-xl-1 mt-3" for="title" >Title</label>
            				<input class="form-control col-xl-11 mt-3" type="text" name="newTitle" id="title" value="<? echo $event['title']?>">
            			</div>
						<div class="row px-3 justify-content-between mt-3">
            				<label class="col-form-label px-0 col-xl-1" for="address" >Address</label>
            				<input class="form-control col-xl-11" type="text" name="newAddress" id="address" value="<? echo $event['address']?>">
						</div>
						<div class="row px-3 mt-3 justify-content-between">
							<label class="col-form-label px-0 col-xl-1" for="postal">Postcode </label>
							<input class="form-control col-xl-3" type="text" name="newPostCode" id="postal" value="<? echo $event['pc']?>">
							<label class="col-form-label col-xl-1" for="city">City</label>
							<input class="form-control col-xl-3" type="text" name="newCity" id="city" value="<? echo $event['city']?>">
							<label class="col-form-label col-xl-1" for="country">Country</label>
							<input class="form-control col-xl-3" type="text" name="newCountry" id="country" value="<? echo $event['country']?>">
						</div>
						<div class="row px-3 mt-3 justify-content-between">
							<label class="col-form-label px-0 col-xl-1" for="date">Date</label>
							<input class="form-control col-xl-5" type="date" name="newDate" id="date" value="<? echo $event['date']?>">
							<label class="col-form-label col-xl-1" for="newHour">Hour</label>
							<input class="form-control col-xl-5" type="time" name="newHour" id="time" value="<? echo $event['hour']?>">
            			</div>
						<label class="col-form-label mt-3">Make a choice</label>
						<!-- <fieldset class="form-group"> -->
							<div class="form-check">
								<label class="form-check-label">
									<? if($isImage == 1){?>
									<input type="radio" class="form-check-input" name="optionsRadios"
										id="optionsRadios1" value="image" checked=""><?} else { ?>
									<input type="radio" class="form-check-input" name="optionsRadios"
									id="optionsRadios1" value="image">
										<?}	?>
									Upload an image
								</label>
							</div>
							<div class="form-check">
								<label class="form-check-label">
								<? if($isImage == 0){?>
									<input type="radio" class="form-check-input" name="optionsRadios"
									id="optionsRadios1" value="video" checked="">
									<?} else { ?>
										<input type="radio" class="form-check-input" name="optionsRadios"
										id="optionsRadios2" value="video">
										<?}	?>
									Link a video
								</label>
							</div>
							<label class="col-form-label mt-3" for="newImage">Image <small>(must be .jpeg, .jpg, .gif,
									.png and
									smaller
									than
									500kb)</small> : </label>
							<input name="newImage" type="file" class="form-control-file" id="newImage"><br />
							<label class="col-form-label" for="newVideo">video URL <small>(past here the entire URL of your
									video.
									Example : https://www.youtube.com/watch?v=u0yHr6sdf7Q)</small>:</label><br />
							<?php
			if($isImage == 1) {
			echo '<input class="form-control" type="text" name="newVideo" id="video"><br />';
		  } else {
			echo '<input class="form-control" type="text" name="newVideo" id="video" value="https://www.youtube.com/watch?v=' . $event['image'] . '"><br />';
		  }
			?>
							<div class="modal-text">
								<br>
								<p>Edit description</p>
								<textarea class="form-control" rows="10" name="newDescription" type="text"
									data-emojiable="true"
									data-emoji-input="unicode"><?php echo  $event['description'];?></textarea>
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
	<?php } else {?>
	<div class="container">
        <div class="jumbotron mt-5">
            <h4 class="text-center mt-3">Event doesn't exist !</h4>

		</div>
	<?php } ?>
</body>
</html>