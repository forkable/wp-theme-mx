<?php
/*
Feature Name:	Post Share
Feature URI:	http://www.inn-studio.com
Version:		2.0.2
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

		
		if(!self::is_enabled()) 
			return false;

		add_filter('frontend_seajs_alias',	__CLASS__ . '::frontend_seajs_alias');
				
		add_action('frontend_seajs_use',__CLASS__ . '::frontend_seajs_use');

		add_action('wp_enqueue_scripts', 	__CLASS__ . '::frontend_css');
	}
	public static function get_options($key = null){
		static $caches = [];
		if(!$caches)
			$caches = (array)theme_options::get_options(self::$iden);
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
			'post_title_text' => theme_cache::get_the_title($post->ID),
			'post_url' => theme_cache::get_permalink($post->ID),
			'blog_name' => theme_cache::get_bloginfo('name'),
			'blog_url' => theme_cache::home_url(),
			'img_url' => esc_url($img_url),
			'post_excerpt' => esc_attr(mb_substr(html_minify(strip_tags(get_the_excerpt())),0,120)),
			'post_content' => esc_attr(mb_substr(html_minify(strip_tags(get_the_content())),0,120)),
			'author' => theme_cache::get_the_author_meta('display_name',$post->post_author),
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
			<legend><?= ___('Posts share settings');?></legend>
			<p class="description">
				<?= ___('Share your post to everywhere. Here are some keywords that can be used:');?>
			</p>
			<p class="description">
				<input type="text" class="small-text text-select" value="%post_title_text%" title="<?= ___('Post Title text');?>" readonly />
				<input type="text" class="small-text text-select" value="%post_url%" title="<?= ___('Post URL');?>" readonly />
				<input type="text" class="small-text text-select" value="%blog_name%" title="<?= ___('Blog name');?>" readonly />
				<input type="text" class="small-text text-select" value="%blog_url%" title="<?= ___('Blog URL');?>" readonly />
				<input type="text" class="small-text text-select" value="%img_url%" title="<?= ___('The first picture of the post.');?>" readonly />
				<input type="text" class="small-text text-select" value="%post_excerpt%" title="<?= ___('The excerpt of post.');?>" readonly />
				<input type="text" class="small-text text-select" value="%post_content%" title="<?= ___('The content of post.');?>" readonly />
			</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="<?= self::$iden;?>_on"><?= ___('Enable or not?');?></label></th>
						<td><input type="checkbox" name="<?= self::$iden;?>[on]" id="<?= self::$iden;?>_on" value="1" <?= $is_checked;?> /><label for="<?= self::$iden;?>_on"><?= ___('Enable');?></label></td>
					</tr>
					<tr>
						<th scope="row"><?= ___('HTML codes');?></th>
						<td><textarea id="<?= self::$iden;?>_code" name="<?= self::$iden;?>[code]" class="widefat" cols="30" rows="10"><?= stripslashes($opt['code']);?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><?= esc_html(___('Restore'));?></th>
						<td>
							<label for="<?= self::$iden;?>_restore">
								<input type="checkbox" id="<?= self::$iden;?>_restore" name="<?= self::$iden;?>[restore]" value="1"/>
								<?= ___('Restore the post share settings');?>
							</label>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	
	}
	
	public static function options_default(array $opts = []){
		ob_start();
		?>
<div class="bdshare_t bdsharebuttonbox" data-tag="bd_share" data-bdshare="{
	'bdText':'%post_title_text% by %author% <?= ___('-- from %blog_name%');?>',
	'bdUrl':'%post_url%',
	'bdPic':'%img_url%'
}">
	<span class="description"><?= ___('Share to: ');?></span>
	<a class="bds_tsina" data-cmd="tsina" title="<?= sprintf(___('Share to %s'),___('Sina Weibo'));?>" href="javascript:;"></a>
	<a class="bds_qzone" data-cmd="qzone" href="javascript:;" title="<?= sprintf(___('Share to %s'),___('QQ zone'));?>"></a>
	<a class="bds_tieba" data-cmd="tieba" title="<?= sprintf(___('Share to %s'),___('Tieba'));?>" href="javascript:;"></a>
	<a class="bds_weixin" data-cmd="weixin" title="<?= sprintf(___('Share to %s'),___('Wechat'));?>" href="javascript:;"></a>
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
		return self::get_options('on') == 1 ? true : false;
	}
	public static function options_save(array $opts = []){
		if(isset($_POST[self::$iden]) && !isset($_POST[self::$iden]['restore'])){
			$opts[self::$iden] = $_POST[self::$iden];
		}
		return $opts;
	}
	public static function frontend_css(){
		if(!theme_cache::is_singular())
			return false;
			
		wp_enqueue_style(
			self::$iden,
			theme_features::get_theme_includes_css(__DIR__),
			'frontend',
			theme_file_timestamp::get_timestamp()
		);
	}
	public static function frontend_seajs_alias(array $alias = []){
		if(!theme_cache::is_singular())
			return $alias;
			
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		return $alias;
	} 
	public static function frontend_seajs_use(){
		if(!theme_cache::is_singular())
			return false;
		?>
		seajs.use('<?= self::$iden;?>',function(m){
			m.init();
		});
		<?php
	}
}
?>