<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Left 4 Dead</title>
</head>

<body style="margin:0;padding:0px;background: #000000;font-family:verdana,arial,sans-serif;color:#fff;font-size:11px;">
<?php
/*==============================================================
L4DStats - Open Source Left4Dead Stats App

Copyright (c) 2009 Gareth Llewellyn

This program is free software, distributed under the terms of
the GNU General Public License Version 2. See the LICENSE file
at the top of the source tree.

================================================================*/

	include('./L4DStats.class.php');
	include('./settings.php');

	$Stats = new L4DStats();
	$TodaysStats = $Stats->GetRecentStats(10);
	$ContentBody = "This is the Statistics page for the $ServerName dedicated Left4Dead Server.<br>
					Feel free to check out how you are doing or find who's the best player to have by your
					side on expert!";
	$ContentBody .= "<table width=\"100%\">".
	"<tr><td><strong>Infected Killed:</strong></td><td> ".$TodaysStats['TotalKills']."</td><td>&nbsp;&nbsp;&nbsp;</td><td><strong>Player Deaths:</strong> </td><td>".$TodaysStats['Deaths']."</td>".
	"<tr><td><strong>Games Played:</strong> </td><td>".$TodaysStats['GameCount']."</td><td>&nbsp;&nbsp;&nbsp;</td><td>&nbsp</td><td>&nbsp</td>".
	"<tr><td width=\"120\" valign=\"top\"><strong>Top Weapons of<br/> Past $GameCount Games:</strong></td><td>";

	foreach($TodaysStats['Weapons'] as $Weapon => $Kills)
	{
		$ContentBody .= "<img src=\"$ServerRoot/images/".$Weapon."_small.png\"> - $Kills<br>\r\n";
	}

	$ContentBody .= "</td><td>&nbsp;&nbsp;&nbsp;</td>".
	"<td valign=\"top\"><strong>Top 10 Players of <br/>Past $GameCount Matches:</strong></td><td valign=\"top\">";
	$ContentBody .= "<table>";
	foreach($TodaysStats['Players'] as $PlayerName => $Kills)
	{
		$ContentBody .= "<tr><td>$PlayerName</td><td>$Kills</td></tr>";
	}
	
	$ContentBody .= "</table>";
	$ContentBody .= "</table><h2 style=\"text-align: center;\">Full Stats available at: $ServerRoot</h2>";	
	print($ContentBody);
?>
</body>
</html>
