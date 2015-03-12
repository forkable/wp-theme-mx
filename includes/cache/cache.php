<?php
/*
Feature Name:	theme-cache
Feature URI:	http://inn-studio.com
Version:		2.1.2
Description:	theme-cache
Author:			INN STUDIO
Author URI:		http://inn-studio.com
*/

add_filter('theme_includes',function($fns){
	$fns[] = 'theme_cache::init';
	return $fns;
});
class theme_cache{
	public static $cache_expire = 3600;
	public static $iden = 'theme-cache';
	
	public static $cache;
	public static $cache_skey;
	
	public static function init(){
		self::$cache_skey = md5(AUTH_KEY . theme_functions::$iden);
		
		add_action('base_settings',get_class() . '::backend_display');
		add_action('wp_ajax_' . self::$iden, get_class() . '::process');
		/**
		 * When delete menu
		 */
		add_filter('pre_set_theme_mod_nav_menu_locations',function($return){
			$caches = (array)self::get(self::$cache_skey);
			if(!isset($caches['nav-menus'])) return $return;
			unset($caches['nav-menus']);
			self::set(self::$cache_skey,$caches);
			return $return;
		});
		/**
		 * When delete menu
		 */
		add_action('wp_delete_nav_menu',function(){
			$caches = (array)self::get(self::$cache_skey);
			if(!isset($caches['nav-menus'])) return;
			unset($caches['nav-menus']);
			self::set(self::$cache_skey,$caches);
		});
		/**
		 * When update widget
		 */
		add_filter('widget_update_callback',function($instance){
			$caches = (array)self::get(self::$cache_skey);
			if(!isset($caches['widget-sidebars'])) return $instance;
			unset($caches['widget-sidebars']);
			self::set(self::$cache_skey,$caches);
			return $instance;
		});
		
		/**
		 * When update option for widget
		 */
		add_action('update_option_sidebars_widgets',function($old_value, $value){
			$caches = (array)self::get(self::$cache_skey);
			if(!isset($caches['widget-sidebars'])) return;
			unset($caches['widget-sidebars']);
			self::set(self::$cache_skey,$caches);
		});
		/**
		 * When delete post
		 */
		add_action('delete_post',function(){
			$caches = (array)self::get(self::$cache_skey);
			if(!isset($caches['queries'])) return;
			unset($caches['queries']);
			self::set(self::$cache_skey,$caches);
		});
		/**
		 * when post delete
		 */
		add_action('delete_post', function($post_id){
			$post = get_post($post_id);
			$caches = (array)wp_cache_get('pages_by_path');
			if(isset($caches[$post->post_name])){
				unset($caches[$post->post_name]);
				wp_cache_set('pages_by_path',$caches,null,2505600);
			}
		});
		/**
		 * when post save
		 */
		add_action('save_post', function($post_id){
			$post = get_post($post_id);
			$caches = (array)wp_cache_get('pages_by_path');
			if(!isset($caches[$post->post_name])){
				$caches[$post->post_name] = $post_id;
				wp_cache_set('pages_by_path',$caches,null,2505600);
			}
		});
		
	}
	private static function get_process_url($type){
		return add_query_arg(array(
			'action' => self::$iden,
			'return' => add_query_arg(self::$iden,1,get_current_url()),
			'type' => $type
		),theme_features::get_process_url());

	}
	/**
	 * Admin Display
	 */
	public static function backend_display(){
		$options = theme_options::get_options();

		?>
		<fieldset id="<?php echo self::$iden;?>">
			<legend><?php echo ___('Theme cache');?></legend>
			<p class="description"><?php echo ___('Maybe the theme used cache for improve performance, you can clean it when you modify some site contents if you want.');?></p>
			<table class="form-table">
				<tbody>
					<?php if(class_exists('Memcache')){ ?>
					<tr>
						<th><?php echo ___('Memcache cache');?></th>
						<td><p>
							<?php
							if(file_exists(WP_CONTENT_DIR . '/object-cache.php')){ ?>
								<a class="button" href="<?php echo self::get_process_url('disable-cache');?>" onclick="return confirm('<?php echo ___('Are you sure DELETE object-cache.php to disable theme object cache?');?>')">
									<?php echo ___('Disable theme object cache');?>
								</a>
								
								<a class="button" href="<?php echo self::get_process_url('re-enable-cache');?>" onclick="return confirm('<?php echo ___('Are you sure RE-CREATE object-cache.php to re-enable theme object cache?');?>')">
									<?php echo ___('Re-enable theme object cache');?>
								</a>
								
							<?php }else { ?>
								<a class="button-primary" href="<?php echo self::get_process_url('enable-cache');?>">
									<?php echo ___('Enable theme object cache');?>
								</a>
							<?php } ?>
							<span class="description"><span class="icon-exclamation"></span><span class="after-icon"><?php echo ___('Save your settings before click.');?></span></span>
							
						</p></td>
					</tr>
					<?php } ?>
					<tr>
						<th scope="row"><?php echo ___('Control');?></th>
						<td>
							<?php
							if(isset($_GET[self::$iden])){
								echo status_tip('success',___('Theme cache has been cleaned or rebuilt.'));
							}
							?>
							<p>
								<a href="<?php echo self::get_process_url('flush');?>" class="button" onclick="javascript:this.innerHTML='<?php echo ___('Processing, please wait...');?>'"><?php echo ___('Clean all cache');?></a>
								
								<a href="<?php echo self::get_process_url('widget-sidebars');?>" class="button" onclick="javascript:this.innerHTML='<?php echo ___('Processing, please wait...');?>'"><?php echo ___('Clean widget cache');?></a>
								
								<a href="<?php echo self::get_process_url('nav-menus');?>" class="button" onclick="javascript:this.innerHTML='<?php echo ___('Processing, please wait...');?>'"><?php echo ___('Clean menu cache');?></a>
								
								
								<span class="description"><span class="icon-exclamation"></span><span class="after-icon"><?php echo ___('Save your settings before clean');?></span></span>
								
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	}
	private static function disable_cache(){
		$result = @unlink(WP_CONTENT_DIR . '/object-cache.php');
		if($result === true) return true;
		die(sprintf(___('Can not delete the %s file, please make sure the folder can be written.'),WP_CONTENT_DIR . '/object-cache.php'));
	}
	private static function enable_cache(){
		$result = copy(__DIR__ . '/object-cache.php',WP_CONTENT_DIR . '/object-cache.php');
		if($result === true) return true;
		die(sprintf(___('Can not create the %s file, please make sure the folder can be written.'),WP_CONTENT_DIR . '/object-cache.php'));
	}
	/**
	 * process
	 */
	public static function process(){
		$output = null;
		$type = isset($_GET['type']) ? $_GET['type'] : null;
		if(isset($_GET['return'])){
			switch($type){
				case 'flush':
					self::cleanup();
				break;
				case 're-enable-cache':
					self::cleanup();
					self::disable_cache();
					self::enable_cache();
				break;
				case 'disable-cache':
					self::cleanup();
					self::disable_cache();
				break;
				case 'enable-cache':
					self::enable_cache();
				break;
				default:
					$caches = (array)self::get(self::$cache_skey);
					if(isset($caches[$type])){
						unset($caches[$type]);
						self::set(self::$cache_skey,$caches);
					}
			}
			if(isset($_GET['return'])){
				wp_redirect($_GET['return']);
			}else{
				wp_redirect(admin_url('themes.php?page=core-options'));
			}
			die();
		}
	}
	public static function cleanup(){
		if(wp_using_ext_object_cache()){
			return wp_cache_flush();
		}
	}
	/**
	 * add cache for get_page_by_path()
	 * 
	 * @version 1.0.0
	 */
	public static function get_page_by_path($page_path, $output = OBJECT, $post_type = 'page'){
		$cache_id = 'pages_by_path';
		$caches = (array)wp_cache_get($cache_id);
		/** get post id from cache */
		if(isset($caches[$page_path])){
			$post_id = $caches[$page_path];
			return get_post($post_id,$output);
		/** get post id from db */
		}else{
			$post = call_user_func_array('get_page_by_path',func_get_args());
			if(!empty($post)){
				$post_id = $post->ID;
				$caches[$page_path] = $post_id;
				wp_cache_set($cache_id,$caches,null,2505600);
				return $post;
			}
			return null;
		}
	}

