//-----------------------------------------------------------------------
// <copyright file="Program.cs" company="NetworksAreMadeOfString">
//      L4DStats - NetworksAreMadeOfString
//      Author: Gareth Llewellyn
// </copyright>
// <purpose>
//  Parses, processes and packages up Left4Dead Dedicated server logs
// and sends them off to a remote server for insertion into the DB
// </purpose> 
//-----------------------------------------------------------------------
namespace L4DStats_Console
{
    // using System.Linq;
    using System;
    using System.Collections;
    using System.Collections.Generic;
    //using System.Drawing;
    using System.IO;
    using System.Text;
    using L4DStatsClass;

    /// <summary>
    /// The main program
    /// </summary>
    class Program
    {
        /// <summary>
        /// Mains the specified args.
        /// </summary>
        /// <param name="args">The arguements need to run the app</param>
        static void Main(string[] args)
        {
            // Give them a header no matter what
            Console.Write("+---------------------------------------+\r\n");
            Console.Write("|         L4DStats .NET Parser          |\r\n");
            Console.Write("|             0.3.0 Beta                |\r\n");

            if (args.Length == 0)
            {
                Console.Write("|                                       |\r\n");
                Console.ForegroundColor = ConsoleColor.Red;
                Console.Write("| Error no arguements passed:           |\r\n");
                Console.ForegroundColor = ConsoleColor.Gray;
                Console.Write("|                                       |\r\n");
                Console.Write("| Arg1: Path of Log Directory           |\r\n");
                Console.Write("| Arg2: Server DNS Name - no http://    |\r\n");
                Console.Write("| Arg3: Remote Stats Pass Key           |\r\n");
                Console.Write("| Arg4: This servers unique ID (1 - 99) |\r\n");
                Console.Write("+---------------------------------------+\r\n");
            }
            else if (args.Length == 1)
            {
                if (args[0] == "?" || args[0] == "/?")
                {
                    Console.Write("| Arg1: Path of Log Directory           |\r\n");
                    Console.Write("| Arg2: Server DNS Name - no http://    |\r\n");
                    Console.Write("| Arg3: Remote Stats Pass Key           |\r\n");
                    Console.Write("| Arg4: This servers unique ID (1 - 99) |\r\n");
                    Console.Write("+---------------------------------------+\r\n");
                    return;
                }
                else
                {
                    Console.Write("|                                       |\r\n");
                    Console.Write("|           www.L4DStats.co.uk          |\r\n");
                    Console.Write("+---------------------------------------+\r\n");
                    Console.ForegroundColor = ConsoleColor.Red;
                    Console.Write("ERROR: ");
                    Console.ForegroundColor = ConsoleColor.Gray;
                    Console.WriteLine("Please ensure you add a double backslash (\\\\) to the end of your directory path if it is enclosed in \" 's");
                    return;
                }
            }
            else if (args.Length == 4)
            {
                string logDirectory = args[0];
                string xmlRPCServer = args[1];
                string xmlRPCKey = args[2];
                string serverID = args[3];
                L4DStats L4DStats = new L4DStats();
                ArrayList gameStats = new ArrayList();
                ArrayList games = new ArrayList();
                FileInfo[] logFiles;

                Console.Write("|                                       |\r\n");
                Console.Write("|           www.L4DStats.co.uk          |\r\n");
                Console.Write("+---------------------------------------+\r\n");

                // Make sure that the user passed us a directory with a trailing slash
                if (logDirectory.Trim().EndsWith("\\") == false)
                {
                    Console.ForegroundColor = ConsoleColor.Blue;
                    Console.Write("INFO: ");
                    Console.ForegroundColor = ConsoleColor.Gray;
                    Console.WriteLine("Ensure the Log Directory provided has a trailing slash");
                    return;
                }

                // Look in the directory that the user provided for a list of log files
                try
                {
                    DirectoryInfo dirInfo = new DirectoryInfo(logDirectory);
                    logFiles = dirInfo.GetFiles("*.log");
                }
                catch (System.IO.DirectoryNotFoundException)
                {
                    Console.ForegroundColor = ConsoleColor.Red;
                    Console.Write("ERROR: ");
                    Console.ForegroundColor = ConsoleColor.Gray;
                    Console.WriteLine("The directory (" + logDirectory + ") doesn't exist.");
                    return;
                }

                if (logFiles.Length == 0)
                {
                    Console.ForegroundColor = ConsoleColor.Blue;
                    Console.Write("INFO: ");
                    Console.ForegroundColor = ConsoleColor.Gray;
                    Console.WriteLine("No valid log files found. Exiting.");
                    return;
                }

                //-------------------------------------------------------------------------------------------
                // Stage 1 - Finding Valid Setup logs (These tell us what Map we are going to be using)
                //-------------------------------------------------------------------------------------------
                // Console.WriteLine("\r\n\r\n----------------------------------");
                Console.WriteLine("\r\nStarting Stage 1 (Log Pre-Processing):\r\n");
                Console.WriteLine("Searching for Setup logs in " + logDirectory);

                // For each one of these log files send it off to be parsed
                // At this stage we only care about whether this is a 'setup' log file
                foreach (FileInfo log in logFiles)
                {
                    bool validFile = false;
                    string logLine = null;
                    string mapName = string.Empty;

                    // Set up all the variables we are going to need
                    try
                    {
                        //Console.WriteLine("Attempting to open: " + logDirectory + log.Name);

                        if (File.Exists(logDirectory + log.Name))
                        {
                            StreamReader re = File.OpenText(logDirectory + log.Name);

                            // If the log file contains 'Loading Map' then its a setup file
                            // This can probably be shortened to: re.Read or something
                            while ((logLine = re.ReadLine()) != null)
                            {
                                // We've found 'Loading Map'
                                if (logLine.Contains("Loading map"))
                                {
                                    // Set the bool to true so the next step knows
                                    // we have a valid file to work with
                                    validFile = true;

                                    // Split the current line on the " char
                                    string[] mapLineSplit = logLine.Split(new char[] { '"' });

                                    // Thankfully the MapName is always after the first "
                                    mapName = mapLineSplit[1];


                                    break;
                                }
                            }

                            // Save them resources!
                            re.Close();
                        }
                        else
                        {
                            // File was deleted by the previous loop
                            // We could break here but the validFile bool check will prevent any further processing
                        }
                    }
                    catch (System.IO.FileLoadException)
                    {
                        Console.ForegroundColor = ConsoleColor.Yellow;
                        Console.Write("WARNING: ");
                        Console.ForegroundColor = ConsoleColor.Gray;
                        Console.WriteLine("File found but was not loadable (Locked?) Skipping...");
                        validFile = false;
                    }
                    catch (System.IO.DirectoryNotFoundException)
                    {
                        Console.ForegroundColor = ConsoleColor.Red;
                        Console.Write("ERROR: ");
                        Console.ForegroundColor = ConsoleColor.Gray;
                        Console.WriteLine("File Directory Not Found. Skipping...");
                        validFile = false;
                    }
                    catch (System.IO.IOException)
                    {
                        Console.ForegroundColor = ConsoleColor.Red;
                        Console.Write("ERROR: ");
                        Console.ForegroundColor = ConsoleColor.Gray;
                        Console.WriteLine("A generic I/O error occured whilst accessing " + logDirectory + log.Name + " Skipping...");
                        validFile = false;
                    }
                    catch
                    {
                        Console.ForegroundColor = ConsoleColor.Red;
                        Console.Write("ERROR: ");
                        Console.ForegroundColor = ConsoleColor.Gray;
                        Console.WriteLine("An un-anticipated error occured. Skipping...");
                        validFile = false;
                    }

                    // The logfile is a setup file!
                    if (validFile == true)
                    {
                        Console.WriteLine(log.Name.ToString() + " is a valid setup file");

                        // Pass the valid file details through to the Log Processer
                        L4DStats.IndivGame gameStruct = L4DStats.ProcessLogs(logDirectory, log.Name.ToString(), mapName);

                        // Once the game struct has been returned it can be packaged up and sent off for remote addition
                        if (gameStruct.Closed == false || gameStruct.Kills < 1)
                        {
                            Console.WriteLine("Insufficient Stats to process Game - skipping\r\n\r\n");
                        }
                        else
                        {
                            L4DStats.PackageStatsForXMLRPC(gameStruct.LogPrefix, gameStruct.MapName, gameStruct.Kills, gameStruct.Stats, xmlRPCServer, xmlRPCKey, serverID);
                        }
                    }
                }

                //-------------------------------------------------------------------------------------------
                // Finished
                //-------------------------------------------------------------------------------------------
                Console.WriteLine("\r\n\r\n--------------------------------------");
                Console.WriteLine("Finished!\r\n");
            }
            else if (args.Length > 4)
            {
                Console.Write("|                                       |\r\n");
                Console.Write("|           www.L4DStats.co.uk          |\r\n");
                Console.Write("+---------------------------------------+\r\n");
                Console.ForegroundColor = ConsoleColor.Red;
                Console.Write("ERROR: ");
                Console.ForegroundColor = ConsoleColor.Gray;
                Console.Write("Too Many Arguments");
            }
            else
            {
                Console.Write("|                                       |\r\n");
                Console.Write("|           www.L4DStats.co.uk          |\r\n");
                Console.Write("+---------------------------------------+\r\n");
                Console.ForegroundColor = ConsoleColor.Red;
                Console.Write("ERROR: ");
                Console.ForegroundColor = ConsoleColor.Gray;
                Console.Write("Argument Missing");
            }
        }
    }
}
