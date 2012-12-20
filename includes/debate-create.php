<?php
  include 'config.php';
  include 'aux_functions.php';
  $user = $fb->getUser();
  if (!$user) echo 0;
  
  if (!(!empty($_POST['debate-topic']) and isset($_POST['debate-desc']) and
        isset($_POST['debate-theme']) and isset($_POST['participant-ids']) and
        isset($_POST['participants']) and isset($_POST['time-limit']) and
        isset($_POST['privacy']) and isset($_POST['post-to-fb-input']) and isset($_POST['uname']))) {
    echo 0;
  } else {
    // there should be a more sane sanity check here !
    $debatetopic = sanityCheck($_POST['debate-topic']);
    $query = "SELECT * FROM `debates` WHERE `topic`='$debatetopic' AND `creator`='$user'";
    if ($result = $conn->query($query)) {
      if (!$result->num_rows) {
        $debatedesc = sanityCheck($_POST['debate-desc']);
        $debatetheme = listSanityCheck($_POST['debate-theme']);
        $participants = listSanityCheck($_POST['participant-ids']);
        $participant_names = listSanityCheck($_POST['participants']);
        $timelimit = sanityCheck($_POST['time-limit']);
        $privacy = sanityCheck($_POST['privacy']);
        $post_to_fb = sanityCheck($_POST['post-to-fb-input']);
        $name = sanityCheck($_POST['uname']);
        
        /* for each of these participants, if there are some not in the db, we add them */
        addUsers($conn, $participants, $participant_names);
        
        $debscore = 0;
        
        $participants = addToString($participants, $user);
        $query = "INSERT INTO `debates` (`topic`, `description`, `timelimit`, `themes`,".
                 "`participants`, `followers`, `creator`, `startdate`, `privacy`,`time`) VALUES ".
                 "('$debatetopic', '$debatedesc', '$timelimit', '$debatetheme', ".
                 "'', '$participants', '$user', '".date('Y,m,d')."', '$privacy', UNIX_TIMESTAMP())";
        
        $conn->insert_id;
        $conn->query($query);
        $debid = $conn->insert_id;
        
        //POSTING WILL BE RETHOUGHT AND CONVERTED TO STORIES
        //if ($post_to_fb == '1')
        //  postUsers($participants, $participant_names, $debatetopic, 
        //            $debatedesc, DOMAIN . 'debate.php?debid=' . $debid);
        $type = $privacy == '1' ? 1 : 0;
        updateActivity($conn, $user, $type, $debid, $name, $debatetopic);
        echo $debid;
      } else {
        $row = $result->fetch_assoc();
        $debid = $row['debid'];
        echo $debid;
      }
    }
  }
?>