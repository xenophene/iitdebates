<?php
  /* all db interface query functions go here! */
	/* NEED TO CHECK: FB->API(ME) IN WILL LEAD TO DUPLICATION */
	
  function getUserProfile($conn, $key, $value, $fb) {
		$myfbid = $fb->getUser();
    $query = "SELECT * FROM `users` WHERE `$key`='$value'";
    if ($result = $conn->query($query)) {
      if ($result->num_rows) {
				$row = $result->fetch_assoc();
			} else if ($key === 'uid') {
				$row = array();
			} else if ($value == $myfbid) {
        // insert this user into the table. and return the tuple
        try {
          $profile = $fb->api('/me');
          $name = $profile['name'];
        } catch (FacebookApiException $e) {
          header('Location: index.php');
        }
        $query = "INSERT INTO `users` (`fbid`,`name`) VALUES ('$value','$name')";
        $conn->query($query);
        $query = "SELECT * FROM `users` WHERE `$key`='$value'";
        $result->close();
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
      } else {
				$row = array();
			}
      $result->close();
      return $row;
    }
    return array();
  }
  
  function navigateInto($conn, $fb, $g) {
    $myfbid = $fb->getUser();
		
    if (isset($g['fbid'])) {
      $qfbid = $g['fbid'];
      $up = getUserProfile($conn, 'fbid', $qfbid, $fb);
      $up['me'] = $myfbid == $qfbid;
			
    } else if (isset($g['uid'])) {
      $quid = $g['uid'];
      $up = getUserProfile($conn, 'uid', $quid, $fb);
      $up['me'] = $up['fbid'] == $myfbid;
			
    } else if ($myfbid) {
      $up = getUserProfile($conn, 'fbid', $myfbid, $fb);
      try {
        $profile = $fb->api('/me');
      } catch (FacebookApiException $e) {
        header('Location: index.php');
      }
      $up['me'] = true;
    } else {
      header('Location: index.php');
    }
    $up['signed_in'] = $myfbid;
    return $up;
  }
  
  function getConnections($conn, $key1, $value, $key2) {
    $query = "SELECT `users`.`fbid` FROM `follower`, `users` ".
             "WHERE `follower`.`$key1`='$value' AND `follower`.`$key2`=`users`.`$key2`";
    if ($result = $conn->query($query)) {
      $ids = array();
      while ($row = $result->fetch_assoc()) {
        array_push($ids, $row['fbid']);
      }
      $result->close();
      return $ids;
    } else return array();
  }
  
	/* THIS NEEDS TO CHANGE. HAVE PARTICIPANTS AS SEPARATE ROW IN NEW TABLE */
  function getDebatesFollowed($conn, $fbid) {
    $query = "SELECT * FROM `debates` ".
             "WHERE `followers` LIKE '%$fbid%' ".
			       "ORDER BY `startdate` DESC";
    $result = $conn->query($query);
    return $result;
  }
  
  function updateActivity ($conn, $source, $type, $target, $sourcename, $targetname) {
    $t = time();
    $query = "INSERT INTO `updates` (`source`,`type`,`target`,`sourcename`,`targetname`,`timestamp`) ".
             "VALUES ('$source','$type','$target','$sourcename','$targetname', '$t')";
    $conn->query($query);
  }
	
  function removeUpdateEntry($conn, $source, $target) {
    $query = "DELETE FROM `updates` WHERE `source`='$source' AND `target`='$target'";
    $conn->query($query);
  }
	/* NEED TO CHANGE THE USER COLUMN DEBATES - TO BE A SEPARATE TABLE Nx3 (DEBID, FOLLOWER, TOKEN)
	WHICH IS COMPARED WITH THE CENTRAL TOKEN FOR THE DEBATE*/
  function updateToken($conn, $user, $debate, $token) {
    $query = "SELECT `debates` FROM `users` ".
             "WHERE `fbid`='$user'";
    if ($result = $conn->query($query)) {
      if ($row = $result->fetch_assoc()) {
        $Dtokens = explode(',', $row['debates']);
        $temp = 'no';
        for ($i = 0; $i < sizeof($Dtokens); $i++) {
          $t = explode(':', $Dtokens[$i]);
          if($t[0] == $debate and $t[1] != $token) {
            $Dtokens[$i] = $debate.':'.$token;
            $debates = implode(",",$Dtokens);
            $conn->query("UPDATE `users` SET `debates`='$debates' ".
                         "WHERE `fbid`='$user'");
          }
        }
      }
    }
  }
  
	/* INVITATION TO BE ACCEPTED BEFORE FOR NEW USERS */
	function addUsers($conn, $pids, $pnames) {
    $pidArray = explode(',', $pids);
    $pnameArray = explode(',', $pnames);
    for ($i = 0; $i < sizeof($pidArray); $i++) {
      $pid = $pidArray[$i];
      $pname = $pnameArray[$i];
      $query = "SELECT * FROM `users` WHERE `fbid`='$pid'";
      if ($result = $conn->query($query)) {
        if (!$result->num_rows) {
          $query = "INSERT INTO `users` (`fbid`, `name`) VALUES ".
                    "('$pid', '$pname')";
          $conn->query($query);
        }
      }
    }
  }
	
  function getDebateCreator($conn, $userid) {
    $query = "SELECT * FROM `users` WHERE fbid='$userid'";
    if ($result = $conn->query($query)) {
      if ($row = $result->fetch_assoc()) {
        $result->close();
        return $row;
      }
      return array();
    }
    return array();
  }
	
	/* CONSISTENCY OF RETURN TYPES! */
  function getDebate($conn, $debid) {
    $query = "SELECT * FROM `debates` WHERE `debid`='$debid'";
    if ($result = $conn->query($query)) {
      if ($row = $result->fetch_assoc()) {
        $result->close();
        return $row;
      }
      header('Location: home.php');
    } else {
      header('Location: home.php');
    }
  }
  function getUserScore($conn, $fbid) {
    $query = "SELECT `score` FROM `users` WHERE `fbid`='$fbid'";
    if ($result = $conn->query($query)) {
      if ($row = $result->fetch_assoc()) {
        if (!empty($row['debscore'])) $uscore = $row['debscore'];
        else $uscore = 0;
        return $uscore;
      }
    }
    return 0;
  }
	
	/* WILL BE CHANGED AFTER TABLE FORMAT CHANGES */
  /*Return the array of (debate,change) for the $user */
  function debateUpdates($conn, $user) {
    $query = "SELECT `debates` FROM `users` ".
             "WHERE `fbid`='$user'";
    if ($result = $conn->query($query)) {
      while ($raw = $result->fetch_assoc()) {
        $Dtokens = explode(',', $raw['debates']);
        $didArray = array();
        $debateArray = array();
        for ($i = 0; $i < sizeof($Dtokens); $i++) {
          $temp = explode(':', $Dtokens[$i]);
          $didArray[] = $temp[0];
          $debateArray[$temp[0]] = intval($temp[1]);
        }
        if(sizeof($didArray) > 1) {
          // only if the user follow at least one debate.   
          $da = implode(',', $didArray);
          $query = "SELECT `debid`,`token` FROM `debates` ".
                   "WHERE `debid` IN (".$da.")";
          if ($result = $conn->query($query)) {
            while ($row = $result->fetch_assoc()) {
              $debateArray[$row['debid']] = intval($row['token']) - $debateArray[$row['debid']];
            }
          }
        }
        return $debateArray;
      }
    }
    return array();
  }
	/* BETTER LOGIC TO BE IMPLEMENTED */
  function getActivities($conn) {
    /*Commented out because right now no fixed policy how to show updates
     * That's why selecting all the udpates from the table and showing.
     * whihch can be filtered and show only updates from $friends(which is
     * not properly defined now)*/
	
	  $query = "SELECT * FROM `updates` ORDER BY `updateid` DESC";
	  if ($result = $conn->query($query)) {
	    while ($row = $result->fetch_assoc()) {
	      $type = $row['type'];
	      $sourceid = $row['source'];
	      $targetid = $row['target'];
	      $sourcename = $row['sourcename'];
	      $targetname =$row['targetname'];
	      echo '<div class="update">';
	      switch ($type) {
		case 0: // created debate 
		  echo '<a href="home.php?fbid='.$sourceid.'">'.$sourcename.'</a> started debate '.
		       '<a href="debate.php?debid='.$targetid.'">'.$targetname.'</a>';
		  break;
		case 1: // challenged on debate
		  echo '<a href="home.php?fbid='.$sourceid.'">'.$sourcename.'</a> challenged '.
		       'to debate on <a href="home.php?fbid='.$targetid.'">'.$targetname.'</a>';
		  break;
		case 2: // followed User
		  echo '<a href="home.php?fbid='.$sourceid.'">'.$sourcename.'</a> is now following '.
		       '<a href="home.php?fbid='.$targetid.'">'.$targetname.'</a>';
		  break;
		case 3: // followed Debate
		  echo '<a href="home.php?fbid='.$sourceid.'">'.$sourcename.'</a> is now following '.
		       'the debate <a href="debate.php?debid='.$targetid.'">'.$targetname.'</a>';
		  break;
	      }
	      echo '</div>';
	    }
    }
  }
  function commentsArray($conn, $debid) {
    $query = "SELECT * FROM `comments`, `users` WHERE debid='$debid' ".
             "AND `author`=`fbid` ORDER BY `comments`.`comid` DESC";
    if ($result = $conn->query($query)) {
      $comments = array();
      while ($row = $result->fetch_assoc()) {
        array_push($comments, $row);
      }
      $result->close();
      $votes = array();
      $comids = array();
      foreach ($comments as $key => $row) {
        $votes[$key] = voteCount($row['upvotes'], $row['downvotes']);
        $comids[$key] = $row['comid'];
      }
      array_multisort($votes, SORT_DESC, $comids, SORT_DESC, $comments);
      return $comments;
    }
    return array();
  }
  
?>
