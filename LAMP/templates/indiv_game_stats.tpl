<p>This page shows all the statistics for this game!</p>

<h2>Player Matrix</h2>
<table cellpadding="4">
<tr style="font-weight: bold;">
<!--<td align="center">Player</td></td>
<td align="center">Auto Shotgun</td></td>
<td align="center">Dual Pistols</td>
<td align="center">Infected</td>
<td align="center">Rifle</td>
<td align="center">Sniper Rifle</td>
<td align="center">Inferno</td>
<td align="center">Pipe Bomb</td>
<td align="center">Pistol</td>
<td align="center">SMG</td>
<td align="center">Pump Shotgun</td>
<td align="center">Mini Gun</td>
<td align="center">Tank Claw</td>
<td align="center">Hunter Claw</td>
<td align="center">Molotov</td>
<td align="center">Tank Rock</td>
<td align="center">Smoker Claw</td>
-->
<td align="center"><img src="{$ServerRoot}/images/player_label.png"></td></td>
<td align="center"><img src="{$ServerRoot}/images/pistol_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/dual_pistols_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/smg_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/pump_shotgun_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/auto_shotgun_90_label.png"></td></td>
<td align="center"><img src="{$ServerRoot}/images/rifle_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/hunting_rifle_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/inferno_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/molotov_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/pipe_bomb_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/first_aid_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/pain_pills_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/minigun_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/infected_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/tank_claw_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/hunter_claw_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/tank_rock_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/smoker_claw_90_label.png"></td>
<td align="center"><img src="{$ServerRoot}/images/boomer_claw_90_label.png"></td>

</tr>
{section name=Player loop=$PlayerStats}
<tr>
<td style="font-weight: bold;"><a href="{$ServerRoot}{$IndivPlayerLink}{$PlayerStats[Player].ID}">{$PlayerStats[Player].PlayerName}</a></td></td>
<td align="center">{$PlayerStats[Player].pistol|default:"-"}</td>
<td align="center">{$PlayerStats[Player].dual_pistols|default:"-"}</td>
<td align="center">{$PlayerStats[Player].smg|default:"-"}</td>
<td align="center">{$PlayerStats[Player].pumpshotgun|default:"-"}</td>
<td align="center">{$PlayerStats[Player].autoshotgun|default:"-"}</td></td>
<td align="center">{$PlayerStats[Player].rifle|default:"-"}</td>
<td align="center">{$PlayerStats[Player].hunting_rifle|default:"-"}</td>
<td align="center">{$PlayerStats[Player].inferno|default:"-"}</td>
<td align="center">{$PlayerStats[Player].molotov|default:"-"}</td>
<td align="center">{$PlayerStats[Player].pipe_bomb|default:"-"}</td>
<td align="center">{$PlayerStats[Player].first_aid_kit|default:"-"}</td>
<td align="center">{$PlayerStats[Player].pain_pills|default:"-"}</td>
<td align="center">{$PlayerStats[Player].prop_minigun|default:"-"}</td>
<td align="center">{$PlayerStats[Player].infected|default:"-"}</td>
<td align="center">{$PlayerStats[Player].tank_claw|default:"-"}</td>
<td align="center">{$PlayerStats[Player].hunter_claw|default:"-"}</td>
<td align="center">{$PlayerStats[Player].tank_rock|default:"-"}</td>
<td align="center">{$PlayerStats[Player].smoker_claw|default:"-"}</td>
<td align="center">{$PlayerStats[Player].boomer_claw|default:"-"}</td>

</tr>
{/section}
</table>

<br>
<div>
	<div style="float:left; width:300px">
		<h2>Total Weapon Stats:</h2>
		<table cellspacing="2" cellpadding="2">
			<tr><td>Auto Shotgun:</td><td> {$autoshotgun|default:"0"}</td>
			<tr><td>Dual Pistols:</td><td>{$dual_pistols|default:"0"}</td>
			<tr><td>Infected Mob:</td><td>{$infected|default:"0"}</td>
			<tr><td>Assualt Rifle:</td><td>{$rifle|default:"0"}</td>
			<tr><td>Flames:</td><td>{$entityflame|default:"0"}</td>
			<tr><td>Sniper Rifle:</td><td>{$hunting_rifle|default:"0"}</td>
			<tr><td>An Inferno:</td><td>{$inferno|default:"0"}</td>
			<tr><td>Pipe Bomb:</td><td>{$pipe_bomb|default:"0"}</td>
			<tr><td>Pistol:</td><td>{$pistol|default:"0"}</td>
			<tr><td>SMG:</td><td>{$smg|default:"0"}</td>
			<tr><td>Fire:</td><td>{$env_fire|default:"0"}</td>
			<tr><td>Pump Shotgun</td><td>{$pumpshotgun|default:"0"}</td>
			<tr><td>Mounted Machine Gun&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>{$prop_minigun|default:"0"}</td>
			<tr><td>Scripted Deaths:</td><td>{$trigger_hurt|default:"0"}</td>
			<tr><td>Tank Attack:</td><td>{$tank_claw|default:"0"}</td>
			<tr><td>Hunter Attack:</td><td>{$hunter_claw|default:"0"}</td>
			<tr><td>Witch Attack:</td><td>{$witch|default:"0"}</td>
			<tr><td>Pills Melee:</td><td>{$pain_pills|default:"0"}</td>
			<tr><td>Suicide:</td><td>{$player|default:"0"}</td>
			<tr><td>Molotovs:</td><td>{$molotov|default:"0"}</td>
			<tr><td>Generic Explosion:</td><td>{$env_explosion|default:"0"}</td>
			<tr><td>Oxygen Tank Trap:</td><td>{$oxygentank|default:"0"}</td>
			<tr><td>Propane Tank Trap:</td><td>{$propanetank|default:"0"}</td>
			<tr><td>Tanks Rock Throw:</td><td>{$tank_rock|default:"0"}</td>
			<tr><td>Smoker Attack:</td><td>{$smoker_claw|default:"0"}</td>
			<tr><td>A Ghost!:</td><td>{$trigger_hurt_ghost|default:"0"}</td>
			<tr><td>Scenary:</td><td>{$prop_door_rotating_checkpoint|default:"0"}</td>
			<tr><td>The World:</td><td>{$world|default:"0"}</td>
			<tr><td>Boomer Claw Attack:</td><td>{$boomer_claw|default:"0"}</td>
			<tr><td>Boomer Other:</td><td>{$boomer|default:"0"}</td>
			<tr><td>Telefrag:</td><td>{$worldspawn|default:"0"}</td>
			<tr><td>First Aid Kit Melee:</td><td>{$first_aid_kit|default:"0"}</td>
			<tr><td>Physics Engine:</td><td>{$prop_physics|default:"0"}</td>
			<tr><td></td><td></td></tr>
			<tr><td><h2>Total Kills:</h2></td><td align="right"><h2>{$TotalKills}</h2></td>
		</table>
	</div>
	<div style="float:right; width:300px">
	{$PlayerKillGraph}
	{$WeaponDistribution}
	{$HeadShots}
	</div>
	
	{$KillsOverTime}
</div>
