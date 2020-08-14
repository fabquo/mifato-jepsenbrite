<?php
if(isset($_SESSION['id'])){
    $gravatar = $_SESSION['email'];
    $size = 100;
    $grav_url = "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $gravatar ) ) ). "&s=" . $size;
}
?>
<header>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
<?php 
    if(isset($_SESSION['id'])){
        ?>
        
        <a class="navbar-brand flex-row text-success" href="<?php echo "profile.php?id=".$_SESSION['id'];?>">
            <img class="rounded-circle" width="30" height="30" src="<?php echo $grav_url ?>" alt="USER PICTURE">
            <?php echo $_SESSION['username']; ?>
        </a>
        <?php
    }else{
        ?>
            <a class="navbar-brand flex-row text-success" href="login.php">
                
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
        <a class="nav-link" href="../index.php">Home <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="past.php">Past Events</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="category.php">Events Category</a>
      </li>
        <?php
                        if(isset($_SESSION['id'])){
                    ?>
                            <li class="nav-item">
                                <a class="nav-link" href="create_event.php">Create Event</a>
                            </li>
                    <?php       
                        }
                    ?>
        <?php 
                if(isset($_SESSION['id'])){
                    ?>
                    <li>
                        <a class="nav-link text-danger" href="disconnect.php">Log out</a>
                    </li>
                    <?php
                }else{
                    ?>
                        <a class="nav-link text-warning" href="create_user.php">Sign in</a>
                    <?php
                }
            ?>
    </ul>
</nav>
    </header>