<?php
/*
Feature Name:	Maintenance Mode
Feature URI:	http://www.inn-studio.com
Version:		1.1.5
Description:	Site in the background to maintain or measured using the change function, visitors will jump to a specified page, the administrator will not.
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'maintenance_mode::init';
	return $fns;
});
class maintenance_mode{
	public static $iden = 'maintenance_mode';
	
	public static function init(){
		add_action('dev_settings', __CLASS__ . '::display_backend',90);
		add_action('after_setup_theme', __CLASS__ . '::redirect');
		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_action('wp_ajax_nopriv_' . self::$iden, __CLASS__ . '::process');
	
	}
	public static function get_options($key = null){
		static $caches = [];
		if(!isset($caches[self::$iden]))
			$caches[self::$iden] = theme_options::get_options(self::$iden);

		if($key){
			return isset($caches[self::$iden][$key]) ? $caches[self::$iden][$key] : null;
		}else{
			return $caches[self::$iden];
		}
	}
	public static function has_url(){
		static $caches = [];
		if(!isset($caches[self::$iden]))
			$caches[self::$iden] = trim(esc_url(self::get_options('url')));

		return empty($caches[self::$iden]) ? null : $caches[self::$iden];
	}
	public static function display_backend(){
		
		$options = self::get_options();
		$url = isset($options['url']) ?  stripslashes($options['url']): null
		?>
		<!-- maintenance_mode -->
		<fieldset>
			<legend><?= ___('Maintenance Mode');?></legend>
			<p class="description"><?= esc_html(___('If your site needs to test privately, maybe fill a URL in the redirect area that the the visitors will see the redirect page but yourself, otherwise left blank.'));?></p>
			<p class="description"><strong><?= ___('Attention: if theme has frontend log-in page, please DO NOT use maintenance mode, or you can not log-in to background.');?></strong></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="<?= self::$iden;?>-url"><?= ___('Redirect URL (include http://):');?></label></th>
						<td>
							<input type="url" id="<?= self::$iden;?>-url" name="<?= self::$iden;?>[url]" class="widefat" value="<?= $url;?>"/>
							
							<p class="description">
								<?= ___('Optional template URL: ');?>
							
								<input type="url" class="widfat text-select" value="<?= theme_features::get_process_url(array('action'=>self::$iden));?>" readonly />
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	}
	public static function process($output){
		if(!self::has_url())
			return;
		$output = '
<!doctype html>
<html lang="' . get_bloginfo('language') . '">
	<head>
	<meta charset="' . get_bloginfo( 'charset' ) . '">
	<title>' . esc_attr(get_bloginfo('name')) . ' - ' . ___('Maintenance Mode') . '</title>
	<style>
	body {font:20px/2 "Microsoft YaHei",Arial,"Liberation Sans",FreeSans,sans-serif;text-align: center; padding: 150px; color: #333;}
	article { display: block; text-align: left; width: 650px; margin: 0 auto; }
	a { color: #dc8100; text-decoration: none; }
	a:hover { color: #333; }
	.by{text-align:right;}
	</style>
	</head>
	 <body>
		<article>
		<h1>' . ___('We&rsquo;ll be back soon!') . '</h1>
		<p>' . sprintf(___('Sorry for the inconvenience but we&rsquo;re performing some maintenance at the moment. If you need to you can always <a href="mailto:%s">contact us</a>, otherwise we&rsquo;ll be back online shortly!'),esc_html(get_bloginfo('admin_email'))) . '</p>
		<p class="by">&mdash; ' . esc_html(get_bloginfo('name')) . '</p>
		</article>
	</body>
</html>
		';
		die($output);
		
	}
	/**
	 * Save options
	 */
	public static function options_save($options){
		if(isset($_POST[self::$iden])){
			$options[self::$iden] = $_POST[self::$iden];
		}
		return $options;
	}
	/**
	 * Redirect
	 */
	public static function redirect(){
		$url = self::has_url();
		if(!current_user_can('manage_options') && $url){
			header("Location: $url");
			die();
		}
	}
}
?>