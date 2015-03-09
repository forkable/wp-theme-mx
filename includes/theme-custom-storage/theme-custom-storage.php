<?php
/**
 * @version 1.0.0
 */
theme_custom_storage::init();
class theme_custom_storage{
	public static $iden = 'theme_custom_storage';
	public static $post_meta_key = array(
		'key' => '_theme_custom_storage'
	);
	public static function init(){
		add_action('add_meta_boxes', get_class() . '::meta_box_add');
		add_action('save_post_post', get_class() . '::meta_box_save');
	}
	public static function get_types($key = null){
		$types = array(
			'bdyun' => array(
				'text' => ___('Baidu storage'),
			)
		);
		if(empty($key)){
			return $types;
		}else{
			return isset($types[$key]) ? $types[$key] : null;
		}
	}
	public static function get_post_meta($post_id = null){
		if(!$post_id){
			global $post;
			$post_id = $post->ID;
		}
		$meta = get_post_meta($post_id,self::$post_meta_key['key'],true);
		if(empty($meta)){
			return null;
		}else{
			return (array)$meta;
		}
	}
	public static function meta_box_add(){
		$screens = array( 'post' );
		foreach ( $screens as $screen ) {
			add_meta_box(
				self::$iden,
				___('File storage'),
				get_class() . '::meta_box_display',
				$screen,
				'side'
			);
		}
	}
	public static function meta_box_save($post_id){
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		if(!isset($_POST[self::$iden])) return;
		//if(!isset($_POST[self::$iden . '-nonce']) || !wp_verify_nonce($_POST[self::$iden . '-nonce'])) return;
		
		$new_meta = $_POST[self::$iden];
		if(is_null_array($new_meta)){
			delete_post_meta($post_id,self::$post_meta_key['key']);
		}else{
			update_post_meta($post_id,self::$post_meta_key['key'],$new_meta);
		}
	}
	public static function meta_box_display($post){
		$meta = self::get_post_meta($post->ID);
		//wp_nonce_field(self::$iden,self::$iden . '-nonce');
		foreach(self::get_types() as $k => $v){
			?>
			<div class="<?php echo self::$iden;?>-<?php echo $k;?>">
				<p><strong>
					<label for="<?php echo self::$iden;?>-<?php echo $k;?>-url"><?php echo $v['text'];?></label>
				</strong></p>
				<input 
					type="url" 
					name="<?php echo self::$iden;?>[<?php echo $k;?>][url]" 
					id="<?php echo self::$iden;?>-<?php echo $k;?>-url" 
					class="widefat code" 
					placeholder="<?php echo sprintf(___('%s url'),$v['text']);?>"
					value="<?php echo isset($meta[$k]['url']) ? esc_url($meta[$k]['url']) : null;?>" 
				>
				<input 
					type="text" 
					name="<?php echo self::$iden;?>[<?php echo $k;?>][pwd]" 
					id="<?php echo self::$iden;?>-<?php echo $k;?>-pwd" 
					class="widefat code" 
					placeholder="<?php echo sprintf(___('%s password'),$v['text']);?>"
					value="<?php echo isset($meta[$k]['pwd']) ? esc_url($meta[$k]['pwd']) : null;?>" 
				>
			</div>			
			<?php
		}
	}
}
?>