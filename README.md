# L4DStats - A Statistics Rendering tool for Left4Dead

[www.L4DStats.co.uk](http://www.L4DStats.co.uk)


A collection of tools for consolidating and presenting
statistics from Log files of a Left4Dead dedicated server

## Version 0.3.1 Beta


## Requirements:
- php 5.1 or later
- MySQL 5.0 or later
- Left4Dead Server Logs (sv_log_onefile 1 gives best results)

### Optional:
- .NET Framework 2.0 (Windows)
- libcurl / libstdc++ etc (Linux)

## Install Instructions: ( http://www.l4dstats.co.uk/documentation/ )
- Import the SQL structure from l4dstats.sql into your MySQL server
- Change the variables in settings.php to allow access to the DB
- Change the variables in settings.php to customise the look and feel
- If processing logs with process_logs.php put the Left4Dead logs in logs/ or specify your logs directory in settings.php
- Ensure apache has read / write access to templates_c
- Allow .htaccess Overrides if not possible set $mod_rewrite_urls to be false
- Execute process_logs.php via cron, from your browser or use the .NET tools
- Bask in the the joy of numbers

## Upgrade Instructions (0.3 -> 0.3.1)
- Make a backup of settings.php
- Replace all files with the new version
- Update the new settings.php with your original details and change any of the new settings to your requirements.
- *OPTIONAL* To be ready for 0.4 you may want to make a backup of your DB (data only) and then import the new schema and reimport the stats or re-process your logs
	
## TroubleShooting:
### 404's when Accesing /top_games/ etc
You need mod_rewrite install and to allow overrides for this virtual directory or set $mod_rewrite_urls in settings.php to be false
	
	<Directory /www/domains/l4dstats/demo/>
		Options FollowSymLinks
		AllowOverride All
	</Directory>

### process_logs.php doesn't return any output
As this is designed to be run by a cron job it doesn't return any info by default set $Debug to be true in settings.php


This is the third beta release - I expect there to be some issues. 

Please report them all including as much information as you can to my email address below.

## ToDo:
- Parse and record environment deaths / kills
- Fix any the bugs
- Identify and record which player was which character
- Improved VS support
	
## Open Source Suggestions:
- Default HTML template is in ./templates/Default.tpl
- All CSS is in ./Default.css
- Images are in ./images/
- Any of the ToDo list

## Change Log:
	See CHANGELOG.txt

- - -

L4DStats 			- Copyright 2009 - 2013 Gareth Llewellyn 
				  Gareth@NetworksAreMadeOfString.co.uk

 C# Serialization Library 	- Copyright 2004 Conversive, Inc.

 XML/SWF Charts 		- Copyright 2008 maani.us

 CSS (as of 0.3)		- Copyright 2008 Free CSS Templates

## Thanks go out to;
Mara Salami for putting up with me bringing the laptop to bed :)

Erik Ljungstrom for beta testing and pointing out the silly mistakes

Dateranoth for Beta testing / code suggestions

> L4DStats is free software; you can redistribute it and/or modify
> it under the terms of the GNU General Public License version 2,
> as published by the Free Software Foundation.
>
> This program is distributed in the hope that it will be useful,
> but WITHOUT ANY WARRANTY; without even the implied warranty of
> MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
> GNU General Public License for more details.

> You should have received a copy of the GNU General Public License
> along with this program; if not, write to the Free Software
> Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA