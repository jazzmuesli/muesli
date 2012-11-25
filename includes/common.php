<?php
$start_time = mtime();
$tmpdir = sys_get_temp_dir();

ini_set('display_errors','On');
ini_set('error_log',$tmpdir . '/muesli.log');
error_reporting(E_ALL ^ E_NOTICE);
session_start();

require 'UserManager.php';
$dsn = 'sqlite:' . $tmpdir . '/muesli.db';
$db = new PDO($dsn);
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); 
$um = new UserManager($db);
try {
	$last_minute_users = $um->userCount(60);
} catch (PDOException $e) {
	if (strpos($e->getMessage(), "no such table") !== false) {
		$_SESSION['message'] = "Created necessary tables in the database $dsn";
		$um->recreateTables();
	} else {
		throw $e;
	}

}
/**
* Microtime as double.
*/
function mtime() {
        $mtime = microtime();
        $mtime = explode(" ",$mtime);
        return $mtime[1] + $mtime[0];
}
/**
* Base URL of this website.
*/
function getBaseUrl() {
	return "http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/';
}
/**
* Redirect to $page in the same directory.
*/
function redirect($page) {
	header('Location: ' . getBaseUrl() . $page);
	exit();
}
?>
