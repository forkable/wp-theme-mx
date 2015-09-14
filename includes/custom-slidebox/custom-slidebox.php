<?php
/*
Feature Name:	theme_custom_slidebox
Feature URI:	http://www.inn-studio.com
Version:		2.0.1
Description:	theme_custom_slidebox
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_slidebox::init';
	return $fns;
});
class theme_custom_slidebox{
	public static $file_exts = ['png','jpg','gif'];
	public static $image_size = [800,500,true];
	public static function init(){
		add_action('after_backend_tab_init',__CLASS__ . '::backend_seajs_use'); 
		add_action('page_settings',__CLASS__ . '::display_backend');
		add_action('wp_ajax_' . __CLASS__,__CLASS__ . '::process');
		add_filter('theme_options_save',__CLASS__ . '::options_save');
		add_action('backend_css',__CLASS__ . '::backend_css'); 

		/**
		 * frontend
		 */
		add_action('frontend_seajs_alias',__CLASS__ . '::frontend_seajs_alias');
		add_action('frontend_seajs_use',__CLASS__ . '::frontend_seajs_use');
		add_action('wp_enqueue_scripts', 	__CLASS__ . '::frontend_css');
	}
	public static function options_save(array $opts = []){
		if(isset($_POST['slidebox'])){
			$opts[__CLASS__] = $_POST['slidebox'];
			self::delete_cache();
		}
		return $opts;
	}
	private static function get_cat_checkbox_list($name,$id,$selected_cat_ids = []){
		$cats = get_categories(array(
			'hide_empty' => false,
			'orderby' => 'term_group',
			'exclude' => '1',
		));
		
		ob_start();
		if($cats){
			foreach($cats as $cat){
				if(in_array($cat->term_id,(array)$selected_cat_ids)){
					$checked = ' checked="checked" ';
					$selected_class = ' button-primary ';
				}else{
					$checked = null;
					$selected_class = null;
				}
			?>
			<label for="<?= $id;?>-<?= $cat->term_id;?>" class="item button <?= $selected_class;?>">
				<input 
					type="checkbox" 
					id="<?= $id;?>-<?= $cat->term_id;?>" 
					name="<?= esc_attr($name);?>[]" 
					value="<?= $cat->term_id;?>"
					<?= $checked;?>
				/>
					<?= esc_html($cat->name);?>
			</label>
			<?php 
			}
		}else{ ?>
			<p><?= ___('No category, pleass go to add some categories.');?></p>
		<?php }
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	public static function get_options($key = null){
		$caches = null;
		if($caches === null)
			$caches = (array)theme_options::get_options(__CLASS__);

		if($key)
			return isset($caches[$key]) ? $caches[$key] : null;
		return $caches;
	}
	public static function process(){
		$output = [];
		
		/** 
		 * if not image
		 */
		$filename = isset($_FILES['img']['name']) ? $_FILES['img']['name'] : null;
		$file_ext = $filename ? strtolower(array_slice(explode('.',$filename),-1,1)[0]) : null;
		if(!in_array($file_ext,self::$file_exts)){
			$output['status'] = 'error';
			$output['code'] = 'invaild_file_type';
			$output['msg'] = ___('Invaild file type.');
			die(theme_features::json_format($output));
		}
		/** 
		 * check permission
		 */
		if(!theme_cache::current_user_can('manage_options')){
			$output['status'] = 'error';
			$output['code'] = 'invaild_permission';
			$output['msg'] = ___('You have not permission to upload.');
			die(theme_features::json_format($output));
		}
		/** 
		 * pass
		 */
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
	
		add_image_size(__CLASS__, self::$image_size[0],self::$image_size[1],self::$image_size[2]);
		
		$attach_id = media_handle_upload('img',0);
		if(is_wp_error($attach_id)){
			$output['status'] = 'error';
			$output['code'] = $attach_id->get_error_code();
			$output['msg'] = $attach_id->get_error_message();
			die(theme_features::json_format($output));
		}else{
			$output['status'] = 'success';
			$output['url'] = wp_get_attachment_image_src($attach_id,__CLASS__)[0];
			$output['msg'] = ___('Upload success.');
			die(theme_features::json_format($output));
		}
		die(theme_features::json_format($output));
	}
	private static function get_box_tpl($placeholder){
		$boxes = self::get_options();
		$title = isset($boxes[$placeholder]['title']) ? $boxes[$placeholder]['title'] : null;
		$subtitle = isset($boxes[$placeholder]['subtitle']) ? $boxes[$placeholder]['subtitle'] : null;
		$link_url = isset($boxes[$placeholder]['link-url']) ? $boxes[$placeholder]['link-url'] : null;
		$img_url = isset($boxes[$placeholder]['img-url']) ? $boxes[$placeholder]['img-url'] : null;
		$checked_rel_nofollow = isset($boxes[$placeholder]['rel']['nofollow']) ? ' checked ' : null;
		$checked_target_blank = isset($boxes[$placeholder]['target']['blank']) ? ' checked ' : null;
		
		ob_start();
		?>
		<table 
			class="form-table slidebox-item" 
			id="slidebox-item-<?= $placeholder;?>" 
			data-placeholder="<?= $placeholder;?>" 
		>
		<tbody>
		<tr>
			<th><label for="slidebox-title-<?= $placeholder;?>"><?= sprintf(___('Slide-box title - %s'),$placeholder);?></label></th>
			<td><input type="text" id="slidebox-title-<?= $placeholder;?>" name="slidebox[<?= $placeholder;?>][title]" class="widefat" placeholder="<?= ___('Title will be display as attribute-alt');?>" value="<?= $title;?>"/></td>
		</tr>
		<tr>
			<th><label for="slidebox-subtitle-<?= $placeholder;?>"><?= ___('Subtitles (optional)');?></label></th>
			<td><input type="text" id="slidebox-subtitle-<?= $placeholder;?>" name="slidebox[<?= $placeholder;?>][subtitle]" class="widefat" placeholder="<?= ___('Subtitle can be date or any text');?>" value="<?= $subtitle;?>"/>
				<a href="javascript:;" onclick="document.getElementById('slidebox-subtitle-<?= $placeholder;?>').value='<?= date('m.d');?>';" class="slidebox-subtitle-date" data-target="#slidebox-subtitle-<?= $placeholder;?>" data-date="<?= date('m.d');?>"><?= ___('Current date');?></a>
			</td>
		</tr>
		<tr>
			<th><label for="slidebox-cat-<?= $placeholder;?>"><?= ___('Categories (optional)');?></label></th>
			<td>
				
				<?php
				$selected_cat_ids = isset($boxes[$placeholder]['catids']) ? (array)$boxes[$placeholder]['catids'] : [];
				echo self::get_cat_checkbox_list("slidebox[$placeholder][catids]","slidebox-catids-$placeholder",$selected_cat_ids);
				?>
			</td>
		</tr>
		<tr>
			<th><label for="slidebox-link-url-<?= $placeholder;?>"><?= ___('Link url');?></label></th>
			<td><input type="url" id="slidebox-link-url-<?= $placeholder;?>" name="slidebox[<?= $placeholder;?>][link-url]" class="widefat" placeholder="<?= ___('Url address');?>" value="<?= esc_attr($link_url);?>"/></td>
		</tr>
		<tr>
			<th><label for="slidebox-img-url-<?= $placeholder;?>"><?= ___('Image url');?></label></th>
			<td>
				<div class="slidebox-upload-area">
					<input type="url" id="slidebox-img-url-<?= $placeholder;?>" name="slidebox[<?= $placeholder;?>][img-url]" class="slidebox-img-url" placeholder="<?= ___('Image address');?>" value="<?= esc_attr($img_url);?>"/>
					<a href="javascript:;" class="button-primary slidebox-upload" id="slidebox-upload-<?= $placeholder;?>"><?= ___('Upload image');?><input type="file" id="slidebox-file-<?= $placeholder;?>" class="slidebox-file"/></a>
				</div>
				<div class="slidebox-upload-tip hide"></div>
			</td>
		</tr>
		<tr>
			<th><?= ___('Addon options');?></th>
			<td>
				<label for="slidebox-rel-nofollow-<?= $placeholder;?>" class="button">
					<input type="checkbox" name="slidebox[<?= $placeholder;?>][rel][nofollow]" id="slidebox-rel-nofollow-<?= $placeholder;?>" value="1" <?= $checked_rel_nofollow;?> />
					<?= ___('Nofollow link');?>
				</label>
				<label for="slidebox-target-blank-<?= $placeholder;?>" class="button">
					<input type="checkbox" name="slidebox[<?= $placeholder;?>][target][blank]" id="slidebox-target-blank-<?= $placeholder;?>" value="1" <?= $checked_target_blank;?> />
					<?= ___('Open in new window');?>
				</label>

				<a href="javascript:;" class="slidebox-del delete" id="slidebox-del-<?= $placeholder;?>" data-id="<?= $placeholder;?>" data-target="#slidebox-item-<?= $placeholder;?>"><?= ___('Delete this item');?></a>
			</td>
		</tr>
		
		</tbody>
		</table>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	public static function display_backend(){
		$boxes = self::get_options();
		?>
		<fieldset>
			<legend><?= ___('Slide-box settings');?></legend>
			<p class="description">
				<?= sprintf(___('You can set images and link to slide-box on homepage. Image size is %s&times;%s px. Remember save your settings when all done.'),self::$image_size[0] === 999 ? ___('unlimited') : self::$image_size[0],self::$image_size[1] === 999 ? ___('unlimited') : self::$image_size[1]);?>
			</p>
			<?php
			if(!empty($boxes)){
				foreach($boxes as $k => $v){
					echo self::get_box_tpl($k);
				}
			}else{
				echo self::get_box_tpl(1);
			}
			?>
			<table class="form-table" id="slidebox-control">
			<tbody>
			<tr>
			<th><?= ___('Control');?></th>
			<td>
				<a id="slidebox-add" href="javascript:;" class="button-primary"><?= ___('Add a new item');?></a>
			</td>
			</tr>
			</tbody>
			</table>
		</fieldset>
	<?php
	}
	public static function delete_cache(){
		theme_cache::delete(__CLASS__);
	}
	public static function set_cache($data){
		theme_cache::set(__CLASS__,$data);
	}
	public static function get_cache(){
		return theme_cache::get(__CLASS__);
	}
	public static function display_frontend(){
	
		$cache = self::get_cache();
		if($cache){
			echo $cache;
			unset($cache);
			return;
		}
		$boxes = (array)self::get_options();
		
		if(is_null_array($boxes) || count($boxes) < 2) return false;

		$placeholder = theme_features::get_theme_includes_image(__DIR__,'placeholder.png');
		
		krsort($boxes);
		ob_start();
		?>
<div class="slidebox-container">
<div class="area-overdely"></div>
<div class="area-blur">
	<?php 
	$i = 0;
	foreach($boxes as $v){ 
	++$i;
	?>
		<div class="item <?= $i === 1 ? 'active' : null;?>" style="background-image:url(<?= esc_url($v['img-url']);?>)"></div>
	<?php } ?>
</div>
<div id="slidebox" class="container hidden-xs">
	<div class="area-main">
		<?php
		$i = 0;
		foreach($boxes as $v){
			++$i;
			$rel_nofollow = isset($v['rel']['nofollow']) ? 'rel="nofollow"' : null;
			$target_blank = isset($v['target']['blank']) ? 'target="blank"' : null;
			$title = esc_html($v['title']);
			$subtitle = esc_html($v['subtitle']);
			$img_url = esc_url($v['img-url']);
			$link_url = esc_url($v['link-url']);
			?>
			<div class="item <?= $i === 1 ? 'active' : null;?>">
				<a 
					class="img" 
					href="<?= $link_url;?>" 
					title="<?= $title;?>" 
					<?= $rel_nofollow;?> 
					<?= $target_blank;?> 
				><img src="<?= $img_url;?>" alt="<?= $title;?>" width="<?= self::$image_size[0];?>" height="<?= self::$image_size[1];?>"></a>

				<a 
					class="des" 
					href="<?= $link_url;?>" 
					title="<?= $title;?>" 
					<?= $rel_nofollow;?> 
					<?= $target_blank;?> 
				>
					<span class="title"><?= $title;?></span>
					<?php if($subtitle !== ''){ ?>
						<span class="sub-title"><?= $subtitle;?></span>
					<?php } ?>
					<span class="more"><?= ___('Detail &raquo;');?></span>
				</a>
			</div>
		<?php } ?>
	</div>
	<div class="area-thumbnail">
		<?php
		$i = 0;
		foreach($boxes as $v){
			++$i;
			$rel_nofollow = isset($v['rel']['nofollow']) ? 'rel="nofollow"' : null;
			$target_blank = isset($v['target']['blank']) ? 'target="blank"' : null;
			$title = esc_html($v['title']);
			$img_url = esc_url($v['img-url']);
			$link_url = esc_url($v['link-url']);
			?>
			<a 
				class="item <?= $i === 1 ? 'active' : null;?>" 
				href="<?= $link_url;?>" 
				title="<?= $title;?>" 
				<?= $rel_nofollow;?> 
				<?= $target_blank;?> 
			>
				<img src="<?= $img_url;?>" alt="placeholder" width="<?= self::$image_size[0];?>" height="<?= self::$image_size[1];?>">
				<h2><?= $title;?></h2>
			</a>
		<?php } ?>
	</div>
</div><!-- /#slidebox -->
</div><!-- /.slidebox-container -->
		<?php
		$cache = html_minify(ob_get_contents());
		ob_end_clean();
		self::set_cache($cache);
		echo $cache;
		unset($cache);
	}
	private static function get_labels($boxes){
		static $cache = null;
		if($cache !== null)
			return $cache;
		
		$len = count($boxes);
		if($len < 2)
			return false;
			
		for($i = 1; $i <= $len; ++$i){
			$cache .= '<label for="slide-' . $i . '"></label>';
		}
		return $cache;
	}
	public static function backend_css(){
		?>
		<link href="<?= theme_features::get_theme_includes_css(__DIR__,'backend',true,true);?>" rel="stylesheet"  media="all"/>
		<?php
	}
	public static function frontend_seajs_alias(array $alias = []){
		if(!theme_cache::is_home())
			return $alias;
			
		$alias[__CLASS__] = theme_features::get_theme_includes_js(__DIR__);

		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!theme_cache::is_home())
			return false;
		?>
		seajs.use('<?= __CLASS__;?>',function(m){
			m.init();
		});
		<?php
	}
	public static function frontend_css(){
		if(!theme_cache::is_home())
			return false;
		wp_enqueue_style(
			__CLASS__,
			theme_features::get_theme_includes_css(__DIR__),
			'frontend',
			theme_file_timestamp::get_timestamp()
		);

	}
	public static function backend_seajs_use(){
		?>
		seajs.use('<?= theme_features::get_theme_includes_js(__DIR__,'backend.js');?>',function(m){
			m.config.tpl = <?= json_encode(html_minify(self::get_box_tpl('%placeholder%')));?>;
			m.config.process_url = '<?= theme_features::get_process_url(array('action'=>__CLASS__));?>';
			m.config.lang.M00001 = '<?= ___('Loading, please wait...');?>';
			m.config.lang.E00001 = '<?= ___('Server error or network is disconnected.');?>';
			m.init();
		});

		<?php
	}

}

?>
