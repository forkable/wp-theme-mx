<?php
/** 
 * version 1.0.0
 */

add_action('widgets_init','widget_author_posts::register_widget' );
class widget_author_posts extends WP_Widget{
	public static $iden = 'widget_author_posts';
	function __construct(){
		$this->alt_option_name = self::$iden;
		parent::__construct(
			self::$iden,
			___('Author posts <small>(custom)</small>'),
			array(
				'classname' => self::$iden,
				'description'=> ___('Display author posts on singular page.'),
			)
		);
	}
	public static function frontend_display(array $args = [],$instance){
		global $post;
		$instance = array_merge([
			'title' => ___('Author posts'),
			'posts_per_page' => 12,
			'orderby' => 'random',
			'category__in' => [],
			'content_type' => 'img',
		],$instance);
		$title = $instance['title'];
		echo $args['before_title'];
			?>
			<a href="<?= class_exists('theme_custom_author_profile') ? theme_custom_author_profile::get_tabs('works',$post->post_author)['url'] : theme_cache::get_author_posts_url($post->post_author);?>" title="<?= ___('Views more author posts.');?>">
				<i class="fa fa-file-text"></i> 
				<?= sprintf($title,theme_cache::get_the_author_meta('display_name',$post->post_author));?>
			</a>
			<?php
		echo $args['after_title'];
		
		$query = new WP_Query([
			'category__in' => (array)$instance['category__in'],
			'posts_per_page' => (int)$instance['posts_per_page'],
			'orderby' => $instance['orderby'],
			'author' => $post->post_author,
			'post_not__in' => [$post->ID],
		]);
		$content_type_class = $instance['content_type'] === 'tx' ? ' post-tx-lists ' : ' post-img-list ';
		?>
		<div class="panel-body">
			<?php
			if($query->have_posts()){
				?>
				<ul class="row post-img-lists widget-author-post-<?= $instance['orderby'];?>">
					<?php
					foreach($query->posts as $post){
						setup_postdata($post);
						theme_functions::archive_img_content([
							'classes' => ['col-xs-6 col-sm-4 col-md-6']
						]);
					}
					wp_reset_postdata();
					?>
				</ul>
			<?php }else{ ?>
				<div class="page-tip not-found">
					<?= status_tip('info',___('No data yet.'));?>
				</div>
			<?php } ?>
		</div>
		<?php
	}
	function widget($args,$instance){
		echo $args['before_widget'];
		self::frontend_display($args,$instance);
		echo $args['after_widget'];
	}
	
	function form($instance = []){
		$instance = array_merge([
			'title'=>___('Author posts'),
			'posts_per_page' => 6,
			'category__in' => [],
			'content_type' => 'img',
			'orderby' => 'random',
		],$instance);
		?>
		<p>
			<label for="<?= self::get_field_id('title');?>"><?= ___('Title (optional)');?></label>
			<input 
				id="<?= self::get_field_id('title');?>"
				class="widefat"
				name="<?= self::get_field_name('title');?>" 
				type="text" 
				value="<?= $instance['title'];?>" 
				placeholder="<?= ___('Title (optional)');?>"
			/>
		</p>
		<p>
			<label for="<?= self::get_field_id('posts_per_page');?>"><?= ___('Post number (required)');?></label>
			<input 
				id="<?= self::get_field_id('posts_per_page');?>"
				class="widefat"
				name="<?= self::get_field_name('posts_per_page');?>" 
				type="number" 
				value="<?= $instance['posts_per_page'];?>" 
				placeholder="<?= ___('Post number (required)');?>"
			/>
		</p>
		<p>
			<?= ___('Categories: ');?>
			<?= self::get_cat_checkbox_list(
				self::get_field_name('category__in'),
				self::get_field_id('category__in'),
				$instance['category__in']
			);?>
		</p>

		<p>
			<label for="<?= self::get_field_id('content_type');?>"><?= ___('Content type');?></label>
			<select 
				name="<?= self::get_field_name('content_type');?>" 
				class="widefat"
				id="<?= self::get_field_id('content_type');?>"
			>
				<?php
				/** 
				 * image type
				 */
				echo get_option_list('img',___('Image type'),$instance['content_type']);
				
				/** 
				 * text type
				 */
				echo get_option_list('tx',___('Text type'),$instance['content_type']);?>
			</select>
		</p>
		<p>
			<label for="<?= self::get_field_id('orderby');?>">
				<?= ___('Order by');?>
			</label>
			<select 
				name="<?= self::get_field_name('orderby');?>" 
				class="widefat"
				id="<?= self::get_field_id('orderby');?>"
			>
				
				<?php
				
				/** 
				 * orderby views
				 */
				if(class_exists('theme_post_views') && theme_post_views::is_enabled()){
					echo get_option_list('views',___('Most views'),$instance['orderby']);
				}
				
				/** 
				 * orderby thumb-up
				 */
				if(class_exists('theme_post_thumb') && theme_post_thumb::is_enabled()){
					echo get_option_list('thumb-up',___('Thumb up'),$instance['orderby']);
				}
				
				/** 
				 * orderby recommended
				 */
				if(class_exists('theme_recommended_post')){
					echo get_option_list('recommended',___('Recommended'),$instance['orderby']);
				}
				/** 
				 * orderby random
				 */
				echo get_option_list('random',___('Random'),$instance['orderby']);
				
				/** 
				 * orderby latest
				 */
				echo get_option_list('latest',___('Latest'),$instance['orderby']);
				
				?>
			</select>
		</p>
		<?php
	}
	/**
	 * 
	 *
	 * @param 
	 * @return 
	 * @version 1.0.0
	 */
	private static function get_cat_checkbox_list($name,$id,$selected_cat_ids = []){
		$cats = get_categories(array(
			'hide_empty' => false,
			'orderby' => 'term_group',
			'exclude' => '1',
		));
		
		ob_start();
		if($cats){
			foreach($cats as $cat){
				if(in_array($cat->term_id,(array)$selected_cat_ids)){
					$checked = ' checked="checked" ';
					$selected_class = ' button-primary ';
				}else{
					$checked = null;
					$selected_class = null;
				}
			?>
			<label for="<?= $id;?>-<?= $cat->term_id;?>" class="item button <?= $selected_class;?>">
				<input 
					type="checkbox" 
					id="<?= $id;?>-<?= $cat->term_id;?>" 
					name="<?= esc_attr($name);?>[]" 
					value="<?= $cat->term_id;?>"
					<?= $checked;?>
				/>
					<?= esc_html($cat->name);?>
			</label>
			<?php 
			}
		}else{ ?>
			<p><?= ___('No category, pleass go to add some categories.');?></p>
		<?php }
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	function update($new_instance,$old_instance){
		return array_merge($old_instance,$new_instance);
	}
	public static function register_widget(){
		register_widget(self::$iden);
	}
}