// crossplatformtest.cpp : Defines the entry point for the console application.
//

#include "l4dstats.h"
using namespace std;

int main(int argc, char* argv[])//_TCHAR
{
	string version = "0.4.0";
	ifstream setupLogFile;
	string currentLogline, currentMapName;
	string logDir,fullFilePath;
	if (argv[1]) logDir = argv[1];
	bool validSetupFile = false;

	// Give them a header no matter what
	printf("+---------------------------------------+\r\n");
	printf("|          L4DStats Log Parser          |\r\n");
	printf("|             %s Beta                |\r\n", version.c_str());
	#ifdef linux
		printf("|           Platform: Linux             |\r\n");
	#endif

	#ifdef __MSC_VER
		printf("|           Platform: Win32             |\r\n");
	#endif

	if (argc == 1)
	{
		printf("|                                       |\r\n");
		// Try and set this bit red
		printf("| Error no arguments passed:            |\r\n");
		// Back to default
		printf("|                                       |\r\n");
		printf("| Arg1: Path of Log Directory           |\r\n");
		printf("| Arg2: Server DNS Name - no http://    |\r\n");
		printf("| Arg3: Remote Stats Pass Key           |\r\n");
		printf("| Arg4: This servers unique ID (1 - 99) |\r\n");
		printf("+---------------------------------------+\r\n");
	}
	else if (argc == 2)
	{
		if (strncmp(argv[1], "?", strlen("?")) == 0 ||
			strncmp(argv[1], "-h", strlen("-h")==0))
		{
			printf("| Arg1: Path of Log Directory           |\r\n");
			printf("| Arg2: Server DNS Name - no http://    |\r\n");
			printf("| Arg3: Remote Stats Pass Key           |\r\n");
			printf("| Arg4: This servers unique ID (1 - 99) |\r\n");
			printf("+---------------------------------------+\r\n");
			return 1;
		}
		else
		{
			printf("|                                       |\r\n");
			printf("|           www.L4DStats.co.uk          |\r\n");
			printf("+---------------------------------------+\r\n");
			// Try and set the color to red
			printf("ERROR: ");
			// Back to grey
			printf("Please ensure you add a double backslash (\\\\) to the end of your directory path if it is enclosed in \" 's");
			return 1;
		}
	}
	else if (argc == 5 || argc == 6)
	{
		printf("|                                       |\r\n");
		printf("|           www.L4DStats.co.uk          |\r\n");
		printf("+---------------------------------------+\r\n");

		//This stuff is for debug purposes
		printf("Log Dir: %s \r\n", logDir.c_str());
		printf("XML RPC Server: %s \r\n", argv[2]);
		printf("XML RPC Key: %s \r\n", argv[3]);
		printf("This Servers ID: %s \r\n", argv[4]);

		bool debugOutput = false;
		/*string XMLRPCServer,XMLRPCKey,ServerID;
		int argCount = 0,c;

		while ((c = getopt (argc, argv, "vlskid?")) != -1)
		{
				switch (c)
				{
					case 'v': printf("L4DStats-%s\n", "0.4.0"); exit(0);
					case 'l': fullFilePath = argv[argCount+1]; cout << "LogDir: " << argv[argCount+1] << endl; break;
					case 's': XMLRPCServer = argv[argCount+1]; cout << "XMLRPC Server: " << argv[argCount+1] << endl;break;
					case 'k': XMLRPCKey = argv[argCount+1]; cout << "XMLRPC Key: " << argv[argCount+1] << endl; break;
					case 'i': ServerID = argv[argCount+1]; cout << "Server ID: " << argv[argCount+1] << endl; break;
					case 'd': debugOutput = true; cout << "Debug Output: " << argv[argCount+1] << endl; break;
					case '?': cout << "-l logdir" << endl;
					default: abort ();
				}
				argCount++;
		}
		*/

		//Check if the client passed a 6th (debug) arguement
		if(argc == 6)
		{
			if(strncmp(argv[5],"true", strlen("true")) == 0)
			{
				cout << ">> DEBUG ENABLED <<" << endl;
				debugOutput = true;
			}
			else
			{
				cout << "Debug Argument was invalid" << endl;
				exit(EXIT_FAILURE);
			}
		}

		//Check all the inputs are valid
		// Log dir exists
		DIR *dp;
		struct dirent *dirp;
		if((dp  = opendir(logDir.c_str())) == NULL)
		{
			cout << "Error(" << errno << ") opening " << logDir << ": " << strerror(errno) <<  endl;
//			return errno; //Errno goes far higher than bash accepts as return codes
			exit(EXIT_FAILURE);
		}

		// XMLRPC server is valid
		// --I could check if this is a valid dns name but I guess we'll find that out at
		//   curl time

		// XMLRPC key is a valid string
		// If its a string its ok

		// ServerID is an int
		// I'll let the PHP handle this for the moment

		cout << endl << "Starting directory enumeration:" << endl;

		// Loop through all the logs in the dir and send em to get processed
		// If we discover a valid setup log then pass that log file through
		// to process its subsequent stats log
		while ((dirp = readdir(dp)) != NULL)
		{
			string setupLogFileName = dirp->d_name;
//			string fullFilePath = fullFilePath;
			// If this is . or .. then ignore it
			if(strcmp(setupLogFileName.c_str(),".") == 0 ||
			   strcmp(setupLogFileName.c_str(),"..") == 0 ||
			   strcmp(setupLogFileName.c_str(),"old") == 0)
			{
				//do nothing
			}
			else
			{
				//cout << setupLogFileName << endl;
				fullFilePath = logDir + setupLogFileName;

				//Check if the file is a valid setup file
				setupLogFile.open(fullFilePath.c_str());
				//cout << strerror(errno) << endl;

				if(setupLogFile)
				{
					//cout << fullFilePath << " is not I/O locked. Checking if it is a setup log" << endl;

					//while (setupLogFile >> currentLogline)
					while( getline( setupLogFile, currentLogline ))
					{
						// See if this line has "Loading map" in it
						// This could be consolidated into the if below
						char* result = strstr( currentLogline.c_str(), "Loading map" );
						int logLineLength = currentLogline.size();

						//Nope pointer is null
						if( result == NULL )
							//cout << "Not yet matched" << endl;
							validSetupFile = false;
						else
						{
							// Set the bool to be true so we know we can continue
							validSetupFile = true;

							//Now to get the map name out of the string
							//L MM/DD/YYYY - HH:MM:SS: Loading map "l4d_smalltown04_mainstreet"
							//									   ^38                        ^66
							currentMapName = currentLogline.substr(38,logLineLength - 39);

							//Somehow some maps have the " at the end - this is crap
							if(currentMapName[currentMapName.size()-1]  == '"')
								currentMapName = currentMapName.substr(0,currentMapName.size()-1);

							//Bit of space just for the hell of it
							cout << endl << endl;

							//Tell the user what is going on
							cout << "Found a setup log for a game played on map " << currentMapName << endl;
							//No point wasting time / resources checking the remaining lines
							break;
						}
					}
				}
				else
				{
					if(debugOutput)
					{
						cout << endl << "Couldn't open file descriptor for " << fullFilePath << ": " << strerror(errno) << endl;
						cout << "In version 0.4 this is because the initial readdir struct isn't flushed between loop iterations" << endl;
					}
					validSetupFile = false;
				}

				//Whether it worked or not we should close it
				setupLogFile.close();

				//After all that we should be able to tell if the file is valid or not
				if(validSetupFile)
				{
					//Instantiate the class of amazing win
					L4DStats StatsProcesser(debugOutput);

					//Do we want verbose output?
					if(debugOutput)
						cout << "Calling ProcessLogs() against " << setupLogFileName << endl;

					// Lets see if we can process the stats log

					int processSuccess = StatsProcesser.ProcessLogs(logDir,setupLogFileName,currentMapName);

					// If the log processing was successful lets package everything up
					// to be sent off to the wonderful world of oz
					if(!processSuccess)
					{
						//Do we want verbose output?
						if(debugOutput)
							cout << "Calling PackageStatsForXMLRPC()" << endl;

						cout << "Preparing to package data up for XML RPC..." << endl;

						int xmlCreateSuccess = StatsProcesser.PackageStatsForXMLRPC(argv[4]);

						if(!xmlCreateSuccess)
						{
							//cout << StatsProcesser.GetXMLRPCString() << endl;

							//Do we want verbose output?
							if(debugOutput)
								cout << "Calling HttpPost()" << endl;

							cout << "XML creation complete, preparing to post to remote server..." << endl;

							string postSuccess = StatsProcesser.HttpPost(argv[2], argv[3]);

							//check if post was successful
							//if(something)
							//{
								if(debugOutput)
									cout << "Log Processing completed successfully - moving files.." << endl;
								//Move the files so they aren't processed again
								StatsProcesser.moveFiles(logDir);
							//}
						}//xmlCreateSuccess
						else
							cout << "Creating the XML String for HTTP Post failed..." << endl;
					}//processSuccess
					else if (processSuccess == 1)
						cout << "Stats Log was locked - skipping" << endl;
					else if (processSuccess == 2)
					{
						cout << "No kills were recorded so no further processing required" << endl;
						//Move the files so they aren't processed again
						StatsProcesser.moveFiles(logDir);
					}
				}//Was the validSetupFile bool true
				else
				{
					// We don't really *need* to be told this
					//cout << setupLogFileName << " was not a valid setup file" << endl;
				}
				//Set validSetupFile to be false for the next iteration
				//validSetupFile = false;
			}//ifnot . or ..
		}//while loop

		//Close the file descriptor
		closedir(dp);

		//Tell everyone we are finished
		cout << "L4DStats processing complete!" << endl;
	}//argv == 5
	else if (argc > 6)
	{
		printf("|                                       |\r\n");
		printf("|           www.L4DStats.co.uk          |\r\n");
		printf("+---------------------------------------+\r\n");
		// Try and set the color to red
		printf("ERROR: ");
		// Back to grey
		printf("Too Many Arguments");
		return 1;
	}
	else
	{
		printf("|                                       |\r\n");
		printf("|           www.L4DStats.co.uk          |\r\n");
		printf("+---------------------------------------+\r\n");
		// Try and set the color to red
		printf("ERROR: ");
		// Back to grey
		printf("Argument Missing");
		exit(EXIT_FAILURE);
	}
	exit(EXIT_SUCCESS);
}
