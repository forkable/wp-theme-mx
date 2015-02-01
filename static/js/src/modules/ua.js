/**
 * UA module Ckeck the client's UA
 */
define(function(require, exports, module){
	module.exports = (function(){
		function getVersion(ua, regExp, index, defVal){
			var result = ua.match(regExp);
			var val = defVal || -1;
			if(null != result){
				val = result[index] || val;
				if(val.indexOf("_") != -1){
					val = val.replace("_", ".");
				}
			}
			return +val;
		};
		//userAgent
		var ua = navigator.userAgent.toLowerCase();

		//检测平台
		var linux = (ua.indexOf("linux") != -1);
		var mac_os = (ua.indexOf("macintosh") != -1 || ua.indexOf("mac os") != -1);
		var windows = (ua.indexOf("windows") != -1);
		var windows_ce = (ua.indexOf("windows ce") != -1);
		var windows_nt = (ua.indexOf("windows nt") != -1);
		var windows_xp = (ua.indexOf("windows xp") != -1);
		var iphone_os = (ua.indexOf("iphone os") != -1);
		var iphone = /iphone\;/.test(ua);
		var ipod = /ipod\;/.test(ua);
		var ipad = /ipad\;/.test(ua);
		var android = (ua.indexOf("android") != -1);
		var blackberry = (ua.indexOf("blackberry") != -1);
		var iemobile = (ua.indexOf("iemobile") != -1);
		var symbian_os = (ua.indexOf("symbianos") != -1);
		var s60 = (ua.indexOf("series60") != -1);


		//检测平台版本
		var linux_version = -1;
		var mac_os_version = getVersion(ua, /mac os x[\s ]?(\d+(_\d+)?)/, 1);
		var iphone_os_version = getVersion(ua, /iphone os[\s ]?(\d+(_\d+)?)/, 1); +(iphone_os ? (ua.match(/iphone os ([\d_]+)/))[1] || -1 : -1);
		var windows_version = getVersion(ua, /Windows[\s ]?(\d+)/, 1);
		var windows_nt_version = (windows_nt ? getVersion(ua, /windows nt[\s ]?([\d\.]+)/, 1, 4.0) : (windows_xp ? 5.1 : -1));
		var android_version = getVersion(ua, /android[\s ]?(\d+(\.\d+)?)/, 1);
		var iemobile_version = getVersion(ua, /iemobile[\s ]?(\d+(\.\d+)?)/, 1);
		var symbian_os_version = getVersion(ua, /symbianos\/(\d+(\.\d+)?)/, 1);
		var s60_version = getVersion(ua, /series60\/(\d+(\.\d+)?)/, 1);
		
		//检测浏览器
		var ie = getVersion(ua, /msie[\s \/]?(\d+(\.\d+)?)/, 1);
			if(ie == -1) ie = getVersion(ua, /rv\:(\d+)/, 1);
		var chrome = getVersion(ua, /chrome[\s \/]?(\d+(\.\d+)?)/, 1);
		var safari = getVersion(ua, /version\/(\d+)[\w\W]+safari/, 1);
		var firefox = getVersion(ua, /firefox[ \/](\d+(\.\d+)?)/, 1);
		var maxthon = getVersion(ua, /maxthon[ \/](\d+(\.\d+)?)/, 1);
		var netscape = getVersion(ua, /(netscape[\d]*|navigator)\/(\d+(\.\d+)?)/, 2);
		var myie = getVersion(ua, /myie(\d+)/, 1);
		var opera = getVersion(ua,/(opera[\s ]?|opera\/)(\d+(\.\d+)?)/, 2);
		var nokia = getVersion(ua, /browserng\/(\d+(\.\d+)?)/, 1);
		var qqbrowser = getVersion(ua, /qqbrowser\/(\d+(\.\d+)?)/, 1);
		
		var o = {
			"Platform" : {
				"Linux" : linux,
				"MacOS" : mac_os,
				"Windows" : windows,
				"WindowsCE" : windows_ce,
				"WindowsNT" : windows_nt,
				"WindowsXP" : (windows_xp || (5.1 == windows_nt_version)),
				"iPhoneOS" : iphone_os,
				"iPhone" : iphone,
				"iPod" : ipod,
				"iPad" : ipad,
				"Android" : android,
				"BlackBerry" : blackberry,
				"IEMobile" : iemobile,
				"SymbianOS" : symbian_os,
				"S60" : s60
			},
			"Version" : {
				"Linux" : linux_version,
				"MacOS" : mac_os_version,
				"iPhoneOS" : iphone_os_version,
				"Windows" : windows_version,
				"WindowsNT" : windows_nt_version,
				"Android" : android_version,
				"IEMobile" : iemobile_version,
				"SymbianOS" : symbian_os_version,
				"S60" : s60_version
			},
			"Browser" : {
				"ie" : ie,
				"chrome" : chrome,
				"safari" : safari,
				"firefox" : firefox,
				"maxthon" : maxthon,
				"netscape" : netscape,
				"myie" : myie,
				"opera" : opera,
				"nokia" : nokia,
				"QQBrowser" : qqbrowser
			}
		};
		
		var arr = [];
		
		for(var key in o){
			if(o.hasOwnProperty(key)){
				if(o[key] instanceof Object){
					for(var i in o[key]){
						if(o[key].hasOwnProperty(i)){
							arr.push(key + "::" + i + " = " + o[key][i]);
						}
					}
				}
			}
		}
		
		o["info"] = arr;
		return o;
	})();
});