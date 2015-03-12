<?php
/*
Feature Name:	theme-widget-adbox
Feature URI:	http://www.inn-studio.com
Version:		1.0.2
Description:	theme-widget-adbox
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/

add_action('widgets_init','widget_adbox::register_widget');
class widget_adbox extends WP_Widget{
	public static $iden = 'widget_adbox';
	function __construct(){
		$this->alt_option_name = self::$iden;
		parent::__construct(
			self::$iden,
			___('Advertisement code <small>(Custom)</small>'),
			array(
				'classname' => self::$iden,
				'description'=> ___('Show your advertisement.'),
			)
		);
	}
	function widget($args,$instance){
		$type = isset($instance['type']) ? $instance['type'] : 'desktop';
		$classes = $instance['type'] != 'desktop' ? 'hide-on-desktop' : 'hide-on-mobile';
		extract($args);
		echo $before_widget;
		?>
		<div class="adbox <?php echo $classes;?>">
			<?php echo stripslashes($instance['code']);?>
		</div>
		<?php
		echo $after_widget;
	}
	function form($instance){
		$instance = wp_parse_args(
			(array)$instance,
			array(
				'title' =>___('Advertisement'),
				'type' => 'all',
				'code' => null,
			)
		);
		?>
		<p>
			<label for="<?php echo esc_attr(self::get_field_id('type'));?>"><?php echo esc_html(___('Type'));?></label>
			<select 
				name="<?php echo esc_attr(self::get_field_name('type'));?>" 
				class="widefat"
				id="<?php echo esc_attr(self::get_field_id('type'));?>"
			>
				<?php echo get_option_list('all',___('All'),$instance['type']);?>
				<?php echo get_option_list('desktop',___('Desktop'),$instance['type']);?>
				<?php echo get_option_list('mobile',___('Mobile'),$instance['type']);?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr(self::get_field_id('code'));?>"><?php echo esc_html(___('Code'));?></label>
			<textarea 
				name="<?php echo esc_attr(self::get_field_name('code'));?>" 
				id="<?php echo esc_attr(self::get_field_id('code'));?>" 
				cols="30" 
				rows="10" 
				class="widefat"
			><?php echo stripslashes($instance['code']);?></textarea>
			
		</p>
		<?php
	}
	function update($new_instance,$old_instance){
		$instance = wp_parse_args($new_instance,$old_instance);
		return $instance;
	}
	public static function register_widget(){
		register_widget(self::$iden);
	}

}
