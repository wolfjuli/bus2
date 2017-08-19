'use strict';

// Declare app level module which depends on views, and components
angular.module('bus', [
  'ngRoute',
  'bus.view1',
  'bus.view2',
  'bus.version',
    'bus.parts'
])
    .config(['$locationProvider', '$routeProvider', '$logProvider', function($locationProvider, $routeProvider, $logProvider) {
  $locationProvider.hashPrefix('!');
    $logProvider.debugEnabled(true);
  $routeProvider.otherwise({redirectTo: '/view1'});
}]);
