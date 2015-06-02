'use strict';

/* Controllers */

angular.module('Register', [])
	.controller('RegisterController', function ($scope, userService) {
	    //userService.Register.SecurityQuestions = [
        //           { value: 'overview', Name: 'Overview' },
        //           { value: 'list', Name: 'List' },
        //           { value: 'add', Name: 'Add' }
	    //];

	    $scope._this = userService.Register;

	    //$scope.$watch('_this.Username', function (newValue, oldValue) {
	    //    console.dir(newValue);
	    //    console.dir(oldValue);
	    //});

	    //$scope.$watch('_this.SecurityQuestions', function (newValue, oldValue) {
	    //    console.dir(newValue);
	    //    console.dir(oldValue);
	    //});
	});