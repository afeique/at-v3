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
		'bootstrap.min',
		'jquery.isotope.min', //timeline
		'jquery.isotope.sloppy-masonry.min', //timeline
		'jquery.validate.min', //timeline
		'moment.min', //timeline
		'HumanizeDuration.min', //timeline
		'acrosstime', // global helpers
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
	$result = $STH->fetchAll();

	$json = json_encode( $result );
	
	return $json;

	
});

$klein->respond('POST', '/post/timeline', function ($request, $response, $service) {
	$service->layout(Null);

	// Return object properties:
	//
	// code:	 1 = success!
	// 			 0 = missing field
	//			-1 = invalid field (i.e. invalid DateTimes )
	//			-2 = submitted while no user logged in
	// error:	humanized error msg
	// aid:		We already have most of the fields from our form
	//			We can guess time_submit, save ourselves a query
	//			We need to pass the last insert id
	
	// Crude server-side verification
	if( empty($_POST['title']) or empty($_POST['time_start']) or empty($_POST['time_end']) ) {
		$out['error'] = 'Missing field!';
		$out['success'] = 0;
		return json_encode( $out );
	}
	
	// Verify the data via DateTime construct
	// Checking for false doesn't work! Simply using construct accepts ridiculous inputs w/o warning
	//	(e.g. 'a' is acceptable)
	// TODO: perhaps accept more datetime formats? test with strict regex to determine the exact format
	
	$format_in = 'm/d/y g:i a';
	$format_out = 'Y-m-d H:i:s';
	
	$start = DateTime::createFromFormat( $format_in, $_POST['time_start'] );
	$end = DateTime::createFromFormat( $format_in, $_POST['time_end'] );
	$se = $start->getLastErrors();
	$ee = $end->getLastErrors();
	if( $se['warning_count'] > 0 or $se['error_count'] > 0 or
		$ee['warning_count'] > 0 or $ee['error_count'] > 0 ) {
		
		$out['error'] = 'Invalid time!';
		$out['success'] = -1;
		return json_encode( $out );
		
	}
	
	// Check if the user is logged in
	if( !$service->ipbwi->member->isLoggedIn() ) {
		$out['error'] = 'Not logged in!';
		$out['success'] = -2;
		return json_encode( $out );
	}
	
	// Pull from ipbwi to get member id
	// TODO: *Absolutely* verify if the user is submitting for themselves? Somehow.
	$m = $service->ipbwi->member->info();
	
	// Insert the data into the db
	$DBH = \AcrossTime\initPDO();
	$STH = $DBH->prepare('INSERT INTO at_timeline (member,title, description,time_start,time_end)
		VALUES 	(:member,:title,:description,:start,:end)');
	
	$STH->execute( array(
		':member' => $m['member_id'],
		':title' => $_POST['title'],
		':description' => $_POST['description'],
		':start' => $start->format($format_out),
		':end' => $end->format($format_out)
	));
	
	// Gather last insert id
	$out['aid'] = $DBH->lastInsertId();
	$out['success'] = 1;
	
	return json_encode( $out );
	
});
$klein->respond('POST', '/delete/post', function ($request, $response, $service) {
	$service->layout(Null);
	// Return object properties:
	//
	// code:	 1 = success!
	// 			 0 = post not found
	//			-1 = post does not belong to user
	//			-2 = user not logged in
	//			-3 = some sort of PDO error
	// error:	humanized error msg
	// aid:		Just spit the $_POST var right back out
	
	// Check if user is logged in
	if( !$service->ipbwi->member->isLoggedIn() ) {
		$out['error'] = 'Not logged in!';
		$out['success'] = -2;
		return json_encode( $out );
	}else{
		$m = $service->ipbwi->member->info();
	}
	
	// Moving into database phase
	$DBH = \AcrossTime\initPDO();
	$STH = $DBH->prepare('SELECT member FROM at_timeline WHERE aid=:id');
	$STH->setFetchMode(PDO::FETCH_ASSOC);
	$STH->execute( array(':id' => $_POST['aid']) );
	$result = $STH->fetch();
	
	// Check if post exists
	if( !$result ) {
		$out['error'] = 'Post not found!';
		$out['success'] = 0;
		return json_encode( $out );
	}
	
	// Check if user owns post
	if( $result['member'] != $m['member_id'] ) {
		$out['error'] = 'Not your post!';
		$out['success'] = -1;
		return json_encode( $out );
	}
	
	// Delete the post
	$STH = $DBH->prepare('DELETE FROM at_timeline WHERE aid=:id');
	$result = $STH->execute( array(':id' => $_POST['aid']) );
	
	if( $result > 0 ) {
		$out['aid'] = $_POST['aid'];
		$out['success'] = 1;
		return json_encode( $out );
	}else{
		$out['error'] = 'Unknown error!';
		$out['success'] = -3;
		return json_encode( $out );
	}
	
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

/*
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
*/

$klein->dispatch();
