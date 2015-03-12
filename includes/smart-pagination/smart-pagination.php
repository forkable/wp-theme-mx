<?php

/**
 * theme_smart_pagination
 *
 * @version 1.0.0
 * @author KM@INN STUDIO
 */
class theme_smart_pagination{
	
	public static function get_post_pagination($args = null){
		global $post,$page,$numpages;
		$output = null;
	
		$defaults = array(
			'add_fragment' => 'post-' . $post->ID
		);
		$r = wp_parse_args($args,$defaults);
		extract($r);
		$output['numpages'] = $numpages;
		$output['page'] = $page;
		/** 
		 * prev post
		 */
		$prev_post = get_previous_post(true);
		$prev_post = empty($prev_post) ? get_previous_post() : $prev_post;
		if(!empty($prev_post)){
			$output['prev_post'] = $prev_post;
		}
		/** 
		 * next post
		 */
		$next_post = get_next_post(true);
		$next_post = empty($next_post) ? get_next_post() : $next_post;
		// var_dump($next_post);
		if(!empty($next_post)){
			$output['next_post'] = $next_post;
		}		
		/** 
		 * exists multiple page
		 */
		if($numpages != 1){
			/** 
			 * if has prev page
			 */
			if($page > 1){
				$prev_page_number = $page - 1;
				$output['prev_page']['url'] = theme_features::get_link_page_url($prev_page_number,$add_fragment);
				$output['prev_page']['number'] = $prev_page_number;
			}
			/** 
			 * if has next page
			 */
			if($page < $numpages){
				$next_page_number = $page + 1;
				$output['next_page']['url'] = theme_features::get_link_page_url($next_page_number,$add_fragment);
				$output['next_page']['number'] = $next_page_number;
			}
		}
		// var_dump(array_filter($output));
		return array_filter($output);
	}
}