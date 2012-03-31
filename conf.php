<?php

$scraper_conf = [];

// Location of scrape file to download
$scraper_conf['uri']		=	'compress.bzip2://http://publicbt.com/all.txt.bz2';

// Database update method: INSERT, UPDATE, or DUMMY (for no updates)
$scraper_conf['mode']		=	'DUMMY';

// Database config (see http://php.net/pdo.construct )
$scraper_conf['db_dsn']		=	'pgsql:dbname=MYDBNAME;host=localhost';
$scraper_conf['db_user']	=	'MYDBUSER';
$scraper_conf['db_pass']	=	'MYDBPASS';

// Verbosity
// 0 = no output, except errors
// 9 = full output
$scraper_conf['v']			=	2;


// Version name/number
$scraper_conf['version']	=	file_get_contents(dirname(__FILE__).'/VERSION');

// Be nice to web browsers
if (!defined('STDIN')) {
	header('Content-Type: text/plain');
	set_time_limit(0);
}
