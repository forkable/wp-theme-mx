<?php 
/**
 * Theme Options
 * the theme options and show admin control planel
 * 
 * @version 5.0.1
 * @author KM@INN STUDIO
 * 
 */
theme_options::init();
class theme_options{
	public static $iden = 'theme_options';
	public static $opts = [];
	/**
	 * init
	 * 
	 * @return 
	 * @version 2.0.0
	 * @author KM@INN STUDIO
	 */
	public static function init(){
		
		add_action('admin_menu', __CLASS__ . '::add_page');
		add_action('admin_bar_menu', __CLASS__ . '::add_bar',61);
		add_action('wp_ajax_' . self::$iden , __CLASS__ . '::process');
		
		add_action('admin_init', __CLASS__ . '::admin_init' );

	}
	public static function admin_init(){
		if(!self::is_options_page())
			return false;
		add_action('admin_head',__CLASS__ . '::backend_header');
		add_action('backend_seajs_alias', __CLASS__ . '::backend_seajs_alias');
	}
	/**
	 * get the theme options from the features default value or DB.
	 * 
	 * @usedby theme_options::get_options()
	 * @return array
	 * @version 2.0.0
	 * @since 3.1.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_options($key = null){

		if(empty(self::$opts)){
		/** Default options hook */
			self::$opts = array_merge(
				apply_filters('theme_options_default',[]),
				(array)get_theme_mod(self::$iden)
			);
		}

		if($key){
			return isset(self::$opts[$key]) ? self::$opts[$key] : null;
		}else{
			return self::$opts;
		}
	}
	public static function process(){
		
		if(!isset($_POST[self::$iden]['nonce']))
			die();
			
		if(!wp_verify_nonce($_POST[self::$iden]['nonce'],self::$iden))
			die();
		
		self::options_save();
		
		wp_redirect(add_query_arg(
			'updated',
			true,
			self::get_url()
		));
		die();
	}
	public static function get_url(){
		static $caches = [];
		if(!isset($caches[self::$iden]))
			$caches[self::$iden] = admin_url('themes.php?page=core-options');

		return $caches[self::$iden];
	}
	public static function current_user_can($key){
		static $caches = [];
		if(!isset($caches[$key]))
			$caches[$key] = current_user_can($key);

		return $caches[$key];
	}
	public static function backend_header(){
		if(!self::current_user_can('manage_options'))
			return false;


		if(!self::is_options_page())
			return false;
		?>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css">
		<?php
		echo theme_features::get_theme_css('backend/style','normal');
		/**
		 * add admin_css hook 
		 */
		do_action('backend_css');
		?><script id="seajsnode" src="https://cdnjs.cloudflare.com/ajax/libs/seajs/3.0.1/sea.js"></script>
		<script>
		<?php
		$config = [];
		$config['base'] = theme_features::get_theme_js();
		$config['paths'] = array(
			'theme_js' => theme_features::get_theme_js(),
			'theme_css' => theme_features::get_theme_css(),
		);
		$config['vars'] = array(
			'locale' => str_replace('-','_',get_bloginfo('language')),
			'theme_js' => theme_features::get_theme_js(),
			'theme_css' => theme_features::get_theme_css(),
			'process_url' => esc_js(theme_features::get_process_url()),
		);
		$config['map'] = array(
			['.css','.css?v=' . theme_file_timestamp::get_timestamp()],
			['.js','.js?v=' . theme_file_timestamp::get_timestamp()]
		);
		/** 
		 * seajs hook
		 */
		$config['paths'] = apply_filters('backend_seajs_paths',$config['paths']);
		$config['alias'] = apply_filters('backend_seajs_alias',[]);
		$config['vars'] = apply_filters('backend_seajs_vars',$config['vars']);
		$config['map'] = apply_filters('backend_seajs_map',$config['map']);

		?>
		seajs.config(<?= json_encode($config);?>);
		<?php do_action('before_backend_tab_init');?>
		seajs.use('backend',function(m){
			m.init({
				done : function($btn,$cont,$tab){
					<?php do_action('after_backend_tab_init');?>
				},
				custom : function(b,c,i,t){
					<?php do_action('after_backend_tab_custom');?>
				},
				tab_title : '<?= wp_get_theme();?> <?= ___('theme settings');?>'
			});
		});
		</script>
		<?php	
	}
	public static function backend_seajs_alias(array $alias = []){
		$alias['backend'] = theme_features::get_theme_js('backend');
		return $alias;
	}
	/**
	 * show the options settings for admin theme setting page.
	 * 
	 * @return string html string for options
	 * @version 3.1.7
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function display_backend(){
		?>
		<div class="wrap">
			<?php if(isset($_GET['updated'])){?>
				<div id="settings-updated">
					<?= status_tip('success',___('Settings have been saved.'));?>
				</div>
			<?php } ?>
			<form id="backend-options-frm" method="post" action="<?= theme_features::get_process_url([
				'action' => self::$iden,
			]);?>">
				
				<div class="backend-tab-loading"><?= status_tip('loading',___('Loading, please wait...'));?></div>
				
				<dl id="backend-tab" class="backend-tab">
					<?php do_action('before_base_settings');?>
					<dt title="<?= ___('Theme common settings.');?>">
						<i class="fa fa-fw fa-cog"></i>
						<span class="tx"><?= ___('Basic Settings');?></span>
					</dt>
					<dd>
						<!-- the action of base_settings -->
						<?php do_action('base_settings');?>
					</dd><!-- BASE SETTINGS -->
					
					<?php do_action('before_page_settings');?>
					<dt title="<?= ___('Theme appearance/template settings.');?>">
						<i class="fa fa-fw fa-paint-brush"></i>
						<span class="tx"><?= ___('Page Settings');?></span>
					</dt>
					<dd>
						<!-- the action of page_settings -->
						<?php do_action('page_settings');?>
					</dd><!-- PAGE SETTINGS -->
					
					<?php do_action('before_advanced_settings');?>
					<dt title="<?= ___('Theme special settings, you need to know what are you doing.');?>">
						<i class="fa fa-fw fa-cogs"></i>
						<span class="tx"><?= ___('Advanced Settings');?></span>
					</dt>
					<dd>
						<!-- the action of advanced_settings -->
						<?php do_action('advanced_settings');?>
					</dd><!-- ADVANCED SETTINGS -->
										
					<?php do_action('before_dev_settings');?>
					<dt>
						<i class="fa fa-fw fa-code"></i>
						<span class="tx"><?= ___('Developer Mode');?></span>
					</dt>
					<dd>
						<?php do_action('dev_settings');?>
					</dd><!-- DEVELOPER SETTINGS -->
					
					<?php do_action('before_help_settings');?>
					<dt>
						<i class="fa fa-fw fa-question-circle"></i>
						<span class="tx"><?= ___('About &amp; Help');?></span>
					</dt>
					<dd>
						<?php do_action('help_settings');?>
					</dd><!-- ABOUT and HELP -->
					<?php do_action('after_help_settings');?>
				</dl>
		
				<p>
					<input type="hidden" name="<?= self::$iden;?>[nonce]" value="<?= wp_create_nonce(self::$iden);?>">
					
					<button type="submit" class="button button-primary button-large"><i class="fa fa-check"></i> <?= ___('Save all settings');?></button>
					
					<label for="options-restore" class="label-options-restore" title="<?= ___('Something error with theme? Try to restore. Be careful, theme options will be cleared up!');?>">
						<input id="options-restore" name="<?= self::$iden;?>[restore]" type="checkbox" value="1"/>
						<?= ___('Restore to theme default options');?> <i class="fa fa-question-circle"></i>
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
		if(!self::current_user_can('manage_options'))
			return false;

		$opts_new = apply_filters(self::$iden . '_save',[]);
		
		/** Reset the options? */
		if(isset($_POST[self::$iden]['restore'])){
			/** Delete theme options */
			set_theme_mod(self::$iden,[]);
		}else{
			set_theme_mod(self::$iden,$opts_new);
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
		set_theme_mod(self::$iden,self::$opts);
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
		set_theme_mod(self::$iden,self::$opts);
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
		if(!self::current_user_can('manage_options'))
			return false;
			
		if(is_admin() && isset($_GET['page']) && $_GET['page'] === 'core-options'){
			return true;
		}else{
			return false;
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
		if(!self::current_user_can('manage_options'))
			return false;
		/* Add to theme setting menu */
		add_theme_page(
			___('Theme settings'),
			___('Theme settings'), 
			'edit_themes', 
			'core-options',
			__CLASS__ . '::display_backend'
		);
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
		if(!self::current_user_can('manage_options'))
			return false;
		
		global $wp_admin_bar;
		$wp_admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id' => 'theme_settings',
			'title' => ___('Theme settings'),
			'href' => self::get_url()
		));
	}
}
