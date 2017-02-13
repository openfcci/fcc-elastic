<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;
/*--------------------------------------------------------------
# Debug
--------------------------------------------------------------*/

if ( class_exists( 'PC' ) ) {
	// output FCC custom filtered the search request arguments
	PC::Debug( json_encode( $request_args ),'fcc_filter_ep_search_request_args' );
}

function fcc_ep_formatted_args_callback( $ep_formatted_args, $args = '' ) {
	$existing_query = $ep_formatted_args['query'];
	if ( class_exists( 'PC' ) ) {
		PC::debug( $ep_formatted_args, 'ep_formatted_args' );
	}
	return $ep_formatted_args;
}
add_filter( 'ep_formatted_args', 'fcc_ep_formatted_args_callback', 300, 2 );

function fcc_ep_pre_get_posts( $query ) {
	if ( class_exists( 'PC' ) ) {
		PC::debug( $query, 'action_pre_get_posts' );
	}
	return $query;
}
add_action( 'pre_get_posts', 'fcc_ep_pre_get_posts', 5 );

function fcc_filter_posts_request( $request, $query ) {
	if ( class_exists( 'PC' ) ) {
		PC::debug( $request, '$request' );
		PC::debug( $query, '$query' );
	}
	return $request;
}
add_filter( 'posts_request', 'fcc_filter_posts_request', 10, 2 );
