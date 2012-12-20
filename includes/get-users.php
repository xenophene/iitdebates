<?php
/* Simply query back all the users of the database */
include 'config.php';
$rows = array();

$query = "SELECT `uid`, `name` FROM `users`";
if ($result = $conn->query($query)) {
  while ($row = $result->fetch_assoc()) {
    $t = array();
    $t['name'] = $row['name'];
    $t['uid'] = $row['uid'];
    $t['ltype'] = 'u';
    array_push($rows, 
              array(
                'name'   => $row['name'],
                'uid'    => $row['uid'],
                'ltype'  => 'u'
              )
    );
  }
}
$query = "SELECT `debid`, `topic` FROM `debates`";
if ($result = $conn->query($query)) {
  while ($row = $result->fetch_assoc()) {
    array_push($rows, 
                array(
                  'name'   => $row['topic'],
                  'uid'    => $row['debid'],
                  'ltype'  => 'd'
                )
      );
  }
}
echo json_encode($rows);
?>
