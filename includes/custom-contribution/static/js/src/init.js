define(function(require, exports, module){
	'use strict';
	var tools = require('modules/tools'),
		js_request 	= require('theme-cache-request');
		
	exports.config = {
		fm_id : 			'fm-ctb',
		file_area_id : 		'ctb-file-area',
		file_btn_id : 		'ctb-file-btn',
		file_id : 			'ctb-file',
		file_tip_id : 		'ctb-file-tip',
		files_id : 			'ctb-files',

		process_url : '',
		
		lang : {
			M00001 : 'Loading, please wait...',
			M00002 : 'Uploading {0}/{1}, please wait...',
			M00003 : 'Click to delete',
			M00004 : '{0} files have been uploaded.',
			M00005 : 'Source',
			M00006 : 'Click to view source',
			M00007 : 'Set as cover.',
			M00008 : 'Optional: some description',
			M00009 : 'Insert',
			M00010 : 'Preview',
			M00011 : 'Large size',
			M00012 : 'Medium size',
			M00013 : 'Small size',
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
	function I(e){
		return document.getElementById(e);
	}
	exports.bind = function(){
		cache.$fm = 			I('fm-ctb');
		cache.$file_area = 		I('ctb-file-area');
		cache.$file_btn = 		I('ctb-file-btn');
		cache.$file = 			I('ctb-file');
		cache.$files = 			I('ctb-files');
		cache.$file_progress = 		I('ctb-file-progress');
		cache.$file_completion_tip = I('ctb-file-completion');
		cache.$file_progress_bar = 	I('ctb-file-progress-bar');
		cache.$file_progress_tx = 	I('ctb-file-progress-tx');

		if(!cache.$fm) return false;
		upload();
		//checkbox_select(cache.$fm);
		fm_validate(cache.$fm);
		
		
	}
	/**
	 * send_to_editor
	 * 
	 * @return 
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	function send_to_editor(h) {
		var ed, mce = typeof(tinymce) != 'undefined', qt = typeof(QTags) != 'undefined';

		if ( !wpActiveEditor ) {
			if ( mce && tinymce.activeEditor ) {
				ed = tinymce.activeEditor;
				wpActiveEditor = ed.id;
			} else if ( !qt ) {
				return false;
			}
		} else if ( mce ) {
			if ( tinymce.activeEditor && (tinymce.activeEditor.id == 'mce_fullscreen' || tinymce.activeEditor.id == 'wp_mce_fullscreen') )
				ed = tinymce.activeEditor;
			else
				ed = tinymce.get(wpActiveEditor);
		}

		if ( ed && !ed.isHidden() ) {
			// restore caret position on IE
			if ( tinymce.isIE && ed.windowManager.insertimagebookmark )
				ed.selection.moveToBookmark(ed.windowManager.insertimagebookmark);

			if ( h.indexOf('[caption') !== -1 ) {
				if ( ed.wpSetImgCaption )
					h = ed.wpSetImgCaption(h);
			} else if ( h.indexOf('[gallery') !== -1 ) {
				if ( ed.plugins.wpgallery )
					h = ed.plugins.wpgallery._do_gallery(h);
			} else if ( h.indexOf('[embed') === 0 ) {
				if ( ed.plugins.wordpress )
					h = ed.plugins.wordpress._setEmbed(h);
			}

			ed.execCommand('mceInsertContent', false, h);
		} else if ( qt ) {
			QTags.insertContent(h);
		} else {
			document.getElementById(wpActiveEditor).value += h;
		}

		try{tb_remove();}catch(e){};
	}
	function custom_tag(){
		this.added_container_id = 'custom-tag-added-container';
		this.add_container_id = 'custom-tag-add-container';
		this.add_new_id = 'custom-tag-new';
		this.add_btn_id = 'custom-tag-add-btn';


		function get_tpl(tx){
			var $tpl = document.createElement('span'),
				$remove = document.createElement('span');
			$tpl.textContent = tx;
			$tpl.setAttribute('class','label label-success');
			
			$remove.classList.add('remove');
			$remove.innerHTML = '<i class="fa fa-minus-circle"></i>';
			$remove.addEventListener('click', function (e) {
				$tpl.parentNode.removeChild($tpl);
			}, false);
			
			return $tpl;
		}
	}
	/**
	 * upload
	 */
	
	function upload(){
		cache.$file.addEventListener('change',file_select,false);
		cache.$file.addEventListener('drop',file_select,false);
		
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
					cache.$file_progress_bar.style.width = percent + '%';
					
				}
			}
			xhr.send(fd);
		}
		function beforesend_callback(){
			var tx = config.lang.M00002.format(cache.file_index + 1,cache.file_count);
			cache.$file_progress_bar.style.width = 0;
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
						medium : data.medium,
						large : data.large,
						full : data.full,
						attach_id : data['attach-id']
					},
					$tpl = get_tpl(args);
				cache.$files.style.display = 'block';
				cache.$files.appendChild($tpl);
				$tpl.style.display = 'block';
				/** 
				 * check all thing has finished, if finished
				 */
				if(cache.file_count === cache.file_index){
					var tx = config.lang.M00004.format(cache.file_index,cache.file_count);
					uploading_tip('success',tx);
					cache.$file.value = '';
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
					cache.$file.value = '';

				}
			}
		}
		/**
		 * args = {
			original,
			thumbnail,
			mi
			attach_id
		 }
		 */
		function get_tpl(args){
			var $tpl = document.createElement('div'),
				M00010 = I('ctb-title').value == '' ? config.lang.M00010 : I('ctb-title').value,
				content = '<a class="img-link" href="' + args.full.url + '" target="_blank" title="' + config.lang.M00006 + '">' + 
						'<img src="' + args.thumbnail.url + '" alt="' + M00010 +'" >' +
					'</a>' +
					'<div class="btn-group btn-block">' +
						'<a href="javascript:;" class="btn btn-primary col-xs-10 ctb-insert-btn" id="ctb-insert-' + args.attach_id + '" data-size="medium"><i class="fa fa-plug"></i> ' + config.lang.M00009 + '</a>' +
						'<span class="btn btn-primary dropdown-toggle col-xs-2" data-toggle="dropdown" aria-expanded="false"><span class="caret"></span><span class="sr-only"></span></span><ul class="dropdown-menu" role="menu"><li><a href="javascript:;" class="ctb-insert-btn" data-size="large">' + config.lang.M00011 + '</a></li>' +
					'</div>' +
					'<input type="radio" name="ctb[thumbnail-id]" id="img-thumbnail-' + args.attach_id + '" value="' + args.attach_id + '" hidden class="img-thumbnail-checkbox" required >' +
					'<label for="img-thumbnail-' + args.attach_id + '" class="ctb-set-cover-btn">' + config.lang.M00007 + '</label>' +
					'<input type="hidden" name="ctb[attach-ids][]" value="' + args.attach_id + '" >';
					
			$tpl.id = 'img-' + args.attach_id;
			$tpl.setAttribute('class','thumbnail-tpl col-xs-6 col-sm-3 col-md-2');
			$tpl.innerHTML = content;
			$tpl.style.display = 'none';
			
			//var $del = $tpl.querySelector('.img-del');
			//$del.addEventListener('click',function(){
			//	$tpl.parentNode.removeChild($tpl);
			//},false);
			/**
			 * set as cover
			 */
			if(!cache.first_cover){
				$tpl.querySelector('.img-thumbnail-checkbox').checked = true;
				cache.first_cover = true;
			}
			/**
			 * insert
			 */
			var $insert_btn = $tpl.querySelectorAll('.ctb-insert-btn');
			for(var i = 0, len = $insert_btn.length; i < len; i++){
				$insert_btn[i].addEventListener('click',function(){
					send_to_editor(send_content(args.full.url,args[this.getAttribute('data-size')].url));
				},false);
			}
			/** auto send to editor */
			send_to_editor(send_content(args.full.url,args.medium.url));

			
			function send_content(full_url,img_url){
				return '<p><a href="' + full_url + '" title="' + config.lang.M00006 + '" target="_blank" >' + 
					'<img src="' + img_url + '" alt="' + M00010 + '" >' +
				'</a></p>';
			}

			return $tpl;
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
				cache.$file_progress_tx.innerHTML = tools.status_tip('loading',text);
				cache.$file_progress.style.display = 'block';
				cache.$file_area.style.display = 'none';
				cache.$file_completion_tip.style.display = 'none';
			/** 
			 * success status
			 */
			}else{
				cache.$file_completion_tip.innerHTML = tools.status_tip(status,text)
				cache.$file_completion_tip.style.display = 'block';
				cache.$file_progress.style.display = 'none';
				cache.$file_area.style.display = 'block';
			}
		}
	}

	function fm_validate($fm){
		$fm.addEventListener('submit',function(){
			var fm_data = new FormData(),
				$submit = $fm.querySelector('[type=submit]'),
				submit_ori_tx = $submit.textContent,
				submit_loading_tx = $submit.getAttribute('data-loading-text'),
				inputs = $fm.querySelectorAll('[name]');
			for(var i = 0, len = inputs.length; i<len; i++){
				/**
				 * radio checked
				 */
				if(inputs[i].getAttribute('type') === 'radio' && !inputs[i].checked)
					continue;
				/**
				 * checkbox
				 */
				if(inputs[i].getAttribute('type') === 'checkbox' && !inputs[i].checked)
					continue;
					
				fm_data.append([inputs[i].name],inputs[i].value);
			}
			//console.log(fm_data);

			/**
			 * sending tip
			 */
			tools.ajax_loading_tip('loading',config.lang.M00001);
			$submit.textContent = submit_loading_tx;
			$submit.setAttribute('disabled',true);
			
			var xhr = new XMLHttpRequest();
			xhr.open('POST',config.process_url + '&' + tools.param({
				'theme-nonce' : js_request['theme-nonce'],
				type : 'post'
			}));
			xhr.send(fm_data);
			xhr.onload = function(){
				if(xhr.status >= 200 && xhr.status < 400){
					var data;
					try{data = JSON.parse(xhr.responseText)}catch(e){data = xhr.responseText}
					
					if(data && data.status){
						if(data.status === 'error'){
							//if(data.code ===)
							$submit.removeAttribute('disabled');
						}
						tools.ajax_loading_tip(data.status,data.msg);
						$submit.textContent = submit_ori_tx;
					}else{
						tools.ajax_loading_tip('error',data);
						$submit.textContent = submit_ori_tx;
						$submit.removeAttribute('disabled');
					}
				}else{
					tools.ajax_loading_tip('error',config.lang.E00001);
					$submit.textContent = submit_ori_tx;
					$submit.removeAttribute('disabled');
				}
			};
			xhr.onerror = function(){
				tools.ajax_loading_tip('error',config.lang.E00001);
				$submit.textContent = submit_ori_tx;
				$submit.removeAttribute('disabled');
			}
			
			
		});
		
	}

	function toggle_reprint_group(){
		var $reprint_group = I('reprint-group');
		var $radios = document.querySelectorAll('.theme_custom_post_source-source-radio');
		
		for(var i = 0, len = $radios.length; i<len; i++){
			//console.log(i);
			action($radios[i]);
			$radios[i].addEventListener('change',function(){
				action(this);
			});
		}

		function action($radio){
			//console.log($radio);
			if($radio.id === 'theme_custom_post_source-source-reprint' && $radio.checked){
				$reprint_group.style.display = 'block';
				$reprint_group.querySelector('input').focus();
			}else{
				$reprint_group.style.display = 'none';
			}
		}
	}
});