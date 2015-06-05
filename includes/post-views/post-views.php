<?php
/*
Feature Name:	Post Views
Feature URI:	http://www.inn-studio.com
Version:		3.0.0
Description:	Count the post views.
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_post_views::init';
	return $fns;
});
class theme_post_views{
	private static $iden = 'theme_post_views';
	private static $post_meta_key = 'views';
	private static $cache_key = array(
		'views' => 'theme_post_views',
		'times' => 'theme_post_views_times'
	);
	private static $expire = 2505600;/** 29 days */
	private static $cookie_expire = 60;/** 1 min */
	private static $cookie = null;
	public static $opt;
	public static function init(){

		add_action('base_settings',		__CLASS__ . '::display_backend');

		add_filter('theme_options_default',__CLASS__ . '::options_default');

		add_filter('theme_options_save',__CLASS__ . '::options_save');

		self::$opt = (array)theme_options::get_options(self::$iden);
		
		if(self::is_enabled() === false)
			return;

		add_filter('frontend_seajs_alias',	__CLASS__ . '::frontend_seajs_alias');
		add_action('frontend_seajs_use',	__CLASS__ . '::frontend_seajs_use');

		
		add_filter('cache_request',__CLASS__ . '::process_cache_request');
		add_filter('js_cache_request',__CLASS__ . '::js_cache_request');


		/** admin post/page css */
		add_action('admin_head', __CLASS__ . '::admin_css');
		add_action('manage_posts_custom_column',__CLASS__ . '::admin_show',10,2);
		add_filter('manage_posts_columns', __CLASS__ . '::admin_add_column');
	}
	public static function options_default($opts = []){
		$opts[self::$iden] = array(
			'enabled' => 1,
			'storage-times' => 10,
		);
		return $opts;
	}
	public static function display_backend(){
		$checked = self::is_enabled() ? ' checked ' : null;
		?>
		<fieldset>
			<legend><?= ___('Post views settings');?></legend>
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="<?= self::$iden;?>-enabled"><?= ___('Enable');?></label></th>
						<td>
							<label for="<?= self::$iden;?>-enabled">
								<input type="checkbox" name="<?= self::$iden;?>[enabled]" id="<?= self::$iden;?>-enabled" value="1" <?= $checked;?>> 
								<?= ___('Enabled');?>
							</label>
						</td>
					</tr>
					<?php if(wp_using_ext_object_cache()){ ?>
						<tr>
							<th><label for="<?= self::$iden;?>-storage-times"><?= ___('Max cache storage times');?></label></th>
							<td>
								<input class="short-text" type="number" name="<?= self::$iden;?>[storage-times]" id="<?= self::$iden;?>-storage-times" value="<?= self::get_storage_times();?>" min="1">
								<span class="description"><?= ___('Using cache to improve performance. When the views more than max storage times, views will be save to database.');?></span>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	
	public static function update_views($post_id){
		if(wp_using_ext_object_cache()){
			return self::update_views_using_cache($post_id);
		}else{
			return self::update_views_using_db($post_id);
		}
	}
	private static function update_views_using_db($post_id){
		$meta = (int)get_post_meta($post_id,self::$post_meta_key,true) + 1;
		update_post_meta($post_id,self::$post_meta_key,$meta);
		return $meta;
	}
	/**
	 * update_views_using_cache
	 * 
	 * 
	 * @return 
	 * @version 1.0.2
	 * 
	 */
	private static function update_views_using_cache($post_id,$force = false){

		$times = wp_cache_get($post_id,self::$iden);

		$meta = (int)get_post_meta($post_id,self::$post_meta_key,true) + (int)$times;
		/**
		 * force to update db
		 */
		if($force){
			$meta++;
			wp_cache_set($post_id,0,self::$iden,self::$expire);
			update_post_meta($post_id,self::$post_meta_key,$meta);
		/**
		 * update cache
		 */
		}else{
			/**
			 * if views more than storage times, update db and reset cache
			 */
			if($times >= self::get_storage_times()){
				$meta = $meta + $times + 1;
				update_post_meta($post_id,self::$post_meta_key,$meta);
				wp_cache_set($post_id,0,self::$iden,self::$expire);
			/**
			 * update cache
			 */
			}else{
				if($times === false)
					wp_cache_set($post_id,0,self::$iden,self::$expire);
					
				wp_cache_incr($post_id,1,self::$iden);
				$meta++;
			}
		}
		return $meta;
	}
	private static function get_storage_times(){
		if(isset(self::$opt['storage-times']) && (int)self::$opt['storage-times'] !== 0){
			return (int)self::$opt['storage-times'];
		}else{
			return 10;
		}
	}
	/**
	 * get the views
	 * 
	 * @params int $post_id
	 * @return int the views
	 * @version 2.0.0
	 * 
	 */
	public static function get_views($post_id = null){
		if(!$post_id){
			global $post;
			$post_id = $post->ID;
		}
		
		$meta = (int)get_post_meta($post_id,self::$post_meta_key,true) + 1;
		
		if(wp_using_ext_object_cache())
			return $meta + (int)wp_cache_get($post_id,self::$iden);
		
		return $meta;
	}
	public static function is_enabled(){
		
		if(isset(self::$opt['enabled']) && self::$opt['enabled'] == 1)
			return true;
			
		return false;
	}
	public static function options_save($options){
		if(isset($_POST[self::$iden])){
			$options[self::$iden] = $_POST[self::$iden];
		}
		return $options;
	}

	public static function admin_add_column($columns){
		$columns[self::$post_meta_key] = ___('Views');
		return $columns;
	}
	public static function admin_show($column_name,$post_id){
		if ($column_name != 'views') return;	
		echo self::get_views($post_id);
	}
	public static function admin_css(){
		?><style>.fixed .column-views{width:3em}</style><?php
	}

	public static function process_cache_request(array $output = []){
		$id = isset($_GET[self::$iden]) && is_string($_GET[self::$iden]) ? (int)$_GET[self::$iden] : null;
		
		if(empty($id))
			return $output;

		if(!self::is_viewed($id)){
			$views = self::update_views($id);
		}else{
			$views = self::get_views($id);
		}
		
		$output['views'] = [
			$id => $views
		];
		return $output;
	}
	public static function get_viewed_ids(){
		if(self::$cookie === null)
			self::$cookie = isset($_COOKIE[self::$iden]) ? json_decode($_COOKIE[self::$iden],true) : false;

		return self::$cookie;
	}
	public static function set_viewed_ids($post_id){
		$expire = time() + self::$cookie_expire;
		self::$cookie = self::get_viewed_ids();
		if(empty(self::$cookie)){
			self::$cookie = [$post_id];
			setcookie(self::$iden,json_encode([$post_id]),$expire);
			return true;
		}else{
			if(!in_array($post_id,self::$cookie)){
				self::$cookie[] = $post_id;
				setcookie(self::$iden,json_encode(self::$cookie),$expire);
				return true;
			}
			return false;
		}
	}
	public static function is_viewed($post_id){
		return !self::set_viewed_ids($post_id);
	}
	private static function is_singular_post(){
		static $cache = null;
		if($cache === null)
			$cache = is_singular('post');

		return $cache;
	}
	public static function js_cache_request(array $alias = []){
		if(!self::is_singular_post())
			return $alias;
		$alias[self::$iden] = get_the_ID();
		return $alias;
	}
	public static function frontend_seajs_alias(array $alias = []){
		if(!self::is_singular_post())
			return $alias;

		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!self::is_singular_post())
			return false;
		?>
		seajs.use('<?= self::$iden;?>',function(m){
			m.init();
		});
		<?php
	}
}
?>