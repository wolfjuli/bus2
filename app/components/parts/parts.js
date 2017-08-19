'use strict';

var app = angular.module('bus.parts', [])

.controller('partsCtrl', function($scope, partsDB) {
    $scope.filter = {$: 'd'};
    $scope.parts = [];
    $scope.places = {};
})
.factory('partsDB',  function ($http, $q) {
    var data;
    return {
        getAll: function () {
            var partsPromise = $http.get('rest/api.php/array/parts/');
            var placesPromise = $http.get('rest/api.php/places/');

            return $q.all({parts: partsPromise, places: placesPromise});
        }
    }
})


.directive("parts", function (partsDB) {

    return {
        restrict: 'E',
        controller: 'partsCtrl',
        templateUrl: 'grids/grid-parts.html',
        scope: true,
        link: function (scope, element, attrs) {
            partsDB.getAll().then(function (results) {
                scope.parts = results.parts.data;
                scope.places = results.places.data;
            }, function(error) {
                console.log("got error", error)
            });
        }
    }
});