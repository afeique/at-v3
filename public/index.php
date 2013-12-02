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
		'jquery.autogrow-textarea.min',
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

$klein->respond('GET', '/get/timeline/[:id]', function($request, $response, $service) {
	$service->layout(Null);

	// We'll be reusing the connection init block, so I moved it to functions.php
	$DBH = \AcrossTime\initPDO();

	// TODO: check if this is the logged-in member; filter by post privacy
	
	// aid, title, description
	$STH = $DBH->prepare('SELECT * FROM at_timeline WHERE member=:id ORDER BY time_submit DESC');
	$STH->setFetchMode(PDO::FETCH_ASSOC);
	$STH->execute( array(':id' => $request->id) );
	$Result = $STH->fetchAll();

	# close the connection
	$DBH = null;
	
	$json = json_encode( $Result );
	
	return $json;

	
});

$klein->respond('POST', '/post/timeline', function ($request, $response, $service) {
	$service->layout(Null);

	// Returns:	 1 = success!
	// 			 0 = missing field
	//			-1 = invalid field (i.e. invalid DateTimes )
	//			-2 = submitted while no user logged in
	//			-3 = submitted for the wrong user! hacking attempt?
	
	// Crude server-side verification
	if( empty($_POST['user']) ||
		empty($_POST['task']) ||
		empty($_POST['description']) ||
		empty($_POST['start']) ||
		empty($_POST['end']) )
	return 0; // return false returns nothing!
	
	// Verify the data via DateTime construct
	// Checking for false doesn't work! Simply using construct accepts ridiculous inputs w/o warning
	//	(e.g. 'a' is acceptable)
	// TODO: perhaps accept more datetime formats? regex
	
	$format_in = 'm/d/y g:i a';
	$format_out = 'Y-m-d H:i:s';
	
	$start = DateTime::createFromFormat( $format_in, $_POST['start'] );
	
	$e = DateTime::getLastErrors();
	if( $e['warning_count'] > 0 or $e['error_count'] > 0 )
	return -1;
	
	$end = new DateTime( $_POST['end'] );
	
	$e = DateTime::getLastErrors();
	if( $e['warning_count'] > 0 or $e['error_count'] > 0 )
	return -1;
	
	// Check if the user is logged in
	if( !$service->ipbwi->member->isLoggedIn() )
	return -2;
	
	// Check if the user is submitting for themselves
	$m = $service->ipbwi->member->info();
	if( $m['member_id'] != $_POST['user'] )
	return -3;
	
	// Insert the data into the db
	$DBH = \AcrossTime\initPDO();
	$STH = $DBH->prepare('INSERT INTO at_timeline (member,title, description,time_start,time_end)
		VALUES 	(:member,:title,:description,:start,:end)');
	
	$STH->execute( array(
		':member' => $_POST['user'],
		':title' => $_POST['task'],
		':description' => $_POST['description'],
		':start' => $start->format($format_out),
		':end' => $end->format($format_out)
	));
	
	$DBH = null;
	
	return 1;
	
});

$klein->respond('GET', '/get/users', function($request, $response, $service) {
	$service->layout(Null);

	// We will need to sort this to only return the relevant info
	// Currently, it returns password hashes & salts!
	$memberList = $service->ipbwi->member->getList();
	
	$json = json_encode( $memberList );
	
	return $json;

});

// [m]ember; we'll do this for [g]roups too
$klein->respond('/m/[:id]', function ($request, $response, $service) {

	$service->js->add_script($sequence_index=0, $script='timeline');
	
	// I thought this might be useful, but b/c timeline.js is separate,
	//   we cannot do echo id; we'll have to break down the URI
	//$service->id = $request->id;
	
	// Maybe there's a way to incorporate /get/timeline here, but...
	// We'd have to give up dynamic update!
	
	$service->render( \AcrossTime\view('timeline') );
	
});

$klein->respond('[/]?[:page]?', function ($request, $response, $service) {
	
	// Page-checking
	switch($request->page) {
		case 'upload': 
			$service->render( \AcrossTime\view('upload') );
		break;
		case '':
			$service->render( \AcrossTime\view('recent') );
		break;
		default:
			$service->render( \AcrossTime\view('404') );
		break;
		
	}
});

$klein->dispatch();
