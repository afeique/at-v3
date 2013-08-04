<?php

define('ROOT', dirname(__DIR__) .'/' );
define('VIEWS', ROOT .'views/');
define('LAYOUTS', VIEWS .'layouts/');

require_once ROOT .'vendor/autoload.php';
$klein = new \Klein\Klein();

$klein->respond('[/]?[:page]?', function ($request, $response, $service) {
	// Header & Footer
	$service->layout(LAYOUTS .'main.phtml');
	
	// Page-checking
	switch($request->page) {
		case 'upload': 
		case '':
			$service->render(VIEWS .'upload.phtml');
		break;
		default:
			$service->render(VIEWS .'404.phtml');
		break;
		
	}
});

$klein->dispatch();

?>
