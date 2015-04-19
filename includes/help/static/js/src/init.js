define(function(require, exports, module){
	
	exports.init = function(){
		var that = exports;
		jQuery(document).ready(function(){
			that.alipay.init();
			that.paypal.init();
		});
	};
	exports.paypal = {
		that : exports,
		config : {
			btn_id : '#paypal_donate',
			lang : {
			
			}
		
		},
		cache : {},
		
		init : function(){
			var _this = this,
				that = this.that;
			var $btn = jQuery(_this.config.btn_id);
			if(!$btn[0]) return false;
			$btn.on('click',function(){
				if(_this.cache.$fm){
					_this.cache.$fm.submit();
					return false;
				}
				var inputs = [],
					inputs_data = {
						'cmd' : '_donations',
						'item_name' : _this.config.lang.M00001,
						'amount' : '',
						'currency_code' : 'USD',
						'business' : 'kmvan.com@gmail.com',
						'lc' : 'US',
						'no_note' : '0'
					},
					$fm = jQuery('<form></form>')
						.attr({
							'accept-charset' : 'GBK',
							'name' : '_xclick',
							'id' : 'fm-paypal-donate',
							'action' : 'https://www.paypal.com/cgi-bin/webscr',
							'method' : 'post',
							'target' : '_blank'
						}).hide();

				for(i in inputs_data){
					inputs.push(that.comm.set_input(i,inputs_data[i]));
				}
				that.comm.submit(inputs,$fm);
				return false;
				
			});
		}
	};
	exports.alipay = {
		that : exports,
		config : {
			btn_id : '#alipay_donate',
			
			lang : {
				M00001 : 'Message for INN STUDIO',
				M00002 : 'Donate to INN STUDIO'
			
			}
		},
		
		cache : {},
		init : function(){
			var _this = this,
				that = this.that;
			var $btn = jQuery(_this.config.btn_id);
			if(!$btn[0]) return false;
			$btn.on('click',function(){
				if(_this.cache.$fm){
					_this.cache.$fm.submit();
					return false;
				}
				var inputs = [],
					inputs_data = {
						'optEmail' : 'kmvan.com@gmail.com',
						'title' : _this.config.lang.M00001,
						'memo' : _this.config.lang.M00002,
						'payAmount' : ''
					},
					$fm = jQuery('<form></form>')
						.attr({
							'accept-charset' : 'GBK',
							'id' : 'fm-alipay-donate',
							'action' : 'https://shenghuo.alipay.com/send/payment/fill.htm',
							'method' : 'post',
							'target' : '_blank'
						}).hide();

				for(i in inputs_data){
					inputs.push(that.comm.set_input(i,inputs_data[i]));
				}
				that.comm.submit(inputs,$fm);
				return false;
			});
		}
	};
	exports.comm = {
		set_input : function(n,v){
			return '<input type="hidden" name="' + n + '" value="' + v + '"/>';
		},
		submit : function(inputs,$fm){
			inputs = inputs.join('',inputs);
			$fm.append(inputs);
			jQuery('body').prepend($fm);
			$fm.submit();
		}
	
	}

});