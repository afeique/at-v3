// Instead of document.ready();
head.ready(function() {
	$('#tasknav a').click(function (e) {
		e.preventDefault();
		$(this).tab('show');
	});
});

var module = angular.module("timelineApp", [] );

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

function LogCtrl($scope) {
	// TODO: Allow people to log task completed AFK
}

function PomodoroCtrl($scope) {
	// TODO: Pomodoro Mode. Incorporate into TaskCtrl?
}

function TimelineCtrl($scope, $http) {
	$scope.timeline;
	$http.get('/get/timeline').then( function(response){
		$scope.timeline = response.data;
		console.log($scope.timeline);
	});
	
}