<?

	include("db.php");
	include ("map.php");


	session_start();
	if (!isset($_SESSION['loggedin']))
	{
		Die("Session timeout. Please login again.</body></html>");
	}


	include "iterate.php";


	$query = "update webrpg_characters set state='1',timelastaction=NOW() where id=".$_SESSION['char']['id'];
	$result = mysql_query($query);

	
	
	// using portals changes the region, so let's do it before we load our constants
	if (isset($_REQUEST['portal']))
	{
		$query = "select * from webrpg_portals where id=".$_REQUEST['portal']." and regionid=".$_SESSION['char']['regionid']." and x=".$_SESSION['char']['x']." and y=".$_SESSION['char']['y'];
		$result = mysql_query($query);
		if ($row = mysql_fetch_array($result))
		{
			mysql_free_result($result);
			
			$query = "update webrpg_characters set regionid=".$row['targetregionid'].", x=".$row['targetx'].", y=".$row['targety']." where id=".$_SESSION['char']['id'];
			mysql_query($query);
			
			$_SESSION['char']['regionid'] = $row['targetregionid'];
			$_SESSION['char']['x'] = $row['targetx'];
			$_SESSION['char']['y'] = $row['targety'];
		}
		else
			mysql_free_result($result);
	}
	
	
	

	// get monstertypes, *** restrict to monstertypes of the current level later on
	$query = "select * from webrpg_monstertypes";
	$result = mysql_query($query);
	$monstertypes = array();
	while ($row = mysql_fetch_array($result))
	{
		$monstertypes[$row['id']] = $row;
	}
	mysql_free_result($result);


	// get itemtypes
	$query = "select * from webrpg_itemtypes";
	$result = mysql_query($query);
	$itemtypes = array();
	while ($row = mysql_fetch_array($result))
	{
		$itemtypes[$row['id']] = $row;
	}
	mysql_free_result($result);
	
	
	// get regions, *** restrict to the levels around the player level later on
	$query = "select * from webrpg_regions";
	$result = mysql_query($query);
	$regions = array();
	while ($row = mysql_fetch_array($result))
	{
		$regions[$row['level']] = $row;
	}
	mysql_free_result($result);
	

	// execute commands that can be done before loading the map
	
	// drop an item from the inventory to the floor
	if (isset($_REQUEST['drop']))
	{
		$query = "select * from webrpg_inventory where id=".$_REQUEST['drop']." and characterid=".$_SESSION['char']['id'];
		$result = mysql_query($query);
		if ($row = mysql_fetch_array($result))
		{
			mysql_free_result($result);
			// item is indeed in the characters inventory
			
			// remove it prior to adding it to the floor. In the worst case, this leads to loosing an item
			// what is better then duplicating it.
			// Note: The item automatically gets unequipped this way as well!
			$query = "delete from webrpg_inventory where id=".$_REQUEST['drop'];
			mysql_query($query);
			
			// add it to the floor
			$query = "insert into webrpg_items (itemtypeid,regionid,x,y,timedropped) values (".$row['itemtypeid'].",".$_SESSION['char']['regionid'].",".$_SESSION['char']['x'].",".$_SESSION['char']['y'].",NOW())";
			mysql_query($query);
		}
		else
		{
			mysql_free_result($result);
		}
	}
	
	
	// pickup an item from the floor to the inventory
	if (isset($_REQUEST['pickup']))
	{
		$query = "select * from webrpg_items where id=".$_REQUEST['pickup']." and regionid=".$_SESSION['char']['regionid']." and x=".$_SESSION['char']['x']." and y=".$_SESSION['char']['y'];
		$result = mysql_query($query);
		if ($row = mysql_fetch_array($result))
		{
			mysql_free_result($result);
			// item exists where player stands
			
			// remove it from the floor ...
			$query = "delete from webrpg_items where id=".$_REQUEST['pickup'];
			mysql_query($query);
			
			// ... and add it to the inventory
			$query = "insert into webrpg_inventory (itemtypeid,characterid,equipped) values (".$row['itemtypeid'].",".$_SESSION['char']['id'].",0)";
			mysql_query($query);
		}
		else
		{
			mysql_free_result($result);
		}
	}	
	
	
	
	
	
	// equip an item, eventually unequip another
	if (isset($_REQUEST['equip']))
	{
		$query = "select * from webrpg_inventory where id=".$_REQUEST['equip']." and characterid=".$_SESSION['char']['id'];
		$result = mysql_query($query);
		if ($row = mysql_fetch_array($result))
		{
			mysql_free_result($result);
			// item is indeed in the characters inventory
			
			// unequip a evtl. equipped item first
			$query = "update webrpg_inventory set equipped=0 where characterid=".$_SESSION['char']['id']." and equipped=".$itemtypes[$row['itemtypeid']]['equipable'];
			mysql_query($query);
			
			// equip the new item
			$query = "update webrpg_inventory set equipped=".$itemtypes[$row['itemtypeid']]['equipable']." where id=".$_REQUEST['equip']." and characterid=".$_SESSION['char']['id'];
			mysql_query($query);
		}
		else
		{
			mysql_free_result($result);
		}
	}


	// unequip an item, eventually unequip another
	if (isset($_REQUEST['unequip']))
	{
		$query = "select * from webrpg_inventory where id=".$_REQUEST['unequip']." and characterid=".$_SESSION['char']['id'];
		$result = mysql_query($query);
		if ($row = mysql_fetch_array($result))
		{
			mysql_free_result($result);
			// item is indeed in the characters inventory
			
			// unequip the item
			$query = "update webrpg_inventory set equipped=0 where id=".$_REQUEST['unequip']." and characterid=".$_SESSION['char']['id'];
			mysql_query($query);
		}
		else
		{
			mysql_free_result($result);
		}
	}


	// use an item in the inventory
	if (isset($_REQUEST['useitem']))
	{
		$query = "select * from webrpg_inventory where id=".$_REQUEST['useitem']." and characterid=".$_SESSION['char']['id'];
		$result = mysql_query($query);
		if ($row = mysql_fetch_array($result))
		{
			mysql_free_result($result);
			// item is indeed in the characters inventory
			
			// delete the item
			$query = "delete from webrpg_inventory where id=".$_REQUEST['useitem']." and characterid=".$_SESSION['char']['id'];
			mysql_query($query);
			
			if ($row['itemtypeid'] == 198)
			{
				// mana potion
				$query = "update webrpg_characters set mana=mana+100 where id=".$_SESSION['char']['id'];
				mysql_query($query);
			}
			else
			if ($row['itemtypeid'] == 199)
			{
				// health potion
				$query = "update webrpg_characters set hp=hp+100 where id=".$_SESSION['char']['id'];
				mysql_query($query);
			}
			else
			if (($row['itemtypeid'] >= 200) && ($row['itemtypeid'] <= 221))
			{
				// food
				$query = "update webrpg_characters set hp=hp+30 where id=".$_SESSION['char']['id'];
				mysql_query($query);
			}			
		}
		else
		{
			mysql_free_result($result);
		}		
	}



	// calculate char stats depending on equipment
	$query = "select * from webrpg_itemtypes right join webrpg_inventory on webrpg_itemtypes.id=webrpg_inventory.itemtypeid where characterid=".$_SESSION['char']['id']." and equipped<>0";
	$result = mysql_query($query);
	$chpmax = $_SESSION['char']['hpmax'];
	$cmanamax = $_SESSION['char']['hpmax'];
	$chpregen = 1;
	$cmanaregen = 1;
	$cap = $_SESSION['char']['ap'];
	$crc = 0;
	while ($row = mysql_fetch_array($result))
	{
		$chpmax += $row['modhp'];
		$cmanamax += $row['modmana'];
		$chpregen += $row['modhpregen'];
		$cmanaregen += $row['modmanaregen'];
		$cap += $row['modap'];
		$crc += $row['modrc'];
	}
	if ($chpmax < 1)
		$chpmax = 1;
	if ($cmanamax < 0)
		$cmanamax = 0;
	mysql_free_result($result);
	// save the values in the db for faster availability in iterate.php
	$query = "update webrpg_characters set ".
		"chpmax=".$chpmax.",".
		"cmanamax=".$cmanamax.",".
		"chpregen=".$chpregen.",".
		"cmanaregen=".$cmanaregen.",".
		"cap=".$cap.",".
		"crc=".$crc.
		" where id=".$_SESSION['char']['id'];
	mysql_query($query);



	$map = new Map();
	$map->Load($_SESSION['char']['regionid']);	





	// reget charater data
	$query = "select * from webrpg_characters where id=".$_SESSION['char']['id'];
	$result = mysql_query($query);
	$_SESSION['char'] = mysql_fetch_array($result);
	mysql_free_result($result);
	
	$reloadmap = false;
	
	// execute commands
	$wantstomove = false;
	$xnew = $_SESSION['char']['x'];
	$ynew = $_SESSION['char']['y'];
	if (isset($_REQUEST['walknorth']))
	{
		$xnew = $_SESSION['char']['x'];
		$ynew = $_SESSION['char']['y'] - 1;
		$wantstomove = true;
	}
	if (isset($_REQUEST['walksouth']))
	{
		$xnew = $_SESSION['char']['x'];
		$ynew = $_SESSION['char']['y'] + 1;
		$wantstomove = true;
	}
	if (isset($_REQUEST['walkwest']))
	{
		$xnew = $_SESSION['char']['x'] - 1;
		$ynew = $_SESSION['char']['y'];
		$wantstomove = true;
	}
	if (isset($_REQUEST['walkeast']))
	{
		$xnew = $_SESSION['char']['x'] + 1;
		$ynew = $_SESSION['char']['y'];
		$wantstomove = true;
	}
	if (isset($_REQUEST['walknw']))
	{
		$xnew = $_SESSION['char']['x'] - 1;
		$ynew = $_SESSION['char']['y'] - 1;
		$wantstomove = true;
	}
	if (isset($_REQUEST['walkne']))
	{
		$xnew = $_SESSION['char']['x'] + 1;
		$ynew = $_SESSION['char']['y'] - 1;
		$wantstomove = true;
	}
	if (isset($_REQUEST['walkse']))
	{
		$xnew = $_SESSION['char']['x'] + 1;
		$ynew = $_SESSION['char']['y'] + 1;
		$wantstomove = true;
	}
	if (isset($_REQUEST['walksw']))
	{
		$xnew = $_SESSION['char']['x'] - 1;
		$ynew = $_SESSION['char']['y'] + 1;
		$wantstomove = true;
	}

	$moved = false;
	if ($wantstomove)
	{
		// check for wall
		if (($map->GetSave($xnew, $ynew) & 1024) == 0)
		{
			// no wall
			
			// check for other chars
			$charattarget = false;
			$chars = $map->GetPly($xnew, $ynew);
			if (!empty($chars))
			{
				foreach ($chars as $charkey => $char_)
				{
					if ($charkey != $_SESSION['char']['id'])
					{
						$charattarget = true;
						break;
					}
				}
			}
			if (!$charattarget)
			{
				// no char
				
				// check for monsters
				$mobattarget = false;
				$mobs = $map->GetMob($xnew, $ynew);
				if (!empty($mobs))
				{
					foreach ($mobs as $mobkey => $mob)
					{
						$mobattarget = true;
						break;
					}
				}
				if (!$mobattarget)
				{
					// no mob
					
					/////////////////////////////////////////////////////////////////////////////
					// execute the movement
					$query = "update webrpg_characters set ".
						"x=".$xnew.",".
						"y=".$ynew.
						" where id=".$_SESSION['char']['id'];
					mysql_query($query);
					$_SESSION['char']['x'] = $xnew;			// important! LoadPlys only loads map of plys, not the players char
					$_SESSION['char']['y'] = $ynew;			// in the session variable
					
					$map->LoadPlys();
					$moved = true;
				}
				else
				{
					/////////////////////////////////////////////////////////////////////////////
					// mob is at the target postion
					// => fight
					
					$fightmessage = "";
					$kill = false;
					
					// first, char attacks the mob
					$damage = mt_rand(0, $_SESSION['char']['cap']);
					$damage -= $monstertypes[$mob['monstertypeid']]['rc'];
					if ($damage > 0)
					{
						$mob['hp'] -= $damage;
						if ($mob['hp'] > 0)
						{
							// update hp according to damage
							$query = "update webrpg_monsters set hp=hp-".$damage." where id=".$mob['id'];
							mysql_query($query);
							
							$fightmessage = "You hit the ".$monstertypes[$mob['monstertypeid']]['name']." with ".$damage." ap.<br />";
						}
						else
						{
							// mob dies
							$query = "update webrpg_monsters set hp=0, state=0, timelastdeath=NOW() where id=".$mob['id'];
							mysql_query($query);
							
							// the current player is the killer, so give him some exp and gold ...
							$dropgold = mt_rand(0, $monstertypes[$mob['monstertypeid']]['dropvaluegold']);
							$query = "update webrpg_characters set exp=exp+".$monstertypes[$mob['monstertypeid']]['expgive'].
							 ", gold=gold+".$dropgold." where id=".$_SESSION['char']['id'];
							mysql_query($query);
							$_SESSION['char']['exp'] += $monstertypes[$mob['monstertypeid']]['expgive'];
							$_SESSION['char']['gold'] += $dropgold;
							
							// ... and maybe drop some stuff
							$dropvaluemodded = mt_rand(0, $monstertypes[$mob['monstertypeid']]['dropvalue']);
							$query = "select id from webrpg_itemtypes where dropvalue<=".$dropvaluemodded." order by rand() limit 1";
							$result = mysql_query($query);
							if ($row = mysql_fetch_array($result))
							{
								mysql_free_result($result);
								
								$query = "insert into webrpg_items (itemtypeid, regionid, x, y, timedropped) values (".
									$row['id'].",".$mob['regionid'].",".$mob['x'].",".$mob['y'].",NOW())";
								mysql_query($query);
								
								// reload changed map items
								$map->LoadItems();
							}
							else
							{
								mysql_free_result($result);
							}
					
							

							$fightmessage = "You hit the ".$monstertypes[$mob['monstertypeid']]['name']." with ".$damage." ap and kill it.<br />";
							$kill = true;		
						}
					}
					else
					{
						$fightmessage = "You missed the ".$monstertypes[$mob['monstertypeid']]['name']."<br />";
					}
					
					if (!$kill)
					{
						// monster is still alive => it fights back
						$damage = mt_rand(0, $monstertypes[$mob['monstertypeid']]['ap']);
						$damage -= $_SESSION['char']['crc'];
						if ($damage > 0)
						{
							$_SESSION['char']['hp'] -= $damage;
							if ($_SESSION['char']['hp'] > 0)
							{
								// player is still alive
								$query = "update webrpg_characters set hp=hp-".$damage." where id=".$_SESSION['char']['id'];
								mysql_query($query);
								
								$fightmessage = $fightmessage."The ".$monstertypes[$mob['monstertypeid']]['name']." hits you with ".$damage." hp.";
							}
							else
							{
								// player is dead, something special has to be done
								$query = "update webrpg_characters set hp=chpmax, regionid=spawnregionid, x=spawnx, y=spawny, exp=exp*0.9 where id=".$_SESSION['char']['id'];
								mysql_query($query);
								$reloadmap = true;

								$fightmessage = $fightmessage."The ".$monstertypes[$mob['monstertypeid']]['name']." hits you with ".$damage." hp. You die.";
							}
						}
						else
						{
							$fightmessage = $fightmessage."The ".$monstertypes[$mob['monstertypeid']]['name']." misses you.";
						}
					}
					
					$map->LoadPlys();
					$map->LoadMobs();
				}
							
			}
			
		}
		else
		{
			// wall
		}
	}
	

	// reget charater data
	$query = "select * from webrpg_characters where id=".$_SESSION['char']['id'];
	$result = mysql_query($query);
	$_SESSION['char'] = mysql_fetch_array($result);
	mysql_free_result($result);

	if ($reloadmap)
	{			
		$map = new Map();
		$map->Load($_SESSION['char']['regionid']);	
	}	
	
	

	if (isset($_REQUEST['msg']))
	{
		$query = "insert into webrpg_chat (characterid,charactername,channel,msg,time) values (".
			$_SESSION['char']['id'].",".
			"'".$_SESSION['char']['name']."',".
			$_SESSION['char']['regionid'].",".
			"'".addslashes(htmlentities($_REQUEST['msg']))."',".
			"NOW()".
			")";
		mysql_query($query);
	}
	
	
	
	// leveling system
	$levels[0]['expneeded'] = 0;
	$levels[1]['expneeded'] = 100;
	for ($i = 2; $i < 100; $i++)
		$levels[$i]['expneeded'] = $levels[$i-1]['expneeded'] * 2;
	$levels[100]['expneeded'] = 1000000000;		// hopefully this will never be reached
	
	if ($_SESSION['char']['exp'] >= $levels[$_SESSION['char']['level'] + 1]['expneeded'])
	{
		// levelup
		$hpmaxadd = mt_rand(3,10);
		$manamaxadd = mt_rand(3, 10);
		
		$query = "update webrpg_characters set level=level+1, hpmax=hpmax+".$hpmaxadd.", manamax=manamax+".$manamaxadd.", hp=chpmax, mana=cmanamax where id=".$_SESSION['char']['id'];
		mysql_query($query);
		
		$_SESSION['char']['level']++;
		$_SESSION['char']['hpmax'] += $hpmaxadd;
		$_SESSION['char']['manamax'] += $manamaxadd;
		$_SESSION['char']['hp'] += $_SESSION['char']['hpmax'];
		$_SESSION['char']['mana'] += $_SESSION['char']['manamax'];
		
		$levelupmessage = "Congratulations! You reached level ".$_SESSION['char']['level'].".";
	}
