<?php
/**
 * @version 1.0.0
 */
/**
 * author url
 */
add_filter('get_comment_author_url',function($url, $comment_ID, $comment){
	static $caches;
	$cache_id = md5(serialize(func_get_args()));
	if(isset($caches[$cache_id]))
		return $caches[$cache_id];

		
	if((int)$comment->user_id === 0){
		$caches[$cache_id] = $url;
		return $url;
	}
	
	$caches[$cache_id] = theme_cache::get_author_posts_url($comment->user_id);
	return $caches[$cache_id];
},10,3);

/**
 * author email
 */
add_filter('get_comment_author_email',function($comment_author_email, $comment_ID, $comment){
	
	static $caches;
	$cache_id = md5(serialize(func_get_args()));
	if(isset($caches[$cache_id]))
		return $caches[$cache_id];

		
	if((int)$comment->user_id === 0){
		$caches[$cache_id] = $comment_author_email;
		return $caches[$cache_id];
	}
	
	$caches[$cache_id] = theme_cache::get_the_author_meta('user_email',$comment->user_id,true);
	return $caches[$cache_id];
},10,3);

/**
 * author email link
 */
add_filter('comment_email',function($comment_author_email, $comment){
	static $caches;
	$cache_id = md5(serialize(func_get_args()));
	if(isset($caches[$cache_id]))
		return $caches[$cache_id];
		
	if((int)$comment->user_id === 0){
		$caches[$cache_id] = $comment_author_email;
		return $caches[$cache_id];
	}
	
	$caches[$cache_id] = theme_cache::get_the_author_meta('user_email',$comment->user_id);
	return $caches[$cache_id];
},10,2);