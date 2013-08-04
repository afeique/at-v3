<?php
require_once __DIR__ . '/vendor/autoload.php';
$klein = new \Klein\Klein();

$klein->respond('[/]?[:page]?', function ($request, $response, $service) {
	// Header & Footer
	$service->layout('server/pages/layout.phtml');
	
	// Page-checking
	switch($request->page) {
		case 'upload': 
		case '':
			$service->render('server/pages/upload.php');
		break;
		default:
			$service->render('server/pages/error-404.php');
		break;
		
	}
});

$klein->dispatch();

?>