?>

<html>

<head>
<?
	$gamerefreshnext = 1;
	if (isset($_REQUEST['gamerefresh']))
	{
		if ($_SESSION['gamerefreshcount'] >= 15 * 4)
		{
			// 15 min refreshed without user interaction => stop refreshing
			$gamerefreshnext = 0;
		}
		else
		{
			$_SESSION['gamerefreshcount']++;
		}
	}
	else
	{
		$_SESSION['gamerefreshcount'] = 0;
	}
?>
<script language="javascript">
	var gamerefresh=<? echo $gamerefreshnext ?>;
	function gamere()
	{
		if (gamerefresh==1)
		{
			window.location.href='game.php?gamerefresh';
		}
	}
	function gamerestop()
	{
		gamerefresh = 0;
	}
	setTimeout("gamere()",15000);
</script>

<? /* <!--[if lt IE 7.]>
<script defer type="text/javascript" src="pngfix.js"></script>
<![endif]--> */ ?>

<link rel=stylesheet type="text/css" href="style.css">
</head>

<body bgcolor=black text=white>
<table width=100%><tr><td><h1>WebRPG</h1></td>
	<td align=right>
		<a href="charselect.php">Exit</a><br />
		<a href="stats.php?ingame=1">Stats</a>
	</td>
</tr></table>
<?
	$tilex = 64; //$_SESSION['user']['tilex']
	$tiley = 64; //$_SESSION['user']['tiley']

	$cx = $_SESSION['char']['x'];
	$cy = $_SESSION['char']['y'];
	
	$dx = 5;
	$dy = 5;
	
