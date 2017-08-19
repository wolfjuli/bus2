'use strict';

angular.module('bus.version', [
  'bus.version.interpolate-filter',
  'bus.version.version-directive'
])

.value('version', '0.1');
