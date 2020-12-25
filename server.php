<?php
error_reporting(E_ALL);
define("LISTEN_ADDRESS", "0.0.0.0");
define("LISTEN_PORT", 8080);
define("MEMORY_LIMIT", "1G");
$sleep_time = 2;

ini_set('memory_limit', MEMORY_LIMIT);
$http = new Swoole\HTTP\Server(LISTEN_ADDRESS, LISTEN_PORT, SWOOLE_BASE);
$http->on('request', function ($request, $response) use ($sleep_time) {
	if (isset($request->get['sleep'])) $sleep_time = $request->get['sleep'];
	if (isset($request->get['text'])) {
		$body = $request->get['text'];
	} else { 
		$d = new DateTime();
		$body = "[".$request->server["request_uri"];
		if (isset($request->server["query_string"])) $body .= "?".$request->server["query_string"];
		$body .= "]: [".$d->format("Y-m-d H:i:s.u")."] [".gethostname()."] Hello World, slept ".$sleep_time."s\n";
		if (!isset($request->get['sleep'])) {
			$body .= "Use 'sleep' in query string to set sleep time (e.g. '?sleep=1')\n";
		}
	}
    if ($sleep_time) co::sleep($sleep_time);
    $response->end($body);
});

$http->start();
