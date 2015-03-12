<?php
/*
Feature Name:	Post Thumb Up/Down
Feature URI:	http://www.inn-studio.com
Version:		1.1.4
Description:	Agree or not? Just use the Thumb Up or Down to do it.
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_post_thumb::init';
	return $fns;
});
class theme_post_thumb{
	private static $iden = 'theme_post_thumb';
	public static function init(){
		add_filter('theme_options_save',get_class() . '::save');
		add_filter('theme_options_default',get_class() . '::options_default');	
		add_action('page_settings',get_class() . '::admin');
		if(!self::is_enabled()) return false;
		add_action('frontend_seajs_use',get_class() . '::js');
		add_action('wp_ajax_' . self::$iden,get_class() . '::process');
		add_action('wp_ajax_nopriv_' . self::$iden,get_class() . '::process');
	}

	/**
	 * is_enabled
	 *
	 * @return bool
	 * @version 1.0.1
	 * @author KM@INN STUDIO
	 */
	public static function is_enabled(){
		$opt = theme_options::get_options(self::$iden);
		return isset($opt['on']) ? true : false;
	}
	public static function admin(){
		
		$options = theme_options::get_options();
		$post_thumb = array(
			'text_up' => isset($options[self::$iden]['text_up']) ? $options[self::$iden]['text_up'] : null,
			'text_down' => isset($options[self::$iden]['text_down']) ? $options[self::$iden]['text_down'] : null,
		);
		$is_checked = self::is_enabled() ? ' checked ' : null;
		?>
		<fieldset>
			<legend><?php echo ___('Post Thumb Up or Down Settings');?></legend>
			<p class="description"><?php echo ___('Agree or not? Just thumb up or down! You can set some sentences to show randomly when votes success. Multiple sentences that please use New Line to split them.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="<?php echo self::$iden;?>_enable"><?php echo ___('Enable or not?');?></label></th>
						<td><input type="checkbox" name="<?php echo self::$iden;?>[on]" id="<?php echo self::$iden;?>_enable" value="1"/ <?php echo $is_checked;?> ><label for="<?php echo self::$iden;?>_enable"><?php echo ___('Enable');?></label></td>
					</tr>
					<tr>
						<th scope="row"><label for="post_thumb_up"><?php echo ___('Thumb up');?></label></th>
						<td>
							<textarea id="post_thumb_up" name="<?php echo self::$iden;?>[text_up]" class="widefat code" title="<?php echo ___('Thumb up');?>" cols="30" rows="5"><?php echo $post_thumb['text_up'];?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="post_thumb_down"><?php echo ___('Thumb down');?></label></th>
						<td>
							<textarea id="post_thumb_down" name="<?php echo self::$iden;?>[text_down]" class="widefat code" title="<?php echo ___('Thumb down');?>" cols="30" rows="5"><?php echo $post_thumb['text_down'];?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	
	public static function options_default($options){
		$options[self::$iden]['on'] = 1;
		$options[self::$iden]['text_up'] = sprintf(___("You thumbed up, I am agree it, too!%sYou are right, I think so."),PHP_EOL);
		$options[self::$iden]['text_down'] = sprintf(___("You thumbed down, you are a honest guy!%sYou are right, I think so."),PHP_EOL);
		return $options;
	}
	public static function save($options){
		$options[self::$iden] = isset($_POST[self::$iden]) ? $_POST[self::$iden] : null;
		return $options;
	}
	/* 处理进程 */
	public static function process(){
		if(!self::is_enabled()) return false;
		
		$output = null;
		$options = theme_options::get_options();
		$action = isset($_GET['down']) ? 'down' : 'up';
		$post_id = isset($_GET['up']) ? (int)$_GET['up'] : (int)$_GET['down'];
		$text_up = isset($options[self::$iden]['text_up']) ? $options[self::$iden]['text_up'] : sprintf(___("You thumbed up, I am agree it, too!%sYou are right, I think so."));
		$text_down = isset($options[self::$iden]['text_up']) ? $options[self::$iden]['text_up'] : sprintf(___("You thumbed down, you are a honest guy!%sYou are right, I think so."));
		if($post_id){
			/* 判断是否已经投过票，未曾投过票 */
			if(!self::is_voted($post_id)){
				/**
				 * UP
				 */
				if($action === 'up'){
					$thumb_obj = explode(PHP_EOL,$text_up);
					$thumb_len = count($thumb_obj);/* 统计长度 */
					/* 随机组合同意或反对数组 */
					$thumb_rand = rand(0,((int)$thumb_len-1));/* 产生随机数 */
					$thumb_content = $thumb_obj[$thumb_rand];/* 输出内容 */
					self::update_thumb('up',$post_id);
					$output['status'] = 'success';
					$output['msg'] = $thumb_content;
				/**
				 * DOWN
				 */
				}else{
					$thumb_obj = explode(PHP_EOL,$text_down);
					$thumb_len = count($thumb_obj);/* 统计长度 */
					$thumb_rand = rand(0,((int)$thumb_len-1));/* 产生随机数 */
					$thumb_content = $thumb_obj[$thumb_rand];/* 输出内容 */
					self::update_thumb('down',$post_id);
					$output['status'] = 'success';
					$output['msg'] = $thumb_content;
				}
				/* 写入曲奇 */
				$post_thumb = isset($_COOKIE[self::$iden]) ? (array)@json_decode(base64_decode($_COOKIE[self::$iden])) : array();
				$post_thumb['ids'][] = $post_id;
				setcookie(self::$iden,base64_encode(json_encode($post_thumb)),time()+60*60*24*30*12,'/');
			}else{
				$output['status'] = 'error';
				$output['msg'] = ___('You have already voted.');
			}
		}else{
			$output['status'] = 'error';
			$output['msg'] = ___('Not enough parameter.');
		}
		die(theme_features::json_format($output));
	}
	public static function get_thumb_up(){
		
		global $post;
		$args = array(
			'id' => get_the_ID(),
			'action' => 'up',
			'action_title' => ___('I like it'),
			'extra_tx' => $extra_tx,
		);
		$output = self::get_thumb_content($args);
		return $output;
	}
	
	public static function get_thumb_down($extra_tx = null){
		
		global $post;
		$args = array(
			'id' => get_the_ID(),
			'action' => 'down',
			'action_title' => ___('I dislike it'),
			'extra_tx' => $extra_tx,
		);
		$output = self::get_thumb_content($args);
		return $output;
	}
	/**
	 * post_thumb::get_thumb_content()
	 * 
	 * @param array() $args
	 * @param int $args['id'] the post id
	 * @param string $args['action'] the thumb action, up or down
	 * @param string $args['action_title'] the thumb action title tip
	 * @param string $args['extra_tx']
	 * @return string the html string of thumb content
	 * @version 1.0.0
	 * @author KM@INN STUDIO
	 * 
	 */
	public static function get_thumb_content($args = null){
		/** 
		 * $args = array('id','action','action_title','extra_tx');
		 */
		if(!$args) return;
		$defaults = array(
			'id' => null,
			'action' => null,
			'action_title' => null,
			'extra_tx' => null,
		);
		$r = wp_parse_args($args,$defaults);
		extract($r);
		
		$extra_tx = $extra_tx ? '<span class="post_thumb_extra_tx">' . $extra_tx . '</span>' : null;
		
		$output = '
			<a href="javascript:;" id="post_thumb_' . $id . '" class="post_thumb post_thumb_' . $action . ' ' . self::check_voted('post_thumb_' . $action . '_voted'). '" data-post_thumb="' . $id . ',' . $args['action'] . '" title="' . $action_title . '">
				<i class="icon"></i>
				<span class="post_thumb_count">' . self::get_thumb_count($action) . '</span>
				' . $extra_tx . '
			</a>
		';
		return $output;
	}
	private static function update_thumb($type = null,$post_id = null){
		if(!$type) return false;
		global $post;
		$post_id = $post_id ? $post_id : $post->ID;
		$new_count = (int)self::get_thumb_count($type,$post_id) + 1;
		update_post_meta($post_id,'post_thumb_count_'.$type,$new_count);
	}
	
	private static function get_thumb_count($type = null,$post_id = null){
		if(!$type) return;
		global $post;
		$post_id = $post_id ? $post_id : $post->ID;
		$post_thumb_count = get_post_meta($post_id,'post_thumb_count_'.$type);
		$post_thumb_count = isset($post_thumb_count[0]) ? (int)$post_thumb_count[0] : '0';
		return (int)$post_thumb_count;
	}
	public static function get_thumb_up_count($post_id = null){
		return self::get_thumb_count('up',$post_id);
	}
	public static function get_thumb_down_count($post_id = null){
		return self::get_thumb_count('down',$post_id);
	}
	private static function is_voted($post_id = null){
		global $post;
		$post_thumb = isset($_COOKIE[self::$iden]) ? (array)@json_decode(base64_decode($_COOKIE[self::$iden])) : array();
		if(!isset($post_thumb['ids'])) return false;
		$post_id = $post_id ? $post_id : $post->ID;
		/* 判断存在曲奇，已投票 */
		if(in_array($post_id,$post_thumb['ids'])){
			return true;
		}else{
			return false;
		}
	}
	public static function check_voted($post_id = null,$class = 'voted'){
		global $post;
		$post_id = $post_id ? $post_id : $post->ID;
		if(self::is_voted($post_id)){
			return $class;
		}else{
			return false;
		}
	}
	public static function js(){
		if(!self::is_enabled()) return false;
		?>
		seajs.use('<?php echo theme_features::get_theme_includes_js(__FILE__);?>',function(m){
			m.config.process_url = '<?php echo theme_features::get_process_url(array('action'=>self::$iden));?>';
			m.config.lang.M00001 = '<?php echo ___('Loading, please wait...');?>';
			m.config.lang.E00001 = '<?php echo ___('Server error or network is disconnected.');?>';
			m.init();
		});
	<?php
	}
}
?>