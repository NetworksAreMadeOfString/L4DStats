<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>L4DStats - {$ServerName}</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link href="{$ServerRoot}/default.css" rel="stylesheet" type="text/css" media="screen" />
<!-- This is *ONLY* for the funky graphs-->
<script language="javascript">AC_FL_RunContent = 0;</script>
<script language="javascript"> DetectFlashVer = 0; </script>
<script src="{$ServerRoot}/AC_RunActiveContent.js" language="javascript"></script>
<script language="JavaScript" type="text/javascript">
<!--
var requiredMajorVersion = 9;
var requiredMinorVersion = 0;
var requiredRevision = 45;
-->
</script>
<!-- This is *ONLY* for the funky graphs-->

</head>
<body>
<!-- start header -->
<div id="wrapper">
<div id="header">
	<div id="logo">
		<h1><a href="#">L4DStats - {$ServerName}</a></h1>
		
	</div>
	<div id="rss"><a href="http://www.L4DStats.co.uk">Get L4DStats</a></div>
	<!--<div id="search">
		<form id="searchform" method="get" action="">
			<fieldset>
				<input type="text" name="s" id="s" size="15" value="" />
				<input type="submit" id="x" value="Search" />
			</fieldset>
		</form>
	</div>
	-->
</div>
<!-- end header -->
<!-- star menu -->
<div id="menu">
	<ul>
	<li class="first"><a href="{$ServerRoot}" accesskey="1">Home</a></li>
    <li><a href="{$TopGamesLink}" accesskey="2">Top Games</a></li>
    <li><a href="{$TopMoviesLink}" accesskey="3">Top Movies</a></li>
    <li><a href="{$TopPlayersLink}" accesskey="4">Top 50 Players</a></li>
    <li><a href="{$TopWeaponsLink}" accesskey="5">Top Weapons</a></li>
    <li><a href="{$AboutContactLink}" accesskey="6">About / Contact</a></li>
    <!--
	<li><a href="/top_games/" accesskey="2">Top Games</a></li>
    <li><a href="/top_players/" accesskey="3">Top 50 Players</a></li>
    <li><a href="/top_weapons/" accesskey="4">Top Weapons</a></li>
    <li><a href="/contact/" accesskey="5">About / Contact</a></li>
	-->
	</ul>
</div>
<!-- end menu -->
<!-- start page -->
<div id="page">
	<!-- start ads -->
	<div id="ads">	
		{$ContentImage}
	</div>
	<!-- end ads -->
	<!-- start content -->
	<div id="content">
		<div class="post">
			<div class="title">
				<h2>{$ContentTitle}</h2>
				<p><small>{$SmallRandom}</small></p>
			</div>
			<div class="entry">
				<!--<img src="{$ServerRoot}/images/img06.jpg" alt="" width="120" height="120" class="left" />-->
				<p>{$ContentBody}</p>
			</div>
		</div>
	</div>
	<!-- end content -->
	
	<!-- start sidebar -->
	<!--
	<div id="sidebar">
		<ul>
			<li id="categories">
				<h2>{$ContentNavTitle}</h2>
				<ul>
					{$NavContent}
				</ul>
			</li>
			<li>
				<h2>Random Statistic:</h2>
				<ul> 
				<li><img src="{$ServerRoot}/images/img6.jpg" alt="" width="179" height="59" /></li>
				
					<li>{$RandomStat}</li>
				</ul>
			</li>
		</ul>
	</div>
	-->
	<!-- end sidebar -->
</div>
<!-- end page -->
<!-- start footer -->
<div id="footer">
	<p class="legal">
		Except where otherwise noted, content on this site is licensed under the GPL 2.0 License<br>
		A <A HREF="http://www.NetworksAreMadeOfString.co.uk">NetworksAreMadeOfString</a> Application - 
		&nbsp;&nbsp;&bull;&nbsp;&nbsp;
		Design by <a href="http://www.freecsstemplates.org/">Free CSS Templates</a>
		&nbsp;&nbsp;&bull;&nbsp;&nbsp;
		</p>
</div>
</div>
<!-- end footer -->
</body>
</html>