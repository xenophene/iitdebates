<?php
  include 'includes/config.php';
  include 'includes/aux_functions.php';
  $myfbid = $fb->getUser();
  
  $signed_in = false;
  if ($myfbid) {
    try {
      $profile = $fb->api('/me', 'GET');
      $signed_in = true;
    } catch (FacebookApiException $e) {}
  } else {}
  
  if (!isset($_GET['debid'])) header('Location: home.php');
  $debid = $_GET['debid'];
  
  $debate = getDebate($conn, $debid);
  $userid = $debate['creator'];
  
  $ps = array_filter(explode(',', listSanityCheck($debate['participants'])));
  $is_participant = array_search($myfbid, $ps);
  
  $fs = array_filter(explode(',', listSanityCheck($debate['followers'])));
  $debatetopic = $debate['topic'];
  $debatedesc = $debate['description'];
  $debatethemes = $debate['themes'];
  $timelimit = $debate['timelimit'];
  $startdate = $debate['startdate'];
  $debscore = $debate['debscore'];
  $privacy = $debate['privacy'];
  $token = $debate['token'];
  
  $next_url = 'debate.php?debid=' . $debid;
  
  $time = ($debate['time']) ? timeStamp($debate['time']) : '';
  
  $creator = getDebateCreator($conn, $userid);
  
  if ($signed_in) $myname = $profile['name'];
  else $myname = "Anonymous";
  $creatorname = $creator['name'];
  
  $comments = commentsArray($conn, $debid);
  updateToken($conn, $myfbid, $debid, $token);
?>

