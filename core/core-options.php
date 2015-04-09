<?php 
/**
 * Theme Options
 * the theme options and show admin control planel
 * 
 * @version 5.0.0
 * @author KM@INN STUDIO
 * 
 */
add_action('admin_init','theme_options::init' );
add_action('admin_menu','theme_options::add_page');
add_action('admin_bar_menu','theme_options::add_bar',61);
class theme_options{
	public static $iden = 'theme_options';
	public static $opts = [];
	/**
	 * init
	 * 
	 * @return 
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function init(){
		if(!self::is_options_page())
			return false;
			
		add_action('admin_head',__CLASS__ . '::backend_header');
		self::options_save();
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

		/** Default options hook */
		self::$opts = wp_parse_args(get_theme_mods(),apply_filters('theme_options_default',[]));

		if($key){
			return isset(self::$opts[$key]) ? self::$opts[$key] : null;
		}else{
			return self::$opts;
		}
	}
	public static function backend_header(){
		if(!current_user_can('manage_options'))
			return false;


		if(!self::is_options_page())
			return false;

		echo theme_features::get_theme_css('modules/fa-fonts','normal');
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
			<form id="backend-options-frm" method="post" action="">
				
				<div class="backend-tab-loading"><?php echo status_tip('loading',___('Loading, please wait...'));?></div>
				
				<dl id="backend-tab" class="backend-tab">
					<?php do_action('before_base_settings');?>
					<dt title="<?php echo ___('Theme common settings.');?>">
						<i class="fa fa-cog"></i>
						<span class="tx"><?php echo ___('Basic Settings');?></span>
					</dt>
					<dd>
						<!-- the action of base_settings -->
						<?php do_action('base_settings');?>
					</dd><!-- BASE SETTINGS -->
					
					<?php do_action('before_page_settings');?>
					<dt title="<?php echo ___('Theme appearance/template settings.');?>">
						<i class="fa fa-paint-brush"></i>
						<span class="tx"><?php echo ___('Page Settings');?></span>
					</dt>
					<dd>
						<!-- the action of page_settings -->
						<?php do_action('page_settings');?>
					</dd><!-- PAGE SETTINGS -->
					
					<?php do_action('before_advanced_settings');?>
					<dt title="<?php echo ___('Theme special settings, you need to know what are you doing.');?>">
						<i class="fa fa-cogs"></i>
						<span class="tx"><?php echo ___('Advanced Settings');?></span>
					</dt>
					<dd>
						<!-- the action of advanced_settings -->
						<?php do_action('advanced_settings');?>
					</dd><!-- ADVANCED SETTINGS -->
										
					<?php do_action('before_dev_settings');?>
					<dt>
						<i class="fa fa-code"></i>
						<span class="tx"><?php echo ___('Developer Mode');?></span>
					</dt>
					<dd>
						<?php do_action('dev_settings');?>
					</dd><!-- DEVELOPER SETTINGS -->
					
					<?php do_action('before_help_settings');?>
					<dt>
						<i class="fa fa-question-circle"></i>
						<span class="tx"><?php echo ___('About &amp; Help');?></span>
					</dt>
					<dd>
						<?php do_action('help_settings');?>
					</dd><!-- ABOUT and HELP -->
					<?php do_action('after_help_settings');?>
				</dl>
		
				<p>
					<input type="hidden" value="options-save" name="action" />
					<input type="submit" value="<?php echo ___('Save all settings');?>" class="button button-primary button-large"/>
					<label for="options-restore" class="label-options-restore" title="<?php echo ___('Something error with theme? Try to restore. Be careful, theme options will be cleared up!');?>">
						<input id="options-restore" name="options-restore" type="checkbox" value="1"/>
						<?php echo ___('Restore to theme default options');?>
					</label>
				</p>
			</form>
		</div>
		<?php
	}
	/**
	 * Save Options
	 * 
	 * @version 2.0.0
	 * @author KM@INN STUDIO
	 * 
	 */
	private static function options_save(){
		if(!current_user_can('manage_options'))
			return false;
		/** Check the action and save options */
		if(isset($_POST['action']) && $_POST['action'] === 'options-save'){
			$opts_old = get_theme_mods();
			$opts_new = apply_filters(self::$iden . '_save',[]);

			/** Reset the options? */
			if(isset($_POST['options-restore'])){
				/** Delete theme options */
				$opts_new = array_diff($opts_old,$opts_new);
			}else{
				$opts_new = array_replace($opts_old,$opts_new);
			}
			update_option('theme_mods_' . theme_functions::$iden,$opts_new);
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
	public static function set_options($key,$data){
		self::$opts = self::get_options();		
		self::$opts[$key] = $data;
		update_option('theme_mods_' . theme_functions::$iden,self::$opts);
		return self::$opts;
	}
	/**
	 * delete_options
	 *
	 * @param string
	 * @return 
	 * @version 1.0.2
	 * @author KM@INN STUDIO
	 */
	public static function delete_options($key){
		self::$opts = self::get_options();
		if(!isset(self::$opts[$key]))
			return false;
		
		unset(self::$opts[$key]);
		update_option('theme_mods_' . theme_functions::$iden,self::$opts);
		return self::$opts;
	}
	/**
	 * is_options_page
	 * 
	 * @return bool
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 */
	private static function is_options_page(){
		if(!current_user_can('manage_options'))
			return false;
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
		if(!current_user_can('manage_options'))
			return false;
		if(self::is_options_page() && isset($_POST['action']) && $_POST['action'] === 'options-save'){
			if(isset($_GET['updated'])){
				wp_redirect(get_current_url());
			}else{
				wp_redirect(add_query_arg('updated',true,get_current_url()));
			}
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
		if(!current_user_can('manage_options'))
			return false;
		/* Add to theme setting menu */
		add_theme_page(___('Theme settings'),___('Theme settings'), 'edit_themes', 'core-options',__CLASS__ . '::display');
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
		if(!current_user_can('manage_options'))
			return false;
		
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