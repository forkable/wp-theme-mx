<?php
/*
Feature Name:	theme-custom-homebox
Feature URI:	http://www.inn-studio.com
Version:		1.1.3
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
			$caches = (array)theme_options::get_options(__CLASS__);

		if($key)
			return isset($caches[$key]) ? $caches[$key] : null;
		return $caches;
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
			<label for="<?= __CLASS__;?>-cats-<?= $placeholder;?>-<?= $cat->term_id;?>" class="button <?= empty($checked) ? null : 'button-primary';?>">
				<input 
					type="checkbox" 
					name="<?= __CLASS__;?>[<?= $placeholder;?>][cats][]"
					id="<?= __CLASS__;?>-cats-<?= $placeholder;?>-<?= $cat->term_id;?>"
					value="<?= $cat->term_id;?>"
					<?= $checked;?>
				/>
				<?= $cat->name;?> - <?= urldecode($cat->slug);?>
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
			<table class="form-table" id="<?= __CLASS__;?>-control">
				<tbody>
					<tr>
						<th scope="row"><?= ___('Home box control');?></th>
						<td>
							<a id="<?= __CLASS__;?>-add" href="javascript:;" class="button-primary"><i class="fa fa-plus"></i> <?= ___('Add a new home box');?></a>
						</td>
					</tr>
				</tbody>
			</table>
			<input type="hidden" name="<?= __CLASS__;?>[hash]" value="<?= isset($opt['hash']) ? $opt['hash'] : md5(json_encode($opt));?>">
		</fieldset>
	<?php
	
	}
	private static function get_home_box_tpl($placeholder){
		$boxes = self::get_options();
		
		$title = isset($boxes[$placeholder]['title']) ? stripcslashes($boxes[$placeholder]['title']) : null;

		if($placeholder !== '%placeholder%' && !$title)
			return false;
			
		$link = isset($boxes[$placeholder]['link']) ? $boxes[$placeholder]['link'] : null;
		
		$number = isset($boxes[$placeholder]['number']) ? (int)$boxes[$placeholder]['number'] : 7;
		
		$keywords = isset($boxes[$placeholder]['keywords']) ? $boxes[$placeholder]['keywords'] : null;

		$ad = isset($boxes[$placeholder]['ad']) ? stripslashes($boxes[$placeholder]['ad']) : null;
		
		ob_start();
		?>
		<table 
			class="form-table <?= __CLASS__;?>-item" 
			id="<?= __CLASS__;?>-item-<?= $placeholder;?>" 
			data-placeholder="<?= $placeholder;?>" 
		>
		<tbody>
		<tr>
			<th><label for="<?= __CLASS__;?>-title-<?= $placeholder;?>"><?= ___('Box title');?></label></th>
			<td>
				<input 
					type="text" 
					name="<?= __CLASS__;?>[<?= $placeholder;?>][title]" 
					id="<?= __CLASS__;?>-title-<?= $placeholder;?>" 
					class="widefat" 
					value="<?= esc_attr($title);?>" 
					placeholder="<?= ___('Box title');?>"
				>
			</td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-link-<?= $placeholder;?>"><?= ___('Box link');?></label></th>
			<td>
				<input 
					type="url" 
					name="<?= __CLASS__;?>[<?= $placeholder;?>][link]" 
					id="<?= __CLASS__;?>-link-<?= $placeholder;?>" 
					class="widefat" 
					value="<?= esc_attr($link);?>" 
					placeholder="<?= ___('Box link (include http://)');?>"
				>
			</td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-number-<?= $placeholder;?>"><?= ___('Show posts');?></label></th>
			<td>
				<input 
					type="number" 
					name="<?= __CLASS__;?>[<?= $placeholder;?>][number]" 
					id="<?= __CLASS__;?>-number-<?= $placeholder;?>" 
					class="small-text" 
					value="<?= $number;?>" 
					placeholder="<?= ___('Posts number.');?>" 
					min="3" 
					step="4" 
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
			<th><label for="<?= __CLASS__;?>-<?= $placeholder;?>-keywords"><?= ___('Keywords and links');?></label></th>
			<td>
				<textarea name="<?= __CLASS__;?>[<?= $placeholder;?>][keywords]" id="<?= __CLASS__;?>-<?= $placeholder;?>-keywords" cols="30" rows="5" class="widefat" placeholder="<?= ___('Eg. Tag1 = http://inn-studio.com');?>"><?= esc_textarea($keywords);?></textarea>
				<span class="description"><?= ___('Per keyword/line');?></span>
				<a href="javascript:;" class="<?= __CLASS__;?>-del delete" id="<?= __CLASS__;?>-del-<?= $placeholder;?>" data-id="<?= $placeholder;?>" data-target="#<?= __CLASS__;?>-item-<?= $placeholder;?>"><?= esc_html(___('Delete this item'));?></a>
				
			</td>
		</tr>
		<tr>
			<th><label for="<?= __CLASS__;?>-<?= $placeholder;?>-ad"><?= ___('AD code');?></label></th>
			<td>
				<textarea name="<?= __CLASS__;?>[<?= $placeholder;?>][ad]" id="<?= __CLASS__;?>-<?= $placeholder;?>-ad" cols="30" rows="5" class="widefat" placeholder="<?= ___('HTML code will display below this box.');?>"><?= $ad;?></textarea>
			</td>
		</tr>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
			$old_hash = $_POST[__CLASS__]['hash'];
			
			unset($_POST[__CLASS__]['hash']);
			
			$new_hash = md5(json_encode($_POST[__CLASS__]));
			
			if($old_hash !== $new_hash){
				self::delete_cache();
				$opts[__CLASS__]['hash'] = $new_hash;
			}else{
				$opts[__CLASS__]['hash'] = $old_hash;
			}
		}
		return $opts;
	}
	public static function delete_cache(){
		theme_cache::delete('content',__CLASS__);
	}
	public static function set_cache($data){
		theme_cache::set('content',$data,__CLASS__,3600);
	}
	public static function get_cache(){
		return theme_cache::get('content',__CLASS__);
	}
	public static function backend_css(){
		?>
		<link href="<?= theme_features::get_theme_includes_css(__DIR__,'backend',true,true);?>" rel="stylesheet"  media="all"/>
		<?php
	}
	public static function after_backend_tab_init(){
		?>
		seajs.use('<?= __CLASS__;?>',function(_m){
			_m.config.tpl = <?= json_encode(html_minify(self::get_home_box_tpl('%placeholder%')));?>;
			_m.init();
		});
		<?php
	
	}
	public static function backend_seajs_alias($alias){
		$alias[__CLASS__] = theme_features::get_theme_includes_js(__DIR__,'backend.js');
		return $alias;
	}
}
