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
		add_action('init',					__CLASS__ . '::page_create');
		add_action('add_meta_boxes', 		__CLASS__ . '::meta_box_add');
		add_action('save_post_post', 		__CLASS__ . '::meta_box_save');

		add_action('template_redirect',		__CLASS__ . '::template_redirect');
		
		add_action('wp_enqueue_scripts', 	__CLASS__ . '::frontend_css');
		
		add_shortcode('post-stroage-download',__CLASS__ . '::add_shortcode');
		
		add_filter('wp_title',				__CLASS__ . '::wp_title',10,2);	
	}
	public static function wp_title($title, $sep){
		if(!self::is_page()) 
			return $title;

		$post = self::get_decode_post();
		if($post)
			return get_the_title($post->ID) . $sep . ___('storage download') . $sep . get_bloginfo('name');
		
	}
	public static function is_page(){
		static $caches = [];
		if(isset($caches[self::$iden]))
			return $caches[self::$iden];
			
		$caches[self::$iden] = is_page(self::$page_slug);
		return $caches[self::$iden];
	}
	public static function get_types($key = null){
		$types = array(
			'bdyun' => array(
				'text' => ___('Baidu storage'),
			)
		);
		if($key === null){
			return $types;
		}else{
			return isset($types[$key]) ? $types[$key] : null;
		}
	}
	public static function template_redirect(){
		if(!self::is_page())
			return;
		if(!self::get_decode_post()){
			//wp_redirect(home_url());
			wp_die(
				___('Error: invaild code.'),
				___('Error'),
				[
					'response' => 404,
					'back_link' => true,
				]
			);
		}
	}
	public static function get_post_meta($post_id = null){
		if(!$post_id){
			global $post;
			$post_id = $post->ID;
		}
		static $caches = [];
		if(isset($caches[$post_id]))
			return $caches[$post_id];
			
		$caches[$post_id] = get_post_meta($post_id,self::$post_meta_key['key'],true);

		return $caches[$post_id];
		
	}
	public static function meta_box_add(){
		$screens = array( 'post' );
		foreach ( $screens as $screen ) {
			add_meta_box(
				self::$iden,
				___('File storage'),
				__CLASS__ . '::meta_box_display',
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
				'post_content' 	=> '[post-' . self::$page_slug . ']',
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
	public static function get_download_page_url($post_id = null){
		if($post_id === null){
			global $post;
			$post_id = $post->ID;
		}
		
		static $caches;
		if(isset($caches[$post_id]))
			return $caches[$post_id];
			
		$code_obj = array(
			'post-id' => (int)$post_id
		);
		$caches[$post_id] = add_query_arg(array(
			'code' => base64_encode(authcode(serialize($code_obj),'encode'))
			),self::get_url());
		return $caches[$post_id];
	}
	public static function get_decode_post(){
		$code = isset($_GET['code']) && is_string($_GET['code']) ? base64_decode($_GET['code']) : null;
		if(!$code)
			return false;
			
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
	<?php foreach(self::get_types() as $k => $v){ ?>
		<fieldset class="post-download-module">
			<legend><span class="label label-default"><?php echo $v['text'];?></span></legend>
			<div class="fieldset-content">
				<div class="row">
					<?php if(isset($meta[$k]['pwd']) && !empty($meta[$k]['pwd'])){ ?>
						<div class="col-sm-3">
							<div class="form-group">
								<div class="pwd btn btn-info btn-lg btn-block" id="<?php echo self::$iden;?>-<?php echo $k;?>-pwd" title="<?php echo sprintf(___('%s password'),$v['text']);?>" >
									<?php echo isset($meta[$k]['pwd']) ? esc_html($meta[$k]['pwd']) : '-';?>
								</div>
							</div>
						</div>
			
						<div class="col-sm-9">
							<div class="form-group">
								<div class="btn-group btn-group-lg btn-block">
									<a 
										href="<?php echo isset($meta[$k]['url']) ? esc_url($meta[$k]['url']) : null;?>" 
										class="btn btn-success col-xs-9 col-sm-10" 
										rel="nofollow"
									>
											<i class="fa fa-cloud-download"></i> 
											<?php echo ___('Download now');?>
										</a>
									<a 
										href="<?php echo isset($meta[$k]['url']) ? esc_url($meta[$k]['url']) : null;?>" 
										class="btn btn-success col-xs-3 col-sm-2" 
										target="_blank" 
										rel="nofollow"
									>
										<i class="fa fa-external-link"></i>
									</a>
								</div>
							</div>
						</div>
					<?php }else{ ?>
						<div class="col-sm-12">
							<div class="form-group">
								<div class="btn-group btn-group-lg btn-block">
									<a 
										href="<?php echo isset($meta[$k]['url']) ? esc_url($meta[$k]['url']) : null;?>" 
										class="btn btn-success col-xs-9 col-sm-11"
										rel="nofollow"
									>
											<i class="fa fa-cloud-download"></i> 
											<?php echo ___('Download now');?>
										</a>
									<a 
										href="<?php echo isset($meta[$k]['url']) ? esc_url($meta[$k]['url']) : null;?>" 
										class="btn btn-success col-xs-3 col-sm-1" target="_blank" 
										title="<?php echo ___('Open in new window');?>" 
										rel="nofollow"
										>
											<i class="fa fa-external-link"></i>
										</a>
								</div>
							</div><!-- /.form-group -->
						</div><!-- /.col-sm-12 -->
					<?php } ?>
				</div><!-- /.row -->
			</div><!-- /.fieldset -->
		</fieldset>
	<?php } ?>
</div><!-- /.post-download -->
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

		$download_url = esc_url(self::get_download_page_url($post->ID));

		?>
		<div class="post-storage">
			<div class="btn-group btn-group-lg btn-block">
				<a href="<?php echo $download_url;?>" class="download-link btn btn-success col-xs-9 col-sm-11" rel="nofollow" >
					<i class="fa fa-cloud-download"></i>
					<?php echo ___('Download now');?>
					
				</a>
				<a href="<?php echo $download_url;?>" class="download-link btn btn-success col-xs-3 col-sm-1" rel="nofollow" target="_blank" title="<?php echo ___('Open in new window');?>" rel="nofollow" >
					<i class="fa fa-external-link fa-fw"></i>
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