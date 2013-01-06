<?php
/*==============================================================
L4DStats - Open Source Left4Dead Stats App

Copyright (c) 2008 Gareth Llewellyn

This program is free software, distributed under the terms of
the GNU General Public License Version 2. See the LICENSE file
at the top of the source tree.

================================================================*/

/// process_logs.php
/// Iterates through the logs directory and passes the log
/// names through to the processing function in the L4DStats
/// class for parsing and insertion into the DB

include('./L4DStats.class.php');
include('./settings.php');

$L4DStats = new L4DStats();
$MoveFiles = false;

//Open up the logs directory
if ($LogDirHandle = opendir($LogDir)) 
{

	//Make a note of the map outside the while loop
	$MapName = "";

	while (false !== ($PotentialLogFile = readdir($LogDirHandle))) 
	{
		//This is because the file list is made once and not reevaluated despite us deleting files
		if(!file_exists($LogDir.$PotentialLogFile))
			continue;
			
		$validFile = false;
		
		//if ($PotentialLogFile != "." && $PotentialLogFile != ".." && $PotentialLogFile != "old" && $PotentialLogFile != "temp" && $PotentialLogFile != "logs" && $PotentialLogFile != "")
		if(stristr($PotentialLogFile,".log") === FALSE)
		{
			//File is something weird - ignore it	
			if($Debug == true)
			{
				print("<br>$PotentialLogFile does not match the *.log filter<br>");
			}
		}
		else
		{
			if($Debug == true)
			{
				print("Attempting to open $LogDir$PotentialLogFile<br>");
			}
			
			$LogFileHandle = fopen($LogDir.$PotentialLogFile, 'r');
			
			//Now loop through each line of the file parsing it as we go
			while (!feof($LogFileHandle))
			{
				//Get an entire line from the file at a time
				$LogLine = fgets($LogFileHandle);
				
				//Need to find out if this is a valid log
				if(stristr($LogLine, 'Loading map') === FALSE)
				{
					if($Debug == true)
					{
						//print("Haven't matched \"Loading Map\" string yet...<br>");
					}
				}
				else
				{
					// Set the bool to true so the next step knows
                    // we have a valid file to work with
                    $validFile = true;
                    
                    $LogLineSplit = explode("\"",$LogLine);
					$MapName = $LogLineSplit[1];

					if($Debug == true)
					{
						print("Setup Log ($MapName) - ($GameDateTime) Noted and continuing processing<br>");
					}
					
					//Break as we don't need to continue looping through the file.
					break;
					
				}

			}//End looping through the file
			
			//Close the file handle
			fclose($LogFileHandle);
			
			//Check if the file passed the validation test above
			if($validFile == true)
			{
				if($Debug == true)
				{
					print("$PotentialLogFile is a valid setup file; processing...<br>");
				}
				
				$ReturnArray = $L4DStats->ProcessLog($LogDir, $PotentialLogFile, $MapName, $Debug);
				
				if($Debug == true)
				{
					print("Total Kills:" . $ReturnArray['Kills']."<br>");
				}
				
				//Check that the returned array contains data from a closed log file
				if($ReturnArray['Closed'] == true && $ReturnArray['Kills'] > 0)
				{
					if($MapName != $ReturnArray['MapName'])
					{
						$MapName = 	$ReturnArray['MapName'];
					}
					
					$FileName = explode("_",$PotentialLogFile);
					$LogFilePrefix = $FileName[0] . "_". $FileName[1] . "_". $FileName[2] . "_". $FileName[3] . "_". $FileName[4] . "_". $FileName[5];
		
					$MoveFiles = true;
					
					//If we are going to send this off
					if($Remote_Stats_Aggregation == true)
					{
						if($Debug == true)
						{
							print("Packaging in XML for XMLRPC remote processing<br>");
						}
						
						//Send the data to the XMLRPC processing center
						$L4DStats->ProcessStatsToXMLRPC($ReturnArray['Stats'],$ReturnArray['Kills'],$MapName,$LogFilePrefix,$Local_Server_ID,$Remote_Stats_Server,$Remote_Stats_Key);
					}
					else 
					{
						if($Debug == true)
						{
							print("Processing in SQL for local processing<br>");
						}
						//Process the data locally
						$L4DStats->ProcessStats($ReturnArray['Stats'],$ReturnArray['Kills'],$MapName,$LogFilePrefix);
					}
				}
				else 
				{
					if($Debug == true)
					{
						print("Stats log is either locked or not closed yet<br>");
						$MoveFiles = false;
					}
				}
				
			}
			else 
			{
				if($Debug == true)
				{
					print("$PotentialLogFile does not appear to be a valid log file");
				}
			}
			
		}
	}
	
}
else
{
	print("The directory ($LogDir) doesn't exist.<br>");
}


?>