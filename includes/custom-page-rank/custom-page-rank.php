<?php
/**
 * theme_page_rank
 *
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
//add_filter('theme_includes',function($fns){
//	$fns[] = 'theme_page_rank::init';
//	return $fns;
//});
class theme_page_rank{
	
	public static $iden = 'theme_page_rank';
	public static $page_slug = 'rank';
	
	public static function init(){
		add_action('init',__CLASS__ . '::page_create');

		//add_action('page_settings', 		__CLASS__ . '::display_backend');

		//add_action('wp_ajax_' . self::$iden, __CLASS__ . '::process');
		
		//add_filter('theme_options_save', 	__CLASS__ . '::options_save');

		//add_action('backend_seajs_alias',__CLASS__ . '::backend_seajs_alias');

		//add_action('after_backend_tab_init',__CLASS__ . '::backend_seajs_use'); 


		
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(self::$iden);
		
		if(empty($key)){
			return $caches;
		}else{
			return isset($caches[$key]) ? $caches[$key] : false;
		}
	}
	public static function options_save($opts){
		if(isset($_POST[self::$iden])){
			$opts[self::$iden] = $_POST[self::$iden];
		}
		return $opts;
	}
	public static function display_backend(){
		$opt = self::get_options();
		?>
		<fieldset>
			<legend><?= ___('Categories index settings');?></legend>
			<p class="description"><?= ___('Display posts number or alphabet slug index on categories index page.')?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?= ___('Index Categories');?></th>
						<td>
							<?= theme_features::cat_checkbox_list(self::$iden,'cats');?>
						</td>
					</tr>
					<tr>
						<th><?= ___('Control');?></th>
						<td>
							<div id="<?= self::$iden;?>-tip-clean-cache"></div>
							<p>
							<a href="javascript:;" class="button" id="<?= self::$iden;?>-clean-cache" data-tip-target="<?= self::$iden;?>-tip-clean-cache"><i class="fa fa-refresh"></i> <?= ___('Flush cache');?></a>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function process(){
		theme_features::check_referer();
		$output = [];
		wp_cache_delete(self::$iden);
		$output['status'] = 'success';
		$output['msg'] = ___('Cache has been cleaned.');
		die(theme_features::json_format($output));
	}
	public static function page_create(){
		if(!current_user_can('manage_options')) 
			return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '',
				'post_name'		=> self::$page_slug,
				'post_title'	=> ___('Rank'),
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
			$page = theme_cache::get_page_by_path($k);
			if(!$page)
				$page_id = wp_insert_post(array_merge($defaults,$v));
		}

	}
	public static function get_tabs($key = null){

		$base_url = get_permalink(theme_cache::get_page_by_path(self::$iden)->ID);
		$types = [
			'recommend' => [
				'tx' => ___('Recommend'),
				'icon' => 'star',
				'url' => add_query_arg([
					'type' => 'recommend'
				],$base_url),
			],
			
			'latest' => [
				'tx' => ___('Latest'),
				'icon' => 'refresh',
				'url' => add_query_arg([
					'type' => 'latest'
				],$base_url),
			],
			
			'popular' => [
				'tx' => ___('Popular'),
				'icon' => 'bar-chart',
				'url' => add_query_arg([
					'type' => 'popular'
				],$base_url),
				'filter' => [
					'day' => [
						'tx' => ___('Daily popular'),
						'url' => add_query_arg([
							'type' => 'popular',
							'filter' => 'day',
						],$base_url),
					],
					'week' => [
						'tx' => ___('Weekly popular'),
						'url' => add_query_arg([
							'type' => 'popular',
							'filter' => 'week',
						],$base_url),
					],
					'month' => [
						'tx' => ___('Monthly popular'),
						'url' => add_query_arg([
							'type' => 'popular',
							'filter' => 'month',
						],$base_url),
					],
				],/** end filter */
			],/** end popular */
			'user' => [
				'tx' => ___('User rank'),
				'icon' => 'users',
				'url' => add_query_arg([
					'type' => 'user'
				],$base_url),
			]
		];/** end types */
	}
	public static function display_frontend(){
		
	}
	public static function backend_seajs_alias($alias){
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__,'backend');
		return $alias;
	}
	public static function backend_seajs_use(){
		?>
		seajs.use('<?= self::$iden;?>',function(m){
			m.config.process_url = '<?= theme_features::get_process_url(array('action'=>self::$iden));?>';
			m.config.lang.M00001 = '<?= ___('Loading, please wait...');?>';
			m.init();
		});
		<?php
	}
}