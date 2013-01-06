//-----------------------------------------------------------------------
// <copyright file="L4DStatsClass.cs" company="NetworksAreMadeOfString">
//      L4DStats - NetworksAreMadeOfString
//      Author: Gareth Llewellyn
// </copyright>
// <purpose>
//  The main 'bulk' of the parsing and processing goes on here
// </purpose> 
//-----------------------------------------------------------------------
namespace L4DStatsClass
{
    // using System.Linq;
    using System;
    using System.Collections;
    using System.Collections.Generic;
    using System.IO;
    using System.Net;
    using System.Text;
    using System.Text.RegularExpressions;
    using System.Xml;
    using Conversive.PHPSerializationLibrary;

    /// <summary>
    /// The main workhorse for the Windows apps
    /// </summary>
    public class L4DStats
    {
        /// <summary>
        /// Contains all the info of each game
        /// </summary>
        public struct IndivGame
        {
            /// <summary>
            /// Contains a list of the statisticcollection structs
            /// </summary>
            public ArrayList Stats;

            /// <summary>
            /// The total number of kills in a game
            /// </summary>
            public int Kills;

            /// <summary>
            /// Indicates where the log file (and this entire struct) is a valid
            /// representation of the game
            /// </summary>
            public bool Closed;

            /// <summary>
            /// The log prefix used by both the setup and stats logs
            /// </summary>
            public string LogPrefix;

            /// <summary>
            /// The name of the map this game was played on
            /// </summary>
            public string MapName;
        }

        /// <summary>
        /// Contains a players details
        /// </summary>
        public struct Player
        {
            /// <summary>
            /// The gamers name
            /// TODO: UTF8 support for those people with crazy names
            /// </summary>
            public string GamerName;

            /// <summary>
            /// The gamers unique (or not so unique if they are a bot)
            /// ID - used for tracking global stats
            /// </summary>
            public string SteamID;
        }

        /// <summary>
        /// Contains a list of the files we are gonna move later on
        /// </summary>
        public struct filesToMove
        {
            /// <summary>
            /// The setup log
            /// </summary>
            public string SetupLog;

            /// <summary>
            /// The stats log (if we find one)
            /// </summary>
            public string StatsLog;

            public string LogDirectory;
        }

        /// <summary>
        /// Contains all the pertinant info of each 'statistic'
        /// the who / what / where
        /// </summary>
        public struct StatisticCollection
        {
            /// <summary>
            /// The name of the user who scored the kill
            /// </summary>
            public string GamerName;

            /// <summary>
            /// The steam ID of the user that scored this kill
            /// </summary>
            public string SteamID;

            /// <summary>
            /// Where this happened (useful for map drawing / AI Director stats)
            /// </summary>
            public string AreaID;

            /// <summary>
            /// What was killed
            /// </summary>
            public string KilledEntity;

            /// <summary>
            /// What weapon was used
            /// </summary>
            public string Weapon;

            /// <summary>
            /// Was this a headshot kill?
            /// </summary>
            public string HeadShot;
        }

        public filesToMove validFiles = new filesToMove();

