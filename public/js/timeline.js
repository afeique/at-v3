// Instead of document.ready();
head.ready( function() {

	// We'll accept 'm/d/y g:i a' first - DONE!
	// TODO: omit 'm/d/y' and assume that it is within the same day
	
	$.validator.addMethod("dateTime", function(value, element) {
		
		// This is the key!!!
		// http://momentjs.com/docs/#/parsing/string-formats/
		
		// Split date & time, keep am/pm with time
		// TODO: Make more efficient?
		var stamp = value.split(" ");
		stamp[1] = stamp[1] + ' ' + stamp[2];
		delete stamp[2];
		
		var validDate = !/Invalid|NaN/.test(new Date(stamp[0]).toString());
		var validTime = /^(([0]?[1-9]|1[0-2])(:)[0-5][0-9]( )(AM|am|a\.m\.|PM|pm|p\.m\.))$/i.test(stamp[1]);
		
		return this.optional(element) || (validDate && validTime);
		
	});
	
	// Script-wide member id - used for /get/timeline/
	var url = document.location.pathname.split('/');
	var id = url[url.length-1];
	var format = 'MM/DD/YY h:mm a';

	// Bootstrap tab handling
	$('#tasknav a').click(function (e) {
		e.preventDefault();
		$(this).tab('show');
	});
	
	// Expand description textbox when overflow
	//$('#description').css('overflow', 'hidden').autogrow();

	// "Now" Buttons
	$('#time_start_now, #time_end_now').click( function() {
		var input = $(this).parents('.input-group').find('input');
		input.val( moment().format( format ) );
		input.keyup();
	});
	
	// Define validation rules & submit handler
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
			// TODO: *Actually* disable the form, not just the inputs
			// Collect and save data, serialize form..?
			// e.g. form can be sent by hitting enter on a textbox
			$('#options .loader').fadeTo('fast',1);
			$.post('/post/timeline', $('#recordtask').serialize(), recordtaskCallback );
			$("#recordtask :input").prop('disabled', true);
		},
		validClass: "valid",
		errorClass: "invalid",
		errorPlacement: function(error, element) {}
	});
	
	// Calculate total time or display validation errors
	// Because it checks if the fields are valid, it must go after the validation rules
	// TODO: make this into some sort of validation rule?
	$('#time_start, #time_end').keyup( function() {
		
		if( !$('#time_start,#time_end').valid() ) {
			$('#total').text( 'Invalid time!');
		}else{
			var start = moment( $('#time_start').val(), format);
			var end = moment( $('#time_end').val(), format);
			var diff = end.diff(start);
			
			if( diff < 0 ) {
				$('#total').text( 'Negative duration!' );
			}else{
				// Moment.js does not humanize exact durations
				$('#total').text( humanizeDuration(diff) );
			}
			
		}
		
	});
	

	//* DEBUG: Tired of retyping this everytime
	var start = moment().subtract('m', 10).format( format );
	var end = moment().format( format );
	$('#recordtask #title').val( 'AcrossTime' );
	$('#recordtask #description').val( 'Testing!' );
	$('#recordtask #time_start').val( start );
	$('#recordtask #time_end').val( end );
	$('#time_start').keyup();
	//*/
	
	// Decide if insert is successful, then delegate to timelineAdd();
	function recordtaskCallback( data ) {
		var result = JSON.parse(data);
		
		if( result.success != 1 ) {
			var msg = result.error;
			
			// TODO: Abstract this into an alert helper function
			$('<div class="alert alert-danger alert-dismissable"/>')
				.append('<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>')
				.append('<p>' + msg + '</p>')
				.appendTo( '#error');
				
		}else{
			// TODO: Enable form properly? (see above)
			$("#recordtask :input").prop('disabled', false);
			$('#options .loader').fadeTo('slow', 0);
			
			var item = $('#recordtask').serializeObject();
			item.time_submit = moment().format('YYYY-MM-DD HH:mm:ss');
			item.aid = result.aid;
			
			timelineAdd( 0, item );
			
			// Reset form after serializing
			$('#recordtask')[0].reset();

		}
		
	}

	
	
	
	// Trigger isotope! We'll add items later, one by one, via timelineAdd();
	// Wait after isotope init to AJAX timeline!
	$('#timeline').isotope({
		itemSelector : '.item',
		// TODO: modify sloppyMasonry to go straight across (kinda)
		//		 kind like adding height difference tolerance
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
		$.each( items, timelineAdd );
		$('#options .loader').fadeTo('slow',0);
	}
	
	// Populates isotope div
	function timelineAdd( i, e ) {
		
		var item = $('<div/>');
		
		// TODO: add more filters?
		item.attr('data-aid', e.aid );
		
		item.append('<p class="time_submit text-muted">' + e.time_submit + '</p>');
		item.append('<h2 class="title">' + e.title + '</h2>');
		item.append('<p class="description">' + e.description + '</p>');
		
		// Image first or text first? Leaning towards text first
		item.append('<img class="image" src="/img/0.jpg"/>');
		
		item.wrapInner('<div class="item-inner"/>');
		item.addClass('item');
		
		// Remove button
		var btn = $('<button type="button" class="close" aria-hidden="true">&times;</button>')
			btn.attr('data-target', e.aid);
			btn.click( removePost );
		item.prepend( btn );
		
		// TODO: Comments
		// TODO: Likes?
		// TODO: Privacy indicator
		
		// This takes care of cache issues. Namely, this:
		// http://stackoverflow.com/questions/8622906/isotope-overlapping-images
		item.imagesLoaded( function() {
			$('#timeline').isotope( 'insert', item );
		});
		
	}
	
	function removePost() {
		var data = new Object();
		data['aid'] = $(this).data('target');
		
		// TODO: Add confirm dialog y/n
		
		$.post('/delete/post', $.param(data), removePostCallback );
		$(this).html('<img src="/img/preloader.gif" alt="Deleting..." />');
		$(this).prop('disabled', true);
	}
	
	function removePostCallback(data) {
		var result = JSON.parse(data);
		
		console.log( result );
		
		if( result.success != 1 ) {
			$(this).html('&times;');
			$(this).prop('disabled', false);
		}else{
			var target = $('.item[data-aid='+result.aid+']');
			$('#timeline').isotope( 'remove', target );
		}
		
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