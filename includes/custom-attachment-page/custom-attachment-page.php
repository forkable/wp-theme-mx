<?php
/**
 * @version 1.0.0
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_attachment::init';
	return $fns;
});
class theme_custom_attachment{

	public static function init(){

		add_filter('the_content', __CLASS__ . '::filter_the_content');
		/**
		 * frontend
		 */
		add_action('frontend_seajs_alias', __CLASS__ . '::frontend_seajs_alias');
		add_action('frontend_seajs_use', __CLASS__ . '::frontend_seajs_use');
		add_action('wp_enqueue_scripts', __CLASS__ . '::frontend_css');
	}
	public static function filter_the_content($content){
		if(!theme_cache::is_attachment())
			return $content;
		global $post;
		$post_title = theme_cache::get_the_title($post->post_parent);
		//var_dump($post);
		if(!wp_attachment_is_image($post->ID))
			return $content;
			
		$current_img_full = wp_get_attachment_image_src($post->ID,'full');
		$current_img_thumbnail = wp_get_attachment_image_src($post->ID,'thumbnail');
		
		$children = get_children([
			'post_parent' => $post->post_parent,
			'post_status' => 'inherit',
			'post_type' => 'attachment',
			'order' => 'ASC',
		]);
		
		$count = count($children);
		$child_posts = [];
		
		foreach($children as $child){
			$child_img = wp_get_attachment_image_src($child->ID,'thumbnail');
			$child_posts[] = [
				'id' => $child->ID,
				'permalink' => theme_cache::get_permalink($child->ID) . '#main',
				'src' => $child_img[0],
				'w' => $child_img[1],
				'h' => $child_img[2],
			];
		}
		//unset($children);
		unset($child_img);

		ob_start();
		?>
		<div class="attachment-slide">
			<div class="attachment-slide-content">
				<?php
				if($post->ID !== $child_posts[$count - 1]['id']){
					$url_next = $img_full['permalink'];
					$title_next = ___('Next page');
				}else{
					$url_next = 'javascript:;';
					$title_next = ___('Already last page');
				}
				?>
				<a href="<?= $url_next;?>" title="<?= $title_next;?>">
					<img src="<?= $current_img_full[0];?>" alt="" width="<?= $current_img_full[1];?>" height="<?= $current_img_full[2];?>">
				</a>
			</div>
			<div class="attachment-slide-thumbnail">
				<?php
				foreach($child_posts as $child){
					$child_img = wp_get_attachment_image_src($child->ID,'thumbnail');
					$class_active = $post->ID === $child['id'] ? 'active' : null;
					?>
					<a class="<?= $class_active;?>" href="<?= theme_cache::get_permalink($child['id']);?>#main">
						<img src="<?= $child['src'];?>" alt="<?= $post_title;?>" width="<?= $child['w'];?>" height="<?= $child['h'];?>">
					</a>
				<?php } ?>
			</div>
		</div>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	public static function frontend_seajs_alias(array $alias = []){
		if(!theme_cache::is_attachment())
			return $alias;
			
		$alias[__CLASS__] = theme_features::get_theme_includes_js(__DIR__);

		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!theme_cache::is_attachment())
			return false;
		?>
		seajs.use('<?= __CLASS__;?>',function(m){
			m.init();
		});
		<?php
	}
	public static function frontend_css(){
		if(!theme_cache::is_attachment())
			return false;
		wp_enqueue_style(
			__CLASS__,
			theme_features::get_theme_includes_css(__DIR__),
			'frontend',
			theme_file_timestamp::get_timestamp()
		);
	}
}