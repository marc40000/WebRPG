<?
	include ("db.php");
?>
<html>
	<title>
		WebRPG
	</title>
<body bgcolor=black text=white>
	<h1>WebRPG</h1>
	<br />
	<h2>Statistics</h2>
	<table width=100%>
		<tr>
			<td valign=top>
	


	<h3>Top 10 Exp</h3>
	<table width=300>
		<tr>
			<td>Name</td>
			<td>Lvl</td>
			<td>Exp</td>
		</tr>
	<?
		$query = "select * from webrpg_characters order by exp desc limit 10";
		$result = mysql_query($query);
		while ($row = mysql_fetch_array($result))
		{
			echo "<tr>";
			echo "<td>";
			echo $row['name'];
			echo "</td>";
			echo "<td align=right>";
			echo $row['level'];
			echo "</td>";
			echo "<td align=right>";
			echo $row['exp'];
			echo "</td>";
			echo "</tr>";
		}
		mysql_free_result($result);
		
	?>
	</table>
	
	
			</td>
			<td valign=top>



	<h3>Top 10 Gold</h3>
	<table width=300>
		<tr>
			<td>Name</td>
			<td>Lvl</td>
			<td>Gold</td>
		</tr>
	<?
		$query = "select * from webrpg_characters order by gold desc limit 10";
		$result = mysql_query($query);
		while ($row = mysql_fetch_array($result))
		{
			echo "<tr>";
			echo "<td>";
			echo $row['name'];
			echo "</td>";
			echo "<td align=right>";
			echo $row['level'];
			echo "</td>";
			echo "<td align=right>";
			echo $row['gold'];
			echo "</td>";
			echo "</tr>";
		}
		mysql_free_result($result);
		
	?>
	</table>


			</td>
			<td valign=top>



	<h3>Top 10 AP</h3>
	<table width=300>
		<tr>
			<td>Name</td>
			<td>Lvl</td>
			<td>AP</td>
		</tr>
	<?
		$query = "select * from webrpg_characters order by cap desc limit 10";
		$result = mysql_query($query);
		while ($row = mysql_fetch_array($result))
		{
			echo "<tr>";
			echo "<td>";
			echo $row['name'];
			echo "</td>";
			echo "<td align=right>";
			echo $row['level'];
			echo "</td>";
			echo "<td align=right>";
			echo $row['cap'];
			echo "</td>";
			echo "</tr>";
		}
		mysql_free_result($result);
		
	?>
	</table>


			</td>
		</tr>
		<tr>
			<td>
				<br />
				<br />
			</td>
		</tr>
		<tr>
			<td valign=top>


	<h3>Top 10 Most Items</h3>
	<table width=300>
		<tr>
			<td>Name</td>
			<td>Lvl</td>
			<td>Items</td>
		</tr>
	<?
		$query = "select count(webrpg_inventory.id) as itemn, webrpg_characters.name, webrpg_characters.level from webrpg_itemtypes right join webrpg_inventory on webrpg_itemtypes.id=webrpg_inventory.itemtypeid right join webrpg_characters on webrpg_inventory.characterid=webrpg_characters.id group by characterid order by itemn desc limit 10";
		$result = mysql_query($query);
		while ($row = mysql_fetch_array($result))
		{
			echo "<tr>";
			echo "<td>";
			echo $row['name'];
			echo "</td>";
			echo "<td align=right>";
			echo $row['level'];
			echo "</td>";
			echo "<td align=right>";
			echo $row['itemn'];
			echo "</td>";
			echo "</tr>";
		}
		mysql_free_result($result);
		
	?>
	</table>

			</td>
			<td valign=top>


	<h3>Top 10 Most valuable Equipment</h3>
	<table width=300>
		<tr>
			<td>Name</td>
			<td>Lvl</td>
			<td>Value</td>
		</tr>
	<?
		$query = "select sum(webrpg_itemtypes.dropvalue) as dropvalue, webrpg_characters.name, webrpg_characters.level from webrpg_itemtypes right join webrpg_inventory on webrpg_itemtypes.id=webrpg_inventory.itemtypeid right join webrpg_characters on webrpg_inventory.characterid=webrpg_characters.id where webrpg_itemtypes.equipable<>0 and webrpg_inventory.equipped<>0 group by characterid order by dropvalue desc limit 10";
		$result = mysql_query($query);
		while ($row = mysql_fetch_array($result))
		{
			echo "<tr>";
			echo "<td>";
			echo $row['name'];
			echo "</td>";
			echo "<td align=right>";
			echo $row['level'];
			echo "</td>";
			echo "<td align=right>";
			echo $row['dropvalue'];
			echo "</td>";
			echo "</tr>";
		}
		mysql_free_result($result);
		
	?>
	</table>
	
			</td>
			<td valign=top>



	<h3>Top 10 Carrying most useless Stuff</h3>
	<table width=300>
		<tr>
			<td>Name</td>
			<td>Lvl</td>
			<td>Useless stuff</td>
		</tr>
	<?
		$query = "select count(webrpg_inventory.id) as useless, webrpg_characters.name, webrpg_characters.level from webrpg_itemtypes right join webrpg_inventory on webrpg_itemtypes.id=webrpg_inventory.itemtypeid right join webrpg_characters on webrpg_inventory.characterid=webrpg_characters.id where webrpg_itemtypes.equipable<>0 and webrpg_inventory.equipped=0 group by characterid order by useless desc limit 10";
		$result = mysql_query($query);
		while ($row = mysql_fetch_array($result))
		{
			echo "<tr>";
			echo "<td>";
			echo $row['name'];
			echo "</td>";
			echo "<td align=right>";
			echo $row['level'];
			echo "</td>";
			echo "<td align=right>";
			echo $row['useless'];
			echo "</td>";
			echo "</tr>";
		}
		mysql_free_result($result);
		
	?>
	</table>
	
	
			</td>
		</tr>
	</table>
	
	<br />
	<br />

	<h3>Global Game Statistics</h3>
	<?
		$query = "select count(*) as n from webrpg_users";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);
		mysql_free_result($result);
		echo "Registered users: ".$row['n']."<br/>";
		
		$query = "select count(*) as n from webrpg_characters";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);
		mysql_free_result($result);
		echo "Characters: ".$row['n']."<br/>";

		$query = "select count(*) as n from webrpg_characters where state<>0";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);
		mysql_free_result($result);
		echo "Currently playing: ".$row['n']."<br/>";
	?>
	
	<br />
	<br />
	
	<? 
		if (isset($_REQUEST['ingame']))
		{
			echo "<a href='game.php'>back to game</a>";
		}
		else
		{
			echo "<a href='index.php'>back to main page</a>";
		}
	?>
	
	<br />
	<br />
	
</body>
</html>