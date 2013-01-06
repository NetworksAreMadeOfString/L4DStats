<?php
/*==============================================================
L4DStats - Open Source Left4Dead Stats App

Copyright (c) 2009 Gareth Llewellyn

This program is free software, distributed under the terms of
the GNU General Public License Version 2. See the LICENSE file
at the top of the source tree.

================================================================*/

/// L4DStats.class.php
/// A Class that contains all the functions for the app

error_reporting (E_ALL ^ E_NOTICE);

class L4DStats
{
	//static public $Debug = true;

	//---------------------------------------------------------------------------------------------------------
	// Processing Functions
	//---------------------------------------------------------------------------------------------------------
	/**
 	* ProcessLog
 	*
 	* This function looks for all the log files in the specified directory
 	* and then checks to see if its been closed by the server (to check its
 	* a finished log) and then parses it for passing to other functions
 	*/
	function ProcessLog($LogDir, $Log, $MapName, $Debug=false)
	{
		$SetupLog = "";
		$StatsLog = null;
		$Kills = 0;
		$Stats = array();
		
		if($this->Check_sv_log_onefile($LogDir.$Log) == true)
		{
			if($Debug == true)
			{
				print("<b>Processing as a log file written with sv_log_onefile 1</b><br>");
			}
			$MapName = $MapName . "_movie";
			$CurrentLog = $LogDir.$Log;
			$SetupLog = $Log;
			$StatsLog = "";
		}
		else 
		{
			if($Debug == true)
           	{
				print("<b>Processing as a log file written with sv_log_onefile 0</b><br>");
				print("Attempting to find accompanying stats log....<br>");
           	}
           	
			$FileName = explode("_",$Log);
			$LogFilePrefix = $FileName[0] . "_". $FileName[1] . "_". $FileName[2] . "_". $FileName[3] . "_". $FileName[4] . "_". $FileName[5];
		
			if(file_exists($LogDir.$LogFilePrefix."_001.log"))
			{
				$CurrentLog = $LogDir.$LogFilePrefix."_001.log";
				$SetupLog = $Log;
				$StatsLog = $LogFilePrefix."_001.log";
				if($Debug == true)
           		{
					print("Found possible match: $CurrentLog");
           		}
			}
			else 
			{
				if($Debug == true)
           		{
					print("Usual statistics log X_001.log not found - trying known alternatives<br>");
           		}
				$StatsLog = $this->FindAlternativeLogFile($LogDir,$Log,$Debug);
				
				if ($StatsLog == "FAILED")
                {
                    print("Alternative Files not found or some other such nasty error\r\n");
                    $ReturnArray = array('Kills' => 0,
										'Stats' => 0,
										'MapName' => "",
										'Closed' => false);
					return $ReturnArray;
                }
                else
                {
                	$SetupLog = $Log;
                    $CurrentLog = $LogDir . $StatsLog;
                }   
			}
			
		}
		
		//Open the file
		$handle = fopen($CurrentLog, 'r');
		

		//First thing to try is to see if this log has been closed yet
		try
		{
			$LogAsArray = file($CurrentLog);
			$LogLineCount = count($LogAsArray);

			//If the Last line isn't 'Log file closed' then the server is probably still
			//using this file
			if(stristr($LogAsArray[$LogLineCount-1], 'Log file closed') === FALSE)
			{
				if($Debug == true)
				{
					print("Log File not closed yet, skipping. <br>");
				}

				$ReturnArray = array('Kills' => 0,
									'Stats' => 0,
									'MapName' => "",
									'Closed' => false);
				return $ReturnArray;

			}
			else
			{
				if($Debug == true)
				{
					print("Log file appears to have been closed, processing:<br>");
				}
			}

		}
		catch (Exception $e)
		{
			if($Debug == true)
			{
				print("An error occured during log file tests - locked? Quitting.<br>$e");

			}
			$ReturnArray = array('Kills' => 0,
								'Stats' => 0,
								'MapName' => "",
								'Closed' => false);
			return $ReturnArray;
		}

		//Now loop through each line of the file parsing it as we go
		while (!feof($handle))
		{
			$Data = fgets($handle);
			
			//Experimental Regular Expression matching
			$StatType = substr($Data,26,4);

			switch($StatType)
			{
				//This is a line where something killed something
				case "DEAT":
				{
					$Stat = $this->RegexForDeathStats($Data);
					if(isset($Stat['GamerName']) && isset($Stat['SteamID']) && isset($Stat['AreaID']) && isset($Stat['Killed']) && isset($Stat['Weapon']))
					{
						$Kills = $Kills +1;
						$Stats[] = $Stat;
					}
					else 
					{
						if($Debug == true)
						{
							//print("Something went wrong with this line during parsing<br>");
						}
					}
				}
				break;
			
				//Something got incapped
				case "INCA":
				{
					//print("INCAP Event  -  ");
					//RegexIncapParser($Data);
				}
				break;
			}
			
			
			///////////////////////////////////////////////////////////////////////////////////////////////////////////
			// OLD STYLE MATCHING
			/*$Line = explode(": (",$Data);

			//Make sure this isn't a funky line
			if(count($Line) == 2)
			{
				switch(substr($Line[1], 0, 4))
				{
					case "1)Ta":
						{
							//print("TANK Event<br>");
						}
						break;

					case "2)Ta":
						{
							//print("TANK Event<br>");
						}
						break;
					case "RESC":
						{
							//print("Rescue Event<br>");
						}
						break;

					case "INCA":
						{
							if($Debug == true)
							{
								print("A Player was incapacitated<br>");
							}
						}
						break;

					case "PHYS":
						{
							//print("The physics engine did something<br>");
						}
						break;

					case "TONG":
						{
							//print("A smoker did something<br>");
							//print("$Line[1] <br>");
						}
						break;

					case "MOB)":
						{
							//print("MOB Event<br>");
						}
						break;

					case "DEAT":
						{
							//Cheap and cheerful way of counting kills
							$Kills = $Kills +1;

							$GamerName = substr($Line[1],7,strpos($Line[1], "<")-7);

							$SteamID = explode("><",$Line[1]);
							$SteamID = $SteamID[1];

							//Get the area this happened
							$AreaID = substr($Line[1],strpos($Line[1], "<Area")+6,strpos($Line[1],"killed") - strpos($Line[1], "<Area") - 9);

							//Find out what was killed
							$KilledEntity = substr($Line[1],strpos($Line[1], "killed \"")+8,strpos($Line[1],"<",strpos($Line[1], "killed \"")+8) - strpos($Line[1], "killed \"") - 8);

							//Find out what was used
							$Weapon = explode("with \"",$Line[1]);
							$Weapon = $Weapon[1];
							$Weapon = str_replace("\"","",$Weapon);
							$Weapon = explode(" ",$Weapon);
							$Weapon = $Weapon[0];
							$Weapon = rtrim($Weapon);

							//Did someone score a headshot?
							if(stristr($Line[1], 'headshot') === FALSE)
							{
								$HeadShot = 0;
							}
							else
							{
								$HeadShot = 1;

							}

							switch($GamerName)
							{
								case "Tank":
								case "(1)Tank":
								case "(2)Tank":
								case "(3)Tank":
								case "(4)Tank":
									{
										$GamerName = "Tank";
										$SteamID = "BOT-TANK";
									}
									break;

								case "Hunter":
									{
										$SteamID = "BOT-Hunter";
									}
									break;

								case "infected":
									{
										$SteamID = "BOT-infected";
									}
									break;

								case "witch":
									{
										$SteamID = "BOT-witch";
									}
									break;

								case "Smoker":
									{
										$SteamID = "BOT-Smoker";
									}
									break;

								case "Boomer":
									{
										$SteamID = "BOT-Boomer";
									}
									break;

								case "Bill":
									{
										$SteamID = "BOT-Bill";
									}
									break;

								case "Louis":
									{
										$SteamID = "BOT-Louis";
									}
									break;

								case "Zoey":
									{
										$SteamID = "BOT-Zoey";
									}
									break;

								case "Francis":
									{
										$SteamID = "BOT-Francis";
									}
									break;
							}

							if($Weapon == "")
							{
								break;
							}

							$GamerName = str_replace("\"","",$GamerName);

							if($Weapon  == "player" || $Weapon  == "prop_physics" ||$Weapon  == "trigger_hurt" || $Weapon  == "env_fire" || $Weapon == "trigger_hurt_ghost" || $Weapon  == "Reset" || $Weapon == "prop_door_rotating_checkpoint" || $Weapon == "entityflame" || $Weapon == "world" || $Weapon == "worldspawn")
							{
								break;
							}
							else
							{
								$Stats[] = array('GamerName' => $GamerName,
												'SteamID' => $SteamID,
												'AreaID' => $AreaID,
												'Killed' => $KilledEntity,
												'Weapon' => $Weapon,
												'HeadShot' => $HeadShot);
							}

							//If we are debugging lets have a nice sentence
							if($Debug == true)
							{
								print("$GamerName <b>(</b>$SteamID<b>)</b> <b>killed</b> $KilledEntity <b>in area</b> $AreaID <b>with</b> $Weapon");

								if($HeadShot == true)
								{
									print(" (HEADSHOT!)<br>\r\n");
								}
								else
								{
									print("<br>\r\n");
								}
							}
						}
						break;

					case "SKIN":
						{
							//print("(SKIN) Event<br>");
						}
						break;

					default:
						{
							//print("Match error: " . $Data);
						}
						break;

				}
			}
			else
			{
				if($Debug == true)
				{
					print("This line is of no value:");
					print("<br>".$Data."<br>");
				}
			}*/
			/////////////////////////////////////// end of original parser ///////////////////////////////////////////////////////		

		}
		
		
		fclose($handle);
		
		$ReturnArray = array('Kills' => $Kills,
							'Stats' => $Stats,
							'MapName' => $MapName,
							'Closed' => true);
							
		//Now we clean up
		//Move the setup log (sometimes this is also the stats log)
		if(file_exists($LogDir.$SetupLog))
		{
			if($Debug == true)
			{
				print("Moving setup log: ". $LogDir.$SetupLog."<br>");
			}
			rename($LogDir.$SetupLog,$LogDir."old/".$SetupLog);
		}
		else 
		{
			if($Debug == true)
			{
				print("Setup log: ".$LogDir.$SetupLog." has disappeared during processing!<br>");
			}
		}
		
		if($StatsLog != "")
		{
			if(file_exists($LogDir.$StatsLog))
				{
				if($Debug == true)
				{
					print("Moving stats Log ". $LogDir.$StatsLog."<br>");
				}
				rename($LogDir.$StatsLog,$LogDir."old/".$StatsLog);
			}
			else 
			{
				if($Debug == true)
				{
					print("Stats log: ".$LogDir.$StatsLog." has disappeared during processing!<br>");
				}
			}
		}
		else 
		{
			if($Debug == true)
			{
				print("No stats log used during processing (invalid log or sv_log_onefile 1)<br>");
			}
		}
		
		return $ReturnArray;
	}

