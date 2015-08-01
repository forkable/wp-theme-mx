<?php
/*
Feature Name:	Post Copyright
Feature URI:	http://www.inn-studio.com
Version:		2.0.1
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
		?>
		<fieldset>
			<legend><?= ___('Post copyright settings');?></legend>
			<p class="description">
				<?= ___('Posts copyright settings maybe protect your word. Here are some keywords that can be used:');?></p>
			<p class="description">
				<input type="text" class="small-text text-select" value="%post_title_text%" title="<?= ___('Post Title text');?>" readonly/>
				<input type="text" class="small-text text-select" value="%post_url%" title="<?= ___('Post URL');?>" readonly/>
				<input type="text" class="small-text text-select" value="%blog_name%" title="<?= ___('Blog name');?>" readonly/>
				<input type="text" class="small-text text-select" value="%blog_url%" title="<?= ___('Blog URL');?>" readonly/>
			</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="<?= self::$iden;?>-enabled"><?= ___('Enable or not?');?></label></th>
						<td>
							<label for="<?= self::$iden;?>-enabled">
								<input type="checkbox" name="<?= self::$iden;?>[enabled]" id="<?= self::$iden;?>-enabled" value="1" <?= self::is_enabled() ? 'checked' : null;?> />
								<?= ___('Enable');?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="<?= self::$iden;?>-code"><?= ___('HTML code:');?></label></th>
						<td>
							<textarea id="<?= self::$iden;?>-code" name="<?= self::$iden;?>[code]" class="widefat code" rows="10"><?= $code;?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><?= ___('Restore');?></th>
						<td>
							<label for="<?= self::$iden;?>-restore">
								<input type="checkbox" id="<?= self::$iden;?>-restore" name="<?= self::$iden;?>[restore]" value="1"/>
								<?= ___('Restore the post copyright settings');?>
							</label>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = theme_options::get_options(self::$iden);

		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;

		return $caches;
	}
	public static function is_enabled(){
		return self::get_options('enabled') == 1 ? true : false;
	}
	/**
	 * options_default
	 * 
	 * 
	 * @return array
	 * @version 1.0.0
	 * 
	 */
	public static function options_default(array $opts = []){
		
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
	public static function options_save(array $opts = []){
		if(isset($_POST[self::$iden]) && !isset($_POST[self::$iden]['restore'])){
			$opts[self::$iden] = $_POST[self::$iden];
		}
		return $opts;
	}
	/**
	 * output
	 */
	public static function display_frontend(){
		global $post;
		$tpl_keywords = [
			'%post_title_text%',
			'%post_url%',
			'%blog_name%',
			'%blog_url%'
		];
		$output_keywords = [
			theme_cache::get_the_title($post->ID),
			theme_cache::get_permalink($post->ID),
			theme_cache::get_bloginfo('name'),
			theme_cache::home_url()
		];
		$codes = str_replace(
			$tpl_keywords,
			$output_keywords,
			self::get_options('code')
		);
		echo stripslashes($codes);
	}
}
