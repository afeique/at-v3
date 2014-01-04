// Instead of document.ready();
head.ready( function() {

	// We'll accept 'm/d/y g:i a' first - DONE!
	// TODO: omit 'm/d/y' and assume that it is within the same day
	
	$.validator.addMethod("dateTime", function(value, element) {
		
		// Split date & time, keep am/pm with time
		// TODO: Make more efficient?
		var stamp = value.split(" ");
		stamp[1] = stamp[1] + ' ' + stamp[2];
		delete stamp[2];
		
		var validDate = !/Invalid|NaN/.test(new Date(stamp[0]).toString());
		var validTime = /^(([0]?[1-9]|1[0-2])(:)[0-5][0-9]( )(AM|am|a\.m\.|PM|pm|p\.m\.))$/i.test(stamp[1]);
		
		return this.optional(element) || (validDate && validTime);
		
	});
	
	
	/*
	// This is a more forgiving version. Disabled until I get server-side working to match
	// TODO: add 24 hour military time with no separator?
	$.validator.addMethod("dateTime", function(value, element) {
		
		var stamp = value.split(" ");
		
		if( stamp.length > 2 ) {
			stamp[1] = stamp[1] + ' ' + stamp[2];
			delete stamp[2];
		}
		
		var validDate = !/Invalid|NaN/.test(new Date(stamp[0]).toString());
		
		if( !validDate ) {
			if( stamp.length > 1 ) {
				stamp[0] = stamp[0] + ' ' + stamp[1];
				delete stamp[1];
			}
		}
		
		var validTime = /^((([0]?[1-9]|1[0-2])(:|\.)[0-5][0-9]((:|\.)[0-5][0-9])?( )?(AM|am|a\.m\.|PM|pm|p\.m\.))|(([0]?[0-9]|1[0-9]|2[0-3])(:|\.)[0-5][0-9]((:|\.)[0-5][0-9])?))$/i.test(stamp[0]);
		
		return this.optional(element) || (validTime);
		
	}, 'Please enter a valid time!');
	//*/

	// Script-wide member id
	var url = document.location.pathname.split('/');
	var id = url[url.length-1];

	// Bootstrap tab handling
	$('#tasknav a').click(function (e) {
		e.preventDefault();
		$(this).tab('show');
	});
	
	// Expand description textbox when overflow
	$('#description').css('overflow', 'hidden').autogrow();
	
	// Define user id in form
	$('#recordtask #member').val( id );
	
	// DEBUG: 
	//*
	var start = moment().subtract('m', 10).format('MM/DD/YY h:mm a');
	var end = moment().format('MM/DD/YY h:mm a');
	$('#recordtask #title').val( 'AcrossTime' );
	$('#recordtask #description').val( 'Testing!' );
	$('#recordtask #time_start').val( start );
	$('#recordtask #time_end').val( end );
	//*/
	
	// TODO: validate form here
	$('#recordtask').validate({
		rules: {
			title: {
				required: true
			},
			description: {
				required: false
			},
			time_start: {
				dateTime: true,
				required: true
			},
			time_end: {
				dateTime: true,
				required: true
			}
		},
		submitHandler: function(form) {
			$.post('/post/timeline', $('#recordtask').serialize(), recordtaskCallback );
		},
		validClass: "valid",
		errorClass: "invalid",
		errorPlacement: function(error, element) {}
	});
	
	function recordtaskCallback( data ) {
		var msg, obj;
		
		switch( parseInt(data) ) {
			case 1:
				obj = $('#recordtask').serializeObject();
				obj.time_submit = moment().format('YYYY-MM-DD HH:mm:ss');
				timelineAdd( 0, obj );
			break;
			case 0:
				msg = 'Missing field!';
			break;
			case -1:
				msg = 'Invalid field!';
			break;
			case -2:
				msg = "Not logged in!";
			break;
			case -3:
				msg = "Wrong user. Naughty you!";
			break;
		}
		
		if( typeof msg !== 'undefined' ) {
			$('<div class="alert alert-dismissable"/>')
				.append('<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>')
				.append('<p>' + msg + '</p>')
				.addClass('alert-danger')
				.appendTo( '#error');
		}
		
	}

	// Trigger isotope! We'll add items later. Wait for init, then get timeline
	$('#timeline').isotope({
		itemSelector : '.item',
		layoutMode : 'sloppyMasonry',
		sortBy : 'time',
		sortAscending : false,
		getSortData : {
			time : function ( $elem ) {
				return $elem.find('.text-muted').text();
			}
		}
	}, function(){
		// Load timeline from server
		$.get('/get/timeline/' + id, timelineCallback );
	});

	
	
	
	function timelineCallback( data ) {
		
		var items = JSON.parse(data);
		
		// Temporary solution for no results
		if( items.length < 1 )
		$('#loading-timeline .panel-body').html('<p>No results!</p>');
		
		// Process server data into #timeline
		$.each( items, timelineAdd );
		
		$('#loading-timeline').hide();
	}
	

	
	function timelineAdd( i, e ) {
		var item = $('<div/>');
		
		// TODO: add more filters
		
		item.append('<p class="text-muted">' + e.time_submit + '</p>');
		item.append('<h2>' + e.title + '</h2>');
		item.append('<p>' + e.description + '</p>');
		
		// Image first or text first? Leaning towards text first
		item.append('<img src="/img/0.jpg"/>');
		
		item.wrapInner('<div class="item-inner"/>');
		item.addClass('item');
		
		// This takes care of cache issues. Namely, this:
		// http://stackoverflow.com/questions/8622906/isotope-overlapping-images
		item.imagesLoaded( function() {
			$('#timeline').isotope( 'insert', item );
		});
	}
	

	
	function submitLog() {
	}
	
});

/* Removed temporarily - 11/25/13

// Angular turns "wgTimer" into "wg-timer"
module.directive('wgTimer', function() {
	return {
		restrict: 'A', // Restrict it to be an attribute in this case
		link: function( scope, element, attrs) {
			scope.$watch( "timerGoing", function( newVal, oldVal ) {
				if( newVal === oldVal ) {
					// Generates 00:00:00 - on page/div load
					element.countdown( scope.$eval( attrs.wgTimer ) );
				}else if( newVal ) {
					element.countdown('resume');
				}else{
					element.countdown('pause');
				}
			});
		}
	};
});
// */

/* Removed temporarily - 11/25/13
function TimerCtrl($scope) {
	$scope.timerGoing = false; // widget var
	$scope.taskStarted = false;
	$scope.taskFinished = false;
	$scope.taskStage = 0;
	$scope.task = {title: "Task Title", description: "Description." };
	
	$scope.startTask = function() {
		$scope.timerGoing = true;
		$scope.taskStarted = true;
		$scope.taskStage = 1;
		
		console.log("Task started!");
	}
	
	$scope.endTask = function() {
		$scope.timerGoing = false;
		$scope.taskFinished = true;
		$scope.taskStage = 2;
		console.log("Task ended.");
	}
	
	$scope.pauseTimer = function() {
		$scope.timerGoing = ! $scope.timerGoing;
	}
}
// */

/* Removed temporarily - 11/25/13
function PomodoroCtrl($scope) {
	// TODO: Pomodoro Mode. Incorporate into TaskCtrl?
}
// */