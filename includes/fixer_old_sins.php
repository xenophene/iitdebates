<?php
 	include "config.php"; 

function copyDebateFollowers(){
  global $conn;
		$query = "SELECT * from `debates`";
		if($result = $conn->query($query)){
			while($row = $result->fetch_assoc()){
				$debid = $row['debid'];
				$token = $row['token'];
				$followers = explode(',', $row['followers']);
				for($i = 0; $i <sizeof($followers);$i++){
								$q = "insert into `debate_followers` (`debid`,`follower`,`user_token`)".
								"VALUES('$debid','$followers[$i]','$token')";
								$conn->query($q);
				}
			}
		}
  return "Copied followers of debate";
	}

function copyDebateParticipants(){
  global $conn;
		$query = "SELECT * from `debates`";
		if($result = $conn->query($query)){
			while($row = $result->fetch_assoc()){
				$debid = $row['debid'];
				$token = $row['token'];
				$participants = explode(',', $row['participants']);
				
				for($i = 0; $i <sizeof($participants);$i++){
								$q = "insert into `debate_participants` (`debid`,`participant`,`user_token`)".
								"VALUES('$debid','$participants[$i]','$token')";
								$conn->query($q);
				}
			}
		}
  return "Copied Participants of debate Successfully. !";
	}
	
function copyDebatethemes(){
  global $conn;
		$query = "SELECT * from `debates`";
		if($result = $conn->query($query)){
			while($row = $result->fetch_assoc()){
				$debid = $row['debid'];
				$themes = explode(',', $row['themes']);
				for($i = 0; $i <sizeof($themes);$i++){
								$q = "insert into `debate_themes` (`debid`,`theme`)".
								"VALUES('$debid','$themes[$i]')";
								$conn->query($q);
				}
			}
		}
  return "Copied themes of debate Successfully. !";
	}
	
function copyUserInterest(){
  global $conn;
		$query = "SELECT * from `users`";
		if($result = $conn->query($query)){
			while($row = $result->fetch_assoc()){
				$uid = $row['uid'];
				$interest = explode(',', $row['interests']);
				for($i = 0; $i < sizeof($interest);$i++){
								$q = "insert into `user_interests` (`uid`,`interest`)".
								"VALUES('$uid','$interest[$i]')";
								$conn->query($q);
				}
			}
		}
  return "Copied interests of Users Successfully. !";
	}	
	
	//echo (copyDebateFollowers());
	//echo (copyDebateParticipants());
	//echo copyDebatethemes();
	//echo copyUserInterest();
?>