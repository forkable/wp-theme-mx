<?php
/**
 * @version 1.0.0
 * @author KM <kmvan.com@gmail.com>
 */
class theme_custom_author_profile{
	public static $iden = 'theme_custom_author_profile';
	
	public static function init(){
		
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
				'url' => add_query_arg('tab','works',$baseurl)
			),
			'comments' => array(
				'text' => ___('Comments'),
				'icon' => 'comments',
				'url' => add_query_arg('tab','comments',$baseurl)
			),
			'followers' => array(
				'text' => ___('Comments'),
				'icon' => 'venus-double',
				'url' => add_query_arg('tab','followers',$baseurl)
			),
			'following' => array(
				'text' => ___('Following'),
				'icon' => 'venus-mars',
				'url' => add_query_arg('tab','following',$baseurl)
			)
		);
		if($key){
			return isset($tabs[$key]) ? $tabs[$key] : false;
		}
		return $tabs;
	}
}
?>