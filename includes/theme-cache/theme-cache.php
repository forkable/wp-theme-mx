<?php
/*
Feature Name:	theme-cache
Feature URI:	http://inn-studio.com
Version:		2.1.1
Description:	theme-cache
Author:			INN STUDIO
Author URI:		http://inn-studio.com
*/

theme_cache::init();
class theme_cache{
	public static $cache_expire = 3600;
	public static $iden = 'theme-cache';
	
	public static $cache;
	public static $cache_skey;
	
	public static function init(){
		self::$cache_skey = md5(AUTH_KEY . theme_functions::$iden);
		
		if(!wp_using_ext_object_cache()){
			if(!class_exists('phpFastCache')) include dirname(__FILE__) . '/inc/phpfastcache.php';
			self::$cache = new phpFastCache();
			self::$cache->option('securityKey',self::$cache_skey);
			self::$cache->option('path',WP_CONTENT_DIR);
		}
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
		$result = copy(dirname(__FILE__) . '/object-cache.php',WP_CONTENT_DIR . '/object-cache.php');
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
		self::$cache->clean();
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
	 * @version 2.0.1
	 * @author KM@INN STUDIO
	 */
	public static function set($key,$data,$group = '',$expire = 3600){
		$key = self::build_key($key,$group);
		$keys = (array)self::get('keys');
		$keys_id = self::build_key('keys');
		$add_to_keys = false;
		if(!isset($keys[$group]) || !in_array($key,$keys[$group])){
			$keys[$group][] = $key;
			$add_to_keys = true;
		}
		if(wp_using_ext_object_cache()){
			if($add_to_keys) wp_cache_set($keys_id,$keys,'',2505600);
			return wp_cache_set($key,$data,$group,$expire);
		}
		if($add_to_keys) self::$cache->set($keys_id,$keys,2505600);
		return self::$cache->set($key,$data,$expire);
	}
	/**
	 * Get the cache
	 *
	 * @param string $key Cache ID
	 * @param string $group Cache group
	 * @param bool $force True to get cache forced
	 * @return mixed
	 * @version 2.0.0
	 * @author KM@INN STUDIO
	 */
	public static function get($key,$group = '',$force = false){
		$key = self::build_key($key,$group);
		if(wp_using_ext_object_cache()){
			return wp_cache_get($key,$group);
		}
		return self::$cache->get($key);
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
	/**
	 * output dynamic sidebar from cache
	 *
	 * @param string The widget sidebar name/id
	 * @param int Cache expire time
	 * @return string
	 * @version 2.0.1
	 * @author KM@INN STUDIO
	 */
	public static function dynamic_sidebar($id,$expire = 3600){
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
		}else{
			$cache_id_prefix = 'unknow';
		}
		
		$cache_group_id = 'widget-sidebars';
		$cache_id = $cache_id_prefix . $id;
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
	 * @version 2.0.1
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
		
		if(is_singular()){
			global $post;
			$cache_id_prefix = 'post-' . $post->ID;
		}else if(is_home()){
			$cache_id_prefix = 'home';
		}else if(is_category()){
			$cache_id_prefix = 'cat-' . theme_features::get_current_cat_id();
		}else if(is_tag()){
			$cache_id_prefix = 'cat-' . theme_features::get_current_tag_id();
		}else if(is_search()){
			$cache_id_prefix = 'search';
		}else if(is_404()){
			$cache_id_prefix = 'error404';
		}else{
			$cache_id_prefix = 'unknow';
		}
		$cache_id = $cache_id_prefix . $args['theme_location'];
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