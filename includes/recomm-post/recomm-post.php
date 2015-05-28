<?php
/**
 * theme recommended post
 *
 * @version 2.0.4
 * @author INN STUDIO <inn-studio.com>
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_recommended_post::init';
	return $fns;
});
class theme_recommended_post{
	
	public static $iden = 'theme_recommended_post';
	
	public static function init(){
		add_action('add_meta_boxes',get_class() . '::add_meta_boxes');
		add_action('page_settings',get_class() . '::display_backend');
		add_filter('theme_options_save',get_class() . '::options_save');
		add_filter('theme_options_default',get_class() . '::opttions_default');
		
		add_action('save_post',get_class() . '::save_post');
		add_action('delete_post',get_class() . '::delete_post');
	}
	public static function add_meta_boxes(){
		$screens = array('post');

		foreach ( $screens as $screen ) {
			add_meta_box(
				self::$iden,
				___( 'Recommended post' ),
				get_class() . '::box_display',
				$screen,
				'side'
			);
		}
	}
	public static function get_options($key = null){
		$caches = [];
		if(!isset($caches[self::$iden]))
			$caches[self::$iden] = theme_options::get_options(self::$iden);

		if($key){
			return isset($caches[self::$iden][$key]) ? $caches[self::$iden][$key] : null;
		}else{
			return $caches[self::$iden];
		}
	}
	public static function box_display($post){
	
		wp_nonce_field(self::$iden,self::$iden . '-nonce' );

		$recomm_posts = (array)self::get_options('ids');

		$checked = in_array($post->ID,$recomm_posts) ? ' checked ' : null;
		$btn_class = $checked ? ' button-primary ' : null;
		?>
		<label for="recomm-set" class="button widefat <?= $btn_class;?>">
			<input type="checkbox" id="recomm-set" name="<?= self::$iden;?>" value="1" <?= $checked;?> />
			<?= ___('Set as recommended post');?>
		</label>
		<?php
	}
	public static function delete_post($post_id){
		if ( !current_user_can( 'delete_posts' ) )
			return;
		$opt = (array)self::get_options();
		$recomm_posts = isset($opt['ids']) ? (array)$opt['ids'] : [];
		$k = array_search($post_id,$recomm_posts);
		
		if($k !== false){
			unset($opt['ids'][$k]);
			sort($opt['ids']);
			theme_options::set_options(self::$iden,$opt);
		}
	}
	public static function opttions_default($opts){
		$opts[self::$iden]['enabled'] = 1;
		return $opts;
	}
	public static function save_post($post_id){
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return false;

		if(!isset($_POST[self::$iden . '-nonce']) || !wp_verify_nonce($_POST[self::$iden . '-nonce'], self::$iden)) 
			return false;

		$opt = (array)self::get_options();
		
		if(!isset($opt['ids']))
			$opt['ids'] = [];
		/**
		 * set to recomm
		 */
		if(isset($_POST[self::$iden])){
			if(!isset($opts['ids'][$post_id])){
				$opt['ids'][$post_id] = $post_id;
			}
		}else{
			if(isset($opt['ids'][$post_id])){
				unset($opt['ids'][$post_id]);
			}
		}
		theme_options::set_options(self::$iden,$opt);
	}
	public static function get_ids(){
		return self::get_options('ids');
	}
	public static function is_enabled(){
		return self::get_options('enabled') == 1 ? true : false;
	}
	public static function display_backend(){
		$checked = self::is_enabled() ? ' checked ' : null;
		$recomm_posts = self::get_ids();
		?>
		<fieldset>
			<legend><?= ___('Recommended posts');?></legend>
			<p><?= ___('Recommended posts will display on home page if enabled.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th><?= ___('Enabled');?></th>
						<td>
							<label for="<?= self::$iden;?>-enabled">
								<input type="checkbox" name="<?= self::$iden;?>[enabled]" id="<?= self::$iden;?>-enabled" <?= $checked;?> value="1">
								<?= ___('Enabled');?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?= ___('Marked posts');?></th>
						<td>
							<?php
							if(!empty($recomm_posts)){
								global $post;
								$query = new WP_Query([
									'posts_per_page' => -1,
									'post__in' => $recomm_posts
								]);
								if($query->have_posts()){
									foreach($query->posts as $post){
										setup_postdata($post);
										?>
<label for="<?= self::$iden;?>-<?= $post->ID;?>" class="button">
	<input type="checkbox" id="<?= self::$iden;?>-<?= $post->ID;?>" name="<?= self::$iden;?>[ids][<?= $post->ID;?>]" value="<?= $post->ID;?>" checked >
	<?= esc_html(get_the_title($post->ID));?>
	-
	<a href="<?= esc_url(get_edit_post_link($post->ID));?>" target="_blank" title="<?= ___('Open in open window');?>"><i class="fa fa-external-link"></i></a>
</label>
										<?php
									}
									wp_reset_postdata();
								}else{
									echo status_tip('info',___('No any post yet'));
								}
							}else{
								echo status_tip('info',___('No any post yet'));
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
		if(isset($_POST[self::$iden])){
			$options[self::$iden] = $_POST[self::$iden];
		}
		return $options;
	}
}