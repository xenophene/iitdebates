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
      case 5: postComment($_POST, $conn, $fb); break;
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
  function postComment($_POST, $conn, $fb) {
    require 'Pusher.php';
    require 'aux_functions.php';
    
    $author = sanityCheck($_POST['author']);
    $authorName = sanityCheck($_POST['authorname']);
    $value = sanityCheck($_POST['value']);
    $debid = sanityCheck($_POST['debid']);
    $foragainst = sanityCheck($_POST['foragainst']);
    $parentComId = sanityCheck($_POST['parentId']);

    $d = time();
    $query = "INSERT INTO `comments` (`author`, `value`, `debid`, `foragainst`, `parentid`, `date`) ".
             "VALUES ('$author', '$value', '$debid', '$foragainst', '$parentComId','$d')";
    $conn->insert_id();
    if ($result = $conn->query($query)) {
      echo $conn->insert_id();
    }
    /* author will be fbid of the user. if he is not a part of the participants of
       the debate, we will add him. additionally, add his points to the debate */
    if ($result = $conn->query("SELECT * FROM `debates` WHERE `debid`='$debid'")) {
      while ($row = $result->fetch_assoc()) {
        $p = explode(',', $row['participants']);
        $f = explode(',', $row['followers']);
        if (!in_array($author, $p)) {
          array_push($p, $author);
        }
        $uscore = getUserScore($author);
        $conn->query("UPDATE `debates` SET `debscore`=`debscore`+'" . $uscore . "' WHERE `debid`='$debid'");
        if (!in_array($author, $f)) {
          array_push($f, $author);
        }
        $p = implode(',', $p);
        $f = implode(',', $f);
        $query = "UPDATE `debates` SET `participants`='$p', `followers`='$f' WHERE `debid`='$debid'";
        $conn->query($query);
        $query = "UPDATE `debates` SET `token`=`token`+1 WHERE `debid`='$debid'";
        $conn->query($query);
      }
    }
    /* Creating data to be sent to every user subscribed to this debate and push real time comment to his browser.*/
    $channel_name = 'comments-' . $_POST['debid'];
    $event_name = 'new_comment';
    $data = new array(
	    'author'     => $author,
	    'authorname' => $authorName,
	    'value'			 => $value,
	    'foragainst' => $foragainst
    );
    $socket_id = isset($_POST['socket_id'] ? $_POST['socket_id'] : null);

    $pusher = new Pusher(PUSHER_APP_KEY, PUSHER_APP_SECRET, PUSHER_APP_ID);
    $pusher->trigger($channel_name, $event_name, $data, $socket_id);
  }
  function pass() {}
?>
