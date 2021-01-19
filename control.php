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
if (isset($_REQUEST['action']) && $_REQUEST['action'] == "manageusers")
{
  renderuser($mysqli);
}
elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == "managechores")
{
  renderallchores($mysqli);
}
elseif (!isset($_REQUEST['action']))
{
  renderhome($mysqli);
}
$mysqli->close(); ?>
 </body>
</html>
<?php
function renderhome($mysqli)
{
?>
    <div class="grid-container">
    <div class="Header"><?php echo "<h2>" . welcome() . " and welcome to Chorinator 9000</h2>"; ?></div>
    <div class="Left"><?php
  echo "<form method =\"POST\" id=\"namebutton\" action=\"./\"><input class=\"namebutton\" type=\"submit\" value=\"View today's list\"/>
  <input name=\"action\" type=\"hidden\" id=\"i\" value=\"allchores\"/></form>";
  $statement = $mysqli->prepare("select realname,id from users where type != 1");
  $statement->execute();
  $statement->store_result();
  $statement->bind_result($name, $id);
  if ($statement->num_rows > 0)
  {
    while ($statement->fetch())
    {
      echo "<form method =\"POST\" id=\"namebutton\" action=\"./\"><input class=\"namebutton\" type=\"submit\" value=\"" . $name . "\"/>
              <input name=\"action\" type=\"hidden\" id=\"i\" value=\"chorelist\"/>
              <input name=\"userid\" type=\"hidden\" id=\"i\" value=\"" . $id . "\"/>
              </form><p>\n";
    }
  }
?></div><div class="Right">
    <div class="report-container" id="weather-report">
    </div>
</div>
</div>
<script type="text/javascript">
function getHTTPObject() {
  var xmlhttp;
  /*@cc_on
  @if (@_jscript_version >= 5)
    try {
      xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
      try {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (E) {
        xmlhttp = false;
      }
    }
  @else
    xmlhttp = false;
  @end @*/
  if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
    try {
      xmlhttp = new XMLHttpRequest();
    } catch (e) {
      xmlhttp = false;
    }
  }
  return xmlhttp;
};
 
function fetchData() {
  http = getHTTPObject();
  http.open('get', "./weather.php?xhttp=true", true);
  http.onreadystatechange = function() {
    if (http != null && http.readyState == 4) {
      document.getElementById('weather-report').innerHTML = http.responseText;
    }
  };
  http.send(null);
};
 
function initPage() {
  http = null;
  fetchData();
  pageTimerHandle = setInterval('fetchData()', 15000);
};
 
window.onload = initPage;
</script>
<?php
}

