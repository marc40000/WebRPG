<?

	include("db.php");
	
	session_start();
	if (!isset($_SESSION['loggedin']))
	{
		Die("Session timeout. Please login again.</body></html>");
	}

?>
<html>
<head>
</head>
<body bgcolor=black text=white>

<?
	echo "Please select the character you wish to play:<br /><br />";
	
	$query = "select * from webrpg_characters where userid=".$_SESSION['user']['id'];
	$result = mysql_query($query);
	while ($row = mysql_fetch_array($result))
	{
		echo "<a href=\"charselectdo.php?id=".$row['id']."\">".$row['name']."</a><br />";
	}
	mysql_free_result($result);
	
	echo "<br /><a href=\"charnew.php\">create a new character</a><br />";
	
	echo "<br /><a href='index.php'>back to main page</a>";
	
?>
<br />
<br />
<br />

</body>
</html>