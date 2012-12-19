<?php
  require_once 'includes/facebook.php';
  require_once 'includes/config.php';
	                
  $user = $fb->getUser();
  if ($user) header('Location: home.php');
?>
<!DOCTYPE html>
<html>
  <head>
    <title>IIT Debates</title>
    <link rel="icon" href="includes/assets/ico/favicon.ico"/>
    <link rel="stylesheet" href="includes/assets/css/welcome.css"/>
  </head>
  <body>
    <div id="desc" class="well">
      Have something to ask? Have someone to pick a bone with? Have something to settle? Where is your point of view? <br/><br/>
      <span class="welcome">Well, welcome to <strong>IIT Debates</strong>.</span><br/><br/>
      Start your own debates, invite your friends to express their views, get rated and compete among your friends. 
      <br>Get invited to popular debates, give your opinions and get noticed. Raise issues close to your heart and your institute.<br>
      Have them settled the <strong>right way</strong>.<br/><br/>
      <a href="<?php echo $fb->getLoginUrl($params);?>" class="btn btn-primary btn-large">Sign in with Facebook</a>
      <ul>
        <li class="first"><a href="fb-ju-ab.php#join-us">Join Us</a></li>
        <li class="second"><a href="fb-ju-ab.php#feedback">Feedback</a></li>
        <li><a href="fb-ju-ab.php#about">About</a></li>
      </ul>
    </div>
  </body>
</html>
