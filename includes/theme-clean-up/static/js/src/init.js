define(function(require, exports, module){
	var $ = require('modules/jquery');
	exports.config = {
		btn_ids : {
			redundant_post_id : '#clean_redundant_posts',
			orphan_postmeta_id : '#clean_orphan_postmeta',
			redundant_comment_id : '#clean_redundant_comments',
			orphan_commentmeta_id : '#clean_orphan_commentmeta',
			orphan_relationship_id : '#clean_orphan_relationships',
			database_optimization_id : '#database_optimization'
			
		},
		process_url : '',
		lang : {
			M00001 : 'Loading, please wait...',
			E00001 : 'Server error or network is disconnected.'
		}
	
	};
	exports.init = function(){
		$(document).ready(function(){
			exports.bind();
		
		});
	
	};
	
	exports.bind = function(){
		var btn_ids = [];
		for(var k in exports.config.btn_ids){
			btn_ids.push(exports.config.btn_ids[k]);
		}
		btn_ids = btn_ids.join();
		$(btn_ids).on('click',function(){
			var $this = $(this);
			exports.hook.process($this);
			
		});
	}
	
	exports.hook = {
		process : function($btn){
			if(typeof $btn == 'undefined') return false;
			var ajax_data = {
				type : $btn.attr('data-action')
			};
			$.ajax({
				url : exports.config.process_url,
				type : 'get',
				dataType : 'json',
				data : ajax_data,
				beforeSend : function(){
					exports.hook.tips($btn,'loading',exports.config.lang.M00001);
				},success : function(data){
					if(data && data.status && data.status === 'success'){
						exports.hook.tips($btn,'success',data.des.content);
					}else if(data && data.status && data.status === 'error'){
						exports.hook.tips($btn,'error',data.des.content);
					}else{
						exports.hook.tips($btn,'error',exports.config.lang.E00001);
					}
				},error : function(){
					exports.hook.tips($btn,'error',exports.config.lang.E00001);
				}
			})
		},
		
		tips : function($b,t,s,hide){
			require.async(['modules/tools'],function(tools){
				var $next = $b.next();
				if($next.hasClass('tip-status')){
					$next.replaceWith(tools.status_tip(t,'small',s,'span'));
				}else{
					$b.after(' ' + tools.status_tip(t,'small',s,'span'));
				}
			});
		}
	
	}
});