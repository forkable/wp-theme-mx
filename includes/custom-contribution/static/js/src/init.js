define(function(require, exports, module){
	'use strict';
	var tools = require('modules/tools'),
		js_request 	= require('theme-cache-request');
		
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
			M00002 : 'Uploading {0}/{1}, please wait...',
			M00003 : 'Click to delete',
			M00004 : '{0} files have been uploaded.',
			E00001 : 'Sorry, server error please try again later.'
		}
	}
	var config = exports.config,
		cache = {};
	exports.init = function(){
		tools.ready(function(){
			exports.bind();
			toggle_reprint_group();
		});
	}
		
	exports.bind = function(){
		cache.$fm = 			jQuery('#fm-ctb');
		cache.$file_area = 		jQuery('#ctb-file-area');
		cache.$file_btn = 		jQuery('#ctb-file-btn');
		cache.$file = 			jQuery('#ctb-file');
		cache.$files = 			jQuery('#ctb-files');
		cache.$file_progress = 		jQuery('#ctb-file-progress');
		cache.$file_completion_tip = jQuery('#ctb-file-completion');
		cache.$file_progress_bar = 	jQuery('#ctb-file-progress-bar');
		cache.$file_progress_tx = 	jQuery('#ctb-file-progress-tx');

		if(!cache.$fm[0]) return false;
		upload();
		checkbox_select(cache.$fm);
		fm_validate(cache.$fm);
		
		
	}
	/**
	 * upload
	 */
	
	function upload(){
		cache.$file.on({
			change 		: file_select,
			drop 		: file_select
		});
		/**
		 * file_select
		 */
		function file_select(e){
			e.stopPropagation();
			e.preventDefault();
			cache.files = e.target.files.length ? e.target.files : e.originalEvent.dataTransfer.files;
			cache.file_count = cache.files.length;
			cache.file = cache.files[0];
			cache.file_index = 0;
			file_upload(cache.files[0]);
		}
		/**
		 * file_upload
		 */
		function file_upload(file){
			var	reader = new FileReader();
			reader.onload = function (e) {
				submission(file);
			};
			reader.readAsDataURL(file);
		}
		/**
		 * submission
		 */
		function submission(file){
			beforesend_callback();
			var fd = new FormData(),
				xhr = new XMLHttpRequest();

			fd.append('type','upload');
			fd.append('theme-nonce',js_request['theme-nonce']);
			fd.append('img',file);
			
			xhr.open('post',config.process_url);
			xhr.onload = complete_callback;
			xhr.onreadystatechange = function(){
				if (xhr && xhr.readyState === 4) {
					status = xhr.status;
					if (status >= 200 && status < 300 || status === 304) {
						
					}else{
						error_callback();
					}
				}
				xhr = null;
			}
			xhr.upload.onprogress = function(e){
				if (e.lengthComputable) {
					var percent = e.loaded / e.total * 100;		
					cache.$file_progress_bar.animate({
						width : percent + '%'
					},500);
					
				}
			}
			xhr.send(fd);
		}
		function beforesend_callback(){
			var tx = config.lang.M00002.format(cache.file_index + 1,cache.file_count);
			cache.$file_progress_bar.width('0');
			uploading_tip('loading',tx);
		}
		function error_callback(msg){
			msg = msg ? msg : config.lang.E00001;
			uploading_tip('error',msg);
		}
		/** 
		 * upload_started
		 */
		function upload_started(i,file,count){
			var t = config.lang.M00002.format(i,count);
			uploading_tip('loading',t);
		}
		function complete_callback(){
			var data = this.responseText;
			try{
				data = JSON.parse(this.responseText);
			}catch(error){
				data = false;
			}
			cache.file_index++;
			/** 
			 * success
			 */
			if(data && data.status === 'success'){
				var args = {
					thumbnail : data.thumbnail,
					original : data.original,
					attach_id : data['attach-id']
					},
					$tpl = get_$tpl(args);
				cache.$files.show().append($tpl);
				$tpl.fadeIn();
				/** 
				 * check all thing has finished, if finished
				 */
				if(cache.file_count === cache.file_index){
					var tx = config.lang.M00004.format(cache.file_index,cache.file_count);
					uploading_tip('success',tx);
					cache.$file.val('');
				/**
				 * upload next file
				 */
				}else{
					file_upload(cache.files[cache.file_index]);
				}
			/** 
			 * no success
			 */
			}else{
				/** 
				 * notify current file is error
				 */
				if(cache.file_index > 0){
					//error_file_tip(cache.files[cache.file_index - 1]);
				}
				/** 
				 * if have next file, continue to upload next file
				 */
				if(cache.file_count > cache.file_index){
					file_upload(cache.files[cache.file_index]);
				/** 
				 * have not next file, all complete
				 */
				}else{
					cache.is_uploading = false;
					if(data && data.status === 'error'){
						error_callback(data.msg);
					}else{
						error_callback(config.lang.E00001);
						console.error(data);
					}
					/** 
					 * reset file input
					 */
					cache.$file.val('');

				}
			}
		}
		/**
		 * args = {
			original,
			thumbnail,
			attach_id
		 }
		 */
		function get_$tpl(args){
			var tpl = '<div id="img-' + args.attach_id + '" class="thumbnail-tpl col-xs-6 col-sm-3 col-md-2">' +
					'<a class="img-link" href="' + args.original + '" target="_blank">' + 
						'<img src="' + args.thumbnail + '" alt="Thumbnail preview" >' +
					'</a>' +
					'<a href="javascript:;" class="img-del">X</a>' +
					'<input type="hidden" name="ctb[attach-ids][]" value="' + args.attach_id + '" title="' + config.lang.M00003 + '" >' + 
				'</div>',
				$tpl = jQuery(tpl).hide(),
				$del = $tpl.find('a.img-del');
			$del.on('click',function(){
				$tpl.fadeOut('slow',function(){
					$tpl.remove();
				});
			});
			return $tpl;
		}
		/**
		 * get_img_url_by_size
		 * 
		 * @params string size The img size,eg:
		 * 						square 		(mw/mh:80)
		 * 						thumbnail 	(mw/mh:120)
		 * 						thumb150 	(150x150,crop)
		 * 						mw600 		(mw:600)
		 * 						bmiddle  	(mw:440)
		 * 						large 		(original)
		 * @return string The img url
		 * @version 1.0.2
		 * @author KM@INN STUDIO
		 */
		function get_img_url_by_size(size,img_url){
			if(!size) size = 'square';
			var file_obj = img_url.split('/'),
				len = file_obj.length,
				basename = file_obj[len - 1],
				old_size = file_obj[len - 2],
				hostname = img_url.substr(0,img_url.indexOf(old_size));
				url = hostname + size + '/' + basename;
			return url;
		}
		/**
		 * get_id
		 * 
		 * @params string Image url
		 * @return string The ID
		 * @version 1.0.0
		 * @author KM@INN STUDIO
		 */
		function get_id(img_url){
			var id = img_url.split('/'),
				id = id[id.length - 1].split('.')[0];
			return id;
		}
		/**
		 * The tip when pic is uploading
		 *
		 * @param string status 'loading','success' ,'error'
		 * @param string text The content of tip
		 * @return 
		 * @version 1.0.1
		 * @author KM@INN STUDIO
		 */
		function uploading_tip(status,text){
			/** 
			 * uploading status
			 */
			if(!status || status === 'loading'){
				cache.$file_progress_tx.html(tools.status_tip('loading',text));
				cache.$file_progress.show();
				cache.$file_area.hide();
				cache.$file_completion_tip.hide();
			/** 
			 * success status
			 */
			}else{
				cache.$file_completion_tip.html(tools.status_tip(status,text)).show();
				cache.$file_progress.hide();
				cache.$file_area.show();
			}
		}
	}
	function checkbox_select($fm){
		var $boxes = $fm.find('.checkbox-select'),
			selected_class = 'label-success';
		$boxes.each(function(){
			var $labels = jQuery(this).find('label');
			$labels.on('click',function(){
				var $this = jQuery(this);
				if($this.hasClass(selected_class)){
					$this.removeClass(selected_class);
				}else{
					$this.addClass(selected_class);
				}
			});
			
		});
	}
	function fm_validate($fm){
		var m = new tools.validate();
			m.process_url = config.process_url + '&' + jQuery.param({
				'theme-nonce' : js_request['theme-nonce'],
				type : 'post'
			});
			m.done = function(data){
				if(data && data.status === 'success'){
					$fm.find('input:text').val('');
					$fm.find('textarea').text('');
					//setTimeout(function(){
					//	location.href = location.href;
					//},2000);
				}
				$fm.find('.page-tip').show();
			};
			m.loading_tx = config.lang.M00001;
			m.error_tx = config.lang.E00001;
			m.$fm = $fm;
			m.init();
	}

	function toggle_reprint_group(){
		var $reprint_group = jQuery('#reprint-group');
		var $radios = jQuery('.theme_custom_post_source-source-radio');
		$radios.each(function(){
			action(jQuery(this));
		});
		$radios.on('change',function(){
			action(jQuery(this));
		});
		function action($radio){
			if($radio[0].id === 'theme_custom_post_source-source-reprint' && $radio.prop('checked')){
				$reprint_group.show();
				$reprint_group.find('input').eq(0).focus();
			}else{
				$reprint_group.hide();
			}
		}
	}
});