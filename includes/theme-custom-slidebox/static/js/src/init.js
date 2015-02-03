define(function(require,exports,module){
	var $ = require('modules/jquery'),
		jQuery = $;
		require('modules/jquery.kandytabs');
	exports.config = {
		width : 1000,
		height : 400
	}
	exports.init = function(){
		$(document).ready(function(){
			var $slide = $('#slidebox');
			if(!$slide[0]) return;
			$slide.KandyTabs({
				//classes:"kandySlide",
				type:"slide",
				action:"slifade",
				full:true,
				auto:true,
				nav:true,
				last:2000,
				done : function(){
					$slide.show();
					$('#slidebox-ready').hide();
				}
			})
		});
	}
	
});