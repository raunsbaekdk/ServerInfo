<?php
class ServerInfo
{
	private static function uptime()
	{
		$uptime = strtok(exec('cat /proc/uptime'), '.');


		return array(
			'days' => sprintf("%2d", ($uptime / (3600*24))),
			'hours' => sprintf("%2d", (($uptime % (3600*24)) / 3600)),
			'minutes' => sprintf("%2d", ($uptime % (3600*24) % 3600) / 60),
			'seconds' => sprintf("%2d", ($uptime % (3600*24) % 3600) % 60)
		);
	}


	private static function memory()
	{
		$results['ram'] = array('total' => 0, 'free' => 0, 'used' => 0, 'percent' => 0);


		$buffed = explode("\n", shell_exec('cat /proc/meminfo'));


		foreach($buffed as $buffer)
		{
			if(preg_match('/^MemTotal:\s+(.*)\s*kB/i', $buffer, $bufferMatched)) {
				$results['ram']['total'] = $bufferMatched['1'];
			}elseif(preg_match('/^MemFree:\s+(.*)\s*kB/i', $buffer, $bufferMatched)) {
				$results['ram']['free'] = $bufferMatched['1'];
			}
		}


		$results['ram']['used'] = $results['ram']['total'] - $results['ram']['free'];
		$results['ram']['percent'] = round(($results['ram']['used'] * 100) / $results['ram']['total']);


		return($results);
	}


	private static function users()
	{
		$users = preg_split('/=/', shell_exec('who -q'));


		return((int) $users['1']);
	}

	private static function load()
	{
		$load = preg_split("/\s/", shell_exec('cat /proc/loadavg'), 4);
		unset($load['3']);


		return( implode(' ', $load) );
	}


	private static function convertSize($size)
	{
		$size = $size * 1024;
		$filesizename = array('Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');


		return $size ? number_format(round($size / pow(1024, ($i = floor(log($size, 1024)))), 2), 2, ',', '') . ' ' . $filesizename[$i] : '0 Bytes';
	}


	public static function info()
	{
		// Overview
		$info['servername'] = $_SERVER['SERVER_NAME'];
		$info['serverport'] = $_SERVER['SERVER_PORT'];
		$info['date'] = date('d/m-Y H:i:s');
		$info['author'] = '<a href="http://URL">DIT NAVN</a>';


		// Restart
		$info['uptime'] = self::uptime();
		// Users
		$info['users'] = self::users('users');
		// Load
		$info['load'] = self::load('load');


		// Memory
		$memory = self::memory();
		$info['ram']['percent'] = $memory['ram']['percent'];
		$info['ram']['free'] = self::convertSize($memory['ram']['free']);
		$info['ram']['used'] = self::convertSize($memory['ram']['used']);
		$info['ram']['total'] = self::convertSize($memory['ram']['total']);


		// User infos
		$info['ip'] = (getenv('HTTP_X_FORWARDED_FOR') ? getenv('HTTP_X_FORWARDED_FOR') : getenv('REMOTE_ADDR'));
		$info['ua'] = $_SERVER['HTTP_USER_AGENT'];


		// Return
		return $info;
	}
}


// Info
$info = ServerInfo::info();


// HTML
?><!DOCTYPE html>
<html>
<head>
	<title>Servour</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.no-icons.min.css" rel="stylesheet" />
	<style type="text/css">body{background:#e7e7e7;padding:35px}@media(min-width:0) and (max-width:992px){body{padding:10px}}.container{max-width:900px;background:#fff;padding-left:25px;padding-right:25px;border:1px solid #ccc}.container>hr{margin:20px 0}</style>
</head>
<body>
	<div class="container">
		<hr />
		<div class="row">
			<div class="col-sm-6">
				<h4>Klient IP</h4>
				<p><?php echo $info['ip']; ?></p>
			</div>
			<div class="col-sm-6">
				<h4>Klient browser</h4>
				<p><?php echo $info['ua']; ?></p>
			</div>
		</div>
		<br />
		<div class="row">
			<div class="col-sm-6">
				<h4>Sidst genstartet</h4>
				<p><?php echo $info['uptime']['days'] . ' dag(e)' . ', ' . $info['uptime']['hours'] . ' time(r)' . ', ' . $info['uptime']['minutes'] . ' minut(ter)' . ', ' . $info['uptime']['seconds'] . ' sekund(er)'; ?></p>
			</div>
			<div class="col-sm-6">
				<h4>Brugere logget ind</h4>
				<p><?php echo $info['users']; ?></p>
			</div>
		</div>
		<br />
		<div class="row">
			<div class="col-sm-6">
				<h4>Load</h4>
				<p><?php echo $info['load']; ?></p>
			</div>
			<div class="col-sm-6">
				<h4>Hukommelsesforbrug</h4>
				<p><?php echo $info['ram']['percent'] . '% brugt - Fri ' . $info['ram']['free'] . ', brugt ' . $info['ram']['used'] . ', total ' . $info['ram']['total']; ?></p>
			</div>
		</div>
		<hr />
		<div class="footer">
			<p>&copy; 2013 Server - <?php echo $info['date']; ?> - <?php echo $info['author']; ?></p>
		</div>
		<hr />
	</div>
</body>
</html>
