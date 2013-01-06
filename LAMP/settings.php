<?
/*==============================================================
L4DStats - Open Source Left4Dead Stats App

Copyright (c) 2008 Gareth Llewellyn

This program is free software, distributed under the terms of
the GNU General Public License Version 2. See the LICENSE file
at the top of the source tree.

================================================================*/

/// settings.php
/// Set all your custom per server details here

//--------------------------------------------------------------------
// General Server Details
//--------------------------------------------------------------------
//Admin Name
$AdminName = "Server Admin";

//Admin email address
$AdminEmail = "admin@thisserver.com";

//Server Name
$ServerName = "L4DStats Default Server";

//Server Description
$ServerDescripton = "A Default Left 4 Dead Dedicated Server - Play nice and enjoy!";

//Full URL of your site - for images / CSS absolute paths
$ServerRoot = "http://demo.l4dstats.co.uk";

//To enable nice looking URL's set this to true (false by default in 0.4)
$mod_rewrite_urls = false;

//If you don't want to display AI kills set this to false
$Show_AI_Stats = true;

//The directory where your logs are held
$LogDir = "./logs/";

//--------------------------------------------------------------------
// DB Connection Settings
//--------------------------------------------------------------------
$dbhost = 'localhost';
$dbuser = 'dbusername';
$dbpass = 'dbpassword';
$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
mysql_select_db('L4DStats');


//--------------------------------------------------------------------
// Multi Server Setup Settings
//--------------------------------------------------------------------
//Set to true to send processed stats to a central server with XMLRPC
$Remote_Stats_Aggregation = false;

//Set this to 'true' if this is the central server
$Is_Central_Server = true;

//If the above is true set this to be a DNS name of your central stats server
$Remote_Stats_Server = "demo.l4dstats.co.uk";

//To prevent abuse enter your stats key (so only you can make updates)
$Remote_Stats_Key = "L4DRocks";

//Whilst $ServerName can be the same this *must* be unique
$Local_Server_ID = 0; 


//--------------------------------------------------------------------
// Debug and Special Settings - Changing may break stuff
//--------------------------------------------------------------------
$Version = "0.4 Beta";
$XMLSWF_License = "";
$Debug = false;
$DeleteFileOnceProcessed = true;
?>
