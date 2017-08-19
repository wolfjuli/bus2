'use strict';

describe('bus.version module', function() {
  beforeEach(module('bus.version'));

  describe('version service', function() {
    it('should return current version', inject(function(version) {
      expect(version).toEqual('0.1');
    }));
  });
});
