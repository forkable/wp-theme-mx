<?php

/**
 * Output select tag html
 *
 * @param string $value Option value
 * @param string $text Option text
 * @param string $current_value Current option value
 * @return string
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
function get_option_list($value,$text,$current_value){
	ob_start();
	$selected = $value == $current_value ? ' selected ' : null;
	?>
	<option value="<?php echo esc_attr($value);?>" <?php echo $selected;?>><?php echo esc_html($text);?></option>
	<?php
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}
/**
 * mult_search_array
 *
 * @param array
 * @param string
 * @param string
 * @return array
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
function mult_search_array($key,$value,$array){ 
	$results = array(); 
	if (is_array($array)){ 
		if (isset($array[$key]) && $array[$key] == $value) 
		$results[] = $array; 
		foreach ($array as $subarray){ 
			$results = array_merge($results, mult_search_array($key, $value,$subarray)); 
		}
	} 
	return $results; 
} 
/**
 * check_referer
 *
 * @param string
 * @return bool
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
function check_referer($referer = null){
	$referer = $referer ? : home_url();
	if(!isset($_SERVER["HTTP_REFERER"]) || stripos($_SERVER["HTTP_REFERER"],$referer) !== 0){
		return false;
	}else{
		return true;
	}
}
/**
 * array_multiwalk
 *
 * @param array
 * @param string function name
 * @return array
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
function array_multiwalk($a,$fn){
	if(!$a || !$fn) return false;
	foreach($a as $k => $v){
		if(is_array($v)){
			$a[$k] = array_multiwalk($v,$fn);
			continue;
		}else{
			$a[$k] = $fn($v);
		}
	}
	return $a;
}
/**
 * is_null_array
 *
 * @param array
 * @return bool
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
function is_null_array($arr = null){
	if(is_array($arr)){  
	   foreach($arr as $k=>$v){  
			if($v && !is_array($v)){  
				return false;  
			}
			$t = is_null_array($v);  
			if(!$t){  
				return false;  
			}  
		}
		return true;  
	}elseif(!$arr){  
		return true;  
	}else{  
		return false;  
	} 
}
/**
 * chmodr
 * 
 * @param string $path path or filepath
 * @param int $filemode
 * @return bool
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
function chmodr($path = null, $filemode = 0777) { 
	if(!is_dir($path)) return chmod($path,$filemode); 
	$dh = opendir($path); 
	while(($file = readdir($dh)) !== false){
		if($file != '.' && $file != '..'){
			$fullpath = $path.'/'.$file;
			if(is_link($fullpath)){
				return false;
			}else if(!is_dir($fullpath)&& !chmod($fullpath, $filemode)){
				return false;
			}else if(!chmodr($fullpath,$filemode)){
				return false;
			}
		}
	}
	closedir($dh);
	if(chmod($path,$filemode)){
		return true; 
	}else{
		return false;
	}
}
/**
 * mk_dir
 * 
 * 
 * @param string $target
 * @return bool
 * @version 1.0.1
 * @author KM@INN STUDIO
 * 
 */
function mk_dir($target = null){
	if(!$target) return false;
	$target = str_replace('//', '/', $target); 
	if(file_exists($target)) return is_dir($target); 

	if(@mkdir($target)){
		$stat = stat(dirname($target)); 
		chmod($target, 0755); 
		return true; 
	}else if(is_dir(dirname($target))){
		return false; 
	} 
	/* If the above failed, attempt to create the parent node, then try again. */
	if(($target != '/')&&(mk_dir(dirname($target)))){
		return mk_dir($target); 
	}
	return false; 
}
 /**
  * remove_dir
  * 
  * 
  * @param string $path
  * @return 
  * @version 1.0.2
  * @author KM@INN STUDIO
  * 
  */
