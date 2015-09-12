<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_point_lottery::init';
	return $fns;
});
class theme_point_lottery{

	public static $page_slug = 'account';
	public static $user_meta_key = [
		'redeem' => 'lottery_redeems',
	];
	
	public static function init(){
		add_action('wp_enqueue_scripts', 	__CLASS__ . '::frontend_css');
		
		foreach(self::get_tabs() as $k => $v){
			$nav_fn = 'filter_nav_' . $k; 
			add_filter('account_navs',__CLASS__ . "::$nav_fn",$v['filter_priority']);
		}

		add_filter('wp_title', __CLASS__ . '::wp_title',10,2);

		
		add_action('wp_ajax_' . __CLASS__, __CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . __CLASS__, __CLASS__ . '::process');
		
		add_filter('frontend_seajs_alias', __CLASS__ . '::frontend_seajs_alias');
		add_action('frontend_seajs_use', __CLASS__ . '::frontend_seajs_use');

		add_action('page_settings' , __CLASS__ . '::display_backend');
		
		add_filter('after_backend_tab_init',__CLASS__ . '::after_backend_tab_init');
		add_filter('backend_seajs_alias',__CLASS__ . '::backend_seajs_alias');
		add_action('backend_css', __CLASS__ . '::backend_css'); 
		/**
		 * list history
		 */

		add_filter('theme_options_default', __CLASS__ . '::options_default');
		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_action('list_point_histroy', __CLASS__ . '::list_history');

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
	public static function filter_nav_lottery($navs){
		$navs['lottery'] = '<a href="' . self::get_tabs('lottery')['url'] . '">
			<i class="fa fa-' . self::get_tabs('lottery')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('lottery')['text'] . '
		</a>';
		return $navs;
	}
	private static function get_box_tpl($placeholder){
		$boxes = self::get_options('boxes');
		if(isset($boxes[$placeholder])){
			$boxes = $boxes[$placeholder];
		}else{
			$boxes = [];
		}
		
		$name = isset($boxes['name']) ? $boxes['name'] : null;
		
		$consume = isset($boxes['consume']) ? (int)$boxes['consume'] : 0;
		
		$award = isset($boxes['award']) ? (int)$boxes['award'] : 0;

		$percent = isset($boxes['percent']) ? (int)$boxes['percent'] : 0;

		$remaining = isset($boxes['remaining']) ? (int)$boxes['remaining'] : 0;

		$des = isset($boxes['des']) ? stripslashes($boxes['des']) : null;

		$success = isset($boxes['success']) ? stripslashes($boxes['success']) : ___('Congratulations! You got a prize.');
		
		$fail = isset($boxes['fail']) ? stripslashes($boxes['fail']) : ___('Not lucky, you are not able to get a prize. Try again?');

		$fixed_user_id = isset($boxes['fixed-user-id']) ? (int)$boxes['fixed-user-id'] : 0;
		
		ob_start();
		?>
		<table 
			class="form-table <?= __CLASS__;?>-item" 
			id="<?= __CLASS__;?>-item-<?= $placeholder;?>" 
			data-placeholder="<?= $placeholder;?>" 
		>
		<tbody>
		<tr>
			<th><label for="<?= __CLASS__;?>-name-<?= $placeholder;?>"><?= sprintf(___('Lottery-box name - %s'),$placeholder);?></label></th>
			<td><input type="text" id="<?= __CLASS__;?>-name-<?= $placeholder;?>" name="<?= __CLASS__;?>[boxes][<?= $placeholder;?>][name]" class="widefat" placeholder="<?= ___('Lottery item name');?>" title="<?= ___('Lottery item name');?>" value="<?= $name;?>"/></td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-consume-<?= $placeholder;?>"><?= ___('Consume point');?></label></th>
			<td><input type="number" id="<?= __CLASS__;?>-consume-<?= $placeholder;?>" name="<?= __CLASS__;?>[boxes][<?= $placeholder;?>][consume]" class="short-number" placeholder="<?= ___('Consume points');?>" title="<?= ___('Consume points');?>" value="<?= $consume;?>" step="1"/></td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-award-<?= $placeholder;?>"><?= ___('award point');?></label></th>
			<td><input type="number" id="<?= __CLASS__;?>-award-<?= $placeholder;?>" name="<?= __CLASS__;?>[boxes][<?= $placeholder;?>][award]" class="short-number" placeholder="<?= ___('Award points');?>" value="<?= $award;?>" step="1" placeholder="<?= ___('Award points');?>"/></td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-percent-<?= $placeholder;?>"><?= ___('Award percent');?></label></th>
			<td><input type="number" id="<?= __CLASS__;?>-percent-<?= $placeholder;?>" name="<?= __CLASS__;?>[boxes][<?= $placeholder;?>][percent]" class="short-number" placeholder="<?= ___('Award percent');?>" value="<?= $percent;?>" min="0" max="100" step="1" title="<?= ___('Award percent');?>"/> %</td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-percent-<?= $placeholder;?>"><?= ___('Remaining');?></label></th>
			<td><input type="number" id="<?= __CLASS__;?>-remaining-<?= $placeholder;?>" name="<?= __CLASS__;?>[boxes][<?= $placeholder;?>][remaining]" class="short-number" placeholder="<?= ___('Remaining number');?>" value="<?= $remaining;?>" min="0" step="1" title="<?= ___('Remaining number');?>" /></td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-fixed-user-id-<?= $placeholder;?>"><?= ___('Fixed award user ID');?></label></th>
			<td>
				<input type="number" id="<?= __CLASS__;?>-fixed-user-id-<?= $placeholder;?>" name="<?= __CLASS__;?>[boxes][<?= $placeholder;?>][fixed-user-id]" class="short-number" placeholder="<?= ___('Fixed user ID');?>" value="<?= $fixed_user_id;?>" min="0" step="1" title="<?= ___('Fixed user ID');?>" /> 
				<span class="description"><?= ___('If fill a user, the award percent will be 100% and remaining will be 1.');?></span>
			</td>
		</tr>
		<tr>
			<th>
				<?= ___('Type');?>
				<td>
					<?php self::the_type_select($placeholder);?>
				</td>
			</th>
		</tr>
		<tr>
			<th>
				<label for="<?= __CLASS__;?>-des-<?= $placeholder;?>"><?= ___('Description');?></label>
				<td>
					<textarea name="<?= __CLASS__;?>[boxes][<?= $placeholder;?>][des]" id="<?= __CLASS__;?>-des-<?= $placeholder;?>" rows="3" class="widefat" placeholder="<?= ___('About the lottery item description.');?>"><?= $des;?></textarea>
				</td>
			</th>
		</tr>
		<tr>
			<th>
				<label for="<?= __CLASS__;?>-success-<?= $placeholder;?>"><?= ___('Success description');?></label>
				<td>
					<input  type="text" name="<?= __CLASS__;?>[boxes][<?= $placeholder;?>][success]" id="<?= __CLASS__;?>-success-<?= $placeholder;?>" rows="3" class="widefat" value="<?= $success;?>" placeholder="<?= ___('When user win the lottery game message.');?>">
				</td>
			</th>
		</tr>
		<tr>
			<th>
				<label for="<?= __CLASS__;?>-fail-<?= $placeholder;?>"><?= ___('Fail description');?></label>
				<td>
					<input type="text" name="<?= __CLASS__;?>[boxes][<?= $placeholder;?>][fail]" id="<?= __CLASS__;?>-fail-<?= $placeholder;?>" rows="3" class="widefat" value="<?= $fail;?>" placeholder="<?= ___('When user lose the lottery game message.');?>">
				</td>
			</th>
		</tr>
		<tr>
			<th><?= ___('Box control');?></th>
			<td>
				<a href="javascript:;" data-target="<?= __CLASS__;?>-item-<?= $placeholder;?>" class="delete"><?= ___('Delete this item');?></a>
			</td>
		</tr>
		</tbody>
		</table>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	private static function the_type_select($placeholder){
		$type = self::get_box($placeholder,'type');
		$types = [
			'point' => ___('Point'),
			'redeem' => ___('Redeem'),
		];
		?>
		<select name="<?= __CLASS__;?>[boxes][<?= $placeholder;?>][type]" class="widefat">
			<?php foreach($types as $k => $v){ ?>
				<option value="<?= $k;?>" <?= $type === $k ? 'selected' : null;?> ><?= $v;?></option>
			<?php } ?>
		</select>
		<?php
	}
	public static function display_backend(){
		?>
		<fieldset>
			<legend><?= ___('Lottery settings');?></legend>
			<p class="description"><?= ___('You can add/edit lottery items, here are some keywords for replace.');?></p>
			
			<input type="text" class="text-select" value="%redeem%" title="<?= ___('Redeem code');?>" readonly > 
			<input type="text" class="text-select" value="%award%" title="<?= ___('Award points');?>" readonly > 
			<input type="text" class="text-select" value="%consume%" title="<?= ___('Consume points');?>"readonly > 
			
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="<?= __CLASS__;?>-des"><?= ___('Description');?></label></th>
						<td>
							<textarea name="<?= __CLASS__;?>[des]" id="<?= __CLASS__;?>-des" cols="50" rows="5" class="widefat"><?= self::get_des();?></textarea>
						</td>
					</tr>
					<tr>
						<th><label for="<?= __CLASS__;?>-max-times"><?= ___('Max times daily');?></label></th>
						<td>
							<input type="number" name="<?= __CLASS__;?>[max-times]" id="<?= __CLASS__;?>-max-times" class="short-number" value="<?= self::get_max_times();?>" step="1" min="1">
						</td>
					</tr>
				</tbody>
			</table>
			<hr>
			<?php
			$boxes = self::get_options('boxes');
			if(empty($boxes)){
				echo self::get_box_tpl(0);
			}else{
				foreach($boxes as $k => $box){
					echo self::get_box_tpl($k);
				}
			}
			?>
			<table class="form-table" id="<?= __CLASS__;?>-control">
			<tbody>
			<tr>
			<th><?= ___('Control');?></th>
			<td>
				<a id="<?= __CLASS__;?>-add" href="javascript:;" class="button-primary"><?= ___('Add a new item');?></a>
			</td>
			</tr>
			</tbody>
			</table>
			<hr>
			<table class="form-table">
				<tr>
					<th><?= ___('Check redeem code');?></th>
					<td>
						<div id="<?= __CLASS__;?>-tip" class="page-tip"></div>
						<div id="<?= __CLASS__;?>-btns">
							<input type="number" id="<?= __CLASS__;?>-redeem-user-id" placeholder="<?= ___('User ID');?>" class="short_number"> 
							<input type="number" id="<?= __CLASS__;?>-redeem-code" placeholder="<?= ___('Redeem code');?>" class="short_number"> 
							
							<a href="javascript:;" class="button button-primary" id="<?= __CLASS__;?>-check-redeem"><?= ___('Check and set redeem code');?></a>
						</div>
					</td>
				</tr>
			</table>
		</fieldset>
		<?php
	}
	public static function get_options($key = null){
		static $cache = null;
		if($cache === null)
			$cache = theme_options::get_options(__CLASS__);
		if($key)
			return isset($cache[$key]) ? $cache[$key] : false;
		return $cache;
	}
	public static function get_url(){
		static $cache = null;
		if($cache === null){
			$cache = theme_cache::get_permalink(theme_cache::get_page_by_path(self::$page_slug)->ID);
		}
		return $cache;
	}
	public static function get_tabs($key = null){
		$baseurl = self::get_url();

		$tabs = array(
			'lottery' => array(
				'text' => ___('Lottery game'),
				'icon' => 'yelp',
				'url' => esc_url(add_query_arg('tab','lottery',$baseurl)),
				'filter_priority' => 70,
			),
		);
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
	public static function get_box($box_id = null,$key = null){
		$boxes = self::get_options('boxes');
		if($box_id === null)
			return $boxes;
		if($key !== null)
			return isset($boxes[$box_id][$key]) ? $boxes[$box_id][$key] : false;
		return isset($boxes[$box_id]) ? $boxes[$box_id] : false;
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'boxes' => [
				1 => [
					'name' => ___('Lottery 100'),
					'consume' => 100,
					'award' => 200,
					'percent' => 50,
					'remaining' => 9999,
					'type' => 'point',
					'des' => ___('You can consume 100 points to get 200 points if you win.'),
					'success' => ___('Congratulations! You won %award% points.'),
					'fail' => ___('Not lucky, you lost the game and consume %consume% points. Try again?'),
				],
				2 => [
					'name' => ___('Lottery 200'),
					'consume' => 200,
					'award' => 400,
					'percent' => 50,
					'remaining' => 9999,
					'type' => 'point',
					'des' => ___('You can consume 200 points to get 400 points if you win.'),
					'success' => ___('Congratulations! You won %award% points.'),
					'fail' => ___('Not lucky, you lost the game and consume %consume% points. Try again?'),
				],
				3 => [
					'name' => ___('Keychain'),
					'consume' => 1000,
					'award' => 0,
					'percent' => 10,
					'remaining' => 10,
					'type' => 'redeem',
					'des' => ___('You can consume 1000 points to get a keychain if you win.'),
					'success' => ___('Congratulations! You win a keychain, the redeem code is %redeem%. Please contact administrator to get your award.'),
					'fail' => ___('Not lucky, you lost the game and get nothing. Try again?'),
				],
			],
		
			'des' => self::get_des_default(),
			'max-times' => 5,
		];
		return $opts;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}
		return $opts;
	}
	public static function get_des_default(){
		return 
'<div class="well">
	<p>' . ___('Welcome to lottery game. You can consume your points to get award. Please read the blew item before operation:') . '</p>
	<ol>
		<li>' . ___('List item 1 description.') . '</li>
		<li>' . ___('List item 2 description.') . '</li>
	</ol>
</div>';
	}
	public static function get_des(){
		$des = self::get_options('des');
		if(trim($des) === '')
			$des = self::get_des_default();
		return stripslashes($des);
	}
	public static function check_login(){
		if(!theme_cache::get_current_user_id())
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'need_login',
				'msg' => sprintf(
					___('Sorry, please %s.'),
					'<a href="' . esc_url(add_query_arg('redirect',self::get_tabs('lottery')['url'])) . '">' . ___('log-in') . '</a>'
				),
			]));
		return theme_cache::get_current_user_id();
	}
	private static function get_times_id(){
		return md5(theme_cache::get_current_user_id() . current_time('Ymd'));
	}
	public static function get_max_times(){
		$times = (int)self::get_options('max-times');
		return $times === 0 ? 5 : $times;
	}
	private static function get_times(){
		return (int)wp_cache_get(self::get_times_id());
	}
	private static function set_times($times){
		wp_cache_set(self::get_times_id(),(int)$times,null,3600*24);
	}
	private static function is_max_times(){
		return self::get_max_times() <= self::get_times();
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
		$output = [];
		
		$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;

		$current_user_id = theme_cache::get_current_user_id();
		
		switch($type){
			/**
			 * start
			 */
			case 'start':
				theme_features::check_nonce();
				/** item */
				$item_id = isset($_REQUEST['id']) && is_string($_REQUEST['id']) ? $_REQUEST['id'] : false;
				if($item_id === false){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'empty_item',
						'msg' => ___('Sorry, the lottery item is empty.'),
					]));
				}
				/** check max times */
				self::check_max_times();
				
				/** lottery box */
				$box = self::get_box($item_id);

				/** check item */
				if(!$box){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'invaild_item',
						'msg' => ___('Sorry, the lottery item is invaild.'),
					]));
				}

				/** check current user points */
				$current_user_points = theme_custom_point::get_point($current_user_id);
				if($current_user_points < $box['consume']){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'not_enough_points',
						'msg' => ___('Sorry, your points is not enough to make a lottery.'),
					]));
				}

				/** check remaining time */
				if($box['remaining'] == 0){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'not_enough_remaining',
						'msg' => ___('Sorry, the lottery item remaining is empty.'),
					]));
				}

				/** check max times */
				if(self::is_max_times()){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'reach_max_times',
						'msg' => ___('Sorry, you reached max lottery times today, see you tomorrow.'),
					]));
				}

				/** check fixed user id */
				if($box['type'] === 'redeem' && $box['fixed-user-id'] == $current_user_id){
					$win = true;
				}else{
					$win = self::get_win_status($item_id,$current_user_id);
				}
				

				/** add history */
				$history_data = $box;
				$history_data['lottery-type'] = $box['type'];
				$history_data['win'] = $win;
				$history_data['points'] = (int)$box['award'] - (int)$box['consume'];
				$history_data['name'] = $box['name'];

				$history_data = self::add_history($current_user_id,$history_data);
				
				/** output win */
				if($win === true){
					$output['msg'] = $box['success'];
					/** win point */
					if($box['type'] === 'point'){
						$user_new_points = theme_custom_point::get_point($current_user_id) + $box['award'];
					/** win redeem */
					}else{
						/** add to redeem meta */
						self::add_user_redeem($current_user_id,[
							'id' => $history_data['redeem'],
							'name' => $box['name'],
							'des' => $box['des'],
						]);
						/** replace placeholder */
						$output['msg'] = str_replace('%redeem%',$history_data['redeem'],$output['msg']);
						$user_new_points = theme_custom_point::get_point($current_user_id) - $box['consume'];
					}
				}else{
					$output['msg'] = $box['fail'];
					$user_new_points = theme_custom_point::get_point($current_user_id) - $box['consume'];
				}
				/** replace placeholder for points */
				$output['msg'] = str_replace('%award%',$box['award'],$output['msg']);
				$output['msg'] = str_replace('%consume%',$box['consume'],$output['msg']);
				
				/** update user point */
				theme_custom_point::update_user_points($current_user_id,$user_new_points);
				
				$output['win'] = $win;
				$output['new-points'] = theme_custom_point::get_point($current_user_id,true);
				$output['status'] = $win ? 'success' : 'warning';

				
				/** update remaining */
				$output['new-remaining'] = (int)self::reduce_remaining($item_id);
				
				/** check the type is point */
				if($box['type'] === 'redeem'){
					$output['redeem'] = $redeem;
				}
				die(theme_features::json_format($output));

			/**
			 * change status for redeem
			 */
			case 'check-redeem':
				/** check permission */
				if(!theme_cache::current_user_can('manage_options'))
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'invaild_permission',
						'msg' => ___('Sorry, your permission is invaild.'),
					]));
					
				/** check user */
				$user_id = isset($_REQUEST['user-id']) && is_numeric($_REQUEST['user-id']) ? $_REQUEST['user-id'] : false;
				$user = get_user_by('id',$user_id);
				if(!$user)
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'invaild_user',
						'msg' => ___('Sorry, the user is invaild.'),
					]));
				/** check redeem */
				$redeem_id = isset($_REQUEST['redeem']) && is_string($_REQUEST['redeem']) ? $_REQUEST['redeem'] : false;
				if(!$redeem_id)
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'invaild_redeem',
						'msg' => ___('Sorry, the redeem is invaild.'),
					]));
				/** get redeem metas */
				$redeems = self::get_user_redeem_codes($user_id);
				if(!isset($redeems[$redeem_id]))
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'not_exist_redeem',
						'msg' => ___('Sorry, the redeem does not exist.'),
					]));

				/** redeemed */
				if(isset($redeems[$redeem_id]['redeemed']))
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'already_redeemed',
						'msg' => ___('Sorry, the redeem code has been redeemed.'),
					]));

				/** update */
				self::update_user_redeem($user_id,$redeem_id);
				
				die(theme_features::json_format([
					'status' => 'success',
					'msg' => ___('Redeem code status has been updated to redeemed.'),
				]));
			default:
				die(theme_features::json_format([
					'status' => 'error',
					'code' => 'invaild_type_param',
					'msg' => ___('Sorry, type param is invaild.'),
				]));
		}
	}
	private static function reduce_remaining($item_id){
		$box = self::get_box($item_id);
		if(!$box)
			return false;
		$opts = self::get_options();
		--$opts['boxes'][$item_id]['remaining'];
		theme_options::set_options(__CLASS__,$opts);
		return $opts['boxes'][$item_id]['remaining'];
	}
	private static function get_win_status($item_id,$user_id){
		$percent = (int)self::get_box($item_id,'percent');
		$consume = (int)self::get_box($item_id,'consume');
		$user_points = theme_custom_point::get_point($user_id);
		
		if(!$percent)
			return false;

		$user_point_percent = $user_points * 0.0001;
		if($user_point_percent > 5)
			$user_point_percent = 5;

		$consume_percent = $consume * 0.001;
		if($consume_percent > 5)
			$consume_percent = 5;
			
		$rand = mt_rand(1,100) + $user_point_percent + $consume_percent;
		
		return $percent >= $rand;
	}
	private static function get_timestamp(){
		static $cache = null;
		if($cache === null)
			$cache = current_time('timestamp');
		return $cache;
	}
	public static function add_history($user_id,array $data = []){
		$meta = [
			'type'=> 'lottery',
			'timestamp' => self::get_timestamp(),
			'win' => (bool)$data['win'],
			'consume' => (int)$data['consume'],
			'lottery-type' => $data['type'],
			'points' => (int)$data['points'],
			'name' => $data['name'],
		];

		if($data['lottery-type'] === 'redeem'){
			$meta['redeem'] = abs(crc32(json_encode($meta)));
		}
		add_user_meta($user_id,theme_custom_point::$user_meta_key['history'],$meta);
		
		return $meta;
	}
	private static function get_user_meta($user_id,$key){
		static $caches = [];
		$cache_id = $user_id . $key;
		if(!isset($caches[$cache_id]))
			$caches[$cache_id] = get_user_meta($user_id,$key,true);
		return $caches[$cache_id];
	}
	public static function add_user_redeem($user_id,array $data){
		$data = array_merge([
			'id' => null,
			'name' => null,
			'des' => null,
			'timestamp' => self::get_timestamp(),
		],$data);
		$codes = self::get_user_redeem_codes($user_id);
		if(isset($codes[$data['id']]))
			return false;
		$codes[$data['id']] = $data;
		update_user_meta($user_id,self::$user_meta_key['redeem'],$codes);
	}
	public static function update_user_redeem($user_id,$code_key){
		$codes = self::get_user_redeem_codes($user_id);
		if(!isset($codes[$code_key]))
			return false;
		$codes[$code_key]['redeemed'] = self::get_timestamp();
		update_user_meta($user_id,self::$user_meta_key['redeem'],$codes);
	}
	
	public static function get_user_redeem_codes($user_id,$code_key = null){
		$codes = self::get_user_meta($user_id,self::$user_meta_key['redeem']);
		if($code_key)
			return isset($codes[$code_key]) ? $codes[$code_key] : false;
		return $codes;
	}
	public static function is_redeemed($user_id,$code_key){
		$code = self::get_user_redeem_codes($user_id,$code_key);
		if(!$code)
			return false;
		return isset($code['redeemed']);
	}
	/**
	 * list history
	 */
	public static function list_history($history){
		if($history['type'] !== 'lottery')
			return false;
			//var_dump($history);
		?>
		<li class="list-group-item <?= $history['win'] && $history['lottery-type'] === 'redeem' ? 'list-group-item-success' : null;?>">
			<?php theme_custom_point::the_list_icon('yelp');?>
			<?php theme_custom_point::the_point_sign($history['points']);?>
			<span class="history-text">
				<a href="<?= self::get_tabs('lottery')['url'];?>" class="label label-default"><?= ___('Lottery game');?></a>
				<?php
				if($history['win']){
					/** type is redeem */
					if($history['lottery-type'] === 'redeem'){
						echo sprintf(
							___('You won %1$s . Here is redeem code: %2$s.'),

							$history['name'],

							'<strong class="label label-success">' . $history['redeem'] . '</strong>'
						);
					/** type is point */
					}else{
						echo sprintf(
							___('You won %s.'),

							$history['name']
							
						);
					}
				}else{
					echo sprintf(
						___('You lost the lottery game %s.'),
						
						$history['name']
					);
				}
				?>
			</span>
			
			<?php theme_custom_point::the_time($history);?>
		</li>
		<?php
	}
	public static function frontend_seajs_alias($alias){
		if(self::is_page()){
			$alias[__CLASS__] = theme_features::get_theme_includes_js(__DIR__);
		}
		return $alias;
	}
	public static function after_backend_tab_init(){
		?>
		seajs.use('<?= __CLASS__;?>',function(_m){
			_m.config.process_url = '<?= theme_features::get_process_url(array('action' => __CLASS__));?>';
			_m.config.tpl = <?= json_encode(self::get_box_tpl('%placeholder%'));?>;
			_m.config.lang.M01 = '<?= ___('Results coming soon...');?>';
			_m.config.lang.E01 = '<?= ___('Sorry, server is busy now, can not respond your request, please try again later.');?>';
			_m.init();
		});
		<?php
	
	}
	public static function backend_seajs_alias(array $alias = []){
		$alias[__CLASS__] = theme_features::get_theme_includes_js(__DIR__,'backend.js');
		return $alias;
	}
	public static function backend_css(){
		?>
		<link href="<?= theme_features::get_theme_includes_css(__DIR__,'backend',true,true);?>" rel="stylesheet"  media="all"/>
		<?php
	}
	public static function frontend_seajs_use(){
		if(!self::is_page()) 
			return false;
		?>
		seajs.use('<?= __CLASS__;?>',function(m){
			m.config.process_url = '<?= theme_features::get_process_url(array('action' => __CLASS__));?>';
			m.config.lang.M01 = '<?= ___('Results coming soon...');?>';
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