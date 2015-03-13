<?php 
/**
 * Theme Options
 * the theme options and show admin control planel
 * 
 * @version 4.2.2
 * @author KM@INN STUDIO
 * 
 */
add_action('admin_init','theme_options::init' );
add_action('admin_menu','theme_options::add_page');
add_action('admin_bar_menu','theme_options::add_bar',61);
class theme_options{
	public static $iden = 'theme_options';
	/**
	 * init
	 * 
	 * @return 
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function init(){
		if(!self::is_options_page()) return false;
		add_action('admin_head',get_class() . '::backend_header');
		self::save_options();
		self::redirect();
	}
	/**
	 * get the theme options from the features default value or DB.
	 * 
	 * @usedby theme_options::get_options()
	 * @return array
	 * @version 1.2.2
	 * @since 3.1.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_options($key = null){
		/** Get first options */
		$options = get_option(self::$iden);
		/** Default options hook */
		$options_default = apply_filters('theme_options_default',null);
		$options = wp_parse_args($options,$options_default);
		if($key){
			return  isset($options[$key]) ? $options[$key] : null;
		}else{
			return $options;
		}
	}
	public static function backend_header(){
		if(!self::is_options_page()) return false;
		$options = self::get_options();/* get the options */
		echo theme_features::get_theme_css('modules/fa-fonts','normal');
		echo theme_features::get_theme_css('backend/fonts','normal');
		echo theme_features::get_theme_css('backend/style','normal');
		/**
		 * add admin_css hook 
		 */
		do_action('backend_css');
		?><script id="seajsnode" src="<?php echo theme_features::get_theme_js('seajs/sea');?>"></script>
		<script>
		<?php
		$config = array();
		$config['base'] = esc_js(theme_features::get_theme_js());
		$config['paths'] = array(
			'theme_js' => esc_js(theme_features::get_theme_js()),
			'theme_css' => esc_js(theme_features::get_theme_css()),
		);
		$config['vars'] = array(
			'locale' => str_replace('-','_',get_bloginfo('language')),
			'theme_js' => esc_js(theme_features::get_theme_js()),
			'theme_css' => esc_js(theme_features::get_theme_css()),
			'process_url' => esc_js(theme_features::get_process_url()),
		);
		$config['map'] = array(
			array('.css','.css?v=' . theme_features::get_theme_info('version')),
			array('.js','.js?v=' . theme_features::get_theme_info('version'))
		);
		/** 
		 * seajs hook
		 */
		$config['paths'] = apply_filters('backend_seajs_paths',$config['paths']);
		$config['alias'] = apply_filters('backend_seajs_alias',[]);
		$config['vars'] = apply_filters('backend_seajs_vars',$config['vars']);
		$config['map'] = apply_filters('backend_seajs_map',$config['map']);

		?>
		seajs.config(<?php echo json_encode($config);?>);
		<?php do_action('before_backend_tab_init');?>
		seajs.use('backend',function(m){
			m.init({
				done : function($btn,$cont,$tab){
					<?php do_action('after_backend_tab_init');?>
				},
				custom : function(b,c,i,t){
					<?php do_action('after_backend_tab_custom');?>
				},
				tab_title : '<?php echo wp_get_theme();?> <?php echo ___('theme settings');?>'
			});
		});
		</script>
		<?php	
	}
	/**
	 * show the options settings for admin theme setting page.
	 * 
	 * @usedby theme_options::display()
	 * @return string html string for options
	 * @version 3.1.7
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function display(){
		?>
		<div class="wrap">
			<?php if(isset($_GET['updated'])){?>
				<div id="settings-updated">
					<?php echo status_tip('success',___('Settings have been saved.'));?>
				</div>
			<?php } ?>
			<form id="backend-options-frm" method="post">
				<?php
				/**
				 * loading
				 */
				echo '<div class="backend-tab-loading">' . status_tip('loading',___('Loading, please wait...')) . '</div>';
				?>
				<dl id="backend-tab" class="backend-tab">
					<?php do_action('before_base_settings');?>
					<dt title="<?php echo esc_attr(___('Theme common settings.'));?>"><span class="icon-setting"></span><span class="after-icon hide-on-mobile"><?php echo esc_html(___('Basic Settings'));?></span></dt>
					<dd>
						<!-- the action of base_settings -->
						<?php do_action('base_settings');?>
					</dd><!-- BASE SETTINGS -->
					
					<?php do_action('before_page_settings');?>
					<dt title="<?php echo esc_attr(___('Theme appearance/template settings.'));?>"><span class="icon-insert-template"></span><span class="after-icon hide-on-mobile"><?php echo esc_html(___('Page Settings'));?></span></dt>
					<dd>
						<!-- the action of page_settings -->
						<?php do_action('page_settings');?>
					</dd><!-- PAGE SETTINGS -->
					
					<?php do_action('before_advanced_settings');?>
					<dt title="<?php echo esc_attr(___('Theme special settings, you need to know what are you doing.'));?>"><span class="icon-settings"></span><span class="after-icon hide-on-mobile"><?php echo esc_html(___('Advanced Settings'));?></span></dt>
					<dd>
						<!-- the action of advanced_settings -->
						<?php do_action('advanced_settings');?>
					</dd><!-- ADVANCED SETTINGS -->
										
					<?php do_action('before_dev_settings');?>
					<dt><span class="icon-console"></span><span class="after-icon hide-on-mobile"><?php echo esc_html(___('Developer Mode'));?></span></dt>
					<dd>
						<?php do_action('dev_settings');?>
					</dd><!-- DEVELOPER SETTINGS -->
					
					<?php do_action('before_help_settings');?>
					<dt><span class="icon-help"></span><span class="after-icon hide-on-mobile"><?php echo esc_html(___('About & Help'));?></span></dt>
					<dd>
						<?php do_action('help_settings');?>
					</dd><!-- ABOUT and HELP -->
					<?php do_action('after_help_settings');?>
				</dl>
		
				<p>
					<input type="hidden" value="save_options" name="action" />
					<input type="submit" value="<?php echo esc_attr(___('Save all settings'));?>" class="button button-primary button-large"/>
					<label for="options-reset" class="label-options-reset" title="<?php echo esc_attr(___('Something error with theme? Try to restore:)'));?>"><input id="options-reset" name="reset_options" type="checkbox" value="1"/> <?php echo esc_html(___('Restore default theme options'));?></label>
				</p>
			</form>
		</div>
		<?php
	}
	/**
	 * Save Options
	 * 
	 * 
	 * @return n/a
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function save_options(){
		$options = null;
		/** Check the action and save options */
		if(isset($_POST['action']) && $_POST['action'] === 'save_options'){
			/** Add Hook */
			$options = apply_filters(self::$iden . '_save',$options);
			/** Reset the options? */
			if(isset($_POST['reset_options'])){
				/** Delete theme options */
				delete_option(self::$iden);
			}else{
				/** Update theme options */
				update_option(self::$iden,$options);
			}
		}
	}
	/**
	 * set_options
	 *
	 * @param string options key
	 * @param mixd
	 * @return array options
	 * @version 1.0.2
	 * @author KM@INN STUDIO
	 */
	public static function set_options($key = null,$data = null){
		$options = self::get_options();
		if(!$key || !$data) return $options;
		$options[$key] = $data;
		update_option(self::$iden,$options);
		return $options;
	}
	/**
	 * delete_options
	 *
	 * @param string
	 * @return 
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function delete_options($key = null){
		if(!$key) return false;
		$options = self::get_options();
		unset($options[$key]);
		update_option(self::$iden,$options);
	}
	/**
	 * is_options_page
	 * 
	 * @return bool
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	private static function is_options_page(){
		if(is_admin() && isset($_GET['page']) && $_GET['page'] === basename(__FILE__,'.php')){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * redirect
	 * 
	 * @return n/a
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	private static function redirect(){
		if(self::is_options_page() && isset($_POST['action']) && $_POST['action'] === 'save_options'){
			if(isset($_GET['updated']) && $_GET['updated'] === 'true'){
				$redirect_updated = null;
			}else{
				$redirect_updated = '&updated=true';
			}
			/** refer */
			header('Location: '.get_current_url() . $redirect_updated);
		}
	}
	/**
	 * Add to page
	 * 
	 * 
	 * @return n/a
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function add_page(){
		/* Add to theme setting menu */
		add_theme_page(___('Theme settings'),___('Theme settings'), 'edit_themes', 'core-options','theme_options::display');
	}
	/**
	 * Add admin bar
	 * 
	 * 
	 * @return 
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function add_bar(){
		if(!current_user_can('manage_options')) return false;
		
		global $wp_admin_bar;
		$wp_admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id' => 'theme_settings',
			'title' => ___('Theme settings'),
			'href' => admin_url('themes.php?page=core-options')
		));
	}
}
/* End Theme Options */
?>