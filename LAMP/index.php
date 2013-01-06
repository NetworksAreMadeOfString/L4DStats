<?php
/*==============================================================
L4DStats - Open Source Left4Dead Stats App

Copyright (c) 2009 Gareth Llewellyn

This program is free software, distributed under the terms of
the GNU General Public License Version 2. See the LICENSE file
at the top of the source tree.

================================================================*/

/// index.php
/// Where all the magic happens

include('./libs/Smarty.class.php');
include('./L4DStats.class.php');
include('./settings.php');

$Stats = new L4DStats();
$smarty = new Smarty;
$URLPath = $_SERVER['REQUEST_URI'];

if($mod_rewrite_urls == true)
{
	$Pages = explode("/",$URLPath);
	$smarty->assign('TopGamesLink',"/top_games/");
	$smarty->assign('TopPlayersLink',"/top_players/");
	$smarty->assign('TopWeaponsLink',"/top_weapons/");
	$smarty->assign('AboutContactLink',"/contact/");
	$smarty->assign('TopMoviesLink',"/top_movies/");
}
else
{
	$smarty->assign('TopGamesLink',"index.php?page=top_games");
	$smarty->assign('TopPlayersLink',"index.php?page=top_players");
	$smarty->assign('TopWeaponsLink',"index.php?page=top_weapons");
	$smarty->assign('AboutContactLink',"index.php?page=contact");
	$smarty->assign('TopMoviesLink',"index.php?page=top_movies");
	
	$Pages[0] = $_SERVER['REQUEST_URI'];
	if(isset($_GET['page']))
	{
		$Pages[1] = str_replace("\\","",$_GET['page']);
		
		if(isset($_GET['subpage']))
		{
			$Pages[2] = str_replace("\\","",$_GET['subpage']);
		}
		else 
		{
			$Pages[2] = "";
		}
	}
	else 
	{
		$Pages[1] = "";
	}
	
	
}

$ContentImage = "<img src=\"$ServerRoot/images/ad.gif\">";

//These always gets set:
$smarty->assign('RandomStat',$Stats->GetRandomStat());
$smarty->assign('ServerName',$ServerName);
$smarty->assign('ServerRoot',$ServerRoot);
$smarty->assign('SmallRandom',$Stats->GetRandomStat());


