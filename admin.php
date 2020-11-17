<html>
  <head>
    <link rel="stylesheet" href="style.css">
    <title>Chores UI parents page</title>
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
  echo "<form method =\"POST\" id=\"namebutton\" action=\"./\"><input class=\"namebutton\" type=\"submit\" value=\"View today's list\"/>
  <input name=\"action\" type=\"hidden\" id=\"i\" value=\"allchores\"/></form>";
  $statement = $mysqli->prepare("select date, u.realname, sum(act.payrate * act.quantity) as pay, sum(quantity) from activity act join users u on u.id = act.user_id where (date = curdate() -1 or date = curdate() -2 or date = curdate() -3) group by u.realname, date order by date;");
  $statement->execute();
  $statement->store_result();
  $statement->bind_result($date, $name, $pay, $count);
  if ($statement->num_rows > 0)
  {
    while ($statement->fetch())
    {
      echo "<p>".$name." earned ".$pay." on " .$date. " for ".$count." chores</p>\n";
    }
  }
