<?php
  /* One all, end all place for all small & big AJAX scripts. Given a
     fid-> function id
     suitable arguments
     map it to a function implemented which does the right thing!
     Each of these functions do the appropriate argument containment checks
     MOVE ALL DB INTERFACE FUNCTIONS TO DB_INTERFACE
  */
  include 'config.php';
  require 'aux_functions.php';
  if (isset($_POST['fid'])) {
    
    $fid = $_POST['fid'];
    switch($fid) {
      case 1: removeComment($_POST, $conn, $fb); break;
      case 2: getMyFriends($_POST, $conn, $fb); break;
      case 3: getUsers($_POST, $conn, $fb); break;
      case 4: inviteFriends($_POST, $conn, $fb); break;
      case 5: postComment($_POST, $conn, $fb); break;
      case 6: removeDebate($_POST, $conn, $fb); break;
      case 7: followeDebate($_POST, $conn, $fb); break;
      case 8: postVote($_POST, $conn, $fb); break;
      case 9: followUser($_POST, $conn, $fb); break;
      case 10: changeInterests($_POST, $conn, $fb); break;
      case 11: editDebates($_POST, $conn, $fb); break;
      case 12: editProfile($_POST, $conn, $fb); break;
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
  /* SCALING UP ISSUE */
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
  // TO DO: Need to notify these users
  function inviteFriends($POST, $conn, $fb) {
    $ids = listSanityCheck($_POST['ids']);
    $idNames = listSanityCheck($_POST['idNames']);
    addUsers($conn, $ids, $idNames);
    
    $debid = sanityCheck($_POST['debid']);
    $inviterName = sanityCheck($_POST['inviterName']);
    $inviterId = sanityCheck($_POST['inviterId']);
    
    // add each as participants and notify them
    $query = "SELECT * FROM `debates` WHERE `debid`='$debid'";
    if ($result = $conn->query($query)) {
      if ($row = $result->fetch_assoc()) {
        $f = listSanityCheck($row['followers'] . $ids);
        $query = "UPDATE `debates` SET `followers`='$f' WHERE `debid`='$debid'";
        $conn->query($query);
      }
    }
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
    $conn->insert_id();
    $query = "INSERT INTO `comments` (`author`, `value`, `debid`, `foragainst`, `parentid`, `date`) ".
             "VALUES ('$author', '$value', '$debid', '$foragainst', '$parentComId', UNIX_TIMESTAMP())";
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
    $data = array(
	    'author'     => $author,
	    'authorname' => $authorName,
	    'value'			 => $value,
	    'foragainst' => $foragainst
    );
    $socket_id = (isset($_POST['socket_id'])) ? $_POST['socket_id'] : null;

    $pusher = new Pusher(PUSHER_APP_KEY, PUSHER_APP_SECRET, PUSHER_APP_ID);
    $pusher->trigger($channel_name, $event_name, $data, $socket_id);
  }
  function removeDebate($_POST, $conn, $fb) {
    $debid = $_POST['debid'];
    $user = $_POST['user'];
    if ($user != $fb->getUser()) return;  // can only delete my own debates
    // need to also unsubscribe from the UPDATES HERE!
    $query = "SELECT * FROM `debates` WHERE `debid`='$debid'";
    if ($result = $conn->query($query)) {
      if ($row = $result->fetch_assoc()) {
        $p = removeFromString($row['participants'], $user);
        $f = removeFromString($row['followers'], $user);
        $query = "UPDATE `debates` SET `participants`='$p', `followers`='$f' WHERE `debid`='$debid'";
        $conn->query($query);
        removeUpdateEntry($conn, $user, $debid);
      }
    }
  }
  function followDebate($_POST, $conn, $fb) {
    $debid = $_POST['debid'];
    $nf = $_POST['follower'];
    $user = $fb->getUser();
    if ($nf != $user or !$user) return;
    
    $query = "SELECT * FROM `debates` WHERE `debid`='$debid'";
    if ($result = $conn->query($query)) {
      if ($row = $result->fetch_assoc()) {
        $nf = addToString($row['follower'], $nf);
        $query = "UPDATE `debates` SET `followers`='$nf' WHERE `debid`='$debid'";
        $conn->query($query);
        $debatetopic = $row['topic'];
        $query = "SELECT `name` FROM `users` WHERE `fbid`='$user'";
        if ($result = $conn->query($query)) {
          if ($row = $result->fetch_assoc()) {
            updateActivity($conn, $user, 3, $debid, $row['name'], $debatetopic);
          }
        }
      }
    }
  }
  
  function postVote($_POST, $conn, $fb) {
    $comid = sanityCheck($_POST['comid']);
    $userid = sanityCheck($_POST['userid']);
    $upvote = sanityCheck($_POST['upvote']);
    $user = $fb->getUser();
    if ($userid != $user or !$user) return;
    $query = "SELECT * FROM `comments` WHERE `comid`='$comid'";
    if ($result = $conn->query($query)) {
      if ($row = $result->fetch_assoc()) {
        if ($upvote == 1) $key = 'upvotes';
        else $key = 'downvotes';
        $value = $row[$key];
        $value = addToString($value, $userid);
        $uscore = getUserScore($conn, $userid);
        $query = "UPDATE `comments` SET `".$key."`='$value', ".
                 "`score`=`score`+'$uscore' WHERE `comid`='$comid'";
        $conn->query($query);
      }
    }
  }
  
  function followUser($_POST, $conn, $fb) {
    $followee = sanityCheck($_POST['followee']); // the user who is to be followed
    $follower = sanityCheck($_POST['follower']); // the user who wants to follow uid
    $follow = sanityCheck($_POST['follow']); // whether to follow or unfollow
    $user = $fb->getUser();
    if ($follower != $user or !$user) return;
    if ($follow) {
      $query = "INSERT INTO `follower` (`uid`, `fbid`) VALUES ('$followee', '$follower')";
      $conn->query($query);
    } else {
      $query = "DELETE FROM `follower` WHERE `uid`='$followee' AND `fbid`='$follower'";
      $conn->query($query);
    }
  }
  function changeInterests($_POST, $conn, $fb) {
    require 'aux_functions.php';
    $fbid = sanityCheck($_POST['fbid']);
    $interest = sanityCheck($_POST['interests']);
    $user = $fb->getUser();
    if ($user != $fbid or !$user) return;
    $query = "UPDATE `users` SET `interests`='$interest' WHERE `fbid`='$fbid'";
    $conn->query($query);
  }
  
  /* CHAL NAHI RAHA ABHI */
  function editDebates($_POST, $conn, $fb) {
    $new_value = sanityCheck($_POST['update_value']);
    $type 	   = sanityCheck($_POST['field_type']);
    $old_value = sanityCheck($_POST['original_html']);
    $id        = sanityCheck($_POST['id']);
    switch ($type) {
      case 'desc':
        $query = "UPDATE `debates` SET `description`='$new_value' WHERE `debid`='$id'";
        break;
      case 'topic':
        $query = "UPDATE `debates` SET `topic`='$new_value' WHERE `debid`='$id'";
        break;
      case 'comment':
        $query = "UPDATE `comments` SET `value`='$new_value' WHERE `comid`='$id'";
        break;
      default: $query = '';
    }
    if ($conn->query($query)) {
      echo sanitizeForClient($new_value);
    } else {
      echo sanitizeForClient($old_value);
    }
  }
  
  function editProfile($_POST, $conn, $fb) {
    $new_value = sanityCheck($_POST['update_value']);
    $type 	   = sanityCheck($_POST['field_type']);
    $old_value = sanityCheck($_POST['original_html']);
    $id        = sanityCheck($_POST['id']);
    $query     = "UPDATE `users` SET `interests`='$new_value' WHERE `fbid`='$id'";
    if ($conn->query($query)) {
      echo sanitizeForClient($new_value);
    } else {
      echo sanitizeForClient($old_value);
    }
  }
  function pass() {}
?>