function remove_dir($path = null){
	if(!$path || !file_exists($path)) return false;
	if(file_exists($path) || is_file($path)) @unlink($path);
	if($handle = opendir($path)){
		while(false !==($item = readdir($handle))){
			if($item != "." && $item != ".."){
				if(is_dir($path . '/' . $item)){
					remove_dir($path . '/' . $item);
				}else{
					unlink($path . '/' . $item);
				}
			}
		}
		closedir($handle);
		rmdir($path);
	}
}
/**
 * esc_html___
 *
 * @param string
 * @param string
 * @return string
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
function esc_html___($text,$tdomain = null){
	$tdomain = $tdomain ? $tdomain : theme_features::$iden;
	return esc_html__($text,$tdomain);
}
/**
 * esc_attr___
 *
 * @param string
 * @param string
 * @return string
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
function esc_attr___($text,$tdomain = null){
	$tdomain = $tdomain ? $tdomain : theme_features::$iden;
	return esc_attr__($text,$tdomain);
}
/**
 * __n
 *
 * @param string
 * @param string
 * @param int
 * @param string
 * @return string
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
function __n($single,$plural,$number,$tdomain = null){
	$tdomain = $tdomain ? $tdomain : theme_features::$iden;
	return _n($single,$plural,$number,$tdomain);
}
/**
 * __ex
 *
 * @param string
 * @param string
 * @param string
 * @return n/a
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
function __ex($text,$content,$tdomain = null){
	echo __x($text,$content,$tdomain);
}
/**
 * __x
 *
 * @param string
 * @param string
 * @param string
 * @return string
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
function __x($text,$content,$tdomain = null){
	$tdomain = $tdomain ? $tdomain : theme_functions::$iden;
	return _x($text,$content,$tdomain);
}
/**
 * __e()
 * translate function
 * 
 * @param string $text your translate text
 * @param string $tdomain your translate tdomain
 * @return string display
 * @version 1.0.2
 * @author KM@INN STUDIO
 * 
 */
function __e($text = null,$tdomain = null){
	echo ___($text,$tdomain);
}
/**
 * ___()
 * translate function
 * 
 * @param string $text your translate text
 * @param string $tdomain your translate domain
 * @version 1.0.4
 * @author KM@INN STUDIO
 * 
 */
function ___($text = null,$tdomain = null){
	if(!$text) return false;
	$tdomain = $tdomain ? $tdomain : theme_functions::$iden;
	return __($text,$tdomain);
}
/**
 * status_tip
 *
 * @param mixed
 * @return string
 * @example status_tip('error','content');
 * @example status_tip('error','big','content');
 * @example status_tip('error','big','content','span');
 * @version 2.0.1
 * @author KM@INN STUDIO
 */
function status_tip(){
	$args = func_get_args();
	if(empty($args)) return false;
	$defaults = array('type','size','content','wrapper');
	$types = array('loading','success','error','question','info','ban','warning');
	$sizes = array('small','middle','large');
	$wrappers = array('div','span');
	$type = null;
	$size = null;
	$wrapper = null;

	switch(count($args)){
		case 1:
			$content = $args[0];
			break;
		case 2:
			$type = $args[0];
			$content = $args[1];
			break;
		default:
			foreach($args as $k => $v){
				$$defaults[$k] = $v;
			}
	}
	$type = $type ? $type : $types[0];
	$size = $size ? $size : $sizes[0];
	$wrapper = $wrapper ? $wrapper : $wrappers[0];

	switch($type){
		case 'success':
			$icon = 'check-circle';
			break;
		case 'error' :
			$icon = 'times-circle';
			break;
		case 'info':
		case 'warning':
			$icon = 'exclamation-circle';
			break;
		case 'question':
		case 'help':
			$icon = 'question-circle';
			break;
		case 'ban':
			$icon = 'minus-circle';
			break;
		case 'loading':
		case 'spinner':
			$icon = 'circle-o-notch';
			break;
		default:
			$icon = $type;
	}

	
	$tpl = '<' . $wrapper . ' class="tip-status tip-status-' . $size . ' tip-status-' . $type . '"><i class="fa fa-' . $icon . '"></i> ' . $content . '</' . $wrapper . '>';
	return $tpl;
}

/**
 * Get remote server file size
 * 
 * 
 * @param string $url The remote file
 * @return int The file size, false if file is not exist
 * @version 1.0.0
 * @author KM@INN STUDIO
 * 
 */
function get_remote_size($url = null){  
	if(!$url) return false;
	
    $uh = curl_init();  
    curl_setopt($uh, CURLOPT_URL, $url);  
  
    // set NO-BODY to not receive body part  
    curl_setopt($uh, CURLOPT_NOBODY, 1);  
  
    // set HEADER to be false, we don't need header  
    curl_setopt($uh, CURLOPT_HEADER, 0);  
  
    // retrieve last modification time  
    curl_setopt($uh, CURLOPT_FILETIME, 1);  
    curl_exec($uh);  
  
    // assign filesize into $filesize variable  
    $filesize = curl_getinfo($uh,CURLINFO_CONTENT_LENGTH_DOWNLOAD);  
  
    // assign file modification time into $filetime variable  
    $filetime = curl_getinfo($uh,CURLINFO_FILETIME);  
    curl_close($uh);  
  
    // push out  
    return array("size"=>$filesize,"time"=>$filetime);  
}
/**
 * multidimensional searching
 * 
 * 
 * @param array $parents The parents array
 * @param array $searched What want you search,
 * like this: array('date'=>1320883200, 'uid'=>5)
 * @return string If exists return the key name, or false
 * @version 1.0.0
 * @author revoke
 * 
 */
