<?php
/*
Feature Name:	Related Post
Feature URI:	http://www.inn-studio.com
Version:		1.0.1
Description:	Display the related post below your article.<br/>在您的日志下方显示相关文章内容
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
//add_filter('theme_includes',function($fns){
//	$fns[] = 'theme_related_post::init';
//	return $fns;
//});
class theme_related_post{
	public static $iden = 'theme_related_post';

	public static function init(){
		// add_action('save_post',__CLASS__ . '::flush_cache');
		// add_action('deleted_post',__CLASS__ . '::flush_cache');
		// add_action('switch_theme',__CLASS__ . '::flush_cache');
		add_action('page_settings',__CLASS__ . '::admin');
		add_filter('theme_options_default',__CLASS__ . '::options_default');
		add_filter('theme_options_save',__CLASS__ . '::save');

	}
	public static function admin(){
		
		$options = theme_options::get_options();
		?>
		<h3><?php echo ___('Related Post Settings');?></h3>
		<p class="description"><?php echo ___('The related posts will be displayed below the post content and some settings in here.');?></p>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="related_post_title"><?php echo ___('Related post title:');?></label></th>
					<td>
						<input id="related_post_title" name="related_post_title" type="text" class="regular-text" value="<?php echo $options['related_post_title'];?>" title="<?php echo ___('Related post title');?>" />
						<span class="description"><?php echo ___('For example: Maybe you will like them');?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="related_post_num"><?php echo ___('How many posts to show:');?></label></th>
					<td>
						<input id="related_post_num" name="related_post_num" type="number" class="regular-text" value="<?php echo $options['related_post_num'];?>" title="<?php echo ___('Related post number');?>" />
						<span class="description"><?php echo ___('For example: 10');?></span>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}
	public static function options_default($options_default){
		
		$options_default['related_post_title'] = ___('Maybe you will like them');
		$options_default['related_post_num'] = 10;
		return $options_default;
	}
	public static function save($options){
		$options['related_post_title'] = trim($_POST['related_post_title']);
		$options['related_post_num'] = (int)trim($_POST['related_post_num']);
		return $options;
	}
	public static function flush_cache(){
		wp_cache_delete($post->ID,'theme_related_post');
	}
	public static function get_posts($args = null){
		global $post;
		$current_post = $post;
		$options = theme_options::get_options();

		$defaults = array(
			'posts_per_page' => isset($options['related_post_num']) ? (int)$options['related_post_num'] : 6 ,
		);
		$r = array_merge($defaults,$args);
		extract($r);
		/**
		 * get the cache
		 */
		$posts = (array)wp_cache_get($current_post->ID,'theme_related_post');
		if(!is_null_array($posts)) return $posts;
		$tags = wp_get_post_tags($current_post->ID);
		$tags_len = count($tags);
		$surprise_num = $posts_per_page;
		$found_posts = 0;
		/* 存在tags */
		if($tags_len){
			for($i=0;$i<$tags_len;$i++){
				$tags_array[] = $tags[$i]->term_id;
				
			}
			$query_args = array(
				'tag__in' => $tags_array,
				'post__not_in' => array($current_post->ID),
				'posts_per_page' => $surprise_num,
			);
			$query = new WP_Query($query_args);
			if($query->have_posts()){
				while($query->have_posts()){
					$query->the_post();
					$posts[] = $post;
				}
			}
			$found_posts = $query->found_posts;/* 发现到的文章数量 */
		}
		$surprise_num = $surprise_num - $found_posts;/* 计算剩余文章数量 */

		/* 当剩余文章大于0时候，调用分类目录中的文章来补充 */
		if($surprise_num > 0 ){
			$args = array(
				'category__in' => array(theme_features::get_current_cat_id()),
				'post__not_in' => array($current_post->ID),
				'posts_per_page' => $surprise_num,
			);
			$query = new WP_Query($args);
			if($query->have_posts()){
				while($query->have_posts()){
					$query->the_post();
					$posts[] = $post;
				}
			}
			$found_posts = $query->found_posts;/* 发现到的文章数量 */
			$surprise_num = $surprise_num - $found_posts;/* 计算剩余文章数量 */
		}
		$posts = array_filter($posts);

		wp_cache_set($current_post->ID,$posts,'theme_related_post');
		//wp_reset_query();
		wp_reset_postdata();
		
		return $posts;
	}
}
?>