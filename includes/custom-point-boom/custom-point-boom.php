<?php
/**
 * @version 1.0.0
 */
//add_filter('theme_includes',function($fns){
//	$fns[] = 'theme_custom_point_boom::init';
//	return $fns;
//});
class theme_custom_point_boom{

	public static $iden = 'theme_custom_point_boom';

	public static function init(){
		
		add_action('wp_ajax_' . self::$iden, __CLASS__ . '::process');
		add_action('wp_ajax_nopriv_' . self::$iden, __CLASS__ . '::process');
		
		add_action('before_delete_post',__CLASS__ . '::sync_delete_post');

		add_filter('frontend_seajs_alias',__CLASS__ . '::frontend_seajs_alias');
		add_action('frontend_seajs_use',__CLASS__ . '::frontend_seajs_use');

		add_filter('custom_point_options_default',__CLASS__ . '::filter_custom_point_options_default');

		add_filter('custom_point_types',__CLASS__ . '::filter_custom_point_types');
	}

	public static function filter_custom_point_types(array $types = []){
		$types['boom-percent'] = [
			'text' => ___('Victory percentage'),
			'des' => ___('User booms other user points victory percentage. The unit is the percentage.'),
		];
		$types['boom'] = [
			'text' => ___('When user boom points'),
			'des' => ___('Use commas to separate multiple point, first as the default.'),
		];
		return $types;
	}
	public static function filter_custom_point_options_default(array $opts = []){
		$opts['points']['boom'] = '10,50,100';
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
		<h3><?= ___('User points boom description');?></h3>
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
	<p>' . sprintf(___("Welcome to %1$s boom. You can consume your %1$s to boom opponent's %1$s. Please read the blew item before booms:"), theme_custom_point::get_point_name()) . '</p>
	<ol>
		<li>' . sprintf(___('If you hit the opponent when you boom, your consumption will come back and you will get the extra %s of consumption.'), theme_custom_point::get_point_name()) . '</li>
		<li>' . sprintf(___('If you miss the opponent when you boom, the opponent will get your half of %s of consumption.'), theme_custom_point::get_point_name()) . '</li>
		<li>' . ___('Do not boom atrociously, be careful opponent booms back.') . '</li>
	</ol>
</div>';
	}
	public static function get_des(){
		$des = self::get_options('des');
		return trim($des) === '' ? self::get_des_default() : $des;
	}
	public static function process(){
		theme_features::check_referer();
		theme_features::check_nonce();
		$output = [];

		$type = isset($_REQUEST['type']) && is_string($_REQUEST['type']) ? $_REQUEST['type'] : null;

		switch($type){
			case 'boom':
				/**
				 * check target user id
				 */
				$target_user_id = isset($_REQUEST['target']) && is_numeric($_REQUEST['target']) ? $_REQUEST['target'] : null;
				if(!$target_user_id){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'invaild_target_user_id',
						'msg' => ___('Sorry, the target user ID is invaild.'),
					]));
				}
				/**
				 * check target user
				 */
				$target = get_user_by('id',$target_user_id);
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
				 * pass 
				 */
				
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
	private static function get_points_for_target($hit,$point){
		return $hit ? 0 - $points : round($points / 2);
	}
	private static function get_points_for_sponsor($hit,$point){
		return $hit ? $points : 0 - $point;
	}
	public static function add_history_for_target($sponsor_id,$target_id,$points,$hit){

		$meta = [
			'type'=> 'be-boom',
			'timestamp' => self::get_timestamp(),
			'hit' => $hit,
			'sponsor-id' => $sponsor_id,
			'points' => self::get_points_for_target($hit,$points),
		];
		
		add_user_meta($target_id,theme_custom_point::$user_meta_key['history'],$meta);
		
	}
	public static function add_history_for_sponsor($sponsor_id,$target_id,$points,$hit){

		$meta = [
			'type'=> 'boom',
			'timestamp' => self::get_timestamp(),
			'hit' => $hit,
			'target-id' => $target_id,
			'points' => self::get_points_for_target($hit,$points),
		];
		
		add_user_meta($target_id,theme_custom_point::$user_meta_key['history'],$meta);
		
	}

}