	/**
    * Check_sv_log_onefile
    *
    * This function is to check whether or not this log is part of a log 
    * pair (sv_log_onefile 0) or a single log (sv_log_onefile 1)
	*/
	function Check_sv_log_onefile($LogFileName)
	{
		$LogFileHandle = fopen($LogFileName, 'r');

		//Now loop through each line of the file parsing it as we go
		while (!feof($LogFileHandle))
		{
			//Get an entire line from the file at a time
			$LogLine = fgets($LogFileHandle);
			//Need to find out if this is a valid log
			if(strstr($LogLine, 'DEATH') === FALSE)
			{
				//false
				//print("<i>$LogLine</i>");
			}
			else
			{
				//print("<b>$LogLine</b>");
				fclose($LogFileHandle);
				return true;
			}
		}
		//If the while loop finishes without exiting we can assume this is a setup file only.
		fclose($LogFileHandle);
		return false;
	}
        
	//---------------------------------------------------------------------------------------------------------
	// Regex Functions
	//---------------------------------------------------------------------------------------------------------
	
	/**
    * RegexForDeathStats
    *
    * This function is a much cleaner way to parse a log line
	*/
	function RegexForDeathStats($Data)
	{
		//I hate the stack
		$PlayerName = "";
		$AreaID = "";
		$KilledEntity = "";
		$Weapon = "";
		$HeadShot = 0;
		
		//PlayerName - limit the string to a max of 30 characters to prevent double regex match
		preg_match("/^(\w|\W)+\<[0-9]+\>(<ST|<BO|<><)/",substr($Data,33,40),$Origmatch);
		preg_match("/^(\w|\W)+\<[0-9]/",$Origmatch[0],$Matches);
		$PlayerName = $this->FormatBotNames(substr($Matches[0],0,-2));
		
		
		//SteamID
		preg_match("/STEAM_[0-9]:[0-9]:[0-9]+/",$Data,$Matches);
		
		 if(!isset($Matches[0]) ||
		    $PlayerName == "Tank" ||
		    $PlayerName == "Smoker" ||
		    $PlayerName == "witch" ||
		    $PlayerName == "Boomer" ||
		    $PlayerName == "Hunter" ||
		    $PlayerName == "Louis" ||
		    $PlayerName == "Zoey" ||
		    $PlayerName == "Bill" ||
		    $PlayerName == "Francis" ||
		    $PlayerName == "infected" ||
		    $PlayerName == "Infected")
	    {
	    	$SteamID = "BOT-".$PlayerName;
	    }
	    else
	    {
	     $SteamID = $Matches[0];
	    }
		
		//Substr for Area
		preg_match("/\<Area\s\d+/",$Data,$Matches);
		$AreaID = substr($Matches[0],6,strlen($Matches[0]));
		
		//Substr for killed
		//preg_match("/(killed\s\"\w+|e\swith\s\"\w+)/",$Data,$Matches);
		preg_match("/killed\s\"(\w|\W)+\<\d+\>/",$Data,$Matches);
		$KilledEntity = substr($Matches[0],8,strlen($Matches[0]));
		
		preg_match("/(\w|\W)+\</",$KilledEntity,$Matches);
		$KilledEntity = substr($Matches[0],0,strlen($Matches[0]) -1);
		
		//Substr for weapon
		preg_match("/with\s\"\w+/",$Data,$Matches);
		$Weapon = substr($Matches[0],6,strlen($Matches[0]));

		//Did someone score a headshot?
		if(stristr($Data, 'headshot') === FALSE)
		{
			$HeadShot = 0;
		}
		else
		{
			$HeadShot = 1;

		}
			
		//Make sure there aren't any weird weapons
		if($Weapon  == "player" || 
		$Weapon  == "prop_physics" ||
		$Weapon  == "trigger_hurt" || 
		$Weapon  == "env_fire" || 
		$Weapon == "trigger_hurt_ghost" || 
		$Weapon  == "Reset" || 
		$Weapon == "prop_door_rotating_checkpoint" || 
		$Weapon == "entityflame" || 
		$Weapon == "world" || 
		$Weapon == "worldspawn" ||
		$PlayerName == "" ||
		$SteamID == "" ||
		$KilledEntity == "" ||
		$Weapon == "" ||
		$AreaID == "" ||
		!isset($PlayerName) ||
		!isset($SteamID) ||
		!isset($KilledEntity) ||
		!isset($Weapon) ||
		!isset($AreaID))
		{
			return;
		}
		else
		{
			$ReturnArray = array('GamerName' => $PlayerName,
								'SteamID' => $SteamID,
								'AreaID' => $AreaID,
								'Killed' => $KilledEntity,
								'Weapon' => $Weapon,
								'HeadShot' => $HeadShot);	
		}
			
		
		if($Debug == true)
		{
			if(stristr($SteamID,"STE") !== false && $PlayerName == "infected")
			{
				print("<BR><BR><h2>$Data</h2><br><BR>");
			}
			print("$PlayerName ($SteamID) killed $KilledEntity with $Weapon ($HeadShot) in area $AreaID<br>");
		}
		
		return $ReturnArray;
	}
	
	/**
    * FindAlternativeLogFile
    *
    * This function is to try and find the alternative file if the log file is a setup log
    **/
	function FindAlternativeLogFile($LogDir,$SetupLog,$Debug)
	{
		//print("Log: $LogDir, Setup Log: $SetupLog<br><br>");
		
		$logFileParts = explode("_",$SetupLog);
		$logFilePrefix = $FileName[0] . "_". $FileName[1] . "_". $FileName[2] . "_". $FileName[3] . "_". $FileName[4] . "_". $FileName[5];
		$fullFilePath = "";
		$ValidFullFilePath = false;
		$NewFileName = "";
		
		for ($a =1; $a<5; $a++)
        {
            $NewFileName = $logFileParts[0] . "_" . $logFileParts[1] . "_" . $logFileParts[2] . "_" . $logFileParts[3] . "_" . $logFileParts[4] . "_" + $logFileParts[5] . "_00" . $a . ".log";
           	if($Debug == true)
           	{
            	print("Trying: $NewFileName<br>");
           	}
           	
            if ($NewFileName == $SetupLog)
            {
                continue;
            }
            else
            {
                $fullFilePath = $LogDir + $NewFileName;
                if (file_exists($fullFilePath))
                {
                    $ValidFullFilePath = true;
                    break;
                }
            }
        }        

        //Check if that worked
        if ($ValidFullFilePath == false)
        {
        	$minutes = substr($logFileParts[5],10,2);
            $hours = substr($logFileParts[5],8, 2);
            $day = substr($logFileParts[5],6, 2);
            $month = substr($logFileParts[5],4, 2);
            $year = substr($logFileParts[5],0, 4);
            $dateTimeString = "";
            $minutesString = "";
                
            for ($a = 1; $a < 5; $a++)
            {
                if ($minutes == 59)
                {
                    $hours++;
                    $dateTimeString = substr($logFileParts[5],0, 8) . $hours . "00";
                }
                else
                {
                    $minutes++;
                    if (strlen($minutes) == 1)
                    {
                        $minutesString = "0" . $minutes;
                    }
                    else
                    {
                        $minutesString = $minutes;
                    }
                    $dateTimeString = substr($logFileParts[5],0, 10) . $minutesString;
                }
                
                $NewFileName = $logFileParts[0] . "_" . $logFileParts[1] . "_" . $logFileParts[2] . "_" . $logFileParts[3] . "_" . $logFileParts[4] . "_" . $dateTimeString . "_000.log";
                
                if ($NewFileName == $SetupLog)
                {
                    continue;
                }
                else
                {
                    $fullFilePath = $LogDir . $NewFileName;
					
                    if (file_exists($fullFilePath))
                    {
                    	if($Debug == true)
           				{
                    		print("Found alternative log name ($NewFileName)<br>");
           				}
                        $ValidFullFilePath = true;
                        break;
                    }
                }
            }
        }
        
        if ($ValidFullFilePath == false)
        {
            return "FAILED";
        }
        else
        {
            return substr($fullFilePath,strlen($LogDir));
        }
	}
	
