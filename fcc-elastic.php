<?php
/*
Plugin Name: FCC Elastic
Plugin URI: https://github.com/openfcci/fcc-elastic
Description: Customization to extend ElasticPress and Elasticsearch functionality.
Author: Forum Communications Company
Version: 1.17.02.13
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
 * Filter the number of shards per index
 *
 * @param int $shards
 * @since 1.17.02.13
 */
function fcc_ep_default_index_number_of_shards( $shards ) {
	$shards = 3;
	return $shards;
}
add_filter( 'ep_default_index_number_of_shards', 'fcc_ep_default_index_number_of_shards' );

/**
 * Filter the number of shards per index
 *
 * @param int $replicas
 * @since 1.17.02.13
 */
function fcc_ep_default_index_number_of_replicas( $replicas ) {
	$replicas = 1;
	return $replicas;
}
add_filter( 'ep_default_index_number_of_replicas', 'fcc_ep_default_index_number_of_replicas' );

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

/**
 * Add local debug filters
 *
 * Don't include on production or FCC server environments.
 * @since 1.17.02.13
 */
if ( ! getenv( 'ENVIRONMENT' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . '/includes/debug.php' );
}
