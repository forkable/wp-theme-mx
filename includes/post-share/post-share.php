<?php
/*
Feature Name:	Post Share
Feature URI:	http://www.inn-studio.com
Version:		2.0.0
Description:	
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_post_share::init';
	return $fns;
});
class theme_post_share{
	public static $iden = 'theme_post_share';

	public static function init(){
		add_filter('theme_options_default',__CLASS__ . '::options_default');
		add_filter('theme_options_save',__CLASS__ . '::options_save');
		add_action('page_settings',__CLASS__ . '::backend_display');

		
		if(!self::is_enabled()) return false;

		add_filter('frontend_seajs_alias',	__CLASS__ . '::frontend_seajs_alias');
				
		add_action('frontend_seajs_use',__CLASS__ . '::frontend_seajs_use');

		add_action('wp_enqueue_scripts', 	__CLASS__ . '::frontend_css');
	}
	public static function get_options($key = null){
		static $caches;
		if(!$caches)
			$caches = theme_options::get_options(self::$iden);
		if($key){
			return isset($caches[$key]) ? $caches[$key] : null;
		}
		return $caches;
	}
	public static function display($args = []){
		global $post;
		$opt = self::get_options();
		$img_url = theme_features::get_thumbnail_src();
		$defaults = array(
			'post_title_text' => esc_attr(get_the_title()),
			'post_url' => esc_url(get_permalink()),
			'blog_name' => esc_attr(get_bloginfo('name')),
			'blog_url' => esc_url(home_url()),
			'img_url' => esc_url($img_url),
			'post_excerpt' => esc_attr(mb_substr(html_compress(strip_tags(get_the_excerpt())),0,120)),
			'post_content' => esc_attr(mb_substr(html_compress(strip_tags(get_the_content())),0,120)),
			'author' => esc_attr(get_the_author_meta('display_name',$post->post_author)),
		);
		$output_keywords = array_merge($defaults,$args);
	
		$tpl_keywords = array(
			'%post_title_text%',
			'%post_url%',
			'%blog_name%',
			'%blog_url%',
			'%img_url%',
			'%post_excerpt%',
			'%post_content%',
			'%author%'
			
		);
		$post_share_code = stripslashes(str_ireplace($tpl_keywords,$output_keywords,$opt['code']));

		echo $post_share_code;
	}
	
	public static function backend_display(){

		
		$opt = self::get_options();
		
		$is_checked = self::is_enabled() ? ' checked ' : null;
		?>
		<fieldset>
			<legend><?php echo ___('Posts share settings');?></legend>
			<p class="description">
				<?php echo ___('Share your post to everywhere. Here are some keywords that can be used:');?>
			</p>
			<p class="description">
				<input type="text" class="small-text text-select" value="%post_title_text%" title="<?php echo ___('Post Title text');?>" readonly />
				<input type="text" class="small-text text-select" value="%post_url%" title="<?php echo ___('Post URL');?>" readonly />
				<input type="text" class="small-text text-select" value="%blog_name%" title="<?php echo ___('Blog name');?>" readonly />
				<input type="text" class="small-text text-select" value="%blog_url%" title="<?php echo ___('Blog URL');?>" readonly />
				<input type="text" class="small-text text-select" value="%img_url%" title="<?php echo ___('The first picture of the post.');?>" readonly />
				<input type="text" class="small-text text-select" value="%post_excerpt%" title="<?php echo ___('The excerpt of post.');?>" readonly />
				<input type="text" class="small-text text-select" value="%post_content%" title="<?php echo ___('The content of post.');?>" readonly />
			</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="<?php echo self::$iden;?>_on"><?php echo ___('Enable or not?');?></label></th>
						<td><input type="checkbox" name="<?php echo self::$iden;?>[on]" id="<?php echo self::$iden;?>_on" value="1" <?php echo $is_checked;?> /><label for="<?php echo self::$iden;?>_on"><?php echo ___('Enable');?></label></td>
					</tr>
					<tr>
						<th scope="row"><?php echo ___('HTML codes');?></th>
						<td><textarea id="<?php echo self::$iden;?>_code" name="<?php echo self::$iden;?>[code]" class="widefat" cols="30" rows="10"><?php echo stripslashes($opt['code']);?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html(___('Restore'));?></th>
						<td>
							<label for="<?php echo self::$iden;?>_restore">
								<input type="checkbox" id="<?php echo self::$iden;?>_restore" name="<?php echo self::$iden;?>[restore]" value="1"/>
								<?php echo ___('Restore the post share settings');?>
							</label>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	
	}
	
	public static function options_default($opts){
		ob_start();
		?>
<div class="bdshare_t bdsharebuttonbox" data-tag="bd_share" data-bdshare="{
	'bdText':'%post_title_text% by %author% <?php echo ___('-- from %blog_name%');?>',
	'bdUrl':'%post_url%',
	'bdPic':'%img_url%'
}">
	<span class="description"><?php echo ___('Share to: ');?></span>
	<a class="bds_tsina" data-cmd="tsina" title="<?php echo sprintf(___('Share to %s'),___('Sina Weibo'));?>" href="javascript:;"></a>
	<a class="bds_qzone" data-cmd="qzone" href="javascript:;" title="<?php echo sprintf(___('Share to %s'),___('QQ zone'));?>"></a>
	<a class="bds_tieba" data-cmd="tieba" title="<?php echo sprintf(___('Share to %s'),___('Tieba'));?>" href="javascript:;"></a>
	<a class="bds_weixin" data-cmd="weixin" title="<?php echo sprintf(___('Share to %s'),___('Wechat'));?>" href="javascript:;"></a>
	<a class="bds_more" data-cmd="more" href="javascript:;"></a>
</div>				
<?php
		$content = ob_get_contents();
		ob_end_clean();

		$opts[self::$iden] = array(
			'on' => 1,
			'code' => $content,
		);

		return $opts;
	}
	public static function is_enabled(){
		$opt = self::get_options();
		return isset($opt['on']) && $opt['on'] == 1;
	}
	public static function options_save($options){
		if(isset($_POST[self::$iden]) && !isset($_POST[self::$iden]['restore'])){
			$opt = $_POST[self::$iden];
		}
		return $options;
	}
	public static function frontend_css(){
		$opt = self::get_options();
		if(!self::is_enabled())
			return false;
			
		wp_enqueue_style(
			self::$iden,
			theme_features::get_theme_includes_css(__DIR__),
			'frontend',
			theme_file_timestamp::get_timestamp()
		);
	}
	public static function frontend_seajs_alias($alias){
		$opt = self::get_options();
		if(!self::is_enabled())
			return $alias;
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		return $alias;
	} 
	public static function frontend_seajs_use(){
		$opt = self::get_options();
		if(!self::is_enabled())
			return false;
		?>
		seajs.use('<?php echo self::$iden;?>',function(m){
			m.init();
		});
		<?php
	}
}
?>