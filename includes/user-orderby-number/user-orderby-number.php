<?php
/**
 * @version 1.0.0
 */
add_action( 'pre_user_query', 'add_action_user_orderby_number');

function add_action_user_orderby_number( &$query ){
	if(isset($query->query_vars['orderby']) && $query->query_vars['orderby'] === 'meta_value_num'){
		$query->query_orderby = 'ORDER BY meta_value+0 ' . $query->query_vars['order'];
	}
}