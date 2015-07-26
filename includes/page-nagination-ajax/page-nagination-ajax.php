<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_page_nagination_ajax::init';
	return $fns;
});
class theme_page_nagination_ajax{
	public static $iden = 'theme_page_nagination_ajax';
	public static function init(){
		add_action('frontend_seajs_use',	__CLASS__ . '::frontend_seajs_use');
		add_filter('frontend_seajs_alias',	__CLASS__ . '::frontend_seajs_alias');
		
		add_action('wp_ajax_' . self::$iden,	__CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . self::$iden,	__CLASS__ . '::process');
		
	}
	private static function is_enabled(){
		global $post, $numpages;
		return theme_cache::is_singular() && $numpages > 1;
	}
	public static function process(){

		theme_features::check_referer();

		$post_id = isset($_GET['post-id']) && is_numeric($_GET['post-id']) ? (int)$_GET['post-id'] : false;
		if(!$post_id)
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'invaild_post_id',
				'msg' => ___('Sorry, post id is invaild.'),
			]));
			
		global $post,$page;
		/**
		 * post
		 */
		$post = get_post($post_id);
		if(!$post)
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'post_not_exist',
				'msg' => ___('Sorry, the post does not exist.'),
			]));

		/**
		 * page
		 */
		$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : false;
		if(!$page)
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'invaild_page_number',
				'msg' => ___('Sorry, page number is invaild.'),
			]));

		set_query_var('page',$page);
		setup_postdata($post);
		ob_start();
		the_content();
		$content = html_minify(ob_get_contents());
		ob_end_clean();

		die(theme_features::json_format([
			'status' => 'success',
			'content' => $content,
		]));
	}
	
	public static function frontend_seajs_alias(array $alias = []){
		if(self::is_enabled()){
			$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		}
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!self::is_enabled())
			return false;
		global $post,$page,$numpages;
			if($page < 1)
				$page = 1;
			if($page > $numpages)
				$page = $numpages;
			
			?>
		seajs.use('<?= self::$iden;?>',function(m){
			m.config.process_url = '<?= theme_features::get_process_url([
				'action' => self::$iden,
				'post-id' => $post->ID,
			]);?>';
			m.config.post_id = <?= $post->ID;?>;
			m.config.numpages = <?= $numpages;?>;
			m.config.page = <?= $page;?>;
			m.config.url_tpl = <?= json_encode(theme_features::get_link_page_url(9999));?>;
			m.config.lang.M01 = '<?= ___('Loading, please wait...');?>';
			m.config.lang.M02 = '<?= ___('Content loaded.');?>';
			m.config.lang.M03 = '<?= ___('Already first page.');?>'
			m.config.lang.M04 = '<?= ___('Already last page.');?>'
			m.config.lang.E01 = '<?= ___('Sorry, some server error occurred, the operation can not be completed, please try again later.');?>';
			m.init();
		});
		<?php
	}

}

?>