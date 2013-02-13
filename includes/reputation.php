<?php
  function voteScoreUpdate($comid,$userid,$upvote,$conn){
    /*
    get the author of comment.
    get the details/rank/score of the voter.
    get the rank/score,follower of the debate
    calculate points to be given to author.
    calculate points to be given to the debate.
    */
    $q = "select * from `comments` where `comid`='$comid'";
    $result = $conn->query($q);
    if($result){
      
      $factor =1;
      if($upvote != 1)
        $factor = -1;
      
      $row = $result->fetch_assoc();
      $author = $row['author'];
      //VOTER INFO
      $r = $conn->query("select * from `users` where `fbid`='$userid'");
      $voter_row = $r->fetch_assoc();
      $voter_score = $voter_row['score'];
//      THE LOGIC CAN BE CHANGED LATER
      $author_score_change = $factor*$voter_score/100;
      $r = $conn->query("update `users` set `score`=`score`+'$author_score_change'".
                        "where `fbid`='$author'");
      
      
//    DEBATE INFO
      $debid =  $row['debid'];
//      THE LOGIC CAN BE CHANGED LATER
      $debate_score_change = $voter_score/100;
      $com_score = $row['score'];
      $r = $conn->query("update `debates` set `debscore` = `debscore`+'$debate_score_change'".
                        "where `debid`='$debid'");
    
      
    }
    
    //return  "pranay";
  }
?>