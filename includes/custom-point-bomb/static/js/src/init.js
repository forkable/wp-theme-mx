define(function(require, exports, module){
	'use strict';
	var js_request = require('theme-cache-request'),
		tools = require('modules/tools');
		
	exports.config = {
		process_url : '',
		lang : {
			M01 : 'Target locking...',
			M02 : 'Bombing, please wait...',
			
			E01 : 'Sorry, some server error occurred, the operation can not be completed, please try again later.'
		}
	}

	var config = exports.config,
		cache = {};

	exports.init = function(){
		tools.ready(function(){
			exports.bind();
			radio_check();
			
		});
	}

	exports.bind = function(){
		cache.$fm_loading = I('fm-bomb-loading');
		cache.$fm = I('fm-bomb');
		cache.$attacker_points = I('bomb-attacker-points');
		cache.$target = I('bomb-target');
		cache.$target_name = I('bomb-target-name');
		cache.$target_points = I('bomb-target-points');
		cache.$target_avatar = I('bomb-target-avatar');
		cache.$points = document.querySelectorAll('.bomb-points');
		
		
		if(!cache.$fm)
			return false;

		cache.$submit = cache.$fm.querySelector('.submit');
		
		cache.$fm_loading.parentNode.removeChild(cache.$fm_loading);
		cache.$fm.style.display = 'block';
		cache.$fm.addEventListener('submit', fm_submit);
		cache.$target.addEventListener('blur', get_target.bind, true);

		/**
		 * check if exists value
		 */
		if(cache.$target.value.trim() !== '' && !isNaN(cache.$target.value)){
			get_target.init(cache.$target.value);
		}
		
	}

	var get_target = {
		bind : function(){
			
			cache.target_id = cache.$target.value.trim();
			
			if(cache.target_id === '' || isNaN(cache.target_id))
				return false;

			get_target.init(cache.target_id);
			
		},
		init : function(target_id){
			/**
			 * check cache
			 */
			if(this.get_target_form_cache(target_id)){
				this.set_target(target_id);
			}else{
				this.get_target_form_server(target_id);
			}	
		},
		/**
		 * get target data from server
		 * @param int target Target user ID
		 */
		get_target_form_server : function(target_id){
			var that = this,
				xhr = new XMLHttpRequest();
			/** loding tip */
			tools.ajax_loading_tip('loading',config.lang.M01);
			
			xhr.open('GET',config.process_url + '&type=get-target&target=' + target_id + '&theme-nonce=' + js_request['theme-nonce']);
			xhr.send();
			xhr.onload = function(){
				if(xhr.status >= 200 && xhr.status < 400){
					var data;
					try{data = JSON.parse(xhr.responseText)}catch(e){data = xhr.responseText}
					
					if(data && data.status === 'success'){
						/** set cache */
						that.set_target_cache(target_id,{
							name : data.name,
							points : data.points,
							avatar : data.avatar
						});
						
						/** set to html */
						that.set_target(target_id);
						
						/** tip */
						tools.ajax_loading_tip(data.status,data.msg);
						
						cache.$submit.removeAttribute('disabled');
					}else if(data && data.status === 'error'){
						cache.$target.select();
						
						/** tip */
						tools.ajax_loading_tip(data.status,data.msg);

					}else{
						tools.ajax_loading_tip('error',data);
						cache.$target.select();
					}
				}else{
					tools.ajax_loading_tip('error',config.lang.E01);
				}
			};
			xhr.onerror = function(){
				tools.ajax_loading_tip('error',config.lang.E01);
			};
		},
		/**
		 * set target data to html
		 * @param int target Target user ID
		 */
		set_target : function(target_id){
			cache.$target_points.innerHTML = cache.users[target_id].points;
			cache.$target_name.innerHTML = cache.users[target_id].name;
			cache.$target_avatar.src = cache.users[target_id].avatar;
		},
		/**
		 * get target data from cache
		 * @param int target Target user ID
		 */
		get_target_form_cache : function(target_id){
			return cache.users && cache.users[target_id];
		},
		/**
		 * set target data to cache
		 * @param int target Target user ID
		 * @param object data Target data
		 */
		set_target_cache : function(target_id,data){
			if(!cache.users)
				cache.users = [];
			
			cache.users[target_id] = data;
		}
	};

	function radio_check(){
		function helper(e){
			active(this);
		}
		function active($radio){
			var $label = $radio.parentNode,
				$loop_label;
			for( var i = 0, len = cache.$points.length; i < len; i++ ){
				$loop_label = cache.$points[i].parentNode;
				if($loop_label.classList.contains('label-success'))
					$loop_label.classList.remove('label-success');
			}
			
			$label.classList.add('label-success');
			cache.points = parseInt($radio.value);
			
		}
		for( var i = 0, len = cache.$points.length; i < len; i++ ){
			if(cache.$points[i].checked)
				active(cache.$points[i]);
			cache.$points[i].addEventListener('change', helper);
		}
	}
	function fm_submit(e){
		e.preventDefault();
		/** tip */
		tools.ajax_loading_tip('loading',config.lang.M02);
		cache.$submit.setAttribute('disabled',true);
		
		var xhr = new XMLHttpRequest(),
			fd = new FormData(cache.$fm);
			
		fd.append('theme-nonce',js_request['theme-nonce']);
		xhr.open('POST',config.process_url);
		xhr.send(fd);
		xhr.onload = function(){
			var data;
			try{data = JSON.parse(xhr.responseText)}catch(e){data = xhr.responseText}
			
			if(data && data.status === 'success'){
				/** get attack and target points */
				var old_attacker_points = parseInt(cache.$attacker_points.textContent.trim()),
					old_target_points = parseInt(cache.$target_points.textContent.trim());
				/**
				 * hit
				 */
				if(data.hit){
					tools.ajax_loading_tip(data.status,data.msg);
					/** attacker points */
					cache.$attacker_points.innerHTML = old_attacker_points + '<span class="text-success">+' + cache.points + '</span>';
					setTimeout(function(){
						cache.$attacker_points.innerHTML = old_attacker_points + cache.points;
					}, 3000);
					/** target points */
					cache.$target_points.innerHTML = old_target_points + '<span class="text-danger">-' + cache.points + '</span>';
					setTimeout(function(){
						cache.$target_points.innerHTML = old_target_points - cache.points;
					}, 3000);
				/**
				 * miss
				 */
				}else{
					tools.ajax_loading_tip('warning',data.msg);
					/** attacker points */
					var half_points = Math.round(cache.points / 2);
					
					cache.$attacker_points.innerHTML = old_attacker_points + '<span class="text-danger">-' + cache.points + '</span>';
					setTimeout(function(){
						cache.$attacker_points.innerHTML = old_attacker_points - cache.points;
					}, 3000);
					
					/** target points */
					cache.$target_points.innerHTML = old_target_points + '<span class="text-success">+' + half_points + '</span>';
					setTimeout(function(){
						cache.$target_points.innerHTML = old_target_points + half_points;
					}, 3000);
				}
				
				
			}else if(data && data.status === 'error'){
				tools.ajax_loading_tip(data.status,data.msg);
			}else{
				tools.ajax_loading_tip('error',config.lang.E01);
			}
			cache.$submit.removeAttribute('disabled');
		};
		xhr.onerror = function(){
			tools.ajax_loading_tip('error',config.lang.E01);
			cache.$submit.removeAttribute('disabled');
		};
	}
	
	function I(s){
		return document.getElementById(s);
	}
});