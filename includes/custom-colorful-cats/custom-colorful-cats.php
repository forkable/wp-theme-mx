<?php
/*
Feature Name:	theme_colorful_cats
Feature URI:	http://www.inn-studio.com
Version:		2.0.0
Description:	theme_colorful_cats
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
theme_colorful_cats::init();
class theme_colorful_cats{
	public static $iden = 'theme_colorful_cats';
	public static $colors = array(
		'61b4ca',	'e1b32a',	'ee916f',	'a89d84',
		'86b767',	'6170ca',	'c461ca',	'ca6161',
		'ca8661',	'333333',	'84a89e',	'a584a8'
	);
	public static function init(){
		add_action('after_backend_tab_init',__CLASS__ . '::backend_js'); 
		add_action('backend_seajs_alias',__CLASS__ . '::backend_seajs_alias'); 
		add_action('backend_css',__CLASS__ . '::backend_css'); 
		add_action('page_settings',__CLASS__ . '::display_backend');
		add_filter('theme_options_save',__CLASS__ . '::options_save');
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(__CLASS__);
		
		if(empty($key)){
			return $caches;
		}else{
			return isset($caches[$key]) ? $caches[$key] : false;
		}
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}
		return $opts;
	}
	public static function get_rand_color(){
		return self::$colors[array_rand(self::$colors,1)];
	}
	public static function get_cat_color($cat_id,$rgb = false){
		static $caches = null;
		$cache_id = $cat_id . $rgb;
		if(!isset($caches[$cache_id])){
			$colors = (array)self::get_options('cat-color');
			$rand_color = self::get_rand_color();
			if($rgb)
				$rand_color = self::hex2rgb($rand_color);
			$caches[$cache_id] = isset($colors[$cache_id]) ? $colors[$cache_id] : $rand_color;
		}
		return $caches[$cache_id];
	}
	public static function hidden_inputs(array $cats){
		foreach($cats as $cat){ 
			?>
			<input class="<?= __CLASS__;?>-cat-color" type="hidden" name="<?= __CLASS__;?>[cat-color][<?= $cat->term_id;?>]" id="<?= __CLASS__;?>-cat-color-<?= $cat->term_id;?>" value="<?= self::get_cat_color($cat->term_id);?>">
		<?php 
		}
	}
	public static function the_preset_colors(){
		foreach(self::$colors as $color){
			?>
			<a href="javascript:;" data-color="<?= $color;?>" style="background-color:#<?= $color;?>;">T</a>
		<?php
		}
	}
	public static function display_backend(){
		$color_cats = (array)self::get_options();
		/** 
		 * get all categories
		 */
		$cats = get_categories(array(
			'orderby' => 'id',
			'hide_empty' => false,
		));
		?>
		<fieldset>
			<legend><?= ___('Colorful category');?></legend>
			<p class="description">
				<?= ___('You can select the category and set color for category. Preset is random color.');?>
			</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<?= ___('Select category and select color to set');?>
						</th>
						<td id="colorful-cats">
							<?php
							if(!$cats){
								echo status_tip('info',___('Empty category.'));
							}else{
								wp_dropdown_categories([
									'show_option_none' => ___('Select category'),
									'show_count' => 1,
									'id' => __CLASS__ . '-cat-ids',
									'name' => '',
									'hierarchical' => 1,
								]);
							}
							?>
							<span id="<?= __CLASS__;?>-preset-colors">
								<?php self::the_preset_colors();?>
							</span>
							
							<?php self::hidden_inputs($cats);?>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	}
	/**
	 * author Jake http://stackoverflow.com/users/1895094/jake
	 */
	private static function hex2rgb($hex, $alpha = false) {
		if ( substr($hex, 0, 1) == '#' ) {
			$hex = substr($hex, 1);
		}
		if ( strlen($hex) == 6 ) {
			$rgb['r'] = hexdec(substr($hex, 0, 2));
			$rgb['g'] = hexdec(substr($hex, 2, 2));
			$rgb['b'] = hexdec(substr($hex, 4, 2));
		}
		else if ( strlen($hex) == 3 ) {
			$rgb['r'] = hexdec(str_repeat(substr($hex, 0, 1), 2));
			$rgb['g'] = hexdec(str_repeat(substr($hex, 1, 1), 2));
			$rgb['b'] = hexdec(str_repeat(substr($hex, 2, 1), 2));
		}
		else {
			$rgb['r'] = '0';
			$rgb['g'] = '0';
			$rgb['b'] = '0';
		}
		if ( $alpha ) {
			$rgb['a'] = $alpha;
		}
		return $rgb;
	}
	public static function backend_css(){
		?>
		<link href="<?= theme_features::get_theme_includes_css(__DIR__,'backend',true,true);?>" rel="stylesheet"  media="all"/>
		<?php
	}
	public static function backend_seajs_alias(array $alias = []){
		$alias[__CLASS__] = theme_features::get_theme_includes_js(__DIR__,'backend.js');
		return $alias;
	}
	public static function backend_js(){
		?>
		seajs.use('<?= __CLASS__;?>',function(m){
			m.init();
		});
		<?php
	}
}

?>
