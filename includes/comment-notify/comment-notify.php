<?php
/**
 * @version 1.0.2
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_comment_notify::init';
	return $fns;
});
class theme_comment_notify {
	public static $iden = 'theme-comment-notify';
	public static $opt;

	public static function init(){
		
		add_action('page_settings',__CLASS__ . '::display_backend');
		add_filter('theme_options_default',__CLASS__ . '::options_default');
		add_filter('theme_options_save',__CLASS__ . '::options_save');

		self::$opt = theme_options::get_options(self::$iden);
		
		if(!self::is_enabled()) return;
		add_action('comment_post',__CLASS__ . '::reply_notify');
		add_action('comment_unapproved_to_approved', __CLASS__ . '::approved_notify');
	}
	public static function is_enabled(){
		return isset(self::$opt['on']) && self::$opt['on'] == 1 ? true : false;
	}
	public static function display_backend(){
		$is_checked = self::is_enabled() ? ' checked ' : null;
		?>
		<fieldset>
			<legend><?= ___('Comment reply notifier');?></legend>
			<p class="description">
				<?= ___('It will send a mail to notify the being reply comment author when comment has been reply, if your server supports.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="<?= self::$iden;?>-on"><?= ___('Enable or not?');?></label></th>
						<td><label for="<?= self::$iden;?>-on"><input type="checkbox" name="<?= self::$iden;?>[on]" id="<?= self::$iden;?>-on" value="1" <?= $is_checked;?> /><?= ___('Enable');?></label></td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function options_default($opts){
		$opts[self::$iden]['on'] = 1;
		return $opts;
	}
	public static function options_save($opts){
		if(isset($_POST[self::$iden])){
			$opts[self::$iden] = $_POST[self::$iden];
		}else{
			$opts[self::$iden]['on'] = -1;
		}
		return $opts;
	}
	public static function approved_notify($comment){
		$GLOBALS['comment'] = $comment;

		if(!is_email($comment->comment_author_email)) return false;
		
		$to = $comment->comment_author_email;
		
		$post_title = get_the_title($comment->comment_post_ID);
		$post_url = get_permalink($comment->comment_post_ID);
		
		$mail_title = sprintf(___('[%s] Your comment has been approved in "%s".'),get_bloginfo('name'),$post_title);
		ob_start();
		?>
		<p><?= sprintf(___('Your comment has been approved in "%s".'),'<a href="' . $post_url . '" target="_blank"><strong>' . $post_title . '</strong></a>');?></p>
		<p><?= esc_html(sprintf(___('Comment content: %s')),get_comment_text($comment->comment_ID));?></p>
		<p><?= sprintf(___('Article URL: %s'),'<a href="' . esc_url($post_url) . '">' . esc_url($post_url) . '</a>');?></p>
		<p><?= sprintf(___('Comment URL: %s'),'<a href="' . esc_url($post_url) . '#comment-' . $comment->comment_ID . '" target="_blank">' . esc_url($post_url) . '#comment-' . $comment->comment_ID . '</a>');?></p>
		
		<?php
		$mail_content = ob_get_contents();
		ob_get_clean();
		
		add_filter('wp_mail_content_type',__CLASS__ . '::set_html_content_type');
		
		wp_mail($to,$mail_title,$mail_content);
		
		remove_filter('wp_mail_content_type',__CLASS__ . '::set_html_content_type');

	}
	public static function reply_notify($comment_id){
		$current_comment = get_comment($comment_id);
		/** 
		 * if current comment has not parent or current comment is unapproved, return false
		 */
		if($current_comment->comment_parent == 0 || $current_comment->comment_approved != 1) return false;
			
		$parent_comment = get_comment($current_comment->comment_parent);

		/** 
		 * send start
		 */
		self::send_email($parent_comment,$current_comment);
		
	}
	private static function send_email($parent_comment,$child_comment){
		if(!is_email($parent_comment->comment_author_email)) return false;
		
		/** if parent email equal child email, do nothing */
		if($parent_comment->comment_author_email == $child_comment->comment_author_email) return false;

		$post_id = $parent_comment->comment_post_ID;
		$post_title = get_the_title($post_id);
		
		$mail_title = sprintf(___('[%s] Your comment has a reply in "%s".'),esc_html(get_bloginfo('name')),esc_html($post_title));
		ob_start();
		?>
		<p><?= sprintf(___('Your comment: %s'),get_comment_text($parent_comment->comment_ID));?></p>
		<p><?= sprintf(___('%s\'s reply: %s'),get_comment_author($child_comment->comment_ID),get_comment_text($child_comment->comment_ID));?></p>
		<p><?= sprintf(___('Views the comment: %s'),'<a href="' . esc_url(get_permalink($post_id)) . '#comment-' . $parent_comment->comment_post_ID . '" target="_blank">' . esc_html(get_permalink($post_id)) . '#comment-' . $parent_comment->comment_post_ID . '</a>');?></p>
		<?php
		$mail_content = ob_get_contents();
		ob_end_clean();
		
		add_filter('wp_mail_content_type',__CLASS__ . '::set_html_content_type');
		
		wp_mail($parent_comment->comment_author_email,$mail_title,$mail_content);
		
		remove_filter('wp_mail_content_type',__CLASS__ . '::set_html_content_type');

	}
	public static function set_html_content_type(){
		return 'text/html';
	}
}

?>
