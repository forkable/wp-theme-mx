<?php
/*
Feature Name:	theme-custom-homebox
Feature URI:	http://www.inn-studio.com
Version:		1.1.0
Description:	
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
theme_custom_homebox::init();
class theme_custom_homebox{
	public static $iden = 'theme_custom_homebox';
	private static $colors = array(
		'61b4ca',	'e1b32a',	'ee916f',	'a89d84',
		'86b767',	'6170ca',	'c461ca',	'ca6161',
		'ca8661',	'333333',	'84a89e',	'a584a8'
	);
	public static function init(){
		add_filter('theme_options_save',get_class() . '::options_save');
		add_filter('after_backend_tab_init',get_class() . '::after_backend_tab_init');
		add_filter('backend_seajs_alias',get_class() . '::backend_seajs_alias');
		add_action('backend_css',get_class() . '::backend_css'); 
		add_action('page_settings',get_class() . '::backend_display');

	}

	public static function keywords_to_html($keywords = null,$class = null){
		if(!$keywords) return false;
		/** 
		 * split per line
		 */
		$output_kws = array();
		$keyword_arr = explode(PHP_EOL,$keywords);
		foreach($keyword_arr as $k => $v){
			$kw_arr = explode('=',$v);
			$output_kws[$k]['name'] = trim($kw_arr[0]);
			$output_kws[$k]['url'] = trim($kw_arr[1]);
		}
		return $output_kws;
	}
	public static function frontend_display($args = null){
		$boxes = (array)theme_options::get_options(self::$iden);
		if(empty($boxes)) return false;
		global $wp_query,$post;
		$defaults = array(
			'dt_title' => null,
			'posts_per_page' => 10,
			'classes' => array('grid-20','tablet-grid-20','mobile-grid-50'),
		);
		$r = wp_parse_args($args,$defaults);
		extract($r,EXTR_SKIP);
		
		foreach($boxes as $k => $v){
			$category = get_category($v['cat']);
			?>
			<section class="mod main-posts">
				<h3 class="tabtitle">
					<a href="<?php echo esc_url(get_category_link($category->term_id));?>" class="link">
						<span class="icon-play"></span><span class="after-icon"><?php echo esc_html($category->name);?></span>
						<small class="detail"><?php echo esc_html(___('&raquo; detail'));?></small>
					</a>
					<?php self::keywords_to_html($v['keywords']);?>
				</h3>
				<?php
				$wp_query = theme_functions::get_posts_query(array(
					'orderby' => 'lastest',
					'posts_per_page' => 10,
					'category__in' => array($v['cat']),
				));
				if(have_posts()){
					?>
					<ul class="post-img-lists">
						<?php
						while(have_posts()){
							the_post();
							theme_functions::archive_img_content(array(
								'classes' => array('grid-20 tablet-grid-20 mobile-grid-50'),
							));
						}
						?>
					</ul>
					<?php
				}else{
					?>
					<?php echo status_tip('info',___('Not data in this category'));?>
					<?php
				}
				wp_reset_query();
				wp_reset_postdata();
				?>
			</section>
			<?php
		}
	}
	private static function cat_checkbox_tpl($placeholder){
		$opt = (array)theme_options::get_options(self::$iden);
		$exists_cats = isset($opt[$placeholder]['cats']) ? (array)$opt[$placeholder]['cats'] : array();
		$cats = get_categories(array(
			'orderby' => 'id',
			'hide_empty' => false,
		));
		foreach($cats as $cat){
			$checked = !empty($exists_cats) && in_array($cat->term_id,$exists_cats) ? ' checked ' : null;
			?>
			<label for="<?php echo self::$iden;?>-cats-<?php echo $placeholder;?>-<?php echo $cat->term_id;?>" class="button <?php echo empty($checked) ? null : 'button-primary';?>">
				<input 
					type="checkbox" 
					name="<?php echo self::$iden;?>[<?php echo $placeholder;?>][cats][]"
					id="<?php echo self::$iden;?>-cats-<?php echo $placeholder;?>-<?php echo $cat->term_id;?>"
					value="<?php echo $cat->term_id;?>"
					<?php echo $checked;?>
				/>
				<?php echo esc_html($cat->name);?> - <?php echo esc_html(urldecode($cat->slug));?>
				-
				<a href="<?php echo esc_url(get_category_link($cat->term_id));?>" target="_blank"><?php echo ___('link');?></a>
			</label>
			<?php
		}
	}
	public static function backend_display(){
		$opt = (array)theme_options::get_options(self::$iden);
		?>
		<fieldset>
			<legend><?php echo ___('Theme home box settings');?></legend>
			<?php
			if(!empty($opt)){
				foreach($opt as $k => $v){
					echo self::get_home_box_tpl($k);
				}
			}else{
				echo self::get_home_box_tpl(1);
			}
			?>
			<table class="form-table" id="<?php echo self::$iden;?>-control">
				<tbody>
					<tr>
						<th scope="row"><?php echo ___('Home box control');?></th>
						<td>
							<a id="<?php echo self::$iden;?>-add" href="javascript:void(0);" class="button-primary"><?php echo ___('Add a new home box');?></a>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	
	}
	private static function get_home_box_tpl($placeholder){
		$boxes = (array)theme_options::get_options(self::$iden);
		$title = isset($boxes[$placeholder]['title']) ? $boxes[$placeholder]['title'] : null;
		$link = isset($boxes[$placeholder]['link']) ? $boxes[$placeholder]['link'] : null;
		$selected = isset($boxes[$placeholder]['cat']) ? (int)$boxes[$placeholder]['cat'] : null;
		$keywords = isset($boxes[$placeholder]['keywords']) ? $boxes[$placeholder]['keywords'] : null;
		ob_start();
		?>
		<table 
			class="form-table <?php echo self::$iden;?>-item" 
			id="<?php echo self::$iden;?>-item-<?php echo $placeholder;?>" 
			data-placeholder="<?php echo $placeholder;?>" 
		>
		<tbody>
		<tr>
			<th><label for="<?php echo self::$iden;?>-title-<?php echo $placeholder;?>"><?php echo ___('Box title');?></label></th>
			<td>
				<input 
					type="text" 
					name="<?php echo self::$iden;?>[<?php echo $placeholder;?>][title]" 
					id="<?php echo self::$iden;?>-title-<?php echo $placeholder;?>" 
					class="widefat" 
					value="<?php echo esc_attr($title);?>" 
					placeholder="<?php echo ___('Box title');?>"
				>
			</td>
		</tr>
		<tr>
			<th><label for="<?php echo self::$iden;?>-link-<?php echo $placeholder;?>"><?php echo ___('Box link');?></label></th>
			<td>
				<input 
					type="url" 
					name="<?php echo self::$iden;?>[<?php echo $placeholder;?>][link]" 
					id="<?php echo self::$iden;?>-link-<?php echo $placeholder;?>" 
					class="widefat" 
					value="<?php echo esc_attr($link);?>" 
					placeholder="<?php echo ___('Box link (include http://)');?>"
				>
			</td>
		</tr>
		<tr>
			<th><?php echo ___('Categories');?></th>
			<td>
				<?php self::cat_checkbox_tpl($placeholder);?>
			</td>
		</tr>
		<tr>
			<th><label for="<?php echo self::$iden;?>-<?php echo $placeholder;?>-keywords"><?php echo ___('Keywords and links');?></label></th>
			<td>
				<textarea name="<?php echo self::$iden;?>[<?php echo $placeholder;?>][keywords]" id="<?php echo self::$iden;?>-<?php echo $placeholder;?>-keywords" cols="30" rows="5" class="widefat" placeholder="<?php echo ___('Eg. Tag1 = http://inn-studio.com');?>"><?php echo esc_textarea($keywords);?></textarea>
				<span class="description"><?php echo ___('Per keyword/line');?></span>
				<a href="javascript:void(0);" class="<?php echo self::$iden;?>-del delete" id="<?php echo self::$iden;?>-del-<?php echo $placeholder;?>" data-id="<?php echo $placeholder;?>" data-target="#<?php echo self::$iden;?>-item-<?php echo $placeholder;?>"><?php echo esc_html(___('Delete this item'));?></a>
				
			</td>
		</tr>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	public static function options_save($options){
		if(isset($_POST[self::$iden])){
			$options[self::$iden] = (array)$_POST[self::$iden];
		}
		return $options;
	}
	public static function backend_css(){
		?>
		<link href="<?php echo theme_features::get_theme_includes_css(__FILE__,'backend');?>" rel="stylesheet"  media="all"/>
		<?php
	}
	public static function after_backend_tab_init(){
		?>
		seajs.use('<?php echo self::$iden;?>',function(_m){
			_m.config.tpl = <?php echo json_encode(self::get_home_box_tpl('%placeholder%'));?>;
			_m.init();
		});
		<?php
	
	}
	public static function backend_seajs_alias($alias){
		$alias[self::$iden] = theme_features::get_theme_includes_js(__FILE__,'backend.js');
		return $alias;
	}
}
