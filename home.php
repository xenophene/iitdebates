<?php
  require_once 'includes/config.php';
  include 'includes/aux_functions.php';
  
  $myfbid = $fb->getUser();
  $up = navigateInto($conn, $fb, $_GET);
  $followers = getConnections($conn, 'uid', $up['uid'], 'follower');
  $followees = getConnections($conn, 'follower', $up['fbid'], 'uid');
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $up['name'];?> | IIT Debates</title>
    <link rel="stylesheet" href="includes/assets/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="includes/assets/css/jquery-ui.css"/>
    <link rel="stylesheet" href="includes/assets/css/style.css"/>
    <link rel="icon" href="includes/assets/ico/favicon.ico"/>
  </head>
  <body>
    <!--Site Header-->
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
        <?php if ($up['signed_in']): ?>
          <li class="search-form">
            <input class="navbar-search" type="text" id="friend-search" data-provide="typeahead" placeholder="Search" autocomplete="off">
            <div class="icon-search icon-black"></div>
          </li>
          <li class="log-out-link"><a href="home.php">Home</a></li>
          <li class="log-out-link" id="log-out-btn"><a href="logout.php">Log Out</a></li>
        <?php else: ?>
          <li class="log-out-link">
            <a href="<?php echo $fb->getLoginUrl($params);?>">Sign In</a>
          </li>
        <?php endif; ?>
        </ul>
      </span>
    </div>
    <!--The user profile is displayed here. Pic, Score, ...-->
    <div id="profile">
      <a href="<?php echo '//facebook.com/profile.php?id='. $up['fbid']; ?>" target="_blank">
      <img class="pic" src="<?php echo '//graph.facebook.com/'. $up['fbid'] .'/picture?type=normal';?>"/></a>
      <table class="name-table">
        <thead>
          <tr><td class="name"><?php echo $up['name'];?></td></tr>
        </thead>
      </table>
      <table class="details">
        <tbody>
          <tr>
            <td class="contain-interest"><span id="interested-in" class="interest">interested in:</span></td>
            <?php $editable = ($up['signed_in'] and $up['me']) ? 'editable' : '';?>
            <td name="<?php echo $up['fbid'];?>" class="interest-elements <?php echo $editable;?>"><?php echo $up['interests']; ?></td>
          </tr>
          <tr>
            <td><span id="debating-points" class="interest">debating points:</span></td>
            <td class="debate-score"><?php echo $up['score']; ?></td>
          </tr>
        </tbody>
      </table>
      <div class="engage">
      <?php if ($up['signed_in'] and $up['me']): ?>
        <a title="Start a New debate" id="start" class="btn btn-primary usr-engage-btn">Start a new debate</a><br/>
        <a title="View my followers" id="my-followers" class="btn usr-engage-btn">My Followers</a><br/>
        <a title="View my followees" id="my-followees" class="btn usr-engage-btn">My Followees</a>
      <?php elseif ($up['signed_in']):
        if (!in_array($myfbid, $followers)) {
          $fclass = 'btn btn-primary';
          $ftext = 'Follow';
        } else {
          $fclass = 'btn btn-danger';
          $ftext = 'Unfollow';
        }
      ?>
        <a title="Follow this user's activity" id="follow" class="<?php echo $fclass;?>"><?php echo $ftext;?></a><br/>
        <a title="Challenge to a debate" id="challenge" class="btn usr-engage-btn2">Challenge</a>
      <?php endif; ?>
      </div>
    </div>
    <div id="content">
      <div id="my-debates" class="leftcol">
        <span class="home-heading">
        <?php
          if ($up['me']) echo 'My Debates';
          else echo $up['name'] ."'s Debates";
        ?>
        </span>
        <table class="table debate-table">
        <?php
          $du = debateUpdates($conn, $up['fbid']);
          $result = getDebatesFollowed($conn, $up['fbid']);
          if ($result->num_rows) {
            echo
              '<thead><tr>'.
              '<td>Debate Name</td>'.
              '<td>Points</td>'.
              '<td>Time Left</td>'.
              '</tr></thead>'.
              '<tbody>';
          } else {
            echo 
              '<thead></thead><tbody>'.
              '<tr id="nill">'.
              '<td>No ongoing debates right now</td>'.
              '</tr>';
          }
          while ($row = $result->fetch_assoc()) {
            $debid = $row['debid'];
            $color = '';
            if (isset($du[$debid])) $color = '#ffff90';
            echo '<tr bgcolor='.$color.'>'.
                 '<td class="dname" id="'.$debid.'">'.
                 '<a href="debate.php?debid='.$debid.'">'.$row['topic'].'</a></td>'.
                 '<td class="points">'.$row['debscore'].' pts.</td><td>';
            $days = (strtotime(date("Y-m-d")) - strtotime($row['startdate'])) / (60 * 60 * 24);
            $daylimit = $row['timelimit'];
            if ($daylimit - $days > 0) echo ($daylimit - $days).' days';
            else echo 'Closed';
            echo '</td>';
            
            if ($up['me']) {
                echo '<td style="padding:8px 4px 8px 0;">'.
                     '<a href="#" class="close delete-debate">&times;</a>'.
                     '</td>';
              }
            echo '</tr>';
          }
        ?>
          </tbody>
        </table>
      </div>
      <div id="my-updates" class="rightcol">
        <span class="home-heading">Updates</span>
        <?php echo getActivities($conn);?>
      </div>
      <div class="clear"></div>
    </div>
    <!-- Generic Overlay box for which we set the code and call the modal -->
    <div id="overlay" class="modal hide fade">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">x</button>
        <h1></h1>
      </div>
      <div class="modal-body">
      </div>
    </div>
    <div id="start-debate-form" class="modal hide fade well">
      <div class="modal-header">
        <button type="button" id="cancel-debate" class="close" data-dismiss="modal">x</button>
        <h1>Start a new debate</h1>
      </div>
      <div class="modal-body">
        <form>
          <input type="text" title="Debate's Topic" name="debate-topic" id="debate-topic" class="input-xxlarge" placeholder="Debate Topic" autocomplete="off"/>
          <textarea class="input-xxlarge" title="Debate's Description" name="debate-desc" id="debate-desc" placeholder="Debate Description" rows="4" autocomplete="off"></textarea>
          <input type="text" name="debate-theme" title="Debate's Themes" id="debate-theme" class="input-xxlarge" placeholder="Debate Themes" autocomplete="off" spellcheck="false"/>
          <input type="text" name="participants" title="Debate's Participants" id="participants" class="input-xxlarge ui-autocomplete-input" placeholder="Challenge Friends" autocomplete="off" spellcheck="false"/>
          <input type="hidden" name="participant-ids" title="Debate's Participants" id="participant-ids"/>
          <div id="radio" title="Enter the debate time limit">
	          <input type="radio" id="time-limit-1" name="time-limit" value="10"  checked="checked" /><label for="time-limit-1">10 days</label>
	          <input type="radio" id="time-limit-2" name="time-limit" value="20" /><label for="time-limit-2">20 days</label>
	          <input type="radio" id="time-limit-3" name="time-limit" value="30" /><label for="time-limit-3">30 days</label>
	          <input type="radio" id="time-limit-4" name="time-limit" value="40" /><label for="time-limit-4">40 days</label>
          </div>
          <div id="radio2" title="Set the debate privacy">
            <input type="radio" id="privacy-1" name="privacy" value="0"  checked="checked" /><label for="privacy-1">Public Debate</label>
	    <input type="radio" id="privacy-2" name="privacy" value="1" /><label for="privacy-2">Private Debate</label>
          </div>
          <!--
          NEEDS TO BE INTEGRATED WITH FB STORIES, BEHIND THE SCENES!
          <a class="btn btn-inverse active" title="Post to Facebook" id="post-to-fb" data-toggle="button">This debate will be posted to Facebook</a>
          -->
          <input type="hidden" name="post-to-fb-input" id="post-to-fb-input" value="1"/>
          <button type="submit" class="btn btn-primary" id="start-debate">Start</button>
          <img src="includes/assets/img/loading3.gif" alt="Loading" class="hide" id="start-loading" title="Loading"/>
        </form>
      </div>
    </div>
    
    <?php
      echo "<script>
        var myfbid = '$myfbid';
        var ufbid = '". $up['fbid'] ."';
        var uname = '". $up['name'] ."';
        var uuid = '". $up['uid'] ."';
        var followers = ". json_encode($followers) ."
        var followees = ". json_encode($followees) ."
            </script>
      ";
    ?>
    <script src="includes/assets/js/jquery-1.7.2.min.js"></script>
    <script src="includes/assets/js/bootstrap.min.js"></script>
    <script src="includes/assets/js/jquery-ui-min.js"></script>
    <script src="includes/assets/js/jquery.editinplace.js"></script>
    <script src="includes/assets/js/common.js"></script>
    <script src="includes/assets/js/script.js"></script>
  </body>
</html>