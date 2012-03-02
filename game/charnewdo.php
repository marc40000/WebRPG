<?
	session_start();
?>

<html>
<body bgcolor=black text=white>
Create a new character<br />
<br />

<?
	if (!isset($_REQUEST['name']))
	{
		echo "You need to fill out all required fields!";
	}
	else
	{
		if ($_REQUEST['name']=='')
		{
			echo "You need to fill out all required fields!";
		}
		else
		{
			include ("db.php");
				
			// check that name is not already taken
			$query = "select count(*) as n from webrpg_characters where name='".$_REQUEST['name']."'";
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);
			mysql_free_result($result);
				
			if ($row['n']!=0)
			{
				echo "Name already taken. Please choose another one.";
			}
			else
			{
				// finally insert the new char
					
				$query = "insert into webrpg_characters (name,regionid,x,y,spawnregionid,spawnx,spawny,hp,hpmax,mana,ap,manamax,exp,level,img,state,userid,gold) values (".
					"'".$_REQUEST['name']."',".
					"1,22,20,3,16,5,100,100,100,5,100,0,0,'gameimgs/magician.png',0,".$_SESSION['user']['id'].",0)";
				mysql_query($query);
				
				echo "Your character is created!<br /><br/>";
				echo "<a href=\"charselect.php\">Character selection</a>";
			}
		}
	}	
?>

</body>
</html>