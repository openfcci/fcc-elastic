<?php
/*
Plugin Name: FCC Elastic
Plugin URI: https://github.com/openfcci/fcc-elastic
Description: Customization to extend ElasticPress and Elasticsearch functionality.
Author: Forum Communications Company
Version: 1.16.08.23
Author URI: http://forumcomm.com/
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/*--------------------------------------------------------------
# Plugin Functions
--------------------------------------------------------------*/

/**
 * Add Featured Image URL to Post Meta
 *
 * Triggers on save, edit or update of published posts
 * Works in "Quick Edit", but not bulk edit.
 * @since 1.16.05.05
 * @version 1.16.05.05
 */
function fcc_add_featured_image_post_meta( $post_id, $post, $update ) {
	if ( 'post' == $post->post_type && 'publish' == $post->post_status ) {

		/*if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( $id ) ) {
			if ( class_exists( 'Jetpack' ) && method_exists( 'Jetpack', 'get_active_modules' ) && in_array( 'photon', Jetpack::get_active_modules() ) && function_exists( 'jetpack_photon_url' ) ) {
				// Photon Image
				$featured_image_full = jetpack_photon_url( wp_get_attachment_url( get_post_thumbnail_id( $post_id ) ) );
			} else {
				// WordPress Core
				$featured_image_full = wp_get_attachment_url( get_post_thumbnail_id( $post_id ) );
			}
		}*/

		$featured_image_full = wp_get_attachment_url( get_post_thumbnail_id( $post_id ) );
		$featured_image_full_photon = jetpack_photon_url( wp_get_attachment_url( get_post_thumbnail_id( $post_id ) ) );
		$featured_image_medium = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'medium' )[0];
		$featured_image_thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'thumbnail' )[0];
		$featured_image_small_thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'featured-image' )[0];

		update_post_meta( $post_id, 'featured_image_full', $featured_image_full );
		update_post_meta( $post_id, 'featured_image_full_photon', $featured_image_full_photon );
		update_post_meta( $post_id, 'featured_image_medium', $featured_image_medium );
		update_post_meta( $post_id, 'featured_image_thumbnail', $featured_image_thumbnail );
		update_post_meta( $post_id, 'featured_image_small_thumb', $featured_image_small_thumb );

	}
}
add_action( 'wp_insert_post', 'fcc_add_featured_image_post_meta', 10, 3 );


/**
 * Filter search scope
 *
 * Allows for easier filtering of search scope to meet the requirements of more complex sites, etc.
 * @link https://github.com/10up/ElasticPress/pull/364
 *
 * @since 2.1
 *
 * @param string $scope The search scope.
 */
function fcc_filter_ep_search_scope( $scope ) {
	$scope = 'all';
	return $scope;
}
add_filter( 'ep_search_scope', 'fcc_filter_ep_search_scope' );


/**
 * Filter the search request arguments
 *
 * @param array $request_args
 * @param array $args
 * @param array $scope
 * @since 1.16.08.23
 */
function fcc_filter_ep_search_request_args( $request_args, $args = '', $scope = '' ) {

	// TODO Add score boosting of post_title
	// TODO Add score boosting of posts for current site

	# Do not return results from areavoices.com main site
	$request_args['query']['bool']['must_not'] = array(
			'term' => array(
				'_index' => 'areavoicescom-1',
			),
		);

	# Add Guassian Date Scoring
	$gauss = new \stdClass();
	$gauss->post_date = array( 'scale' => '7d', 'offset' => '7d', 'decay' => 0.5 );

	$function_score = array(
		'function_score' => array(
			'functions' => array(
				array(
					'gauss' => $gauss,
					'weight' => '1.5',
				),
			),
			'score_mode' => 'multiply',
			'query' => $request_args['query'],
		),
	);
	$request_args['query'] = $function_score;

	return $request_args;
}
add_filter( 'ep_formatted_args', 'fcc_filter_ep_search_request_args' );


/*--------------------------------------------------------------
# Debug
--------------------------------------------------------------*/
// PC::Debug( json_encode( $request_args ),'fcc_filter_ep_search_request_args' );

function fcc_ep_formatted_args_callback( $ep_formatted_args, $args = '' ) {
	//$existing_query = $ep_formatted_args['query'];
	if ( class_exists( 'PC' ) ) {
		PC::debug( $ep_formatted_args, 'ep_formatted_args' );
	}
	return $ep_formatted_args;
}
//add_filter( 'ep_formatted_args', 'fcc_ep_formatted_args_callback', 300, 2 );

function fcc_ep_pre_get_posts( $query ) {
	PC::debug( $query, 'action_pre_get_posts' );
	return $query;
}
//add_action( 'pre_get_posts', 'fcc_ep_pre_get_posts', 5 );

function fcc_filter_posts_request( $request, $query ) {
	PC::debug( $request, '$request' );
	PC::debug( $query, '$query' );
}
//add_filter( 'posts_request', 'fcc_filter_posts_request', 10, 2 );
