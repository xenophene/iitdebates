<?php
  include 'db_interface.php';
  function timeStamp ($creation_time) {
    if($creation_time <= 0) return -1;
    
    $time_difference = time() - $creation_time;
    $seconds = $time_difference;
    $minutes = round($time_difference / 60 );
    $hours = round($time_difference / 3600 ); 
    $days = round($time_difference / 86400 ); 
    $weeks = round($time_difference / 604800 ); 
    $months = round($time_difference / 2419200 ); 
    $years = round($time_difference / 29030400 ); 
    
    if ($seconds <= 60) return "$seconds seconds ago";
    else if ($minutes <= 60) {
      if ($minutes == 1) return "a minute ago"; 
      else return "$minutes minutes ago"; 
    } else if ($hours <= 24) {
      if($hours == 1) return "an hour ago";
      else return "$hours hours ago";
    } else if ($days <= 7) {
      if ($days == 1) return "a day ago";
      else return "$days days ago";
    } else if ($weeks <= 4) {
      if ($weeks == 1) return "a week ago";
      else return "$weeks weeks ago";
    } else if ($months <= 12) {
      if ($months == 1) return "a month ago";
      else return "$months months ago";
    } else {
      if ($years == 1) return "a year ago";
      else return "$years years ago";
    }
  }
  
  /* returns the current vote tally */
  function voteCount($upvotes, $downvotes) {
    if (!$upvotes) $upvote = 0;
    else $upvote = sizeof(explode(',', $upvotes));
    if (!$downvotes) $downvote = 0;
    else $downvote = sizeof(explode(',', $downvotes));
    return $upvote - $downvote;
  }
  
  function voteTally($upvotes, $downvotes) {
    $vote_tally = voteCount($upvotes, $downvotes);
    echo '<span title="View Vote Count" class="votes vote-store">'.
         '<span class="hide" id="upvoters">'.$upvotes.'</span>'.
         '<span class="hide" id="downvoters">'.$downvotes.'</span>'.
         '<span class="vote-count">'.($vote_tally).'</span>'.
         ' votes</span>';
  }
  function commentInfo($comment, $authorUid, $authorName, $myfbid) {
    $class = 'comment-data';
    if ($authorUid == $myfbid) $class = 'editable ' . $class;
    echo '
      <div class="comment" name="'.$comment['comid'].'">
      <span name="'.$authorUid.'" class="author"><a href="home.php?fbid='.$authorUid.'"><img class="author-pic" src="https://graph.facebook.com/'
              .$comment['author'].'/picture?type=square"/>'.$authorName.'</a></span>
      <span class="'.$class.'" name="'.$comment['comid'].'">'.$comment['value'].'</span>
    ';
  }
  function deleteSupportVote($comment, $user) {
    $dontShow = false;
    $date = timeStamp($comment['date']);
    if ($date != -1) echo '<span class="comment-date" title="Post Time">'.($date).'</span>';
    
    if ($comment['author'] == $user and $user) {
      $dontShow = true;
      echo '
      <span class="delete-point votes" title="Delete this point">Delete</span>';
    }
    
    if (!$dontShow and $user) {
      echo '
      <span class="support-point votes" title="Support this point">Support</span>
      <span class="rebutt-point votes" title="Rebutt this point">Rebutt</span>';
    }
    
    foreach(explode(',', $comment['upvotes']) as $upvoter) {
      if ($user == $upvoter) $dontShow = true;
    }
    foreach(explode(',', $comment['downvotes']) as $downvoter) {
      if ($user == $downvoter) $dontShow = true;
    }
    $parent_id = $comment['parentid'];
    if ($parent_id > 0)
      echo '
        <span class="view-conversation votes"
        name="'.$parent_id.'">View Conversation</span>';
    if (!$dontShow and $user) {
      echo '
        <span class="upvote icon-arrow-up" title="Vote Up"></span>
        <span class="downvote icon-arrow-down" title="Vote Down"></span>';
    }
    
  }
  
  // HAS TO BE SERIOUSLY RETHOUGHT!
  //function postUsers($pids, $pnames, $debatetopic, $debatedesc, $link) {
  //  $pidArray = explode(',', $pids);
  //  $facebook = new Facebook(array(
  //    "appId"   => APP_ID,
  //    "secret"  => APP_SECRET
  //  ));
  //  $body = array(
  //            'message'     => $debatetopic,
  //            'link'        => $link,
  //            'description' => $debatedesc,
  //            'name'        => 'IIT Debates'
  //          );
  //  
  //  for ($i = 0; $i < sizeof($pidArray) - 1; $i++) {
  //    $pid = trim($pidArray[$i]);
  //    $to ='/'.$pid.'/feed';
  //    $queries = array(
  //                'method'      => "POST",
  //                'relative_url'=> $to,
  //                'body'        => http_build_query($body)
  //              );
  //  }
  //  try{
  //    $facebook->api('/?batch='.urlencode(json_encode($queries)), 'POST');
  //  } catch (Exception $e) { echo 'error'; }
  //}
  //function removeFromString($str, $needle) {
  //  $p = explode(',', $str);
  //  if ($key = array_search($needle, $p) !== false) unset($p[$key]);
  //  $p = implode(',', $p);
  //  return $p;
  //}
  
  function sanityCheck($text) {
    return mysql_real_escape_string(stripslashes($text));
  }
  function listSanityCheck($text) {
    $text = sanityCheck($text);
    $text = array_filter(array_map('trim', array_unique(explode(',', $text))));
    return implode(',', $text);
  }
  function addToString($list, $user) {
    $a = explode(',', trim($list));
    array_push($a, $user);
    return listSanityCheck(implode(',', $a));
  }
  
  function sanitizeForClient($str) {
    $str = stripslashes($str);
    return $str;
  }

?>
