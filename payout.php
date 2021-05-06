<html>
  <head>
    <link rel="stylesheet" href="style.css">
    <title>Chores UI parents page</title>
  </head>
  <body>
<?php
echo "<form method =\"POST\" id=\"namebutton\" action=\"./\"><input class=\"namebutton\" type=\"submit\" value=\"View today's list\"/>
<input name=\"action\" type=\"hidden\" id=\"i\" value=\"allchores\"/></form>";
include_once ("./config.php");
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($mysqli->connect_errno)
{
  echo "Failed to connect to MySQL: " . $mysqli->connect_error;
  exit();
}
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && time < $_REQUEST['timestamp'] + 60){
    echo "Processing payout<br>";
    $statement = $mysqli->prepare("UPDATE activity set paid = 1 where id = ?");  
    foreach ($_REQUEST['id'] as $aid){    
      echo "Paying ".$aid."<br>\n";
      $statement->bind_param('i', $aid);
      $statement->execute();
    }
  }
  echo "<br>";
  $statement = $mysqli->prepare("select date(date), u.realname, act.payrate * act.quantity as pay, act.id, ch.name from activity act join users u on u.id = act.user_id join assignments asg on act.assignment_id = asg.id join chores ch on asg.chore_id = ch.id where date >= DATE_SUB(CURDATE(),INTERVAL 10 day) and date != curdate() and paid != 1 order by u.realname,  date;");
  $statement->execute();
  $statement->store_result();
  $statement->bind_result($date, $name, $pay, $id, $cname);
  $maxid = 0;
  if ($statement->num_rows > 0)
  {
    while ($statement->fetch())
    {
      $rows[$id]['date'] = $date;
      $rows[$id]['name'] = $name;
      $rows[$id]['pay'] = $pay;
      $rows[$id]['id'] = $id;
      $rows[$id]['name'] = $cname;
      $pays[$name] = $pays[$name] + $pay;
      echo $name." earned ". $pay ." on ".$date." for ".$cname."<br>\n";
    }
  }
  //Print the payout list
  foreach ( $pays as $name=>$pay ) {
    echo $name . " earned $". money_format('%.2n',$pay). " since last payout.<br>\n";
  }
  //Build the form for payout
  echo "<form method =\"POST\" id=\"namebutton\" action=\"./payout.php\">\n";
  foreach ( $rows as $row ) {
    echo "<input type=\"hidden\" id=\"id\" name=\"id[]\" value=\"".$row['id']."\"/>\n";
  }
  echo "<input class=\"namebutton\" type=\"submit\" value=\"Pay all\"/><input id=\"timestamp\" name=\"timestamp\" type=\"hidden\" value=\"".time()."\"/>\n</form>";
?>
</body>
</html>
