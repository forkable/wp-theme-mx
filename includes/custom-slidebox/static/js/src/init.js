define(function(require,exports,module){
	'use strict';
	var tools = require('modules/tools');
	exports.init = function(){
		tools.ready(function(){
			var $box = document.getElementById('slidebox');
			if(!$box)
				return false;

			var $checkboxes = $box.querySelectorAll('input[type="radio"]');
			if($checkboxes.length < 2)
				return false;
				
			var i = 0,
				t;
				
			function switch_checkbox(t){
				//if(t){
					if(i === $checkboxes.length)
						i = 0;
						
					$checkboxes[i].checked = true;
					i++;
				//}
				t = setTimeout(function(){
					switch_checkbox(t);
				}, 5000);
			}
			switch_checkbox(t);
			//$box.addEventListener('mouseover',function(){
			//	clearTimeout(t);
			//	switch_checkbox(t);
			//})
		});
	}
	
});