<?php
/* An example of php file that can be include()-d by swoole http
 * server or run directly under normal web server. Note that you
 * can't use:
 * - header()
 * - anything that will cause error if executed more than once
 *   (e.g. declare function, unless you test for it first)
 * - sleep (use co:sleep instead)
 *
 * Note that in this particular implementation, to make portablity
 * between swoole and normal php easier, you can test  
 * 'defined("IN_SWOOLE")'
 */

$sleep_time = 2;

if (!function_exists("safe_sleep")) {
	function safe_sleep($sleep_time) {
		if (defined("SWS_ENABLE")) co::sleep($sleep_time);
		else sleep($sleep_time);
	}
}

if (!defined("IN_SWOOLE")) error_reporting(E_ALL);

if (isset($_GET['sleep'])) $sleep_time = $_GET['sleep'];
if (isset($_GET['text'])) {
	$body = $_GET['text'];
} else {
	$d = new DateTime();
	$body = "[".$_SERVER["REQUEST_URI"]."]";
	$body .= ": [".$d->format("Y-m-d H:i:s.u")."] [".gethostname()."] Hello World, slept ".$sleep_time."s\n";
	if (!isset($_GET['sleep'])) {
		$body .= "Use 'sleep' in query string to set sleep time (e.g. '?sleep=1')\n";
	}
}

safe_sleep($sleep_time);
echo $body;