<!DOCTYPE html>
<html>
  <head>
    <?php
    // user is my facebook ID
    // userid is the facebook ID of the creator of this debate
    // debid is the debate's ID
    echo "
      <script>
      var user = '$myfbid'; //my fb id
      var userid = '$userid'; //the creator's fb user id
      var debid = '$debid';
      var myname = '$myname';
      var username = '$creatorname';
      var participantIds = ". json_encode($ps) .";
      var followerIds = ". json_encode($fs) .";
      </script>
    ";
    ?>
    <link rel="stylesheet" href="includes/assets/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="includes/assets/css/jquery-ui.css"/>
    <link rel="stylesheet" href="includes/assets/css/style.css"/>
    <link rel="icon" href="includes/assets/ico/favicon.ico"/>
    <title><?php echo $debatetopic;?> | IIT Debates</title>
  </head>
  <body>
    <div id="header">
      <span class="logo"><a href="home.php">IIT Debates</a></span>
      <span class="fb-ju-ab">
        <ul>
          <li><a href="fb-ju-ab.php#join-us" id="ju">Join Us</a></li>
          <li><a href="fb-ju-ab.php#about" id="ab">About</a></li>
        </ul>
      </span>
      <span class="options">
        <ul>
        <?php if ($signed_in):?>
          <li class="search-form">
          <input class="navbar-search" type="text" id="friend-search" data-provide="typeahead" placeholder="Search" autocomplete="off">
          <div class="icon-search icon-black"></div>
          </li>
          <li class="log-out-link"><a href="home.php">Home</a></li>
          <li class="log-out-link"><a href="logout.php">Log Out</a></li>
        <?php endif;?>
        </ul>
      </span>
    </div>
    <div id="profile">
      <div id="debate-details">
        <!-- Topic editable -->
        <div class="topic" name="<?php echo $debid;?>"> <?php echo $debatetopic;?> </div>
        <div class="desc"> 
          <!-- Making the description of debate editable(Currently editable for all.will have to change that.) -->
          <p id="desc-data" name="<?php echo $debid;?>"> <?php echo $debatedesc; ?> </p>
        </div>
        <div class="deb-themes">
          <?php
            $themes = explode(',', $debatethemes);
            foreach ($themes as $theme)
              if ($theme) echo '<span class="theme" title="Debate Themes">'.$theme.'</span>';
          ?>
        </div>
      </div>
      <table class="d-details">
        <tbody>
          <tr><td><span class="interest">Created by:</span></td>
          <td>
          <?php
            echo '<a href="home.php?uid='.$creator['uid'].'">'.$creator['name'].'</a>';
          ?>
          </td>
          </tr>
          <tr><td><span class="interest">Debate Points:</span></td><td><?php echo $debscore;?></td></tr>
          <tr><td><span class="interest"># Followers:</span></td><td><?php echo sizeof($fs);?></td></tr>
          <tr><td><span class="interest">Time Left:</span></td>
          <td>
          <?php 
            $days = (strtotime(date("Y-m-d")) - strtotime($startdate)) / (60 * 60 * 24);
            if ($timelimit - $days > 0) echo ($timelimit - $days).' days';
            else echo 'Closed';
          ?>
          </td>
          </tr>
          <tr><td><span class="interest">Created:</span></td><td><?php echo $time;?></td></tr>
        </tbody>
      </table>
      <div class="engage">
        <?php if (!$is_participant and $signed_in): 
          if (!in_array($myfbid, $fs)) {
            $fclass = 'btn btn-primary';
            $ftext = 'Follow';
          } else {
            $fclass = 'btn btn-danger';
            $ftext = 'Unfollow';
          }
        ?>
        <a title="Follow Debate" id="follow-debate" class="btn <?php echo $fclass;?> engage-btn"><?php echo $ftext;?></a><br/>
        <?php elseif (!$signed_in) : ?>
        <a href="<?php echo $facebook->getLoginUrl($params);?>" class="btn btn-primary engage-btn">Sign in</a><br>
        <?php else: ?>
        <a title="Invite friends to this debate" id="invite-to-debate" class="btn btn-primary engage-btn">Invite Friends</a><br/>
        <?php endif; ?>
        <a title="See all participants" id="view-participants" class="btn engage-btn">Participants</a><br/>
        <a title="See all followers" id="view-followers" class="btn engage-btn">Followers</a>
      </div>
    </div>
    <div id="content">
      <div id="yes" class="leftcol">
        <div id="comments">
          <?php
            /* echo the comments from the comments array for the for side */
            foreach ($comments as $comment) {
              $authorUid = $comment['author'];
              $authorName = $comment['name'];
              if ($comment['foragainst']) {
                commentInfo($comment, $authorUid, $authorName);
                voteTally($comment['upvotes'], $comment['downvotes']);
                /* only show the upvote/downvote if comment was NOT posted by me & 
                   I have not already upvoted or downvoted this comment */
                deleteSupportVote($comment, $myfbid);
                echo '</div>';
              }
            }
          ?>
        </div>
      </div>
      <div id="no" class="rightcol">
        <div class="comments">
          <?php
            foreach ($comments as $comment) {
              $authorUid = $comment['author'];
              $authorName = $comment['name'];
              if ($comment['foragainst'] == 0) {
                commentInfo($comment, $authorUid, $authorName);
                voteTally($comment['upvotes'], $comment['downvotes']);
                /* only show the upvote/downvote if comment was NOT posted by me & 
                   I have not already upvoted or downvoted this comment */
                deleteSupportVote($comment, $myfbid);
                echo '</div>';
              }
            }
          ?>
        </div>
      </div>
      <div class="clear"></div>
    </div>
    <div id="overlay" class="modal hide fade">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">x</button>
        <h1></h1>
      </div>
      <div class="modal-body">
      </div>
    </div>
    <script>
      var PUSHER_APP_KEY = '<?php echo(PUSHER_APP_KEY); ?>';
    </script>
    <script src="includes/assets/js/jquery-1.7.2.min.js"></script>
    <script src="includes/assets/js/jquery-ui-min.js"></script>
    <script src="includes/assets/js/jquery.autosize-min.js"></script>
    <script src="includes/assets/js/jquery.easing.1.3.js"></script>
    <script src="includes/assets/js/bootstrap.min.js"></script>
    <script src="includes/assets/js/marked.js"></script>
    <script src="includes/assets/js/common.js"></script>
    <script src="includes/assets/js/pusher.min.js"></script>
    <script src="includes/assets/js/debate-script.js"></script>
    <script src="includes/assets/js/editinplace.js"></script>
    <script src="includes/assets/js/jquery.editinplace.js"></script>
  </body>
</html>