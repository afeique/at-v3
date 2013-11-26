// Instead of document.ready();
head.ready(function() {
	
	var timeline;
	
	// Bootstrap tab handling
	$('#tasknav a').click(function (e) {
		e.preventDefault();
		$(this).tab('show');
	});
	
	// Load timeline from server
	$.get('/get/timeline', timelineCallback );
	
	function timelineCallback( data ) {
		
		timeline = JSON.parse(data);
		console.log( timeline );
		
		// Process server data into #timeline
		$.each( timeline, function( i, e ) {
			var item = $('<div/>');
			item.append('<p class="text-muted">' + e.when + '</p>');
			item.append('<h2>' + e.title + '</h2>');
			item.append('<p>' + e.description + '</p>');
			
			item.wrapInner('<div class="item-inner"/>');
			item.addClass('item');
			
			$('#timeline').append( item )
		});
		
		// Trigger isotope
		$('#timeline').isotope({
			itemSelector : '.item',
			layoutMode : 'sloppyMasonry'
		}, function() {
			$('#loading-timeline').slideUp();
		});
		
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