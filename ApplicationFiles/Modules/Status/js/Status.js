'use strict';

/* Controllers */

angular.module('Status', [])
	.controller('StatusController', function ($scope, userService, $state) {
	    $scope._this = userService.Status;

	    $scope.$watch('_this.RealmlistID', function (sNewModule, sOldModule) {
	        if (sNewModule !== null && sNewModule !== undefined) {
	            $state.go('State.RecordID', { State: 'Status', RecordID: sNewModule });
	            $scope._this.RealmlistID = null;
	        }
	    });
	});