	/**
    * ProcessStatsToXMLRPC
    *
    * This function packages up the processed stats into a huge XML string which
	* can be post'd to the XMLRPC server 
	*/
	function ProcessStatsToXMLRPC($Stats,$Kills,$Map,$LogFilePrefix,$Local_Server_ID,$Remote_Stats_Server,$Remote_Stats_Key)
	{
		$Players = array();

		$Date = explode("_",$LogFilePrefix);
		$Date = $Date[5];

		if($Debug == true)
		{
			print("Processing Stats for Game $LogFilePrefix on map $Map where there were $Kills kills!<br>");
		}

		//Get an array contain the names and steam ID's of the players
		foreach($Stats as $Stat)
		{
			$Players[$Stat['SteamID']] = $this->CleanSQL($Stat['GamerName']);
		}

		$XMLString = "<L4DStats><GAME><MAP>$Map</MAP><KILLS>$Kills</KILLS><LOGPREFIX>$LogFilePrefix</LOGPREFIX><PLAYERS>".serialize($Players)."</PLAYERS><SERVERID>$Local_Server_ID</SERVERID><STATS>";

		foreach ($Stats as $Stat)
		{
			if($Stat['HeadShot'] == 1)
			{
				$HeadShot = 1;
			}
			else
			{
				$HeadShot = 0;
			}

			if($Stat['AreaID'] == "")
			{
				$Stat['AreaID'] = 0;
			}

			$XMLString .= "<STAT><STEAMID>".$Stat['SteamID']."</STEAMID><AREAID>".$Stat['AreaID']."</AREAID><KILLED>".$Stat['Killed']."</KILLED><WEAPON>".$Stat['Weapon']."</WEAPON><HEADSHOT>$HeadShot</HEADSHOT></STAT>\r\n";
		}
		$XMLString .= "</STATS></GAME></L4DStats>";


		$URL = $Remote_Stats_Server."/xmlrpc.php";
		$ch = curl_init();
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, "XML=$XMLString&Key=$Remote_Stats_Key");
		// grab URL, and return output
		$returnedxml = curl_exec($ch);

		if($Debug == true)
		{
			print($returnedxml);
		}

