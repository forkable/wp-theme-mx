<?php

add_filter('theme_includes',function($fns){
	$fns[] = 'theme_group_order::init';
	return $fns;
});
class theme_group_order {
	private static $iden = 'theme-group-order';
	private static $form_field_name = 'term-group-order';
	private static $taxonomies;

	
	public static function add_column_header ($columns) {
		$columns['term_group'] = ___('Group');
		
		return $columns;
 		
	}
	
	public static function add_column_value ($empty = '', $custom_column, $term_id) {
		
		$taxonomy = (isset($_POST['taxonomy'])) ? $_POST['taxonomy'] : $_GET['taxonomy'];
		
		$term = get_term($term_id, $taxonomy);
		
		return $term->$custom_column;
		
	}
	
	public static function add_edit_term_group ($term_id) {
		
		global $wpdb;
		
		if (isset($_POST[self::$form_field_name])) {
			
			$wpdb->update($wpdb->terms, array('term_group' => $_POST[self::$form_field_name]), array('term_id' => $term_id));
			
		}
		
	}
	
	public static function term_group_add_form_field () {
		
		$form_field = '<div class="form-field"><label for="' . self::$form_field_name . '">' . ___('Group') . '</label><input name="' . self::$form_field_name . '" id="' . self::$form_field_name . '" type="text" value="0" size="10" /><p>' . ___('You can give a group number for similar categories or tags. For example: Red, green, blue can be group 1 of categories. Small, medium, large can be group 2 of categories.') . '</p></div>';
		
		echo $form_field;
		
	}
	
	public static function term_group_edit_form_field ($term) {
		
		$form_field = '<tr class="form-field"><th scope="row" valign="top"><label for="' . self::$form_field_name . '">' . ___('Group')  . '</label></th><td><input name="' . self::$form_field_name . '" id="' . self::$form_field_name . '" type="text" value="' . $term->term_group . '" size="10" /><p class="description">' . ___('You can give a group number for similar categories or tags. For example: Red, green, blue can be group 1 of categories. Small, medium, large can be group 2 of categories.') .'</p></td></tr>';
		
		echo $form_field;
		
	}
	
	public static function quick_edit_term_group () {
	
		
		$term_group_field = '<fieldset><div class="inline-edit-col"><label><span class="title">' . ___( 'Group' ) . '</span><span class="input-text-wrap"><input class="ptitle" name="'. self::$form_field_name . '" type="text" value="" /></span></label></div></fieldset>';
		
		$term_group_field .= '<script type="text/javascript">
		
		</script>';
		
		echo $term_group_field;
		
	}
	
	public static function init () {
		
		self::$taxonomies = get_taxonomies();
		
		foreach (self::$taxonomies as $key => $value) {
			
			add_filter("manage_edit-{$value}_columns", get_class() . '::add_column_header');
			add_filter("manage_{$value}_custom_column", get_class() . '::add_column_value' ,10, 3);
			
			add_action("{$value}_add_form_fields", get_class() . '::term_group_add_form_field');
			add_action("{$value}_edit_form_fields", get_class() . '::term_group_edit_form_field');
			
		}
		
		add_filter("manage_edit-tags_columns", get_class() . '::add_column_header');
		
		add_action('create_term', get_class() . '::add_edit_term_group');
		
		add_action('edit_term', get_class() . '::add_edit_term_group');
		add_action('quick_edit_custom_box', get_class() . '::quick_edit_term_group', 10, 3);
		
		
	}
}

?>
