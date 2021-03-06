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
	function widget($args = [], $instance = []){
		$instance = array_merge([
			'title'=> ___('User point rank'),
			'total_number' => 100,
			'rand_number' => 12
		],$instance);
		
		if((int)$instance['total_number'] === 0 || (int)$instance['rand_number'] === 0)
			return false;

		
		echo $args['before_widget'];
		echo $args['before_title'];
		?>
		<i class="fa fa-bar-chart"></i> <?= $instance['title'];?>
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
				<div class="page-tip"><?= status_tip('info',___('No matched user yet.'));?></div>
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
							'extra_title' => sprintf(
								__x('%s %s','eg. 20 points'),
								'%',
								theme_custom_point::get_point_name()
							),
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
	function form($instance = []){
		$instance = array_merge([
			'title'=> ___('User point rank'),
			'total_number' => 100,
			'rand_number' => 12
		],$instance);
		?>
		<p>
			<label for="<?= self::get_field_id('title');?>"><?= ___('Title (optional)');?></label>
			<input 
				id="<?= self::get_field_id('title');?>"
				class="widefat"
				name="<?= self::get_field_name('title');?>" 
				type="text" 
				value="<?= esc_attr($instance['title']);?>" 
				placeholder="<?= ___('Title (optional)');?>"
			/>
		</p>
		<p>
			<label for="<?= self::get_field_id('total_number');?>"><?= ___('Total number');?></label>
			<input 
				id="<?= self::get_field_id('total_number');?>"
				class="widefat"
				name="<?= self::get_field_name('total_number');?>" 
				type="number" 
				value="<?= esc_attr($instance['total_number']);?>" 
				placeholder="<?= ___('Total number');?>"
			/>
		</p>
		<p>
			<label for="<?= self::get_field_id('rand_number');?>"><?= ___('Random number');?></label>
			<input 
				id="<?= self::get_field_id('rand_number');?>"
				class="widefat"
				name="<?= self::get_field_name('rand_number');?>" 
				type="number" 
				value="<?= esc_attr($instance['rand_number']);?>" 
				placeholder="<?= ___('Random number');?>"
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
		return array_merge($old_instance,$new_instance);
	}
	public static function register_widget(){
		register_widget(self::$iden);
	}
}