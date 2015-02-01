<?php
/*
Feature Name:	Post Copyright
Feature URI:	http://www.inn-studio.com
Version:		1.1.0
Description:	Your post notes on copyright information, although this is not very rigorous.
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
theme_post_copyright::init();
class theme_post_copyright{
	private static $iden = 'theme_post_copyright';
	public static function init(){
		add_action('page_settings',get_class() . '::admin');
		add_filter('theme_options_default',get_class() . '::options_default');
		add_filter('theme_options_save',get_class() . '::save');
	
	}
	public static function admin(){
		
		$options = theme_options::get_options();
		$code = isset($options[self::$iden]['code']) ? stripslashes($options[self::$iden]['code']) : null;
		$is_checked = isset($options[self::$iden]['on']) ? ' checked ' : null;
		?>
		<fieldset>
			<legend><?php echo ___('Post Copyright Settings');?></legend>
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
						<th scope="row"><label for="<?php echo self::$iden;?>_on"><?php echo ___('Enable or not?');?></label></th>
						<td><input type="checkbox" name="<?php echo self::$iden;?>[on]" id="<?php echo self::$iden;?>_on" value="1" <?php echo $is_checked;?> /><label for="<?php echo self::$iden;?>_on"><?php echo ___('Enable');?></label></td>
					</tr>
					<tr>
						<th scope="row"><?php echo ___('HTML code:');?></th>
						<td>
							<textarea id="<?php echo self::$iden;?>_code" name="<?php echo self::$iden;?>[code]" class="widefat code" rows="10"><?php echo $code;?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html(___('Restore'));?></th>
						<td>
							<label for="<?php echo self::$iden;?>_restore">
								<input type="checkbox" id="<?php echo self::$iden;?>_restore" name="<?php echo self::$iden;?>[restore]" value="1"/>
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
		return isset($opt['on']) ? true : false;
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
	public static function options_default($options){
		
		$options[self::$iden]['code'] = '
<ul>
	<li>
		' . esc_html(___('Permanent URL: ')). '<a href="%post_url%">%post_url%</a>
	</li>
	<li>
		' . esc_html(___('Welcome to reprint: ')). ___('Addition to indicate the original, the article of <a href="%blog_url%">%blog_name%</a> comes from internet. If infringement, please contact me to delete.'). '
	</li>
</ul>';
		$options[self::$iden]['on'] = 1;
		return $options;
	}
	/**
	 * save 
	 */
	public static function save($options){
		if(isset($_POST[self::$iden]) && !isset($_POST[self::$iden]['restore'])){
			$options[self::$iden] = isset($_POST[self::$iden]) ? $_POST[self::$iden] : null;
		}
		return $options;
	}
	/**
	 * output
	 */
	public static function display(){
		global $post;
		$options = theme_options::get_options();
		$tpl_keywords = array('%post_title_text%','%post_url%','%blog_name%','%blog_url%');
		$output_keywords = array(get_the_title(),get_permalink(),get_bloginfo('name'),home_url());
		$codes = str_ireplace($tpl_keywords,$output_keywords,$options[self::$iden]['code']);
		echo stripslashes($codes);
	}
}