?>	
<script language="Javascript">
	function lead0(id)
	{
		var t = "" + id;
		while (t.length < 4)
		{
			t = "0" + t;
		}
		return t;
	}
	function N()
	{
		document.write("<img src='gameimgs/nothing.png' width='<? echo $tilex ?>' height='<? echo $tiley ?>'>");
	}
	function E()
	{
		document.write("<br />");
	}
	function T(id)
	{
		document.write("<img src='gameimgs/tiles/" + lead0(id) + ".gif' width='<? echo $tilex ?>' height='<? echo $tiley ?>'>");
	}
	function I(id)
	{
		document.write("<img src='gameimgs/items/" + lead0(id) + ".gif' width='<? echo $tilex ?>' height='<? echo $tiley ?>'>");
	}
	function C(id)
	{
		document.write("<img src='" + id + "' width='<? echo $tilex ?>' height='<? echo $tiley ?>'>");
	}
	function M(id)
	{
		document.write("<img src='gameimgs/mobs/" + lead0(id) + ".gif' width='<? echo $tilex ?>' height='<? echo $tiley ?>'>");
	}
	function K(x,y,text)
	{
		document.write("<div style='position:absolute; top:" + (64 + (y + <? echo $dy ?>) * <? echo $tiley ?>) + "px; left:" + (5 + (x + <? echo $dx ?>) * <? echo $tilex ?>) + "px; z-index:50'>" + text + "</div>");
	}