        /// <summary>
        /// Given a log directory, the prefix of the setup log and the mapname this function
        /// chews through the log counting up the stats and creating a big ol' array of
        /// arrays ready for further processing
        /// </summary>
        /// <param name="logDirectory">The log directory.</param>
        /// <param name="logFile">The log file.</param>
        /// <param name="mapName">Name of the map.</param>
        /// <returns>
        /// an array that can be sent for further processing
        /// </returns>
        public IndivGame ProcessLogs(string logDirectory, string logFile, string mapName)
        {
            ArrayList thisGame = new ArrayList();
            IndivGame returnArray = new IndivGame();
            bool isLogClosed = false;
            int kills = 0;
            
            string[] logFileParts = logFile.Split(new char[] { '_' });
            string logFilePrefix = logFileParts[0] + "_" + logFileParts[1] + "_" + logFileParts[2] + "_" + logFileParts[3] + "_" + logFileParts[4] + "_" + logFileParts[5];
            string fullFilePath = string.Empty;
            string fullFileName = string.Empty;

            //Check if the file passed (which we already know has setup elements in it) is also a stats log
            if (this.Check_sv_log_onefile(logDirectory + logFile) == true)
            {
                Console.WriteLine("Found a log file written as sv_log_onefile 1. Processing...");
                fullFilePath = logDirectory + logFile;
                fullFileName = logFile;
                validFiles.SetupLog = logFile;
                validFiles.StatsLog = "false";
                validFiles.LogDirectory = logDirectory;
                returnArray.MapName = mapName + "_movie";
            }
            else
            {
                Console.WriteLine("Log file was only a setup file, attempting to find a matching stats log...");
                validFiles.SetupLog = logFile;
                validFiles.LogDirectory = logDirectory;
                // Setup logs should be either 000.log (first log in this minute) or 002.log (previous game failed and a new one started in the same minute)
                if (logFileParts[6] == "000.log")
                {
                    fullFilePath = logDirectory + logFilePrefix + "_001.log";
                    fullFileName = logFilePrefix + "_001.log";
                }
                else
                {
                    fullFilePath = logDirectory + logFilePrefix + "_003.log";
                    fullFileName = logFilePrefix + "_003.log";
                }

                returnArray.MapName = mapName;
            }
            returnArray.LogPrefix = logFilePrefix;
            
            StreamReader re;

            Console.WriteLine("Opening File: " + fullFilePath);

            //Try and open the file
            try
            {
                if (File.Exists(fullFilePath))
                {
                    re = File.OpenText(fullFilePath);
                }
                else
                {
                    Console.WriteLine("Usual statistics log X_001.log not found - trying known alternatives");
                    
                    fullFileName = this.FindAlternativeLogFile(logDirectory, validFiles.SetupLog);
                    
                    if (fullFileName == "FAILED")
                    {
                        Console.WriteLine("Alternative Files not found or some other such nasty error\r\n");
                        returnArray.Closed = false;
                        return returnArray;
                    }
                    else
                    {
                        fullFilePath = logDirectory + fullFileName;
                        re = File.OpenText(fullFilePath);
                    }
                }

                //No point repeating ourselves [Will cause MoveFiles() to crash
                if (fullFileName != validFiles.SetupLog)
                {
                    validFiles.StatsLog = fullFileName; // logFilePrefix + "_001.log";
                }
                else
                {
                    validFiles.StatsLog = "false";
                }
            }
            catch (System.IO.FileNotFoundException)
            {
                Console.WriteLine("Despite a call to FindAlternativeLogFile() no alternative logs were found");

                returnArray.Closed = false;
                return returnArray;
            }
            catch
            {
                Console.WriteLine("An exception unrelated to I/O Operations occured\r\n");

                returnArray.Closed = false;
                return returnArray;
            }

            // Check this is a valid log file
            if (re.ReadToEnd().Contains("Log file closed"))
            {
                isLogClosed = true;
                Console.WriteLine("Log File has been closed by server - continuing to process\r\n");
            }
            else
            {
                Console.WriteLine("Log File is still in use or crashed - aborting processing\r\n");
                returnArray.Closed = false;
                return returnArray;
            }

            // Its valid so loop through each line
            string logLine = string.Empty;
            string StatType = string.Empty;
            re.Close();
            re = File.OpenText(fullFilePath);

            while ((logLine = re.ReadLine()) != null)
            {
                //Experimental Regular Expression matching

                if (logLine.Length > 30)
                {
                    StatType = logLine.Substring(26, 4);
                }
                else
                {
                    continue;
                }

			switch(StatType)
			{
				//This is a line where something killed something
				case "DEAT":
				{
                     // Initialise this struct
                    StatisticCollection statistic = new StatisticCollection();

                    statistic = this.RegexForDeathStats(logLine);

                    // If all is OK assign the struct values
                    if (statistic.Weapon == "player" ||
                        statistic.Weapon == "prop_physics" ||
                        statistic.Weapon == "trigger_hurt" ||
                        statistic.Weapon == "env_fire" ||
                        statistic.Weapon == "trigger_hurt_ghost" ||
                        statistic.Weapon == "Reset" ||
                        statistic.Weapon == "prop_door_rotating_checkpoint" ||
                        statistic.Weapon == "entityflame" ||
                        statistic.Weapon == "world" ||
                        statistic.Weapon == "worldspawn" ||
                        String.IsNullOrEmpty(statistic.AreaID) ||
                        String.IsNullOrEmpty(statistic.GamerName) ||
                        String.IsNullOrEmpty(statistic.HeadShot) ||
                        String.IsNullOrEmpty(statistic.KilledEntity) ||
                        String.IsNullOrEmpty(statistic.SteamID) ||
                        String.IsNullOrEmpty(statistic.Weapon))
                    {
                        // do nothing
                    }
                    else
                    {
                        kills++;
                        thisGame.Add(statistic);
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

                /////////////////////////// OLD PARSER ///////////////////////////////////////////
                /*
                // Do all the parsing
                // Explode the line into componant parts
                string[] line = logLine.Split(new char[] { ':' });

                // Make sure this isn't a funky line
                if (line.Length >= 6)
                {
                    string gamerName = string.Empty;
                    string headShot = "false";
                    string steamID = string.Empty;
                    string areaID = string.Empty;
                    string killedEntity = string.Empty;
                    string weapon = string.Empty;
                    string testLine = line[3];

                    // This is a lazy hack to fix people who have :'s in their name
                    // This *REALLY* needs to be fixed
                    if (testLine.Length < 6)
                    {
                        testLine = "123456";
                    }

                    // Evaluate the first 6 characters of the chopped line and work out what sort of stat record this is
                    switch (testLine.Substring(0, 6))
                    {
                        case " (1)Ta":
                            {
                                // Console.WriteLine("TANK Event");
                            }

                            break;

                        case " (2)Ta":
                            {
                                // Console.WriteLine("TANK Event");
                            }

                            break;

                        case " (RESC":
                            {
                                // Console.WriteLine("Rescue Event");
                            }

                            break;

                        case " (INCA":
                            {
                                // Console.WriteLine("Player Incap'd");
                            }

                            break;

                        case " (PHYS":
                            {
                                // Console.WriteLine("The physics engine did something");
                            }

                            break;

                        case " (TONG":
                            {
                                // Console.WriteLine("A smoker did something");
                            }

                            break;

                        case " (MOB)":
                            {
                                // Console.WriteLine("MOB Event");
                            }

                            break;

                        case " (SKIN":
                            {
                                // Console.WriteLine("(SKIN) Event");
                            }

                            break;

                        case " (DEAT":
                            {
                                string[] ltgtdelimter = { "><" };
                                string[] steamIDArray;
                                try
                                {
                                    int startPlayerName = logLine.IndexOf('"') + 1;
                                    int endPlayerName = logLine.IndexOf('<') - startPlayerName;
                                    gamerName = logLine.Substring(startPlayerName, endPlayerName);
                                }
                                catch // (Exception GamerNameException)
                                {
                                    Console.WriteLine("Ascertaining the players name failed - this record will be scrubbed");
                                    continue;
                                }

                                try
                                {
                                    // Get the Steam ID (we do this first then do the check for bots later)
                                    steamIDArray = logLine.Split(ltgtdelimter, System.StringSplitOptions.None);
                                    steamID = steamIDArray[1];
                                }
                                catch // (Exception SteamIDException)
                                {
                                    Console.WriteLine("Ascertaining the players Steam ID failed - this record will be scrubbed");
                                    continue;
                                }

                                // If they have a valid SteamID it won't be changed *if* however they are 
                                // a bot we need to record that
                                switch (gamerName)
                                {
                                    case "(1)Tank":
                                    case "(2)Tank":
                                    case "(3)Tank":
                                    case "(4)Tank":
                                        {
                                            gamerName = "Tank";
                                            steamID = "BOT-TANK";
                                        }

                                        break;

                                    case "Hunter":
                                        {
                                            steamID = "BOT-Hunter";
                                        }

                                        break;

                                    case "infected":
                                        {
                                            steamID = "BOT-infected";
                                        }

                                        break;

                                    case "witch":
                                        {
                                            steamID = "BOT-witch";
                                        }

                                        break;

                                    case "Smoker":
                                        {
                                            steamID = "BOT-Smoker";
                                        }

                                        break;

                                    case "Boomer":
                                        {
                                            steamID = "BOT-Boomer";
                                        }

                                        break;

                                    case "Bill":
                                        {
                                            steamID = "BOT-Bill";
                                        }

                                        break;

                                    case "Louis":
                                        {
                                            steamID = "BOT-Louis";
                                        }

                                        break;

                                    case "Zoey":
                                        {
                                            steamID = "BOT-Zoey";
                                        }

                                        break;

                                    case "Francis":
                                        {
                                            steamID = "BOT-Francis";
                                        }

                                        break;
                                }

                                // Get the AREAID
                                try
                                {
                                    areaID = steamIDArray[7].Substring(5, (steamIDArray[7].IndexOf('>') - 5));
                                }
                                catch // (Exception AreaIDException)
                                {
                                    Console.WriteLine("Ascertaining the Area ID failed - this record will be scrubbed");
                                    continue;
                                }

                                try
                                {
                                    // Get what was killed
                                    string[] killedDelimiter = { "\" killed \"" };
                                    string[] killedEntityArray = logLine.Split(killedDelimiter, System.StringSplitOptions.None);

                                    // This is to prevent the OOB error due to someone dying 'weirdly'
                                    if (killedEntityArray.Length < 2)
                                    {
                                        killedEntity = "Unknown";
                                    }
                                    else
                                    {
                                        killedEntity = killedEntityArray[1];
                                        killedEntity = killedEntity.Substring(0, killedEntity.IndexOf('<'));
                                    }
                                }
                                catch
                                {
                                    Console.WriteLine("Ascertaining what was killed failed - this record will be scrubbed");
                                    continue;
                                }

                                try
                                {
                                    // Get What Weapon Was used
                                    string[] weaponDelimiter = { "\" with \"" };
                                    string[] weaponArray = logLine.Split(weaponDelimiter, System.StringSplitOptions.None);
                                    if (weaponArray.Length < 2)
                                    {
                                        weapon = "world";
                                    }
                                    else
                                    {
                                        weapon = weaponArray[1].Substring(0, weaponArray[1].IndexOf('"'));
                                    }
                                }
                                catch // (Exception WeaponException)
                                {
                                    Console.WriteLine("Ascertaining what weapon was used failed - this record will be scrubbed");
                                    continue;
                                }

                                // Did someone score a headshot?
                                if (logLine.Contains("headshot") == true)
                                {
                                    headShot = "1";
                                }
                                else
                                {
                                    headShot = "0";
                                }
                            }

                            break;

                        default:
                            {
                                // This is not an error its output to help for matching!
                                // Console.WriteLine("Match error:" + Line[3].Substring(0, 6));
                            }

                            break;
                    }

                    // Initialise this struct
                    StatisticCollection statistic = new StatisticCollection();

                    // If all is OK assign the struct values
                    if (weapon == "player" ||
                        weapon == "prop_physics" ||
                        weapon == "trigger_hurt" ||
                        weapon == "env_fire" ||
                        weapon == "trigger_hurt_ghost" ||
                        weapon == "Reset" ||
                        weapon == "prop_door_rotating_checkpoint" ||
                        weapon == "entityflame" ||
                        weapon == "world" ||
                        weapon == "worldspawn" ||
                        weapon == "" ||
                        steamID == "")
                    {
                        // do nothing
                    }
                    else
                    {
                        kills++;
                        statistic.GamerName = gamerName;
                        statistic.SteamID = steamID;
                        statistic.AreaID = areaID;
                        statistic.KilledEntity = killedEntity;
                        statistic.Weapon = weapon;
                        statistic.HeadShot = headShot;

                        thisGame.Add(statistic);
                    }
                }*/
            }

            // Close the file so we can move it
            re.Close();

            // Move the files we know were valid - moved to the calling function to check if the XMLRPC post worked 28/01/09
            //this.MoveFiles(validFiles, logDirectory);

            // Pass all these stats back for further processing
            returnArray.Kills = kills;
            returnArray.Closed = isLogClosed;
            returnArray.Stats = thisGame;
            return returnArray;
        }

        /// <summary>
        /// A quick and dirty check to try and detect if this log file is a setup and stats file (sv_log_onefile 1)
        /// or just a setup file sv_log_onefile 0
        /// </summary>
        /// <param name="LogFileName"></param>
        /// <returns></returns>
        public bool Check_sv_log_onefile(string LogFileName)
        {
            StreamReader re;
            string logLine;
            re = File.OpenText(LogFileName);

            while ((logLine = re.ReadLine()) != null)
            {
                if (logLine.Contains("DEATH"))
                {
                    re.Close();
                    return true;
                    break;
                }
            }

            re.Close();
            //If the while loop finishes without exiting we can assume this is a setup file only.
            return false;
        }

        /// <summary>
        /// This is the new Regex based parser
        /// </summary>
        /// <param name="logline"></param>
        /// <returns></returns>
        public StatisticCollection RegexForDeathStats(string logline)
        {
            // Initialise this struct
            StatisticCollection statistic = new StatisticCollection();

            //Player Name
            Match PlayerNameMatch = Regex.Match(logline.Substring(33,40), "^(\\w|\\W)+\\<[0-9]+\\>(<ST|<BO|<><)");
            PlayerNameMatch = Regex.Match(PlayerNameMatch.ToString(), "^(\\w|\\W)+\\<[0-9]");
            if (PlayerNameMatch.Success)
            {
                statistic.GamerName = System.Security.SecurityElement.Escape(this.FormatBotNames(PlayerNameMatch.ToString().Substring(0, (PlayerNameMatch.ToString().Length - 2))));
            }

            //SteamID
            Match SteamIDMatch = Regex.Match(logline, "STEAM_[0-9]:[0-9]:[0-9]+");
            if (SteamIDMatch.Success == false ||
                statistic.GamerName == "Tank" ||
                statistic.GamerName == "Smoker" ||
                statistic.GamerName == "witch" ||
                statistic.GamerName == "Boomer" ||
                statistic.GamerName == "Hunter" ||
                statistic.GamerName == "Louis" ||
                statistic.GamerName == "Zoey" ||
                statistic.GamerName == "Bill" ||
                statistic.GamerName == "Francis" ||
                statistic.GamerName == "infected" ||
                statistic.GamerName == "Infected")
            {
                statistic.SteamID = "BOT-" + statistic.GamerName;
            }
            else
            {
                statistic.SteamID = SteamIDMatch.ToString();
            }

            //Area
            Match AreaIDMatch = Regex.Match(logline, "\\<Area\\s\\d+");
            if (AreaIDMatch.Success)
            {
                statistic.AreaID = AreaIDMatch.ToString().Substring(6);
            }

            //Killed entity
            Match KilledEntityMatch = Regex.Match(logline, "(killed\\s\\\"\\w+|e\\swith\\s\\\"\\w+)");
            if (KilledEntityMatch.Success)
            {
                statistic.KilledEntity = System.Security.SecurityElement.Escape(KilledEntityMatch.ToString().Substring(8));
            }
            else
            {
                KilledEntityMatch = Regex.Match(logline, "killed\\s\\\"(\\w|\\W)+\\<\\d+\\>");
                if (KilledEntityMatch.Success)
                {
                    KilledEntityMatch = Regex.Match(KilledEntityMatch.ToString().Substring(8), "(\\w|\\W)+\\<");

                    statistic.KilledEntity = System.Security.SecurityElement.Escape(KilledEntityMatch.ToString().Substring(0,KilledEntityMatch.ToString().Length -1));
                }
                
            }

            //Weapon
            Match WeaponMatch = Regex.Match(logline, "with\\s\\\"\\w+");
            if (WeaponMatch.Success)
            {
                statistic.Weapon = WeaponMatch.ToString().Substring(6);
            }

            // Did someone score a headshot?
            if (logline.Contains("headshot") == true)
            {
                statistic.HeadShot = "1";
            }
            else
            {
                statistic.HeadShot = "0";
            }

            return statistic;
        }

        public string FindAlternativeLogFile(string logDirectory, string SetupLog)
        {
            string[] logFileParts = SetupLog.Split(new char[] { '_' });
            string logFilePrefix = logFileParts[0] + "_" + logFileParts[1] + "_" + logFileParts[2] + "_" + logFileParts[3] + "_" + logFileParts[4] + "_" + logFileParts[5];
            string fullFilePath = string.Empty;
            bool ValidFullFilePath = false;
            string NewFileName = string.Empty;

            for (int a =1; a<5; a++)
            {
                NewFileName = logFileParts[0] + "_" + logFileParts[1] + "_" + logFileParts[2] + "_" + logFileParts[3] + "_" + logFileParts[4] + "_" + logFileParts[5] + "_00" + a + ".log";
               
                if (NewFileName == SetupLog)
                {
                    continue;
                }
                else
                {
                    fullFilePath = logDirectory + NewFileName;
                    if (File.Exists(fullFilePath))
                    {
                        ValidFullFilePath = true;
                        break;
                    }
                }
            }

            if (ValidFullFilePath == false)
            {
                for (int a = 1; a < 5; a++)
                {
                    // 200901141100
                    
                    int minutes = Convert.ToInt32(logFileParts[5].Substring(10,2));
                    int hours = Convert.ToInt32(logFileParts[5].Substring(8, 2));
                    int day = Convert.ToInt32(logFileParts[5].Substring(6, 2));
                    int month = Convert.ToInt32(logFileParts[5].Substring(4, 2));
                    int year = Convert.ToInt32(logFileParts[5].Substring(0, 4));
                    string dateTimeString = "";
                    string minutesString = string.Empty;
                    
                    if (minutes == 59)
                    {
                        hours++;
                        dateTimeString = logFileParts[5].Substring(0, 8) + hours.ToString() + "00";
                    }
                    else
                    {
                        minutes++;
                        if (minutes.ToString().Length == 1)
                        {
                            minutesString = "0" + minutes.ToString();
                        }
                        else
                        {
                            minutesString = minutes.ToString();
                        }
                        dateTimeString = logFileParts[5].Substring(0, 10) + minutesString;
                    }
                    NewFileName = logFileParts[0] + "_" + logFileParts[1] + "_" + logFileParts[2] + "_" + logFileParts[3] + "_" + logFileParts[4] + "_" + dateTimeString + "_000.log";
                    if (NewFileName == SetupLog)
                    {
                        continue;
                    }
                    else
                    {
                        fullFilePath = logDirectory + NewFileName;

                        if (File.Exists(fullFilePath))
                        {
                            ValidFullFilePath = true;
                            break;
                        }
                    }
                }
            }

            if (ValidFullFilePath == false)
            {
                return "FAILED";
            }
            else
            {
                return fullFilePath.Substring(logDirectory.Length);
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
        public int PackageStatsForXMLRPC(string logFile, string mapName, int kills, ArrayList stats, string xmlRPCServer, string xmlRPCKey, string serverID)
        {
            // ArrayList Players = new ArrayList();
            Hashtable players = new Hashtable();
            Serializer serializer = new Serializer();

            // Get Player List
            try
            {
                foreach (StatisticCollection stat in stats)
                {
                    /*Player ThisPlayer = new Player();
                    ThisPlayer.GamerName = Stat.GamerName;
                    ThisPlayer.SteamID = Stat.SteamID;*/

                    if (players.Contains(stat.SteamID))
                    {
                        // Skip it as its a duplicate
                    }
                    else
                    {
                        // Players.Add(Stat.SteamID, ThisPlayer);
                        //players.Add(stat.SteamID, System.Security.SecurityElement.Escape(stat.GamerName));
                        string GamerName = stat.GamerName.Replace("<", "");
                        GamerName = GamerName.Replace("\\", "&#92;");
                        players.Add(stat.SteamID, GamerName);
                    }
                }
            }
            catch (Exception e)
            {
                Console.WriteLine(e.Message.ToString());
            }

            string serializedPlayers = serializer.Serialize(players);

            // XML Post string
            string xmlString = "<L4DStats><GAME><MAP>"
                                + mapName +
                                "</MAP><KILLS>"
                                + kills +
                                "</KILLS><LOGPREFIX>"
                                + logFile +
                                "</LOGPREFIX><PLAYERS>"
                                + serializedPlayers +
                                "</PLAYERS><SERVERID>"
                                + serverID +
                                "</SERVERID><STATS>";

            foreach (StatisticCollection stat in stats)
            {
                xmlString += "<STAT><STEAMID>"
                                + stat.SteamID +
                                "</STEAMID><AREAID>"
                                + stat.AreaID +
                                "</AREAID><KILLED>" +
                                stat.KilledEntity +
                                "</KILLED><WEAPON>"
                                + stat.Weapon +
                                "</WEAPON><HEADSHOT>"
                                + stat.HeadShot +
                                "</HEADSHOT></STAT>\r\n";
            }

            xmlString += "</STATS></GAME></L4DStats>";

            Console.WriteLine("XML String POST Size: " + (xmlString.Length * 2));

            // Post the data
            Console.WriteLine("Performing XMLRPC Request via HTTP");
            string httpReturn = this.HttpPost(xmlRPCServer, xmlString, xmlRPCKey);

            // Evaluate the return - I could do proper XML evaluation but I cba
            if (httpReturn == "<L4DStats><ERR></ERR><SUCCESS>true</SUCCESS></L4DStats>")
            {
                Console.WriteLine("XMLRPC Post Successful\r\n");

                //Move the files
                this.MoveFiles();
            }
            else
            {
                Console.WriteLine("XMLRPC Post Failed\r\n");
                Console.WriteLine("Log Files have not been moved to old/\r\n");
                Console.WriteLine(httpReturn + "\r\n");
            }

            // tell everyone its ok
            return 0;
        }

        
        // ---------------------------------------------------------------------------------
        // Formatters
        // ---------------------------------------------------------------------------------
        public string FormatBotNames(string Name)
        {
            switch(Name)
		    {
			    case "infected":
			    case "Infected":
			    case "(1)infected":
			    case "(1)Infected":
				    {
					    Name = "infected";
				    }
				    break;
    				
			    case "Tank":
			    case "(1)Tank":
			    case "(2)Tank":
			    case "(3)Tank":
			    case "(4)Tank":
				    {
					    Name = "Tank";
				    }
				    break;
    				
			    default:
				    {
					    //do nothing
				    }
				    break;
    			
		    }
		
		return Name;
        }
        // ---------------------------------------------------------------------------------
        // Setters / Putters 
        // ---------------------------------------------------------------------------------

        /// <summary>
        /// Moves the files.
        /// </summary>
        /// <param name="files">The files.</param>
        /// <param name="logDirectory">The log directory.</param>
        /// <returns>an int indicating status</returns>
        private int MoveFiles()
        {

            Console.WriteLine("Moving all successfully processed log files to: " + this.validFiles.LogDirectory + "\\old");
            DirectoryInfo dirInfo = new DirectoryInfo(this.validFiles.LogDirectory);
            DirectoryInfo oldLogDir = new DirectoryInfo(this.validFiles.LogDirectory + "/old/");
            if (oldLogDir.Exists == false)
            {
                try
                {
                    dirInfo.CreateSubdirectory("old");
                }
                catch (IOException e)
                {
                    Console.WriteLine(e.Message);
                    return 1;
                }
            }
            try
            {
                //The setup log will always be set
                File.Move(this.validFiles.LogDirectory + this.validFiles.SetupLog, this.validFiles.LogDirectory + "/old/" + this.validFiles.SetupLog);

                //If the log was made with sv_log_onefile then the stats log will be "false"
                if (this.validFiles.StatsLog != "false")
                {
                    File.Move(this.validFiles.LogDirectory + this.validFiles.StatsLog, this.validFiles.LogDirectory + "/old/" + this.validFiles.StatsLog);
                }
            }
            catch (Exception e)
            {
                Console.WriteLine("An error occured moving the files: \r\n" + e.Message);
                return 2;
            }

            return 0;
        }

        /// <summary>
        /// Sends the XMLRPC stream via a HTTP post
        /// </summary>
        /// <param name="remoteServer">The remote server.</param>
        /// <param name="xmlString">The XML string.</param>
        /// <param name="remoteAccessKey">The remote access key.</param>
        /// <returns>
        /// null if something breaks or a string if its ok
        /// </returns>
        private string HttpPost(string remoteServer, string xmlString, string remoteAccessKey)
        {
            string sendString = "XML=" + xmlString + "&Key=" + remoteAccessKey;

            WebRequest webRequest = WebRequest.Create("http://" + remoteServer + "/xmlrpc.php");
            webRequest.ContentType = "application/x-www-form-urlencoded";
            webRequest.Method = "POST";
            byte[] bytes = Encoding.ASCII.GetBytes(sendString);
            Stream os = null;
            try
            { // send the Post
                // Count bytes to send
                webRequest.ContentLength = bytes.Length;   
                
                // Set the Expect 100 continue to be false (fixes issues with lighttpd)
                System.Net.ServicePointManager.Expect100Continue = false;

                os = webRequest.GetRequestStream();
                os.Write(bytes, 0, bytes.Length);         // Send it
            }
            catch (WebException ex)
            {
                Console.WriteLine(ex.Message.ToString());
            }
            finally
            {
                if (os != null)
                {
                    os.Close();
                }
            }

            try
            { // get the response
                WebResponse webResponse = webRequest.GetResponse();
                if (webResponse == null)
                {
                    return null;
                }

                StreamReader sr = new StreamReader(webResponse.GetResponseStream());
                return sr.ReadToEnd().Trim();
            }
            catch (WebException ex)
            {
                Console.WriteLine(ex.Message.ToString());
            }

            return null;
        }
    }
}
