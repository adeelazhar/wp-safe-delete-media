<?php
/**
 * Utility Functions for plugin
*/

defined( 'ABSPATH' ) || die;

if( !function_exists( 'get_image_usage_list' ) ) {
	/**
	 * Returns usage list of an attachment/media
	 *
	 * @param $post_id     int related to attachment/media ID(under the hood it's still a post)
	 * @param $usage_types array ['featured-image','content','term']
	 *
	 * @return string
	 */
	function get_image_usage_list( $post_id, $usage_types ) {
		$image_url        = wp_get_attachment_url( $post_id );
		$attached_objects = [];

		if ( in_array( 'featured-image', $usage_types, true ) ) {
			// Query for posts where the attachment is being used as a featured image
			$args = [
				'post_type'      => 'any',
				'post_status'    => 'any',
				'posts_per_page' => 5,
				'fields'         => 'ids',
				'meta_query'     => [
					[
						'key'     => '_thumbnail_id',
						'value'   => $post_id,
						'compare' => '=',
					],
				],
			];

			$posts_with_featured_image = get_posts( $args );

			$attached_objects = array_merge( $attached_objects, $posts_with_featured_image );
		}

		if ( in_array( 'content', $usage_types, true ) ) {
			// Query for posts where the attachment is being used in the content
			$content_search_args = [
				's'              => $image_url,
				'post_type'      => 'any',
				'post_status'    => 'any',
				'posts_per_page' => 5,
				'fields'         => 'ids',
			];

			$posts_with_image_in_content = get_posts( $content_search_args );

			$attached_objects = array_merge( $attached_objects, $posts_with_image_in_content );
		}

		if ( in_array( 'term', $usage_types, true ) ) {
			// Query for terms where the attachment is being used
			$terms_with_image = get_terms( [
				'taxonomies' => array_keys( get_taxonomies( [], 'names' ) ),
				'hide_empty' => false,
				'fields'     => 'ids',
				'meta_query' => [
					[
						'key'     => 'smd_image',
						'value'   => $image_url,
						'compare' => '=',
					],
				],
			] );
			
			$attached_objects = ['posts' => $attached_objects, 'terms' => $terms_with_image];
		}

		return $attached_objects;
	}
}