</script>
<?
	// walls and floor
	echo "<div style='position:absolute; top:64px; left:5px; width:100%; z-index:0; height:".$tilex * ($dx * 2 + 1)."px'>";
	echo "<script language='javascript'>\n";
	for ($y = -$dy; $y <= $dy; $y++)
	{
		for ($x = -$dx; $x <= $dx; $x++)
		{
			$w = $map->GetSave($cx + $x, $cy + $y);
			printf ("T(%u);", ($w & 1023));
		}
		echo "E();";
		echo "\n";
	}
	echo "</script>";
	echo "</div>\n\n";


	// items
	echo "<div style='position:absolute; top:64px; left:5px; width:100%; z-index:1; height:".$tilex * ($dx * 2 + 1)."px'>";
	echo "<script language='javascript'>\n";
	for ($y = -$dy; $y <= $dy; $y++)
	{
		for ($x = -$dx; $x <= $dx; $x++)
		{
			$items = $map->GetItem($cx + $x, $cy + $y);
			if (!empty($items))
			{
				foreach ($items as $itemkey => $item)
				{
					printf ("I(%u);", $item['itemtypeid']);
					//echo "<img src='gameimgs/items/".$itemtypes[$item['itemtypeid']]['img']."' width=".$tilex." height=".$tiley.">";
					break;
				}
			}
			else
			{
				printf ("N();");
				//echo "<img src='gameimgs/nothing.png' width=".$tilex." height=".$tiley.">";
			}
		}
		echo "E();";
		echo "\n";
	}
	echo "</script>";
	echo "</div>\n\n";
	

	// characters
	echo "<div style='position:absolute; top:64px; left:5px; width:100%; z-index:1; height:".$tilex * ($dx * 2 + 1)."px'>";
	echo "<script language='javascript'>\n";
	for ($y = -$dy; $y <= $dy; $y++)
	{
		for ($x = -$dx; $x <= $dx; $x++)
		{
			$chars = $map->GetPly($cx + $x, $cy + $y);
			if (!empty($chars))
			{
				foreach ($chars as $charkey => $char_)
				{
					echo "C('".$char_['img']."');";
					//echo "<img src='".$char['img']."' width=".$tilex." height=".$tiley.">";
					break;
				}
			}
			else
			{
				printf ("N();");
				//echo "<img src='gameimgs/nothing.png' width=".$tilex." height=".$tiley.">";
			}
		}
		echo "E();";
		echo "\n";
	}
	echo "</script>";
	echo "</div>\n\n";
	

	// monsters
	echo "<div style='position:absolute; top:64px; left:5px; width:100%; z-index:1; height:".$tilex * ($dx * 2 + 1)."px'>";
	echo "<script language='javascript'>\n";
	for ($y = -$dy; $y <= $dy; $y++)
	{
		for ($x = -$dx; $x <= $dx; $x++)
		{
			$mobs = $map->GetMob($cx + $x, $cy + $y);
			if (!empty($mobs))
			{
				foreach ($mobs as $mobkey => $mob)
				{
					printf ("M(%u);", $mob['monstertypeid']);
					//echo "<img src='gameimgs/mobs/".$monstertypes[$mob['monstertypeid']]['img']."' width=".$tilex." height=".$tiley.">";
					break;
				}
			}
			else
			{
				printf ("N();");
				//echo "<img src='gameimgs/nothing.png' width=".$tilex." height=".$tiley.">";
			}
		}
		echo "E();";
		echo "\n";
	}
	echo "</script>";
	echo "</div>\n\n";
	

	// characternames
	echo "<script language='javascript'>\n";
	for ($y = -$dy; $y <= $dy; $y++)
	{
		for ($x = -$dx; $x <= $dx; $x++)
		{
			$chars = $map->GetPly($cx + $x, $cy + $y);
			if (!empty($chars))
			{
				foreach ($chars as $charkey => $char_)
				{
					echo "K(".$x.",".$y.",'".$char_['name']."');\n";
				}
			}
		}
	}
	echo "</script>\n\n";



	echo "<div style='position:absolute; top:64px; left:". (5 + $tilex * ($dx * 2 + 1) + 10) ."px; z-index:10'>";
