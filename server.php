<?php
error_reporting(E_ALL);
define("SWS_ENABLE", true);
define("SWS_LISTEN", ["ADDRESS"=>"0.0.0.0", "PORT"=>8080]);
define("SWS_LIMIT", ["MEMORY"=>"1G"]);
define("SWS_HTML", __DIR__."/html");
define("SWS_INDEX", SWS_HTML."/index.php");
define("SWS_HELPER", __DIR__."/helper.php");
define("SWS_HEADER", ["SERVER"=>"nunya"]);

if(file_exists(SWS_HELPER)) require_once(SWS_HELPER);
ini_set('memory_limit', SWS_LIMIT["MEMORY"]);
if (is_dir(SWS_HTML)) chdir(SWS_HTML);

$sws_server = new Swoole\HTTP\Server(SWS_LISTEN["ADDRESS"], SWS_LISTEN["PORT"], SWOOLE_BASE);

function swsCopyRequestInfo($request) {
	$_SERVER['REQUEST_URI'] = $request->server['request_uri'];
	if (isset($request->server["query_string"])) $_SERVER['REQUEST_URI'] .= "?".$request->server["query_string"];
	$_GET = $request->get;
	$_POST = $request->post;
}
function swsGetFileInfo($request) {
	if (is_string($request)) $path = realpath($request);
	else $path = realpath(SWS_HTML.$request->server['request_uri']);

	if ($path === false) return false;
	if (is_dir($path)) return swsGetFileInfo($path."/index.php");
	$file['path'] = $path;
	$file['type'] = mime_content_type($path);
	$file['size'] = filesize($path);
	$file['modified'] = filemtime($path);
	return $file;
}
function swsCheckDefaultIndex($request, $response) {
	if (($request->server['request_uri'] == "/") && !is_dir(SWS_HTML)) {
	    $response->header('Content-Type', "text/plain");
		$body = "[".date("Y-m-d H:i:s")."] [".gethostname()."] Swoole HTTP server.\n";
		$body .= "To change this text, create '".SWS_INDEX."'.\n";
	    $response->end($body);
		return true;
	} else return false;
}
if (!function_exists("swsCounterResponse")) {
	function swsCounterResponse($ok) {
		return $ok;
	}
}

$sws_server->on("start", function ($server) {
    printf("HTTP server started at %s:%s\n", $server->host, $server->port);
    printf("Master PID: %d\n\n", $server->master_pid);
});
$sws_server->on('request', function ($request, $response) {
    $response->header('Server', SWS_HEADER["SERVER"]);
	if (swsCheckDefaultIndex($request, $response)) return;

	ob_start();
	$file=swsGetFileInfo($request);
	if (!$file) {
		$response->status("404");
	    $response->header('Content-Type', "text/plain");
		$response->write("nothing here\n");
	    $response->end();
		$swsResponseOK = false;
	} elseif ($file['type'] != 'text/x-php') {
	    $response->header('Content-Type', $file['type']);
	    $response->header('Last-Modified', gmdate('D, d M Y H:i:s', $file['modified']) . ' GMT');
	    $response->sendfile($file['path']);
		$swsResponseOK = true;
	} else {
		swsCopyRequestInfo($request);
		ob_start();
		include($file['path']);
		if ($body=ob_get_clean()) $response->write($body);
	    $response->end();
		$swsResponseOK = true;
	}
	swsCounterResponse($swsResponseOK);
});

$sws_server->start();
