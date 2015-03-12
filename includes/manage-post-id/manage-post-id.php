<?php
/*
Plugin Name: theme_manage_post_id
Plugin URI: http://www.inn-studio.com/theme_manage_post_id
Description: To show the post ID on posts/pages list table.
Author: INN STUDIO
Version: 1.0.0
Text Domain:theme_manage_post_id
Domain Path:/languages
Author URI: http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_manage_post_id::init';
	return $fns;
});
class theme_manage_post_id{
	public static function init(){
		add_action('manage_posts_custom_column',get_class() . '::column_display',10,2);
		add_action('manage_pages_custom_column',get_class() . '::column_display',10,2);
		add_action('admin_head', get_class() . '::admin_css');
		
		add_filter('manage_posts_columns',get_class() . '::columns_add');
		add_filter('manage_pages_columns',get_class() . '::columns_add');
	}
	public static function admin_css(){
		?><style>.fixed .column-post_id{width:3em}</style><?php
	}
	public static function columns_add($columns){
		$columns['post_id'] = 'ID';
		return $columns;
	}
	public static function column_display($column,$post_id){
		if ($column == 'post_id') {
			echo $post_id;
		}
	}
}


?>