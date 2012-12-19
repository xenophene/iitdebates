<?php
  /* One all, end all place for all small & big AJAX scripts. Given a
     fid-> function id
     suitable arguments
     map it to a function implemented which does the right thing!
  */
  include 'config.php';
  if (isset($_POST['fid'])) {
    
    $fid = $_POST['fid'];
    switch($fid) {
      case 1: removeComment($_POST, $conn, $fb); break;
      case 2: getMyFriends($_POST, $conn, $fb); break;
      case 3: getUsers($_POST, $conn, $fb); break;
      case 4: inviteFriends($_POST, $conn, $fb); break;
      default: pass();
    }
  }
  function removeComment($p, $conn, $fb) {
    $comid = $p['comid'];
    $user = $fb->getUser();
    $query = "SELECT * FROM `comments` WHERE `comid`='$comid'";
    if ($result = $conn->query($query)) {
      while ($row = $result->fetch_assoc()) {
        if ($row['author'] == $user) {
          $query = "DELETE FROM `comments` WHERE `comid`='$comid'";
          $conn->query($query);
        }
      }
    }
  }
  function getMyFriends($p, $conn, $fb) {
    $user = $fb->getUser();
    if ($user) {
      try {
        $access_token = $fb->getAccessToken();
        $friends = $fb->api('/me/friends/', 'GET');
        echo json_encode($friends);
      }
      catch(FacebookApiException $e) {}
    }
  }
  function getUsers($p, $conn, $fb) {
    $rows = array();
    $query = "SELECT `uid`, `name` FROM `users`";
    if ($result = $conn->query($query)) {
      while ($row = $result->fetch_assoc()) {
        $t = array();
        $t['name'] = $row['name'];
        $t['uid'] = $row['uid'];
        $t['ltype'] = 'u';
        array_push($rows, 
                  array(
                    'name'   => $row['name'],
                    'uid'    => $row['uid'],
                    'ltype'  => 'u'
                  )
        );
      }
    }
    $query = "SELECT `debid`, `topic` FROM `debates`";
    if ($result = $conn->query($query)) {
      while ($row = $result->fetch_assoc()) {
        array_push($rows, 
                    array(
                      'name'   => $row['topic'],
                      'uid'    => $row['debid'],
                      'ltype'  => 'd'
                    )
          );
      }
    }
    echo json_encode($rows);
  }
  function inviteFriends($POST, $conn, $fb) {
  }
  function pass() {}
?>
