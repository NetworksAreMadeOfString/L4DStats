Headshot kills are shown in [ ] brackets.<br>
<table>
	<tr>
		<td width="200" valign="top">
			<h2>Weapons</h2>
			<table>
				{section name=Player loop=$PlayerWeapons}
					<tr>
				        <td width="190px" height="110px" style="background-image: url({$ServerRoot}/images/{$PlayerWeapons[Player].Weapon}.png); background-repeat: no-repeat; text-align: center;" valign="middle"><span style="font-size: xxlarge; font-weight: 1000;">{$PlayerWeapons[Player].Kills} {$PlayerWeapons[Player].HeadShots}</span></td>
					</tr>
				{/section}
			</table>
		</td>
		<td valign="top">
			<table>
			<tr>
				<td colspan="2"><h2>Movie Stats</h2></td>
			</tr>
			{section name=Movie loop=$PlayerMovies}
					<tr>
				        <td>{$PlayerMovies[Movie].GameName}&nbsp;&nbsp;&nbsp;</td> <td>{$PlayerMovies[Movie].Kills}</td>
					</tr>
			{/section} 

			<tr><td></td><td></td></tr>
			<tr><td></td><td></td></tr>
			
			<tr>
				<td colspan="2"><h2>Best Fellow Survivors</h2></td>
			</tr>
			<td colspan="2">
				{section name=Survivor loop=$BestSurvivors}
					<tr>
				        <td colspan="2"><a href="{$IndivPlayerLink}{$BestSurvivors[Survivor].SurvivorID}">{$BestSurvivors[Survivor].SurvivorName}</a></td>
					</tr>
				{/section} 
			</td>
			
			<tr><td></td><td></td></tr>
			<tr><td></td><td></td></tr>
			
			<tr>
				<td colspan="2"><h2>Worst Enemies</h2></td>
			</tr>
			<td colspan="2">
				{section name=Enemy loop=$WorstEnemies}
					<tr>
				        <td valign="top">({$WorstEnemies[Enemy].Deaths}) <a href="{$IndivPlayerLink}{$WorstEnemies[Enemy].EnemyID}">{$WorstEnemies[Enemy].EnemyName}</a></td>
				        <td>{$WorstEnemies[Enemy].Weapons}<br></td>
					</tr>
			{/section} 
			</td>
			
			</table>
		</td>
	</tr>
</table>

{$IndivKillsOverTimeGraph}
<br>
{$IndivKillsPerMapGraph}