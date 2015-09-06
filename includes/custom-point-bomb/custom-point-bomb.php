<?php
/**
 * @version 1.0.1
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_point_bomb::init';
	return $fns;
});
class theme_custom_point_bomb{

	public static $page_slug = 'account';
	
	public static function init(){
		add_action('wp_enqueue_scripts', __CLASS__ . '::frontend_css');
		
		foreach(self::get_tabs() as $k => $v){
			$nav_fn = 'filter_nav_' . $k; 
			add_filter('account_navs', __CLASS__ . "::$nav_fn",$v['filter_priority']);
		}

		add_filter('wp_title', __CLASS__ . '::wp_title',10,2);

		
		add_action('wp_ajax_' . __CLASS__, __CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . __CLASS__, __CLASS__ . '::process');
		
		add_filter('frontend_seajs_alias', __CLASS__ . '::frontend_seajs_alias');
		add_action('frontend_seajs_use', __CLASS__ . '::frontend_seajs_use');

		add_filter('custom_point_value_default', __CLASS__ . '::filter_custom_point_value_default');

		add_filter('custom_point_types',__CLASS__ . '::filter_custom_point_types');

		/**
		 * list history
		 */
		foreach([
			'list_history_for_attacker',
			'list_history_for_target'
		] as $v)
			add_action('list_point_histroy',__CLASS__ . '::' . $v);

		/**
		 * noti event
		 */
		foreach([
			'list_noti_be_bomb'
		] as $v)
			add_action('list_noti',__CLASS__ . '::' . $v);
	}
	public static function wp_title($title, $sep){
		if(!self::is_page()) 
			return $title;
			
		if(self::get_tabs(get_query_var('tab'))){
			$title = self::get_tabs(get_query_var('tab'))['text'];
		}
		return $title . $sep . theme_cache::get_bloginfo('name');
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::is_page(self::$page_slug) && self::get_tabs(get_query_var('tab'));
			
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
			$cache = esc_url(theme_cache::get_permalink(theme_cache::get_page_by_path(self::$page_slug)->ID));
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
				'text' => ___('Bomb world'),
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
			'type' => 'number',
			'des' => ___('User bombs other user points victory percentage. The unit is the percentage.'),
		];
		$types['bomb'] = [
			'text' => ___('When user bomb points'),
			'type' => 'text',
			'des' => ___('Use commas to separate multiple point, first as the default.'),
		];
		$types['bomb-times'] = [
			'text' => ___('User daily bomb max-times'),
			'type' => 'number',
			'des' => ___('The maximum number of attacks per user daily.'),
		];
		return $types;
	}
	public static function filter_custom_point_value_default(array $opts = []){
		$opts['bomb-percent'] = self::get_victory_percent_default();
		$opts['bomb'] = self::get_points_default(true);
		$opts['bomb-times'] = self::get_max_times_default();
		return $opts;
	}
	public static function get_max_times_default(){
		return 5;
	}
	public static function get_victory_percent_default(){
		return 30;
	}
	public static function get_points_default($text = false){
		return $text === true ? '5,10,50,100' : [10,20,50,100];
	}
	public static function get_point_values(){
		static $cache = null;

		if($cache !== null)
			return $cache;
			
		$values = explode(',',theme_custom_point::get_point_value('bomb'));

		if(!is_null_array($values)){
			$cache = array_map(function($v){
				$v = trim($v);
				if(is_numeric($v))
					return $v;
			},$values);
		}else{
			$cache = self::get_points_default();
		}
		return $cache;
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = theme_options::get_options(__CLASS__);

		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;

		return $caches;
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__]['des'] = self::get_des_default();
		return $opts;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
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
						<textarea name="<?= __CLASS__;?>[des]" id="<?= __CLASS__;?>-des" cols="50" rows="3" class="widefat"><?= self::get_des();?></textarea>
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
		if(!$percent)
			$percent = self::get_victory_percent_default();
			
		if($percent > 100)
			$percent = 100;
		
		if($percent < 0)
			$percent = 0;
			
		return $percent;
	}
	public static function check_login(){
		if(!theme_cache::get_current_user_id())
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'need_login',
				'msg' => sprintf(
					___('Sorry, please %s.'),
					'<a href="' . esc_url(add_query_arg('redirect',self::get_tabs('bomb')['url'])) . '">' . ___('log-in') . '</a>'
				),
			]));
		return theme_cache::get_current_user_id();
	}
	public static function check_target( &$target_id ){
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
		if(class_exists('number_user_nicename'))
			$target_id -= number_user_nicename::$prefix_number;
		
		$target = get_user_by('id',$target_id);
		if(!$target){
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'target_user_not_exist',
				'msg' => ___('Sorry, the target user does not exist.'),
			]));
		}

		/**
		 * check target is myself
		 */
		if(theme_cache::get_current_user_id() == $target_id){
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'target_is_myself',
				'msg' => ___('Sorry, you can not attack yourself.'),
			]));
		}
		return $target;
	}
	private static function get_times_group_id(){
		static $cache = null;
		if($cache === null)
			$cache = __CLASS__ . theme_cache::get_current_user_id() . current_time('Ymd');

		return $cache;
	}
	public static function get_max_times(){
		$times = (int)theme_custom_point::get_point_value('bomb-times');
		return $times === 0 ? self::get_max_times_default() : $times;
	}
	private static function get_times(){
		return isset($_COOKIE[self::get_times_group_id()]) ? (int)$_COOKIE[self::get_times_group_id()] : 0;
	}
	private static function set_times($times){
		setcookie(self::get_times_group_id(),$times,time()+3600*24);
	}
	private static function is_max_times(){
		if(self::get_max_times() <= self::get_times())
			return true;
		return false;
	}
	private static function check_max_times(){
		if(self::is_max_times()){
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'reach_max_times',
				'msg' => ___('Sorry, you have reached the maximun times today, see you tomorrow.'),
			]));
		}
	}
	public static function process(){
		theme_features::check_referer();
		theme_features::check_nonce();
		$output = [];
		
		$type = isset($_REQUEST['type']) && is_string($_REQUEST['type']) ? $_REQUEST['type'] : null;

		$target_id = isset($_REQUEST['target']) && is_numeric($_REQUEST['target']) ? $_REQUEST['target'] : null;
		
		switch($type){
			case 'get-target':
				/**
				 * check login
				 */
				$current_user_id = self::check_login();
				
				/**
				 * check times
				 */
				self::check_max_times();
				
				/**
				 * get target
				 */
				$target = self::check_target($target_id);
				
				$output = [
					'status' => 'success',
					'points' => theme_custom_point::get_point($target_id),
					'avatar' => str_replace('&#038;','&',get_avatar_url($target_id)),
					'name' => esc_html($target->display_name),
					'msg' => ___('Target locked, bomb is ready.'),
				];

				die(theme_features::json_format($output));
			/**
			 * bomb
			 */
			case 'bomb':
				/**
				 * check login
				 */
				$current_user_id = self::check_login();

				/**
				 * check times
				 */
				self::check_max_times();
				
				/**
				 * get target
				 */
				$target = self::check_target($target_id);
				/**
				 * check points
				 */
				$points = isset($_REQUEST['points']) && is_numeric($_REQUEST['points']) ? $_REQUEST['points'] : null;
				if(!$points || !in_array($points,self::get_point_values())){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'invaild_point_value',
						'msg' => ___('Sorry, the point value is invaild.'),
						'points' => self::get_point_values(),
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
						'msg' => sprintf(___('Sorry, the target %s is not enough to bear your bomb.'),theme_custom_point::get_point_name()),
					]));
				}
				/**
				 * check attacker points
				 */
				$attacker_id = theme_cache::get_current_user_id();
				$attacker_points = theme_custom_point::get_point($attacker_id);
				if($points > $attacker_points){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'attacker_points_not_enough',
						'msg' => sprintf(___('Sorry, your %s is not enough to bomb target.'),theme_custom_point::get_point_name()),
					]));
				}
				/**
				 * pass 
				 */
				$says = isset($_REQUEST['says']) && is_string($_REQUEST['says']) ? mb_substr($_REQUEST['says'],0,30) : false;



				/**
				 * define $hit
				 */
				$hit = false;
				if(mt_rand(0,100) <= self::get_victory_percent())
					$hit = true;

				/**
				 * define data
				 */
				$data = [
					'attacker-id' => $current_user_id,
					'target-id' => $target_id,
					'says' => $says,
					'points' => $points,
					'hit' => $hit,
				];
				/** add history for target */
				self::add_history_for_target($data);

				/** add history for attacker */
				self::add_history_for_attacker($data);

				//self::add_noti_for_target($current_user_id,$target_id,$points,$hit);
				/**
				 * new target points
				 */
				$target_extra_points = self::get_extra_points_for_target($hit,$points);
				$new_target_points = $target_points + $target_extra_points;
				
				/**
				 * new attacker points
				 */
				$attacker_extra_points = self::get_extra_points_for_attacker($hit,$points);
				$new_attacker_points = $attacker_points + $attacker_extra_points;
				
				/** update attacker points */
				theme_custom_point::update_user_points($attacker_id, $new_attacker_points);
				
				/** update target points */
				theme_custom_point::update_user_points($target_id, $new_target_points);

				$target_name = '<a href="' . esc_url(theme_cache::get_author_posts_url($target_id)) . '" target="_blank" class="author">' . esc_html($target->display_name) . '</a>';
							
				/**
				 * hit target
				 */
				if( $hit ){
					
					$output['msg'] = sprintf(
						___('Bombing successfully! Your bomb hit %1$s, you got %2$s %3$s. Target remaining %4$s %3$s.'),

						$target_name,
						
						'<strong class="plus">+' . $attacker_extra_points . '</strong>',/** %2$s */
						
						theme_custom_point::get_point_name(),/** %3$s */
						
						$new_target_points /** %4$s */
					);
					
				/**
				 * miss target
				 */
				}else{
					$output['msg'] = sprintf(
						___('Unlucky! %1$s miss your attack, you lost %2$s %3$s and remaining %4$s %3$s.'),
						
						$target_name,
						
						'<strong class="mins">' . $attacker_extra_points . '</strong>',/** %2$s */
						
						theme_custom_point::get_point_name(),/** %3$s */
						
						$new_attacker_points /** %4$s */
						
					);
				}
				$output['hit'] = $hit;
				$output['status'] = 'success';
				/**
				 * set times
				 */
				self::set_times(self::get_times() + 1);
				
				die(theme_features::json_format($output));
						
			default:
				die(theme_features::json_format([
					'status' => 'error',
					'code' => 'invaild_type_param',
					'msg' => ___('Sorry, type param is invaild.'),
				]));
		}
	}
	private static function get_timestamp(){
		static $cache = null;
		if($cache === null)
			$cache = current_time('timestamp');
		return $cache;
	}
	private static function get_extra_points_for_target($hit,$points){
		return $hit ? 0 - $points : round($points / 2);
	}
	private static function get_extra_points_for_attacker($hit,$points){
		return $hit ? $points : 0 - $points;
	}
	public static function add_history_for_target(array $data = []){

		$meta = [
			'type'=> 'be-bomb',
			'timestamp' => self::get_timestamp(),
			'hit' => $data['hit'],
			'attacker-id' => $data['attacker-id'],
			'points' => self::get_extra_points_for_target($data['hit'],$data['points']),
		];
		if(isset($data['says']) && $data['says'] !== '')
			$meta['says'] = $data['says'];
			
		add_user_meta($data['target-id'],theme_custom_point::$user_meta_key['history'],$meta);
		
	}
	public static function add_history_for_attacker(array $data = []){

		$meta = [
			'type'=> 'bomb',
			'timestamp' => self::get_timestamp(),
			'hit' => $data['hit'],
			'target-id' => $data['target-id'],
			'points' => self::get_extra_points_for_attacker($data['hit'],$data['points']),
		];
		if(isset($data['says']) && $data['says'] !== '')
			$meta['says'] = $data['says'];
		
		add_user_meta($data['attacker-id'],theme_custom_point::$user_meta_key['history'],$meta);
		
	}
	public static function add_noti_for_target(array $data = []){
		$meta = [
			'id' 		=> self::get_timestamp(),
			'type'		=> 'be-bomb',
			'timestamp' => self::get_timestamp(),
			'hit' 		=> $data['hit'],
			'attacker-id' => $data['attacker-id'],
			'points' 	=> self::get_extra_points_for_target($data['hit'],$data['points']),
		];
		if(isset($data['says']) && $data['says'] !== '')
			$meta['says'] = $data['says'];
			
		add_user_meta($data['target-id'],theme_notification::$user_meta_key['key'],$meta);
	}
	public static function add_noti_for_attacker(array $data = []){
		$meta = [
			'id' 		=> self::get_timestamp(true),
			'type'		=> 'bomb',
			'timestamp' => self::get_timestamp(),
			'hit' 		=> $data['hit'],
			'target-id' => $data['target-id'],
			'points' 	=> self::get_extra_points_for_attacker($data['hit'],$data['points']),
		];
		if(isset($data['says']) && $data['says'] !== '')
			$meta['says'] = $data['says'];
		
		add_user_meta($data['target-id'],theme_notification::$user_meta_key['key'],$meta);
	}
	/**
	 * list history for attacker
	 */
	public static function list_history_for_attacker($history){
		if($history['type'] !== 'bomb')
			return false;

		$target_name = theme_cache::get_the_author_meta('display_name',$history['target-id']);

		$says = isset($history['says']) && trim($history['says']) !== '' ? esc_html($history['says']) : false;
		?>
		<li class="list-group-item">
			<?php theme_custom_point::the_list_icon('bomb');?>
			<?php theme_custom_point::the_point_sign($history['points']);?>
			<span class="history-text">

				<?php
				if($says){
					?>
					<span class="label label-primary says"><?= $says;?></span>
					<?php
				}
				if($history['hit']){
					echo sprintf(
						___('You bombed %1$s and hit! You got %2$s %3$s.'),
						
						'<a href="' . esc_url(theme_cache::get_author_posts_url($history['target-id'])) . '" target="_blank"><img src="' . get_avatar_url($history['target-id']) . '" alt="' . $target_name . '" width="16" height="16" class="avatar">' . $target_name  . '</a>',
						
						'<strong>+' . abs($history['points']) . '</strong>',
						
						theme_custom_point::get_point_name()
					);
				}else{
					echo sprintf(
						___('You bombed %1$s but miss! You lost %2$s %3$s.'),
						
						'<a href="' . esc_url(theme_cache::get_author_posts_url($history['target-id'])) . '" target="_blank"><img src="' . get_avatar_url($history['target-id']) . '" alt="' . $target_name . '" width="16" height="16" class="avatar"> ' . $target_name  . '</a>',
						
						'<strong>' . (0 - abs($history['points'])) . '</strong>',
						
						theme_custom_point::get_point_name()
					);
				}
				?>
			</span>
			
			<?php theme_custom_point::the_time($history);?>
		</li>
		<?php
	}
	public static function list_history_for_target($history){
		if($history['type'] !== 'be-bomb')
			return false;

		$attacker_name = theme_cache::get_the_author_meta('display_name',$history['attacker-id']);
		
		$attacker_name = '<a href="' . esc_url(theme_cache::get_author_posts_url($history['attacker-id'])) . '" attacker="_blank"><img src="' . get_avatar_url($history['attacker-id']) . '" alt="' . $attacker_name . '" width="16" height="16" class="avatar"> ' . $attacker_name  . '</a>';

		if(class_exists('number_user_nicename')){
			$fight_back_url = self::get_tabs('bomb',$history['attacker-id'] + number_user_nicename::$prefix_number)['url'];
		}else{
			$fight_back_url = self::get_tabs('bomb',$history['attacker-id'])['url'];
		}

		$says = isset($history['says']) && trim($history['says']) !== '' ? esc_html($history['says']) : false;
		
		?>
		<li class="list-group-item">
			
			<?php theme_custom_point::the_list_icon('bomb');?>
			<?php theme_custom_point::the_point_sign($history['points']);?>
			
			<span class="history-text">
				<?php
				if($says){
					?>
					<span class="label label-primary says"><?= $says;?></span>
					<?php
				}
				if($history['hit']){
					echo sprintf(
						___('%1$s bombed you and hit. You lost %2$s %3$s.'),
						
						$attacker_name,
						
						'<strong>' . ( 0 - abs($history['points'])) . '</strong>',
						
						theme_custom_point::get_point_name()
					);
					?>
					<a class="label label-danger" href="<?= $fight_back_url;?>" target="_blank" >
						<?= ___('It is time to fight back');?>
					</a>
					<?php
				}else{
					echo sprintf(
						___('%1$s bombed you but miss. You got %2$s %3$s.'),
						
						$attacker_name,
						
						'<strong>+' . abs($history['points']) . '</strong>',
						
						theme_custom_point::get_point_name()
					);
				}
				?>
			</span>
			
			<?php theme_custom_point::the_time($history);?>
		</li>
		<?php
	}
	/**
	 * list noti be-bomb
	 */
	public static function list_noti_be_bomb($noti){
		if($noti['type'] !== 'be-bomb')
			return false;

		if($noti['hit']){
			$points = '<strong class="label label-success">' . $noti['points'] . '</strong>';
		}else{
			$points = '<strong class="label label-danger">+' . $noti['points'] . '</strong>';
		}

		if(class_exists('number_user_nicename')){
			$fight_back_url = self::get_tabs('bomb',$noti['attacker-id'] + number_user_nicename::$prefix_number)['url'];
		}else{
			$fight_back_url = self::get_tabs('bomb',$noti['attacker-id'])['url'];
		}
		?>
		<div class="media">
			<div class="media-left">
				<a href="<?php esc_url(theme_cache::get_author_posts_url($noti['attacker-id']));?>">
				<img src="<?= get_avatar_url($noti['attacker-id']);?>" class="avatar media-object" alt="avatar" width="60" height="60">
				</a>
			</div>
			<div class="media-body">
				<h4 class="media-heading">
					<a class="label label-default" href="<?= self::get_tabs('bomb')['url'];?>"><i class="fa fa-<?= self::get_tabs('bomb')['icon'];?>"></i> <?= ___('Bomb world');?></a>
					<?= $points;?>
					
					<?php theme_custom_point::the_time($noti);?>

					<?php if($noti['hit']){ ?>
						<a class="fight-back btn btn-danger btn-xs" href="<?= $fight_back_url;?>" target="_blank"><strong><?= ___('It is time to fight back');?> <i class="fa fa-external-link"></i></strong></a>
					<?php } ?>
				</h4>
				<div class="excerpt">
					<p>
					<?php
					$attacker_name = theme_cache::get_the_author_meta('display_name',$noti['attacker-id']);

					$attacker_name = '<a href="' . esc_url(get_author_posts_url($noti['attacker-id'])) . '" target="_blank">' . $attacker_name  . '</a>';
					
					if($noti['hit']){
						echo sprintf(
							___('%1$s bombed you and hit. You lost %2$s %3$s.'),
							
							$attacker_name,
							
							'<strong>' . ( 0 - abs($noti['points'])) . '</strong>',
							
							theme_custom_point::get_point_name()
						);
					}else{
						echo sprintf(
							___('%1$s bombed you but miss! You got %2$s %3$s.'),
							
							$attacker_name,
							
							'<strong>+' . abs($noti['points']) . '</strong>',
							
							theme_custom_point::get_point_name()
						);					
					}

					?>
					
					</p>
				</div>
			</div><!-- /.media-body -->
		</div><!-- /.media -->
		<?php
	}
	public static function frontend_seajs_alias($alias){
		if(self::is_page()){
			$alias[__CLASS__] = theme_features::get_theme_includes_js(__DIR__);
		}
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!self::is_page()) 
			return false;
		?>
		seajs.use('<?= __CLASS__;?>',function(m){
			m.config.process_url = '<?= theme_features::get_process_url(array('action' => __CLASS__));?>';
			m.config.lang.M01 = '<?= ___('Target locking...');?>';
			m.config.lang.M02 = '<?= ___('Bombing, please wait...');?>';
			m.config.lang.E01 = '<?= ___('Sorry, server is busy now, can not respond your request, please try again later.');?>';
			m.init();
		});
		<?php
	}
	public static function frontend_css(){
		if(!self::is_page()) 
			return false;
			
		wp_enqueue_style(
			__CLASS__,
			theme_features::get_theme_includes_css(__DIR__),
			'frontend',
			theme_file_timestamp::get_timestamp()
		);
	}
}