<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_point_bomb::init';
	return $fns;
});
class theme_custom_point_bomb{

	public static $iden = 'theme_custom_point_bomb';
	public static $page_slug = 'account';
	
	public static function init(){
		
		foreach(self::get_tabs() as $k => $v){
			$nav_fn = 'filter_nav_' . $k; 
			add_filter('account_navs',__CLASS__ . "::$nav_fn",$v['filter_priority']);
		}

		add_filter('wp_title',				__CLASS__ . '::wp_title',10,2);

		
		add_action('wp_ajax_' . self::$iden, __CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . self::$iden, __CLASS__ . '::process');
		
		add_filter('frontend_seajs_alias',__CLASS__ . '::frontend_seajs_alias');
		add_action('frontend_seajs_use',__CLASS__ . '::frontend_seajs_use');

		add_filter('custom_point_options_default',__CLASS__ . '::filter_custom_point_options_default');

		add_filter('custom_point_types',__CLASS__ . '::filter_custom_point_types');
	}
	public static function wp_title($title, $sep){
		if(!self::is_page()) 
			return $title;
			
		if(self::get_tabs(get_query_var('tab'))){
			$title = self::get_tabs(get_query_var('tab'))['text'];
		}
		return $title . $sep . get_bloginfo('name');
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = is_page(self::$page_slug) && self::get_tabs(get_query_var('tab'));
			
		return $cache;
	}
	public static function filter_query_vars($vars){
		if(!in_array('tab',$vars)) $vars[] = 'tab';
		return $vars;
	}
	public static function filter_nav_bomb($navs){
		$navs['bomb'] = '<a href="' . self::get_tabs('bomb')['url'] . '">
			<i class="fa fa-' . self::get_tabs('bomb')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('bomb')['text'] . '
		</a>';
		return $navs;
	}
	public static function get_url(){
		static $cache = null;
		if($cache === null){
			$page = theme_cache::get_page_by_path(self::$page_slug);
			$cache = esc_url(get_permalink($page->ID));
		}
		return $cache;
	}
	public static function get_tabs($key = null, $target_id = null){
		$baseurl = self::get_url();
		/**
		 * target param
		 */
		if( is_numeric($target_id) )
			$baseurl = add_query_arg('target',(int)$target_id,$baseurl);
			
		$tabs = array(
			'bomb' => array(
				'text' => ___('Bomb!'),
				'icon' => 'bomb',
				'url' => esc_url(add_query_arg('tab','bomb',$baseurl)),
				'filter_priority' => 33,
			),
		);
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
	public static function filter_custom_point_types(array $types = []){
		$types['bomb-percent'] = [
			'text' => ___('Victory percentage'),
			'des' => ___('User bombs other user points victory percentage. The unit is the percentage.'),
		];
		$types['bomb'] = [
			'text' => ___('When user bomb points'),
			'des' => ___('Use commas to separate multiple point, first as the default.'),
		];
		return $types;
	}
	public static function filter_custom_point_options_default(array $opts = []){
		$opts['points']['bomb'] = '10,50,100';
		return $opts;
	}
	public static function get_point_values(){
		static $cache = null;

		if($cache !== null)
			return $cache;
			
		$values = explode(',',theme_custom_point::get_point_value('pk'));
		
		$cache = array_map(function($v){
			$v = trim($v);
			if(is_numeric($v))
				return $v;
		},$values);
		
		return $cache;
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = theme_options::get_options(self::$iden);

		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;

		return $caches;
	}
	public static function options_default(array $opts = []){
		$opts[self::$iden]['des'] = self::get_des_default();
		return $opts;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[self::$iden])){
			$opts[self::$iden] = $_POST[self::$iden];
		}
		return $opts;
	}
	public static function theme_custom_point_backend(){
		?>
		<h3><?= ___('User points bomb description');?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th><?= ___('Description');?></th>
					<td>
						<textarea name="<?= self::$iden;?>[des]" id="<?= self::$iden;?>-des" cols="50" rows="3" class="widefat"><?= self::get_des();?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}
	public static function get_des_default(){
		return 
'<div class="well">
	<p>' . sprintf(___('Welcome to bomb world. You can consume your %1$s to bomb opponent\'s %1$s. Please read the blew item before bombs:'), theme_custom_point::get_point_name()) . '</p>
	<ol>
		<li>' . sprintf(___('If you hit the opponent when you bomb, your consumption will come back and you will get the extra %1$s of consumption.'), theme_custom_point::get_point_name()) . '</li>
		<li>' . sprintf(___('If you miss the opponent when you bomb, the opponent will get your half of %s of consumption.'), theme_custom_point::get_point_name()) . '</li>
		<li>' . ___('Do not bomb atrociously, be careful opponent bombs back.') . '</li>
	</ol>
