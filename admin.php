<html>
  <head>
    <link rel="stylesheet" href="style.css">
    <title>Chores UI main page</title>
  </head>
  <body>

<?php
include_once ("./config.php");
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($mysqli->connect_errno)
{
  echo "Failed to connect to MySQL: " . $mysqli->connect_error;
  exit();
}
exit

select date, u.realname, act.payrate * act.quantity as pay, quantity from activity act join users u on u.id = act.user_id where date = "2020-11-02 00:00:00" or date = "2020-11-01 00:00:00" order by u.id;
