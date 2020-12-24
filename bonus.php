function renderbonus($mysqli)
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
