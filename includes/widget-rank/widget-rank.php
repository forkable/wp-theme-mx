<?php
/** 
 * version 1.0.5
 */

add_action('widgets_init','widget_rank::register_widget' );
class widget_rank extends WP_Widget{
	public static $iden = 'widget_rank';
	function __construct(){
		$this->alt_option_name = self::$iden;
		parent::__construct(
			self::$iden,
			___('Posts rank <small>(custom)</small>'),
			array(
				'classname' => self::$iden,
				'description'=> ___('Posts ranking'),
			)
		);
	}
	public static function frontend_display(array $args = [],$instance){
		$instance = array_merge([
			'title' => ___('Posts rank'),
			'posts_per_page' => 6,
			'date' => 'all',
			'orderby' => 'views',
			'category__in' => [],
			'content_type' => 'tx',
		],$instance);
		$title = esc_html($instance['title']);
		echo $args['before_title'];
		if(isset($instance['category__in'][0])){ ?>
			<a class="link" href="<?= get_category_link($instance['category__in'][0]);?>" title="<?= sprintf(___('Views more about %s'),$title);?>">
				<i class="fa fa-bar-chart"></i> 
				<?= $title;?>
			</a>
			<a href="<?= get_category_link($instance['category__in'][0]);?>" title="<?= sprintf(___('Views more about %s'),$title);?>" class="more"><?= ___('More &raquo;');?></a>
		<?php }else{ ?>
			<i class="fa fa-bar-chart"></i> 
			<?= $title;?>
		<?php } ?>
		<?php
		echo $args['after_title'];
		
		global $post;
		$query = theme_functions::get_posts_query(array(
			'category__in' => (array)$instance['category__in'],
			'posts_per_page' => (int)$instance['posts_per_page'],
			'date' => $instance['date'],
			'orderby' => $instance['orderby'],
		));
		$content_type_class = $instance['content_type'] === 'tx' ? ' post-tx-lists ' : ' post-mixed-lists ';
		
		if($query->have_posts()){
			?>
			<ul class="list-group <?= $content_type_class;?> widget-orderby-<?= $instance['orderby'];?>">
				<?php
				foreach($query->posts as $post){
					setup_postdata($post);
					if($content_type_class === 'tx'){
						theme_functions::widget_rank_tx_content(array(
							'meta_type' => $instance['orderby'],
						));
					}else{
						theme_functions::widget_rank_img_content();
					}
				}
				wp_reset_postdata();
				?>
			</ul>
		<?php }else{ ?>
			<div class="page-tip not-found">
				<?= status_tip('info',___('No data yet.'));?>
			</div>
		<?php 
		}
	}
	function widget($args,$instance){
		echo $args['before_widget'];
		self::frontend_display($args,$instance);
		echo $args['after_widget'];
	}
	
	function form($instance = []){
		$instance = array_merge([
			'title'=>___('Posts ranking'),
			'posts_per_page' => 6,
			'category__in' => [],
			'content_type' => 'tx',
			'orderby' => 'latest',
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
		<!-- date -->
		<p>
			<label for="<?= self::get_field_id('date');?>"><?= ___('Date');?></label>
			<select
				name="<?= self::get_field_name('date');?>" 
				class="widefat"				
				id="<?= self::get_field_id('date');?>"
			>
				<?php
				foreach(self::get_rank_data() as $k => $v){
					echo get_option_list($k,$v,$instance['date']);
				}
				?>
			</select>
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
	public static function get_rank_data($key = null){
		$content = [
			'all' 			=> ___('All'),
			'daily' 		=> ___('Daily'),
			'weekly' 		=> ___('Weekly'),
			'monthly' 		=> ___('Monthly'),
		];
		if($key) 
			return isset($content[$key]) ? $content[$key] : false;
		return $content;
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