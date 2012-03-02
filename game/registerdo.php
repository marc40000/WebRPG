<html>
<body bgcolor=black text=white>
Register<br />
<br />

<?
	if ((!isset($_REQUEST['login'])) ||
		(!isset($_REQUEST['password'])) ||
		(!isset($_REQUEST['password2'])) ||
		(!isset($_REQUEST['email'])))
	{
		echo "You need to fill out all required fields!";
	}
	else
	{
		if (($_REQUEST['login']=='') ||
			($_REQUEST['password']=='') ||
			($_REQUEST['password2']=='') ||
			($_REQUEST['email']==''))
		{
			echo "You need to fill out all required fields!";
		}
		else
		{
			if ($_REQUEST['password']!=$_REQUEST['password2'])
			{
				echo "Passwords don't match.";
			}
			else
			{
				include ("db.php");
				
				// check that login is not already taken
				$query = "select count(*) as n from webrpg_users where login='".$_REQUEST['login']."'";
				$result = mysql_query($query);
				$row = mysql_fetch_array($result);
				mysql_free_result($result);
				
				if ($row['n']!=0)
				{
					echo "Login already taken. Please choose another one.";
				}
				else
				{
					// finally insert the new user
					
					if (isset($_REQUEST['homepage']))
					{
						$homepage = $_REQUEST['homepage'];
					}
					else
					{
						$homepage = '';
					}
					
					$query = "insert into webrpg_users (login,password,email,homepage) values (".
						"'".$_REQUEST['login']."',".
						"'".$_REQUEST['password']."',".
						"'".$_REQUEST['email']."',".
						"'".$_REQUEST['homepage']."')";
					mysql_query($query);
					
					echo "Your registration was successfull! Welcome on board!<br /><br/>";
					echo "<a href=\"index.php\">Login</a>";
				}
			}
		}
	}	
?>

</body>
</html>