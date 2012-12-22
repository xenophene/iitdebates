<?php
  include 'config.php';
  include 'aux_functions.php';

usleep(1000000 * .5);

/*
 * These are the default parameters that get to the server from the in place editor
 *
 * $_POST['update_value']
 * $_POST['element_id']
 * $_POST['original_html']
 *
*/

/*
 * since the in place editor will display whatever the server returns
 * we're just going to echo out what we recieved. In reality, we can 
 * do validation and filtering on this value to determine if it was valid
*/
$new_value = $_POST['update_value'];
//$type 	   = $_POST['element_id'];
$type 	   = $_POST['field_type'];
$old_value = $_POST['original_html'];
$id        = $_POST['id'];

$query = "";
 if($type=='desc')
	$query = "UPDATE `debates` SET `description`='$new_value' WHERE `debid`='$id'";
 if($type=='topic')
	$query = "UPDATE `debates` SET `topic`='$new_value' WHERE `debid`='$id'";
if($type=='comment')
	$query = "UPDATE `comments` SET `value`='$new_value' WHERE `comid`='$id'";

$conn->query($query);
mysql_query($query);
$result = mysql_affected_rows();

if($result)
	echo $new_value;
else
	echo $old_value;

?>	