function renderuser($mysqli)
{
  $statement = $mysqli->prepare("select u.realname from users u where u.id = ?");
  $statement->bind_param('i', $_REQUEST['userid']);
  if ($statement->execute())
  {
    $statement->store_result();
    $statement->bind_result($name);
    $statement->fetch();
  }
?><div class="Header"><?php echo "<h2>" . welcome() . " " . $name . "</h2>"; ?></div><?php
  echo "<form method =\"POST\" id=\"namebutton\" action=\"./\"><input class=\"namebutton\" type=\"submit\" value=\"Return Home\"/></form>";
  $statement = $mysqli->prepare("select u.realname, c.name, c.description, a.id, case when 
  (select count(1) from activity act where act.assignment_id = a.id and act.date = date(now()) and act.user_id = a.assigned_user) > 0 then \"completebutton\" else \"incompletebutton\" end
  from assignments a join users u on a.assigned_user = u.id join chores c on a.chore_id = c.id join schedule s on a.schedule_id = s.id where (( to_days(curdate()) - repeat_start_days) % repeat_interval_days = 0) and a.assigned_user = ?");
  #$statement = $mysqli->prepare("select u.realname, c.name, c.description, a.id from assignments a join users u on a.assigned_user = u.id join chores c on a.chore_id = c.id join schedule s on a.schedule_id = s.id where (( UNIX_TIMESTAMP(CURDATE()) - repeat_start) % repeat_interval = 0) and a.assigned_user = ?");
  $statement->bind_param('i', $_REQUEST['userid']);
  if ($statement->execute())
  {
    $statement->store_result();
    $statement->bind_result($name, $chore, $description, $assignment, $buttonstyle);
    if ($statement->num_rows > 0)
    {
      echo "<p>You have the following chores today</p>";
      while ($statement->fetch())
      {
        echo "<form method =\"POST\" id=\"namebutton\" action=\"./\">
              <input class=\"".$buttonstyle."\" type=\"submit\" value=\"" . $chore . "\"/>              
              <input name=\"action\" type=\"hidden\" id=\"i\" value=\"choredetail\"/>
              <input name=\"assignment\" type=\"hidden\" id=\"i\" value=\"" . $assignment . "\"/>
              <input name=\"userid\" type=\"hidden\" id=\"i\" value=\"" . $_REQUEST['userid'] . "\"/>
              </form><p>";

      }
    }
    else
    {
      echo "<p>You don't have any chores right now</p>";
    }
  }
  else
  {
    echo ('Error executing MySQL query: ' . $statement->error);
  }
}

function renderallchores($mysqli)
{
  echo "<form method =\"POST\" id=\"namebutton\" action=\"./\"><input class=\"namebutton\" type=\"submit\" value=\"Return Home\"/></form>";
  #$statement = $mysqli->prepare("select u.realname, u.id, c.name, c.description, a.id, case when 
  #(select count(1) from activity act where act.assignment_id = a.id and act.date = date(now()) and act.user_id = a.assigned_user) > 0 
  #then \"completebutton\" else \"incompletebutton\" end, 
  #(select sum(quantity) from activity act where act.assignment_id = a.id and act.date = date(now()) and act.user_id = a.assigned_user) as quantity
  #from assignments a join users u on a.assigned_user = u.id join chores c on a.chore_id = c.id join schedule s on a.schedule_id = s.id where (( UNIX_TIMESTAMP(CURDATE()) - repeat_start) % repeat_interval = 0) order by a.id");
  $statement = $mysqli->prepare("select u.realname, u.id, c.name, c.description, a.id, 
  case when (select count(1) from activity act where act.assignment_id = a.id and act.date = date(now()) and act.user_id = a.assigned_user) > 0    
  then 'completebutton' else 'incompletebutton'
  end, (select sum(quantity) from activity act where act.assignment_id = a.id and act.date = date(now()) and act.user_id = a.assigned_user) as quantity 
  from assignments a join users u on a.assigned_user = u.id join chores c on a.chore_id = c.id join schedule s on a.schedule_id = s.id 
  where (( to_days(curdate()) - repeat_start_days) % repeat_interval_days = 0) order by a.id");
  #$statement = $mysqli->prepare("select u.realname, u.id, c.name, c.description, a.id from assignments a join users u on a.assigned_user = u.id join chores c on a.chore_id = c.id join schedule s on a.schedule_id = s.id where (( UNIX_TIMESTAMP(CURDATE()) - repeat_start) % repeat_interval = 0) order by a.id");
  #$statement->bind_param('i', $_REQUEST['userid']);
  if ($statement->execute())
  {
    $statement->store_result();
    $statement->bind_result($name, $userid, $chore, $description, $assignment, $buttonstyle, $quantity);
    if ($statement->num_rows > 0)
    {
      echo "<p>Here are all the chores for today</p>";
      while ($statement->fetch())
      {
        $chorecount = "";
        if ( $quantity > 1 ) {          
          $chorecount = "x ".$quantity;
        }
        echo "<form method =\"POST\" id=\"namebutton\" action=\"./\">
              <label for=\"chore\">" . $name . "</label></br>
              <input class=\"".$buttonstyle."\" type=\"submit\" value=\"" . $chore . " " . $chorecount . "\"/>
              <input name=\"action\" type=\"hidden\" id=\"i\" value=\"choredetail\"/>
              <input name=\"assignment\" type=\"hidden\" id=\"i\" value=\"" . $assignment . "\"/>
              <input name=\"userid\" type=\"hidden\" id=\"i\" value=\"" . $userid . "\"/>
              </form><p>";
      }
    }
    else
    {
      echo "<p>You don't have any chores right now</p>";
    }
  }
  else
  {
    echo ('Error executing MySQL query: ' . $statement->error);
  }
}

function renderchore($mysqli)
{
  echo "<form method =\"POST\" id=\"namebutton\" action=\"./\"><input class=\"namebutton\" type=\"submit\" value=\"Return to chore list\"/>
        <input name=\"action\" type=\"hidden\" id=\"i\" value=\"chorelist\"/>
        <input name=\"userid\" type=\"hidden\" id=\"i\" value=\"" . $_REQUEST['userid'] . "\"/>
        </form><p>\n";
  $statement = $mysqli->prepare("select c.name, c.description, c.pay, a.id, a.assigned_user from chores c join assignments a on a.chore_id = c.id join users u on a.assigned_user = u.id where a.assigned_user = ? and a.id = ?");
  $statement->bind_param('ii', $_REQUEST['userid'], $_REQUEST['assignment']);
  if ($statement->execute())
  {
    $statement->store_result();
    $statement->bind_result($chore, $description, $pay, $assignment, $user);
    if ($statement->num_rows > 0)
    {
      $statement->fetch();
      echo "<h2>" . $chore . "</h2>";
      echo "<p>" . $description . "</p>";
      echo "<p>" . $pay . "</p>";
      echo "<form method =\"POST\" id=\"namebutton\" action=\"./\"><input class=\"namebutton\" type=\"submit\" value=\"I finished this chore\"/>
              <input name=\"action\" type=\"hidden\" id=\"i\" value=\"authenticate\"/>
              <input name=\"userid\" type=\"hidden\" id=\"i\" value=\"" . $user . "\"/>
              <input name=\"assignment\" type=\"hidden\" id=\"i\" value=\"" . $assignment . "\"/>
              </form><p>\n";
    }
  }
}

function renderauth($mysqli)
{
  echo "<form method =\"POST\" id=\"namebutton\" action=\"./\"><input class=\"namebutton\" type=\"submit\" value=\"Return to chore list\"/>
        <input name=\"action\" type=\"hidden\" id=\"i\" value=\"chorelist\"/>
        <input name=\"userid\" type=\"hidden\" id=\"i\" value=\"" . $_REQUEST['userid'] . "\"/>
        </form><p>\n";
  echo "<form method =\"POST\" id=\"namebutton\" action=\"./\"><input class=\"namebutton\" type=\"submit\" value=\"Return Home\"/></form><p>\n\n";
  if (isset($_REQUEST['code']))
  {
    $statement = $mysqli->prepare("select u.realname, u.id, u.pin from users u where u.id = ?");
    $statement->bind_param('i', $_REQUEST['approver']);
    if ($statement->execute())
    {
      $statement->store_result();
      $statement->bind_result($approvername, $approver, $code);
      if ($statement->num_rows > 0)
      {
        $statement->fetch();
        if ($code == $_REQUEST['code'])
        {
          echo "Approved by " . $approvername . "<br>";
          $statement = $mysqli->prepare("select a.id, (c.pay * ?), c.pay from assignments a join chores c on c.id = a.chore_id where a.id = ?");
          $statement->bind_param('ii',$_REQUEST['count'],$_REQUEST['assignment']);
          if ($statement->execute())
          {
            $statement->store_result();
            $statement->bind_result($assignment, $pay, $payrate);
            if ($statement->num_rows > 0)
            {
              $statement->fetch();
              echo "Pay of  $" . $pay . " for ".$_REQUEST['count']." at ".$payrate."<br>";
              $statement = $mysqli->prepare("insert into activity (date, timestamp, assignment_id, user_id, payrate, quantity) values (CURDATE(), NOW(), ?, ?, ?, ?)");
			  $statement->bind_param('iidi', $_REQUEST['assignment'], $_REQUEST['userid'], $payrate, $_REQUEST['count']);
			  $statement->execute();
			  $statement->store_result();
			  echo $statement->num_rows;
            }
          }
        }

      }
    }
  }
  else
  {
?>
<form id="keyform" action="./" method="POST" action="./">
<input name="action" type="hidden" id="i" value="choreapprove"/>
<?php echo "<input name=\"userid\" type=\"hidden\" id=\"i\" value=\"" . $_REQUEST['userid'] . "\"/>
            <input name=\"approver\" type=\"hidden\" id=\"i\" value=\"" . $_REQUEST['approver'] . "\"/>
            <input name=\"assignment\" type=\"hidden\" id=\"i\" value=\"" . $_REQUEST['assignment'] . "\"/>" ?>
<table id="keypad" cellpadding="5" cellspacing="3">
  <tr>
      <td onclick="addCode('1');">1</td>
        <td onclick="addCode('2');">2</td>
        <td onclick="addCode('3');">3</td>
    </tr>
    <tr>
      <td onclick="addCode('4');">4</td>
        <td onclick="addCode('5');">5</td>
        <td onclick="addCode('6');">6</td>
    </tr>
    <tr>
      <td onclick="addCode('7');">7</td>
        <td onclick="addCode('8');">8</td>
        <td onclick="addCode('9');">9</td>
    </tr>
    <tr>
      <td onclick="addCode('*');">*</td>
        <td onclick="addCode('0');">0</td>
        <td onclick="addCode('#');">#</td>
    </tr>
</table>
<input type="password" name="code" value="" maxlength="4" class="display" readonly="readonly" /><br>
<?php
    $statement = $mysqli->prepare("select c.name, c.description, c.pay, a.id, a.assigned_user, c.max from chores c join assignments a on a.chore_id = c.id join users u on a.assigned_user = u.id where a.assigned_user = ? and a.id = ?");
    $statement->bind_param('ii', $_REQUEST['userid'], $_REQUEST['assignment']);
    if ($statement->execute())
    {
      $statement->store_result();
      $statement->bind_result($chore, $description, $pay, $assignment, $user, $max);
      if ($statement->num_rows > 0)
      {
        $statement->fetch();
        if ($max > 1)
        {
?>
            <label for="count">Choose how many you did:</label>
            <select name="count" id="count" class="select-css">
            <?php
          for ($cnt = 1;$cnt <= $max;$cnt++)
          {
            if ($cnt == $_REQUEST['count'])
            {
              echo "<option value =\"" . $cnt . "\" selected>" . $cnt . "</option>\n";
            }
            else
            {
              echo "<option value =\"" . $cnt . "\">" . $cnt . "</option>\n";
            }
          }
?></select><br>
            <?php
        } else {
		  echo "<input name=\"count\" type=\"hidden\" id=\"i\" value=\"1\"/>";
	    }	
      }
    }
    $statement = $mysqli->prepare("select u.realname, u.id from  users u where u.id != ? and u.type < 3");
    $statement->bind_param('i', $_REQUEST['userid']);
    if ($statement->execute())
    {
      $statement->store_result();
      $statement->bind_result($approvername, $approverid);
      if ($statement->num_rows > 0)
      {
?>
        <label for="approver">Who is approving the chore?</label>
        <select name="approver" id="approver" class="select-css"><?php
        while ($statement->fetch())
        {
          echo "<option value =\"" . $approverid . "\">" . $approvername . "</option>\n";
        }
?></select><?php
      }
    }
?>
<p id="message">VERIFYING...</p>
</form>
    <script type="text/javascript">
    function addCode(key){
      var code = document.getElementById("keyform").code;
      if(code.value.length < 4){
        code.value = code.value + key;
      }
      if(code.value.length == 4){
        document.getElementById("message").style.display = "block";
        setTimeout(submitForm,1000);  
      }
    }

    function submitForm(){
      document.getElementById("keyform").submit();
    }

    function emptyCode(){
      document.getElementById("keyform").code.value = "";
    }
    </script>

<?php
  }
}
function welcome()
{

  if (date("H") < 12)
  {

    return "Good morning";

  }
  elseif (date("H") > 11 && date("H") < 18)
  {

    return "Good afternoon";

  }
  elseif (date("H") > 17)
  {

    return "Good evening";

  }

}
function renderapprove($mysqli)
{
  echo "Hi";
}
?>
