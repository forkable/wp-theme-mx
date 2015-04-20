<?php
/**
 * User rank widget
 * 
 * @version 1.0.0
 */
add_action('widgets_init','widget_point_rank::register_widget');
class widget_point_rank extends WP_Widget{
	public static $iden = 'widget_point_rank';
	function __construct(){
		$this->alt_option_name = self::$iden;
		parent::__construct(
			self::$iden,
			___('User point rank <small>(custom)</small>'),
			array(
				'classname' => self::$iden,
				'description'=> ___('Display user point rank list'),
			)
		);
	}
	function widget($args,$instance){
		$instance = wp_parse_args(
			(array)$instance,
			array(
				'title'=> ___('User point rank'),
				'total_number' => 100,
				'rand_number' => 12
			)
		);
		
		if((int)$instance['total_number'] === 0 || (int)$instance['rand_number'] === 0)
			return false;

		
		echo $args['before_widget'];
		echo $args['before_title'];
		?>
		<i class="fa fa-bar-chart"></i> <?php echo $instance['title'];?>
		<?php 
		echo $args['after_title'];

		$users = wp_cache_get('widget',self::$iden);
		if(!$users){
			$user_query = new WP_User_Query([
				'meta_key' => theme_custom_point::$user_meta_key['point'],
				'orderby' => 'meta_value_num',
				'order' => 'desc',
				'number' => $instance['total_number'],
			]);
			$users = $user_query->results;
		}
		if(!$users){
			?>
			<div class="panel-body">
				<div class="page-tip"><?php echo status_tip('info',___('No matched user yet.'));?></div>
			</div>
			<?php
		}else{
			wp_cache_set('widget',$users,self::$iden,3600);
			/**
			 * rand
			 */
			$rand_users = array_rand($users,$instance['rand_number']);
			
			?>
			<div class="panel-body">
				<div class="user-lists row">
					<?php
					$user = null;
					foreach($rand_users as $k){ 
						//$user = $users[$k];
						theme_functions::the_user_list([
							'user' => $users[$k],
							'extra' => 'point',
						]);
					}
					?>
				</div>
			</div>
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
				'total_number' => 100,
				'rand_number' => 12
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