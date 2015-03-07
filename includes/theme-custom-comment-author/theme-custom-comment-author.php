<?php
/**
 * @version 1.0.0
 */
/**
 * author url
 */
add_filter('get_comment_author_url',function($url, $comment_ID, $comment){
	if((int)$comment->user_id === 0)
		return $url;
	return get_author_posts_url($comment->user_id);
},10,3);

/**
 * author email
 */
add_filter('get_comment_author_email',function($comment_author_email, $comment_ID, $comment){
	if((int)$comment->user_id === 0)
		return $comment_author_email;
	return get_the_author_meta('user_email',$comment->user_id,true);
},10,3);

/**
 * author email link
 */
add_filter('comment_email',function($comment_author_email, $comment){
	if((int)$comment->user_id === 0)
		return $comment_author_email;
	return get_the_author_meta('user_email',$comment->user_id);
},10,2);