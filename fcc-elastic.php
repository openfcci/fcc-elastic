<?php
/*
Plugin Name: FCC Elastic
Plugin URI: https://github.com/openfcci/fcc-elastic
Description: Customization to extend ElasticPress and Elasticsearch functionality.
Author: Forum Communications Company
Version: 0.16.05.05
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
 * Triggers on save, edit or update of published podcasts
 * Works in "Quick Edit", but not bulk edit.
 * @since 0.16.05.05
 * @version 0.16.05.05
 */
function fcc_add_featured_image_post_meta( $post_id, $post, $update ) {
  if ( $post->post_type == 'post' && $post->post_status == 'publish' ) {

    /*if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( $id ) ) {
      if ( class_exists('Jetpack') && method_exists('Jetpack', 'get_active_modules') && in_array('photon', Jetpack::get_active_modules()) && function_exists('jetpack_photon_url') ) {
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
add_action('wp_insert_post', 'fcc_add_featured_image_post_meta', 10, 3 );
