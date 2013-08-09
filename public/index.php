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
	$service->js = new \Acrosstime\JavaScriptCollection(array(array('jquery.min', 'bootstrap.min', 'jquery.countdown', 'jquery.isotope.min', 'angular.min') ) );

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

	try {
		require ROOT_DIR .'private.php';

		# MySQL with PDO_MYSQL
		$DBH = new PDO("mysql:host={$private['SQL_HOST']};dbname={$private['SQL_DB']}", $private['SQL_USER'], $private['SQL_PASS']);
		$DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}
	catch(PDOException $e) {
		unset($private);
		echo $e->getMessage();
	}

	unset($private);

	$STH = $DBH->prepare('SELECT aid, title, description FROM at_timeline');
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
			$service->js->add_script($sequence_index=0, $script='timeline', $relative_to='angular', $insert_after=True);
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