		// close curl resource, and free up system resources
		curl_close($ch);
	}

	/**
    * ProcessStatsFromXML
    *
    * This function receives the XMLRPC stream and turns it back into PHP objects to be
	* passed to the ProcessStats() function
	*/
	function ProcessStatsFromXML($XMLString)
	{
		if($Debug == true)
		{
			print("In ProcessStatsFromXML function:<br>");
		}

		if(strpos(strtolower($XMLString), "l4dstats") == 1)
		{
			$xml = @simplexml_load_string($XMLString);

			$XMLArray = array();

			if ($xml)
			{
				if($Debug == true)
				{
					print("Valid XML!");
				}
				$XMLArray = $this->XMLToArray($xml);
			}
			else
			{
				if($Debug == true)
				{
					print("Invalid XML!");
				}
				$XMLArray = false;
				//print($XMLString);
				print("<L4DStats><ERR>Incorrect XML</ERR></L4DStats>");
				return;
			}


			//First loop is for each Game (incase there are multiple)
			foreach($XMLArray as $Game)
			{
				$Map = $Game['MAP'];
				$Kills = $Game['KILLS'];
				$LogFilePrefix = $Game['LOGPREFIX'];
				$Players = unserialize($Game['PLAYERS']);
				$ServerID = $Game['SERVERID'];
				$PreProcessedStats = $Game['STATS'];
				$Stats = array();

				foreach($PreProcessedStats as $StatContainter)
				{
					foreach($StatContainter as $Stat)
					{

						$Stats[] = array('GamerName' => $Players[$Stat['STEAMID']],
										'SteamID' => $Stat['STEAMID'],
										'AreaID' => $Stat['AREAID'],
										'Killed' => $Stat['KILLED'],
										'Weapon' => $Stat['WEAPON'],
										'HeadShot' => $Stat['HEADSHOT']);
					}
				}

				if($Debug == true)
				{
					print("Processing Stats for Game $LogFilePrefix on map $Map where there were $Kills kills by ".count($Players)." players on server $ServerID!<br>");
					print("<pre>");
					print_r($Players);
					print("<br><br>");
					print_r($Stats);
					print("</pre>");
				}

				//Now to process stats like normal
				$this->ProcessStats($Stats,$Kills,$Map,$LogFilePrefix,$ServerID);
			}

		}
		else
		{
			print("<L4DStats><ERR>Incorrect XML</ERR></L4DStats>");
		}
	}

	/**
    * XMLToArray
    *
    * Simply takes an XML object and turns it into Arrays
    */
	function XMLToArray($xml)
	{

		if (!($xml->children()))
		{
			return (string) $xml;
		}

		foreach ($xml->children() as $child)
		{
			$name=$child->getName();

			if (count($xml->$name)==1)
			{
				$element[$name] = $this->XMLToArray($child);
			} else
			{
				$element[][$name] = $this->XMLToArray($child);
			}
		}

		return $element;
	}

	/**
    * ProcessStats
    *
    * The *uber* function that takes a bunch of arrays and then puts them in the DB
    */
	function ProcessStats($Stats,$Kills,$Map,$LogFilePrefix,$ServerID=0)
	{
		//$Debug =true;
		$Players = array();

		$Date = explode("_",$LogFilePrefix);
		$Date = $Date[5];

		if($Debug == true)
		{
			print("Processing Stats for Game $LogFilePrefix on map $Map where there were $Kills kills!<br>");
		}

		//Get an array contain the names and steam ID's of the players
		foreach($Stats as $Stat)
		{
			$Players[$Stat['SteamID']] = $this->CleanSQL($Stat['GamerName']);
		}

		//Now check if they exist in the DB yet
		foreach($Players as $SteamID => $GamerName)
		{
			if(!isset($SteamID) || $SteamID == "")
			{
				$SteamID = "BOT-".$GamerName;
			}

			if($GamerName  == "trigger_hurt" || 
			$GamerName  == "env_fire" || 
			$GamerName == "trigger_hurt_ghost" || 
			$GamerName  == "Reset" || 
			$GamerName == "prop_door_rotating_checkpoint")
			{
				//do nothing
			}
			else
			{
				$PlayerIDQuery = "select PlayerID from Player where SteamID = '".$SteamID."'";
				$PlayerIDQueryResult = mysql_query($PlayerIDQuery);
				$ReturnField = mysql_fetch_assoc($PlayerIDQueryResult);

				if(isset($ReturnField['PlayerID']))
				{
					if($Debug == true)
					{
						print("Player Matched: $GamerName - " . $ReturnField['PlayerID']."<br>");
					}
					$DBPlayers[htmlentities($GamerName,ENT_QUOTES)] = $ReturnField['PlayerID'];
					$SteamLookup[$SteamID] = $ReturnField['PlayerID'];
				}
				else
				{
					$InsertPlayerIDQuery = "insert into Player (PlayerName,SteamID) VALUES('".$GamerName."', '".$SteamID."')";

					mysql_query($InsertPlayerIDQuery);

					if($Debug == true)
					{
						print("Player Not matched - inserting: $InsertPlayerIDQuery<br>");
					}
					$PlayerID = mysql_insert_id();
					$DBPlayers[htmlentities($GamerName,ENT_QUOTES)] = $PlayerID;
					$SteamLookup[$SteamID] = $PlayerID;
				}
			}
		}


		$GameInsertQuery = "insert into Game (LogFile, Players, Date, Kills, Map, ServerID) VALUES ('".$LogFilePrefix."', '".serialize($DBPlayers)."','".$this->FormatLogFileToDateTime($Date)."',$Kills,'$Map',$ServerID)";

		if($Debug == true)
		{
			print("Running insert: $GameInsertQuery<br>");
		}
		if(count($Players) == 0)
		{
			return;
		}
		else
		{
			mysql_query($GameInsertQuery);

			$GameID = mysql_insert_id();

			$StatInsertQuery = "insert into Statistics (GameID,PlayerID,AreaID,EntityKilled,Weapon,Headshot) VALUES ";
			$StatCount = count($Stats);
			$i = 0;
			//GamerName SteamID AreaID Killed Weapon HeadShot
			foreach ($Stats as $Stat)
			{
				/*if($Stat['Weapon']  == "trigger_hurt" || $Stat['Weapon']  == "env_fire" || $Stat['Weapon'] == "trigger_hurt_ghost" || $Stat['Weapon']  == "Reset" || $Stat['Weapon'] == "prop_door_rotating_checkpoint" || $Stat['Weapon'] == "entityflame" || $Stat['Weapon'] == "pain_pills" || $Stat['Weapon'] == "world" || $Stat['Weapon'] == "worldspawn" || $Stat['Weapon'] == "first_aid_kit")
				{
				$i++;
				break;
				}*/

				if($Stat['HeadShot'] == 1 || $Stat['HeadShot'] == "1")
				{
					$HeadShot = 1;
				}
				else
				{
					$HeadShot = 0;
				}

				if($Stat['AreaID'] == "")
				{
					$Stat['AreaID'] = 0;
				}

				if($Debug == true)
				{
					print("$i iteration of $StatCount<br>");
				}

				if($i == $StatCount -1)
				{
					$StatInsertQuery .= "(".$GameID.",'".$this->CleanSQL($SteamLookup[$Stat['SteamID']])."',".$Stat['AreaID'].",'".$this->GetPlayerIDFromName($Stat['Killed'])."','".$Stat['Weapon']."',".$HeadShot.")";
				}
				else
				{
					$StatInsertQuery .= "(".$GameID.",'".$this->CleanSQL($SteamLookup[$Stat['SteamID']])."',".$Stat['AreaID'].",'".$this->GetPlayerIDFromName($Stat['Killed'])."','".$Stat['Weapon']."',".$HeadShot."),\r\n";
				}

				$i++;
			}

			if($Debug == true)
			{
				print("<br>$StatInsertQuery<br><br>");
				print("Inserting into the DB<br>");
			}


			mysql_query($StatInsertQuery);

			if($Debug == true)
			{
				print("<h2>STATS PROCESSING COMPLETE</h2>");
			}
		}
	}

	//---------------------------------------------------------------------------------------------------------
	// Display / Format Functions
	//---------------------------------------------------------------------------------------------------------

	/**
    * PrintKillCount
    *
    * This just dumbly prints out a kill count
    */
	function PrintKillCount($KillCount)
	{
		$InfectedKillCount = 0;

		if(count($KillCount) < 1)
		{
			//print("Not enough stats for correct processing");
		}
		else
		{
			foreach($KillCount as $GamerName => $KillCount)
			{
				if($GamerName == "infected")
				{
					$InfectedKillCount = $KillCount;
					break;
				}

				print("$GamerName killed $KillCount of the infected<br/>");
			}

			print("And the infected killed $InfectedKillCount of their own!");
		}
	}


	/**
    * PrintKillTypes
    *
    * This just dumbly prints out a kill type
    */
	function PrintKillTypes($KillTypes,$LogFileName)
	{
		foreach($KillTypes as $GamerName => $InnerArray)
		{
			print($GamerName . ":<br>");

			foreach ($InnerArray as $Weapon => $KillCount)
			{
				if($Weapon == "")
				{
					print("&nbsp;&nbsp;&nbsp;Unknown - " . $KillCount ."<br>");
				}
				else
				{
					print("&nbsp;&nbsp;&nbsp;" . $Weapon . " - " . $KillCount ."<br>");
				}
			}
		}
	}

	/**
    * ASCIIFIPlayerName
    *
    * This is just a crap way to prevent players with names that break javascript from breaking
    * the graphs
    */
	function ASCIIFIPlayerName($PlayerName)
	{
		$PlayerName = str_replace("'"," ",$PlayerName);
		$PlayerName = str_replace("<","&#60;",$PlayerName);
		$PlayerName = str_replace(">","&#62;",$PlayerName);
		return $PlayerName;
	}
	
	/**
    * ASCIIFIPlayerName
    *
    * Formats the array from GetTopGames to be Smarty Friendly
    */
	function FormatTopGames($TopGames)
	{
		$ArraySize = count($TopGames);
		$PercentGames = array();

		foreach($TopGames as $GameID => $Kills)
		{
			//$SortedGames = array();
			$Percent = $Kills / $ArraySize+0;
			$Percent = $Percent * 10;
			$Percent = round($Percent);


			$PercentGames[] = array('GameDesc' => $this->FormatGameTitleFromID($GameID),
			'GameID' => $GameID,
			'TotalKills' => $Kills,
			'ScorePercent' => $Percent);

		}

		return $PercentGames;

	}

	/**
    * FormatGameTitleFromID
    *
    * Gets the Game Title from the ID and formats it pretty like
    * TODO: Use the 'Movie' Name instead of the MapName
    */
	function FormatGameTitleFromID($GameID)
	{
		$GameID = $this->CleanSQL($GameID);
		$GameQuery = "select * from Game where GameID = $GameID";
		$GameQueryResult = mysql_query($GameQuery);

		$GameReturnResult = mysql_fetch_assoc($GameQueryResult);

		return $GameReturnResult['Date'] . " " . $this->FormatMapNames($GameReturnResult['Map']);
	}

	/**
    * FormatLogFileToDateTime
    *
    * There was a reason for this a long time ago but I don't know what it was
    */
	function FormatLogFileToDateTime($Log)
	{
		return substr($Log,0,4)."/".substr($Log,4,2)."/".substr($Log,6,2)." ".substr($Log,8,2).":".substr($Log,10,2);
	}

	/**
    * FormatLogFileToDateTime
    *
    * Formats the output from GetTopPlayers so its Smarty Friendly
    */
	function FormatTopPlayers($TopPlayers)
	{
		$ArraySize = count($TopPlayers);
		$Headshots = $this->GetPlayerHeadshots();
		$FormattedPlayers = array();
		
		foreach($TopPlayers as $Player)
		{
			//PlayerName/ SteamID / KillCount

			//$SortedPlayers = array();
			/*$Percent = $Player['KillCount'] / $ArraySize;
			$Percent = round($Percent);*/

			if(isset($Headshots[$Player['PlayerID']]))
			{
				$HeadShot = $Headshots[$Player['PlayerID']];
			}
			else 
			{
				$HeadShot = "0";
			}
			
			$FormattedPlayers[] = array('PlayerName' => $Player['PlayerName'],
									  'PlayerID' => $Player['PlayerID'],
									  'TotalKills' => $Player['KillCount'],
									   'HeadShots' => $HeadShot);
		}

		return $FormattedPlayers;

	}
	
	/**
    * FormatBotNames
    *
    * Prevents the bot names from getting duplicated / messed up with multiple bots during a game
    */
	function FormatBotNames($Name)
	{
		switch($Name)
		{
			case "infected":
			case "Infected":
			case "(1)infected":
			case "(1)Infected":
				{
					$Name = "infected";
				}
				break;
				
			case "Tank":
			case "(1)Tank":
			case "(2)Tank":
			case "(3)Tank":
			case "(4)Tank":
				{
					$Name = "Tank";
				}
				break;
				
			default:
				{
					//do nothing
				}
				break;
			
		}
		
		return $Name;
	}

	/**
    * FormatMapNames
    *
    * Takes the map file name and formats it to make it something more human friendly
    */
	function FormatMapNames($Map)
	{
		switch($Map)
		{
			case "l4d_vs_hospital05_rooftop":
				{
					return "No Mercy - Rooftop (Verses)";
				}
				break;
			case "l4d_vs_hospital04_interior":
				{
					return "No Mercy - Interior (Verses)";
				}
				break;
			case "l4d_vs_hospital03_sewers":
				{
					return "No Mercy - Sewers (Verses)";
				}
				break;
			case "l4d_vs_hospital02_subway":
				{
					return "No Mercy - Subway (Verses)";
				}
				break;
			case "l4d_vs_hospital01_apartment":
				{
					return "No Mercy - Apartments (Verses)";
				}
				break;
				
			case "l4d_vs_hospital01_apartment_movie":
				{
					return "No Mercy [Entire Movie] (Verses)";
				}
				break;
				
				
				
			case "l4d_smalltown05_houseboat":
				{
					return "Death Toll - Houseboat";
				}
				break;
			case "l4d_smalltown04_mainstreet":
				{
					return "Death Toll - Main Street";
				}
				break;
			case "l4d_smalltown03_ranchhouse":
				{
					return "Death Toll - Ranchhouse";
				}
				break;
			case "l4d_smalltown02_drainage":
				{
					return "Death Toll - Drainage";
				}
				break;
			case "l4d_smalltown01_caves":
				{
					return "Death Toll - Caves";
				}
				break;
				
			case "l4d_smalltown01_caves_movie":
				{
					return "Death Toll - [Entire Movie]";
				}
				break;
				
				
				
			case "l4d_hospital05_rooftop":
				{
					return "No Mercy - Rooftop";
				}
				break;
			case "l4d_hospital04_interior":
				{
					return "No Mercy - Interior";
				}
				break;
			case "l4d_hospital03_sewers":
				{
					return "No Mercy - Sewers";
				}
				break;
			case "l4d_hospital02_subway":
				{
					return "No Mercy - Subway";
				}
				break;
			case "l4d_hospital01_apartment":
				{
					return "No Mercy - Apartment";
				}
				break;
				
			case "l4d_hospital01_apartment_movie":
				{
					return "No Mercy [Entire Movie]";
				}
				break;
				
				
				
				
			case "l4d_farm05_cornfield":
				{
					return "Blood Harvest - Cornfield";
				}
				break;
			case "l4d_farm04_barn":
				{
					return "Blood Harvest - Barn";
				}
				break;
			case "l4d_farm03_bridge":
				{
					return "Blood Harvest - Bridge";
				}
				break;
			case "l4d_farm02_traintunnel":
				{
					return "Blood Harvest - Train Tunnel";
				}
				break;
			case "l4d_farm01_hilltop":
				{
					return "Blood Harvest - Hilltop (Verses)";
				}
				
				
			case "l4d_farm01_hilltop_movie":
				{
					return "Blood Harvest [Entire Movie] (Verses)";
				}
				break;
				
				
				
			case "l4d_vs_farm05_cornfield":
				{
					return "Blood Harvest - Cornfield (Verses)";
				}
				break;
			case "l4d_vs_farm04_barn":
				{
					return "Blood Harvest - Barn (Verses)";
				}
				break;
			case "l4d_vs_farm03_bridge":
				{
					return "Blood Harvest - Bridge (Verses)";
				}
				break;
			case "l4d_vs_farm02_traintunnel":
				{
					return "Blood Harvest - Train Tunnel (Verses)";
				}
				break;
			case "l4d_vs_farm01_hilltop":
				{
					return "Blood Harvest - Hilltop (Verses)";
				}
				break;	
				
			case "l4d_vs_farm01_hilltop_movie":
				{
					return "Blood Harvest - [Entire Movie] (Verses)";
				}
				break;	
				
				
				
			case "l4d_airport05_runway":
				{
					return "Dead Air - Runway";
				}
				break;
			case "l4d_airport04_terminal":
				{
					return "Dead Air - Terminal";
				}
				break;
			case "l4d_airport03_garage":
				{
					return "Dead Air - Garage";
				}
				break;
			case "l4d_airport02_offices":
				{
					return "Dead Air - Offices";
				}
				break;
			case "l4d_airport01_greenhouse":
				{
					return "Dead Air - Greenhouse";
				}
				break;
				
			case "l4d_airport01_greenhouse_movie":
				{
					return "Dead Air - [Entire Movie]";
				}
				break;
				
				
			default:
				{
					return "Unknown Map";
				}
				break;
		}
	}

	/**
    * FormatMapNames
    *
    * Takes the weapon name and formats it to make it something more human friendly
    */
	function FormatWeaponNames($WeaponName)
	{
		switch($WeaponName)
		{
			case "infected":
				{
					$WeaponFriendlyName = "The Infected";
				}
				break;
			case "rifle":
				{
					$WeaponFriendlyName = "Assault Rifle";
				}
				break;
			case "dual_pistols":
				{
					$WeaponFriendlyName = "Dual Pistols";
				}
				break;
			case "autoshotgun":
				{
					$WeaponFriendlyName = "Auto Shotgun";
				}
				break;
			case "prop_minigun":
				{
					$WeaponFriendlyName = "MiniGun Turret";
				}
				break;
			case "pipe_bomb":
				{
					$WeaponFriendlyName = "A Pipe Bomb";
				}
				break;
			case "inferno":
				{
					$WeaponFriendlyName = "Fire!!";
				}
				break;
			case "hunting_rifle":
				{
					$WeaponFriendlyName = "Sniper Rifle";
				}
				break;
			case "tank_claw":
				{
					$WeaponFriendlyName = "Tanks Claws";
				}
				break;
			case "pumpshotgun":
				{
					$WeaponFriendlyName = "Pump Shotgun";
				}
				break;
			case "smg":
				{
					$WeaponFriendlyName = "Uzi";
				}
				break;
			case "env_explosion":
				{
					$WeaponFriendlyName = "An Explosion";
				}
				break;
			case "tank_rock":
				{
					$WeaponFriendlyName = "Rock thrown by Tank";
				}
				break;
			case "pistol":
				{
					$WeaponFriendlyName = "Pistol";
				}
				break;
			case "hunter_claw":
				{
					$WeaponFriendlyName = "The Hunter";
				}
				break;
			case "molotov":
				{
					$WeaponFriendlyName = "A Molotov";
				}
				break;
			case "witch":
				{
					$WeaponFriendlyName = "The Witch";
				}
				break;
			case "propanetank":
				{
					$WeaponFriendlyName = "A Propane Tank";
				}
				break;
			case "oxygentank":
				{
					$WeaponFriendlyName = "An Oxygen Tank";
				}
				break;
			case "smoker_claw":
				{
					$WeaponFriendlyName = "A Smoker";
				}
				break;
			case "boomer_claw":
				{
					$WeaponFriendlyName = "Boomers Claws";
				}
				break;
			case "boomer":
				{
					$WeaponFriendlyName = "Boomer Bile";
				}
				break;
			case "gascan":
				{
					$WeaponFriendlyName = "A Gas Can";
				}
				break;
			case "pain_pills":
				{
					$WeaponFriendlyName = "Melee with Pills";
				}
				break;
			case "first_aid_kit":
				{
					$WeaponFriendlyName = "1st Aid Melee";
				}
				break;
			default:
				{
					$WeaponFriendlyName = $WeaponName;
				}
				break;
		}
		
		return $WeaponFriendlyName;
	}
	
	/**
    * FormatMovieNameFromMapMatch
    *
    * Takes the substr'd map name and formats it to show just the movie name
    */
	function FormatMovieNameFromMapMatch($MapMatch)
	{
		switch ($MapMatch)
		{
			case "l4d_airp":
				{
					$MovieName = "Left4Dead - Dead Air";
				}
				break;
				
			case "l4d_smal":
				{
					$MovieName = "Left4Dead - Death Toll";
				}
				break;
				
			case "l4d_farm":
				{
					$MovieName = "Left4Dead - Blood Harvest";
				}
				break;
			
			case "l4d_hosp":
				{
					$MovieName = "Left4Dead - No Mercy";
				}
				break;
				
			case "l4d_vs_a":
				{
					$MovieName = "Left4Dead - Dead Air (Verses)";
				}
				break;
				
			case "l4d_vs_s":
				{
					$MovieName = "Left4Dead - Death Toll (Verses)";
				}
				break;
				
			case "l4d_vs_f":
				{
					$MovieName = "Left4Dead - Blood Harvest (Verses)";
				}
				break;
			
			case "l4d_vs_h":
				{
					$MovieName = "Left4Dead - No Mercy (Verses)";
				}
				break;
				
			default:
				{
					$MovieName = "Unknown Movie";
				}
				break;
		}
		
		return $MovieName;
	}
	
	//---------------------------------------------------------------------------------------------------------
	// Getter Functions
	//---------------------------------------------------------------------------------------------------------
	
	/**
    * GetPlayerIDFromName
    *
    * Gets a Players L4DStats ID from their name
    */
	function GetPlayerIDFromName($Name)
	{
		$Name = $this->CleanSQL($Name);
		$PlayerIDQuery = "select PlayerID from Player where PlayerName = '".$Name."'";
		$PlayerIDQueryResult = mysql_query($PlayerIDQuery);

		$ReturnField = mysql_fetch_assoc($PlayerIDQueryResult);

		return $this->CleanSQL($ReturnField['PlayerID']);
	}

	/**
    * GetPlayerNameFromSteamID
    *
    * Gets a Players L4DStats Name from their SteamID
    */
	function GetPlayerNameFromSteamID($SteamID)
	{
		$SteamID = $this->CleanSQL($SteamID);
		$PlayerNameQuery = "select PlayerName from Player where SteamID = '".$SteamID."'";
		$PlayerNameQueryResult = mysql_query($PlayerNameQuery);

		$ReturnField = mysql_fetch_assoc($PlayerNameQueryResult);

		return $this->CleanSQL($ReturnField['PlayerName']);
	}

	/**
    * GetRandomStat
    *
    * This is for the smaller subtitle that has a random stat
    */
	function GetRandomStat()
	{
		switch(rand(0,4))
		{
			case 0:
				{
					return "Total Kills: ". $this->GetTotalKills();
				}
				break;

			case 1:
				{
					return "Total Rifle Kills: ". $this->GetTotalKills("rifle");
				}
				break;

			case 2:
				{
					return "Total SMG Kills: ". $this->GetTotalKills("smg");
				}
				break;

			case 3:
				{
					return "Total Auto Shotgun Kills: ". $this->GetTotalKills("autoshotgun");
				}
				break;

			default:
				{
					return "Total Infected killed: ". $this->GetTotalKills();
				}
				break;
		}
	}

	/**
    * GetTotalKills
    *
    * Returns either the total amount of kills for the server or total amount of kills per weapon
    */
	function GetTotalKills($Weapon = "none")
	{
		if($Weapon != "none")
		{
			$Query = "select count(1) from Statistics where Weapon = '$Weapon'";
		}
		else
		{
			$Query = "select count(1) from Statistics";
		}

		$result = mysql_query($Query);
		$ReturnField = mysql_fetch_assoc($result);
		$Kills = $ReturnField['count(1)'];

		return $Kills;
	}

	/**
    * GetTopGames
    *
    * Returns a list of top x to x+50 games
    */
	function GetTopGames($Page)
	{
		$Limit = $Page * 50;
		$GamesQuery = "select * from Game order by Kills DESC limit $Limit,50";
		$GamesQueryResult = mysql_query($GamesQuery);
		$Games = array();

		while($DistinctGameRow = mysql_fetch_assoc($GamesQueryResult))
		{
			//$LogFile = $DistinctGameRow['LogFile'];
			//$Date = $DistinctGameRow['Date'];
			$Kills = $DistinctGameRow['Kills'];
			//$Map = $DistinctGameRow['Map'];
			$GameID = $DistinctGameRow['GameID'];

			$Games[$GameID] = $Kills;

		}

		return $Games;

	}

	/**
    * GetTitleForIndivGame
    *
    * Gets the friendly map name for a particular game
    * I'm not sure why the call to FormatMapNames isn't made from within the IndivGame page....
    */
	function GetTitleForIndivGame($GameID)
	{
		$GameID = $this->CleanSQL($GameID);
		$Query = "select Map,Date from Game where GameID = $GameID";
		$result = mysql_query($Query);
		$ReturnField = mysql_fetch_assoc($result);
		return $this->FormatMapNames($ReturnField['Map'])." | ".$ReturnField['Date'];
	}

	/**
    * GetMapImageFromGameID
    *
    * Gets the relevent movie image from a GameID 
    */
	function GetMapImageFromGameID($GameID)
	{
		$GameID = $this->CleanSQL($GameID);
		$Query = "select Map from Game where GameID = $GameID";
		$result = mysql_query($Query);
		$ReturnField = mysql_fetch_assoc($result);

		$MapParts = explode("_",$ReturnField['Map']);
		if($MapParts[1] == "vs")
		{
			return substr($MapParts[2],0,strlen($MapParts[2])-2);
		}
		else
		{
			return substr($MapParts[1],0,strlen($MapParts[1])-2);
		}
	}

	/**
    * GetMapNameFromGameID
    *
    * Works out the friendly map name from the GameID
    */
	function GetMapNameFromGameID($GameID)
	{
		$GameID = $this->CleanSQL($GameID);
		$Query = "select Map from Game where GameID = $GameID";
		$result = mysql_query($Query);
		$ReturnField = mysql_fetch_assoc($result);

		return $this->FormatMapNames($ReturnField['Map']);
	}

	/**
    * GetMapDateFromGameID
    *
    * Works out the map date from the GameID
    */
	function GetMapDateFromGameID($GameID)
	{
		$GameID = $this->CleanSQL($GameID);
		$Query = "select Date from Game where GameID = $GameID";
		$result = mysql_query($Query);
		$ReturnField = mysql_fetch_assoc($result);

		return $ReturnField['Date'];
	}

	/**
    * GetLogFromID
    *
    * Gets the LogPrefix from a GameID - I've no idea why this is here
    */
	function GetLogFromID($GameID)
	{
		$GameID = $this->CleanSQL($GameID);
		$Query = "select LogFile from Game where GameID = $GameID";
		$result = mysql_query($Query);
		$ReturnField = mysql_fetch_assoc($result);

		if(!isset($ReturnField['LogFile']))
		{
			return "000000000000";
		}
		else
		{
			return $ReturnField['LogFile'];
		}

	}

	/**
    * GetTopWeapons
    *
    * Gets the top weapons for this server
    */
	function GetTopWeapons()
	{
		$HeadShots = $this->GetWeaponHeadshotCounts();
		
		$StatsQuery = "select count(1) as Kills,Weapon from Statistics group by Weapon order by Kills DESC";
		$StatsResult = mysql_query($StatsQuery);
		$WeaponStats = array();

		while($StatsRow = mysql_fetch_assoc($StatsResult))
		{
			if(isset($HeadShots[$StatsRow['Weapon']]))
			{
				$HeadShot = "[".$HeadShots[$StatsRow['Weapon']]."]";
			}
			else 
			{
				$HeadShot = "";
			}
			
			$ReturnArray[] = array('Weapon' => $StatsRow['Weapon'],
									'Kills' => $StatsRow['Kills'],
									'HeadShots' => $HeadShot);
		}

		return $ReturnArray;
	}
	
	/**
    * GetTopWeapons
    *
    * Gets the top Movies for this server
    */
	function GetTopMovies()
	{
		$Query = "select distinct(substring(Map,1,8)) as MapMatch from Game";

		$result = mysql_query($Query);

		while($StatsRow = mysql_fetch_assoc($result))
		{
			$SubQuery = "select SUM(Kills) as Kills from Game where Map like \"". $StatsRow['MapMatch'] ."%\"";
			$SubResult = mysql_query($SubQuery);
			$MovieKills = mysql_fetch_assoc($SubResult);
			
			$Movies[$StatsRow['MapMatch']] = $MovieKills['Kills'];	
		}
		
		arsort($Movies);
		
		return $Movies;
	}
	
	/**
    * GetTopWeaponsXLastGames
    *
    * Gets the top weapons from the last X amount of games
    */
	function GetTopWeaponsXLastGames($GamesCount)
	{
		$Query = "select GameID from Game order by Date Desc limit $GamesCount";

		$result = mysql_query($Query);
		
		$Games = 0;
		$Weapons = array();
		$GameIDs = array();

		while($StatsRow = mysql_fetch_assoc($result))
		{
			$GameIDs[] = $StatsRow['GameID'];
			$Games++;
		}

		$WeaponCountQuery = "select Weapon,count(1) as Kills from Statistics where ";

		foreach($GameIDs as $GameID)
		{
			if($i == $Games -1)
			{
				$WeaponCountQuery .= "GameID = $GameID";
			}
			else
			{
				$WeaponCountQuery .= "GameID = $GameID OR ";
			}
			$i++;
		}
		$WeaponCountQuery .= " group by Weapon order by Kills Desc";
		$WeaponStatsResult = mysql_query($WeaponCountQuery);

		while($WeaponStatsRow = mysql_fetch_assoc($WeaponStatsResult))
		{
			if($WeaponStatsRow['Kills'] > 50)
			{	
			$Weapons[$this->FormatWeaponNames($WeaponStatsRow['Weapon'])] = $WeaponStatsRow['Kills'];
			}
		}
		
		return $Weapons;
	}

	/**
    * GetTopWeaponsIndivGame
    *
    * Gets the top weapons from an individual game
    */
	function GetTopWeaponsIndivGame($GameID,$Show_AI_Stats)
	{
		$GameID = $this->CleanSQL($GameID);
		if($Show_AI_Stats == true)
		{
			$StatsQuery = "select count(1) as Kills,Weapon from Statistics where GameID = $GameID group by Weapon order by Kills DESC";
		}
		else 
		{
			$StatsQuery = "select count(1) as Kills,Weapon from Statistics where GameID = $GameID and PlayerID not in (select PlayerID from Player where SteamID like \"BO%\") group by Weapon order by Kills DESC";
		}
		
		$StatsResult = mysql_query($StatsQuery);
		$WeaponStats = array();

		while($StatsRow = mysql_fetch_assoc($StatsResult))
		{
			$ReturnArray[$this->FormatWeaponNames($StatsRow['Weapon'])] =  $StatsRow['Kills'];
		}

		return $ReturnArray;
	}

	/**
    * GetAreaDistributionIndivGame
    *
    * This is for the planned heat map stuff
    */
	function GetAreaDistributionIndivGame($GameID)
	{
		$GameID = $this->CleanSQL($GameID);
		
		$StatsQuery = "select distinct(AreaID) from Statistics where GameID = $GameID";

		$StatsResult = mysql_query($StatsQuery);
		$AreaID = array();

		while($StatsRow = mysql_fetch_assoc($StatsResult))
		{
			$AreaID[] = $StatsRow['AreaID'];

		}

		return $AreaID;
	}

	/**
    * GetKillsLastXGames
    *
    * Simply returns a count of the kills from the last X games
    */
	function GetKillsLastXGames($X)
	{
		$StatsQuery = "select Kills from Game order by Date Desc limit $X";

		$StatsResult = mysql_query($StatsQuery);

		while($StatsRow = mysql_fetch_assoc($StatsResult))
		{
			$Kills[] = $StatsRow['Kills'];
		}

		return $Kills;
	}
	
	/**
    * GetIndivPlayerKillsOverTime
    *
    * Simply returns a count of the kills a player has acheived over their 'career' on this server
    */
	function GetIndivPlayerKillsOverTime($PlayerID)
	{
		$StatsQuery = "select GameID,count(1) as Kills from Statistics where PlayerID = $PlayerID group by GameID";

		$StatsResult = mysql_query($StatsQuery);

		while($StatsRow = mysql_fetch_assoc($StatsResult))
		{
			$Kills[] = $StatsRow['Kills'];
		}

		return $Kills;
	}
	
	/**
    * GetIndivKillsPerMapGraph
    *
    * Simply returns a count of the kills a player has acheived over their 'career' on this server [per map]
    */
	function GetIndivKillsPerMapGraph($PlayerID)
	{
		$StatsQuery = "select Game.Map,count(1) as Kills from Statistics inner join Game on Statistics.GameID = Game.GameID where Statistics.PlayerID = $PlayerID group by Statistics.GameID";

		$StatsResult = mysql_query($StatsQuery);

		while($StatsRow = mysql_fetch_assoc($StatsResult))
		{
			$Kills[$this->FormatMapNames($StatsRow['Map'])] = $StatsRow['Kills'];
		}

		return $Kills;
	}
	
	/**
    * GetIndivKillsPerMapGraph
    *
    * Simply returns a count of the kills a player has acheived over their 'career' on this server [per map]
    */
	function GetBestPlayerMovies($PlayerID)
	{
		//$Query = "select distinct(substring(Map,1,8)) as MapMatch from Game inner join Statistics on Statistics.GameID = Game.GameID where Game.GameID in (select distinct(GameID) from Statistics where PlayerID = $PlayerID)";
		$Query = "select count(1) as Kills,substring(Map,1,8) as MapMatch from Statistics inner join Game on Statistics.GameID = Game.GameID where PlayerID = $PlayerID group by MapMatch";
		$result = mysql_query($Query);

		while($StatsRow = mysql_fetch_assoc($result))
		{
			/*$SubQuery = "select count(1) as Kills,substring(Map,1,8) as MapMatch from Statistics inner join Game on Statistics.GameID = Game.GameID where PlayerID = $ group by MapMatch";
			$SubResult = mysql_query($SubQuery);
			$MovieKills = mysql_fetch_assoc($SubResult);*/
			
			$Movies[$this->FormatMovieNameFromMapMatch($StatsRow['MapMatch'])] = $StatsRow['Kills'];	
		}
		
		arsort($Movies);
		
		return $Movies;
	}
	
	/**
    * GetBestFellowSurvivors
    *
    * Returns an array containing a players best fellow survivors
    * TODO: Infected can have best fellow survivors :\
    */
	function GetBestFellowSurvivors($PlayerID,$PlayerName)
	{
		$Query = "select distinct(PlayerID) from Statistics where GameID = (select Game.GameID from Statistics inner join Game on Statistics.GameID = Game.GameID where PlayerID = $PlayerID group by GameID order by Game.Kills DESC limit 0,1) limit 0,5";
		
		$Result = mysql_query($Query);
		$OtherSurvivors = array();
		$BestFellowSurvivors = array();
		
		while($StatsRow = mysql_fetch_assoc($Result))
		{
			if($StatsRow['PlayerID'] != $PlayerID)
			{
				$OtherSurvivorsName = $this->GetPlayerName($StatsRow['PlayerID']);
				
				if($OtherSurvivorsName == "infected" ||
					$OtherSurvivorsName == "witch" ||
					$OtherSurvivorsName == "Hunter" ||
					$OtherSurvivorsName == "Tank" ||
					$OtherSurvivorsName == "Smoker" ||
					$OtherSurvivorsName == "env_explosion" ||
					$OtherSurvivorsName == "Boomer")
					{
						//Eventually we'll check to see if the player was playing as an infected
					}
					else 
					{
						$OtherSurvivors[$StatsRow['PlayerID']] = "$OtherSurvivorsName<br>";
					}
			}
			
		}
		
		if(!empty($OtherSurvivors))
		{
			foreach ($OtherSurvivors as $SurvivorID => $SurvivorName)
			{
				$BestFellowSurvivors[] = array('SurvivorID' => $SurvivorID,'SurvivorName' => $SurvivorName);
			}
		}
		
						
		return $BestFellowSurvivors;
	}
	
	/**
    * GetPlayersEnemies
    *
    * Returns an array containing a players worst enemies and the weapons they were killed with
    */
	function GetPlayersEnemies($PlayerID)
	{
		$Query = "select count(1) as Kills,Player.PlayerID,Player.PlayerName,GROUP_CONCAT(distinct(Weapon)) as Weapons from Statistics inner join Player on Player.PlayerID = Statistics.PlayerID where EntityKilled = $PlayerID  group by Player.PlayerName order by Kills Desc limit 0,10";
		$Result = mysql_query($Query);
		$PlayersWorstEnemies = array();
		
		if(mysql_num_rows($Result) > 0)
		{
			while($StatsRow = mysql_fetch_assoc($Result))
			{	
				$Weapons = "";
				foreach (explode(",",$StatsRow['Weapons']) as $WeaponName)
				{
					$Weapons .= $this->FormatWeaponNames($WeaponName)."<br>";
				}
				$PlayersWorstEnemies[] = array('EnemyName' => $StatsRow['PlayerName'],
											   'Deaths' => $StatsRow['Kills'],
											   'Weapons' => $Weapons,
											   'EnemyID' => $StatsRow['PlayerID']);
			}
		}
		return $PlayersWorstEnemies;
	}

	/**
    * GetHeadShotRatio
    *
    * Gets the headshot ratio for an individual game
    */
	function GetHeadShotRatio($GameID)
	{
		$GameID = $this->CleanSQL($GameID);
		$HeadShotQuery = "SELECT (SELECT count(1) FROM Statistics where GameID = $GameID) AS 'TotalKills',(SELECT count(1) FROM Statistics WHERE GameID = $GameID and Headshot = 1) AS 'HeadShots'";	
		
		$result = mysql_query($HeadShotQuery);
		$ReturnField = mysql_fetch_assoc($result);

		$HeadShotRatio['HeadShots'] = $ReturnField['HeadShots'];
		$HeadShotRatio['TotalKills'] = $ReturnField['TotalKills'];
		
		return $HeadShotRatio;
	}
	
	/**
    * GetGameStats
    *
    * Gets all the relevent game stats for a particular game
    */
	function GetGameStats($GameID,$Show_AI_Stats)
	{
		$GameID = $this->CleanSQL($GameID);
		
		if($Show_AI_Stats == true)
		{
		$StatisticsQuery = "select * from Statistics where GameID = $GameID";
		}
		else 
		{
			$StatisticsQuery = "select * from Statistics where GameID = $GameID and PlayerID not in (select PlayerID from Player where SteamID like \"BO%\")";
		}
		
		$StatisticsResult = mysql_query($StatisticsQuery);
		$TotalKills = 0;
		$PlayerStats = array();
		$WeaponStats = array();

		while($Gamerow = mysql_fetch_assoc($StatisticsResult))
		{
			//Set up the variables we'll need
			$PlayerID = $Gamerow['PlayerID'];

			$Weapon = $Gamerow['Weapon'];

			//$KilledEntity = $Gamerow['EntityKilled'];
			$GamerName = $this->GetPlayerName($PlayerID);

			//Create the 3D Player Array
			$PlayerStats[$GamerName][$Weapon] = $PlayerStats[$GamerName][$Weapon] + 1;
			$PlayerStats[$GamerName]['ID'] = $PlayerID;

			//Weapons array is only 2D
			$WeaponStats[$Weapon] = $WeaponStats[$Weapon] + 1;

			//Total Kills in 1D
			$TotalKills++;
		}

		$Return = array();
		$Return['PlayerStats'] = $PlayerStats;
		$Return['WeaponStats'] = $WeaponStats;
		$Return['TotalKills'] = $TotalKills;
		return $Return;
	}

	/**
    * GetRecentStats
    *
    * Gets a selection of recent stats (used for front page and MOTD)
    */
	function GetRecentStats($MaxDays)
	{
		$Today = date("Y-m-d");
		//$Query = "select Kills from Game where Date like '$Today%'";
		$Query = "select GameID,Kills from Game order by Date Desc limit $MaxDays";

		$result = mysql_query($Query);
		$TotalKills = 0;
		$Games = 0;
		$GameIDs = array();
		$Weapons = array();
		$Players = array();
		//$WeaponsStats = array();

		while($StatsRow = mysql_fetch_assoc($result))
		{
			$TotalKills += $StatsRow['Kills'];
			$GameIDs[] = $StatsRow['GameID'];
			$Games++;
		}

		$WeaponCountQuery = "select Weapon,count(1) as Kills from Statistics where ";
		$PlayerQuery = "select PlayerName,count(1) as Kills,Statistics.PlayerID from Statistics inner join Player on Player.PlayerID = Statistics.PlayerID where Statistics.PlayerID not in (select PlayerID from Player where SteamID like \"BO%\") and (";
		$DeathQuery = "select count(1)as Deaths from Statistics where EntityKilled not in (select PlayerID from Player where SteamID like \"BO%\") and (";
		$i=0;

		foreach($GameIDs as $GameID)
		{
			if($i == $Games -1)
			{
				$WeaponCountQuery .= "GameID = $GameID";
				$PlayerQuery .= "GameID = $GameID)";
				$DeathQuery .= "GameID = $GameID)";
			}
			else
			{
				$WeaponCountQuery .= "GameID = $GameID OR ";
				$PlayerQuery .= "GameID = $GameID OR ";
				$DeathQuery .= "GameID = $GameID OR ";
			}
			$i++;
		}
		$WeaponCountQuery .= " group by Weapon order by Kills Desc";
		$WeaponStatsResult = mysql_query($WeaponCountQuery);

		while($WeaponStatsRow = mysql_fetch_assoc($WeaponStatsResult))
		{
			$Weapons[$WeaponStatsRow['Weapon']] = $WeaponStatsRow['Kills'];
		}

		$PlayerQuery .= " group by Statistics.PlayerID order by Kills DESC limit 0,10";
		$PlayersResult = mysql_query($PlayerQuery);

		while($PlayerRow = mysql_fetch_assoc($PlayersResult))
		{
			$Players[str_replace("|","&#124;",$PlayerRow['PlayerName'])."|".$PlayerRow['PlayerID']] =  $PlayerRow['Kills'];
		}

		$DeathsResult = mysql_query($DeathQuery);
		$DeathCount = mysql_fetch_assoc($DeathsResult);

		$TodaysStats['TotalKills'] = $TotalKills;
		$TodaysStats['GameCount'] = $Games;
		$TodaysStats['Weapons'] = $Weapons;
		$TodaysStats['Players'] = $Players;
		$TodaysStats['Deaths'] = $DeathCount['Deaths'];
		return $TodaysStats;
	}

	/**
    * GetTodaysStats
    *
    * Gets just todays stats
    */
	function GetTodaysStats()
	{
		$Today = date("Y-m-d");
		//$Query = "select Kills from Game where Date like '$Today%'";
		$Query = "select GameID,Kills from Game where Date between '$Today 00:00:00' and '$Today 23:59:59'";

		$result = mysql_query($Query);
		$TotalKills = 0;
		$Games = 0;
		$GameIDs = array();
		$Weapons = array();
		//$WeaponsStats = array();

		while($StatsRow = mysql_fetch_assoc($result))
		{
			$TotalKills += $StatsRow['Kills'];
			$GameIDs[] = $StatsRow['GameID'];
			$Games++;
		}

		$WeaponCountQuery = "select Weapon from Statistics where ";
		$i=0;

		foreach($GameIDs as $GameID)
		{
			if($i == $Games -1)
			{
				$WeaponCountQuery .= "GameID = $GameID";
			}
			else
			{
				$WeaponCountQuery .= "GameID = $GameID OR ";
			}
			$i++;
		}

		$WeaponStatsResult = mysql_query($WeaponCountQuery);
		while($WeaponStatsRow = mysql_fetch_assoc($WeaponStatsResult))
		{
			$Weapons[$WeaponStatsRow['Weapon']] += 1;
		}

		$TodaysStats['TotalKills'] = $TotalKills;
		$TodaysStats['GameCount'] = $Games;
		$TodaysStats['Weapons'] = $Weapons;
		return $TodaysStats;
	}

	function GetAllKills()
	{
		return 9001;
	}

	function GetInfectedKills()
	{
		return 9001;
	}

	function GetServerCount()
	{
		return 9001;
	}

	function GetPlayerCount()
	{
		return 9001;
	}

	function GetTopMaps($DateRange="none")
	{
		return "No Mercy";
	}

	function GetPlayerBestAtWeapon($Weapon="pistol")
	{
		return "Bob";
	}

	/**
    * GetPlayerStats
    *
    * Gets all stats relating to an individual player
    */
	function GetPlayerStats($PlayerID)
	{
		$HeadShotWeapons = $this->GetPlayerHeadshotWeapons($PlayerID);
		
		$PlayerWeaponsQuery = "select count(1) as Kills,Weapon from Statistics where PlayerID = $PlayerID group by Weapon order by Kills DESC";
		$PlayerWeaponsResult = mysql_query($PlayerWeaponsQuery);
		
		while($StatsRow = mysql_fetch_assoc($PlayerWeaponsResult))
		{
			if(isset($HeadShotWeapons[$StatsRow['Weapon']]))
			{
				$HeadShot = "[".$HeadShotWeapons[$StatsRow['Weapon']]."]";
			}
			else 
			{
				$HeadShot = "";
			}
			
			$ReturnArray[] = array('Weapon' => $StatsRow['Weapon'],
									'Kills' => $StatsRow['Kills'],
									'HeadShots' => $HeadShot);
		}

		return $ReturnArray;
	}
	
	/**
    * GetPlayerHeadshotWeapons
    *
    * Gets a collection of weapons with which the player scored headshots
    */
	function GetPlayerHeadshotWeapons($PlayerID)
	{
		$PlayerHeadshotsWeaponsQuery = "select count(1) as Headshots,Weapon from Statistics where PlayerID = $PlayerID and HeadShot = 1 group by Weapon";
		$PlayerHeadshotsWeaponsResult = mysql_query($PlayerHeadshotsWeaponsQuery);
		
		while($StatsRow = mysql_fetch_assoc($PlayerHeadshotsWeaponsResult))
		{
			$ReturnArray[$StatsRow['Weapon']] = $StatsRow['Headshots'];
		}
		return $ReturnArray;
	}
	
	/**
    * GetWeaponHeadshotCounts
    *
    * Gets a collection of weapons and their respective headshot count
    */
	function GetWeaponHeadshotCounts()
	{
		$PlayerHeadshotsWeaponsQuery = "select count(1) as Headshots,Weapon from Statistics where HeadShot = 1 group by Weapon";
		$PlayerHeadshotsWeaponsResult = mysql_query($PlayerHeadshotsWeaponsQuery);
		
		while($StatsRow = mysql_fetch_assoc($PlayerHeadshotsWeaponsResult))
		{
			$ReturnArray[$StatsRow['Weapon']] = $StatsRow['Headshots'];
		}
		return $ReturnArray;
	}
	
	/**
    * GetPlayerHeadshots
    *
    * Gets a list of all players and their head shot count
    */
	function GetPlayerHeadshots()
	{
		$PlayerHeadshotsQuery = "select count(1) as Headshots,PlayerID from Statistics where HeadShot = 1 group by PlayerID";
		$PlayerHeadshotsResult = mysql_query($PlayerHeadshotsQuery);
		
		while($StatsRow = mysql_fetch_assoc($PlayerHeadshotsResult))
		{
			$ReturnArray[$StatsRow['PlayerID']] = $StatsRow['Headshots'];
		}
		return $ReturnArray;
	}

	/**
    * GetPlayerTotalKillsIndivGame
    *
    * Gets a list of players and their respective kill counts for an individual game
    */
	function GetPlayerTotalKillsIndivGame($GameID,$Show_AI_Stats)
	{
		$PlayerKillCount = array();

		if($Show_AI_Stats == true)
		{
			$Query = "select count(1) as Kills, PlayerName from Statistics inner join Player on Player.PlayerID = Statistics.PlayerID where GameID = $GameID group by Statistics.PlayerID";
		}
		else 
		{
			$Query = "select count(1) as Kills, PlayerName from Statistics inner join Player on Player.PlayerID = Statistics.PlayerID where Statistics.PlayerID not in (select PlayerID from Player where SteamID like \"BO%\") and GameID = $GameID group by Statistics.PlayerID";
		}
		$result = mysql_query($Query);
		while($PlayerRow = mysql_fetch_assoc($result))
		{
			$PlayerKillCount[$PlayerRow['PlayerName']] = $PlayerRow['Kills'];
		}
		
		return $PlayerKillCount;

	}
	
	/**
    * GetPlayerName
    *
    * Gets a players name from their L4DStats PlayerID
    */
	function GetPlayerName($PlayerID)
	{
		$Query = "select PlayerName from Player where PlayerID = $PlayerID";
		$result = mysql_query($Query);
		$ReturnField = mysql_fetch_assoc($result);

		if(isset($ReturnField['PlayerName']))
		{
			return htmlentities($ReturnField['PlayerName']);
		}
		else
		{
			return "Unknown Player";
		}
	}


	/**
    * GetTopPlayers
    *
    * Gets a list of the top players on this server
    */
	function GetTopPlayers($Show_AI_Stats)
	{
		if($Show_AI_Stats == true)
		{
			$TopPlayerQuery = "select count(1) as PlayerKills,PlayerName,Player.PlayerID as PlayerID from Statistics inner join Player on Player.PlayerID = Statistics.PlayerID group by PlayerID order by PlayerKills DESC limit 0,50";
		}
		else 
		{
			$TopPlayerQuery = "select count(1) as PlayerKills,PlayerName,Player.PlayerID as PlayerID from Statistics inner join Player on Player.PlayerID = Statistics.PlayerID where Statistics.PlayerID not in (select PlayerID from Player where SteamID like \"BO%\") group by PlayerID order by PlayerKills DESC limit 0,50 ";
			//$StatisticsQuery = "select * from Statistics where GameID = $GameID and PlayerID not in (select PlayerID from Player where SteamID like \"BO%\")";
		}
		
		$TopPlayerResult = mysql_query($TopPlayerQuery);
		
		while($PlayerRow = mysql_fetch_assoc($TopPlayerResult))
		{
			$Players[] = array('PlayerName' => $PlayerRow['PlayerName'],
			'PlayerID' => $PlayerRow['PlayerID'],
			'KillCount' => $PlayerRow['PlayerKills']);
		}

		return $Players;
	}
	
	//---------------------------------------------------------------------------------------------------------
	// Setter Functions
	//---------------------------------------------------------------------------------------------------------
	
	// This is now redundant due to the amazing new GetTopPlayers query
	function SortTopPlayers($Players)
	{
		$sort_arr = array();
		foreach($Players AS $uniqid => $row)
		{
			foreach($row AS $key=>$value)
			{
				$sort_arr[$key][$uniqid] = $value;
			}
		}

		array_multisort($sort_arr["KillCount"], constant("SORT_DESC"), $Players);

		return $Players;
	}

	//---------------------------------------------------------------------------------------------------------
	// SQL Functions
	//---------------------------------------------------------------------------------------------------------
	/**
    * CleanSQL
    *
    * Should hopefully prevent the basic "'; drop table;" type exploits
    */
	function CleanSQL( $value )
	{
		if( get_magic_quotes_gpc() )
		{
			$value = stripslashes( $value );
		}
		//check if this function exists
		if( function_exists( "mysql_real_escape_string" ) )
		{
			$value = mysql_real_escape_string( $value );
		}
		//for PHP version < 4.3.0 use addslashes
		else
		{
			$value = addslashes( $value );
		}
		return $value;
	}

	//---------------------------------------------------------------------------------------------------------
	// Dubug Functions
	//---------------------------------------------------------------------------------------------------------

	function debug_print_game_array($Stats)
	{
		print("<h2>DEBUG - GAME STATS ARRAY PRINTOUT</h2>");
		//Each Array element will contain the following:
		//GamerName, SteamID, AreaID, Killed, Weapon, HeadShot
		foreach($Stats as $Game)
		{
			print($Game['GamerName'] ." - ". $Game['Weapon'] ."<br>");
		}
	}
}
?>
