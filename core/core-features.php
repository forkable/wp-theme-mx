<?php

/**
 * WordPress Extra Theme Features.
 *
 * Help you write a wp site quickly.
 *
 * @package KMTF
 * @version 5.0.1
 * @author KM@INN STUDIO
 * @date 2015-04-13
 */
theme_features::init();
class theme_features{
	
	public static $basedir_js 					= '/static/js/';
	public static $basedir_js_src 				= '/static/js/src/';
	public static $basedir_js_min				= '/static/js/min/';
	public static $basedir_css		 			= '/static/css/';
	public static $basedir_css_src 				= '/static/css/src/';
	public static $basedir_css_min 				= '/static/css/min/';
	public static $basedir_images 				= '/static/images/';
	public static $basedir_images_src 			= '/static/images/src/';
	public static $basedir_images_min 			= '/static/images/min/';
	public static $basedir_includes 			= '/includes/';
	public static $basedir_core 				= '/core/';
	public static $basedir_languages 			= '/languages/';
	
	public static $iden = 'theme_features';	
	
	public static function init(){
		add_action('after_setup_theme',	__CLASS__ . '::after_setup_theme');
		add_action('wp_footer',			__CLASS__ . '::theme_js',20);
	}

	/**
	 * theme_js
	 * 
	 * @return 
	 * @example 
	 * @version 1.1.4
	 * @author KM (kmvan.com@gmail.com)
	 * @copyright Copyright (c) 2011-2013 INN STUDIO. (http://www.inn-studio.com)
	 **/
	public static function theme_js(){
		?>
		<script>
		<?php
		$config = [];
		$config['base'] = self::get_theme_js();
		$config['paths'] = array(
			'theme_js' => self::get_theme_js(),
			'theme_css' => self::get_theme_css(),
		);
		$config['vars'] = array(
			'locale' => str_replace('-','_',get_bloginfo('language')),
			'theme_js' => self::get_theme_js(),
			'theme_css' => self::get_theme_css(),
			'theme_images' => self::get_theme_images_url(),
			'process_url' => self::get_process_url(),
		);
		$config['map'] = [
			['.css','.css?v=' . theme_file_timestamp::get_timestamp()],
			['.js','.js?v=' . theme_file_timestamp::get_timestamp()],
		];
		/** 
		 * seajs hook
		 */
		$config['paths'] = apply_filters('frontend_seajs_paths',$config['paths']);
		$config['alias'] = apply_filters('frontend_seajs_alias',[]);
		$config['vars'] = apply_filters('frontend_seajs_vars',$config['vars']);
		$config['map'] = apply_filters('frontend_seajs_map',$config['map']);
		?>
		seajs.config(<?= json_encode($config);?>);
		<?php
		/** Hook 'frontend_seajs_use' */
		do_action('frontend_seajs_use');
		?>
		</script>
		<?php
	}
	/**
	 * minify_force
	 *
	 * @param string
	 * @return 
	 * @version 1.0.2
	 * @author KM@INN STUDIO
	 */
	public static function minify_force($file_or_dir){
		/** 
		 * is dir
		 */
		if(is_dir($file_or_dir)){
			$files = glob($file_or_dir . '/*');
			if(!empty($files)){
				foreach($files as $file){
					self::minify_force($file);
				}
			}
		/** 
		 * is file
		 */
		}else{
			$file = $file_or_dir;
			$pi = pathinfo($file);
			$ext = strtolower($pi['extension']);
			/** 
			 * not js or css ,return
			 */
			if($ext !== 'js' && $ext !== 'css') return;
			/** 
			 * replace // to /,\ to /,\\ to /
			 */
			$search = array('//','\\','\\\\','/\\','\\/');
			$replace = '/';
			$file = str_replace($search,$replace,$file);
			/** 
			 * check has /src/ keyword and minify
			 */
			$self_basedir_src = 'basedir_' . $ext . '_src';
			$self_basedir_min = 'basedir_' . $ext . '_min';
			$found = stristr($file,self::$$self_basedir_src);
			if($found !== false){
				$file_min = str_ireplace(self::$$self_basedir_src,self::$$self_basedir_min,$file);
				self::minify($file,$file_min);
			}
		}
	}
	/**
	 * get_theme_info
	 *
	 * @param string get what 
	 * @param string 
	 * @param string 
	 * @return string
	 * @version 1.0.3
	 * @see http://codex.wordpress.org/Function_Reference/wp_get_theme
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_info($key = null,$stylesheet = null, $theme_root = null){
		static $caches = [],$theme = null;
		$cache_id = md5(serialize(func_get_args()));
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
			
		if($theme === null)
			$theme = wp_get_theme($stylesheet = null, $theme_root = null);
		
		if(!$key){
			$caches[$cache_id] = $theme;
			return $caches[$cache_id];
		}
		
		$key = ucfirst($key);
		$caches[$cache_id] = $theme->get($key);
		return $caches[$cache_id];
	}

	/**
	 * set_theme_version
	 *
	 * @return n/a
	 * @version 1.0.2
	 * @author KM@INN STUDIO
	 */
	public static function set_theme_info(){
		$theme = self::get_theme_info();
		set_transient(theme_functions::$iden . 'theme_info',$theme);
	}
	public static function get_stylesheet_directory(){
		static $cache = null;
		if($cache === null)
			$cache = get_stylesheet_directory();

		return $cache;
	}
	public static function get_template_directory_uri(){
		static $cache = null;
		if($cache === null)
			$cache = get_template_directory_uri();

		return $cache;
	}
	/**
	 * get_theme_file_url
	 * return your file for load under the theme.
	 * 
	 * @param string $file_basename the file name, like 'functions.js'
	 * @param bool/string $version The file version
	 * @return string
	 * @version 1.2.5
	 * @since 3.3.0
	 * @author INN STUDIO
	 * 
	 */
	public static function get_theme_file_url($file_basename = null,$version = false){

		if(!$file_basename)
			return self::get_template_directory_uri();
		

		
		/**
		 * fix basename string
		 */
		$file_basename = $file_basename[0] === '/' ? $file_basename : '/' .$file_basename;
		/**
		 * get file url and path full
		 */
		$file_url = self::get_template_directory_uri() . $file_basename;
		//$file_path = self::get_stylesheet_directory() . $file_basename;

		/**
		 * get file mtime
		 */
		if($version === true){
			$file_url = $file_url . '?v=' . theme_file_timestamp::get_timestamp();
		}
		
		return $file_url;
	}
	/**
	 * minify
	 * 
	 * @param string 
	 * @param string 
	 * @return n/a
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	public static function minify($file_path = null,$file_path_min){
		if(!file_exists($file_path)) return false;
		$file_path_info = pathinfo($file_path);
		/**
		 * minify
		 */
		switch(strtolower($file_path_info['extension'])){
			/**
			 * JS file
			 */
			case 'js':
				if(!class_exists('theme_includes\JSMin')) 
					include theme_features::get_theme_includes_path('class/jsmin.php');
					
				$source = file_get_contents($file_path);
				$min = theme_includes\JSMin::minify($source);
				mk_dir(dirname($file_path_min));
				file_put_contents($file_path_min,$min);
				unset($min);
				break;
			/**
			 * CSS file
			 */
			case 'css':
				if(!class_exists('theme_includes\CSSMin')) 
					include theme_features::get_theme_includes_path('class/cssmin.php');
					
				$source = file_get_contents($file_path);
				$cssmin = new theme_includes\CSSMin();
				$min = $cssmin->run($source);
				mk_dir(dirname($file_path_min));
				file_put_contents($file_path_min,$min);
				unset($min);
				break;
			/**
			 * Other
			 */
			default:
				
			
		}
	}
	/**
	 * theme_features::get_theme_js
	 * 
	 * @since 3.2.0
	 * @version 1.2.3
	 * @param string $file_basename js file basename, function = function.js
	 * @param bool $url_only Only output the file src if true
	 * @param bool/string $version The file version
	 * @return string <script> tag string or js url only
	 */
	public static function get_theme_js($file_basename = null,$url_only = true,$version = false){
		static $caches = [];
		/**
		 * cache
		 */
		$cache_id = $file_basename . $url_only . $version;
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
			
		
		$basedir = self::$basedir_js_min;
		/**
		 * if dev mode is ON
		 */
		if(class_exists('theme_dev_mode') && theme_dev_mode::is_enabled()){
			$basedir = self::$basedir_js_src;
		}
		
		/**
		 * file_basename
		 */
		if(!$file_basename) return self::get_template_directory_uri() . $basedir;
		/**
		 * extension
		 */
		$extension = '.js';
		/**
		 * get path info
		 */
		$path_info = pathinfo($file_basename);
		/**
		 * redefine $file_basename
		 */
		$file_basename = isset($path_info['extension']) && $path_info['extension'] === 'js' ? $file_basename : $file_basename . $extension;
		$file_url = self::get_theme_file_url($basedir . $file_basename,$version);
		$caches[$cache_id] = $url_only ? $file_url : '<script src="' . esc_url($file_url) . '"></script>';
		
		return $caches[$cache_id];
	}
	/**
	 * theme_features::get_theme_css
	 * 
	 * @since 3.2.0
	 * @version 1.2.3
	 * @param string $file_basename css file basename, style = style.css
	 * @param mix $args
	 * @param bool/string $version The file version
	 * @return string url only /<link...> by $args
	 */
	public static function get_theme_css($file_basename = null,$args = null,$version = true){
		static $caches = [];
		
		/**
		 * cache
		 */
		$cache_id = md5(serialize(func_get_args()));
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];

			
		$basedir = self::$basedir_css_min;
		/**
		 * if dev mode is ON
		 */
		if(class_exists('theme_dev_mode') && theme_dev_mode::is_enabled()){
			$basedir = self::$basedir_css_src;
		}
			
		/**
		 * file_basename
		 */
		if(!$file_basename) return self::get_template_directory_uri() . $basedir;
		/**
		 * extension
		 */
		$extension = '.css';
		/**
		 * get path info
		 */
		$path_info = pathinfo($file_basename);
		/**
		 * redefine $file_basename
		 */
		$file_basename = isset($file_info['extension']) ? $file_basename : $file_basename . $extension;
		$file_url = self::get_theme_file_url($basedir . $file_basename,$version);
		/**
		 * check the $args
		 */
		if(!$args){
			$caches[$cache_id] = $file_url;
		}else if($args === 'normal'){
			$caches[$cache_id] = '<link id="' . $path_info['filename'] . '" href="' . esc_url($file_url) .'" rel="stylesheet" media="all"/>';
		}else if(is_array($args)){
			/* check the array of $args and to release */
			$ext = null;
			foreach($args as $key => $value){
				$ext .= ' ' .$key. '="' .$value. '"';
			}
			$caches[$cache_id] = '<link id="' . $path_info['filename'] . '" href="' . esc_url($file_url) .'" rel="stylesheet" ' .$ext. '/>';
		}

		return $caches[$cache_id];
	}
	/**
	 * get_theme_extension_url
	 * 
	 * @param array $args = [
			'type' => 'includes/features',
			'basedir' => null,
			'file_basename' => null,
			'mtime' => null,
			'url_only' => true,
		 ]
	 * @return 
	 * @version 2.0.0
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_extension_url(array $args = []){
		$defaults = [
			'type' => null,
			'basedir' => null,
			'file_basename' => null,
			'ext' => null,
			'mtime' => false,
			'url_only' => true,
		];
		$args = array_merge($defaults,$args);
		
		$dev_mode = class_exists('theme_dev_mode') && theme_dev_mode::is_enabled() ? true : false;


		$file_ext = substr(strrchr($args['file_basename'], '.'), 1);

		
		$self_basedir_type = 'basedir_' . $args['type'];
		$self_get_theme_type_url = 'get_theme_' . $args['type'] . '_url';

		if($dev_mode){
			$self_basedir_extension = 'basedir_' . $args['ext'] . '_src';
		}else{
			$self_basedir_extension = 'basedir_' . $args['ext'] . '_min';
		}

		if(!empty($args['type'])){
			$file_path = $args['basedir'] . self::$$self_basedir_extension . $args['file_basename'];

			$self_basedir = 'basedir_' . $args['type'];
			$file_url = self::get_theme_url() . self::$$self_basedir . basename($args['basedir']) . self::$$self_basedir_extension . $args['file_basename'];
			
		}
		//if(!file_exists($file_path))
		//	return false;

		//$mtime = theme_file_timestamp::get_timestamp();
		
		/**
		 * return if not url_only
		 */
		if($args['url_only'] === false)
			return [
				'path' => $file_path,
				'url' => $file_url,
				//'mtime' => $mtime,
			];
		
		//$version = '?v=' . $mtime;

		//return $file_url . $version;
		return $file_url;
	}
	
	/**
	 * get_theme_includes_js
	 * 
	 * @param string $DIR
	 * @param string $filename
	 * @param bool $mtime
	 * @return string
	 * @version 2.0.0
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_includes_js($DIR = null,$filename = 'init',$url_only = true){
		static $caches = [];
		$cache_id = md5($DIR.$filename.$url_only);
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
		
		$path_info = pathinfo($filename);
		if(!isset($path_info['extension']) || empty($path_info['extension']) || $path_info['extension'] !== 'js'){
			$file_basename = $filename . '.js';
		}else{
			$file_basename = $filename;
		}
		$args = [
			'type' => 'includes',
			'basedir' => $DIR,
			'ext' => 'js',
			'file_basename' => $file_basename,
			'url_only' => $url_only,
		];
		
		$caches[$cache_id] = self::get_theme_extension_url($args);
		return $caches[$cache_id];
	}
	/**
	 * get_theme_includes_css
	 * 
	 * @param string $DIR
	 * @param string $filename
	 * @param bool $mtime
	 * @return string
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_includes_css($DIR = null,$filename = 'style',$url_only = true, $mtime = false){
		
		$path_info = pathinfo($filename);
		if(!isset($path_info['extension']) || empty($path_info['extension']) || $path_info['extension'] !== 'css'){
			$file_basename = $filename . '.css';
		}else{
			$file_basename = $filename;
		}
		$args = [
			'type' => 'includes',
			'basedir' => $DIR,
			'ext' => 'css',
			'file_basename' => $file_basename,
			'url_only' => $url_only,
		];
		if($mtime === true){
			$mtime = '?v=' . theme_file_timestamp::get_timestamp();
		}
		return self::get_theme_extension_url($args) . $mtime;
	}
	/**
	 * get_theme_includes_image
	 * 
	 * @param string $DIR
	 * @param string $filename
	 * @return string
	 * @version 1.0.3
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_includes_image($DIR,$filename){
		static $caches = [];
		
		$cache_id = md5($DIR.$filename);
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
			
		$args = [
			'type' => 'includes',
			'basedir' => basename($DIR) . self::$basedir_images_min,
			'file_basename' => $filename,
		];

		$url = self::get_template_directory_uri() . '/' . $args['type'] . '/' . $args['basedir'] . $args['file_basename'] . '?v=' . theme_file_timestamp::get_timestamp();

		$caches[$cache_id] = esc_url($url);
		return $caches[$cache_id];
	}
	/**
	 * get_theme_extension_url_core
	 * 
	 * @param array $args
	 * @param bool $mtime
	 * @return string
	 * @version 1.0.2
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_extension_url_core($args = null){
		/**
		 * $args = array(
		 *	'type' => 'includes/features',
		 *	'basedir' => null,
		 *	'file_basename' => null,
		 *	'mtime' => null,
		 * )
		 */
		if(!$args || !is_array($args)) return false;
		$defaults = array(
			'type' => null,
			'basedir' => null,
			'file_basename' => null,
			'mtime' => true,
		);
		$r = array_merge($defaults,$args);
		extract($r,EXTR_SKIP);
		
		
		/**
		 * check $basedir
		 */
		if(!$basedir) return self::get_theme_url();
		$basedir_pi = pathinfo($basedir);
		$basedir = basename($basedir);
		
		if(!$file_basename) 
			return self::get_theme_url() . '/' . $type . '/' . $basedir;
		
		/**
		 * self::get_theme_includes_path / self::get_theme_features_path
		 */
		$self_get_theme_type_path = 'get_theme_' . $type . '_path';
		/**
		 * self::$basedir_includes / self::$basedir_features
		 */
		$self_basedir_type = 'basedir_' . $type;
		
		$dir_path = self::$self_get_theme_type_path('/' . $basedir . '/');
		/**
		 * pathinfo
		 */
		$path_info = pathinfo($file_basename);
		$extension = $path_info['extension'];
		
		/**
		 * file type
		 */
		if($extension === 'js' || $extension === 'css'){
			/**
			 * self::$basedir_js_src / self::$basedir_css_src
			 */
			$self_basedir_extension_src = 'basedir_' . $extension . '_src';
			
			$file_path_src = $dir_path . self::$$self_basedir_extension_src . $file_basename;
			//if(!file_exists($file_path_src)) return false;
			/**
			 * file url src
			 */
			$file_url = self::get_theme_url() . self::$$self_basedir_type . $basedir . self::$$self_basedir_extension_src . $file_basename;

			/**
			 * if dev mode is ON
			 */
			if(class_exists('theme_dev_mode') && theme_dev_mode::is_enabled()){
				$file_path = $file_path_src;
			}else{
				$self_basedir_extension_min = 'basedir_' . $extension . '_min';
				$file_url = self::get_theme_url() . self::$$self_basedir_type . $basedir . self::$$self_basedir_extension_min . $file_basename;
			}
		}else{
			$file_url = self::get_theme_url() . self::$$self_basedir_type . $basedir . $file_basename;
		}
		/**
		 * get file mtime
		 */
		if($mtime === true){
			if(file_exists($file_path_src)){
				$file_mtime = filemtime($file_path_src);
			}else{
				$file_mtime = self::get_theme_info('version');
			}
		}

		$mtime_str = $mtime ? '?v=' . $file_mtime : null;
		return $file_url . $mtime_str;
	}
	
	
	/**
	 * get_theme_includes_url
	 * 
	 * @param string $DIR
	 * @param string $file_basename
	 * @param bool $mtime
	 * @return string
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_includes_url($DIR = null,$file_basename = null,$mtime = false){
	
		$args = array(
			'type' => 'includes',
			'basedir' => basename($DIR),
			'file_basename' => $file_basename,
			'mtime' => $mtime,
		);
		return self::get_theme_extension_url_core($args);
	}
	/**
	 * get_theme_images_url
	 * 
	 * @param string $file_basename
	 * @param bool $mtime
	 * @return string
	 * @version 1.0.3
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_images_url($file_basename = null,$mtime = false){
		static $caches = [];
		
		$cache_id = $file_basename.$mtime;
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
			
		if(!$file_basename) return self::get_template_directory_uri() . self::$basedir_images_min;
		$file_url = self::get_theme_file_url(self::$basedir_images_min . $file_basename,$mtime);

		$caches[$cache_id] = $file_url;
		return $file_url;
	}
	/**
	 * get the process file url of theme
	 * 
	 * @param array $param The url args
	 * @return string The process file url
	 * @version 1.2.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_process_url(array $param = []){
		static $admin_ajax_url = null;
		
		if($admin_ajax_url === null)
			$admin_ajax_url = admin_url('admin-ajax.php');

		if(!$param) 
			return $admin_ajax_url;
		
		return $admin_ajax_url . '?' . http_build_query($param);
	}
	/**
	 * json_format
	 *
	 * @param object
	 * @return string
	 * @version 1.0.3
	 * @author KM@INN STUDIO
	 */
	public static function json_format(array $output = [],$die = false,$jsonp = false){
		if(empty($output)) 
			return false;
		/** Reduce the size but will inccrease the CPU load */
		$output = json_encode(array_multiwalk($output,'html_compress'));
		//$output = json_encode($output);
		/**
		 * If the remote call, return the jsonp format
		 */
		if(isset($_GET['callback']) && is_string($_GET['callback']) && !empty($_GET['callback'])){
			$jsonp = $_GET['callback'];
			$output = $jsonp. '(' .$output. ')';
		}else{
			header('Content-Type: application/javascript');
		}

		return $die ? die($output) : $output;

	}
	public static function is_mobile(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)wp_is_mobile();

		return $cache;
	}
	/**
	 * check_referer
	 *
	 * @param string
	 * @return bool
	 * @version 1.1.0
	 * @author KM@INN STUDIO
	 */
	public static function check_referer($referer = null){
		static $home_url = null;
		if($home_url === null)
			$home_url = home_url();
			
		if(!$referer)
			$referer = $home_url;

		if(!isset($_SERVER['HTTP_REFERER']) || stripos($_SERVER["HTTP_REFERER"],$referer) !== 0){
			$output = array(
				'status' => 'error',
				'code' => 'invalid_referer',
				'msg' => ___('Sorry, referer is invalid.')
			);
			die(theme_features::json_format($output));
		}
	}
	public static function create_nonce($key = 'theme-nonce'){
		static $nonce = null;
		if($nonce === null)
			$nonce = wp_create_nonce($key);

		return $nonce;
	}
	/**
	 * Check theme nonce code
	 *
	 * @return 
	 * @version 1.3.0
	 * @author KM@INN STUDIO
	 */
	public static function check_nonce($action = 'theme-nonce',$key = 'theme-nonce'){
		$nonce = isset($_REQUEST[$action]) ? $_REQUEST[$action] : null;
		if(!wp_verify_nonce($nonce,$key)){
			$output = array(
				'status' => 'error',
				'code' => 'invalid_security_code',
				'msg' => ___('Sorry, security code is invalid.')
			);
			die(theme_features::json_format($output));
		}
	}
	/**
	 * Get user avatar
	 *
	 * @param int|object $user The user stdClass object or user ID
	 * @return string
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function get_avatar($user,$size = 80,$default = null,$alt = null){
		$cache_id = md5(serialize(func_get_args()));
		
		static $caches = [];
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
			
		if(is_object($user)){
			$user_id = $user->ID;
		}else{
			$user_id = (int)$user;
			$user = get_user_by('id',$user);
		}

		/** check avatar from user meta */
		$caches[$cache_id] = get_user_meta($user_id,'avatar',true);
		/** check avatar from  */
		if(!$caches[$cache_id]){
			$caches[$cache_id] = get_avatar($user->user_email,$size, $default, $alt);
		}
		return $caches[$cache_id];
	}
	/**
	 * theme_features::get_theme_url
	 * 
	 * @param 
	 * @param 
	 * @return 
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_url($file_basename = null,$mtime = false){
		
		static $caches;
		$cache_id = $file_basename.$mtime;
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
			
		if(!$file_basename){
			$caches[$cache_id] = self::get_template_directory_uri();
			return $caches[$cache_id];
		}
		$file_url = self::get_theme_file_url($file_basename,$mtime);

		$caches[$cache_id] = $file_url;
		return $file_url;
	}
	/**
	 * theme_features::get_theme_path
	 * 
	 * @param string $file_basename
	 * @return string $file_path
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_path($file_basename = null){
		static $caches;
		$cache_id = md5($file_basename);
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
		
		if(!$file_basename) return get_stylesheet_directory() . '/';
		$file_path = $file_basename[0] === '/' ? self::get_stylesheet_directory() . $file_basename : self::get_stylesheet_directory() . '/' . $file_basename;

		$caches[$cache_id] = $file_path;
		return $file_path;
	}
	/**
	 * theme_features::get_theme_includes_path
	 * 
	 * @param string $file_basename
	 * @return string $file_path
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_includes_path($file_basename = null){
		static $caches;
		$cache_id = md5($file_basename);
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
		
		if(!$file_basename) return self::get_stylesheet_directory() . self::$basedir_includes;
		$file_path = $file_basename[0] === '/' ? self::get_stylesheet_directory() . self::$basedir_includes . $file_basename : self::get_stylesheet_directory() . self::$basedir_includes . $file_basename;
		
		$caches[$cache_id] = $file_path;
		return $file_path;
	}
	/**
	 * Get post thumbnail src, if the post have not thumbnail, then get the first image from post content.
	 *
	 * @version 1.1.0
	 * @param int $post_id The post ID, default is global $post->ID
	 * @param string $size Thumbnail size
	 * @return string Placeholder img url
	 */
	public static function get_thumbnail_src($post_id = null,$size = 'thumbnail',$replace_img = null){
		static $caches = [];
		$cache_id = md5(serialize(func_get_args()));
		
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];
			
		if(!$post_id){
			global $post;
			$post_id = $post->ID;
		}
		
		$src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id),$size);
		
		if(!empty($src)){
			$caches[$cache_id] = $src[0];
			return $caches[$cache_id];
		}
			
		/**
		 * have not thumbnail, get first img from post content
		 */
		$caches[$cache_id] = get_img_source($post->post_content);
		if($caches[$cache_id])
			return $caches[$cache_id];

		if($replace_img)
			return $replace_img;

		return null;
		
	}
	/**
	 * Get post excerpt and limit string lenght
	 *
	 * @param int $len Limit string
	 * @param string $extra The more string
	 * @return string
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_post_excerpt($len = 120,$extra = '...'){
		static $caches = [];
		global $post;
		
		if(isset($caches[$post->ID]))
			return $caches[$post->ID];
			
		$excerpt = get_the_excerpt();
		
		if($excerpt){
			$caches[$post->ID] = str_sub($excerpt,$len,$extra);
		}else{
			$caches[$post->ID] = str_sub(get_the_content(),$len,$extra);
		}
		unset($excerpt);
		
		return $caches[$post->ID];
	}
	/**
	 * Get the thumbnail source of preovious post
	 * 
	 * 
	 * @param string $replace_img
	 * @param string $size
	 * @return string
	 * @version 1.0.1
	 * @since 3.0.0
	 * @author INN STUDIO
	 * 
	 */
	public static function get_previous_thumbnail_src($replace_img = null,$size = 'thumbnail'){
		global $post;
		$post_obj = get_previous_post();
		if($post_obj->ID){
			$thumb_src = theme_features::get_thumbnail_src($post_obj->ID,$size,$replace_img);
		}else{
			$thumb_src = null;
		}
		return $thumb_src;
	}
	/**
	 * Get the thumbnail source of next post
	 * 
	 * 
	 * @param string $replace_img
	 * @param string $size
	 * @return string
	 * @version 1.0.1
	 * @since 3.0.0
	 * @author INN STUDIO
	 * 
	 */
	public static function get_next_thumbnail_src($replace_img = null,$size = 'thumbnail'){
		global $post;
		$post_obj = get_next_post();
		if($post_obj->ID){
			$thumb_src = theme_features::get_thumbnail_src($post_obj->ID,$size,$replace_img);
		}else{
			$thumb_src = null;
		}
		return $thumb_src;
	}
	/* 获取当前项 ================================================== */
	/**
	 * get_current_tag_obj (in tag page)
	 * 
	 * @return stdClass
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function get_current_tag_obj(){
		if(is_tag()){
			static $cache = null;
			if($cache !== null)
				return $cache;
				
			$tag_id = self::get_current_tag_id();
			$tag_obj = get_tag($tag_id);
			$cache = $tag_obj;
			return $cache;
		}
	}
	/**
	 * get_current_tag_name (in tag page)
	 * 
	 * @return string
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	public static function get_current_tag_name(){
		if(is_tag()){
			$tag_obj = self::get_current_tag_obj();
			$tag_name = $tag_obj->name;
			return $tag_name;
		}
	}
	/**
	 * get_current_tag_slug (in tag page)
	 * 
	 * @return string
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	public static function get_current_tag_slug(){
		if(is_tag()){
			$tag_obj = self::get_current_tag_obj();
			$tag_slug = $tag_obj->slug;
			return $tag_slug;
		}
	}
	/**
	 * get_current_tag_count (in tag page)
	 * 
	 * @return int
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	public static function get_current_tag_count(){
		if(is_tag()){
			$tag_obj = self::get_current_tag_obj();
			$tag_count = $tag_obj->count;
			return $tag_count;
		}
	}
	/**
	 * get_current_tag_id (in tag page)
	 * 
	 * @return int
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	public static function get_current_tag_id(){
		if(is_tag()){
			global $wp_query;
			$tag_id = $wp_query->query_vars['tag_id'];
			return $tag_id;
		}
	}
	/* 分类目录项目 ================================================= */
	/* 详细查询 http://codex.wordpress.org/Function_Reference#Functions_by_category */
	/**
	 * Get the current category name
	 * 
	 * 
	 * @return string The current category name(not slug)
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_current_cat_name(){
		$cat_obj = get_category(self::get_current_cat_id());
		$cat_name = $cat_obj->name;
		return $cat_name;
	}
	/**
	 * get the current category slug
	 * 
	 * 
	 * @return string The current category slug
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_current_cat_slug(){
		$cat_obj = get_category(self::get_current_cat_id());
		$cat_slug = $cat_obj->slug;
		return $cat_slug;
	}
	/**
	 * get the current category id
	 * 
	 * 
	 * @return int The current category id
	 * @version 1.1.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_current_cat_id(){
		static $cache = null;
		if($cache !== null)
			return $cache;
			
		global $cat,$post;
		if($cat){
			$cat_obj = get_category($cat);
			$cat_id = $cat_obj->term_id;
		}else if($post){
			$cat_obj = get_the_category($post->ID);
			$cat_id = isset($cat_obj[0]) ? $cat_obj[0]->cat_ID : 0;
		}
		$cache = $cat_id;
		return $cache;
	}
	/**
	 * get the root of category id
	 * 
	 * @param int (optional) $current_cat_id The category id
	 * @return int The root of category id
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_cat_root_id($current_cat_id = null){
		/* 如果无参数，进行备选方案 */
		$current_cat_id = $current_cat_id ? $current_cat_id : self::get_current_cat_id();
		/* 获取目录对象 */
		$current_cat_parent_obj = get_category($current_cat_id);
		/* 获取父目录ID */
		$current_cat_parent_id = $current_cat_parent_obj->category_parent;
		/* 获取当前目录ID */
		$current_cat_id = $current_cat_parent_obj->term_id;
		/* 存在父目录 */
		if($current_cat_parent_id != 0){
			/* 循环判断 */
			return self::get_cat_root_id($current_cat_parent_id);
		/* 已经是父目录 */
		}else{
			$have_parent_cat = false;
			/* 返回根目录ID */
			return $current_cat_id;
		}
	}
	/**
	 * get the root of category slug
	 * 
	 * @param int (optional) $current_cat_id
	 * @return string  The category slug
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_cat_root_slug($current_cat_id = null){
		$current_cat_obj = get_category(self::get_cat_root_id($current_cat_id));
		$current_cat_slug = $current_cat_obj->slug;
		return $current_cat_slug;
	}
	/**
	 * get the category id by category slug
	 * 
	 * 
	 * @param string $cat_slug
	 * @return 
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_cat_id_by_slug($cat_slug = null) {
		if(!$cat_slug) return false;
		$cat_obj = get_category_by_slug($cat_slug); 
		$cat_id = $cat_obj->term_id;
		$output = $cat_id;
		return $output;
	}
	/**
	 * get category slug by category id
	 * 
	 * 
	 * @return string The category slug
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_cat_slug_by_id($cat_id = null){
		if(!$cat_id) return false;
		$cat_obj = get_category($cat_id,false); 
		$cat_slug = $cat_obj->slug;
		$output = $cat_slug;
		return $output;
	}
	/* PAGE 相关 ============================== */
	/**
	 * get current page id
	 * 
	 * 
	 * @return int The page id
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_current_page_id(){
		global $page_id;
		if(!$page_id){
			global $post;
			$page_id = $post->ID;
		}
		return $page_id;
	}
	/**
	 * get_page_url_by_slug
	 *
	 * @param string page slug
	 * @return string url
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	public static function get_page_url_by_slug($slug){
		static $caches = [];
		if(isset($caches[$slug]))
			return $caches[$slug];
			
		$id = self::get_page_id_by_slug($slug);
		$caches[$slug] = get_permalink($id);
		return $caches[$slug];
	}
	/**
	 * get page id by page slug
	 * 
	 * 
	 * @return int The page id
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_page_id_by_slug($slug){
		static $caches = [];
		if(isset($caches[$slug]))
			return $caches[$slug];
			
		$page_obj = get_page_by_path($slug);
		if ($page_obj) {
			$caches[$slug] = $page_obj->ID;
		}else{
			$caches[$slug] = false;
		}
		return $caches[$slug];
	}
	
	/**
	 * Get theme local dir
	 * 
	 * 
	 * @param string $file_name The file name
	 * @return string The file path
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_wp_themes_local_dir($file_name = null){
		static $caches = [];
		if(isset($caches[$file_name]))
			return $caches[$file_name];
			
		$basedir_name = '/themes/';
		$file_name = $file_name ? '/' .$file_name : '/';
		$caches[$file_name] = self::get_stylesheet_directory().$basedir_name.$file_name;
		return $caches[$file_name];
	}
	/**
	 * get_link_page_url
	 *
	 * @param int $page
	 * @return string The link page url
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function get_link_page_url($page = 1,$add_fragment = null){
		global $wp_rewrite,$post;
		$post = get_post();

		if ( 1 == $page ) {
			$url = get_permalink();
		} else {
			if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) )
				$url = add_query_arg( 'page', $page, get_permalink() );
			elseif ( 'page' == get_option('show_on_front') && get_option('page_on_front') == $post->ID )
				$url = trailingslashit(get_permalink()) . user_trailingslashit("$wp_rewrite->pagination_base/" . $page, 'single_paged');
			else
				$url = trailingslashit(get_permalink()) . user_trailingslashit($page, 'single_paged');
		}
		return $add_fragment ? esc_url($url) . '#' . $add_fragment : esc_url($url);	
	}
	/**
	 * get_prev_next_pagination
	 *
	 * @param array
	 * @return string
	 * @version 1.0.2
	 * @author KM@INN STUDIO
	 */
	public static function get_prev_next_pagination($args = null){
		global $page,$numpages,$post;
		/** 
		 * if total 1 page, nothing to do
		 */
		if($numpages == 1) return false;
		
		$defaults = array(
			'nav_class' => 'pagination-pn',
			'add_fragment' => 'post-' . $post->ID,
			'numbers_class' => [],
			'middle_class' => '',
		);
		$r = array_merge($defaults,$args);
		extract($r,EXTR_SKIP);

		$first_class = null;
		$last_class = null;
		$prev_page_url = self::get_link_page_url($page - 1,$add_fragment);
		$next_page_url = self::get_link_page_url($page + 1,$add_fragment);
		
		/** 
		 * last and first page
		 */
		$numbers_class_str = implode(' ',$numbers_class);
		
		//last page
		if($numpages != 1 && $numpages == $page){
			$last_class = 'numbers-last disabled';
			$next_page_url = 'javascript:;';
		}
		//first page
		if($page == 1 && $numpages != $page){
			$first_class = 'numbers-first disabled';
			$prev_page_url = 'javascript:;';
		}
		
		ob_start();
		?>
		<nav class="<?= esc_attr($nav_class);?>">
			<?php 
			ob_start(); 
			?>
			<a href="<?= $prev_page_url;?>" class="page-numbers page-prev <?= esc_attr($numbers_class_str);?> <?= $first_class;?>">
				<?= esc_html(___('&lsaquo; Previous'));?>
			</a>
			<?php
			$prev_page_str = ob_get_contents();
			ob_end_clean();
			/** 
			 * hook get_prev_pagination_link
			 * @param int $page Current page
			 * @param int $numpages Max page number
			 */
			echo apply_filters('prev_pagination_link',$prev_page_str,$page,$numpages);
			?>
			<div class="page-numbers page-middle <?= esc_attr($middle_class);?>">
				<span class="page-middle-btn"><?= $page , ' / ' , $numpages;?></span>
				<div class="page-middle-numbers">
				<?php
				for($i=1;$i<=$numpages;++$i){
					$url = self::get_link_page_url($i,$add_fragment);
					/** 
					 * if current page
					 */
					if($i == $page){
						?>
						<span class="numbers current"><?= $i;?></span>
					<?php }else{ ?>
						<a href="<?= esc_url($url);?>" class="numbers"><?= $i;?></a>
					<?php 
					}
				}
				?>
				</div>
			</div>
			
			<?php ob_start(); ?>
			<a href="<?= $next_page_url;?>" class="page-numbers page-next <?= esc_attr($numbers_class_str);?> <?= $last_class;?>">
				<?= esc_html(___('Next &rsaquo;'));?>
			</a>
			<?php
			$next_page_str = ob_get_contents();
			ob_end_clean();
			echo apply_filters('next_pagination_link',$next_page_str,$page,$numpages);
			?>

		</nav>
		
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	/**
	 * get_pagination
	 *
	 * @param array $args
	 * @return string 
	 * @version 1.0.2
	 * @author KM@INN STUDIO
	 */
	public static function get_pagination($args = null){
		global $page, $numpages, $multipage, $more, $pagenow;
		$defaults = array(
			'class' => 'pagination',
			'pages' => $numpages,//total pages
			'page_numbers_class' => 'page-numbers',
			'range' => 2,
			'first_page_text' => ___('&laquo; First'),
			'previous_page_text' => ___('&lsaquo; Previous'),
			'next_page_text' => ___('Next &rsaquo;'),
			'last_page_text' => ___('Last &raquo;'),
			'add_fragment' => 'post-content',
			
		);
		$r = array_merge($defaults,$args);
		extract($r,EXTR_SKIP);
		$output = null;
		$showitems = ($range * 2)+1;  
		$paged = $page ? $page : 1;

		if($pages != 1) {
			/** 
			 * first page
			 */
			if($paged > 2 && $paged > $range + 1 && $showitems < $pages){
				$output .= '<a href="' . self::get_link_page_url(1,$add_fragment) . '" class="' . $page_numbers_class . ' first_page first-page">' . $first_page_text . '</a>';
			}
			/** 
			 * previous page
			 */
			if($paged > 1 && $showitems < $pages){
				$output .= '<a href="' . self::get_link_page_url($paged - 1,$add_fragment) . '" class="' . $page_numbers_class . ' previous-page previous_page">' . $previous_page_text . '</a>';
			}
			/** 
			 * middle page
			 */
			for ($i=1; $i <= $pages; $i++){
				if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )){
					$output .= ($paged == $i)?
						'<span class="' . $page_numbers_class . ' current">' . $i . '</span>' : 
						'<a href="'.self::get_link_page_url($i,$add_fragment).'" class="' . $page_numbers_class . ' inactive">' . $i . '</a>';
				}
			}
			/** 
			 * next page 
			 */
			if ($paged < $pages && $showitems < $pages){
				$output .= '<a href="' . self::get_link_page_url($paged + 1,$add_fragment) . '" class="' . $page_numbers_class . ' next-page next_page">' . $next_page_text . '</a>';
			}
			/** 
			 * last page
			 */
			if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages){
				$output .= '<a href="' . self::get_link_page_url($pages,$add_fragment) . '" class="' . $page_numbers_class . ' last-page last_page">' . $last_page_text . '</a>';
			}
			$output = '<div class="' . $class . '">' . $output . '</div>';
			return $output;
		}
	}
	/**
	 * 评论楼层显示，
	 * 只能调用一次，多次调用请先赋值给变量，再调用变量；
	 * 或调用一次后，引用函数调用。
	 * @since 3.2
	 * @version 1.0.1
	 * @author INN STUDIO | Km.Van
	 * @param string $comorder Optional. 使用自定义参数，asc为正序，desc为倒序。
	 * @param string $display_child Optional. 是否显示子评论。
	 * @return false|跳过子评论。否则显示楼层数。
	 * 
	 */
	public static function get_comments_floor($comorder = null,$display_child = false){
		global $comment,$wpdb,$post;
		/* 不显示子评论 */
		if(!$display_child){
			if($comment->comment_parent) return false;
		}
		
		$cpp = get_option('comments_per_page');
		$comorder = $comorder ? $comorder : get_option('comment_order');
		$cpaged = get_query_var('cpage');
		/* 正序 asc */
		if($comorder === 'asc'){
			static $commentcount = 0;
			if(!$commentcount){
				$cpaged = ($cpaged < 1) ? 1 : $cpaged;
				--$cpaged;
				$commentcount = $cpp * $cpaged;
			}
			++$commentcount;
		/* 倒序 */
		}else{
			static $commentcount = 0;
			if(!$commentcount){
				/* 显示子评论 */
				if($display_child){
					$commentcount = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = $post->ID AND comment_type = '' AND comment_approved = '1'");
				}else{
					$commentcount = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = $post->ID AND comment_type = '' AND comment_approved = '1' AND comment_parent = '0'");
				}
				/* 总页数 */
				$max_pages = ceil($commentcount / $cpp);
				/* 只存在1页 */
				if($max_pages == 1){
					++$commentcount;
				/* 首页 */
				}else if($cpaged == 1){
					$commentcount = $cpp + 1;
				/* 末页 */
				}else if($cpaged == $max_pages){
					/* $commentcount = $commentcount; */
				/* 中间页 */
				}else{
					$commentcount = $cpp * $cpaged + 1;
				}
			}
			--$commentcount;
		}
		return $commentcount;
	}
	/**
	 * get_user_comments_count
	 *
	 * @param int user_id id
	 * @return 
	 * @version 1.0.2
	 * @author KM@INN STUDIO
	 */
	public static function get_user_comments_count($user_id){
		$cache_id = 'user_comments_count-' . $user_id;
		$cache = wp_cache_get($cache_id);
		if(is_numeric($cache))
			return $cache;
			
		global $wpdb;
		$count = $wpdb->get_var($wpdb->prepare( 
			'
			SELECT COUNT(*)
			FROM ' . $wpdb->comments . '
			WHERE comment_approved = 1 
			AND user_id = %d
			',$user_id
		));
		wp_cache_set($cache_id,$count,null,3600);
		return $count;
	}

	/* 后台登录logo 链接修改 */
	public static function custom_login_logo_url($url) {
		static $cache = null;
		if($cache === null)
			$cache = home_url();
			
		return $cache;
	}

	/* 增強bodyclass樣式 */
	public static function theme_body_classes(array $classes = []){
		if(is_singular()){
			$classes[] = 'singular';
		}
		return $classes;
	}
	/** _fix_custom_background_cb */
	public static function _fix_custom_background_cb() {
		// $background is the saved custom image, or the default image.
		$background = set_url_scheme( get_background_image() );

		// $color is the saved custom color.
		// Added second parameter to get_theme_mod() to get_theme_support() as we need default colors to show with needing to save.
		$color = get_theme_mod( 'background_color', get_theme_support( 'custom-background', 'default-color' ) );

		if ( ! $background && ! $color )
		return;

		$style = $color ? "background-color: #$color;" : '';

		if ( $background ) {
		$image = " background-image: url('$background');";

		$repeat = get_theme_mod( 'background_repeat', get_theme_support( 'custom-background', 'default-repeat' ) );
		if ( ! in_array( $repeat, array( 'no-repeat', 'repeat-x', 'repeat-y', 'repeat' ) ) )
		$repeat = 'repeat';
		$repeat = " background-repeat: $repeat;";

		$position = get_theme_mod( 'background_position_x', get_theme_support( 'custom-background', 'default-position-x' ) );
		if ( ! in_array( $position, array( 'center', 'right', 'left' ) ) )
		$position = 'left';
		$position = " background-position: top $position;";

		$attachment = get_theme_mod( 'background_attachment', get_theme_support( 'custom-background', 'default-attachment' ) );
		if ( ! in_array( $attachment, array( 'fixed', 'scroll' ) ) )
		$attachment = 'scroll';
		$attachment = " background-attachment: $attachment;";

		$style .= $image . $repeat . $position . $attachment;
		}
		?>
		<style id="custom-background-css">
		body.custom-background { <?= trim( $style ); ?> }
		</style>
		<?php
	}
	
	/**
	 * load_includes
	 * Load the includes functions
	 * 
	 * @return 
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 * 
	 */
	private static function load_includes(){
		foreach(glob(self::get_theme_includes_path('*')) as $dir_path){
			$path_info = pathinfo($dir_path);

			$file_path = $path_info['dirname'] . '/' . $path_info['filename'] . '/' . $path_info['filename'] . '.php';
			
			if(is_file($file_path))
				include $file_path;
				

		}
		
		/**
		 * HOOK fires init include features
		 * 
		 * @param array Callback function name
		 * @return array
		 */
		foreach(apply_filters('theme_includes',[]) as $v){
			call_user_func($v);
		}
		
	}
	/**
	 * Hook for after_setup_theme
	 * 
	 * 
	 * @return n/a
	 * @version 1.0.2
	 * @author KM@INN STUDIO
	 */
	public static function after_setup_theme(){
		/**
		 * Load language_pack
		 */
		load_theme_textdomain(theme_functions::$iden,self::get_stylesheet_directory().'/languages' );
		/**
		 * Custom login logo url
		 */
		add_filter('login_headerurl',__CLASS__ . '::custom_login_logo_url' );
		/**
		 * Add thumbnails function
		 */
		add_theme_support('post-thumbnails');
		/**
		 * MB functions charset
		 */
		mb_internal_encoding(get_bloginfo('charset'));
		/**
		 * Add editor style
		 */
		add_editor_style();
		/**
		 * Load includes functions
		 */
		self::load_includes();
		/** 
		 * auto_minify
		 */
		self::auto_minify();
		/**
		 * Othor
		 */
		add_theme_support('automatic-feed-links');
		remove_action('wp_head', 'wp_generator');
		add_filter('body_class',__CLASS__ . '::theme_body_classes');
		add_action('wp_before_admin_bar_render',__CLASS__ . '::remove_wp_support');
	}
	public static function remove_wp_support(){
		global $wp_admin_bar;
		$wp_admin_bar->remove_node('wp-logo');
		$wp_admin_bar->remove_node('about');
		$wp_admin_bar->remove_node('wporg');
		$wp_admin_bar->remove_node('documentation');
		$wp_admin_bar->remove_node('support-forums');
		$wp_admin_bar->remove_node('feedback');
		$wp_admin_bar->remove_node('view-site');
	}
	/**
	 * auto_minify
	 *
	 * @return 
	 * @version 1.0.2
	 * @author KM@INN STUDIO
	 */
	public static function auto_minify(){
		/** 
		 * js and css files version
		 */
		if(theme_file_timestamp::get_timestamp() < filemtime(theme_features::get_stylesheet_directory() . '/style.css')){
			@ini_set('max_input_nesting_level','10000');
			@ini_set('max_execution_time','300'); 
			
			remove_dir(self::get_stylesheet_directory() . self::$basedir_js_min);
			self::minify_force(self::get_stylesheet_directory() . self::$basedir_js_src);
			
			remove_dir(self::get_stylesheet_directory() . self::$basedir_css_min);
			self::minify_force(self::get_stylesheet_directory() . self::$basedir_css_src);
			
			self::minify_force(self::get_stylesheet_directory() . self::$basedir_includes);
			
			theme_file_timestamp::set_timestamp();
		}
		
	}
	/**
	 * Display category on select tag
	 *
	 * @param string $group_id
	 * @param string $cat_id
	 * @param bool $child
	 * @return string
	 * @version 1.1.0
	 * @author KM@INN STUDIO
	 */
	public static function cat_option_list($group_id,$cat_id,$child = false){
		static $caches = [];
		$cache_id = md5(serialize(func_get_args()));

		if(isset($caches[$cache_id]))
			echo $caches[$cache_id];
			
		$opt = (array)theme_options::get_options($group_id);
		if($child !== false){
			$cat_current_id = isset($opt[$cat_id][$child]) && $opt[$cat_id][$child] != 0 ? $opt[$cat_id][$child] : null;
		}else{
			$cat_current_id = isset($opt[$cat_id]) && $opt[$cat_id] != 0 ? $opt[$cat_id] : null;
		}
		$cat_args = array(
			'name' => $child !== false ? $group_id . '[' . $cat_id . '][' . $child . ']' : $group_id . '[' . $cat_id . ']',
			'id' => $child !== false ? $group_id . '-' . $cat_id . '-' . $child : $group_id . '-' . $cat_id,
			'show_option_none' => ___('Select category'),
			'hierarchical' => 1,
			'hide_empty' => false,
			'selected' => $cat_current_id,
			'echo' => 0,
		);		
		$caches[$cache_id] = wp_dropdown_categories($cat_args);
		echo $caches[$cache_id];
	}
	/**
	 * Display category on checkbox tag
	 * 
	 *
	 * @param string $group_id
	 * @param string $ids_name
	 * @return string
	 * @version 1.1.2
	 * @author KM@INN STUDIO
	 */
	public static function cat_checkbox_list($group_id,$ids_name){
		static $caches = [];
		$cache_id = md5(serialize(func_get_args()));

		if(isset($caches[$cache_id]))
			echo $caches[$cache_id];
			
		$opt = (array)theme_options::get_options($group_id);
		$cats = get_categories(array(
			'hide_empty' => false,
			'exclude' => 1
		));
		$cat_ids = isset($opt[$ids_name]) ? (array)$opt[$ids_name] : [];

		ob_start();
		
		if(!empty($cats)){
			foreach($cats as $cat){
				if(in_array($cat->term_id,$cat_ids)){
					$checked = ' checked="checked" ';
					$selected_class = ' button-primary ';
				}else{
					$checked = null;
					$selected_class = null;
				}
			?>
			<label for="<?= $group_id,'-',$ids_name,'-',$cat->term_id;?>" class="tpl-item button <?= $selected_class;?>">
				<input 
					type="checkbox" 
					id="<?= $group_id,'-',$ids_name,'-',$cat->term_id;?>" 
					name="<?= $group_id,'[',$ids_name,'][]';?>" 
					value="<?= $cat->term_id;?>"
					<?= $checked;?>
				/>
					<?= esc_html($cat->name);?> 
					
					<a href="<?= esc_url(get_category_link($cat->term_id));?>" target="_blank">
						<small>
							<?= esc_html(sprintf(___('(%s)'),urldecode($cat->slug)));?>
						</small>
					</a>
			</label>
			<?php 
			}
		}else{ ?>
			<p><?= esc_html(___('No category, pleass go to add some categories.'));?></p>
		<?php }
		$caches[$cache_id] = ob_get_contents();
		ob_end_clean();
		echo $caches[$cache_id];

	}
	/**
	 * Display page list on select tag
	 *
	 * @param string $group_id
	 * @param string $page_slug
	 * @return
	 * @version 1.1.0
	 * @author KM@INN STUDIO
	 */
	public static function page_option_list($group_id,$page_slug){
		static $caches = [];
		$cache_id = md5(serialize(func_get_args()));

		if(isset($caches[$cache_id]))
			echo $caches[$cache_id];
			
		$opt = theme_options::get_options($group_id);
		$page_id = isset($opt[$page_slug]) ? (int)$opt[$page_slug] : null;

		ob_start();
		?>
		<select name="<?= $group_id,'[',$page_slug,']';?>" id="<?= $group_id,'-',$page_slug;?>">
			<option value="0"><?= esc_attr(___('Select page'));?></option>
			<?php
			foreach(get_pages() as $page){
				if($page_id == $page->ID){
					$selected = ' selected ';
				}else{
					$selected = null;
				}
				?>
				<option value="<?= esc_attr($page->ID);?>" <?= $selected;?>><?= esc_attr($page->post_title);?></option>
				<?php
			}
			?>
		</select>
		<?php
		$caches[$cache_id] = ob_get_contents();
		ob_end_clean();
		echo $caches[$cache_id];
		
	}
	/**
	 * Get post format icons
	 *
	 * @param string $key The post format
	 * @return string Post format icon(s)
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	public static function get_post_format_icon($key = null){
		$icons = array(
			'standard'		=> 'pushpin',
			'aside' 		=> 'file-text',
			'chat' 			=> 'comment-discussion',
			'gallery' 		=> 'images',
			'link' 			=> 'link',
			'image' 		=> 'image',
			'quote' 		=> 'quote-left',
			'status' 		=> 'comment',
			'video' 		=> 'video-camera',
			'audio' 		=> 'music',
		);
		$icons = apply_filters('post-format-icons',$icons,$key);
		if(!$key){
			return $icons;
		}else{
			return isset($icons[$key]) ? $icons[$key] : null;
		}
	}
	/**
	 * Get option from cache
	 *
	 * @param string $key
	 * @return mixed 
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_option($key){
		static $caches = [];
		if(isset($caches[$key]))
			return $caches[$key];

		$caches[$key] = get_option($key);
		return $caches[$key];
	}
	/**
	 * Get comment pages count
	 *
	 * @param array $comments 
	 * @return int Max comment pages number
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_comment_pages_count($comments){
		static $count = null;
		if($count === null)
			$count = get_comment_pages_count(
				$comments, 
				self::get_option('comments_per_page'), 
				self::get_option('thread_comments')
			);
		return $count;
	}
	/**
	 * Get all cat ID by children cat id
	 *
	 * @param int $cat_id Current children cat id
	 * @param array &$all_cat_id All cats id
	 * @return 
	 * @version 1.0.0
	 * @author Km.Van inn-studio.com <kmvan.com@gmail.com>
	 */
	public static function get_all_cats_by_child($cat_id, array & $all_cat_id){
		$cat = get_category($cat_id);
		if(!$cat){
			return false;
		}
		$all_cat_id[] = $cat_id;
		if($cat->parent != 0){
			return self::get_all_cats_by_child(get_category($cat->parent)->term_id,$all_cat_id);
		}
	}
}

?>