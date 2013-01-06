/*
* l4dstats.c
*
*  Created on: Jan 13, 2009
*      Author: root
*/
#include <string>
#include <string.h>
#include <iostream>
#include <vector>
#include <sstream>
#include <algorithm>
#include <fstream>
#include <map>
#include <errno.h>
#include <dirent.h>
#include "curl/curl.h"
#include <sys/types.h>
#include <sys/stat.h>

using namespace std;

/*#include <string.h>
#include <string>
#include <iostream>
#include <errno.h>*/

/*#ifndef L4DSTATS_C_
#define L4DSTATS_C_


#endif*/

class L4DStats
{
	private:

	//The String that is sent via CURL
	string XMLRPCString;

	// Write any CURL errors in here
	static char errorBuffer[CURL_ERROR_SIZE];

	// Write all expected data from CURL in here
	static string buffer;

	// Specifies whether there is verbose output or not
	bool debugOutput;

	// Used to store a collection of the players
	map<string,string> PlayerHashTable;

	// The setup log
	string setupLog;

	// The stats log (if we find one)
	string statsLog;

	/// <summary>
	/// Contains a players details
	/// </summary>
	struct Player
	{
		/// <summary>
		/// The gamers name
		/// TODO: UTF8 support for those people with crazy names
		/// </summary>
		string GamerName;

		/// <summary>
		/// The gamers unique (or not so unique if they are a bot)
		/// ID - used for tracking global stats
		/// </summary>
		string SteamID;
	};

	/// <summary>
	/// Contains all the pertinent info of each 'statistic'
	/// the who / what / where
	/// </summary>
	struct StatisticCollection
	{
		/// <summary>
		/// The name of the user who scored the kill
		/// </summary>
		string GamerName;

		/// <summary>
		/// The steam ID of the user that scored this kill
		/// </summary>
		string SteamID;

		/// <summary>
		/// Where this happened (useful for map drawing / AI Director stats)
		/// </summary>
		string AreaID;

		/// <summary>
		/// What was killed
		/// </summary>
		string KilledEntity;

		/// <summary>
		/// What weapon was used
		/// </summary>
		string Weapon;

		/// <summary>
		/// Was this a headshot kill?
		/// </summary>
		string HeadShot;
	};

	/// <summary>
	/// Contains all the info of each game
	/// </summary>
	struct IndivGame
	{
		/// <summary>
		/// Contains a list of the statistic collection structs
		/// </summary>
		std::vector <StatisticCollection> Stats;

		/// <summary>
		/// The total number of kills in a game
		/// </summary>
		int Kills;

		/// <summary>
		/// Indicates if the log file was correctly (and this entire struct) is a valid
		/// representation of the game
		/// </summary>
		bool Closed;

		/// <summary>
		/// The log prefix used by both the setup and stats logs
		/// </summary>
		string LogPrefix;

		/// <summary>
		/// The name of the map this game was played on
		/// </summary>
		string MapName;

		/// <summary>
		/// The name of the statistics log file for this game
		/// </summary>
		string statsLogFile;
	};

	// An instance of the struct used for storing the game data
	IndivGame thisGame;

	public:
	///<summary>
	///Contructer that accepts a bool to indicate if we want verbose output or not
	///</summary>
	L4DStats(bool debugValue)
	{
		debugOutput = debugValue;
	}

	///<summary>
	///Destructer
	///</summary>
	~L4DStats()
	{
		//Doesn't actually do ought
	}

	int moveFiles(string path)
	{
		DIR *dp;
		string newPath = path + "old/";
		string fullOriginalSetup = path+setupLog;
		string fullOriginalStats = path+statsLog;
		string fullNewSetup = newPath+setupLog;
		string fullNewStats = newPath+statsLog;

		//Check if the path exists
		if((dp  = opendir(newPath.c_str())) == NULL)
		{
			if(debugOutput == true)
			{
				cout << "Directory " << newPath << " didn't exist, creating." << endl;
			}
			//Folder doesn't exist - lets make it
			mkdir(newPath.c_str(),0755);
		}

		if(debugOutput == true)
		{
			cout << "Moving files....." << endl;
		}
		rename(fullOriginalSetup.c_str(),fullNewSetup.c_str());
		rename(fullOriginalStats.c_str(),fullNewStats.c_str());

		return 0;
	}

