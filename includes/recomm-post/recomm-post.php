<?php
/**
 * theme recommended post
 *
 * @version 2.0.3
 * @author KM@INN STUDIO
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_recommended_post::init';
	return $fns;
});
class theme_recommended_post{
	
	public static $iden = 'theme_recommended_post';
	public static $css_id = 'theme-recommended-post';
	
	public static function init(){
		add_action('add_meta_boxes',__CLASS__ . '::add_meta_boxes');
		add_action('page_settings',__CLASS__ . '::display_backend');
		add_filter('theme_options_save',__CLASS__ . '::options_save');
		
		add_action('save_post',__CLASS__ . '::save_post');
		add_action('delete_post',__CLASS__ . '::delete_post');
	}
	public static function add_meta_boxes(){
		$screens = array('post');

		foreach ( $screens as $screen ) {
			add_meta_box(
				self::$iden,
				___( 'Recommended post' ),
				__CLASS__ . '::box_display',
				$screen,
				'side'
			);
		}
	}
	public static function box_display($post){
	
		wp_nonce_field(self::$iden,self::$iden . '-nonce' );

		$recomm_posts = (array)theme_options::get_options(self::$iden);
		$checked = in_array($post->ID,$recomm_posts) ? ' checked ' : null;
		$btn_class = $checked ? ' button-primary ' : null;
		?>
		<label for="recomm-set" class="button widefat <?php echo $btn_class;?>">
			<input type="checkbox" id="recomm-set" name="<?php echo self::$iden;?>" value="1" <?php echo $checked;?> />
			<?php echo esc_html(___('Set it to recommended post'));?>
		</label>
		<?php
	}
	public static function delete_post($post_id){
		if ( current_user_can( 'delete_posts' ) ){
			$recomm_posts = (array)theme_options::get_options(self::$iden);
			$k = array_search($post_id,$recomm_posts);
			if(!empty($recomm_posts) && $k !== false){
				unset($recomm_posts[$k]);
				sort($recomm_posts);
				theme_options::set_options(self::$iden,$recomm_posts);
			}
		}
	}
	public static function save_post($post_id){
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return false;

		if(!isset($_POST[self::$iden . '-nonce']) || !wp_verify_nonce($_POST[self::$iden . '-nonce'], self::$iden)) return false;
		
		$recomm_posts = (array)theme_options::get_options(self::$iden);
		
		$recomm_set = isset($_POST[self::$iden]) ? (int)$_POST[self::$iden] : null;
		
		$k = array_search($post_id,$recomm_posts);
		//var_dump($recomm_posts,$recomm_set);exit;
		/** 
		 * if checked and no add yet, just add to recomm. posts
		 */
		if($recomm_set && $k === false){
			$recomm_posts[] = $post_id;
			theme_options::set_options(self::$iden,$recomm_posts);
		}
		/** 
		 * if no checked and added, just remove it
		 */
		if(!$recomm_set && $k !== false){
			unset($recomm_posts[$k]);
		//var_dump(empty($recomm_posts));exit;
			if(empty($recomm_posts)){
				theme_options::delete_options(self::$iden,null);
			}else{
				sort($recomm_posts);
				theme_options::set_options(self::$iden,$recomm_posts);
			}
		}
		//var_dump(theme_options::get_options(self::$iden));exit;
	}
	public static function display_backend(){
		$recomm_posts = (array)theme_options::get_options(self::$iden);
		?>
		<fieldset>
			<legend><?php echo ___('Recommended posts');?></legend>
			<p><?php echo esc_html(___('Some feature will be use recommended posts.'));?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?php echo ___('Posts');?></th>
						<td>
							<?php
							if(!empty($recomm_posts)){
								global $post,$wp_query;
								$wp_query = new WP_Query(array(
									'posts_per_page' =>-1,
									'post__in' => $recomm_posts
								));
								if(have_posts()){
									while(have_posts()){
										the_post();
										?>
										<label for="recomm-post-<?php echo $post->ID;?>" class="button">
											<input type="checkbox" id="recomm-post-<?php echo $post->ID;?>" name="<?php echo self::$iden;?>[]" value="<?php echo $post->ID;?>" checked/>
											<?php echo esc_html(get_the_title());?>
										</label>
										<?php
									}
								}else{
									echo status_tip('info',esc_html(___('No any post yet')));
								}
								wp_reset_query();
								wp_reset_postdata();
							}else{
								echo status_tip('info',esc_html(___('No any post yet')));
							}
							?>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function options_save($options){
		if(isset($_POST[self::$iden]) && is_array($_POST[self::$iden])){
			$options[self::$iden] = $_POST[self::$iden];
		}
		return $options;
	}
	
	
}