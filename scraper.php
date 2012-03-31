<?php

require dirname(__FILE__).'/conf.php';

if ($scraper_conf['v'])
echo PHP_EOL.$scraper_conf['version'].PHP_EOL;

$request_start = microtime(1);

if (in_array($scraper_conf['mode'],['INSERT','UPDATE'])) {
	if ($scraper_conf['v'])
		echo "connecting to database (".$scraper_conf['db_dsn'].")...";
	try {
		$db = new PDO($scraper_conf['db_dsn'], $scraper_conf['db_user'], $scraper_conf['db_pass']);
	}
	catch (PDOException $e) {
		die('Database Offline: '.$e->getMessage().PHP_EOL);
	}
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	if ($scraper_conf['v'])
		echo round(microtime(1) - $request_start,3)." s".PHP_EOL;
}
else $db = NULL;

$request_start = microtime(1);

if ($scraper_conf['v'])
	echo "connecting to scrape server (".$scraper_conf['uri'].")...";

$fh = fopen($scraper_conf['uri'], 'rb');

if ($scraper_conf['v'])
	echo round(microtime(1) - $request_start,3)." s".PHP_EOL;

if ($scraper_conf['mode']=='INSERT') {
	$st = $db->prepare("
INSERT INTO 
	torrents
(
	seeds
	,leeches
	,infohash
)
VALUES
(
	?
	,?
	,decode(?, 'hex')
)
");
}
elseif ($scraper_conf['mode']=='UPDATE') {
	$st = $db->prepare("
UPDATE
	torrents
SET
	seeds = ?
	,leeches = ?
	,last_scrape = NOW()
WHERE
	infohash = decode(?, 'hex')
");
}
else {
	if ($scraper_conf['v'])
		echo "dummy mode: skipping database updates".PHP_EOL;
	$st = NULL;
}


$batch_start = $request_start = microtime(1);

if ($scraper_conf['v'])
	echo "reading scrape file...".PHP_EOL;

$n = 0;

while ($line = fgets($fh)) {

	$n++;
		
	$t = explode(':',$line);
	
	if ($scraper_conf['v']==9)
		echo $n.'... '.current(unpack('H*',urldecode($t[0])))." = ".(int)$t[1]." = ".(int)$t[2].PHP_EOL;
	
	elseif ($scraper_conf['v']>2)
		echo '.';
	
	if ($st)
	$st->execute([
		$t[1]
		,$t[2]
//		,urldecode($t[0])	// gives errors in postgres (not escaped properly?)
		,current(unpack('H*',urldecode($t[0]))) 	// encoding in HEX works better
	]);
	
	
	if ($scraper_conf['v']>1 && $n%10000==0) {
		$batch_time = microtime(1) - $batch_start;
		if ($scraper_conf['v']>2) echo PHP_EOL;
			echo "scraped $n torrents in ".round(microtime(1) - $request_start)." seconds (".round(10000/$batch_time)." t/s)".PHP_EOL;
		$batch_start = microtime(1);
	}
	
}

$scrape_time = microtime(1) - $request_start;

if ($scraper_conf['v'])
	echo "done!".PHP_EOL;

if ($scraper_conf['v'])
	echo "scraped $n torrents in ".round($scrape_time)." seconds (".round($n/$scrape_time,2)." t/s)".PHP_EOL.PHP_EOL;
