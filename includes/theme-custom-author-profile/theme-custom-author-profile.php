<?php
/**
 * @version 1.0.0
 * @author KM <kmvan.com@gmail.com>
 */
theme_custom_author_profile::init();
class theme_custom_author_profile{
	public static $iden = 'theme_custom_author_profile';
	public static $cache_expire = 2505600; /** 29 days */

	public static $user_meta_key = array(
		'followers_count' 	=> 'followers_count',
		'following_count' 	=> 'following_count',
		'follower' 			=> 'follower',
		'following' 		=> 'following',
	);
	public static function init(){
		add_filter('query_vars',			get_class() . '::filter_query_vars');
		
		add_filter('wp_title',				get_class() . '::wp_title',10,2);
		
	}
	public static function wp_title($title, $sep){
		if(!is_author()) return $title;
		global $author;
		$tab_active = get_query_var('tab');
		$author_display_name = get_the_author_meta('display_name',$author);
		$tabs = self::get_tabs();
		if(!empty($tab_active) && isset($tabs[$tab_active])){
			$title = $tabs[$tab_active]['text'];
		}else{
			$title = $tabs['profile']['text'];
		}
		$title = $author_display_name . $sep . $title;
		return $title . $sep . get_bloginfo('name');
	}
	public static function filter_query_vars($vars){
		if(!in_array('tab',$vars)) $vars[] = 'tab';
		if(!in_array('page',$vars)) $vars[] = 'page';
		return $vars;
	}
	public static function get_count($key,$user_id = null){
		global $author;
		$user_id = $user_id ? $user_id : $author;
		if(empty($user_id)){
			global $post;
			$user_id = $post->post_author;
		}
		$cache_id = 'user-count-' . $user_id;
		$caches = (array)wp_cache_get($cache_id);
		switch($key){
			case 'works':
				if(!isset($caches['works'])){
					$caches['works'] = (int)count_user_posts($user_id);
					wp_cache_set($cache_id,$caches,null,self::$cache_expire);
				}
				return $caches['works'];
			case 'comments':
				if(!isset($caches['comments'])){
					$caches['comments'] = (int)theme_features::get_user_comments_count($user_id);
					wp_cache_set($cache_id,$caches,null,self::$cache_expire);
				}
				return $caches['comments'];
			case 'followers_count':
				return (int)get_user_meta($user_id,self::$user_meta_key['followers_count'],true);
			case 'following_count':
				return (int)get_user_meta($user_id,self::$user_meta_key['following_count'],true);
		}
	}
	public static function get_tabs($key = null,$author_id = null){
		global $author;
		$author_id = $author_id ? $author_id : $author;
		if(empty($author_id)){
			global $post;
			$author_id = $post->post_author;
		}
		$baseurl = get_author_posts_url($author_id);
		$tabs = array(
			'profile' => array(
				'text' => ___('Profile'),
				'icon' => 'newspaper-o',
				'url' => $baseurl
			),
			'works' => array(
				'text' => ___('Works'),
				'icon' => 'file-text',
				'url' => add_query_arg('tab','works',$baseurl),
				'count' => self::get_count('works',$author_id),
			),
			'comments' => array(
				'text' => ___('Comments'),
				'icon' => 'comments',
				'url' => add_query_arg('tab','comments',$baseurl),
				'count' => self::get_count('comments',$author_id),
			),
			'followers' => array(
				'text' => ___('Followers'),
				'icon' => 'venus-double',
				'url' => add_query_arg('tab','followers',$baseurl),
				'count' => self::get_count('followers_count',$author_id),
			),
			'following' => array(
				'text' => ___('Following'),
				'icon' => 'venus-mars',
				'url' => add_query_arg('tab','following',$baseurl),
				'count' => self::get_count('following_count',$author_id),
			)
		);
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
}
?>