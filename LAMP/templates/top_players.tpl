<p>This shows a list of the top 50 players that have played on this server ranked by total kills:<p>
<div style="font-size: xlarge;">
<table cellspacing="6" cellpadding="4">
<tr><td><h2>Player Name</h2></td><td><h2>Kill Count</h2></td><td><h2>HeadShots</h2></td></tr>
{section name=Players loop=$PlayersList}
<tr>
	<td align="left"><a href="{$IndivPlayerLink}{$PlayersList[Players].PlayerID}">{$PlayersList[Players].PlayerName}</a></td>
	<td align="center">{$PlayersList[Players].TotalKills}</td>
	<td align="center">{$PlayersList[Players].HeadShots}</td>
</tr>
{/section}
</table>
</div>
