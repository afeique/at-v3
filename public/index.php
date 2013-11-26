<?php

require '../config.php';
require LIB_DIR .'functions.php';
require ROOT_DIR .'vendor/autoload.php';
require LIB_DIR .'ipbwi/ipbwi.inc.php'; // instantiates $ipbwi class
require LIB_DIR .'JavaScriptCollection.php';

$klein = new \Klein\Klein();

// applies to all responses
$klein->respond(function ($request, $response, $service, $app) use ($ipbwi) {
	// make ipbwi available to all responses
	$service->ipbwi = $ipbwi;

	// create default JavaScript collection
	$service->js = new \Acrosstime\JavaScriptCollection( array( array( 
		'jquery.min',
		'jquery-ui.min',
		'jquery-ui-timepicker-addon.min',
		'bootstrap.min',
		'jquery.countdown',
		'jquery.isotope.min',
		'jquery.isotope.sloppy-masonry.min',
		'angular.min'
	)));

	// default Header & Footer
	$service->layout( \AcrossTime\layout('main') );
});

$klein->respond('POST', '/signin', function ($request, $response, $service) {
	$service->layout( \AcrossTime\layout('redirect') );

	$rememberMe = False;
	if (isset($_POST['rememberMe']))
		$rememberMe = True;

	$anonymous = False;
	if (isset($_POST['anonymous']))
		$anonymous = True;

	$signinSuccess = $service->ipbwi->member->login($_POST['username'], $_POST['password'], $rememberMe, $anonymous);

	$service->error = False;
	if ($signinSuccess and $service->ipbwi->member->isLoggedIn()) {
		$service->message = 'Successfully signed in!';
	} else if (!$signinSuccess and $service->ipbwi->member->isLoggedIn()) {
		$service->message = 'Already signed in!';
	} else {
		$service->message = 'Failed to sign in.';
		$service->error = True;
	}

	$service->redirect = ROOT_URL;
	if (isset($_POST['redirect']))
		$service->redirect = $_POST['redirect'];

	$service->render( \AcrossTime\view('redirect-message') );
});

$klein->respond('GET', '/signout', function ($request, $response, $service) {
	$service->layout( \AcrossTime\layout('redirect') );

	$loggedIn = $service->ipbwi->member->isLoggedIn();

	$service->error = False;
	if (!$loggedIn) {
		$service->message = 'Not signed in.';
		$service->error = True;
	} else if ($loggedIn) {
		$service->ipbwi->member->logout();
		$service->message = 'Signed out!';
	}

	$service->redirect = ROOT_URL;
	if (isset($_GET['redirect']))
		$service->redirect = urldecode($_GET['redirect']);
	
	$service->render( \AcrossTime\view('redirect-message') );
});

$klein->respond('GET', '/get/timeline', function($request, $response, $service) {
	$service->layout(Null);

	// We'll be reusing the connection init block, so I moved it to functions.php
	$DBH = \AcrossTime\initPDO();

	// aid, title, description
	$STH = $DBH->prepare('SELECT * FROM at_timeline');
	$STH->setFetchMode(PDO::FETCH_ASSOC);
	$STH->execute();
	$Result = $STH->fetchAll();

	$json = json_encode( $Result );
	echo $json;

	# close the connection
	$DBH = null;
});

$klein->respond('[/]?[:page]?', function ($request, $response, $service) {
	
	// Page-checking
	switch($request->page) {
		case 'timeline':
			$service->js->add_script($sequence_index=0, $script='timeline');
			$service->render( \AcrossTime\view('timeline') );
		break;
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
