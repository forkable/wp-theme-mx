<?php
/*
Feature Name:	theme-cache
Feature URI:	http://inn-studio.com
Version:		2.1.5
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
	public static $iden = 'theme_cache';
	
	public static $cache;
	public static $cache_key;
	
	public static function init(){
		self::$cache_key = md5(AUTH_KEY . theme_functions::$iden);
		
		add_action('advanced_settings',__CLASS__ . '::display_backend');
		add_action('wp_ajax_' . self::$iden, __CLASS__ . '::process');
		/**
		 * When delete menu
		 */
		add_filter('pre_set_theme_mod_nav_menu_locations',function($return){
			$caches = (array)self::get(self::$cache_key);
			if(!isset($caches['nav-menus'])) return $return;
			unset($caches['nav-menus']);
			self::set(self::$cache_key,$caches);
			return $return;
		});
		/**
		 * When delete menu
		 */
		add_action('wp_delete_nav_menu',function(){
			$caches = (array)self::get(self::$cache_key);
			if(!isset($caches['nav-menus'])) return;
			unset($caches['nav-menus']);
			self::set(self::$cache_key,$caches);
		});
		/**
		 * When update widget
		 */
		add_filter('widget_update_callback',function($instance){
			$caches = (array)self::get(self::$cache_key);
			if(!isset($caches['widget-sidebars'])) return $instance;
			unset($caches['widget-sidebars']);
			self::set(self::$cache_key,$caches);
			return $instance;
		});
		
		/**
		 * When update option for widget
		 */
		add_action('update_option_sidebars_widgets',function(){
			$caches = (array)self::get(self::$cache_key);
			if(!isset($caches['widget-sidebars'])) return;
			unset($caches['widget-sidebars']);
			self::set(self::$cache_key,$caches);
		});
		/**
		 * When delete post
		 */
		add_action('delete_post',function(){
			$caches = (array)self::get(self::$cache_key);
			if(!isset($caches['queries'])) return;
			unset($caches['queries']);
			self::set(self::$cache_key,$caches);
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
		return esc_url(add_query_arg(array(
			'action' => self::$iden,
			'type' => $type
		),theme_features::get_process_url()));

	}
	/**
	 * Admin Display
	 */
	public static function display_backend(){
		?>
		<fieldset id="<?= self::$iden;?>">
			<legend><?= ___('Theme cache');?></legend>
			<p class="description"><?= ___('Maybe the theme used cache for improve performance, you can clean it when you modify some site contents if you want.');?></p>
			<table class="form-table">
				<tbody>
					<?php if(class_exists('Memcache')){ ?>
					<tr>
						<th><?= ___('Memcache cache');?></th>
						<td><p>
							<?php
							if(file_exists(WP_CONTENT_DIR . '/object-cache.php')){ ?>
								<a class="button" href="<?= self::get_process_url('disable-cache');?>" onclick="return confirm('<?= ___('Are you sure DELETE object-cache.php to disable theme object cache?');?>')">
									<?= ___('Disable theme object cache');?>
								</a>
								
								<a class="button" href="<?= self::get_process_url('re-enable-cache');?>" onclick="return confirm('<?= ___('Are you sure RE-CREATE object-cache.php to re-enable theme object cache?');?>')">
									<?= ___('Re-enable theme object cache');?>
								</a>
								
							<?php }else { ?>
								<a class="button-primary" href="<?= self::get_process_url('enable-cache');?>">
									<?= ___('Enable theme object cache');?>
								</a>
							<?php } ?>
							<span class="description"><i class="fa fa-exclamation-circle"></i> <?= ___('Save your settings before click.');?></span>
							
						</p></td>
					</tr>
					<?php } ?>
					<tr>
						<th scope="row"><?= ___('Control');?></th>
						<td>
							<?php
							if(isset($_GET[self::$iden])){
								echo status_tip('success',___('Theme cache has been cleaned or rebuilt.'));
							}
							?>
							<p>
								<a href="<?= self::get_process_url('flush');?>" class="button" onclick="javascript:this.innerHTML='<?= ___('Processing, please wait...');?>'"><?= ___('Clean all cache');?></a>
								
								<a href="<?= self::get_process_url('widget-sidebars');?>" class="button" onclick="javascript:this.innerHTML='<?= ___('Processing, please wait...');?>'"><?= ___('Clean widget cache');?></a>
								
								<a href="<?= self::get_process_url('nav-menus');?>" class="button" onclick="javascript:this.innerHTML='<?= ___('Processing, please wait...');?>'"><?= ___('Clean menu cache');?></a>
								
								
								<span class="description"><i class="fa fa-exclamation-circle"></i> <?= ___('Save your settings before clean');?></span>
								
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
		$type = isset($_GET['type']) ? $_GET['type'] : null;
		if(!self::current_user_can('manage_options'))
			die();
			
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
				$caches = (array)self::get(self::$cache_key);
				if(isset($caches[$type])){
					unset($caches[$type]);
					self::set(self::$cache_key,$caches);
				}
		}
		wp_redirect(admin_url('themes.php?page=core-options&' . self::$iden . '=1'));

		die();
	}
	public static function cleanup(){
		if(wp_using_ext_object_cache()){
			return wp_cache_flush();
		}
	}
	public static function get_current_user_id(){
		if(!self::is_user_logged_in())
			return false;
		static $cache = null;
		if($cache === null)
			$cache = get_current_user_id();
		return $cache;
	}
	public static function current_user_can($key){
		if(!self::is_user_logged_in())
			return false;
		static $caches = [];
		if(isset($caches[$key]))
			return $caches[$key];
		$caches[$key] = current_user_can($key);
		return $caches[$key];
	}
	public static function home_url($path = null){
		static $caches = [],$cache = null;
		if($path === null){
			if($cache !== null)
				return $cache;
			$cache = home_url();
			return $cache;
		}else{
			if(isset($caches[$path]))
				return $caches[$path];
			$caches[$path] = home_url($path);
			return $caches[$path];
		}
	}
	public static function is_singular(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_singular();
		return $cache;
	}
	public static function is_page($page = null){
		static $caches = [],$cache = null;
		if($page === null){
			if($cache === null)
				$cache = is_page();
			return $cache;
		}
		if(!isset($caches[$page]))
			$caches[$page] = is_page($page);
		return $caches[$page];
	}
	public static function get_bloginfo($key){
		static $caches = [];
		if(!isset($caches[$key]))
			$caches[$key] = get_bloginfo($key);
		return $caches[$key];
	}
	public static function is_user_logged_in(){
		static $cache = null;
		if($cache === null)
			$cache = (bool)is_user_logged_in();
		return $cache;
	}
	public static function get_author_posts_url($user_id){
		static $caches = [];
		$cache_id = $user_id;
		$group_id = 'author_posts_urls';
		if(isset($caches[$cache_id]))
			return $caches[$cache_id];

		$caches[$cache_id] = wp_cache_get($cache_id,$group_id);
		
		if(!$caches[$cache_id]){
			$caches[$cache_id] = get_author_posts_url($user_id);
			wp_cache_set($group_id,$caches[$cache_id],$group_id,2505600);
		}
		
		return $caches[$cache_id];
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
		return self::$cache_key . $group . '-' . $key;
	}
	/**
	 * Delete cache
	 *
	 * @param string $key Cache key
	 * @param string $group Cache group
	 * @return bool
	 * @version 2.0
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
	 * @version 2.0.4
	 */
	public static function set($key,$data,$group = null,$expire = 3600){
		if(theme_dev_mode::is_enabled())
			return false;
			
		if(wp_using_ext_object_cache()){
			if(!$group)
				$group = 'default';
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
	 */
	public static function get($key,$group = null,$force = false){
		/**
		 * if dev mode enabled, do NOT get data from cache
		 */
		if(theme_dev_mode::is_enabled()) return false;
		
		if(wp_using_ext_object_cache()){
			if(!$group)
				$group = 'default';
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
	 */
	public static function get_comments($args,$expire = 3600){
		$cache_group_id = 'comments';
		$id = md5(serialize(func_get_args()));
		$caches = (array)self::get(self::$cache_key);
		$cache = isset($caches[$cache_group_id][$cache_id]) ? $caches[$cache_group_id][$cache_id] : null;
		if(empty($cache)){
			$cache = get_comments($args);
			$caches[$cache_group_id][$cache_id] = $cache;
			self::set(self::$cache_key,$caches,null,$expire);
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
	 */
	public static function get_queries($args,$expire = 3600){
		$cache_group_id = 'queries';
		$cache_id = md5(serialize(func_get_args()));
		$caches = (array)self::get(self::$cache_key);
		$cache = isset($caches[$cache_group_id][$cache_id]) ? $caches[$cache_group_id][$cache_id] : null;
		if(empty($cache)){
			$cache = new WP_Query($args);
			$caches[$cache_group_id][$cache_id] = $cache;
			self::set(self::$cache_key,$caches,null,$expire);
			//wp_reset_postdata();
		}
		return $cache;
	}
	private static function get_page_prefix(){
		static $caches = [];
		if(isset($caches[self::$iden]))
			return $caches[self::$iden];
			
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
		$caches[self::$iden] = $cache_id_prefix;
		return $caches[self::$iden];
	}
	/**
	 * output dynamic sidebar from cache
	 *
	 * @param string The widget sidebar name/id
	 * @param int Cache expire time
	 * @return string
	 * @version 2.0.2
	 */
	public static function dynamic_sidebar($id,$expire = 3600){
		
		$cache_group_id = 'widget-sidebars';
		$cache_id = self::get_page_prefix() . $id;
		$caches = (array)self::get(self::$cache_key);
		$cache = isset($caches[$cache_group_id][$cache_id]) ? $caches[$cache_group_id][$cache_id] : null;
		if(empty($cache)){
			ob_start();
			dynamic_sidebar($id);
			$cache = html_minify(ob_get_contents());
			ob_end_clean();
			$caches[$cache_group_id][$cache_id] = $cache;
			self::set(self::$cache_key,$caches,null,$expire);
		}
		echo $cache;
		return empty($cache) ? false : true;
	}
	/**
	 * wp nav menu from cache
	 *
	 * @param string The widget sidebar name/id
	 * @param int Cache expire time
	 * @return string
	 * @version 2.0.2
	 */
	public static function wp_nav_menu($args,$expire = 3600){
		$cache_group_id = 'nav-menus';

		$cache_id = self::get_page_prefix() . $args['theme_location'];
		$caches = (array)self::get(self::$cache_key);
		$cache = isset($caches[$cache_group_id][$cache_id]) ? $caches[$cache_group_id][$cache_id] : null;

		if(empty($cache)){
			ob_start();
			wp_nav_menu($args);
			$cache = html_minify(ob_get_contents());
			ob_end_clean();
			$caches[$cache_group_id][$cache_id] = $cache;
			self::set(self::$cache_key,$caches,null,$expire);
		}
		echo $cache;
	}
	
}
?>