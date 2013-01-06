<table>
{section name=Movie loop=$TopMovies}
	<tr>
        <td colspan="2"><h2>{$TopMovies[Movie].MovieName}</h2></td>
    </tr>
    <tr>
        <td> <img src="{$ServerRoot}/images/{$TopMovies[Movie].MapMatch}.png" valign="middle"></td><td valign="middle"><h3 style="font-size: xx-large;">{$TopMovies[Movie].Kills}</h3></td>
	</tr>
	<tr><td colspan="2"></td></tr>
{/section}
</table>