	/// <summary>
	/// Just returns the XML RPC string - mainly used for debugging
	/// </summary>
	/// <returns>
	/// The XML RPC String
	/// </returns>
	string GetXMLRPCString()
	{
		return XMLRPCString;
	}

	/// <summary>
	/// Given a log directory, the prefix of the setup log and the mapname this function
	/// chews through the log counting up the stats and creating a big ol' array of
	/// arrays ready for further processing
	/// </summary>
	/// <param name="logDirectory">The log directory.</param>
	/// <param name="logFile">The log file.</param>
	/// <param name="mapName">Name of the map.</param>
	/// <returns>
	/// 0 for ok
	/// 1 for error
	/// 2 for no kills
	/// </returns>
	int ProcessLogs(string logDirectory, string logFileName, string mapName)
	{
		// This stuff is for debug purposes
		/*std::cout << "Log Dir: " << logDirectory << std::endl;
		std::cout << "Log File: " << logFile << std::endl;
		std::cout << "mapName: " << mapName << std::endl;*/

		//Start with some variables we are going to use later
		StatisticCollection aStat;
		int totalKills = 0;
		ifstream logFile;
		string logFileSuffix = logFileName.substr(36,7);
		string logFilePrefix = logFileName.substr(0,36);
		string fullFilePath, fullFileName, currentLogline,tempWeaponName;
		setupLog = logFileName;

		// Setup logs should be either 000.log (first log in this minute)
		// or 002.log (previous game failed and a new one started in the same minute)
		if (logFileSuffix.compare("000.log") == 0)
		{
			fullFilePath = logDirectory + logFilePrefix + "001.log";
			fullFileName = logFilePrefix + "001.log";
		}
		else
		{
			fullFilePath = logDirectory + logFilePrefix + "003.log";
			fullFileName = logFilePrefix + "003.log";
		}

		// Store this for later
		statsLog = fullFileName;

		//Open the file
		logFile.open(fullFilePath.c_str());

		// Check if the file has been closed
		// (Valid files are not I/O locked)
		if(logFile)
		{
			thisGame.Closed = true;
			thisGame.statsLogFile = logFilePrefix;
		}
		else
		{
			cout << "Error opening file " << fullFilePath << " exiting...." << endl;
			return 1;
		}

		// Work out the log prefix
		// l081_029_085_068_27015_200811262358_000.log
		thisGame.LogPrefix = logFilePrefix;

		// We already have the mapname so assign it
		thisGame.MapName = mapName;

		// Now onto the evil parsing
		cout << fullFilePath << " is not I/O locked. Processing..." << endl;

		//while (setupLogFile >> currentLogline)
		while( getline( logFile, currentLogline ))
		{
			//
			//L 11/26/2008 - 23:59:06: (SKIN)
			if(currentLogline.size() > 32)
			{
				string evalLine = currentLogline.substr(26,4);
				//cout << "----|" << evalLine << "|----" << endl;

				if(evalLine.substr(0, 6) == "DEAT")
				{
					// L 11/26/2008 - 23:59:39: (DEATH)"Cee<1101><STEAM_1:1:2696233><Survivor><Manager><ALIVE><67+0><setpos_exact -3077.35 -99.04 437.23; setang 20.35 -89.37 0.00><Area 76515>" killed "infected<345><><Infected><infected><DEAD><-40><setpos_exact -3065.73 -1272.21 -55.97; setang -0.00 -99.18 0.00><Area 78726>" with "hunting_rifle"
					//							^26    "   <    ><                 ><
					char currentLogLineAsArray[currentLogline.size()+2];
					strcpy(currentLogLineAsArray,currentLogline.c_str());
					int i = 0;
					string tempGamerName,tempSteamID,tempAreaID,tempKilledEntity,tempWeaponName;

					////////////////////////////////////////////////////////////////////////////////
					//Get the gamer name
					//cout << *currentLogLineAsArray[33] << *currentLogLineAsArray[34] << *currentLogLineAsArray[35] << endl;

					i = 33; //Gamername always starts at 33

					//Loop through the character array
					while(i < currentLogline.size())//it should never get there!
					{
						if(currentLogLineAsArray[i] != '<')
						{
							tempGamerName += currentLogLineAsArray[i];
						}
						else
						{
							break;
						}
						i++;
					}

					//Somehow it still goes wrong
					if(tempGamerName[0] == '<')
					{
						tempGamerName = tempGamerName.substr(1,tempGamerName.size());
					}


					////////////////////////////////////////////////////////////////////////////////
					//Time to find the steam ID
					i = i + 6;
					//Loop through the character array
					while(i < currentLogline.size())//it should never get there!
					{
						if(currentLogLineAsArray[i] != '>')
						{
							tempSteamID += currentLogLineAsArray[i];
						}
						else
						{
							break;
						}
						i++;
					}

					//Somehow it still goes wrong
					if(tempSteamID[0] == '<')
					{
						tempSteamID = tempSteamID.substr(1,tempSteamID.size());
					}

					////////////////////////////////////////////////////////////////////////////////
					// If they have a valid SteamID it won't be changed *if* however they are
					// a bot we need to record that
					if(strcmp(tempGamerName.c_str(),"(1)Tank") == 0 ||
					   strcmp(tempGamerName.c_str(),"(2)Tank") == 0 ||
					   strcmp(tempGamerName.c_str(),"(3)Tank") == 0 ||
					   strcmp(tempGamerName.c_str(),"Tank") == 0 ||
					   strcmp(tempGamerName.c_str(),"(4)Tank") == 0)
					{
						tempGamerName = "Tank";
						tempSteamID = "BOT-TANK";
					}
					else if(strcmp(tempGamerName.c_str(),"Hunter") == 0)
					{
						tempSteamID = "BOT-Hunter";
					}
					else if(strcmp(tempGamerName.c_str(),"infected") == 0)
					{
						tempSteamID = "BOT-infected";
					}
					else if(strcmp(tempGamerName.c_str(),"witch") == 0)
					{
						tempSteamID = "BOT-witch";
					}
					else if(strcmp(tempGamerName.c_str(),"Smoker") == 0)
					{
						tempSteamID = "BOT-Smoker";
					}
					else if(strcmp(tempGamerName.c_str(),"Boomer") == 0)
					{
						tempSteamID = "BOT-Boomer";
					}
					else if(strcmp(tempGamerName.c_str(),"Bill") == 0)
					{
						tempSteamID = "BOT-Bill";
					}
					else if(strcmp(tempGamerName.c_str(),"Louis") == 0)
					{
						tempSteamID = "BOT-Louis";
					}
					else if(strcmp(tempGamerName.c_str(),"Zoey") == 0)
					{
						tempSteamID = "BOT-Zoey";
					}
					else if(strcmp(tempGamerName.c_str(),"Francis") == 0)
					{
						tempSteamID = "BOT-Francis";
					}

					/*if(strcmp(tempSteamID.c_str(),"BOT") == 0 || strcmp(tempSteamID.c_str(),"OT") == 0)
					{
						tempSteamID = "BOT-"+tempGamerName;
					}*/

					//Assign the tempSteamID to the stat collection
					aStat.SteamID = tempSteamID;
					//Assign the tempGamerName to the stat collection
					aStat.GamerName = tempGamerName;

					PlayerHashTable[tempSteamID] = tempGamerName;

					////////////////////////////////////////////////////////////////////////////////
					//Now lets try and find where this happened
					int x=0;
					while(i < currentLogline.size())//it should never get there!
					{
						if(currentLogLineAsArray[i] == '<')
						{
							x++;
						}

						if(x == 6)
						{
							i += 5;
							while(i < currentLogline.size())//it should never get there!
							{
								if(currentLogLineAsArray[i] != '>')
								{
									tempAreaID += currentLogLineAsArray[i];
								}
								else
								{
									break;
								}
								i++;
							}
							x++;
						}
						else
						{
							if(x > 6)
							{
								// If x is greater than 6 then we have the area ID
								break;
							}
						}

						i++;
					}

					// Assign the tempAreaID to the statistic collection
					aStat.AreaID = tempAreaID;

					////////////////////////////////////////////////////////////////////////////////
					//Now we need to find out what was killed
					i = i + 10;
					while(i < currentLogline.size())//it should never get there!
					{
						if(currentLogLineAsArray[i] != '<')
						{
							tempKilledEntity += currentLogLineAsArray[i];
						}
						else
						{
							break;
						}
						i++;
					}

					//cout << "Killed entity: " << tempKilledEntity << endl;
					aStat.KilledEntity = tempKilledEntity;

					////////////////////////////////////////////////////////////////////////////////
					// Find out what weapon was used
					x = 0;
					while(i < currentLogline.size())//it should never get there!
					{

						if(currentLogLineAsArray[i] == '>')
						{
							x++;
						}

						if(x == 8)
						{
							i = i + 9;
							while(currentLogLineAsArray[i] != '"')//it should never get there!
							{
								tempWeaponName += currentLogLineAsArray[i];
								i++;
							}
							x++;
						}

						if(x > 8)
						{
							break;
						}

						i++;
					}

					aStat.Weapon = tempWeaponName;

					////////////////////////////////////////////////////////////////////////////////
					// See if the kill was the result of a headshot
					if(strstr( currentLogline.c_str(), "headshot" ) != NULL)
					{
						aStat.HeadShot = "1";
					}
					else
					{
						aStat.HeadShot = "0";
					}

					////////////////////////////////////////////////////////////////////////////////
					// check all is OK and if so add to the vector

					if (strcmp(tempWeaponName.c_str(),"player") == 0 ||
						strcmp(tempWeaponName.c_str(),"prop_physics") == 0 ||
						strcmp(tempWeaponName.c_str(),"trigger_hurt") == 0 ||
						strcmp(tempWeaponName.c_str(),"env_fire") == 0 ||
						strcmp(tempWeaponName.c_str(),"trigger_hurt_ghost") == 0 ||
						strcmp(tempWeaponName.c_str(),"Reset") == 0 ||
						strcmp(tempWeaponName.c_str(),"prop_door_rotating_checkpoint") == 0 ||
						strcmp(tempWeaponName.c_str(),"entityflame") == 0 ||
						strcmp(tempWeaponName.c_str(),"pain_pills") == 0 ||
						strcmp(tempWeaponName.c_str(),"world") == 0 ||
						strcmp(tempWeaponName.c_str(),"worldspawn") == 0 ||
						strcmp(tempWeaponName.c_str(),"first_aid_kit") == 0 ||
						strcmp(tempWeaponName.c_str(),"d suicide with \"world\"") == 0 ||
						strcmp(tempWeaponName.c_str(),"") == 0 ||
						strcmp(tempGamerName.c_str(),"trigger_hurt") == 0 ||
						strcmp(tempSteamID.c_str(),"trigger_hurt") == 0 ||
						strcmp(tempSteamID.c_str(),"") == 0)
					{
						// do nothing
					}
					else
					{
						thisGame.Stats.push_back(aStat);
					}

					//Increment them kills
					totalKills++;
					//cout << thisGame.MapName << " : " << totalKills << endl;
				}
				else
				{
					// This is not an error its output to help for matching!
					// Console.WriteLine("Match error:" + Line[3].Substring(0, 6));
				}
			}
			else
			{
				//break;
			}
		}

		//Close off the file
		logFile.close();

		thisGame.Kills = totalKills;

		if(totalKills == 0)
		{
			// 2 means no kills (Don't send to XMLRPC)
			return 2;
		}
		else
		{
			return 0;
		}
	}


