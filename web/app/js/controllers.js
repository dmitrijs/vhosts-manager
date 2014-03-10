'use strict';

angular.module('vmApp.controllers', []).
    controller('vmCtrl', function ($scope, $http) {
        $http.get('/api/');
    });