function multidimensional_search($parents, $searched) { 
	if(empty($searched) || empty($parents)) return false;

	foreach($parents as $key => $value){
		$exists = true; 
		foreach ($searched as $skey => $svalue) { 
			$exists = ($exists && IsSet($parents[$key][$skey]) && $parents[$key][$skey] == $svalue);
		} 
		if($exists){
			return $key;
		} 
	} 
	return false; 
} 

/**
 * html_compress
 * 
 * 
 * @param string The html code
 * @return string The clear html code
 * @version 1.0.2
 * @author higrid.net
 * @author KM@INN STUDIO 20131211
 * 
 */
function html_compress($html = null){
	if(!$html) return false;
	$chunks = preg_split( '/(<pre.*?\/pre>)/ms', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	$html = null;
	foreach ( $chunks as $c ){
		if ( strpos( $c, '<pre' ) !== 0 ){
			// remove html comments
			$c = preg_replace( '/<!--[^!]*-->/', ' ', $c );
			//[higrid.net] remove new lines & tabs
			$c = preg_replace( '/[\\n\\r\\t]+/', ' ', $c );
			// [higrid.net] remove extra whitespace
			$c = preg_replace( '/\\s{2,}/', ' ', $c );
			// [higrid.net] remove inter-tag whitespace
			$c = preg_replace( '/>\\s</', '><', $c );
			// [higrid.net] remove CSS & JS comments
			$c = preg_replace( '/\\/\\*.*?\\*\\//i', '', $c );
		}
		$html .= $c;
	}
	return $html;
}
/**
 * Refer to url address
 * 
 * 
 * @param string $url The want to go url
 * @param string/array $param The wnat to go url's param
 * @return bool false that not enough param, otherwise refer to the url
 * @version 1.0.1
 * @author INN STUDIO
 * 
 */
function refer_url($url = null,$param = null){
	if(!$url) return false;
	$param = is_array($param) ? http_build_query($param) : $param;
	$url_obj = parse_url($url);
	if(isset($url_obj['query'])){
		$header = $param ? 'Location: ' .$url. '&' .$param : 'Location: ' .$url;
	}else{
		$header = $param ? 'Location: ' .$url. '?' .$param : 'Location: ' .$url;
	}
	header($header);
	// exit;
}
/**
 * encode
 * @param string $string 原文或者密文
 * @param string $operation 操作(encode | decode), 默认为 decode
 * @param string $key 密钥
 * @param int $expiry 密文有效期, 加密时候有效， 单位 秒，0 为永久有效
 * @return string 处理后的 原文或者 经过 base64_encode 处理后的密文
 * @example
 *   $a = authcode('abc', 'encode', 'key');
 *   $b = authcode($a, 'decode', 'key');  // $b(abc)
 *
 *   $a = authcode('abc', 'encode', 'key', 3600);
 *   $b = authcode('abc', 'decode', 'key'); // 在一个小时内，$b(abc)，否则 $b 为空
 */
function authcode($string, $operation = 'decode', $key = null, $expiry = 0) {

    $ckey_length = 4;

    $key = md5($key ? $key : 'innstudio');
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'decode' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : null;

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'decode' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = null;
    $box = range(0, 255);

    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'decode') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return false;
        }
    } else {
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}
/**
 * Get client IP
 *
 * @return string
 * @version 2.0.0
 * @author KM@INN STUDIO
 */
function get_client_ip(){
	return preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] );
}
/**
 * Name:			delete_files
 * Author:			Km.Van
 * Version:			1.0
 * Type:			function 函数
 * Return:			string(bool)
 * Explain:			删除文件
 * Example:			delete_files(file_path)
 * Update:			PM 06:10 2011/9/2
 */
function delete_files($file_path = null){
	if(!$file_path || !file_exists($file_path)) return false;
	return unlink($file_path);
}
/**
 * Name:			download_files
 * Author:			Km.Van
 * Version:			1.0
 * Type:			function 函数
 * Return:			string(display)
 * Explain:			下载文件名，保存路径，保存文件名，下载时限
 * Example:			download_files(str)
 * Update:			PM 11:28 2011/8/19
 */
 function download_files($url = null,$dir = null,$name = null,$time_limit = 300){
	if(!$url) return false;
	set_time_limit($time_limit);
	$dir = $dir ? $dir : __DIR__;
	
	if(!$name){
		$default_name = explode('/',$url);
		$default_name = $default_name[count($default_name) - 1];
		$name = $default_name;
	}
 
    $fp = fopen($dir.'/'.$name, 'w');
 
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
 
    $data = curl_exec($ch);
 
    curl_close($ch);
    fclose($fp);
	return true;
}