	/// <summary>
	/// Packages up the Stats ArrayList (and other arguements) into a massive string of XML for
	/// the XMLRPC Post
	/// </summary>
	/// <param name="logFile">The log file.</param>
	/// <param name="mapName">Name of the map.</param>
	/// <param name="kills">The kills.</param>
	/// <param name="stats">The stats.</param>
	/// <param name="xmlRPCServer">The XML RPC server.</param>
	/// <param name="xmlRPCKey">The XML RPC key.</param>
	/// <param name="serverID">The server ID.</param>
	/// <returns>
	/// a 0 if everything is ok - any other number if not
	/// </returns>
	int PackageStatsForXMLRPC(string serverID)
	{
		string serializedPlayers = "a:" + IntToString(PlayerHashTable.size()) + ":{";
		int x = 0;

		for( map<string, string>::iterator ii=PlayerHashTable.begin(); ii!=PlayerHashTable.end(); ++ii)
		{
			string GamerName = (*ii).second;
			string SteamID = (*ii).first;
			//serializedPlayers += "i:" + IntToString(x) + ";";
			serializedPlayers += "s:" + IntToString(SteamID.size()) + ":\"" + SteamID + "\";";
			serializedPlayers += "s:" + IntToString(GamerName.size()) + ":\"" + GamerName + "\";";
			x++;
			//cout << "SteamID: " << (*ii).first << "| GamerName: " << GamerName.size() << endl;
		}

		serializedPlayers += "}";

		//cout << serializedPlayers << endl;

		cout << thisGame.Kills << " kills achieved on map: " << thisGame.MapName << endl;

		//cout << "Statistic count: " << thisGame.Stats.size() << endl;

		// XML Post string
		XMLRPCString = "<L4DStats><GAME><MAP>"
							+ thisGame.MapName +
							"</MAP><KILLS>"
							+ IntToString(thisGame.Kills) +
							"</KILLS><LOGPREFIX>"
							+ thisGame.statsLogFile +
							"</LOGPREFIX><PLAYERS>"
							+ serializedPlayers +
							"</PLAYERS><SERVERID>"
							+ serverID +
							"</SERVERID><STATS>\r\n";
		int i = 0;
		while(i < thisGame.Stats.size())
		{
			XMLRPCString += "<STAT><STEAMID>"
							+ thisGame.Stats[i].SteamID +
							"</STEAMID><AREAID>"
							+ thisGame.Stats[i].AreaID +
							"</AREAID><KILLED>" +
							thisGame.Stats[i].KilledEntity +
							"</KILLED><WEAPON>"
							+ thisGame.Stats[i].Weapon +
							"</WEAPON><HEADSHOT>"
							+ thisGame.Stats[i].HeadShot +
							"</HEADSHOT></STAT>\r\n";
			i++;
		}

		XMLRPCString += "</STATS></GAME></L4DStats>";

		return 0;
	}

