<p>This shows a list of all games that have been played on this server ranked by total kills:<p>
<table width="100%">
{section name=Games loop=$GamesList}
<tr>
<td><a href="{$IndivGameLink}{$GamesList[Games].GameID}">{$GamesList[Games].GameDesc}</a></td>
<td>{$GamesList[Games].TotalKills}</td> 
<!--<td width="200px"><img src="{$ServerRoot}/images/100.png" width="{$GamesList[Games].ScorePercent}%" alt="{$GamesList[Games].TotalKills}"></td>-->

{/section}
</table>

{$TopGamesNavLink}