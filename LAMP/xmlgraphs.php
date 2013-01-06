<?php
include('./L4DStats.class.php');
include('./settings.php');
$L4DStats = new L4DStats();
$ReturnXML = "";

switch($_GET["Graph"])
{
	case "PlayerKillDistributionIndivGame":
		{

		}
		break;

	case "line_last_X_Games":
		{

			$Kills = $L4DStats->GetKillsLastXGames(10);

			$ReturnXML = "<chart>"; 	
			if(empty($XMLSWF_License) == false)
			{
				$ReturnXML .= "<license>$XMLSWF_License</license>";   

			}
			$ReturnXML .= "<chart_type>line</chart_type>
						  <axis_value color='FFFFFF' />
						  <chart_transition type='scale'
                     							  delay='0'
                     							  duration='2'
                     							  order='all'
                     							  />
                     	  <series_color>
						     <color>BE8632</color>
						  </series_color>
						  <chart_data>
						  <row>
						  <null/>
						  	<string></string>
						 	<string></string>
						  	<string></string>
						  	<string></string>
						  	<string></string>
						  	<string></string>
						  	<string></string>
						  	<string></string>
						  	<string></string>
						  	<string></string>
      					  </row>
      					  <row>
      					  	<string>Kill Counts for last 10 Games</string>";
			if(sizeof($Kills) == 0)
			{
				$Legend .= "<string>No Kills</string>\r\n";
				$Values .= "<number tooltip='No Kills'>0</number>\r\n";
			}
			else 
			{
				foreach($Kills as $Kill)
				{
					$ReturnXML .= "<number>$Kill</number>";
				}
			}

			$ReturnXML .= "</row></chart_data></chart>";
		}
		break;

	case "pielast10weapons":
		{

			$Weapons = $L4DStats->GetTopWeaponsXLastGames(10);

			$ReturnXML = "<chart>"; 	
			if(empty($XMLSWF_License) == false)
			{
				$ReturnXML .= "<license>$XMLSWF_License</license>";   

			}
			$ReturnXML .= "<chart_type>pie</chart_type>
						  <axis_value color='FFFFFF' />
						  <chart_transition type='drop'
                     							  delay='0'
                     							  duration='2'
                     							  order='all'
                     							  />
                     	  <tooltip font='arial'
                     	  		   bold='true' 
                     	  		   size='14' 
                     	  		   color='FFFFFF'/>

						  <chart_data>
						  <row>
						  <null/>";

			$Legend = "";
			$Values = "<row><string>Weapons Use in last 10 Games</string>";
			
			if(sizeof($Weapons) == 0)
			{
				$Legend .= "<string>No Kills</string>\r\n";
				$Values .= "<number tooltip='No Kills'>0</number>\r\n";
			}
			else 
			{
				foreach($Weapons as $Weapon => $Kill)
				{
					$Legend .= "<string>$Weapon</string>\r\n";
					$Values .= "<number tooltip='$Weapon'>$Kill</number>\r\n";
				}
			}

			$Legend .= "</row>";

			$ReturnXML .= $Legend . $Values . "</row></chart_data></chart>";
		}
		break;

	case "indivgamekills":
		{
			$Legend = "";
			$Values = "<row><string>Individual Player Kill Count</string>";

			$Players = $L4DStats->GetPlayerTotalKillsIndivGame($_GET['GameID'],$Show_AI_Stats);

			if(sizeof($Players) == 0)
			{
				$Legend .= "<string>No Kills</string>\r\n";
				$Values .= "<number tooltip='No Kills'>0</number>\r\n";
			}
			else 
			{
				foreach($Players as $PlayerName => $Kills)
				{
					$Legend .= "<string>".$L4DStats->ASCIIFIPlayerName($PlayerName)."</string>\r\n";

					$Values .= "<number tooltip='".$L4DStats->ASCIIFIPlayerName($PlayerName)." : $Kills'>$Kills</number>\r\n";
					//$Values .= "<number>$Kills</number>\r\n";
				}
			}
			$ReturnXML = "<chart>"; 	
			if(empty($XMLSWF_License) == false)
			{
				$ReturnXML .= "<license>$XMLSWF_License</license>";   

			}
			$ReturnXML .= "<chart_type>bar</chart_type>
						  <axis_value color='FFFFFF' />
						  <axis_category color='FFFFFF' />
						  <chart_transition type='scale'
                     							  delay='0'
                     							  duration='2'
                     							  order='all'
                     							  />
                     	  <series_color>
							<color>0C78FF</color>
						  </series_color>
                     	  
                     	  <tooltip font='arial'
                     	  		   bold='true' 
                     	  		   size='14' 
                     	  		   color='FFFFFF'/>
						  <chart_data><row><null/>";


			$Legend .= "</row>";

			$ReturnXML .= $Legend . $Values . "</row></chart_data></chart>";
		}
		break;

	case "indivgameweapons":
		{
			$Legend = "";
			$Values = "<row><string>Weapon Use</string>";

			$Weapons = $L4DStats->GetTopWeaponsIndivGame($_GET['GameID'],$Show_AI_Stats);


			if(sizeof($Weapons) == 0)
			{
				$Legend .= "<string>No Kills</string>\r\n";
				$Values .= "<number tooltip='No Kills'>0</number>\r\n";
			}
			else 
			{
				foreach($Weapons as $Weapon => $Kills)
				{
					$Legend .= "<string>$Weapon</string>\r\n";
					$Values .= "<number tooltip='$Weapon : $Kills'>$Kills</number>\r\n";
				}
			}
			$ReturnXML = "<chart>"; 	
			if(empty($XMLSWF_License) == false)
			{
				$ReturnXML .= "<license>$XMLSWF_License</license>";   

			}
			$ReturnXML .= "<chart_type>pie</chart_type>
						  <axis_value color='FFFFFF' />
						  <chart_transition type='drop'
                     							  delay='0'
                     							  duration='2'
                     							  order='all'
                     							  />
                     	  <tooltip font='arial'
                     	  		   bold='true' 
                     	  		   size='14' 
                     	  		   color='FFFFFF'/>
						  <chart_data><row><null/>";


			$Legend .= "</row>";

			$ReturnXML .= $Legend . $Values . "</row></chart_data></chart>";

		}
		break;

	case "indivgamekillsovertime":
		{
		$GameID = $_GET['GameID'];
		if ($Show_AI_Stats == true) 
		{
			$Query = "select distinct(PlayerID) from Statistics where GameID = $GameID";
		}
		else 
		{
			$Query = "select distinct(PlayerID) from Statistics where GameID = $GameID and PlayerID not in (select PlayerID from Player where SteamID like \"BO%\")";
		}
		
		$result = mysql_query($Query);
		$Players = array();
		$Legend = array();
		$MaxCount = 0;
		
		$Players = array();
		while($PlayerRow = mysql_fetch_assoc($result))
		{
			// At some point we may want to filter out the infected - but for the moment lets leave them in
			$Players[$PlayerRow['PlayerID']] = 0;
		}
		
		if(sizeof($Players) == 0)
		{
			$ReturnString = "<chart><chart_type>bar</chart_type><axis_value color='FFFFFF' /><axis_category color='FFFFFF' />".
					"<chart_transition type='scale' delay='0' duration='2' order='all'/>".
                    "<series_color><color>0C78FF</color></series_color>".
					"<tooltip font='arial' bold='true' size='14' color='FFFFFF'/>".
					"<chart_data><row><null/><string>No Kills</string>".
					"</row><row><string>Individual Player Kill Count</string>".
					"<number tooltip='No Kills'>0</number></row></chart_data></chart>";
			print($ReturnString);
			return;
		}
		
		if(sizeof($Players) > 12)
		{
			$ExtraLegend = "<legend size = '10' />";
		}
		if(sizeof($Players) > 8)
		{
			$ExtraLegend = "<legend size = '11' />";
		}
		if(sizeof($Players) > 6)
		{
			$ExtraLegend = "<legend size = '12' />";
		}
		else 
		{
			$ExtraLegend = "";
		}
		
		$PlayerKillsQuery = "select StatID,PlayerID from Statistics where GameID = $GameID";
		
		$PlayersKillResult = mysql_query($PlayerKillsQuery);
		$PlayerKillsOverTime = array();	
		
		while($PlayerKills = mysql_fetch_assoc($PlayersKillResult))
		{
			foreach($Players as $PlayerID => $Score)
			{
				if($PlayerKills['PlayerID'] == $PlayerID)
				{
					$NewScore = $Score + 1;
					$Players[$PlayerID] = $NewScore;
					$PlayerKillsOverTime[$PlayerID][] = $NewScore;
				}
				else 
				{
					$PlayerKillsOverTime[$PlayerID][] = $Score;
				}
			}
		}
		
		foreach($PlayerKillsOverTime as $PlayerID => $PlayerStats)
		{
			if(count($PlayerStats) > $MaxCount)
			{
				$MaxCount = count($PlayerStats);
			}
		}
		
			$ReturnXML = "<chart>"; 	
			if(empty($XMLSWF_License) == false)
			{
				$ReturnXML .= "<license>$XMLSWF_License</license>";   

			}
			$ReturnXML .= "<chart_type>line</chart_type>
						  <axis_value color='FFFFFF' />
						  <axis_category color='FFFFFF' />
						  <chart_transition type='zoom'
                     							  delay='0'
                     							  duration='2'
                     							  order='all'
                     							  />
                		  <chart_pref line_thickness='2' point_shape='none' fill_shape='false' />
                     	  <series_color>
							<color>0C78FF</color>
						  </series_color>
                     	  $ExtraLegend
                     	  <tooltip font='arial'
                     	  		   bold='true' 
                     	  		   size='14' 
                     	  		   color='FFFFFF'/>
						  <chart_data>
						    <row>
						    <null/>";
			$Legend = "";
			$AveragingDivisor = 10;
			$Averaged = $MaxCount / $AveragingDivisor;
			while($x < $Averaged)
			{
				$Legend .= "<string></string>\r\n";
				$x++;
			}
			$Legend .= "</row>";
			
			$Data = "";
			
			foreach($PlayerKillsOverTime as $PlayerID => $Stats)
			{
				$Data .= "<row>";
				$Data .= "<string>".$L4DStats->GetPlayerName($PlayerID)."</string>\r\n";
				$i = 1;
				$Average = 0;
				foreach($Stats as $Stat)
				{
					//$Data .= "<number>$Stat</number>\r\n";
					if($i == $AveragingDivisor)
					{	
						$Average += $Stat;
						$Result = $Average / $AveragingDivisor;
						$Data .= "<number>$Result</number>\r\n";
						$i = 1;
						$Average = 0;
					}
					else 
					{
						$Average += $Stat;
						$i++;
					}
				}
				$Data .= "</row>";
			}
			
						  
			$ReturnXML .= $Legend . $Data . "</chart_data></chart>";
		}
		break;
		
	case "indivgameheadshots":
		{
			$Legend = "";
			$Values = "<row><string>Player Stats</string>";

			$HeadShotRatio = $L4DStats->GetHeadShotRatio($_GET['GameID']);
			//print_r($HeadShotRatio);

			$HeadShots = $HeadShotRatio['HeadShots'];
			$TotalKills = $HeadShotRatio['TotalKills'];

			if($HeadShots > 0)
			{
				//$HeadShots = $HeadShots / $TotalKills;
				$TotalKills = $TotalKills - $HeadShots;
			}

			$Legend .= "<string>Standard Kills</string><string>HeadShots</string>";
			$Values .= "<number tooltip='Standard Kills'>$TotalKills</number><number tooltip='Headshots'>$HeadShots</string>\r\n";

			$ReturnXML = "<chart>"; 	
			if(empty($XMLSWF_License) == false)
			{
				$ReturnXML .= "<license>$XMLSWF_License</license>";   

			}
			$ReturnXML .= "<chart_type>pie</chart_type>
						  <axis_value color='FFFFFF' />
						  <chart_transition type='drop'
                     							  delay='0'
                     							  duration='2'
                     							  order='all'
                     							  />
                     	  <tooltip font='arial'
                     	  		   bold='true' 
                     	  		   size='14' 
                     	  		   color='FFFFFF'/>
						  <chart_data><row><null/>";


			$Legend .= "</row>";

			$ReturnXML .= $Legend . $Values . "</row></chart_data></chart>";

		}
		break;
		
	case "IndivKillsOverTimeGraph":
		{

			$Kills = $L4DStats->GetIndivPlayerKillsOverTime($_GET['PlayerID']);

			$ReturnXML = "<chart>"; 	
			if(empty($XMLSWF_License) == false)
			{
				$ReturnXML .= "<license>$XMLSWF_License</license>";   

			}
			$ReturnXML .= "<chart_type>line</chart_type>
						  <axis_value color='FFFFFF' />
						  <chart_transition type='scale'
                     							  delay='0'
                     							  duration='2'
                     							  order='all'
                     							  />
						  <chart_data>
						  <row>
						  <null/>";
			$Legend = "";

			while($x < sizeof($Kills))
			{
				$Legend .= "<string></string>\r\n";
				$x++;
			}
			$Legend .= "</row>";
			
      		$ReturnXML .= "$Legend<row><string>Kill Counts Over Time</string>";
      		
			if(sizeof($Kills) == 0)
			{
				$Legend .= "<string>No Kills</string>\r\n";
				$Values .= "<number tooltip='No Kills'>0</number>\r\n";
			}
			else 
			{
				foreach($Kills as $Kill)
				{
					$ReturnXML .= "<number>$Kill</number>";
				}
			}

			$ReturnXML .= "</row></chart_data></chart>";
		}
		break;
		
	case "IndivKillsPerMapGraph":
		{

			$Kills = $L4DStats->GetIndivKillsPerMapGraph($_GET['PlayerID']);

			$ReturnXML = "<chart>"; 	
			if(empty($XMLSWF_License) == false)
			{
				$ReturnXML .= "<license>$XMLSWF_License</license>";   

			}
			$ReturnXML .= "<chart_type>column</chart_type>
						  <axis_value color='FFFFFF' />
						  <chart_transition type='scale'
                     							  delay='0'
                     							  duration='2'
                     							  order='all'
                     							  />
                   
						  <legend size='12'/>
						  <chart_data>
						  <row>
						  <string></string>
						  <string></string>
						  </row>";
			/*$Legend = "";
			$Values = "<row><string>Total Kills Per Map</string>";
			foreach($Kills as $MapName => $KillCount)
			{
				
				$Legend .= "<string></string>\r\n";
				$Values .= "<number tooltip='$MapName : $KillCount'>$KillCount</number>\r\n";
			}
			$Legend .= "</row>";
			
			$ReturnXML .= $Legend . $Values . "</row></chart_data></chart>";*/
			foreach($Kills as $MapName => $KillCount)
			{
				
				
				$ReturnXML .= "<row><string>$MapName</string><number tooltip='$MapName : $KillCount'>$KillCount</number></row>\r\n";
			}
			
			$ReturnXML .= "</chart_data></chart>";
			
		}
		break;
}

print($ReturnXML);

?>