	private static function build_key($key,$group = ''){
		return self::$cache_skey . $group . '-' . $key;
	}
	/**
	 * Delete cache
	 *
	 * @param string $key Cache key
	 * @param string $group Cache group
	 * @return bool
	 * @version 2.0
	 * @author KM@INN STUDIO
	 */
	public static function delete($key,$group = ''){
		$key = self::build_key($key,$group);
		if(wp_using_ext_object_cache()){
			return wp_cache_delete($key,$group);
		}
		return self::$cache->delete($key);
	}
	/**
	 * Set cache
	 *
	 * @param string $key Cache ID
	 * @param mixed $data Cache contents
	 * @param string $group Cache group
	 * @return int $expire Cache expire time (s)
	 * @version 2.0.3
	 * @author KM@INN STUDIO
	 */
	public static function set($key,$data,$group = 'default',$expire = 3600){
		if(theme_dev_mode::is_enabled()) return false;
		
		if(wp_using_ext_object_cache()){
			return wp_cache_set($key,$data,$group,$expire);
		}
		return false;
	}
	/**
	 * Get the cache
	 *
	 * @param string $key Cache ID
	 * @param string $group Cache group
	 * @param bool $force True to get cache forced
	 * @return mixed
	 * @version 2.0.2
	 * @author KM@INN STUDIO
	 */
	public static function get($key,$group = 'default',$force = false){
		/**
		 * if dev mode enabled, do NOT get data from cache
		 */
		if(theme_dev_mode::is_enabled()) return false;
		
		if(wp_using_ext_object_cache()){
			return wp_cache_get($key,$group,$force);
		}
		return false;
	}
	/**
	 * Get comments 
	 *
	 * @param string $id The cache id
	 * @param int $expire Cache expire time
	 * @return mixed
	 * @version 2.0.1
	 * @author KM@INN STUDIO
	 */
	public static function get_comments($args,$expire = 3600){
		$cache_group_id = 'comments';
		$id = md5(serialize($args));
		$caches = (array)self::get(self::$cache_skey);
		$cache = isset($caches[$cache_group_id][$cache_id]) ? $caches[$cache_group_id][$cache_id] : null;
		if(empty($cache)){
			$cache = get_comments($args);
			$caches[$cache_group_id][$cache_id] = $cache;
			self::set(self::$cache_skey,$caches,null,$expire);
		}
		return $cache;
	}
	/**
	 * Get queries 
	 *
	 * @param string $id The cache id
	 * @param int $expire Cache expire time
	 * @return mixed
	 * @version 2.0.1
	 * @author KM@INN STUDIO
	 */
	public static function get_queries($args,$expire = 3600){
		$cache_group_id = 'queries';
		$cache_id = md5(serialize($args));
		$caches = (array)self::get(self::$cache_skey);
		$cache = isset($caches[$cache_group_id][$cache_id]) ? $caches[$cache_group_id][$cache_id] : null;
		if(empty($cache)){
			$cache = new WP_Query($args);
			$caches[$cache_group_id][$cache_id] = $cache;
			self::set(self::$cache_skey,$caches,null,$expire);
			wp_reset_query();
		}
		return $cache;
	}
	private static function get_page_prefix(){
		if(is_singular()){
			global $post;
			$cache_id_prefix = 'post-' . $post->ID;
		}else if(is_home()){
			$cache_id_prefix = 'home';
		}else if(is_category()){
			$cache_id_prefix = 'cat-' . theme_features::get_current_cat_id();
		}else if(is_tag()){
			$cache_id_prefix = 'tag-' . theme_features::get_current_tag_id();
		}else if(is_search()){
			$cache_id_prefix = 'search';
		}else if(is_404()){
			$cache_id_prefix = 'error404';
		}else if(is_author()){
			global $author;
			$cache_id_prefix = 'author-' . $author;
		}else if(is_front_page()){
			$cache_id_prefix = 'frontpage';
		}else if(is_post_type_archive()){
			$cache_id_prefix = 'post-type-' . get_query_var('post_type');
		}else if(is_archive()){
			$cache_id_prefix = 'archive';
		}else{
			$cache_id_prefix = 'unknow';
		}
		return $cache_id_prefix;
	}
	/**
	 * output dynamic sidebar from cache
	 *
	 * @param string The widget sidebar name/id
	 * @param int Cache expire time
	 * @return string
	 * @version 2.0.2
	 * @author KM@INN STUDIO
	 */
	public static function dynamic_sidebar($id,$expire = 3600){
		
		$cache_group_id = 'widget-sidebars';
		$cache_id = self::get_page_prefix() . $id;
		$caches = (array)self::get(self::$cache_skey);
		$cache = isset($caches[$cache_group_id][$cache_id]) ? $caches[$cache_group_id][$cache_id] : null;
		if(empty($cache)){
			ob_start();
			dynamic_sidebar($id);
			$cache = html_compress(ob_get_contents());
			ob_end_clean();
			$caches[$cache_group_id][$cache_id] = $cache;
			self::set(self::$cache_skey,$caches,null,$expire);
		}
		echo $cache;
		return empty($cache) ? false : true;
	}
	/**
	 * Get nav menu from cache
	 *
	 * @param string The widget sidebar name/id
	 * @param int Cache expire time
	 * @return string
	 * @version 2.0.2
	 * @author KM@INN STUDIO
	 */
	public static function get_nav_menu($args,$expire = 3600){
		$defaults = array(
			'theme_location' => null,
			'menu_class' => null,
			'container' => 'nav',
		);
		$r = wp_parse_args($args,$defaults);
		$cache_group_id = 'nav-menus';

		$cache_id = self::get_page_prefix() . $args['theme_location'];
		$caches = (array)self::get(self::$cache_skey);
		$cache = isset($caches[$cache_group_id][$cache_id]) ? $caches[$cache_group_id][$cache_id] : null;
		if(empty($cache)){
			ob_start();
			wp_nav_menu($r);
			$cache = html_compress(ob_get_contents());
			ob_end_clean();
			$caches[$cache_group_id][$cache_id] = $cache;
			self::set(self::$cache_skey,$caches,null,$expire);
		}
		return $cache;
	}
}
?>