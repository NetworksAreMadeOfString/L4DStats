<?php
/*==============================================================
L4DStats - Open Source Left4Dead Stats App

Copyright (c) 2008 Gareth Llewellyn

This program is free software, distributed under the terms of
the GNU General Public License Version 2. See the LICENSE file
at the top of the source tree.

================================================================*/

/// xmlrpc.php
/// Used for remote servers to send data to a central server

include('./L4DStats.class.php');
include('./settings.php');
$L4DStats = new L4DStats();

if($Is_Central_Server == true)
{
	if($_POST['Key'] == $Remote_Stats_Key)
	{
		$L4DStats->ProcessStatsFromXML($_POST['XML']);
		print("<L4DStats><ERR></ERR><SUCCESS>true</SUCCESS></L4DStats>");
	}
	else 
	{
		print("<L4DStats>\r\n<ERR>Your auth key is incorrect.</ERR>\r\n<SUCCESS>false</SUCCESS>\r\n</L4DStats>");
	}
	
}
else 
{
	print("<L4DStats>\r\n<ERR>This server is not configured to be a central stats server!</ERR>\r\n<SUCCESS>false</SUCCESS>\r\n</L4DStats>");
}

?>
