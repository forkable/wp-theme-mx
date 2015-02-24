define(function(require, exports, module){
	var $ = require('modules/jquery'),
		jQuery = $,
		tools = require('modules/tools'),
		js_request 	= require('theme-cache-request');
	require('modules/jquery.validate');
	require('modules/jquery.validate.lang.{locale}');

	exports.config = {
		fm_id : 			'#fm-ctb',
		file_area_id : 		'#ctb-file-area',
		file_btn_id : 		'#ctb-file-btn',
		file_id : 			'#ctb-file',
		file_tip_id : 		'#ctb-file-tip',
		files_id : 			'#ctb-files',

		process_url : '',
		
		lang : {
			M00001 : 'Loading, please wait...',
			E00001 : 'Sorry, server error please try again later.'
		}
	}
	var config = exports.config,
		cache = {};
	exports.init = function(){
		$(document).ready(function(){
			exports.bind();
		});
	}
		
	exports.bind = function(){
		cache.$fm = 			$('#fm-ctb');
		cache.$file_area = 		$('#ctb-file-area');
		cache.$file_btn = 		$('#ctb-file-btn');
		cache.$file = 			$('#ctb-file');
		cache.$file_tip = 		$('#ctb-file-tip');
		cache.$files = 			$('#ctb-files');

		if(!cache.$fm[0]) return false;
		tools.auto_focus(cache.$fm);
		checkbox_select(cache.$fm);
		fm_validate();
		
		
	}
	function checkbox_select($fm){
		$boxes = $fm.find('.checkbox-select');
		$boxes.each(function(){
			var $labels = $(this).find('label');
			$labels.on('click',function(){
				var $this = $(this);
				if($this.hasClass('btn-primary')){
					$this.removeClass('btn-primary');
				}else{
					$this.addClass('btn-primary');
				}
			});
			
		});
	}
	function fm_validate(){
		var m = new tools.validate();
			m.process_url = config.process_url + '&' + $.param({
				'theme-nonce' : js_request['theme-nonce']
			});
			m.done = function(data){
				if(data && data.status === 'success'){
					setTimeout(function(){
						location.href = location.href;
					},2000);
				}
			};
			m.loading_tx = exports.config.lang.M00001;
			m.error_tx = exports.config.lang.E00001;
			m.$fm = cache.$fm;
			m.init();
	}
});