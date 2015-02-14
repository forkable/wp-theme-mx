<?php
/**
 * @version 1.0.0
 */
theme_custom_point::init();
class theme_custom_point{
	public static $iden = 'theme_custom_point';
	public static $user_meta_key = array(
		'point' => 'theme_point'
	);
	public static function init(){
		add_action('page_settings',get_class() . '::display_backend');
		add_filter('theme_options_default',get_class() . '::options_default');
		add_filter('theme_options_save',get_class() . '::options_save');
	}
	public static function display_backend(){
		$opt = theme_options::get_options(self::$iden);
		$point_name = isset($opt['point-name']) ? $opt['point-name'] : ___('Cat-paw');
		?>
		<fieldset>
			<legend><?php echo ___('User point settings');?></legend>
			<p class="description"><?php echo ___('About user point settings.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><label for="<?php echo self::$iden;?>-point-name"><?php echo ___('Point name');?></label></th>
						<td>
							<input type="text" class="widefat" id="<?php echo self::$iden;?>-point-name" value="<?php echo esc_attr($point_name);?>">
						</td>
					</tr>
					<?php foreach(self::get_point_types() as $k => $v){ ?>
						<tr>
							<th>
								<label for="<?php echo self::$iden;?>-<?php echo $k;?>"><?php echo $v[text];?></label>
							</th>
							<td>
								<input type="number" name="<?php echo self::$iden;?>[<?php echo $k;?>]" class="short-text" id="<?php echo self::$iden;?>-<?php echo $k;?>" value="<?php echo isset($opt[$k]) ? $opt[$k] : 0;?>">
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function get_point_types($key = null){
		$types = array(
			'comment-point' => array(
				'text' => ___('When publish a comment'),
			),
			'post-publish-point' => array(
				'text' => ___('When publish a post'),
			),
			'post-reply-point' => array(
				'text' => ___('When post has been commented'),
			),
		);
		if(empty($key)) return $types;
		
		return isset($types[$key]) ? $types[$key] : null;
	}
	public static function options_default($opts){
		$opts[self::$iden] = array(
			'point-name' 			=> ___('Cat-paw'),
			'comment-point' 		=> 2,
			'post-publish-point' 	=> 5,
			'post-reply-point' 		=> 2,
		);
		return $opts;
	}
	public static function options_save($opts){
		if(!isset($_POST[self::$iden])) return $opts;
		$opts[self::$iden] = $_POST[self::$iden];
		return $opts;
	}
	public static function get($user_id){
		return (int)get_user_meta($user_id,self::$user_meta_key['point'],true);
	}
	public static function add($user_id,$point){
		$old_point = self::get($user_id);
		update_user_meta($user_id,self::$user_meta_key['point'],$old_point + (int)$point);
	}
	public static function reduce($user_id,$point){
		$old_point = self::get($user_id);
		update_user_meta($user_id,self::$user_meta_key['point'],$old_point - (int)$point);
	}
}
?>