
define(function(require,exports,module){var $=require('modules/jquery'),jQuery=$;require('modules/bootstrap');exports.config={width:500,height:300}
exports.init=function(){$(document).ready(function(){var $slide=$('#slidebox');if(!$slide[0])return;$slide.carousel({interval:5000})});}});