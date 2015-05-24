<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_post_source::init';
	return $fns;
});
class theme_custom_post_source{
	public static $iden = 'theme_custom_post_source';
	public static $post_meta_key = array(
		'key' => '_theme_custom_post_source'
	);
	public static function init(){
		add_action('add_meta_boxes', __CLASS__ . '::meta_box_add');
		add_action('save_post_post', __CLASS__ . '::meta_box_save');
	}
	public static function get_types($key = null){
		$types = array(
			'original' => array(
				'text' => ___('Original')
			),
			'reprint' => array(
				'text' => ___('Reprint'),
			)
		);
		if(empty($key)){
			return $types;
		}else{
			return isset($types[$key]) ? $types[$key] : null;
		}
	}
	public static function is_enabled(){
		return true;
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
				___('Post source'),
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
		$source = isset($new_meta['source']) ? $new_meta['source'] : null;
		
		if(!$source || !self::get_types($source))
			return;

		
		update_post_meta($post_id,self::$post_meta_key['key'],$new_meta);
		
	}
	public static function meta_box_display($post){
		$meta = self::get_post_meta($post->ID);
		//wp_nonce_field(self::$iden,self::$iden . '-nonce');
		$default_check = !isset($meta['source']) || $meta['source'] !== 'original' || $meta['source'] !== 'reprint' ? ' checked ' : null;
		?>
		<div class="<?= self::$iden;?>">
			<p>
				<label for="<?= self::$iden;?>-source-original">
					<input 
						type="radio" 
						name="<?= self::$iden;?>[source]" 
						id="<?= self::$iden;?>-source-original" 
						value="original" 
						<?= isset($meta['source']) && $meta['source'] === 'original' ? ' checked ' : null;?>
						<?= $default_check;?>
					>
					<?= self::get_types('original')['text'];?>
				</label>
				
				<label for="<?= self::$iden;?>-source-reprint">
					<input 
						type="radio" 
						name="<?= self::$iden;?>[source]" 
						id="<?= self::$iden;?>-source-reprint" 
						value="reprint" 
						<?= isset($meta['source']) && $meta['source'] === 'reprint' ? ' checked ' : null;?>
					>
					<?= self::get_types('reprint')['text'];?>
				</label>
			</p>
			<p>
				<input 
					type="url" 
					name="<?= self::$iden;?>[reprint][url]" 
					id="<?= self::$iden;?>-reprint-url" 
					class="widefat code" 
					title="<?= ___('Source URL, for reprint work');?>"
					placeholder="<?= ___('Source URL, for reprint work');?>"
					value="<?= isset($meta['reprint']['url']) ? esc_url($meta['reprint']['url']) : null;?>" 
				>
				<input 
					type="text" 
					name="<?= self::$iden;?>[reprint][author]" 
					id="<?= self::$iden;?>-reprint-author" 
					class="widefat code" 
					title="<?= ___('Author, for reprint work');?>"
					placeholder="<?= ___('Author, for reprint work');?>"
					value="<?= isset($meta['reprint']['author']) ? esc_attr($meta['reprint']['author']) : null;?>" 
				>
			</p>
		</div>			
		<?php
	}
	public static function display_frontend(){
		global $post;
		$meta = self::get_post_meta($post->ID);
		if(!isset($meta['source']))
			return false;
			
		?>
		<ul class="post-source hidden-xs">
			<?php
			switch($meta['source']){
				case 'original':
					?>
					<li><?php 
					echo sprintf(
						___('This article is %1$s member %2$s\'s original work.'),
						
						'<a href="' . home_url() . '">' .get_bloginfo('name') . '</a>',
						
						'<a href="' . theme_cache::get_author_posts_url($post->post_author) . '">' . esc_html(get_the_author_meta('display_name',$post->post_author)) . '</a>'
						
						);
						
					?></li>
					<li><?php 
					$permalink = esc_url(get_permalink());
					echo sprintf(
						___('Welcome to reprint but must indicate the source url %1$s.'),
						'<a href="' . $permalink . '" target="_blank" rel="nofollow">' . $permalink . '</a>'
					);?></li>
					<?php
					break;
				case 'reprint':
					$reprint_author = isset($meta['reprint']['author']) && !empty($meta['reprint']['author']) ? trim($meta['reprint']['author']) : ___('Unkown');
					
					$reprint_url = isset($meta['reprint']['url']) && !empty($meta['reprint']['url']) ? '<a href="' . esc_url($meta['reprint']['url']) . '" target="_blank" rel="nofollow">' . esc_url($meta['reprint']['url']) . '</a>' : ___('Unkown');
					?>
					<li><?php 
					echo sprintf(
						___('This article is %1$s member %2$s\'s reprint work.'),
						
						'<a href="' . home_url() . '">' .get_bloginfo('name') . '</a>',
						
						'<a href="' . theme_cache::get_author_posts_url($post->post_author) . '">' . esc_html(get_the_author_meta('display_name',$post->post_author)) . '</a>'
						
						);
						
					?></li>
					<li>
						<?= sprintf(
						___('Source: %s, author: %s.'),
						$reprint_url,
						$reprint_author);
						?>
					</li>
					<?php
					break;
			}
			?>
		</ul>
		<?php
	}
}
?>