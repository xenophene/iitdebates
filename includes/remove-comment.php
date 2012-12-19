<?php
/* remove the comment requested by the user */
include 'config.php';

$comid = $_POST['comid'];
$query = "DELETE FROM `comments` WHERE `comid`='$comid'";
$result = $conn->query($query);

?>
