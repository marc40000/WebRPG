<?
	//
	// Execute this every 15 seconds
	//


	//include ("db.php");
	//include ("map.php");
	
	
	// make sure that only one of these is running at a time !
	$query = "update webrpg_one set bgtaskbusy=".$_SESSION['char']['id']." where bgtaskbusy=0 and NOW()-timelastiterate>15";
	mysql_query($query);
	$query = "select bgtaskbusy from webrpg_one";
	$result = mysql_query($query);
	$one = mysql_fetch_array($result);
	mysql_free_result($result);
	if ($one['bgtaskbusy'] == $_SESSION['char']['id'])
	{
		// we can run and guaranteed alone !
		


		$query = "select * from webrpg_one";
		$result = mysql_query($query);
		$one = mysql_fetch_array($result);
		mysql_free_result($result);
		//echo "time: ".$one['time']."<br />";
		
	
		// logout inactive chars
		//echo "Logging our inactive characters<br />";
		$query = "update webrpg_characters set state=0 where NOW()-timelastaction>300 and state=1"; // logout after 5 minutes
		mysql_query($query);
		
		// increase health/mana of logged in players, every minute
		//echo "Increase Health of players<br />";
		if (($one['time']%4) == 0)
		{
			$query = "update webrpg_characters set hp=if(hp+chpregen>chpmax,chpmax,hp+chpregen),mana=if(mana+cmanaregen>cmanamax,cmanamax,mana+cmanaregen) where state=1";
			mysql_query($query);
		}
	
		// delete old chatmsgs, don't do this until chat is heavily used
		/*echo "Delete old chat msgs<br />";
		$query = "delete from webrpg_chat where NOW()-time>300";	// älter als 5 min
		mysql_query($query);*/
		
	
		//echo "\n<hr />\n";
	
	
		// remove old items lying around, after one day
		$query = "delete from webrpg_items where NOW()-timedropped>86400";
		mysql_query($query);
		
	
	
	
	
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
			$regions[$row['id']] = $row;
		}
		mysql_free_result($result);
		
		
		
		foreach ($regions as $regionkey => $region)
		{
			//echo "Region: ".$region['id']."<br />";
	
			$map = new Map();
			$map->Load($region['id']);
		
			
			// move the mobs		
			$query = "select * from webrpg_monsters where regionid=".$region['id']." and state=1";
			$result = mysql_query($query);
			while ($row = mysql_fetch_array($result))
			{
				$mobcur = $row;
				
				// stupid random walk for now
				$walkdir = mt_rand(0, 3);
				$wantstomove = false;
				if ($walkdir == 0)
				{
					$xnew = $mobcur['x'];
					$ynew = $mobcur['y'] - 1;
					$wantstomove = true;
				}
				else
				if ($walkdir == 1)
				{
					$xnew = $mobcur['x'];
					$ynew = $mobcur['y'] + 1;
					$wantstomove = true;
				}
				else
				if ($walkdir == 2)
				{
					$xnew = $mobcur['x'] - 1;
					$ynew = $mobcur['y'];
					$wantstomove = true;
				}
				else
				if ($walkdir == 3)
				{
					$xnew = $mobcur['x'] + 1;
					$ynew = $mobcur['y'];
					$wantstomove = true;
				}
				//$wantstomove = false;
				
				$moved = false;
				if ($wantstomove)
				{
					//echo "Monster ".$mobcur['id']." wants to move in direction ".$walkdir."<br />";
				
					// check for wall
					if (($map->GetSave($xnew, $ynew) & 1024) == 0)
					{
						// no wall
						
						// check for chars
						$charattarget = false;
						$chars = $map->GetPly($xnew, $ynew);
						if (!empty($chars))
						{
							foreach ($chars as $charkey => $char_)
							{
								$charattarget = true;
								break;
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
									if ($mob['id'] != $mobcur['id'])
									{
										$mobattarget = true;
										break;
									}
								}
							}
							if (!$mobattarget)
							{
								// no mob
								
								/////////////////////////////////////////////////////////////////////////////
								// execute the movement
								$query = "update webrpg_monsters set ".
									"x=".$xnew.",".
									"y=".$ynew.
									" where id=".$mobcur['id'];
								mysql_query($query);
								$mob['x'] = $xnew;
								$mob['y'] = $ynew;
								
								//$map->LoadMobs();
								$moved = true;
							}
							else
							{
								/////////////////////////////////////////////////////////////////////////////
								// mob is at the target postion
								// => do nothing
							}
										
						}
						else
						{
							/////////////////////////////////////////////////////////////////////////////
							// char is at the target postion
							// => do nothing for now
						}
					}
					else
					{
						// wall
					}
				}
				
			}
			mysql_free_result($result);
			
			
	
			// respawn monsters
			$query = "select * from webrpg_monsters where regionid=".$region['id']." and state=0 and NOW()-timelastdeath>=300";
			$result = mysql_query($query);
			while ($row = mysql_fetch_array($result))
			{
				$row['state']=1;
				$row['x']=$row['spawnx'];
				$row['y']=$row['spawny'];
				$row['hp']=$monstertypes[$row['monstertypeid']]['hp'];
				
				$isspace = false;
				$xnew = $row['x'];
				$ynew = $row['y'];
				
	
				// check for chars
				$charattarget = false;
				$chars = $map->GetPly($xnew, $ynew);
				if (!empty($chars))
				{
					foreach ($chars as $charkey => $char_)
					{
						$charattarget = true;
						break;
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
						$isspace = true;
					}
				}
								
				if ($isspace)
				{
					$query = "update webrpg_monsters set ".
						"state=".$row['state'].",".
						"x=".$row['x'].",".
						"y=".$row['y'].",".
						"hp=".$row['hp'].
						" where id=".$row['id'];
					mysql_query($query);
	
					//echo "respawn ".$row['id']." at ".$row['spawnx']." / ".$row['spawny']."<br />";
				}			
				else
				{
					//echo "cannot respawn ".$row['id']." at ".$row['spawnx']." / ".$row['spawny']."<br />";
				}
			}	
			mysql_free_result($result);
			
			//echo "\n<hr />\n";
		}
		
	
	
		// increase time
		$query = "update webrpg_one set time=time+1";
		mysql_query($query);
		
		
		
		////////////////////////////////////////////////////////////////
		// give busy flag free again
		$query = "update webrpg_one set bgtaskbusy=0, timelastiterate=NOW()";
		$result = mysql_query($query);
	}
?>