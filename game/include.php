<? 
$i=10;
while ((!($erg=mysql_pconnect("localhost","confrontation","02confrontation05")))&&($i!=0)){$i--;}
if (!$erg) DIE ("Unable to connect to database.");
mysql_select_db("confrontation");
?>