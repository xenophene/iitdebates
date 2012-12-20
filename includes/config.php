<?php
  require_once 'facebook.php';
  
  $hn = "localhost";
  $user = "root";
  $pwd = "";
  $db = "debators";
  
  $conn = new mysqli($hn, $user, $pwd, $db);
  
  if ($conn->connect_errno) die("Could not connect database");
  
  define('APP_ID', '139456172868758');
  define('APP_SECRET', 'a7a5fbe24d85d85865e53c6661ff00f3');
  define('DOMAIN', '//localhost/iitdebates/');
  
  $fb = new Facebook(array(
                     "appId"   =>  APP_ID,
                     "secret"  =>  APP_SECRET
                    ));
  $params = array('scope' => 'publish_stream, publish_actions');
?>
