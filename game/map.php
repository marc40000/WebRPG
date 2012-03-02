<?
	class Map 
	{
		var $db;
		var $p;
		var $ply;
		var $mob;
		var $item;
		var $portal;
		var $text;
		
		function Map()
		{
		}
		
		function Get($x,$y)
		{
			return $this->p[ $y * $this->db['x'] + $x ];
		}

		function Set($x,$y,$value)
		{
			$this->p[ $y * $this->db['x'] + $x ] = $value;
		}
		
		function GetSave($x,$y)
		{
			if (($x>=0) && ($y>=0) && ($x<$this->db['x']) && ($y<$this->db['y']))
				return $this->p[ $y * $this->db['x'] + $x ];
			else
				return 0;
		}

		function SetSave($x,$y,$value)
		{
			if (($x>=0) && ($y>=0) && ($x<$this->db['x']) && ($y<$this->db['y']))
				$this->p[ $y * $this->db['x'] + $x ] = $value;
		}
		

		function GetPly($x,$y)
		{
			// warning at this point is ok. How do I tell php to do that what it does
			// in the case of the warning while not printing that warning?
			return $this->ply[ $y * $this->db['x'] + $x ];
		}

		function AddPly($x,$y,$value)
		{
			$this->ply[ $y * $this->db['x'] + $x ][$value['id']] = $value;
		}
		
		function GetPlySave($x,$y)
		{
			if (($x>=0) && ($y>=0) && ($x<$this->db['x']) && ($y<$this->db['y']))
				return $this->ply[ $y * $this->db['x'] + $x ];
			else
				return array();
		}

		function AddPlySave($x,$y,$value)
		{
			if (($x>=0) && ($y>=0) && ($x<$this->db['x']) && ($y<$this->db['y']))
				$this->ply[ $y * $this->db['x'] + $x ] = $value;
		}
		

		function GetMob($x,$y)
		{
			return $this->mob[ $y * $this->db['x'] + $x ];
		}

		function AddMob($x,$y,$value)
		{
			$this->mob[ $y * $this->db['x'] + $x ][$value['id']] = $value;
		}
		
		function GetMobSave($x,$y)
		{
			if (($x>=0) && ($y>=0) && ($x<$this->db['x']) && ($y<$this->db['y']))
				return $this->mob[ $y * $this->db['x'] + $x ];
			else
				return array();
		}

		function AddMobSave($x,$y,$value)
		{
			if (($x>=0) && ($y>=0) && ($x<$this->db['x']) && ($y<$this->db['y']))
				$this->mob[ $y * $this->db['x'] + $x ] = $value;
		}
		
		
		function GetItem($x,$y)
		{
			return $this->item[ $y * $this->db['x'] + $x ];
		}

		function AddItem($x,$y,$value)
		{
			$this->item[ $y * $this->db['x'] + $x ][$value['id']] = $value;
		}
		
		function GetItemSave($x,$y)
		{
			if (($x>=0) && ($y>=0) && ($x<$this->db['x']) && ($y<$this->db['y']))
				return $this->item[ $y * $this->db['x'] + $x ];
			else
				return array();
		}
		
		function AddItemSave($x,$y,$value)
		{
			if (($x>=0) && ($y>=0) && ($x<$this->db['x']) && ($y<$this->db['y']))
				$this->item[ $y * $this->db['x'] + $x ] = $value;
		}
		
		
		function GetPortal($x,$y)
		{
			return $this->portal[ $y * $this->db['x'] + $x ];
		}

		function AddPortal($x,$y,$value)
		{
			$this->portal[ $y * $this->db['x'] + $x ][$value['id']] = $value;
		}
		
		function GetPortalSave($x,$y)
		{
			if (($x>=0) && ($y>=0) && ($x<$this->db['x']) && ($y<$this->db['y']))
				return $this->portal[ $y * $this->db['x'] + $x ];
			else
				return array();
		}
		
		function AddPortalSave($x,$y,$value)
		{
			if (($x>=0) && ($y>=0) && ($x<$this->db['x']) && ($y<$this->db['y']))
				$this->portal[ $y * $this->db['x'] + $x ] = $value;
		}
		

		function GetText($x,$y)
		{
			return $this->text[ $y * $this->db['x'] + $x ];
		}

		function AddText($x,$y,$value)
		{
			$this->text[ $y * $this->db['x'] + $x ][$value['id']] = $value;
		}
		
		function GetTextSave($x,$y)
		{
			if (($x>=0) && ($y>=0) && ($x<$this->db['x']) && ($y<$this->db['y']))
				return $this->text[ $y * $this->db['x'] + $x ];
			else
				return array();
		}
		
		function AddTextSave($x,$y,$value)
		{
			if (($x>=0) && ($y>=0) && ($x<$this->db['x']) && ($y<$this->db['y']))
				$this->text[ $y * $this->db['x'] + $x ] = $value;
		}

		
		
		function Load($mapid)
		{
			$this->id = $mapid;
			
			$query = "select * from webrpg_regions where id=".$mapid;
			$result = mysql_query($query);
			$this->db = mysql_fetch_array($result);
			mysql_free_result($result);
			
			// >1024 means unwalkable
			$m['X'] = 10 + 1024;
			$m['.'] = 9;
			$m['!'] = 12;
			$m['+'] = 11;
			$m['<'] = 8;
			$m['>'] = 7;
			$m['B'] = 13 + 1024;
			$m['G'] = 5;
			$m['W'] = 1;
			$m['M'] = 6 + 1024;
			$m['S'] = 2;
			$m['H'] = 4 + 1024;
			$m['C'] = 14 + 1024;
			$m['L'] = 3 + 1024;
			$m['A'] = 15 + 1024;
			$m['K'] = 16;

			$f = fopen ("regions/".$this->db['filename'].".geo","rb");
			for ($y=0;$y<$this->db['y'];$y++)
			{
				for ($x=0;$x<$this->db['x'];$x++)
				{
					$c = fread($f,1);
					if (isset($m[$c]))
					{
						$this->Set($x,$y,$m[$c]);
					}
					else
					{
						// if invalid, set it to lava, the creator will most likely notice that
						$this->Set($x,$y,3 + 1024);
					}
				}
				$c = fread($f,1);
				$c = fread($f,1);
			}
			fclose($f);


			$this->LoadPlys();
			$this->LoadMobs();
			$this->LoadItems();
			$this->LoadPortals();
			$this->LoadTexts();
		}
		
		function LoadPlys()
		{
			$this->ply = array();
			$query = "select * from webrpg_characters where regionid=".$this->id." and state=1";
			$result = mysql_query($query);
			while ($row = mysql_fetch_array($result))
			{
				$this->AddPly($row['x'],$row['y'],$row);
			}
			mysql_free_result($result);
		}
		
		function LoadMobs()
		{
			$this->mob = array();
			$query = "select * from webrpg_monsters where regionid=".$this->id." and state=1";
			$result = mysql_query($query);
			while ($row = mysql_fetch_array($result))
			{
				$this->AddMob($row['x'],$row['y'],$row);
			}
			mysql_free_result($result);
		}

		function LoadItems()
		{
			$this->item = array();
			$query = "select * from webrpg_items where regionid=".$this->id;
			$result = mysql_query($query);
			while ($row = mysql_fetch_array($result))
			{
				$this->AddItem($row['x'],$row['y'],$row);
			}
			mysql_free_result($result);
		}		
		function LoadPortals()
		{
			$this->portal = array();
			$query = "select * from webrpg_portals where regionid=".$this->id;
			$result = mysql_query($query);
			while ($row = mysql_fetch_array($result))
			{
				$this->AddPortal($row['x'],$row['y'],$row);
			}
			mysql_free_result($result);
		}				
		function LoadTexts()
		{
			$this->text = array();
			$query = "select * from webrpg_texts where regionid=".$this->id;
			$result = mysql_query($query);
			while ($row = mysql_fetch_array($result))
			{
				$this->AddText($row['x'],$row['y'],$row);
			}
			mysql_free_result($result);
		}				
	}
?>