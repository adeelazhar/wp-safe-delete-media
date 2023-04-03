<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://#
 * @since      1.0.0
 *
 * @package    Safe_Media_Delete
 * @subpackage Safe_Media_Delete/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Safe_Media_Delete
 * @subpackage Safe_Media_Delete/public
 * @author     adeel <adeel@adeel.com>
 */
class Safe_Media_Delete_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Safe_Media_Delete_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Safe_Media_Delete_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/safe-media-delete-public.css', [], $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Safe_Media_Delete_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Safe_Media_Delete_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/safe-media-delete-public.js', [ 'jquery' ], $this->version, false );

	}

	/**
	 * Register custom endpoints
	 */
	function register_assignment_endpoints() {
		// Register the endpoint to get image details

		//@TODO ask where the endpoint will be used
		register_rest_route( 'assignment/v1', '/(?P<id>\d+)', [
			'methods'  => 'GET',
			'callback' => [ $this, 'get_image_details' ],
			'args'     => [
				'id' => [
					'validate_callback' => function ( $param ) {
						return is_numeric( $param );
					},
				],
			],
		] );

		// Register the endpoint to delete an image
		register_rest_route( 'assignment/v1', '/(?P<id>\d+)', [
			'methods'  => 'DELETE',
			'callback' => [ $this, 'delete_image' ],
			'args'     => [
				'id' => [
					'validate_callback' => function ( $param ) {
						return is_numeric( $param );
					},
				],
			],
		] );
	}


	/**
	 * Retrieves the details of an image attachment.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_Error|WP_REST_Response The response object on success, or an error object on failure.
	 */
	function get_image_details( WP_REST_Request $request ) {
		$image_id   = absint( $request->get_param( 'id' ) );
		$attachment = get_post( $image_id );

		if ( ! $attachment || 'attachment' != $attachment->post_type ) {
			return new WP_Error( 'not_found', 'Media not found', [ 'status' => 404 ] );
		}

		// Get the attached objects
		$usage_types      = [ 'featured-image', 'content', 'term' ];
		$image_usage_list = get_image_usage_list( $image_id, $usage_types );

		// Build the response
		$response_safe = [
			'ID'               => absint( $image_id ),
			'date'             => esc_html( $attachment->post_date ),
			'slug'             => esc_html( $attachment->post_name ),
			'type'             => esc_html( get_post_mime_type( $image_id ) ),
			'link'             => esc_url( wp_get_attachment_url( $image_id ) ),
			'alt_text'         => esc_html( get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ),
			'attached_objects' => $image_usage_list,
		];

		return rest_ensure_response( $response_safe );
	}

	/**
	 * Deletes an image attachment if it's not attached to any post or term.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_Error|WP_REST_Response The response object on success, or an error object on failure.
	 */
	function delete_image( WP_REST_Request $request ) {
		$image_id = absint( $request->get_param( 'id' ) );

		// Check if the image is attached to any post or term
		$usage_types      = [ 'featured-image', 'content', 'term' ];
		$image_usage_list = get_image_usage_list( $image_id, $usage_types );

		if ( ! empty( $image_usage_list ) ) {
			return new WP_Error( 'deletion_failed', 'Image cannot be deleted as it is attached to posts or terms', [ 'status' => 403, 'usage' => $image_usage_list ] );
		}

		// Delete the image
		$deleted = wp_delete_attachment( $image_id, true );

		if ( ! $deleted ) {
			return new WP_Error( 'deletion_failed', 'Failed to delete the image', [ 'status' => 500 ] );
		}

		return rest_ensure_response( [ 'success' => true, 'message' => 'Image deleted successfully' ] );
	}

}
