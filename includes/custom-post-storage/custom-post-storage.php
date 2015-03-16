<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_storage::init';
	return $fns;
});
class theme_custom_storage{
	public static $iden = 'theme_custom_storage';
	public static $page_slug = 'stroage-download';
	public static $post_meta_key = array(
		'key' => '_theme_custom_storage'
	);
	public static function init(){
		add_action('init',					get_class() . '::page_create');
		add_action('add_meta_boxes', 		get_class() . '::meta_box_add');
		add_action('save_post_post', 		get_class() . '::meta_box_save');
		
		add_action('wp_enqueue_scripts', 	get_class() . '::frontend_css');
		
		add_shortcode('post-stroage-download',get_class() . '::add_shortcode');
		
		add_filter('wp_title',				get_class() . '::wp_title',10,2);	
	}
	public static function wp_title($title, $sep){
		if(!self::is_page()) return $title;

		$post = self::get_decode_post();
		if($post){
			return get_the_title($post->ID) . $sep . ___('storage download') . $sep . get_bloginfo('name');
		}
	}
	public static function is_page(){
		return is_page(self::$page_slug);
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
					value="<?php echo isset($meta[$k]['pwd']) ? esc_attr($meta[$k]['pwd']) : null;?>" 
				>
			</div>			
			<?php
		}
	}
	public static function is_enabled(){
		return true;
	}
	public static function process(){
		$output = [];

		$post_id = isset($_GET['post-id']) ? (int)$_GET['post-id'] : null;
		if(!$post_id)
			die();
		
		die(theme_features::json_format($output));
	}
	public static function page_create(){
		if(!current_user_can('manage_options')) return false;
		
		$page_slugs = array(
			self::$page_slug => array(
				'post_content' 	=> '',
				'post_name'		=> self::$page_slug,
				'post_title'	=> ___('Storage download'),
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
			if(!$page){
				$r = wp_parse_args($v,$defaults);
				$page_id = wp_insert_post($r);
			}
		}
	}
	public static function get_url(){
		return get_permalink(theme_cache::get_page_by_path(self::$page_slug)->ID);
	}
	public static function get_download_page_url($post_id){
		$code_obj = array(
			'post-id' => (int)$post_id
		);
		return add_query_arg(array(
			'code' => authcode(serialize($code_obj),'encode')	
			),self::get_url());
	}
	public static function get_decode_post(){
		$code = isset($_GET['code']) ? $_GET['code'] : null;
		$decode = authcode($code,'decode');
		if(!$decode)
			return false;
			
		$decode = unserialize($decode);
		
		if(!isset($decode['post-id']))
			return false;

		return get_post($decode['post-id']);
	}
	public static function add_shortcode($atts){
		global $post;
		$post = self::get_decode_post();
		$meta = self::get_post_meta($post->ID);
		ob_start();
		?>
		<div class="post-download">
			<?php
			foreach(self::get_types() as $k => $v){
				?>
				<fieldset class="post-download-module">
					<legend><label for="<?php echo self::$iden;?>-<?php echo $k;?>-pwd" class="label label-info"><?php echo $v['text'];?></label></legend>
					<div class="fieldset-content form-horizontal">
						<?php if(isset($meta[$k]['pwd']) && !empty($meta[$k]['pwd'])){ ?>
						<div class="form-group">
							<label for="<?php echo self::$iden;?>-<?php echo $k;?>-pwd" class="col-xs-4 col-sm-2 control-label"><?php echo sprintf(___('%s password'),$v['text']);?></label>
							<div class="col-xs-8 col-sm-10">
								<input 
									type="text" 
									id="<?php echo self::$iden;?>-<?php echo $k;?>-pwd" 
									class="pwd form-control" 
									readonly 
									value="<?php echo isset($meta[$k]['pwd']) ? esc_attr($meta[$k]['pwd']) : null;?>" 
									title="<?php echo sprintf(___('%s password'),$v['text']);?>"
									onclick="this.select();"
								>
							</div>
						</div>
						<div class="form-group">
							<div class="col-xs-12">
								<div class="btn-group btn-group-lg btn-block">
									<a href="<?php echo isset($meta[$k]['url']) ? esc_url($meta[$k]['url']) : null;?>" class="btn btn-success col-xs-9 col-sm-10"><i class="fa fa-cloud-download"></i> <?php echo ___('Download now');?></a>
									<a href="<?php echo isset($meta[$k]['url']) ? esc_url($meta[$k]['url']) : null;?>" class="btn btn-success col-xs-3 col-sm-2" target="_blank"><i class="fa fa-external-link"></i></a>
								</div>
							</div>
						</div>
						<?php }else{ ?>
							<div class="btn-group btn-group-lg btn-block">
								<a href="<?php echo isset($meta[$k]['url']) ? esc_url($meta[$k]['url']) : null;?>" class="btn btn-success col-xs-9 col-sm-11"><i class="fa fa-cloud-download"></i> <?php echo ___('Download now');?></a>
								<a href="<?php echo isset($meta[$k]['url']) ? esc_url($meta[$k]['url']) : null;?>" class="btn btn-success col-xs-3 col-sm-1" target="_blank"><i class="fa fa-external-link"></i></a>
							</div>
						<?php } ?>
					</div> <!-- /.fieldset -->
				</fieldset>
			<?php } ?>
		</div>
		<?php
		wp_reset_postdata();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	public static function display_frontend(){
		global $post;
		$meta = self::get_post_meta($post->ID);
		if(!$meta)
			return;
			
		?>
		<div class="post-storage">
			<div class="btn-group btn-group-lg btn-block">
				<a href="<?php echo esc_url(self::get_download_page_url($post->ID));?>" class="download-link btn btn-success col-xs-9 col-sm-11" rel="nofollow" >
					<i class="fa fa-cloud-download"></i>
					<?php echo ___('Download now');?>
					
				</a>
				<a href="<?php echo esc_url(self::get_download_page_url($post->ID));?>" class="download-link btn btn-success col-xs-3 col-sm-1" rel="nofollow" target="_blank" title="<?php echo ___('Open in new window');?>" >
					<i class="fa fa-external-link"></i>
				</a>
			</div>
			
		</div>
		<?php
	}
	public static function frontend_css(){
		if(!is_page(self::$page_slug)) return false;
		wp_enqueue_style(
			self::$iden,
			theme_features::get_theme_includes_css(__DIR__,'style',false),
			false,
			theme_features::get_theme_info('version')
		);

	}
}
?>