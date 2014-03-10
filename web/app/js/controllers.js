'use strict';

angular.module('vmApp.controllers', []).
    controller('vmCtrl', function ($scope, $http) {
        $scope.formData = {};
        
        $scope.addHost = function () {
          $http.post('/api/vhosts', $scope.formData)
              .success(function (data) {
                  $scope.formData = {};
                  $scope.vhosts = data;
              });
        };
        
        $scope.deleteHost = function (nn) {
            $http.delete('/api/vhosts/' + nn)
                .success(function (data) {
                    $scope.vhosts = data;
                });
        };
        
        $http.get('/api/vhosts').success(function (data) {
            $scope.vhosts = data;
        });
    });
