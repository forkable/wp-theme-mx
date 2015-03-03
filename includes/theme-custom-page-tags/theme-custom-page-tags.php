<?php
/**
 * theme_page_tags
 *
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
theme_page_tags::init();
class theme_page_tags{
	
	public static $iden = 'theme_page_tags';
	public static $page_slug = 'tags-index';
	
	public static function init(){
		add_action('init',get_class() . '::page_create');
	}
	public static function get_options($key = null){
		$opt = theme_options::get_options(self::$iden);
		if(empty($key)){
			return $opt;
		}else{
			return isset($opt[$key]) ? $opt[$key] : null;
		}
	}
	public static function options_save($opts){
		if(isset($_POST[self::$iden])){
			$opts[self::$iden] = $_POST[self::$iden];
		}
		return $opts;
	}
	public static function display_backend(){
		$opt = theme_options::get_options(self::$iden);
		?>
		<fieldset>
			<legend><?php echo ___('Tags index settings');?></legend>
			<table class="form-table">
			<tbody>
			<tr>
			<th><?php echo ___('Whitelist - users ');?></th>
			<td>
				<textarea name="<?php echo self::$iden;?>[whitelist][users-ids]" id="<?php echo self::$iden;?>-whitelist-user-ids" rows="3" class="widefat code"><?php echo isset($opt['Whitelist']['user-ids']) ? esc_textarea($opt['Whitelist']['user-ids']) : null;?></textarea>
				<p class="description"><?php echo ___('User ID, multiple users separated by ,(commas). E.g. 1,2,3,4');?></p>
			</td>
			</tr>
			</tbody>
		</fieldset>
		<?php
	}
	public static function page_create(){
		if(!current_user_can('manage_options')) return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '',
				'post_name'		=> self::$page_slug,
				'post_title'	=> ___('Tags index'),
				'page_template'	=> 'page-' . self::$page_slug . '.php',
			)
		);
		
		$defaults = array(
			'post_content' 		=> '[post_content]',
			'post_name' 		=> null,
			'post_title' 		=> null,
			'post_status' 		=> 'publish',
			'post_type'			=> 'page',
			'comment_status'	=> 'closed',
		);
		foreach($page_slugs as $k => $v){
			$page = get_page_by_path($k);
			if(!$page){
				$r = wp_parse_args($v,$defaults);
				$page_id = wp_insert_post($r);
			}
		}

	}
	public static function get_tags(){
		global $wp_query,$post;
		
		$whitelist = (array)self::get_options('whitelist');

		$new_tags = [];
		/**
		 * get all whitelist posts & tag ids
		 */
		$wp_query = new WP_Query(array(
			'author__in' => isset($whitelist['user-ids']) ? explode(',',$whitelist['user']) : array(),
			'category__not_in' => array(1),
		));
		if(have_posts()){
			while(have_posts()){
				the_post();
				$tags = get_the_tags();
				/** skip empty tags */
				if(empty($tags)) 
					continue;
				
				foreach($tags as $tag){
					if(isset($new_tags[$tag->term_id]))
						continue;
					$new_tags[$tag->term_id] = $tag;
				}
			}
		}else{
			return false;
		}
		wp_reset_query();
		wp_reset_postdata();
		return $new_tags;
	}
	public static function display_frontend(){
		$tags = self::get_tags();
		if(is_null_array($tags)){
			?><div class="page-tip"><?php echo status_tip('info',___('No tag yet.'));?></div><?php
			return false;
		}
		global $wp_query,$post;
		include dirname(__FILE__) . '/inc/Pinyin/Pinyin.php';
		
		foreach($tags as $tag){
			echo Overtrue\Pinyin\Pinyin::pinyin($tag->name);
			$wp_query = new WP_Query(array(
				
			));
			wp_reset_query();
			wp_reset_postdata();
		}
	}
}