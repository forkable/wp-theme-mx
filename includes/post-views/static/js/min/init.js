
define(function(require,exports,module){'use strict';var tools=require('modules/tools'),js_request=require('theme-cache-request');exports.init=function(){tools.ready(exports.set_views);}
exports.set_views=function(){if(js_request&&js_request['views']){for(var k in js_request['views']){var $view=document.getElementById('post-views-number-'+k);if($view)
$view.innerHTML=js_request['views'][k];}}}});