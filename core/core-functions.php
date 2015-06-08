<?php
/**
 * Filter the js
 *
 * @param string $value HTML code
 * @return string
 * @version 1.0.0
 */
function fliter_script($value) {
	$value = preg_replace("/(javascript:)?on(click|load|key|mouse|error|abort|move|unload|change|dblclick|move|reset|resize|submit)/i","data-filter",$value);
	$value = preg_replace("/(.*?)<\/script>/si","",$value);
	$value = preg_replace("/(.*?)<\/iframe>/si","",$value);
	return $value;
}
/**
 * Output select tag html
 *
 * @param string $value Option value
 * @param string $text Option text
 * @param string $current_value Current option value
 * @return string
 * @version 1.0.0
 */
function get_option_list($value,$text,$current_value){
	ob_start();
	$selected = $value == $current_value ? ' selected ' : null;
	?>
	<option value="<?= esc_attr($value);?>" <?= $selected;?>><?= $text;?></option>
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
 */
function mult_search_array($key,$value,$array){ 
	$results = []; 
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
 * @version 1.0.1
 */
function check_referer($referer = null){
	static $home_url = null;
	if($home_url === null)
		$home_url = home_url();
		
	if(!$referer)
		$referer = $home_url;
		
	if(!isset($_SERVER['HTTP_REFERER']) || stripos($_SERVER['HTTP_REFERER'],$referer) !== 0){
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
 * @version 1.0.1
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
	}else{
		return !$arr ? true : false;
	}
}
/**
 * chmodr
 * 
 * @param string $path path or filepath
 * @param int $filemode
 * @return bool
 * @version 1.0.0
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
 * @version 1.0.2
 * 
 */
function mk_dir($target = null){
	if(!$target) return false;
	$target = str_replace('//', '/', $target); 
	if(file_exists($target)) return is_dir($target); 

	if(@mkdir($target)){
		$stat = stat(dirname($target)); 
		@chmod($target, 0755); 
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
 * Retrieve the translation of $text and escapes it for safe use in an attribute.
 *
 * If there is no translation, or the text domain isn't loaded, the original text is returned.
 *
 * @since 2.8.0
 *
 * @param string $text   Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated text on success, original text on failure.
 */
function esc_attr___( $text, $domain = null ) {
	if(!$domain)
		$domain = theme_functions::$iden;
	return esc_attr( translate( $text, $domain ) );
}
/**
 * Display translated text that has been escaped for safe use in an attribute.
 *
 * @since 2.8.0
 *
 * @param string $text   Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 */
function esc_attr__e( $text, $domain = null ) {
	if(!$domain)
		$domain = theme_functions::$iden;
	echo esc_attr( translate( $text, $domain ) );
}
/**
 * Display translated text that has been escaped for safe use in HTML output.
 *
 * @since 2.8.0
 *
 * @param string $text   Text to translate.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 */
function esc_html__e( $text, $domain = null ) {
	if(!$domain)
		$domain = theme_functions::$iden;
	echo esc_html( translate( $text, $domain ) );
}
/**
 * Translate string with gettext context, and escapes it for safe use in an attribute.
 *
 * @since 2.8.0
 *
 * @param string $text    Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated text
 */
function esc_attr__x( $text, $context, $domain = null ) {
	if(!$domain)
		$domain = theme_functions::$iden;
	return esc_attr( translate_with_gettext_context( $text, $context, $domain ) );
}
/**
 * Translate string with gettext context, and escapes it for safe use in HTML output.
 *
 * @since 2.9.0
 *
 * @param string $text    Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain  Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Translated text.
 */
function esc_html__x( $text, $context, $domain = null ) {
	if(!$domain)
		$domain = theme_functions::$iden;
	return esc_html( translate_with_gettext_context( $text, $context, $domain ) );
}
/**
 * Retrieve the plural or single form based on the supplied amount.
 *
 * If the text domain is not set in the $l10n list, then a comparison will be made
 * and either $plural or $single parameters returned.
 *
 * If the text domain does exist, then the parameters $single, $plural, and $number
 * will first be passed to the text domain's ngettext method. Then it will be passed
 * to the 'ngettext' filter hook along with the same parameters. The expected
 * type will be a string.
 *
 * @param string $single The text that will be used if $number is 1.
 * @param string $plural The text that will be used if $number is not 1.
 * @param int    $number The number to compare against to use either $single or $plural.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated strings.
 * @return string Either $single or $plural translated text.
 */
function __n($single,$plural,$number,$domain = null){
	if(!$domain)
		$domain = theme_functions::$iden;
	return _n($single,$plural,$number,$domain);
}
/**
 * __x
 *
 * @param string $text Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated 
 * @return string Translated context string without pipe.
 * @version 1.0.1
 */
function __ex($text,$context,$domain = null){
	if(!$domain)
		$domain = theme_functions::$iden;
	echo translate_with_gettext_context( $text, $context, $domain );
}
/**
 * __x
 *
 * @param string $text Text to translate.
 * @param string $context Context information for the translators.
 * @param string $domain Optional. Text domain. Unique identifier for retrieving translated 
 * @return string Translated context string without pipe.
 * @version 1.0.1
 */
function __x($text,$context,$domain = null){
	if(!$domain)
		$domain = theme_functions::$iden;
	return translate_with_gettext_context( $text, $context, $domain );
}
/**
 * __e()
 * translate function
 * 
 * @param string $text your translate text
 * @param string $domain your translate tdomain
 * @return string display
 * @version 1.1.0
 * 
 */
function __e($text,$domain = null){
	if(!$domain)
		$domain = theme_functions::$iden;
	echo translate( $text, $domain );
}
/**
 * ___()
 * translate function
 * 
 * @param string $text your translate text
 * @param string $domain your translate domain
 * @version 1.1.0
 * 
 */
function ___($text,$domain = null){
	if(!$domain)
		$domain = theme_functions::$iden;
	return translate( $text, $domain );
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
 */
function status_tip(){
	$args = func_get_args();
	if(empty($args))
		return false;
		
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
			$icon = 'spinner fa-pulse';
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
	exit;
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
function authcode($string, $operation = 'decode', $key = 'innstudio', $expiry = 0) {

    $ckey_length = 4;

    $key = md5($key);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'decode' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : null;

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'decode' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = null;
    $box = range(0, 255);

    $rndkey = [];
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
 */
function get_client_ip(){
	return preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] );
}
/**
 * delete file
 *
 * @version 1.0.0
 */
function delete_files($file_path){
	if(!file_exists($file_path)) 
		return false;
	return unlink($file_path);
}
/**
 * Download file
 *
 * @param string $url Remote file url
 * @param string $dir Saven dir
 * @param string $name Saven filename
 * @param int $time_limit
 * @return bool
 * @version 1.0.0
 */
 function download_file($url,$dir = null,$name = null,$time_limit = 300){
	set_time_limit($time_limit);
	
	if(!$dir)
		$dir = __DIR__;
	
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
 * Get file modify time
 *
 * @param string $filepath File path
 * @param string $format Date format
 * @return string
 * @version 1.0.0
 */
function get_filemtime($filepath,$format = 'YmdHis'){
	if(!is_file($filepath))
		return false;
	return date($format,filemtime($filepath));
}
/**
 * get_img_source
 *
 * @param string
 * @return string
 * @version 1.1.0
 */
function get_img_source($str,$all = false){
	$pattern = '/<img[^>]+src\s*=\s*[\"\']\s*([^\"\']+)/i';
	preg_match_all($pattern, $str, $matches);
	if($all){
		return $matches[1];
	}else{
		return isset($matches[1][0]) ? $matches[1][0] : null;
	}
		
}
/**
 * friendly_date
 *
 * @param string $time Format timestamp
 * @return string
 * @version 1.0.2
 */
function friendly_date($timestamp){
	$text = null;
	
	/** time difference */
	static $current_time = null;
	if($current_time === null)
		$current_time = current_time( 'timestamp' );
		
	$t = $current_time - $timestamp;

	switch($t){
		/**
		 * in 1 minu, just now
		 */
		case ($t < 60) :
			$text = ___('Just');
			break;
		/** 
		 * in 1 hours, 60 * 60 = 3600
		 */
		case ($t < 3600) :
			$text = sprintf(___('%dmin ago'),floor($t / 60));
			break;
		/** 
		 * in 1 day, 60 * 60 * 24 = 86400
		 */
		case ($t < 86400) :
			$text = sprintf(___('%dh ago'),floor($t / 3600));
			break;
		/** 
		 * in 1 month, 60 * 60 * 24 * 30 = 2592000
		 */
		case ($t < 2592000) :
			$text = sprintf(___('%dd ago'),floor($t / 86400));
			break;
		/**
		 * in 1 year, 60 * 60 * 24 * 30 * 12 = 31104000
		 */
		case ($t < 31104000) :
			$text = sprintf(___('%dm ago'),floor($t / 2592000));
			break;
		/**
		 * dislay date
		 */
		default:
			$text = date(___('M j, Y'), $timestamp);
	}
	return $text;
}
/**
 * get_current_url
 *
 * @return string
 * @version 1.0.2
 */
function get_current_url(){
	$url = $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
	$url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	return $url;
}

/**
 * str_sub
 *
 * @param string
 * @param int
 * @param string
 * @return string
 * @version 1.0.0
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
 * @param string $url URL address
 * @param int $before_len
 * @param int $after_len
 * @param int $extra_len
 * @param int $middle_str
 * @return string
 * @version 1.0.1
 */
function url_sub($url,$before_len = 30,$after_len = 20,$extra_len = 10,$middle_str = ' ... '){
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
?>