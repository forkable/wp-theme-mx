<?php
/*
Feature Name:	Post Copyright
Feature URI:	http://www.inn-studio.com
Version:		2.0.0
Description:	Your post notes on copyright information, although this is not very rigorous.
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_post_copyright::init';
	return $fns;
});
class theme_post_copyright{
	private static $iden = 'theme_post_copyright';
	public static function init(){
		add_action('page_settings',__CLASS__ . '::display_backend');
		add_filter('theme_options_default',__CLASS__ . '::options_default');
		add_filter('theme_options_save',__CLASS__ . '::options_save');
	
	}
	public static function display_backend(){
		$opt = theme_options::get_options(self::$iden);
		$code = isset($opt['code']) ? stripslashes($opt['code']) : null;
		$is_checked = isset($opt['enabled']) && $opt['enabled'] == 1 ? ' checked ' : null;
		?>
		<fieldset>
			<legend><?php echo ___('Post copyright settings');?></legend>
			<p class="description">
				<?php echo ___('Posts copyright settings maybe protect your word. Here are some keywords that can be used:');?></p>
			<p class="description">
				<input type="text" class="small-text text-select" value="%post_title_text%" title="<?php echo ___('Post Title text');?>" readonly="true"/>
				<input type="text" class="small-text text-select" value="%post_url%" title="<?php echo ___('Post URL');?>" readonly="true"/>
				<input type="text" class="small-text text-select" value="%blog_name%" title="<?php echo ___('Blog name');?>" readonly="true"/>
				<input type="text" class="small-text text-select" value="%blog_url%" title="<?php echo ___('Blog URL');?>" readonly="true"/>
			</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="<?php echo self::$iden;?>-enabled"><?php echo ___('Enable or not?');?></label></th>
						<td><input type="checkbox" name="<?php echo self::$iden;?>[enabled]" id="<?php echo self::$iden;?>-enabled" value="1" <?php echo $is_checked;?> /><label for="<?php echo self::$iden;?>-enabled"><?php echo ___('Enable');?></label></td>
					</tr>
					<tr>
						<th scope="row"><label for="<?php echo self::$iden;?>-code"><?php echo ___('HTML code:');?></label></th>
						<td>
							<textarea id="<?php echo self::$iden;?>-code" name="<?php echo self::$iden;?>[code]" class="widefat code" rows="10"><?php echo $code;?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo ___('Restore');?></th>
						<td>
							<label for="<?php echo self::$iden;?>-restore">
								<input type="checkbox" id="<?php echo self::$iden;?>-restore" name="<?php echo self::$iden;?>[restore]" value="1"/>
								<?php echo ___('Restore the post copyright settings');?>
							</label>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function is_enabled(){
		$opt = theme_options::get_options(self::$iden);
		return isset($opt['enabled']) && $opt['enabled'] == 1;
	}
	/**
	 * options_default
	 * 
	 * 
	 * @return array
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function options_default($opts){
		
		$opts[self::$iden]['code'] = '
<ul>
	<li>
		' . ___('Permanent URL: '). '<a href="%post_url%">%post_url%</a>
	</li>
	<li>
		' . ___('Welcome to reprint: '). ___('Addition to indicate the original, the article of <a href="%blog_url%">%blog_name%</a> comes from internet. If infringement, please contact me to delete.'). '
	</li>
</ul>';
		$opts[self::$iden]['enabled'] = 1;
		return $opts;
	}
	/**
	 * save 
	 */
	public static function options_save($opts){
		if(isset($_POST[self::$iden]) && !isset($_POST[self::$iden]['restore'])){
			$opt = isset($_POST[self::$iden]) ? $_POST[self::$iden] : null;
		}
		return $opts;
	}
	/**
	 * output
	 */
	public static function display_frontend(){
		global $post;
		$opt = theme_options::get_options(self::$iden);
		$tpl_keywords = array('%post_title_text%','%post_url%','%blog_name%','%blog_url%');
		$output_keywords = array(get_the_title(),get_permalink(),get_bloginfo('name'),home_url());
		$codes = str_replace($tpl_keywords,$output_keywords,$opt['code']);
		echo stripslashes($codes);
	}
}
