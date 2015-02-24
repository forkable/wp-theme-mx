<?php
/** 
 * @version 1.0.0
 */
theme_custom_contribution::init();
class theme_custom_contribution{
	public static $iden = 'theme-custom-contribution';
	public static $page_slug = 'contribution';
	public static $pages = array();
	public static $post_meta_key = array(
		'bdyun' => '_theme_ctb_bdyun'
	);
	public static function init(){
		/** filter */
		add_filter('frontend_seajs_alias',	get_class() . '::frontend_seajs_alias');

		
		/** action */
		add_action('init', 					get_class() . '::page_create');

		add_action('frontend_seajs_use',	get_class() . '::frontend_seajs_use');
		
		add_action('wp_ajax_' . get_class(), get_class() . '::process');

		add_action('wp_enqueue_scripts', 	get_class() . '::frontend_css');

	}
	public static function page_create(){
		if(!current_user_can('manage_options')) return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '[' . self::$page_slug . ']',
				'post_name'		=> self::$page_slug,
				'post_title'	=> ___('Contribution'),
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
	public static function display_backend(){
		
	}
	public static function get_options($key = null){
		$opt = theme_options::get_options(self::$iden);
		if(empty($key)){
			return $opt;
		}else{
			return isset($opt[$key]) ? $opt[$key] : null;
		}
	}
	public static function get_url(){
		$page = get_page_by_path(self::$page_slug);
		return empty($page) ? null : get_permalink($page->ID);
	}
	public static function process(){
		$output = array();
		
		theme_features::check_referer();
		theme_features::check_nonce();
		
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
		switch($type){
			/**
			 * case upload
			 */
			case 'upload':
				
				break;
		}

		die(theme_features::json_format($output));
	}
	public static function frontend_seajs_alias($alias){
		if(!is_user_logged_in() || !is_page(self::$page_slug)) return $alias;

		$alias[self::$iden] = theme_features::get_theme_includes_js(__FILE__);
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!is_user_logged_in() || !is_page(self::$page_slug)) return false;
		?>
		seajs.use('<?php echo self::$iden;?>',function(m){
			m.config.process_url = '<?php echo theme_features::get_process_url(array('action' => self::$iden));?>';
			m.config.lang.M00001 = '<?php echo esc_js(___('Loading, please wait...'));?>';
			m.config.lang.E00001 = '<?php echo esc_js(___('Sorry, server error please try again later.'));?>';
			
			m.init();
		});
		<?php
	}
	public static function frontend_css(){
		if(!is_user_logged_in() || !is_page(self::$page_slug)) return;
		wp_enqueue_style(self::$iden,theme_features::get_theme_includes_css(__FILE__,'style',false),false,theme_features::get_theme_info('version'));
	}

}