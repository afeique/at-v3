<?php

require '../config.php';
require LIB_DIR .'functions.php';
require ROOT_DIR .'vendor/autoload.php';
require LIB_DIR .'ipbwi/ipbwi.inc.php';

$klein = new \Klein\Klein();

$klein->respond(function ($request, $response, $service, $app) use ($ipbwi) {
	$service->ipbwi = $ipbwi;
});

$klein->respond('[/]?[:page]?', function ($request, $response, $service) {
	// Header & Footer
	$service->layout( \AcrossTime\layout('main') );
	
	// Page-checking
	switch($request->page) {
		case 'upload': 
		case '':
			$service->render( \AcrossTime\view('upload') );
		break;
		default:
			$service->render( \AcrossTime\view('404') );
		break;
		
	}
});

$klein->dispatch();
