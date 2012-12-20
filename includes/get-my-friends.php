<?php
/* return my facebook friends names & ids */
include 'config.php';

$user = $fb->getUser();
/* send the user to login page if he is not correctly logged in */
if ($user) {
  try {
    $access_token = $fb->getAccessToken();
    $friends = $fb->api('/me/friends/', 'GET');
    echo json_encode($friends);
  }
  catch(FacebookApiException $e) {}
}
?>
