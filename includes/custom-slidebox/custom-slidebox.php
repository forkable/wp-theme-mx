<?php
/*
Feature Name:	theme_custom_slidebox
Feature URI:	http://www.inn-studio.com
Version:		1.0.0
Description:	theme_custom_slidebox
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_slidebox::init';
	return $fns;
});
class theme_custom_slidebox{
	public static $iden = 'theme_custom_slidebox';
	public static $file_exts = array('png','jpg','gif');
	public static $image_size = array(554,320,true);
	public static function init(){
		add_action('after_backend_tab_init',__CLASS__ . '::backend_seajs_use'); 
		add_action('backend_css',__CLASS__ . '::backend_css'); 
		add_action('page_settings',__CLASS__ . '::display_backend');
		add_action('frontend_seajs_use',__CLASS__ . '::frontend_seajs_use');
		add_action('wp_ajax_' . self::$iden,__CLASS__ . '::process');
		add_filter('theme_options_save',__CLASS__ . '::options_save');

		add_action('wp_enqueue_scripts', 	__CLASS__ . '::frontend_css');
	}
	public static function options_save($options){
		if(isset($_POST['slidebox'])){
			$options[self::$iden] = $_POST['slidebox'];
		}
		return $options;
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
			<label for="<?php echo $id;?>-<?php echo $cat->term_id;?>" class="item button <?php echo $selected_class;?>">
				<input 
					type="checkbox" 
					id="<?php echo $id;?>-<?php echo $cat->term_id;?>" 
					name="<?php echo esc_attr($name);?>[]" 
					value="<?php echo $cat->term_id;?>"
					<?php echo $checked;?>
				/>
					<?php echo esc_html($cat->name);?>
			</label>
			<?php 
			}
		}else{ ?>
			<p><?php echo ___('No category, pleass go to add some categories.');?></p>
		<?php }
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	public static function get_options($key = null){
		$caches = [];
		if(!isset($caches[self::$iden]))
			$caches[self::$iden] = (array)theme_options::get_options(self::$iden);

		if($key){
			return isset($caches[self::$iden][$key]) ? $caches[self::$iden][$key] : null;
		}else{
			return $caches[self::$iden];
		}
		
	}
	public static function process(){
		$output = [];
		
		/** 
		 * if not image
		 */
		$filename = isset($_FILES['img']['name']) ? $_FILES['img']['name'] : null;
		$file_ext = $filename ? array_slice(explode('.',$filename),-1,1)[0] : null;
		if(!in_array($file_ext,self::$file_exts)){
			$output['status'] = 'error';
			$output['code'] = 'invaild_file_type';
			$output['msg'] = ___('Invaild file type.');
			die(theme_features::json_format($output));
		}
		/** 
		 * check permission
		 */
		if(!current_user_can('manage_options')){
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
	
		add_image_size(self::$iden, self::$image_size[0],self::$image_size[1],self::$image_size[2]);
		
		$attach_id = media_handle_upload('img',0);
		if(is_wp_error($attach_id)){
			$output['status'] = 'error';
			$output['code'] = $attach_id->get_error_code();
			$output['msg'] = $attach_id->get_error_message();
			die(theme_features::json_format($output));
		}else{
			$output['status'] = 'success';
			$output['url'] = wp_get_attachment_image_src($attach_id,self::$iden)[0];
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
			id="slidebox-item-<?php echo $placeholder;?>" 
			data-placeholder="<?php echo $placeholder;?>" 
		>
		<tbody>
		<tr>
			<th><label for="slidebox-title-<?php echo $placeholder;?>"><?php echo sprintf(___('Slide-box title - %s'),$placeholder);?></label></th>
			<td><input type="text" id="slidebox-title-<?php echo $placeholder;?>" name="slidebox[<?php echo $placeholder;?>][title]" class="widefat" placeholder="<?php echo ___('Title will be display as attribute-alt');?>" value="<?php echo $title;?>"/></td>
		</tr>
		<tr>
			<th><label for="slidebox-subtitle-<?php echo $placeholder;?>"><?php echo ___('Subtitles (optional)');?></label></th>
			<td><input type="text" id="slidebox-subtitle-<?php echo $placeholder;?>" name="slidebox[<?php echo $placeholder;?>][subtitle]" class="widefat" placeholder="<?php echo ___('Subtitle can be date or any text');?>" value="<?php echo $subtitle;?>"/>
				<a href="javascript:;" onclick="document.getElementById('slidebox-subtitle-<?php echo $placeholder;?>').value='<?php echo date('m.d');?>';" class="slidebox-subtitle-date" data-target="#slidebox-subtitle-<?php echo $placeholder;?>" data-date="<?php echo date('m.d');?>"><?php echo ___('Current date');?></a>
			</td>
		</tr>
		<tr>
			<th><label for="slidebox-cat-<?php echo $placeholder;?>"><?php echo ___('Categories (optional)');?></label></th>
			<td>
				
				<?php
				$selected_cat_ids = isset($boxes[$placeholder]['catids']) ? (array)$boxes[$placeholder]['catids'] : [];
				echo self::get_cat_checkbox_list("slidebox[$placeholder][catids]","slidebox-catids-$placeholder",$selected_cat_ids);
				?>
			</td>
		</tr>
		<tr>
			<th><label for="slidebox-link-url-<?php echo $placeholder;?>"><?php echo ___('Link url');?></label></th>
			<td><input type="url" id="slidebox-link-url-<?php echo $placeholder;?>" name="slidebox[<?php echo $placeholder;?>][link-url]" class="widefat" placeholder="<?php echo ___('Url address');?>" value="<?php echo esc_attr($link_url);?>"/></td>
		</tr>
		<tr>
			<th><label for="slidebox-img-url-<?php echo $placeholder;?>"><?php echo ___('Image url');?></label></th>
			<td>
				<div class="slidebox-upload-area">
					<input type="url" id="slidebox-img-url-<?php echo $placeholder;?>" name="slidebox[<?php echo $placeholder;?>][img-url]" class="slidebox-img-url" placeholder="<?php echo ___('Image address');?>" value="<?php echo esc_attr($img_url);?>"/>
					<a href="javascript:;" class="button-primary slidebox-upload" id="slidebox-upload-<?php echo $placeholder;?>"><?php echo ___('Upload image');?><input type="file" id="slidebox-file-<?php echo $placeholder;?>" class="slidebox-file"/></a>
				</div>
				<div class="slidebox-upload-tip hide"></div>
			</td>
		</tr>
		<tr>
			<th><?php echo ___('Addon options');?></th>
			<td>
				<label for="slidebox-rel-nofollow-<?php echo $placeholder;?>" class="button">
					<input type="checkbox" name="slidebox[<?php echo $placeholder;?>][rel][nofollow]" id="slidebox-rel-nofollow-<?php echo $placeholder;?>" value="1" <?php echo $checked_rel_nofollow;?> />
					<?php echo ___('Nofollow link');?>
				</label>
				<label for="slidebox-target-blank-<?php echo $placeholder;?>" class="button">
					<input type="checkbox" name="slidebox[<?php echo $placeholder;?>][target][blank]" id="slidebox-target-blank-<?php echo $placeholder;?>" value="1" <?php echo $checked_target_blank;?> />
					<?php echo ___('Open in new window');?>
				</label>

				<a href="javascript:;" class="slidebox-del delete" id="slidebox-del-<?php echo $placeholder;?>" data-id="<?php echo $placeholder;?>" data-target="#slidebox-item-<?php echo $placeholder;?>"><?php echo ___('Delete this item');?></a>
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
			<legend><?php echo ___('Slide-box settings');?></legend>
			<p class="description">
				<?php echo sprintf(___('You can set images and link to slide-box on homepage. Image size is %s&times;%s px. Remember save your settings when all done.'),self::$image_size[0] === 999 ? ___('unlimited') : self::$image_size[0],self::$image_size[1] === 999 ? ___('unlimited') : self::$image_size[1]);?>
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
			<th><?php echo ___('Control');?></th>
			<td>
				<a id="slidebox-add" href="javascript:;" class="button-primary"><?php echo ___('Add a new item');?></a>
			</td>
			</tr>
			</tbody>
			</table>
		</fieldset>
	<?php
	}
	public static function display_frontend(){
		$boxes = (array)self::get_options();
	
		$cache_id = md5(serialize($boxes));
		$cache = wp_cache_get($cache_id);
		if($cache){
			echo $cache;
			return $cache;
		}
		
		if(is_null_array($boxes) || count($boxes) < 2) return false;
		krsort($boxes);
		ob_start();
		?>
<div id="slidebox">
	<?php
	/**
	 * slide setup
	 */
	for($i = 1, $len = count($boxes); $i <= $len; ++$i){ ?>
		<input type="radio" name="slide-checkbox" class="slide-checkbox" id="slide-<?php echo $i;?>" <?php echo $i === 1 ? 'checked' : null;?>/>
	<?php } ?>
	<div class="slides">
		<div class="overflow">
			<div class="inner">
				<?php
				foreach($boxes as $k => $v){
					$rel_nofollow = isset($v['rel']['nofollow']) ? ' rel="nofollow" ' : null;
					$target_blank = isset($v['target']['blank']) ? ' target="blank" ' : null;
					$title = $v['title'];
					?>
					<a 
						href="<?php echo esc_url($v['link-url']);?>" 
						title="<?php echo $v['title'];?>"
						<?php echo $rel_nofollow;?>
						<?php echo $target_blank;?>
					>
						<img src="<?php echo esc_url($v['img-url']);?>" alt="<?php echo $v['title'];?>">

						<h2>
		                    <?php echo $v['title'];?>
							<?php if(isset($v['subtitle']) && !empty($v['subtitle'])){ ?>
								<small><?php echo $v['subtitle'];?></small>
							<?php } ?>
		                </h2>
					</a>
					<?php
				}
				?>
			</div><!-- /.inner -->
		</div><!-- /.overflow -->
	</div><!-- /#slides -->
	<div class="control"><?php echo self::get_labels($boxes);?></div>
	<div class="active"><?php echo self::get_labels($boxes);?></div>
</div><!-- /#slidebox-container -->
		<?php
		$cache = html_compress(ob_get_contents());
		ob_end_clean();
		wp_cache_set($cache_id,$cache);
		echo $cache;
		return $cache;
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
		<link href="<?php echo theme_features::get_theme_includes_css(__DIR__,'backend');?>" rel="stylesheet"  media="all"/>
		<?php
	}
	public static function frontend_seajs_use(){
		if(!is_home()) 
			return;
		?>
		seajs.use('<?php echo theme_features::get_theme_includes_js(__DIR__);?>',function(m){
			m.init();
		});
		<?php
	}
	public static function frontend_css(){
		if(!is_home()) 
			return false;
		wp_enqueue_style(
			self::$iden,
			theme_features::get_theme_includes_css(__DIR__,'style',false),
			'frontend',
			theme_features::get_theme_info('version')
		);

	}
	public static function backend_seajs_use(){
		
		?>
		
		seajs.use('<?php echo theme_features::get_theme_includes_js(__DIR__,'backend.js');?>',function(m){
			m.config.tpl = <?php echo json_encode(html_compress(self::get_box_tpl('%placeholder%')));?>;
			m.config.process_url = '<?php echo theme_features::get_process_url(array('action'=>self::$iden));?>';
			m.config.lang.M00001 = '<?php echo ___('Loading, please wait...');?>';
			m.config.lang.E00001 = '<?php echo ___('Server error or network is disconnected.');?>';
			m.init();
		});

		<?php
	}

}

?>