/**
 * Name:			err
 * Author:			Km.Van
 * Version:			1.0
 * Type:			function 函数
 * Return:			string(display)
 * Explain:			错误提示功能
 * Example:			err(str)
 * Update:			PM 11:28 2011/8/19
 */
function err($ErrMsg){
    header('HTTP/1.1 405 Method Not Allowed');
    echo $ErrMsg;
    exit;
}
/**
 * Name:			get_filemtime
 * Author:			Km.Van
 * Version:			1.2
 * Type:			function 函数
 * Return:			string(display)
 * Explain:			獲取文件修改日期
 * Example:			get_filemtime()
 * Update:			20130208
 */
function get_filemtime($file_name = null,$format = 'YmdHis'){
	if(!$file_name || !file_exists($file_name) || !is_file($file_name)) return false;
	$file_date = filemtime($file_name);
	$file_date = date($format,$file_date);
	return $file_date;
}
/**
 * get_img_source
 *
 * @param string
 * @return string
 * @version 1.0.2
 * @author KM@INN STUDIO
 */
function get_img_source($str = null){
	$output = null;
	if($str){
		$pattern = '!<img.*?src=["|\'](.*?)["|\']!';
		preg_match_all($pattern, $str, $matches);
		$output = isset($matches['1'][0]) ? $matches['1'][0] : false;
	}
	return $output;
}
/**
 * friendly_date
 *
 * @param string time
 * @return string
 * @version 1.0.1
 * @author KM@INN STUDIO
 */
function friendly_date($time = null){
	$text = null;
	$current_time = current_time('timestamp');
	$time = $time === null || $time > $current_time ? $current_time : intval($time);
	$t = $current_time - $time; //时间差 （秒）
	switch($t){
		case ($t < 3) :
			$text = ___('Just');
			break;
		/** 
		 * 60
		 */
		case ($t < 60) :
			$text = sprintf(___('%d seconds ago'),$t); // 一分钟内
			break;
		/** 
		 * 60 * 60 = 3600
		 */
		case ($t < 3600) :
			if($t < 2){
				$text = ___('a minute ago'); //一小时内
			}else{
				$text = sprintf(___('%d minutes ago'),floor($t / 60)); //一小时内
			}
			break;
		/** 
		 * 60 * 60 * 24 = 86400
		 */
		case ($t < 86400) :
			if($t < 2){
				$text = ___('an hour ago'); // 一天内
			}else{
				$text = sprintf(___('%d hours ago'),floor($t / 3600)); // 一天内
			}
			break;
		/** 
		 * 60 * 60 * 24 * 2 = 172800
		 */
		case ($t < 172800) :
			$text = ___('yesterday'); //两天内
			break;
		/** 
		 * 60 * 60 * 24 * 7 = 604800
		 */
		case ($t < 604800) :
			$text = sprintf(___('%d days ago'),floor($t / 86400)); // N天内
			break;
		/** 
		 * 60 * 60 * 24 * 365 = 31,536,000
		 */
		case ($t < 31536000) :
			$text = date(___('M j'), $time); //一年内
			break;
		default:
			$text = date(___('M j, Y'), $time); //一年以前
	}
	return $text;
}

/**
 * Name:			get_server_os_type
 * Author:			Km.Van
 * Version:			1.0
 * Type:			function 函数
 * Return:			string(not display)
 * Explain:			获取主机操作系统类型
 * Example:			get_server_os_type()
 * Update:			AM 10:59 2011/2/16
 */
function get_server_os_type(){
	$output = null;
	if(PATH_SEPARATOR==':'){
		$output = 'linux';
	}else{
		$output = 'windows';
	}
	return $output;
}
/**
 * kill_html
 *
 * @param string
 * @return string
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
function kill_html($str = null){
	$html = array( 
		/* 过滤多余空白*/
		"/\s+/",
		/* 过滤 <script>等 */
		"/<(\/?)(script|i?frame|style|html|body|title|link|meta|\?|\%)([^>]*?)>/isU",
		/* 过滤javascript的on事件 */
		"/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU"
	); 
	$tarr = array( 
		" ", 
		"＜\1\2\3＞",//如果要直接清除不安全的标签，这里可以留空 
		"\1\2", 
	); 
	$str = nl2br($str);
	$str = preg_replace($html,'',$str); 
	return $str; 
}
/**
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param string $email The email address
 * @param string $s Size in pixels, defaults to 80px [ 1 - 512 ]
 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
 * @param boole $img True to return a complete IMG tag False for just the URL
 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
 * @return String containing either just a URL or a complete image tag
 * @source http://gravatar.com/site/implement/images/php/
 */
