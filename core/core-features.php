<?php

/**
 * WordPress Extra Theme Features.
 *
 * Help you write a wp site quickly.
 *
 * @package KMTF
 * @version 4.3.5
 * @author KM@INN STUDIO
 * @date 2015-01-22
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
		add_action('after_setup_theme',	get_class() . '::after_setup_theme');
		add_action('wp_head',			get_class() . '::theme_header');
	}
	/**
	 * theme_header
	 * 
	 * @return 
	 * @example 
	 * @version 1.1.3
	 * @author KM (kmvan.com@gmail.com)
	 * @copyright Copyright (c) 2011-2013 INN STUDIO. (http://www.inn-studio.com)
	 **/
	public static function theme_header(){
		?><script id="seajsnode" src="<?php echo theme_features::get_theme_js('seajs/sea');?>"></script>
		<script>
		<?php
		$theme_version = self::get_theme_info('version');
		$config = array();
		$config['base'] = esc_js(self::get_theme_js());
		$config['paths'] = array(
			'theme_js' => esc_js(self::get_theme_js()),
			'theme_css' => esc_js(self::get_theme_css()),
		);
		$config['vars'] = array(
			'locale' => str_replace('-','_',get_bloginfo('language')),
			'theme_js' => esc_js(self::get_theme_js()),
			'theme_css' => esc_js(self::get_theme_css()),
			'theme_images' => esc_js(self::get_theme_images_url()),
			'process_url' => esc_js(self::get_process_url()),
		);
		$config['map'] = array(
			array('.css','.css?v=' . $theme_version),
			array('.js','.js?v=' . $theme_version)
		);
		/** 
		 * seajs hook
		 */
		$config['paths'] = apply_filters('frontend_seajs_paths',$config['paths']);
		$config['alias'] = apply_filters('frontend_seajs_alias',[]);
		$config['vars'] = apply_filters('frontend_seajs_vars',$config['vars']);
		$config['map'] = apply_filters('frontend_seajs_map',$config['map']);
		?>
		seajs.config(<?php echo json_encode($config);?>);
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
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function minify_force($file_or_dir = null){
		if($file_or_dir){
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
	}
	/**
	 * theme_features::get_ie_css
	 * 
	 * @param mix int/array
	 * @param bool $mtime
	 * @param mix string/array
	 * @return 
	 * @version 1.0.2
	 * @author KM@INN STUDIO
	 */
	public static function get_ie_css($version = null,$args = 'normal',$mtime = false){
		/**
		 * array('lt'=>10),ltie10
		 */
		if(is_array($version)){
			foreach($version as $key => $value){
				$before = '<!--[if ' . $key . ' IE ' . $value . ']>';
				$file_basename = $key . 'ie' . $value; /* ltie10 */
			}
		}else{
			$before = '<!--[if IE ' . $version. ']>'; 
			$file_basename = 'ie' . $version; /* ie10 */
		}
		$output = $before . theme_features::get_theme_css($file_basename,$args,$mtime).'<![endif]-->';
		return $output;
	}
	/**
	 * get_theme_info
	 *
	 * @param string get what 
	 * @param string 
	 * @param string 
	 * @return string
	 * @version 1.0.1
	 * @see http://codex.wordpress.org/Function_Reference/wp_get_theme
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_info($key = null,$stylesheet = null, $theme_root = null){
		$theme = wp_get_theme($stylesheet = null, $theme_root = null);
		if(!$key) return $theme;
		
		$key = ucfirst($key);
		return $theme->get($key);
	}
	/**
	 * get_old_theme_version
	 *
	 * @return string
	 * @version 1.0.3
	 * @author KM@INN STUDIO
	 */
	public static function get_old_theme_info($key = null){
		$theme = get_transient(theme_functions::$iden . 'theme_info');
		if(!$theme) return false;
		if(!$key) return $theme;
		$key = ucfirst($key);
		return $theme->get($key);
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
	/**
	 * get_theme_file_url
	 * return your file for load under the theme.
	 * 
	 * @param string $file_basename the file name, like 'functions.js'
	 * @param bool $mtime 
	 * @return string
	 * @version 1.2.4
	 * @since 3.3.0
	 * @author INN STUDIO
	 * 
	 */
	public static function get_theme_file_url($file_basename = null,$mtime = false){
		if(!$file_basename) return get_template_directory_uri();
		/**
		 * fix basename string
		 */
		$file_basename = $file_basename[0] === '/' ? $file_basename : '/' .$file_basename;
		/**
		 * get file url and path full
		 */
		$file_url = get_template_directory_uri() . $file_basename;
		$file_path = get_stylesheet_directory() . $file_basename;
		/**
		 * get file mtime
		 */
		if($mtime){
			$file_mtime = self::get_theme_info('version');
			$file_url = $file_url . '?v=' . $file_mtime;
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
				if(!class_exists('JSMin')) include theme_features::get_theme_includes_path('class/jsmin.php');
				$source = file_get_contents($file_path);
				$min = JSMin::minify($source);
				mk_dir(dirname($file_path_min));
				file_put_contents($file_path_min,$min);
				break;
			/**
			 * CSS file
			 */
			case 'css':
				if(!class_exists('CssMin')) include theme_features::get_theme_includes_path('class/cssmin.php');
				$source = file_get_contents($file_path);
				$min = CssMin::minify($source);
				mk_dir(dirname($file_path_min));
				file_put_contents($file_path_min,$min);
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
	 * @param bool $mtime the Timestamp
	 * @return string <script> tag string or js url only
	 */
	public static function get_theme_js($file_basename = null,$url_only = true,$mtime = true){
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
		if(!$file_basename) return get_template_directory_uri() . $basedir;
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
		$file_url = self::get_theme_file_url($basedir . $file_basename,$mtime);
		$output = $url_only ? $file_url : '<script src="' . esc_url($file_url) . '"></script>';

		return $output;
	}
	/**
	 * theme_features::get_theme_css
	 * 
	 * @since 3.2.0
	 * @version 1.2.3
	 * @param string $file_basename css file basename, style = style.css
	 * @param mix $args
	 * @param bool $mtime the Timestamp
	 * @return string url only /<link...> by $args
	 */
	public static function get_theme_css($file_basename = null,$args = null,$mtime = true){
		$basedir = self::$basedir_css_min;
		/**
		 * if dev mode is ON
		 */
		if(class_exists('theme_dev_mode') && theme_dev_mode::is_enabled()){
			$basedir = self::$basedir_css_src;
			$glob_path = self::get_theme_path(self::$basedir_css_src . '*.css');
		}
		/**
		 * file_basename
		 */
		if(!$file_basename) return get_template_directory_uri() . $basedir;
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
		$file_url = self::get_theme_file_url($basedir . $file_basename,$mtime);
		/**
		 * check the $args
		 */
		if(!$args){
			$output = $file_url;
		}else if($args === 'normal'){
			$output = '<link id="' . esc_attr($path_info['filename']) . '" href="' . esc_url($file_url) .'" rel="stylesheet" media="all"/>';
		}else if(is_array($args)){
			/* check the array of $args and to release */
			$ext = null;
			foreach($args as $key => $value){
				$ext .= ' ' .$key. '="' .$value. '"';
			}
			$output = '<link id="' . esc_attr($path_info['filename']) . '" href="' . esc_url($file_url) .'" rel="stylesheet" ' .$ext. '/>';
		}
		return $output;
	}
	/**
	 * get_theme_extension_url
	 * 
	 * @param 
	 * @param 
	 * @param 
	 * @return 
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_extension_url($args = null){
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
			'mtime' => false
		);
		$r = wp_parse_args($args,$defaults);
		extract($r,EXTR_SKIP);
		
		$dev_mode = class_exists('theme_dev_mode') && theme_dev_mode::is_enabled() ? true : false;
		$path_info = pathinfo($file_basename);
		$extension = $path_info['extension'];
		
		$self_basedir_extension_src = 'basedir_' . $extension . '_src';
		$self_basedir_extension_min = 'basedir_' . $extension . '_min';
		
		$self_basedir_type = 'basedir_' . $type;
		$self_get_theme_type_url = 'get_theme_' . $type . '_url';
		
		$file_url = null;
		
		$dir_url_src = self::get_theme_url() . self::$$self_basedir_type . $basedir . self::$$self_basedir_extension_src;
		$dir_url_min = self::get_theme_url() . self::$$self_basedir_type . $basedir . self::$$self_basedir_extension_min;
		
		/**
		 * dev
		 */
		if($dev_mode){
			if(!$path_info['filename']) return $dir_url_src;
			$file_url_src = self::$self_get_theme_type_url($basedir,$file_basename,$mtime);
			$file_url = $file_url_src;
		}else{
			if(!$path_info['filename']) return $dir_url_min;
			$file_url_min = self::$self_get_theme_type_url($basedir,$file_basename,$mtime);
			$file_url = $file_url_min;
		}
		return $file_url;
	}
	
	/**
	 * get_theme_includes_js
	 * 
	 * @param string $DIR
	 * @param string $filename
	 * @param bool $mtime
	 * @return string
	 * @version 1.0.3
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_includes_js($DIR = null,$filename = 'init',$mtime = true){
		$path_info = pathinfo($filename);
		if(!isset($path_info['extension']) || empty($path_info['extension']) || $path_info['extension'] !== 'js'){
			$file_basename = $filename . '.js';
		}else{
			$file_basename = $filename;
		}
		$args = array(
			'type' => 'includes',
			'basedir' => $DIR,
			'file_basename' => $file_basename,
			'mtime' => $mtime
		);
		return self::get_theme_extension_url($args);
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
	public static function get_theme_includes_css($DIR = null,$filename = 'style',$mtime = true){
		$path_info = pathinfo($filename);
		if(!isset($path_info['extension']) || empty($path_info['extension']) || $path_info['extension'] !== 'css'){
			$file_basename = $filename . '.css';
		}else{
			$file_basename = $filename;
		}
		$args = array(
			'type' => 'includes',
			'basedir' => $DIR,
			'file_basename' => $file_basename,
			'mtime' => $mtime
		);
		return self::get_theme_extension_url($args);
	}
	/**
	 * get_theme_includes_image
	 * 
	 * @param string $DIR
	 * @param string $filename
	 * @return string
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_includes_image($DIR = null,$filename = null){
		$r = array(
			'type' => 'includes',
			'basedir' => basename($DIR) . self::$basedir_images_min,
			'file_basename' => $filename,
		);
		extract($r,EXTR_SKIP);
		return get_template_directory_uri() . '/' . $type . '/' . $basedir . $file_basename;
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
		$r = wp_parse_args($args,$defaults);
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
			if(!file_exists($file_path_src)) return false;
			/**
			 * file url src
			 */
			$file_url = self::get_theme_url() . self::$$self_basedir_type . $basedir . self::$$self_basedir_extension_src . $file_basename;
			/**
			 * get file mtime
			 */
			$file_mtime = self::get_theme_info('version');
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
	 * @version 1.0.2
	 * @author KM@INN STUDIO
	 */
	public static function get_theme_images_url($file_basename = null,$mtime = false){
		if(!$file_basename) return get_template_directory_uri() . self::$basedir_images_min;
		$file_url = self::get_theme_file_url(self::$basedir_images_min . $file_basename,$mtime);
		return $file_url;
	}
	/* 输出后台页面地址 */
	static function get_admin_url($file_name = null){
		$file_name = $file_name ? '/' .$file_name : '/';
		$output = home_url(). '/wp-admin' .$file_name;
		return $output;
	}
	/**
	 * get the process file url of theme
	 * 
	 * @param mixed 
	 * @return string The process file url
	 * @version 1.1.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_process_url($param = null){
		$admin_ajax_url = admin_url('admin-ajax.php');
		if(!$param) return $admin_ajax_url;
		/** 
		 * is_string
		 */
		if(is_string($param)){
			$param = str_replace('?','',$param);
			/** 
			 * string to array
			 */
			parse_str($param,$param);
			/** 
			 * check the action key and value
			 */
		}
		/** 
		 * check the action key and value
		 */
		if(!isset($param['action']) || empty($param['action'])) return false;
		/** 
		 * add action
		 */
		$query = '?' . http_build_query($param);
		$admin_ajax_url = admin_url('admin-ajax.php') . $query;
		return $admin_ajax_url;
	}
	/**
	 * json_format
	 *
	 * @param object
	 * @return string
	 * @version 1.0.2
	 * @author KM@INN STUDIO
	 */
	public static function json_format($output,$die = false){
		if(empty($output)) 
			return;
			/** Reduce the size but will inccrease the CPU load */
			//$output = json_encode(array_multiwalk($output,'html_compress'));
			$output = json_encode($output);
			/**
			 * If the remote call, return the jsonp format
			 */
			if(isset($_GET['callback']) && !empty($_GET['callback'])){
				$jsonp = $_GET['callback'];
				$output = $jsonp. '(' .$output. ')';
			}else{
				header('Content-Type: application/javascript');
			}
		if($die){
			die($output);
		}else{
			return $output;
		}
	}
	/**
	 * check_referer
	 *
	 * @param string
	 * @return bool
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	public static function check_referer($referer = null){
		$referer = $referer ? : home_url();
		if(!isset($_SERVER["HTTP_REFERER"]) || stripos($_SERVER["HTTP_REFERER"],$referer) !== 0){
			$output = array(
				'status' => 'error',
				'id' => 'invalid_referer',
				'msg' => ___('Sorry, referer is invalid.')
			);
			die(theme_features::json_format($output));
		}
	}
	/**
	 * Check theme nonce code
	 *
	 * @return 
	 * @version 1.1.1
	 * @author KM@INN STUDIO
	 */
	public static function check_nonce($key = 'theme-nonce'){
		$nonce = isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
		if(!wp_verify_nonce($nonce,'theme-nonce')){
			$output = array(
				'status' => 'error',
				'id' => 'invalid_security_code',
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
		if(is_object($user)){
			$user_id = $user->ID;
		}else{
			$user_id = (int)$user;
			$user = get_user_by('id',$user);
		}
		$avatar = null;
		/** check avatar from user meta */
		$avatar = get_user_meta($user_id,'avatar',true);
		/** check avatar from  */
		if(!$avatar){
			$avatar = get_avatar($user->user_email,$size, $default, $alt);
		}
		return $avatar;
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
		if(!$file_basename) return get_template_directory_uri();
		$file_url = self::get_theme_file_url($file_basename,$mtime);
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
		if(!$file_basename) return get_stylesheet_directory() . '/';
		$file_path = $file_basename[0] === '/' ? get_stylesheet_directory() . $file_basename : get_stylesheet_directory() . '/' . $file_basename;
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
		if(!$file_basename) return get_stylesheet_directory() . self::$basedir_includes;
		$file_path = $file_basename[0] === '/' ? get_stylesheet_directory() . self::$basedir_includes . $file_basename : get_stylesheet_directory() . self::$basedir_includes . $file_basename;
		return $file_path;
	}
	/**
	 * theme_features::get_admin_path
	 * 
	 * @param string $file_basename
	 * @return string $file_path
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function get_admin_path($file_basename = null){
		$basedir_name = '/wp-admin/';
		if(!$file_basename) return ABSPATH . $basedir_name;
		$file_basename = $file_basename ? '/' .$file_basename : '/';
		$file_path = ABSPATH . $basedir_name . $file_basename;
		return $file_path;
	}
	/**
	 * 获取日志特色图，如无则显示文章第一张图，再无则显示指定代替图片
	 *
	 * @since 3.0.1
	 * @version 1.0.6
	 * @usedny theme_features::get_thumbnail_src
	 * @param int $post_id 文章ID，默认为$post->ID
	 * @param string $size 特色图规格
	 * @return string The img url
	 */
	public static function get_thumbnail_src($post_id = null,$size = 'thumbnail',$replace_img = null){
		global $post;
		$output = null;
		$post_id= $post_id ? (int)$post_id : $post->ID;
		$src = wp_get_attachment_image_src(get_post_thumbnail_id(),$size);
		if(!empty($src)){
			$src = $src[0];
		/* 不存在特色图 */
		}else{
			$src = get_img_source($post->post_content);
			if(!$src && $replace_img){
				$src = $replace_img;
			}
		}
		return $src;
	}
	/* 获取文章概要 */
	public static function get_post_excerpt($len = 120,$extra = '...'){
		global $post;
		$output = null;
		/* 存在摘要 */
		if(get_the_excerpt()){
			$output = str_sub(get_the_excerpt(),$len,$extra);
		}else{
			$output = str_sub(get_the_content(),$len,$extra);
		}
		return $output;
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
			$tag_id = self::get_current_tag_id();
			$tag_obj = get_tag($tag_id);
			return $tag_obj;
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
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_current_cat_id(){
		global $cat,$post;
		if($cat){
			$cat_obj = get_category($cat);
			$cat_id = $cat_obj->term_id;
		}else if($post){
			$cat_obj = get_the_category($post->ID);
			$cat_id = isset($cat_obj[0]) ? $cat_obj[0]->cat_ID : 0;
		}
		return $cat_id;
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
	public static function get_page_url_by_slug($slug = null){
		if(!$slug) return false;
		$id = self::get_page_id_by_slug($slug);
		return get_permalink($id);
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
	public static function get_page_id_by_slug($page_slug = null){
		if(!$page_slug) return false;
		$page_obj = get_page_by_path($page_slug);
		if ($page_obj) {
			$output = $page_obj->ID;
		}else{
			$output = false;
		}
		return $output;
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
		$basedir_name = '/themes/';
		$file_name = $file_name ? '/' .$file_name : '/';
		$output = get_stylesheet_directory().$basedir_name.$file_name;
		return $output;
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
			'numbers_class' => array(),
			'middle_class' => '',
		);
		$r = wp_parse_args($args,$defaults);
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
			$next_page_url = 'javascript:void(0);';
		}
		//first page
		if($page == 1 && $numpages != $page){
			$first_class = 'numbers-first disabled';
			$prev_page_url = 'javascript:void(0);';
		}
		
		ob_start();
		?>
		<nav class="<?php echo esc_attr($nav_class);?>">
			<?php 
			ob_start(); 
			?>
			<a href="<?php echo $prev_page_url;?>" class="page-numbers page-prev <?php echo esc_attr($numbers_class_str);?> <?php echo $first_class;?>">
				<?php echo esc_html(___('&lsaquo; Previous'));?>
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
			<div class="page-numbers page-middle <?php echo esc_attr($middle_class);?>">
				<span class="page-middle-btn"><?php echo $page , ' / ' , $numpages;?></span>
				<div class="page-middle-numbers">
				<?php
				for($i=1;$i<=$numpages;++$i){
					$url = self::get_link_page_url($i,$add_fragment);
					/** 
					 * if current page
					 */
					if($i == $page){
						?>
						<span class="numbers current"><?php echo $i;?></span>
					<?php }else{ ?>
						<a href="<?php echo esc_url($url);?>" class="numbers"><?php echo $i;?></a>
					<?php 
					}
				}
				?>
				</div>
			</div>
			
			<?php ob_start(); ?>
			<a href="<?php echo $next_page_url;?>" class="page-numbers page-next <?php echo esc_attr($numbers_class_str);?> <?php echo $last_class;?>">
				<?php echo esc_html(___('Next &rsaquo;'));?>
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
		$r = wp_parse_args($args,$defaults);
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
		return (int)$commentcount;
	}
	/**
	 * get_user_comments_count
	 *
	 * @param int user_id id
	 * @return 
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function get_user_comments_count($user_id){
		$user_id = (int)$user_id;
		$cache_id = 'user_comments_count-' . $user_id;
		$cache = (int)wp_cache_get($cache_id);
		if($cache !== false)
			return (int)$cache;
			
		global $wpdb;
		$count = $wpdb->get_var($wpdb->prepare( 
			'
			SELECT COUNT(*)
			FROM ' . $wpdb->comments . '
			WHERE comment_approved = 1 
			AND user_id = %d
			',$user_id
		));
		wp_cache_set($cache_id,(int)$count,null,3600);
		return (int)$count;
	}

	/* 后台登录logo 链接修改 */
	public static function custom_login_logo_url($url) {
		return home_url();
	}
	/* 增強bodyclass樣式 */
	public static function theme_body_classes($classes){
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
		body.custom-background { <?php echo trim( $style ); ?> }
		</style>
		<?php
	}
	/**
	 * get_write_mode
	 * 
	 * @return bool
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	// public static function get_write_mode(){
		// return chmodr(self::get_theme_path(),0755);
	// }
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
			
			if(file_exists($file_path))
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
		load_theme_textdomain(theme_functions::$iden,get_stylesheet_directory().'/languages' );
		/**
		 * Custom login logo url
		 */
		add_filter('login_headerurl','theme_features::custom_login_logo_url' );
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
		add_filter('body_class',get_class() . '::theme_body_classes');
		add_action('wp_before_admin_bar_render',get_class() . '::remove_wp_support');
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
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function auto_minify(){
		/** 
		 * js and css files version
		 */
		$ov = self::get_old_theme_info('version');
		$nv = self::get_theme_info('version');
		if(version_compare($ov,$nv) !== 0){
			self::set_theme_info();
			ini_set('max_input_nesting_level','10000');
			ini_set('max_execution_time','300'); 
			
			remove_dir(get_stylesheet_directory() . self::$basedir_js_min);
			remove_dir(get_stylesheet_directory() . self::$basedir_css_min);

			self::minify_force(get_stylesheet_directory() . self::$basedir_js_src);
			self::minify_force(get_stylesheet_directory() . self::$basedir_css_src);
			self::minify_force(get_stylesheet_directory() . self::$basedir_includes);
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
		echo wp_dropdown_categories($cat_args);
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
		$opt = (array)theme_options::get_options($group_id);
		$cats = get_categories(array(
			'hide_empty' => false,
			'exclude' => 1
		));
		$cat_ids = isset($opt[$ids_name]) ? (array)$opt[$ids_name] : array();
		
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
			<label for="<?php echo $group_id,'-',$ids_name,'-',$cat->term_id;?>" class="tpl-item button <?php echo $selected_class;?>">
				<input 
					type="checkbox" 
					id="<?php echo $group_id,'-',$ids_name,'-',$cat->term_id;?>" 
					name="<?php echo $group_id,'[',$ids_name,'][]';?>" 
					value="<?php echo $cat->term_id;?>"
					<?php echo $checked;?>
				/>
					<?php echo esc_html($cat->name);?> 
					
					<a href="<?php echo esc_url(get_category_link($cat->term_id));?>" target="_blank">
						<small>
							<?php echo esc_html(sprintf(___('(%s)'),urldecode($cat->slug)));?>
						</small>
					</a>
			</label>
			<?php 
			}
		}else{ ?>
			<p><?php echo esc_html(___('No category, pleass go to add some categories.'));?></p>
		<?php }

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
		$opt = theme_options::get_options($group_id);
		$page_id = isset($opt[$page_slug]) ? (int)$opt[$page_slug] : null;
		?>
		<select name="<?php echo $group_id,'[',$page_slug,']';?>" id="<?php echo $group_id,'-',$page_slug;?>">
			<option value="0"><?php echo esc_attr(___('Select page'));?></option>
			<?php
			foreach(get_pages() as $page){
				if($page_id == $page->ID){
					$selected = ' selected ';
				}else{
					$selected = null;
				}
				?>
				<option value="<?php echo esc_attr($page->ID);?>" <?php echo $selected;?>><?php echo esc_attr($page->post_title);?></option>
				<?php
			}
			?>
		</select>
		<?php
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
}

?>