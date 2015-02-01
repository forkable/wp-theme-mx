<?php
/*
Feature Name:	theme-defender
Feature URI:	http://www.inn-studio.com
Version:		1.0.9
Description:	Improve the seo friendly
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_action('init','theme_defender::init',1);
class theme_defender{
	public static $iden = 'theme_defender';
	private static $expire = 60;
	private static $connection = 45;
	private static $block_time = 60;

	public static function init(){
		if(is_user_logged_in()) return false;
		$cache_id = md5(get_client_ip());
		$connection = (int)theme_cache::get($cache_id) + 1;
		if($connection > self::$connection){
			theme_cache::set($cache_id,$connection,null,self::$block_time);
			ob_start();
			?>
			<script>
			(function(){
				var $number = document.getElementsByTagName('strong')[0],
					seconds = parseInt($number.innerHTML),
					si = setInterval(function(){
						seconds--;
						$number.innerHTML = seconds;
						if(seconds === 0){
							clearInterval(si);
							location.reload();
						}
					},1050);
			})();
			</script>
			<?php
			$js = ob_get_contents();
			ob_end_clean();
			wp_die(
				sprintf(___('Your behavior looks like brute Force attack, page will be refreshed after %s seconds automatically.'),'<strong>' . self::$block_time . '</strong>')
				. $js,
				___('Defender'),
				array(
					'response' => 403,
				)
			);
		}else{
			theme_cache::set($cache_id,$connection,null,self::$expire);
		}
	}
}
?>