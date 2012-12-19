<?php
  /* debate-create gets the create debate request from home.php and creates
     the database entries. it then redirects with the debid to debate.php */
  include 'config.php';
  include 'aux_functions.php';
  
  $user = $fb->getUser();
  if (!$user) header('Location: ../index.php');
  try {
    $profile = $fb->api('/me');
    $name = $profile['name'];
  } catch (FacebookApiException $e) {
    header('Location: ../index.php');
  }
  
  if (!(isset($_POST['debate-topic']) and isset($_POST['debate-desc']) and
        isset($_POST['debate-theme']) and isset($_POST['participant-ids']) and
        isset($_POST['participants']) and isset($_POST['time-limit']) and
        isset($_POST['privacy']) and isset($_POST['post-to-fb-input'])))
    header('Location: ../index.php');
  /* send the user to login page if he is not correctly logged in */
  
  $debatetopic = sanityCheck($_POST['debate-topic']);
  $debatedesc = sanityCheck($_POST['debate-desc']);
  $debatetheme = sanityCheck($_POST['debate-theme']);
  
  // there should be a more sane sanity check here !
  $participants = sanityCheck($_POST['participant-ids']);
  $participants = implode(',', array_unique(explode(',', $participants)));
  $participants .= ',' . $user;
  
  /* for each of these participants, if there are some not in the db, we add them */
  $participant_names = sanityCheck($_POST['participants']);
  $participant_names = implode(',', array_map('trim', 
                              array_unique(explode(',', $participant_names))));
  $participant_names .= ',' . $name;

  addUsers($conn, $participants, $participant_names);
  
  $timelimit = sanityCheck($_POST['time-limit']);
  $privacy = sanityCheck($_POST['privacy']);
  
  /**
    * DEBATE SCORING.
    * P(d) = sum of points of those followers who have commented + voted
    * this is updated whenever, a new user comments or upvotes for the first time
    * P(c) = sum of points of a comment. sum of scores of people who upvoted
    * R(u,d) = reward of a user u from debate d. computed everytime a participant
    * visits a debate, he/she is shown the points he stands to gain.
    */
  $debscore = 0;
  
  $is_participant = true;
  $post_to_fb = $_POST['post-to-fb-input'];
  
  /* check if such an entry exists, else make an entry to the db */
  
  $query = "SELECT * FROM `debates` WHERE `topic`='$debatetopic' AND `creator`='$user'";
  if ($result = $conn->query($query)) {
    if (!$result->num_rows) {
      $t = time();
      $query = "INSERT INTO `debates` (`topic`, `description`, `timelimit`, `themes`,".
               "`participants`, `followers`, `creator`, `startdate`, `privacy`,`time`) VALUES ".
               "('$debatetopic', '$debatedesc', '$timelimit', '$debatetheme', ".
               "'', '$participants', '$user', '".date('Y,m,d')."', '$privacy','$t')";
      $conn->query($query);
      $debid = $conn->insert_id;
      /*Post message to wall of all the participants*/
      if ($post_to_fb == '1')
        postUsers($participants, $participant_names, $debatetopic, 
                  $debatedesc, DOMAIN . 'debate.php?debid='.$debid);
      /*Updating the activity in the mother table of updates*/
      $type = 0;
      if ($privacy == '1') $type = 1;
      
      if ($result = $conn->query("SELECT `uid` FROM `users` WHERE `fbid`='$user'")) {
        if ($row = $result->fetch_assoc()) {
          $uid = $row['uid'];
          updateActivity($conn, $uid, $type, $debid, $name, $debatetopic);
          header('Location: ../debate.php?debid=' . $debid);
        }
      }
    }
    
    $row = $result->fetch_assoc();
    $debid = $row['debid'];
    header('Location: ../debate.php?debid=' . $debid);
  }
  
  header('Location: ../index.php');
?>
