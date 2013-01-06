//-----------------------------------------------------------------------
// <copyright file="L4DStatsMainGui.cs" company="NetworksAreMadeOfString">
//      L4DStats - NetworksAreMadeOfString
//      Author: Gareth Llewellyn
// </copyright>
// <purpose>
//  Parses, processes and packages up Left4Dead Dedicated server logs
// and sends them off to a remote server for insertion into the DB in GUI
// form
// </purpose> 
//-----------------------------------------------------------------------
namespace L4DStats_GUI
{
    using System;
    using System.Collections;
    using System.Collections.Generic;
    using System.ComponentModel;
    using System.Data;
    using System.Drawing;
    using System.IO;
    //using System.Linq;
    using System.Text;
    using System.Windows.Forms;
    using L4DStatsClass;

    /// <summary>
    /// The main gui for L4DStats
    /// </summary>
    public partial class L4DStatsMainGui : Form
    {

        System.IO.StringWriter debugLogWriter = new System.IO.StringWriter();

        public L4DStatsMainGui()
        {
            InitializeComponent();
            Console.SetOut(debugLogWriter);
        }

        /// <summary>
        /// Handles the Click event of the label1 control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="System.EventArgs"/> instance containing the event data.</param>
        private void label1_Click(object sender, EventArgs e)
        {
            // nothing
        }

        /// <summary>
        /// Handles the 1 event of the label1_Click control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="System.EventArgs"/> instance containing the event data.</param>
        private void label1_Click_1(object sender, EventArgs e)
        {
            // nothing
        }

        /// <summary>
        /// Handles the Click event of the startProcessingButton control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="System.EventArgs"/> instance containing the event data.</param>
        private void startProcessingButton_Click(object sender, EventArgs e)
        {
            debugOutputRichTextBox.Clear();
            currentStageProgressBar.Value = 0;
            currentTask.ForeColor = System.Drawing.Color.Black;

            string logDirectory = DirectoryTextBox.Text.ToString() + "\\";
            string xmlRPCServer = XMLRPCServerTextBox.Text.ToString();
            string xmlRPCKey = XMLRPCKeyTextBox.Text.ToString();
            string serverID = ServerIDTextBox.Text.ToString();

            L4DStats L4DStats = new L4DStats();
            ArrayList gameStats = new ArrayList();
            ArrayList games = new ArrayList();

            debugOutputRichTextBox.AppendText("       L4DStats Windows Parser\r\n");
            debugOutputRichTextBox.AppendText("             0.2 Beta\r\n");
            debugOutputRichTextBox.AppendText("\r\n");
            debugOutputRichTextBox.AppendText("  www.NetworksAreMadeOfString.co.uk\r\n");
            currentTask.Text = "Looking in log directory for setup logs...";

            // Look in the directory that the user provided for a list of log files
            DirectoryInfo dirInfo = new DirectoryInfo(logDirectory);
            FileInfo[] logFiles;
            try
            {
                logFiles = dirInfo.GetFiles("*.log");
            }
            catch (System.IO.FileNotFoundException)
            {
                MessageBox.Show("Please provide the entire absoloute path for some reason the GUI version can't handle relative paths", "Log Dir Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
                currentTask.Text = "Something bad happened!";
                currentTask.ForeColor = System.Drawing.Color.Red;
                return;
            }
            catch (System.IO.DirectoryNotFoundException)
            {
                MessageBox.Show("Please provide the entire absoloute path for some reason the GUI version can't handle relative paths", "Log Dir Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
                currentTask.Text = "Something bad happened!";
                currentTask.ForeColor = System.Drawing.Color.Red;
                return;
            }
            catch
            {
                MessageBox.Show("Something bad happened!", "Log Dir Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
                currentTask.Text = "Something bad happened!";
                currentTask.ForeColor = System.Drawing.Color.Red;
                return;
            }

            debugOutputRichTextBox.AppendText("\r\nStarting Stage 1 (Log Pre-Processing):\r\n");
            currentTask.Text = "Starting Stage 1 (Log Pre-Processing)....";
            
            /* For each one of these log files send it off to be parsed
                   At this stage we only care about whether this is a 'setup' log file*/
                    foreach (FileInfo log in logFiles)
                    {
                        // Set up all the variables we are going to need
                        string logLine = null;
                        bool validFile = false;
                        string mapName = string.Empty;

                        try
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
                                    string[] split = logLine.Split(new char[] { '"' });

                                    // Thankfully the MapName is always after the first "
                                    mapName = split[1];

                                    // break;
                                }
                            }

                            // Save them resources!
                            re.Close();
                        }
                        catch (System.IO.FileLoadException)//IOException
                        {
                            debugOutputRichTextBox.AppendText("File found but was not loadable (Locked?) Skipping...");
                            validFile = false;
                        }
                        catch (System.IO.DirectoryNotFoundException)//IOException
                        {
                            debugOutputRichTextBox.AppendText("File Directory Not Found. Skipping...");
                            validFile = false;
                        }
                        catch (System.IO.IOException)//IOException
                        {
                            debugOutputRichTextBox.AppendText("A generic I/O error occured. Skipping...");
                            validFile = false;
                        }
                        catch
                        {
                            debugOutputRichTextBox.AppendText("An un-anticipated error occured. Skipping...");
                            validFile = false;
                        }


                        // The logfile is a setup file!
                        if (validFile == true)
                        {
                            // Create a string array containing the Logname and MapName
                            string[] game = { log.Name.ToString(), mapName };

                            // Add it to our Games List
                            games.Add(game);

                            debugOutputRichTextBox.AppendText("Found valid Setup log file " + log.Name.ToString() + "\r\n");
                        }
                    }

                    currentStageProgressBar.Value = 20;
                    
                    //-------------------------------------------------------------------------------------------
                    // Stage 2 - Loop through each one of the 'valid' Games and try to process the stats in its
                    //           sister log file (most logs start at 000.log and the stats in 001.log
                    //-------------------------------------------------------------------------------------------
                    debugOutputRichTextBox.AppendText("\r\n\r\n----------------------------------");
                    debugOutputRichTextBox.AppendText("Starting Stage 2 (Log Processing):\r\n");
                    currentTask.Text = "Starting Stage 2 (Log Processing)....";
                    
                    // Game[LogFile,MapName]
                    foreach (string[] game in games)
                    {
                        L4DStats.IndivGame gameStruct = L4DStats.ProcessLogs(logDirectory, game[0], game[1]);
                        gameStats.Add(gameStruct);
                    }

                    currentStageProgressBar.Value = 40;
                    
                    //-------------------------------------------------------------------------------------------
                    // Stage 3 - Loop through each one of the processed games and package it up in XML for the
                    //           XMLRPC server and post it
                    //-------------------------------------------------------------------------------------------
                    debugOutputRichTextBox.AppendText("\r\n\r\n--------------------------------------");
                    debugOutputRichTextBox.AppendText("Starting Stage 3 (XML Parse & RPC):\r\n");
                    currentTask.Text = "Starting Stage 3 (XML Parse & RPC)....";
                    int i = 0;
                    foreach (L4DStats.IndivGame game in gameStats)
                    {
                        debugOutputRichTextBox.AppendText("Processing Game " + i.ToString() + ":\r\n\r\n");
                        if (game.Closed == false)
                        {
                            debugOutputRichTextBox.AppendText("Insufficient Stats to process Game - skipping\r\n\r\n");
                        }
                        else
                        {
                            L4DStats.PackageStatsForXMLRPC(game.LogPrefix, game.MapName, game.Kills, game.Stats, xmlRPCServer, xmlRPCKey, serverID);
                        }

                        i++;
                    }

                    currentStageProgressBar.Value = 60;

                    //-------------------------------------------------------------------------------------------
                    // Stage 4 - Now get rid of those files so we don't process them again!
                    //-------------------------------------------------------------------------------------------
                    debugOutputRichTextBox.AppendText("\r\n\r\n--------------------------------------");
                    debugOutputRichTextBox.AppendText("Starting Stage 4 (Cleanup):\r\n");
                    currentTask.Text = "Starting Stage 4 (Cleanup)....";
                    currentStageProgressBar.Value = 80;

                    //-------------------------------------------------------------------------------------------
                    // Finished - Now get rid of those files so we don't process them again!
                    //-------------------------------------------------------------------------------------------
                    debugOutputRichTextBox.AppendText("\r\n\r\n--------------------------------------");
                    debugOutputRichTextBox.AppendText("Finished!\r\n");
                    currentTask.Text = "Finished!";
                    currentTask.ForeColor = System.Drawing.Color.Green;
                    currentStageProgressBar.Value = 100;
        }

