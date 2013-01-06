{literal}
<script language="JavaScript" type="text/javascript">

if (AC_FL_RunContent == 0 || DetectFlashVer == 0) 
{
	alert("This page requires AC_RunActiveContent.js.");
} 
else 
{
	var hasRightVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);
	if(hasRightVersion) { 
		AC_FL_RunContent(
			'codebase', 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,45,0',
			'width', '800',
			'height', '300',
			'scale', 'noscale',
			'salign', 'TL',
			'bgcolor', '#777788',
			'wmode', 'transparent',
			{/literal}
			'movie', '{$xmlswfpath}charts',
			'src', '{$xmlswfpath}charts',
			'FlashVars', 'library_path={$xmlswfpath}charts_library&xml_source={$xmlswfpath}xmlgraphs.php?Graph=IndivKillsOverTimeGraph%26PlayerID%3D{$PlayerID}', {literal}
			'id', 'my_chart',
			'name', 'my_chart',
			'menu', 'true',
			'allowFullScreen', 'true',
			'allowScriptAccess','sameDomain',
			'quality', 'high',
			'align', 'middle',
			'pluginspage', 'http://www.macromedia.com/go/getflashplayer',
			'play', 'true',
			'devicefont', 'false'
			); 
	} 
	else 
	{ 
		var alternateContent = 'This content requires the Adobe Flash Player. '
		+ '<u><a href=http://www.macromedia.com/go/getflash/>Get Flash</a></u>.';
		document.write(alternateContent); 
	}
}
</script>
{/literal}