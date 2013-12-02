// Instead of document.ready();
head.ready(function() {

	// Script-wide member id
	var url = document.location.pathname.split('/');
	var id = url[url.length-1];

	/*
	$('#start,#end').datetimepicker({
		timeFormat: "hh:mm tt",
		dateFormat: "mm/dd/y",
		showOn: "button",
		buttonImage: "/img/clock-icon.png",
		buttonImageOnly: true
	}).wrap('<div style="display:inline-block;"/>');
	// */
	
	// Bootstrap tab handling
	$('#tasknav a').click(function (e) {
		e.preventDefault();
		$(this).tab('show');
	});
	
	// Expand description textbox when overflow
	$('#description').css('overflow', 'hidden').autogrow();
	
	// Define user id in form
	$('#recordtask #user').val( id );
	
	// Attach log submit handler to form
	$('#recordtask').on( 'submit', function( event ) {
		
		event.preventDefault();
		
		// $.serialize() requires form fields to have names
		console.log( $(this).serialize() );
		
		// TODO: validate form here
		
		// TODO: server-side confirm that this is the right person
		$.post('/post/timeline', $('#recordtask').serialize(), recordtaskCallback );
		
	});
	
	function recordtaskCallback( data ) {
		console.log( data );
	}
	
	// Load timeline from server
	$.get('/get/timeline/' + id, timelineCallback );
	
	function timelineCallback( data ) {
		
		var timeline = JSON.parse(data);
		
		// Temporary solution for no results
		if( timeline.length < 1 ) {
			$('#loading-timeline .panel-body').html('<p>No results!</p>');
			return false;
		}
		
		// Process server data into #timeline
		$.each( timeline, function( i, e ) {
			
			var item = $('<div/>');
			item.append('<p class="text-muted">' + e.time_submit + '</p>');
			item.append('<h2>' + e.title + '</h2>');
			item.append('<p>' + e.description + '</p>');
			
			// I have code to hard-set the image height & width on first load & resize
			// For now, you'll have to refresh after first load
			// Image first or text first? Leaning towards text first
			item.append('<img src="/img/0.jpg"/>');
			
			
			item.wrapInner('<div class="item-inner"/>');
			item.addClass('item');
			
			$('#timeline').append( item );
			
		});
		
		// Trigger isotope
		$('#timeline').isotope({
			itemSelector : '.item',
			layoutMode : 'sloppyMasonry'
		}, function() {
			$('#loading-timeline').slideUp();
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