<?php
/*
Plugin Name: Gravatar fix
Plugin URI: http://inn-studio.com/gravatar-fix
Description: A simple and easy way to fix your gravatar can not be show in China. Replace by eqoe.cn. 
Author: INN STUDIO
Author URI: http://inn-studio.com
Version: 1.1.1
*/
if(!class_exists('theme_gravatar_fix')){
	add_filter('theme_includes',function($fns){
		$fns[] = 'theme_gravatar_fix::init';
		return $fns;
	});
	class theme_gravatar_fix{
		public static $iden = 'theme_gravatar_fix';
		public static function init(){
			add_filter('get_avatar_url', __CLASS__ . '::get_avatar_url');			
			add_filter('theme_options_save', __CLASS__ . '::options_save');
			
			add_action('page_settings', __CLASS__ . '::display_backend');
			
			add_filter('theme_options_default', __CLASS__ . '::options_default');
			
		}
		public static function display_backend(){
			?>
			<fieldset>
				<legend><?= ___('Gravatar fix');?></legend>
				<p class="description"><?= ___('This feature can fix the gravatar image to display in China. Just for Chinese users. If using "Custom default gravatar" please DO NOT enable this feature.');?></p>
				<table class="form-table">
					<tbody>
						<tr>
							<th><label for="<?= self::$iden;?>-enabled"><?= ___('Enabled or not?');?></label></th>
							<td>
								<label for="<?= self::$iden;?>-enabled">
									<input type="checkbox" id="<?= self::$iden;?>-enabled" name="<?= self::$iden;?>[enabled]" value="1" <?= self::is_enabled() ? 'checked' : null;?> >
									<?= ___('Enable');?>
								</label>
							</td>
						</tr>
					</tbody>
				</table>
			</fieldset>
			<?php
		}
		public static function options_save(array $opts = []){
			if(isset($_POST[self::$iden])){
				$opts[self::$iden] = $_POST[self::$iden];
			}else{
				$opts[self::$iden]['enabled'] = -1;
			}
			return $opts;
		}
		public static function options_default(array $opts = []){
			static $is_zhcn = null;
			if($is_zhcn === null)
				$is_zhcn = get_locale() === 'zh_CN' ? 1 : -1;
				
			$opts[self::$iden]['enabled'] = $is_zhcn;
			return $opts;
		}
		public static function is_enabled(){
			return self::get_options('enabled') == -1;
		}
		public static function get_options($key = null){
			static $caches = null;
			if($caches === null)
				$caches = theme_options::get_options(self::$iden);

			if($key)
				return isset($caches[$key]) ? $caches[$key] : false;
			return $caches;
		}
		public static function get_avatar_url($url){
			
			if(!self::is_enabled())
				return $url;
				
			/** if is SSL */
			if(strpos($url,'https://') === 0){
				$url = preg_replace('/(\d)?(secure)?([a-z]{0,2})\.gravatar\.com\/avatar/i', 'gravatar.tycdn.net/avatar', $url);
			/** Not SSL */
			}else{
				$url = preg_replace('/(\d)?([a-z]{0,2})\.gravatar\.com\/avatar/i', 'gravatar.eqoe.cn/avatar', $url);
			}
			return $url;
		}
	}
}