        /// <summary>
        /// Handles the Load event of the L4DStatsMainGui control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="System.EventArgs"/> instance containing the event data.</param>
        private void L4DStatsMainGui_Load(object sender, EventArgs e)
        {
            // originally we warned people about stuff to do with the lack of GUI feedback
            // this is no longer an issue
        }

        /// <summary>
        /// Handles the Click event of the BrowseDirButton control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="System.EventArgs"/> instance containing the event data.</param>
        private void BrowseDirButton_Click(object sender, EventArgs e)
        {
            logFileDirectoryBrowser.ShowDialog();
            DirectoryTextBox.Text = logFileDirectoryBrowser.SelectedPath;
            this.PopulatelogFileList();
        }

        private void PopulatelogFileList()
        {
            FileInfo[] logFiles;

            // Look in the directory that the user provided for a list of log files
            try
            {
                DirectoryInfo dirInfo = new DirectoryInfo(DirectoryTextBox.Text);
                logFiles = dirInfo.GetFiles("*.log");
            }
            catch (System.IO.DirectoryNotFoundException)
            {
                debugOutputRichTextBox.ForeColor = Color.Red;
                debugOutputRichTextBox.AppendText("ERROR: ");
                debugOutputRichTextBox.ForeColor = Color.Black;
                debugOutputRichTextBox.AppendText("The Directory Specified doesn't exist");

                return;
            }

            foreach (FileInfo log in logFiles)
            {
                logFileTreeView.Nodes.Add(log.Name.ToString());
                logFileTreeView.Nodes[logFileTreeView.Nodes.Count - 1].Checked = true;
            }
        }

        /// <summary>
        /// Handles the HelpRequest event of the logFileDirectoryBrowser control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="System.EventArgs"/> instance containing the event data.</param>
        private void logFileDirectoryBrowser_HelpRequest(object sender, EventArgs e)
        {
            // hmm
        }

        private void getSourceLinkLabel_LinkClicked(object sender, LinkLabelLinkClickedEventArgs e)
        {
            System.Diagnostics.Process.Start("IExplore", "http://www.L4DStats.co.uk/");

        }

        private void consoleRedirect_Tick(object sender, EventArgs e)
        {
            debugOutputRichTextBox.AppendText(debugLogWriter.ToString());
            debugLogWriter = new StringWriter();
            Console.SetOut(debugLogWriter);
        }

        private void selectAllButton_Click(object sender, EventArgs e)
        {
            foreach (TreeNode node in logFileTreeView.Nodes)
            {
                node.Checked = true;
            }
        }

        private void deselectAll_Click(object sender, EventArgs e)
        {
            foreach (TreeNode node in logFileTreeView.Nodes)
            {
                node.Checked = false;
            }
        }
    }
}
