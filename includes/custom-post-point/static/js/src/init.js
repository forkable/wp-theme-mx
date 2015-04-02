/**
 * @version 1.0.0
 */
define(function(require, exports, module){
	'use strict';
	var tools = require('modules/tools');

	exports.config = {
		process_url : ''
	}

	var config = exports.config,
		caches = {};

	exports.init = function(){
		tools.ready(exports.bind);
	}

	exports.bind = function(){
		
	}
});