	///<summary>
	/// This is the writer call back function used by curl
	///</summary>
	static int writer(char *data, size_t size, size_t nmemb,std::string *buffer)
	{
		// What we will return
		int result = 0;

		// Is there anything in the buffer?
		if (buffer != NULL)
		{
			// Append the data to the buffer
			buffer->append(data, size * nmemb);

			// How much did we write?
			result = size * nmemb;
		}

		return result;
	}

	/// <summary>
	/// Sends the XMLRPC stream via a HTTP post
	/// </summary>
	/// <param name="remoteServer">The remote server.</param>
	/// <param name="remoteAccessKey">The remote access key.</param>
	/// <returns>
	/// null if something breaks or a string if its ok
	/// </returns>
	string HttpPost(string remoteServer, string remoteAccessKey)
	{
	 // Our curl objects
	 CURL *curl;
	 CURLcode result;
	 string postFields = "XML=" + XMLRPCString + "&Key=" + remoteAccessKey;
	 string fullURL = remoteServer + "/xmlrpc.php";

	   curl = curl_easy_init();
	   if(curl)
	   {

	      curl_easy_setopt(curl, CURLOPT_URL, fullURL.c_str());
	      curl_easy_setopt(curl, CURLOPT_POSTFIELDS, postFields.c_str());
	      //curl_easy_setopt(curl, CURLOPT_WRITEDATA, &buffer);

	      result = curl_easy_perform(curl);

	      /* always cleanup */
	      curl_easy_cleanup(curl);
	    }
	   cout << endl;
		//return "<L4DStats><ERR></ERR><SUCCESS>true</SUCCESS></L4DStats>";
	   return "1";
	}

	///<summary>
	/// int to string
	///</summary>
	string IntToString(int intValue)
	{
		std::string s;
		std::stringstream out;
		out << intValue;
		s = out.str();
		return s;
	}


};
