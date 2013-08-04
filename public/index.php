<?php

require '../config.php';
require LIB_DIR .'functions.php';
require ROOT_DIR .'vendor/autoload.php';

$klein = new \Klein\Klein();

$klein->respond('[/]?[:page]?', function ($request, $response, $service) {
	// Header & Footer
	$service->layout(LAYOUT_DIR .'main.phtml');
	
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