function get_gravatar( $email, $url_only = 1, $s = 80, $d = 'mm', $r = 'g', $atts = array() ) {
	$server_domains = array(
		'0',
		'1',
		'2',
		'cn',
		'en'
	);
	$rand_i = array_rand($server_domains);
	$url = 'http://' . $server_domains[$rand_i] . '.gravatar.com/avatar/';
	$url .= md5( strtolower( trim( $email ) ) );
	$url .= "?s=$s&d=$d&r=$r";
	if(!$url_only){
		$url = '<img src="' . $url . '"';
		foreach ( $atts as $key => $val )
			$url .= ' ' . $key . '="' . $val . '"';
		$url .= ' />';
	}
	return $url;
}
/**
 * get_current_url
 *
 * @return string
 * @version 1.0.1
 * @author KM@INN STUDIO
 */
function get_current_url(){
	$output = $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$output .= $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"];
	return $output;
}

/**
 * str_sub
 *
 * @param string
 * @param int
 * @param string
 * @return string
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
function str_sub($str,$len = null,$extra = '...'){
	if(!trim($str)) return;
	if(!$len || mb_strlen(trim($str)) <= $len){
		return $str;
	}else{
		$str = mb_substr($str,0,$len);
		$str .= $extra;
	}
	return $str;
}

/**
 * url_sub
 *
 * @param string
 * @param int
 * @param int
 * @param int
 * @return string
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
function url_sub($url = null,$before_len = 30,$after_len = 20,$extra_len = 10,$middle_str = ' ... '){
	if(!$url) return;
	$url_len = mb_strlen($url);
	/* url 小于指定长度 */
	if($url_len <= ($before_len + $after_len + $extra_len)){
		return $url;
	}else{
		$url_before = mb_substr($url,0,$before_len);
		$url_after = mb_substr($url,-$after_len);
		return $url_before.$middle_str.$url_after;
	}
}
/* 检测浏览器 */
class get_browser{
	private static function detection(){
		$u_agent = $_SERVER['HTTP_USER_AGENT'];
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version= "";

		//First get the platform?
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'linux';
		}
		elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
		}
		elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}
	   
		// Next get the name of the useragent yes seperately and for good reason
		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
		{
			$bname = 'Internet Explorer';
			$ub = "MSIE";
		}
		elseif(preg_match('/Firefox/i',$u_agent))
		{
			$bname = 'Mozilla Firefox';
			$ub = "Firefox";
		}
		elseif(preg_match('/Chrome/i',$u_agent))
		{
			$bname = 'Google Chrome';
			$ub = "Chrome";
		}
		elseif(preg_match('/Safari/i',$u_agent))
		{
			$bname = 'Apple Safari';
			$ub = "Safari";
		}
		elseif(preg_match('/Opera/i',$u_agent))
		{
			$bname = 'Opera';
			$ub = "Opera";
		}
		elseif(preg_match('/Netscape/i',$u_agent))
		{
			$bname = 'Netscape';
			$ub = "Netscape";
		}
	   
		// finally get the correct version number
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) .
		')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}
	   
		// see how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
				$version= $matches['version'][0];
			}
			else {
				$version= $matches['version'][1];
			}
		}
		else {
			$version= $matches['version'][0];
		}
	   
		// check if we have a number
		if ($version==null || $version=="") {$version="?";}
	   
		return array(
			'userAgent' => $u_agent,
			'long_name'      => strtolower($bname),
			'name'		=> strtolower($ub),
			'version'   => strtolower($version),
			'platform'  => strtolower($platform),
			'pattern'   => $pattern
		);
	}
	static function get_long_name(){
		$ua = self::detection();
		return $ua['long_name'];
	}
	static function get_name(){
		$ua = self::detection();
		return $ua['name'];
	}
	static function get_version(){
		$ua = self::detection();
		$version = $ua['version'];
		$version_len = strlen($version);
		$version_dot = strpos($version,'.') + 2;
		$version = substr($version,0,$version_dot);
		$version = strtr($version,'.','_');
		return $version;
	}
	static function get_platform(){
		$ua = self::detection();
		return $ua['platform'];
	}
	private static function get_pattern(){
		$ua = self::detection();
		return $ua['pattern'];
	}
}

?>