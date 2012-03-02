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
	$query = "select * from webrpg_characters where userid=".$_SESSION['user']['id']." and id=".$_REQUEST['id'];
	$result = mysql_query($query);
	$_SESSION['char'] = mysql_fetch_array($result);
	mysql_free_result($result);
	
	$query = "update webrpg_characters set state=1,timelastaction=NOW() where id=".$_SESSION['char']['id'];
	$result = mysql_query($query);

	echo "Character selected successfully.<br /><br />";
	echo "<a href=\"game.php\">continue</a>";

?>
<br />
<br />
<br />

</body>
</html>