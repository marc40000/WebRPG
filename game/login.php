<?
	include ("db.php");
	
	if (isset($_REQUEST["login"]) && (isset($_REQUEST["password"])))
	{
		$query = "select * from webrpg_users where login='".$_REQUEST["login"]."' and password='".$_REQUEST["password"]."'";
		$result = mysql_query($query);
		if ($row = mysql_fetch_array($result))
		{
			session_start();

			$_SESSION['loggedin']=1;
			$_SESSION['user']=$row;
			
			echo "<html><body bgcolor=black text=white>";
			echo "Login successfull.<br /><br />";
			echo "<a href=\"charselect.php\">continue</a>";
			
		}
		else
		{
			echo "<html><body bgcolor=black text=white>";
			echo "Wrong login/password.";
		}
		mysql_free_result($result);
		
	}
	else
	{
		echo "Invalid request.";
	}
?>
</body>
</html>