</div>';
	}
	public static function get_des(){
		$des = self::get_options('des');
		return trim($des) === '' ? self::get_des_default() : $des;
	}
	public static function get_victory_percent(){
		$percent = (int)theme_custom_point::get_point_value('bomb-percent');
		if($percent > 100)
			$percent = 100;
		
		if($percent < 0)
			$percent = 0;
			
		return $percent;
	}
	public static function process(){
		theme_features::check_referer();
		theme_features::check_nonce();
		$output = [];

		$type = isset($_REQUEST['type']) && is_string($_REQUEST['type']) ? $_REQUEST['type'] : null;

		switch($type){
			case 'bomb':
				$current_user_id = self::get_current_user_id();
				if(!$current_user_id){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'need_login',
						'msg' => sprintf(
							___('Sorry, please %s.'),
							'<a href="' . esc_url(add_query_arg('redirect',self::get_tabs('bomb')['url'])) . '">' . ___('log-in') . '</a>'
						),
					]));
				}
				/**
				 * check target user id
				 */
				$target_id = isset($_REQUEST['target']) && is_numeric($_REQUEST['target']) ? $_REQUEST['target'] : null;
				if(!$target_id){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'invaild_target_user_id',
						'msg' => ___('Sorry, the target user ID is invaild.'),
					]));
				}
				/**
				 * check target user
				 */
				$target = get_user_by('id',$target_id);
				if(!$target){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'target_user_not_exist',
						'msg' => ___('Sorry, the target user do not exist.'),
					]));
				}
				/**
				 * check points
				 */
				$points = isset($_REQUEST['points']) && is_numeric($_REQUEST['points']) ? $_REQUEST['points'] : null;
				if(!$points || !in_array($points,self::get_point_values())){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'invaild_point_value',
						'msg' => ___('Sorry, the point value is invaild.'),
					]));
				}
				/**
				 * check target points
				 */
				$target_points = theme_custom_point::get_point($target_id);
				if($points > $target_points){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'target_points_not_enough',
						'msg' => ___('Sorry, the target points is not enough to bear your bomb.'),
					]));
				}
				/**
				 * pass 
				 */
				/**
				 * define $hit
				 */
				$hit = false;
				if(mt_rand(0,100) <= self::get_victory_percent())
					$hit = true;

				self::add_history_for_target($current_user_id,$target_id,$points,$hit);
				
				self::add_history_for_attacker($current_user_id,$target_id,$points,$hit);

				self::add_noti_for_target($current_user_id,$target_id,$points,$hit);

				/**
				 * new target points
				 */
				$target_extra_points = self::get_extra_points_for_target($hit,$points);
				$new_target_points += $target_extra_points;
				
				/**
				 * new attacker points
				 */
				$attacker_extra_points = self::get_extra_points_for_attacker($hit,$points);
				$new_attacker_points += $attacker_extra_points;
				
				/** update attacker points */
				theme_custom_point::update_user_points( $attacker_id, $new_attacker_points);
				
				/** update target points */
				theme_custom_point::update_user_points( $target_id, $new_target_points);

				$target_name = '<a href="' . esc_url(get_author_posts_url($target_id)) . '" target="_blank" class="author"><img src="' . esc_url(get_avatar_url($target_id)) . '" width="16" height="16" alt="avatar"> ' . esc_html($target->display_name) . '</a>';
							
				/**
				 * hit target
				 */
				if( $hit ){
					
					die(theme_features::json_format([
						'status' => 'success',
						'msg' => sprintf(
							___('Bombing successfully! Your bomb hit %1$s, you got +%2$d %3$s and remaining %4$d %3$s. Target lost -%5$d %3$s and remaining %6$d %3$s.'),

							$target_name,
							
							'<strong class="plus">' . $attacker_extra_points . '</strong>',/** %2$d */
							
							theme_custom_point::get_point_name(),/** %3$s */
							
							$new_attacker_points,/** %4$d */
							
							$target_extra_points,/** %5$d */
							
							$new_target_points /** %6$d */
						),
					]));
				/**
				 * miss target
				 */
				}else{
					die(theme_features::json_format([
						'status' => 'success',
						'msg' => sprintf(
							___('Unlucky! %1$s miss your attack, you lost -%2$d %3$s and remaining %4$d %3$s. Target picked up +%5$d %3$s and remaining %6$d %3$s.'),
							
							$target_name,
							
							'<strong class="plus">' . $target_extra_points . '</strong>',/** %2$d */
							
							theme_custom_point::get_point_name(),/** %3$s */
							
							$new_attacker_points,/** %4$d */
							
							$attacker_extra_points,/** %5$d */
							
							$new_target_points /** %6$d */
						),
					]));
				}
			default:
				die(theme_features::json_format([
					'status' => 'error',
					'code' => 'invaild_type_param',
					'msg' => ___('Sorry, type param is invaild.'),
				]));
		}
	}
	private static function get_current_user_id(){
		static $cache = null;
		if($cache === null)
			$cache = get_current_user_id();

		return $cache;
	}
	private static function get_timestamp(){
		static $cache = null;
		if($cache === null)
			$cache = current_time('timestamp');
		return $cache;
	}
	private static function get_extra_points_for_target($hit,$point){
		return $hit ? 0 - $points : round($points / 2);
	}
	private static function get_extra_points_for_attacker($hit,$point){
		return $hit ? $points : 0 - $point;
	}
	public static function add_history_for_target($attacker_id,$target_id,$points,$hit){

		$meta = [
			'type'=> 'be-bomb',
			'timestamp' => self::get_timestamp(),
			'hit' => $hit,
			'attacker-id' => $attacker_id,
			'points' => self::get_extra_points_for_target($hit,$points),
		];
		
		add_user_meta($target_id,theme_custom_point::$user_meta_key['history'],$meta);
		
	}
	public static function add_history_for_attacker($attacker_id,$target_id,$points,$hit){

		$meta = [
			'type'=> 'bomb',
			'timestamp' => self::get_timestamp(),
			'hit' => $hit,
			'target-id' => $target_id,
			'points' => self::get_extra_points_for_attacker($hit,$points),
		];
		
		add_user_meta($target_id,theme_custom_point::$user_meta_key['history'],$meta);
		
	}
	public static function add_noti_for_target($attacker_id,$target_id,$points,$hit){
		$meta = [
			'id' 		=> self::get_timestamp(true),
			'type'		=> 'be-bomb',
			'timestamp' => self::get_timestamp(),
			'hit' 		=> $hit,
			'attacker-id' => $attacker_id,
			'points' 	=> self::get_extra_points_for_target($hit,$points),
		];
		
		add_user_meta($target_id,theme_notification::$user_meta_key['key'],$meta);
	}
	public static function add_noti_for_attacker($attacker_id,$target_id,$points,$hit){
		$meta = [
			'id' 		=> self::get_timestamp(true),
			'type'		=> 'bomb',
			'timestamp' => self::get_timestamp(),
			'hit' 		=> $hit,
			'target-id' => $target_id,
			'points' 	=> self::get_extra_points_for_attacker($hit,$points),
		];
		
		add_user_meta($target_id,theme_notification::$user_meta_key['key'],$meta);
	}
	public static function frontend_seajs_alias($alias){
		if(self::is_page()){
			$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		}
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!self::is_page()) 
			return false;
		?>
		seajs.use('<?= self::$iden;?>',function(m){
			m.config.process_url = '<?= theme_features::get_process_url(array('action' => self::$iden));?>';
			m.init();
		});
		<?php
	}
	public static function frontend_css(){
		if(!self::is_page()) 
			return false;
			
		wp_enqueue_style(
			self::$iden,
			theme_features::get_theme_includes_css(__DIR__),
			'frontend',
			theme_file_timestamp::get_timestamp()
		);
	}
}