?>
	<script language="javascript">
		function i(id)
		{
			document.write("<img src='gameimgs/items/" + lead0(id) + ".gif' width=24 height=24>");
		}
	</script>
		<table><tr><td valign=top width=200>
			
			<? 
				/////////////////////////////////////////////////////////////////////////////////////////////////
				// Avatar

				//echo "<img src='".$_SESSION['char']['img']."' width=".$tilex." height=".$tiley.">"; 
				
			?>
			<b>Character</b>
			<table>
				<tr>
					<td>Name:</td>
					<td><? echo $_SESSION['char']['name'] ?></td>
				</tr>
				<tr>
					<td>HP:</td>
					<td><? echo $_SESSION['char']['hp'] ?> / <? echo $_SESSION['char']['chpmax'] ?> / <? echo $_SESSION['char']['chpregen'] ?></td>
				</tr>
				<tr>
					<td>Mana:</td>
					<td><? echo $_SESSION['char']['mana'] ?> / <? echo $_SESSION['char']['cmanamax'] ?> / <? echo $_SESSION['char']['cmanaregen'] ?></td>
				</tr>
				<tr>
					<td>AP / RC:</td>
					<td><? echo $_SESSION['char']['cap'] ?> / <? echo $_SESSION['char']['crc'] ?></td>
				</tr>
				<tr>
					<td>Exp:</td>
					<td><? echo $_SESSION['char']['exp'] ?> / <? echo $levels[$_SESSION['char']['level'] + 1]['expneeded'] ?></td>
				</tr>
				<tr>
					<td>Level:</td>
					<td><? echo $_SESSION['char']['level'] ?></td>
				</tr>
				<tr>
					<td>Gold:</td>
					<td><? echo $_SESSION['char']['gold'] ?></td>
				</tr>
				<tr>
					<td>Region / x / y:</td>
					<td><? echo $_SESSION['char']['regionid'] ?> / <? echo $_SESSION['char']['x'] ?> / <? echo $_SESSION['char']['y'] ?></td>
				</tr>
			</table>
			
			
			<br />
			
			
			<?
				/////////////////////////////////////////////////////////////////////////////////////////////////
				// Movement controls
			?>
			<b>Movement</b>
			<table>
				<tr>
					<td>
						<a href="game.php?walknw=1"><img src="gameimgs/arrownw.gif" border=0></a>
					</td>
					<td>
						<a href="game.php?walknorth=1"><img src="gameimgs/arrowup.jpg" border=0></a>
					</td>
					<td>
						<a href="game.php?walkne=1"><img src="gameimgs/arrowne.gif" border=0></a>
					</td>
				</tr>					
				<tr>
					<td>
						<a href="game.php?walkwest=1"><img src="gameimgs/arrowleft.jpg" border=0></a>
					</td>
					<td>
						<center><a href="game.php?nothing=1">IDLE</a></center>
					</td>
					<td>
						<a href="game.php?walkeast=1"><img src="gameimgs/arrowright.jpg" border=0></a>
					</td>
				</tr>					
				<tr>
					<td>
						<a href="game.php?walksw=1"><img src="gameimgs/arrowsw.gif" border=0></a>
					</td>
					<td>
						<a href="game.php?walksouth=1"><img src="gameimgs/arrowdown.jpg" border=0></a>
					</td>
					<td>
						<a href="game.php?walkse=1"><img src="gameimgs/arrowse.gif" border=0></a>
					</td>
				</tr>					
			</table>


			<b>Messages</b><br />
			<?
				/////////////////////////////////////////////////////////////////////////////////////////////////
				// Event Message
				
				if (isset($fightmessage))
					echo $fightmessage."<br />";
				if (isset($levelupmessage))
					echo $levelupmessage."<br />";
				
				$texts = $map->GetText($_SESSION['char']['x'],$_SESSION['char']['y']);
				if (!empty($texts))
				{
					foreach ($texts as $textkey => $text)
					{
						echo $text['text']."<br />";
					}
				}

				$portals = $map->GetPortal($_SESSION['char']['x'],$_SESSION['char']['y']);
				if (!empty($portals))
				{
					foreach ($portals as $portalkey => $portal)
					{
						echo $portal['text']." <a href='game.php?portal=".$portal['id']."'>Yes.</a><br />";
					}
				}
			?>
			
			<br />

			<?
				/////////////////////////////////////////////////////////////////////////////////////////////////
				// Equipment
				
				echo "<b>Equipment</b><br />";
				$query = "select * from webrpg_inventory ".
					"where characterid=".$_SESSION['char']['id']." and equipped<>0";
				$result = mysql_query($query);
				$equip = array();
				while ($row = mysql_fetch_array($result))
				{
					$equip[$row['equipped']] = $row;
				}
				mysql_free_result($result);
				echo "<table>";
				$i = 1;
				$equippos[$i++] = "Head";
				$equippos[$i++] = "Chest";
				$equippos[$i++] = "Arms";
				$equippos[$i++] = "Legs";
				$equippos[$i++] = "Feet";
				$equippos[$i++] = "Left Hand";
				$equippos[$i++] = "Right Hand";
				$equippos[$i++] = "Ring";
				$equippos[$i++] = "Mantel";
				$equippos[$i++] = "Neck";
				$equippos[$i++] = "Belt";
				$equippos[$i++] = "Eyes";
				$equippos[$i++] = "Hands";
				for ($i = 1; $i < 14; $i++)
				{
					echo "<tr>";
					echo "<td>".$equippos[$i]."</td>";
					echo "<td>";
						if (isset($equip[$i]))
						{
							echo $itemtypes[$equip[$i]['itemtypeid']]['name'];
						}
						else
						{
							echo "nothing";
						}
					echo "</td>";
					echo "<td>";
						if (isset($equip[$i]))
						{
							echo "<a href='game.php?unequip=".$equip[$i]['id']."'>unequip</a>";
						}
						else
						{
							echo "&nbsp;";
						}
					echo "</td>";
					echo "</tr>";		
				}
				echo "</table>";
				
			?>
			
			<br />
		</td><td valign=top>
			
			<?
				/////////////////////////////////////////////////////////////////////////////////////////////////
				// Chat
			
				echo "<b>Chat</b></ br>";
				$query = "select * from webrpg_chat order by time desc limit 10";
				$result = mysql_query($query);
				$chathistory = array();
				$n = 0;
				while ($row = mysql_fetch_array($result))
				{
					$chathistory[$n] = $row;
					$n++;
				}
				echo "<table>";
				for ($i = $n-1; $i >= 0; $i--)
				{
					echo "<tr>";
					echo "<td>".$chathistory[$i]['charactername'].": </td>";
					echo "<td>".$chathistory[$i]['msg']."</td>";
					echo "</tr>";
				}
				echo "</table>";
				mysql_free_result($result);
			?>
			<form action="game.php" method="post">
				<input type="text" name="msg" onkeypress="gamerestop()">
				<input type=submit value="Send">
			</form>

			<?
				/////////////////////////////////////////////////////////////////////////////////////////////////
				// Items on the Floor
				
				echo "<b>Floor</b><br />";
				$items = $map->GetItem($_SESSION['char']['x'], $_SESSION['char']['y']);
				if (!empty($items))
				{
					echo "<table>";
					foreach ($items as $itemkey => $item)
					{
						echo "<tr>";
						echo "<td><script language='javascript'>i(".$item['itemtypeid'].");</script></td>";
						echo "<td>".$itemtypes[$item['itemtypeid']]['name']."</td>";
						echo "<td><a href='game.php?pickup=".$item['id']."'>pick up</a></td>";
						echo "</tr>";
					}
					echo "</table>";
				}
				else
				{
					echo "There is nothing on the floor.<br />";
				}
				
			?>

			<br />
			
			<?
				/////////////////////////////////////////////////////////////////////////////////////////////////
				// Inventory
			
				echo "<b>Inventory</b><br />";
				// because of arrangement after names, we have to join instead of using the consts itemtypedata retrieved at the
				// beginning
				
				// Note: this has to be a right join because the id field gets overridden by the rightmost join partner
				// so let's have webrpg_inventory as the last join partner to save the id. The itemtypeid is still there in
				// the itemtypeid field.
				$query = "select *, max(equipped) as equipped, count(webrpg_itemtypes.id) as stackn from webrpg_itemtypes right join webrpg_inventory on webrpg_inventory.itemtypeid=webrpg_itemtypes.id ".
					"where characterid=".$_SESSION['char']['id']." group by webrpg_itemtypes.id order by webrpg_itemtypes.name, webrpg_inventory.id";
				$result = mysql_query($query);
				echo "<table>";
				while ($row = mysql_fetch_array($result))
				{
					echo "<tr>";
					echo "<td><script language='javascript'>i(".$row['itemtypeid'].");</script></td>";
					echo "<td>".$row['stackn']." ".$row['name']."</td>";
					echo "<td>";
						if ($row['equipped']!=0)
							echo "E";
						else
							echo "&nbsp;";
					echo "</td>";
					echo "<td>";
						if ($row['equipable']!=0)
							echo "<a href='game.php?equip=".$row['id']."'>equip</a>";
						else
							echo "&nbsp;";
					echo "</td>";
					echo "<td>";
						if ($row['useable']!=0)
							echo "<a href='game.php?useitem=".$row['id']."'>use</a>";
						else
							echo "&nbsp;";
					echo "</td>";
					echo "<td><a href='game.php?drop=".$row['id']."'>drop</a></td>";
					echo "</tr>";
				}
				echo "</table>";
				mysql_free_result($result);
			
			?>
			
			<br />

			
		</td></tr></table>
			
			<?
				//echo "<pre>";
				//print_r($_SESSION);
				//echo "</pre>";
			?>
	<? echo "</div>" ?>

</body>
</html>
<?
	////////////////////////////////////////////////////
	// do this after everything is sent
	
	$query = "update webrpg_characters set state='1',timelastaction=NOW() where id=".$_SESSION['char']['id'];
	$result = mysql_query($query);
?>