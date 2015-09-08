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
		'code' => 'lottery_redeem_codes',
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
		//add_action('admin_menu', __CLASS__ . '::add_page' );
		//add_action('admin_bar_menu', __CLASS__ . '::admin_bar_menu' );
		/**
		 * list history
		 */

		add_filter('theme_options_default', __CLASS__ . '::options_default');
		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_action('list_point_histroy', __CLASS__ . '::list_history');

	}
	public static function admin_bar_menu($wp_admin_bar){
		if(!theme_cache::current_user_can('manage_options'))
			return false;
		$wp_admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id' => __CLASS__,
			'title' => ___('Redeem lists'),
			'href' => admin_url('admin.php?page=' . __CLASS__ . '-redeem-lists')
		));
		
	}
	public static function add_page(){
		if(!theme_cache::current_user_can('manage_options'))
			return false;
		add_menu_page(
			___('Redeem lists'),
			___('Redeem lists'),
			'edit_themes', 
			__CLASS__ . '-redeem-lists',
			__CLASS__ . '::display_backend_redeem_lists',
			'dashicons-groups',
			6
		);
	}
	public static function display_backend_redeem_lists(){
		?>
		<div class="wrap">
			<h2><?php echo ___('Redeem lists');?></h2>
			<table class="wp-list-table widefat fixed pages">
				<thead>
					<tr>
						<?php foreach(self::get_types() as $k => $v){ ?>
						<th><?php echo $v['text'];?></th>
						<?php } ?>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<?php foreach(self::get_types() as $k => $v){ ?>
						<th><?php echo $v['text'];?></th>
						<?php } ?>
					</tr>
				</tfoot>
				<tbody>
					<?php if(empty($lists)){ ?>
						<tr><td><?php echo ___('No customer quote request yet.');?></td></tr>
					<?php }else{ ?>
						<?php
						$i = 0;
						foreach($lists as $list){ 
						++$i;
							?>
							<tr class="<?php echo $i%2 == 0 ? '' : 'alternate';?>">
								<?php foreach(self::get_types() as $k => $v){ ?>
									<td>
										<?php echo $k === 'name' ? '<strong>' : null;?>
										<?php echo isset($list[$k]) && !empty($list[$k]) ? esc_html($list[$k]) : '-';?>
										<?php echo $k === 'name' ? '</strong>' : null;?>
									</td>
								<?php } ?>
							</tr>
						<?php } ?>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php
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
			<td><input type="text" id="<?= __CLASS__;?>-name-<?= $placeholder;?>" name="<?= __CLASS__;?>[<?= $placeholder;?>][name]" class="widefat" placeholder="<?= ___('Name');?>" value="<?= $name;?>"/></td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-consume-<?= $placeholder;?>"><?= ___('Consume point');?></label></th>
			<td><input type="number" id="<?= __CLASS__;?>-consume-<?= $placeholder;?>" name="<?= __CLASS__;?>[<?= $placeholder;?>][consume]" class="short-number" placeholder="<?= ___('Consume point');?>" value="<?= $consume;?>" step="1"/></td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-award-<?= $placeholder;?>"><?= ___('award point');?></label></th>
			<td><input type="number" id="<?= __CLASS__;?>-award-<?= $placeholder;?>" name="<?= __CLASS__;?>[<?= $placeholder;?>][award]" class="short-number" placeholder="<?= ___('award point');?>" value="<?= $award;?>" step="1"/></td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-percent-<?= $placeholder;?>"><?= ___('Rewrad percent');?></label></th>
			<td><input type="number" id="<?= __CLASS__;?>-percent-<?= $placeholder;?>" name="<?= __CLASS__;?>[<?= $placeholder;?>][percent]" class="short-number" placeholder="<?= ___('Rewrad percent');?>" value="<?= $percent;?>" min="0" max="100" step="1"/> %</td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-percent-<?= $placeholder;?>"><?= ___('Remaining');?></label></th>
			<td><input type="number" id="<?= __CLASS__;?>-remaining-<?= $placeholder;?>" name="<?= __CLASS__;?>[<?= $placeholder;?>][remaining]" class="short-number" placeholder="<?= ___('Remaining number');?>" value="<?= $remaining;?>" min="0" step="1"/></td>
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
					<textarea name="<?= __CLASS__;?>[<?= $placeholder;?>][des]" id="<?= __CLASS__;?>-des-<?= $placeholder;?>" rows="3" class="widefat"><?= $des;?></textarea>
				</td>
			</th>
		</tr>
		<tr>
			<th>
				<label for="<?= __CLASS__;?>-success-<?= $placeholder;?>"><?= ___('Success description');?></label>
				<td>
					<input  type="text" name="<?= __CLASS__;?>[<?= $placeholder;?>][success]" id="<?= __CLASS__;?>-success-<?= $placeholder;?>" rows="3" class="widefat" value="<?= $success;?>">
				</td>
			</th>
		</tr>
		<tr>
			<th>
				<label for="<?= __CLASS__;?>-fail-<?= $placeholder;?>"><?= ___('Fail description');?></label>
				<td>
					<input type="text" name="<?= __CLASS__;?>[<?= $placeholder;?>][fail]" id="<?= __CLASS__;?>-fail-<?= $placeholder;?>" rows="3" class="widefat" value="<?= $fail;?>">
				</td>
			</th>
		</tr>
		<tr>
			<th><?= ___('Box control');?></th>
			<td>
				<a href="javascript:;" data-target="<?= __CLASS__;?>-item-<?= $placeholder;?>" class="del"><?= ___('Delete this item');?></a>
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
			'other' => ___('Other'),
		];
		?>
		<select name="<?= __CLASS__;?>[<?= $placeholder;?>][type]" class="widefat">
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
			<p class="description"><?= ___('You can edit lottery item for users');?></p>
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
							<input type="number" name="<?= __CLASS__;?>[max-times]" id="<?= __CLASS__;?>--max-times" class="short-number" value="<?= self::get_max_times();?>" step="1" min="1">
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
	public static function filter_custom_point_types(array $types = []){
		$types['lottery-percent'] = [
			'text' => ___('Victory percentage'),
			'type' => 'number',
			'des' => ___('User lotterys other user points victory percentage. The unit is the percentage.'),
		];
		$types['lottery'] = [
			'text' => ___('When user lottery points'),
			'type' => 'text',
			'des' => ___('Use commas to separate multiple point, first as the default.'),
		];
		$types['lottery-times'] = [
			'text' => ___('User daily lottery max-times'),
			'type' => 'number',
			'des' => ___('The maximum number of attacks per user daily.'),
		];
		return $types;
	}
	public static function get_box($box_id = null,$key = null){
		$boxes = self::get_options('boxes');
		if(!$box_id)
			return $boxes;
		if($key)
			return isset($boxes[$box_id][$key]) ? $boxes[$box_id][$key] : false;
		return isset($boxes[$box_id]) ? $boxes[$box_id] : false;
	}
	public static function options_default(array $opts = []){
		$opts[__CLASS__] = [
			'boxes' => [
				0 => [
					'name' => ___('Lottery 100'),
					'consume' => 100,
					'award' => 100,
					'percent' => 50,
					'remaining' => 9999,
					'type' => 'point',
					'des' => ___('You can consume 100 points to get 200 points if you win.'),
					'success' => ___('Congratulations! You win 200 points.'),
					'fail' => ___('Not lucky, you lost the game and consume 100 points. Try again?'),
				],
				1 => [
					'name' => ___('Lottery 200'),
					'consume' => 200,
					'award' => 400,
					'percent' => 50,
					'remaining' => 9999,
					'type' => 'point',
					'des' => ___('You can consume 200 points to get 400 points if you win.'),
					'success' => ___('Congratulations! You win 400 points.'),
					'fail' => ___('Not lucky, you lost the game and consume 200 points. Try again?'),
				],
				2 => [
					'name' => ___('Keychain'),
					'consume' => 1000,
					'award' => 0,
					'percent' => 10,
					'remaining' => 10,
					'type' => 'other',
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
		theme_features::check_nonce();
		$output = [];
		
		$type = isset($_REQUEST['type']) && is_string($_REQUEST['type']) ? $_REQUEST['type'] : null;

		$target_id = isset($_REQUEST['target']) && is_numeric($_REQUEST['target']) ? $_REQUEST['target'] : null;

		$current_user_id = theme_cache::get_current_user_id();
		
		switch($type){
			/**
			 * start
			 */
			case 'start':

				/** item */
				$item = isset($_REQUEST['item']) && is_string($_REQUEST['item']) ? $_REQUEST['item'] : false;
				if(!$item){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'empty_item',
						'msg' => ___('Sorry, the lottery item is empty.'),
					]));
				}

				/** lottery boxes */
				$boxes = self::get_box();
				if(empty($boxes)){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'empty_lottery_item',
						'msg' => ___('It is not any lottery yet.'),
					]));
				}

				/** check item is in boxes */
				if(!isset($boxes[$item])){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'invaild_item',
						'msg' => ___('Sorry, the lottery item is invaild.'),
					]));
				}

				/** check current user points */
				$current_user_points = theme_custom_point::get_point($current_user_id);
				if($current_user_points < $boxes[$item]['consume']){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'not_enough_points',
						'msg' => ___('Sorry, your points is not enough to make a lottery.'),
					]));
				}

				/** check remaining time */
				if($boxes[$item]['remaining'] == 0){
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
	public static function add_history(array $data = []){
		$meta = [
			'type'=> 'lottery',
			'timestamp' => self::get_timestamp(),
			'win' => (bool)$data['win'],
			'consume' => (int)$data['consume'],
			'type' => $data['type'],
			'points' => (int)$data['points'],
		];

		if($data['type'] === 'other'){
			$data['code'] = md5(json_encode($meta));
		}
		add_user_meta($data['user-id'],theme_custom_point::$user_meta_key['history'],$meta);
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
			'timestamp' => self::get_timestamp(),
		]);
		$codes = self::get_user_redeem_codes($user_id);
		if(isset($codes[$data['id']]))
			return false;
		$codes[$data['id']] = $data;
		update_user_meta($user_id,self::$user_meta_key['code'],$codes);
	}
	public static function update_user_redeem($user_id,$code_key){
		$codes = self::get_user_redeem_codes($user_id);
		if(isset($codes[$code_key]))
			return false;
		$codes[$code_key]['redeemed'] = true;
		update_user_meta($user_id,self::$user_meta_key['code'],$codes);
	}
	
	public static function get_user_redeem_codes($user_id,$code_key = null){
		$codes = self::get_user_meta($user_id,self::$user_meta_key['code']);
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

		?>
		<li class="list-group-item">
			<?php theme_custom_point::the_list_icon('lottery');?>
			<?php theme_custom_point::the_point_sign($history['points']);?>
			<span class="history-text">

				<?php
				if($history['win']){
					/** type is point */
					if($history['type'] === 'point'){
						echo sprintf(
							___('You won the lottery game and got %1$s %2$s.'),
							
							'<strong>+' . abs($history['points']) . '</strong>',
							
							theme_custom_point::get_point_name()
						);
					/** type is other */
					}else{
						echo sprintf(
							___('You won the lottery game and got a redemption: %s.'),
							
							'<strong class="label label-default">+' . abs($history['code']) . '</strong>'
						);
					}
				}else{
					echo sprintf(
						___('You lotteryed %1$s but miss! You lost %2$s %3$s.'),
						
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
			m.config.lang.M02 = '<?= ___('lotterying, please wait...');?>';
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