<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_point_views_per_hundred::init';
	return $fns;
});
class theme_custom_point_views_per_hundred{

	public static $iden = 'theme_custom_point_views_per_hundred';
	public static $type_key = 'views-per-hundred';
	public static $post_meta_key = 'views';
	
	public static function init(){
		if(!self::is_enabled())
			return false;

		self::$post_meta_key = theme_post_views::$post_meta_key;
		
		add_filter('custom_point_value_default',__CLASS__ . '::filter_custom_point_value_default');
		
		add_filter('custom_point_types',__CLASS__ . '::filter_custom_point_types');

		add_action('update_postmeta' , __CLASS__ . '::action_update_postmeta', 10, 4);

		/**
		 * list history
		 */
		foreach([
			'list_history'
		] as $v)
			add_action('list_point_histroy',__CLASS__ . '::' . $v);
			
		foreach([
			'list_noti'
		] as $v)
			add_action('list_noti',__CLASS__ . '::' . $v);

	}
	
	public static function is_enabled(){
		return wp_using_ext_object_cache() && class_exists('theme_custom_point') && class_exists('theme_post_views') && theme_post_views::is_enabled();
	}
	public static function filter_custom_point_types(array $types = []){
		$types[self::$type_key] = [
			'text' => ___('When post per hundred views'),
			'type' => 'number',
		];
		return $types;
	}
	public static function get_points_default(){
		return 20;
	}
	public static function filter_custom_point_value_default(array $opts = []){
		$opts[self::$type_key] = self::get_points_default();
		return $opts;
	}
	public static function get_times($post_id){
		return (int)wp_cache_get($post_id,self::$iden);
	}
	private static function set_times($post_id,$times){
		wp_cache_set($post_id,$times,self::$iden,3600*24*29);
	}
	private static function reset_times($post_id){
		self::set_times($post_id,self::get_views($post_id));
	}
	private static function is_max_times($post_id){
		return self::get_views($post_id) - 100 >= self::get_times($post_id);
	}
	private static function get_views($post_id){
		static $caches = [];
		if(!isset($caches[$post_id]))
			$caches[$post_id] = (int)theme_post_views::get_views($post_id);
		return $caches[$post_id];
	}
	public static function action_update_postmeta($meta_id, $object_id, $meta_key, $meta_value){
		if($meta_key !== self::$post_meta_key)
			return;
			
		/** only run once */
		static $i = 0;
		if($i !== 0)
			return;
		++$i;
		
		if(self::is_max_times($object_id)){
			$user_id = self::get_post($object_id)->post_author;
			$new_point = theme_custom_point::get_point($user_id) + self::get_points_value();
			/** update user point */
			theme_custom_point::update_user_points($user_id, $new_point);
			
			/** add history */
			self::add_history_views_per_hundred($object_id);
			
			/** add noti */
			//self::add_noti_views_per_hundred($object_id);
			
			/** reset times */
			self::reset_times($object_id);
			
			return;
		}

		/**
		 * if new post, reset times
		 */
		if(self::get_times($object_id) == 0){
			self::reset_times($object_id);
		}

	}
	private static function get_timestamp(){
		static $cache = null;
		if($cache === null)
			$cache = current_time('timestamp');
		return $cache;
	}
	public static function get_post($post_id){
		static $caches = [];
		if(!isset($caches[$post_id]))
			$caches[$post_id] = get_post($post_id);
		return $caches[$post_id];
	}
	public static function get_points_value(){
		return (int)theme_custom_point::get_point_value(self::$type_key);
	}
	public static function add_history_views_per_hundred($post_id){
		$post = self::get_post($post_id);
		if(!$post)
			return false;
			
		$meta = [
			'type'=> self::$type_key,
			'timestamp' => self::get_timestamp(),
			'post-id' => $post->ID,
			'views' => self::get_views($post_id),
			'points' => theme_custom_point::get_point_value(self::$type_key),
		];
		/**
		 * add to history
		 */
		theme_custom_point::add_history($post->post_author,$meta);
	}
	public static function add_noti_views_per_hundred($post_id){
		if(!class_exists('theme_notification'))
			return;
			
		$post = self::get_post($post_id);
		if(!$post)
			return false;
			
		$meta = [
			'type'=> self::$type_key,
			'timestamp' => self::get_timestamp(),
			'post-id' => $post->ID,
			'views' => self::get_views($post_id),
			'points' => theme_custom_point::get_point_value(self::$type_key),
		];
		theme_notification::add_noti($post->post_author,$meta);
	}
	
	public static function list_history($meta){
		if($meta['type'] !== self::$type_key)
			return;

		global $post;
		$post = self::get_post($meta['post-id']);
		setup_postdata($post);

		$post_title = esc_html(get_the_title());

		$post_permalink = esc_url(get_the_permalink());
		?>
		<li class="list-group-item">
			<?php theme_custom_point::the_list_icon('eye');?>
			<?php theme_custom_point::the_point_sign($meta['points']);?>
			<span class="history-text">
				<?= sprintf(
					___('Your post %1$s reached per hundred views, %2$s %3$s. Views are %4$s.'),
					
					'<a href="' . $post_permalink . '" target="_blank">' . $post_title . ' <i class="fa fa-external-link"></i></a>',
					
					'<strong>+' . $meta['points'] . '</strong>',
					
					theme_custom_point::get_point_name(),
					
					'<strong>' . $meta['views'] . '</strong>'
				);
				?>
			</span>
			
			<?php theme_custom_point::the_time($meta);?>
		</li>
		<?php
		wp_reset_postdata();
	}
	
	public static function list_noti($meta){
		if($meta['type'] !== self::$type_key)
			return;

		global $post;
		$post = self::get_post($meta['post-id']);
		setup_postdata($post);

		$post_title = esc_html(get_the_title());

		$post_permalink = esc_url(get_the_permalink());
		?>
		<div class="media">
			<div class="media-left">
				<img src="<?= theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder);?>" alt="<?= ___('Preview image');?>" data-src="<?= theme_functions::get_thumbnail_src($post->ID);?>" width="60" height="60" class="post-thumbnail media-object avatar">
			</div>
			<div class="media-body">
				<h4 class="media-heading">
					<span class="label label-default">
						<i class="fa fa-eye"></i> 
						<?= ___('Per hundred views');?>
					</span>
					<strong class="label label-danger">+<?= $meta['points'];?></strong> 
					<?php theme_notification::the_time($meta);?>
				</h4>
				<div class="excerpt">
					<p>
					<?= sprintf(
						___('Your post %1$s reached per hundred views, %2$s %3$s. Views are %4$s.'),
						'<a href="' . $post_permalink . '" target="_blank">' . $post_title . ' <i class="fa fa-external-link"></i></a>',
						
						'<strong>+' . $meta['points'] . '</strong>',
						
						theme_custom_point::get_point_name(),
						
						'<strong>' . $meta['views'] . '</strong>'
					);
					?>
					</p>
				</div>
			</div><!-- /.media-body -->
		</div><!-- /.media -->
		<?php
		wp_reset_postdata();
	}
	
}