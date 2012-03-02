<?
	session_start();
?>
<html>
<body bgcolor=black text=white>
Create a new character<br />
<br />
The fields marked with a * are required fields!<br />
<br />
<form action="charnewdo.php" method="post">
	Name* : <input type=text name="name"><br />
	<input type=submit value="Create this character">
</form>
</body>
</html>