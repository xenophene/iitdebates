<?php
  require_once 'facebook.php';
  
  $hn = "localhost";
  $user = "root";
  $pwd = "";
  $db = "iitdebates";
  
  $conn = new mysqli($hn, $user, $pwd, $db);
  
  if ($conn->connect_errno) die("Could not connect database");
  
  define('APP_ID', '139456172868758');
  define('APP_SECRET', 'a7a5fbe24d85d85865e53c6661ff00f3');
  define('DOMAIN', '//localhost/iitdebates/');
  define('PUSHER_APP_ID', '26310');
  define('PUSHER_APP_KEY', '30650aaee53dcd153667');
  define('PUSHER_APP_SECRET', '0cda60cc48b6bd735061');
  
  $fb = new Facebook(array(
                     "appId"   =>  APP_ID,
                     "secret"  =>  APP_SECRET
                    ));
  $params = array('scope' => 'publish_stream, publish_actions');
?>
