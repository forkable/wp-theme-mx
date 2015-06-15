<?php
/*
Feature Name:	theme-custom-homebox
Feature URI:	http://www.inn-studio.com
Version:		1.1.1
Description:	
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_homebox::init';
	return $fns;
});
class theme_custom_homebox{
	public static $iden = 'theme_custom_homebox';
	public static $cache_id_mtime = 'theme_custom_homebox-mtime';
	private static $colors = array(
		'61b4ca',	'e1b32a',	'ee916f',	'a89d84',
		'86b767',	'6170ca',	'c461ca',	'ca6161',
		'ca8661',	'333333',	'84a89e',	'a584a8'
	);
	public static function init(){
		
		add_filter('theme_options_save',__CLASS__ . '::options_save');
		add_filter('after_backend_tab_init',__CLASS__ . '::after_backend_tab_init');
		add_filter('backend_seajs_alias',__CLASS__ . '::backend_seajs_alias');
		add_action('backend_css',__CLASS__ . '::backend_css'); 
		add_action('page_settings',__CLASS__ . '::display_backend');

		add_action('publish_post',__CLASS__ . '::action_public_post');
	}
	public static function action_public_post(){
		self::delete_cache();
		//die();
	}
	public static function keywords_to_html($keywords = null,$class = null){
		if(!$keywords) return false;
		/** 
		 * split per line
		 */
		$output_kws = [];
		$keyword_arr = explode("\n",$keywords);
		foreach($keyword_arr as $k => $v){
			$kw_arr = explode('=',$v);
			
			if(!isset($kw_arr[0]) || !isset($kw_arr[1]))
				continue;
				
			$output_kws[$k]['name'] = trim($kw_arr[0]);
			$output_kws[$k]['url'] = trim($kw_arr[1]);
		}
		return $output_kws;
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(self::$iden);

		if($key){
			return isset($caches[$key]) ? $caches[$key] : null;
		}else{
			return $caches;
		}
	}

	private static function cat_checkbox_tpl($placeholder){
		$opt = self::get_options();
		$exists_cats = isset($opt[$placeholder]['cats']) ? (array)$opt[$placeholder]['cats'] : [];
		$cats = get_categories(array(
			'orderby' => 'id',
			'hide_empty' => false,
		));
		foreach($cats as $cat){
			$checked = !empty($exists_cats) && in_array($cat->term_id,$exists_cats) ? ' checked ' : null;
			?>
			<label for="<?= self::$iden;?>-cats-<?= $placeholder;?>-<?= $cat->term_id;?>" class="button <?= empty($checked) ? null : 'button-primary';?>">
				<input 
					type="checkbox" 
					name="<?= self::$iden;?>[<?= $placeholder;?>][cats][]"
					id="<?= self::$iden;?>-cats-<?= $placeholder;?>-<?= $cat->term_id;?>"
					value="<?= $cat->term_id;?>"
					<?= $checked;?>
				/>
				<?= esc_html($cat->name);?> - <?= esc_html(urldecode($cat->slug));?>
				-
				<a href="<?= esc_url(get_category_link($cat->term_id));?>" target="_blank"><?= ___('link');?></a>
			</label>
			<?php
		}
	}
	public static function display_backend(){
		$opt = self::get_options();
		?>
		<fieldset>
			<legend><?= ___('Theme home box settings');?></legend>
			<?php
			if(is_null_array($opt)){
				echo self::get_home_box_tpl('1');
			}else{
				foreach($opt as $k => $v){
					echo self::get_home_box_tpl($k);
				}
			}
			?>
			<table class="form-table" id="<?= self::$iden;?>-control">
				<tbody>
					<tr>
						<th scope="row"><?= ___('Home box control');?></th>
						<td>
							<a id="<?= self::$iden;?>-add" href="javascript:;" class="button-primary"><i class="fa fa-plus"></i> <?= ___('Add a new home box');?></a>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	
	}
	private static function get_home_box_tpl($placeholder){
		$boxes = self::get_options();
		
		$title = isset($boxes[$placeholder]['title']) ? stripcslashes($boxes[$placeholder]['title']) : null;
		
		$link = isset($boxes[$placeholder]['link']) ? $boxes[$placeholder]['link'] : null;
		
		$selected = isset($boxes[$placeholder]['cat']) ? (int)$boxes[$placeholder]['cat'] : null;
		
		$keywords = isset($boxes[$placeholder]['keywords']) ? $boxes[$placeholder]['keywords'] : null;
		
		ob_start();
		?>
		<table 
			class="form-table <?= self::$iden;?>-item" 
			id="<?= self::$iden;?>-item-<?= $placeholder;?>" 
			data-placeholder="<?= $placeholder;?>" 
		>
		<tbody>
		<tr>
			<th><label for="<?= self::$iden;?>-title-<?= $placeholder;?>"><?= ___('Box title');?></label></th>
			<td>
				<input 
					type="text" 
					name="<?= self::$iden;?>[<?= $placeholder;?>][title]" 
					id="<?= self::$iden;?>-title-<?= $placeholder;?>" 
					class="widefat" 
					value="<?= esc_attr($title);?>" 
					placeholder="<?= ___('Box title');?>"
				>
			</td>
		</tr>
		<tr>
			<th><label for="<?= self::$iden;?>-link-<?= $placeholder;?>"><?= ___('Box link');?></label></th>
			<td>
				<input 
					type="url" 
					name="<?= self::$iden;?>[<?= $placeholder;?>][link]" 
					id="<?= self::$iden;?>-link-<?= $placeholder;?>" 
					class="widefat" 
					value="<?= esc_attr($link);?>" 
					placeholder="<?= ___('Box link (include http://)');?>"
				>
			</td>
		</tr>
		<tr>
			<th><?= ___('Categories');?></th>
			<td>
				<?php self::cat_checkbox_tpl($placeholder);?>
			</td>
		</tr>
		<tr>
			<th><label for="<?= self::$iden;?>-<?= $placeholder;?>-keywords"><?= ___('Keywords and links');?></label></th>
			<td>
				<textarea name="<?= self::$iden;?>[<?= $placeholder;?>][keywords]" id="<?= self::$iden;?>-<?= $placeholder;?>-keywords" cols="30" rows="5" class="widefat" placeholder="<?= ___('Eg. Tag1 = http://inn-studio.com');?>"><?= esc_textarea($keywords);?></textarea>
				<span class="description"><?= ___('Per keyword/line');?></span>
				<a href="javascript:;" class="<?= self::$iden;?>-del delete" id="<?= self::$iden;?>-del-<?= $placeholder;?>" data-id="<?= $placeholder;?>" data-target="#<?= self::$iden;?>-item-<?= $placeholder;?>"><?= esc_html(___('Delete this item'));?></a>
				
			</td>
		</tr>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	public static function process(){
		$output = [];
		
		die(theme_features::json_format($output));
	}
	public static function options_save(array $options = []){
		if(isset($_POST[self::$iden])){
			$options[self::$iden] = $_POST[self::$iden];
			self::delete_cache();
		}
		return $options;
	}
	public static function delete_cache(){
		wp_cache_delete(self::$iden);
	}
	public static function set_cache($data){
		wp_cache_set(self::$iden,$data,null,3600*24);
	}
	public static function get_cache(){
		return wp_cache_get(self::$iden);
	}
	public static function backend_css(){
		?>
		<link href="<?= theme_features::get_theme_includes_css(__DIR__,'backend',true,true);?>" rel="stylesheet"  media="all"/>
		<?php
	}
	public static function after_backend_tab_init(){
		?>
		seajs.use('<?= self::$iden;?>',function(_m){
			_m.config.tpl = <?= json_encode(html_compress(self::get_home_box_tpl('%placeholder%')));?>;
			_m.init();
		});
		<?php
	
	}
	public static function backend_seajs_alias($alias){
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__,'backend.js');
		return $alias;
	}
}