if(count($Pages) > 2)
{
	switch($Pages[1])
	{
		case "top_games":
			{
				$NextPage = $Pages[2] + 1;
				
				if($mod_rewrite_urls == true)
				{
					$smarty->assign('IndivGameLink', "/game/");
					$smarty->assign('TopGamesNavLink', "<br><a href=\"$ServerRoot/top_games/$NextPage\">Next 50 &gt; </a>");
				}
				else 
				{
					$smarty->assign('IndivGameLink', "?page=game&subpage=");
					$smarty->assign('TopGamesNavLink', "<br><a href=\"?page=top_games&subpage=$NextPage\">Next 50 &gt;</a>");
				}
				
				$smarty->assign('ContentTitle',"Stats Listed By Game");
				$smarty->assign('GamesList',$Stats->FormatTopGames($Stats->GetTopGames($Pages[2])));
				//$smarty->assign('GamesList',$Stats->GetTopGames());
				$smarty->assign('ContentBody',$smarty->fetch('top_games.tpl'));
				$smarty->assign('ContentNavTitle',"Main Navigation");
				$smarty->assign('NavContent',"<li>Most Kills</li>
                                                                        <li>Least Kills</li>
                                                                        <li>Least Bots</li>");

			}
			break;

		case "top_players":
			{
				$smarty->assign('ContentTitle',"Stats Listed By Player");
				if($mod_rewrite_urls == true)
				{
				$smarty->assign('IndivPlayerLink', "/player/");
				}
				else 
				{
					$smarty->assign('IndivPlayerLink', "?page=player&subpage=");
				}
				$smarty->assign('PlayersList',$Stats->FormatTopPlayers($Stats->GetTopPlayers($Show_AI_Stats)));
				$smarty->assign('ContentBody',$smarty->fetch('top_players.tpl'));

				$smarty->assign('ContentNavTitle',"Main Navigation");
				$smarty->assign('NavContent',"<li>Most Kills</li>
									<li>Least Deaths</li>
									<li>Best with Pistols</li>");
			}
			break;

		case "top_weapons":
			{
				$smarty->assign('PlayerWeapons',$Stats->GetTopWeapons());
				$smarty->assign('ContentBody',$smarty->fetch('top_weapons.tpl'));
			
				$smarty->assign('ContentTitle',"Stats Listed By Weapons");
				$smarty->assign('ContentNavTitle',"Weapon Stats");
				$smarty->assign('NavContent',"<li>Level 1</li>
											<li>Level 2</li>
											<li>PyroTechnics</li>
											<li>Special</li>
											");
			}
			break;
			
		case "top_movies":
			{
				foreach($Stats->GetTopMovies() as $MapMatch => $Kills)
				{
					$TopMovies[] = array('MapMatch' => $MapMatch,
										'MovieName' => $Stats->FormatMovieNameFromMapMatch($MapMatch),
										'Kills' => $Kills);
				}
				$smarty->assign('TopMovies',$TopMovies);
				$smarty->assign('ContentBody',$smarty->fetch('top_movies.tpl'));

				$smarty->assign('ContentTitle',"Stats Listed By Movie");
			}
			break;

		case "contact":
			{
				$smarty->assign('ContentTitle',"About / Contact Server Admin");
				$smarty->assign('Admin',$AdminName);
				$smarty->assign('AdminEmail',$AdminEmail);
				$smarty->assign('ServerDesc',$ServerDescripton);
				$smarty->assign('ContentBody',$smarty->fetch('contact.tpl'));
				$smarty->assign('ContentNavTitle',"Main Navigation");
				$smarty->assign('NavContent',"<li><a href=\"http://www.Networksaremadeofstring.co.uk\">NetworksAreMadeOfString</a></li>");
			}
			break;

		case "game":
			{
				$smarty->assign('ContentNavTitle',"Stats Navigation");
				$smarty->assign('NavContent',"<li>By Weapon</li>
								<li>By Player</li>
								<li>Matrix</li>
								");	

				$smarty->assign('ContentTitle',$Stats->GetTitleForIndivGame($Pages[2]));
				$GameStats = $Stats->GetGameStats($Pages[2],$Show_AI_Stats);
				$ContentImage = "<img src=\"$ServerRoot/images/".$Stats->GetMapImageFromGameID($Pages[2]).".png\" style=\"right\">";
				
				$i = 0;
				foreach($GameStats['PlayerStats'] as $Player => $Weapons)
				{
					//print_r($GameStats['PlayerStats']);
					$Players[$i]['PlayerName'] = $Player;
					
					foreach($Weapons as $Weapon => $KillCount)
					{
						$Players[$i][$Weapon] = $KillCount;
					}
					$i++;
				}
				
				$smarty->assign('PlayerStats',$Players);

				foreach($GameStats['WeaponStats'] as $Weapon => $Kills)
				{
					$smarty->assign($Weapon, $Kills);
				}

				$smarty->assign('TotalKills', $GameStats['TotalKills']);
				$smarty->assign('GameID',$Pages[2]);
				
				if($mod_rewrite_urls == true)
				{
					$smarty->assign('xmlswfpath', "../../");
					$smarty->assign('IndivPlayerLink', "/player/");
				}
				else 
				{
					$smarty->assign('xmlswfpath', "");
					$smarty->assign('IndivPlayerLink', "?page=player&subpage=");
				}
				
				$smarty->assign('PlayerKillGraph',$smarty->fetch('xmlcharts/indivgamekills.inc'));
				$smarty->assign('WeaponDistribution',$smarty->fetch('xmlcharts/indivgameweapons.inc'));
				$smarty->assign('HeadShots',$smarty->fetch('xmlcharts/indivgameheadshots.inc'));
				$smarty->assign('KillsOverTime',$smarty->fetch('xmlcharts/indivgamekillsovertime.inc'));
				$smarty->assign('ContentBody',$smarty->fetch('indiv_game_stats.tpl'));
			}
			break;

		case "player":
			{
				$smarty->assign('ContentNavTitle',"Stats Navigation");
				$smarty->assign('NavContent',"<li>Weapons</li>
                                                                <li>Best Date</li>
                                                                <li>Matrix</li>
                                                                ");
				$PlayerName = $Stats->GetPlayerName($Pages[2]);
				$smarty->assign('PlayerID',$Pages[2]);
				$smarty->assign('PlayerName',$PlayerName);
				$smarty->assign('ContentTitle',"Server Stats for ".$PlayerName);
				
				//Weapons
				$smarty->assign('PlayerWeapons',$Stats->GetPlayerStats($Pages[2]));
				
				//Movie Stats
				$MovieKills = $Stats->GetBestPlayerMovies($Pages[2]);
				arsort($MovieKills);
				foreach ($MovieKills as $Map => $Kills)
				{
					$KillsPerMovie[] = array('GameName' => $Map,
											'Kills' => (int)$Kills);
				}
				$smarty->assign('PlayerMovies',$KillsPerMovie);
				
				
				//Best Fellow Survivors
				if($mod_rewrite_urls == true)
				{
					$smarty->assign('IndivPlayerLink', "/player/");
					$smarty->assign('xmlswfpath', "../../");
				}
				else 
				{
					$smarty->assign('IndivPlayerLink', "?page=player&subpage=");
					$smarty->assign('xmlswfpath', "");
				}
				
				$BestSurviors = $Stats->GetBestFellowSurvivors($Pages[2],$PlayerName);
				$smarty->assign('BestSurvivors',$BestSurviors);
				
				//Players Worst Enemies
				$smarty->assign('WorstEnemies', $Stats->GetPlayersEnemies($Pages[2]));
				
				//Graphs
				$smarty->assign('IndivKillsOverTimeGraph',$smarty->fetch('xmlcharts/IndivKillsOverTimeGraph.inc'));
				$smarty->assign('IndivKillsPerMapGraph',$smarty->fetch('xmlcharts/IndivKillsPerMapGraph.inc'));
				
				$smarty->assign('ContentBody',$smarty->fetch('indiv_player_stats.tpl'));

			}
			break;

		default:
			{
				$smarty->assign('ContentNavTitle',"Main Navigation");
				$smarty->assign('ContentTitle',"Page Parsing Error");
				$smarty->assign('ContentBody',"There has been an error parsing the URL / GET values. Try going back to the <a href=\"$ServerRoot\">start</a>");
			}
			break;
	}

}
else
{
	// Change this to reflect how many days of stats you want to display
	$GameCount = 10;
	
	$TodaysStats = $Stats->GetRecentStats($GameCount);
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
		$PlayerDetails = explode("|",$PlayerName);
		
		if($mod_rewrite_urls == true)
		{
			$pathPage = "$ServerRoot/player/";
		}
		else 
		{
			$pathPage = "$ServerRoot?page=player&subpage=";
		}
		
		$ContentBody .= "<tr><td><a href=\"$pathPage$PlayerDetails[1]\">$PlayerDetails[0]</a></td><td>$Kills</td></tr>";
	}
	
	$ContentBody .= "</table>";
	$ContentBody .= "</td></tr>";
	$ContentBody .= "<tr><td align=\"middle\" colspan=\"5\">".$smarty->fetch('xmlcharts/past10kills.inc')."</td></tr>";
	$ContentBody .= "<tr><td align=\"middle\" colspan=\"5\">".$smarty->fetch('xmlcharts/past10weapons.inc')."</td></tr>";
	$ContentBody .= "</table>";
	
	//$smarty->assign('SecondaryContent', $smarty->fetch('xmlcharts/xmlChartInc.inc'));

	$smarty->assign('ContentNavTitle',"Main Navigation");
	$smarty->assign('ContentTitle',"Recent Gameplay Stats");
	$smarty->assign('ContentBody',$ContentBody);



}
$smarty->assign('ContentImage',$ContentImage);
//Display the 'text shell' template.
$smarty->display('Default.tpl');


?>
