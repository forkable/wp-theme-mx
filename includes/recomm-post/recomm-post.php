<?php
/**
 * theme recommended post
 *
 * @version 2.0.6
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_recommended_post::init';
	return $fns;
});
class theme_recommended_post{
	
	public static function init(){
		add_action('add_meta_boxes',__CLASS__ . '::add_meta_boxes');
		add_action('page_settings',__CLASS__ . '::display_backend');
		add_filter('theme_options_save',__CLASS__ . '::options_save');
		add_filter('theme_options_default',__CLASS__ . '::opttions_default');
		
		add_action('save_post',__CLASS__ . '::save_post');
		add_action('delete_post',__CLASS__ . '::delete_post');
	}
	public static function add_meta_boxes(){
		$screens = array('post');

		foreach ( $screens as $screen ) {
			add_meta_box(
				__CLASS__,
				___( 'Recommended post' ),
				__CLASS__ . '::box_display',
				$screen,
				'side'
			);
		}
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(__CLASS__);

		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches;
	}
	public static function box_display($post){
	
		wp_nonce_field(__CLASS__,__CLASS__ . '-nonce' );

		$recomm_posts = self::get_ids();

		$checked = in_array($post->ID,$recomm_posts) ? ' checked ' : null;
		$btn_class = $checked ? ' button-primary ' : null;
		?>
		<label for="<?= __CLASS__;?>-set" class="button widefat <?= $btn_class;?>">
			<input type="checkbox" id="<?= __CLASS__;?>-set" name="<?= __CLASS__;?>" value="1" <?= $checked;?> />
			<?= ___('Set as recommended post');?>
		</label>
		<?php
	}
	public static function delete_post($post_id){
		if ( !theme_cache::current_user_can( 'delete_posts' ) )
			return;
		$opt = self::get_options();
		$recomm_posts = isset($opt['ids']) ? (array)$opt['ids'] : [];
		$k = array_search($post_id,$recomm_posts);
		
		if($k !== false){
			unset($opt['ids'][$k]);
			sort($opt['ids']);
			theme_options::set_options(__CLASS__,$opt);
		}
	}
	public static function opttions_default(array $opts = []){
		$opts[__CLASS__]['enabled'] = 1;
		return $opts;
	}
	public static function save_post($post_id){
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return false;

		if(!isset($_POST[__CLASS__ . '-nonce']) || !wp_verify_nonce($_POST[__CLASS__ . '-nonce'], __CLASS__)) 
			return false;

		$opt = self::get_options();
		
		if(!isset($opt['ids']))
			$opt['ids'] = [];
		/**
		 * set to recomm
		 */
		if(isset($_POST[__CLASS__])){
			if(!isset($opts['ids'][$post_id])){
				$opt['ids'][$post_id] = $post_id;
			}
		}else{
			if(isset($opt['ids'][$post_id])){
				unset($opt['ids'][$post_id]);
			}
		}
		theme_options::set_options(__CLASS__,$opt);
	}
	public static function get_ids(){
		return (array)self::get_options('ids');
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
							<label for="<?= __CLASS__;?>-enabled">
								<input type="checkbox" name="<?= __CLASS__;?>[enabled]" id="<?= __CLASS__;?>-enabled" <?= $checked;?> value="1">
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
<label for="<?= __CLASS__;?>-<?= $post->ID;?>" class="button">
	<input type="checkbox" id="<?= __CLASS__;?>-<?= $post->ID;?>" name="<?= __CLASS__;?>[ids][<?= $post->ID;?>]" value="<?= $post->ID;?>" checked >
	<?= theme_cache::get_the_title($post->ID);?>
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
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}
		return $opts;
	}
}