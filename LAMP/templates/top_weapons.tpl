<table>
{section name=Player loop=$PlayerWeapons}
	<tr>
        <td width="190px" height="110px" style="background-image: url({$ServerRoot}/images/{$PlayerWeapons[Player].Weapon}.png); background-repeat: no-repeat; text-align: center;" valign="middle"><span style="font-size: xxlarge; font-weight: 1000;">{$PlayerWeapons[Player].Kills} {$PlayerWeapons[Player].HeadShots}</span></td>
	</tr>
{/section}
</table>
