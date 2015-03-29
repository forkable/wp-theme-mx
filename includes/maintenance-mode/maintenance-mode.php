<?php
/*
Feature Name:	Maintenance Mode
Feature URI:	http://www.inn-studio.com
Version:		1.1.3
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
		add_action('base_settings', __CLASS__ . '::backend_display',90);
		add_action('wp_head', __CLASS__ . '::redirect',10);
		add_filter('theme_options_save', __CLASS__ . '::options_save');
		add_action('wp_ajax_nopriv_maintenance_mode', __CLASS__ . '::process');
	
	}
	/**
	 * Admin Display
	 */
	public static function backend_display(){
		
		$options = theme_options::get_options();
		$maintenance_mode = isset($options['maintenance_site_url']) ?  stripslashes($options['maintenance_site_url']): null
		?>
		<!-- maintenance_mode -->
		<fieldset>
			<legend><?php echo esc_html(___('Maintenance Mode'));?></legend>
			<p class="description"><?php echo esc_html(___('If your site needs to test privately, maybe fill a URL in the redirect area that the the visitors will see the redirect page but yourself, otherwise left blank.'));?></p>
			<p class="description"><strong><?php echo esc_html(___('Attention: if theme has frontend log-in page, please DO NOT use maintenance mode, or you can not log-in to background.'));?></strong></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="maintenance_site_url"><?php echo ___('Redirect URL (include http://):');?></label></th>
						<td><input type="url" id="maintenance_site_url" name="maintenance_site_url" class="widefat" value="<?php echo $maintenance_mode;?>"/>
							<p class="description"><?php echo ___('Optional template URL: ');?><input type="url" class="regular-text text-select" value="<?php echo theme_features::get_process_url(array('action'=>'maintenance_mode'));?>" readonly /></p>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	}
	public static function process($output){
		
		$output = '
<!doctype html>
<html lang="' . get_bloginfo('language') . '">
	<head>
	<meta charset="' . get_bloginfo( 'charset' ) . '">
	<title>' . esc_attr(get_bloginfo('name')) . ' - ' . esc_attr(___('Maintenance Mode')) . '</title>
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
		<h1>' . esc_html(___('We&rsquo;ll be back soon!')) . '</h1>
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
		$options['maintenance_site_url'] = isset($_POST['maintenance_site_url']) ? esc_url($_POST['maintenance_site_url']) : null;
		return $options;
	}
	/**
	 * Redirect
	 */
	public static function redirect(){
		$options = theme_options::get_options();
		if(!current_user_can('administrator') && !empty($options['maintenance_site_url'])){
			header('Location: '.$options['maintenance_site_url']);
			die();
		}
	}
}
?>