<?php
/**
 * @version 1.0.0
 */
add_action('widgets_init','widget_most_fav_users::register_widget');
class widget_most_fav_users extends WP_Widget{
	public static $iden = 'widget_most_fav_users';
	function __construct(){
		$this->alt_option_name = self::$iden;
		parent::__construct(
			self::$iden,
			___('Most popular users rank <small>(Custom)</small>'),
			array(
				'classname' => self::$iden,
				'description'=> ___('Display most popular users rank list'),
			)
		);
	}
	function widget($args,$instance){
		$instance = wp_parse_args(
			(array)$instance,
			array(
				'title'=> ___('Most popular users'),
				'total_number' => 20,
				'display_number' => 6
			)
		);
		
		if((int)$instance['total_number'] === 0 || (int)$instance['display_number'] === 0)
			return false;

		
		echo $args['before_widget'];
		echo $args['before_title'];
		?>
		<i class="fa fa-users"></i> <?php echo $instance['title'];?>
		<?php 
		echo $args['after_title'];


		
		$users = custom_post_fav::get_most_fav_users();
		
		if(empty($users)){
			?>
			<div class="panel-body">
				<div class="page-tip"><?php echo status_tip('info',___('No matched user yet.'));?></div>
			</div>
			<?php
		}else{
			?>
			<ul class="list-group">
				<?php
				foreach($users as $user){ 
					?>
					<li class="list-group-item">
						<a href="<?php echo get_author_posts_url($user->ID);?>" class="media">
							<div class="number"><?php echo theme_custom_point::get_point($user->ID);?></div>
							<div class="media-left">
								<?php echo get_avatar($user->ID,50);?>
							</div>
							<div class="media-body">
								<h4 class="media-heading"><?php echo esc_html($user->display_name);?></h4>
								<div class="description">
									<?php
									$des = get_user_meta($user->ID,'description',true);
									if(empty($des)){
										echo ___('No description is description.');
									}else{
										echo esc_html($des);
									}
									?>
									</div>
							</div>
						</a>
					</li>
				<?php } ?>
			</ul>
			<?php
		}
		?>

		<?php
		echo $args['after_widget'];
	}
	function form($instance){
		$instance = wp_parse_args(
			(array)$instance,
			array(
				'title'=> ___('User point rank'),
				'total_number' => 20,
				'rand_number' => 6
			)
		);
		?>
		<p>
			<label for="<?php echo self::get_field_id('title');?>"><?php echo ___('Title (optional)');?></label>
			<input 
				id="<?php echo self::get_field_id('title');?>"
				class="widefat"
				name="<?php echo self::get_field_name('title');?>" 
				type="text" 
				value="<?php echo esc_attr($instance['title']);?>" 
				placeholder="<?php echo ___('Title (optional)');?>"
			/>
		</p>
		<p>
			<label for="<?php echo self::get_field_id('total_number');?>"><?php echo ___('Total number');?></label>
			<input 
				id="<?php echo self::get_field_id('total_number');?>"
				class="widefat"
				name="<?php echo self::get_field_name('total_number');?>" 
				type="number" 
				value="<?php echo esc_attr($instance['total_number']);?>" 
				placeholder="<?php echo ___('Total number');?>"
			/>
		</p>
		<p>
			<label for="<?php echo self::get_field_id('rand_number');?>"><?php echo ___('Random number');?></label>
			<input 
				id="<?php echo self::get_field_id('rand_number');?>"
				class="widefat"
				name="<?php echo self::get_field_name('rand_number');?>" 
				type="number" 
				value="<?php echo esc_attr($instance['rand_number']);?>" 
				placeholder="<?php echo ___('Random number');?>"
			/>
		</p>
		
		<?php
	}
	function update($new_instance,$old_instance){
		if(isset($old_instance['total_number']) && 
			isset($old_instance['rand_number']) && 
			(int)$old_instance['total_number'] < $old_instance['rand_number']){
			$old_instance['rand_number'] = (int)$old_instance['total_number'];
		}
		return wp_parse_args($new_instance,$old_instance);
	}
	public static function register_widget(){
		register_widget(self::$iden);
	}
}