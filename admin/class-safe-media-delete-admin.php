<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://#
 * @since      1.0.0
 *
 * @package    Safe_Media_Delete
 * @subpackage Safe_Media_Delete/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Safe_Media_Delete
 * @subpackage Safe_Media_Delete/admin
 * @author     adeel <adeel@adeel.com>
 */
class Safe_Media_Delete_Admin {

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
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/safe-media-delete-admin.css', [], $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/safe-media-delete-admin.js', [ 'jquery' ], $this->version, false );

	}

	/**
	 * Registers CMB2 Box
	 *
	 * @related CMB2
	 */
	function register_term_image_metabox() {
		$prefix = 'smd_';

		$cmb_term = new_cmb2_box( [
			'id'               => $prefix . 'edit',
			'title'            => __( 'Term Image', 'smd' ),
			'object_types'     => [ 'term' ],
			'taxonomies'       => array_keys( get_taxonomies( [], 'names' ) ),
			'new_term_section' => true,
		] );

		$cmb_term->add_field( [
			'name'         => __( 'Image', 'smd' ),
			'desc'         => __( 'Upload an image or select one from the media library. (JPEG, PNG)', 'smd' ),
			'id'           => $prefix . 'image',
			'type'         => 'file',
			'options'      => [
				'url' => false,
			],
			'query_args'   => [
				'type' => [
					'image/jpeg',
					'image/png',
				],
			],
			'preview_size' => 'thumbnail',
		] );
	}

	/**
	 * Prevents attachment deletion if its used
	 *
	 * error message is saved in an option top preserve WordPress execution flow
	 *
	 * @param $check null return anything except null prevent image delete
	 * @param $post
	 * @param $force_delete
	 *
	 * @return bool|mixed
	 */
	public function prevent_attachment_delete( $check, $post, $force_delete ) {
		$usage_list = get_image_usage_list( $post->ID, [ 'featured-image', 'content', 'term' ] );
		if ( empty( $usage_list ) ) {
			return $check;
		}

		update_option( 'smd_error_notice', $usage_list );

		return true; // don't return false it will break execution
	}

	/**
	 * Displays and admin notice when user tries to delete a used image
	 *
	 * @param $message
	 */
	public function smd_admin_notice( $message ) {
		$smd_error_notice = get_option( 'smd_error_notice' );
		// print_r($smd_error_notice);
		delete_option( 'smd_error_notice' );

		if ( ! $smd_error_notice ) {
			return;
		}


		$links = $this->get_edit_links( $smd_error_notice );
		$links = implode( ', ', $links );

		printf( '<div class="notice notice-error is-dismissible"><p>This media cannot be deleted because it is being used in the following: <br /> %s</p></div>', wp_kses_post( $links ) );
		delete_option( 'smd_error_notice' );
	}

	/**
	 * Get edit links for an array of post or term IDs
	 *
	 * @param array $ids Array of post or term IDs
	 *
	 * @return array Array of linked IDs
	 */
	function get_edit_links( $ids ) {
		$post_links = [];
		$term_links = [];

		if(!empty($ids['posts'])){
			foreach ( $ids['posts'] as $id ) {
				$post_links[] = sprintf( '<a href="%s">%s</a>', get_edit_post_link( $id ), $id );
				
			}
		}

		if(!empty($ids['terms'])){
		foreach ( $ids['terms'] as $id ) {
			$term     = get_term( $id );
			$taxonomy = $term->taxonomy;
			$term_links[]  = sprintf( '<a href="%s">%s</a>', get_edit_term_link( $id, $taxonomy ), $id );
		}
		}

		if ($post_links) {
			array_unshift($post_links, '<strong>'. __('Articles', 'smd') .'</strong>');
		}

		if ($term_links) {
			array_unshift($term_links, '<br /><strong>'. __('Terms', 'smd') .'</strong>');
		}

		return array_merge($post_links, $term_links);
	}

	/**
	 * Adds a new column under media > library > column view
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function add_attached_objects_column( $columns ) {
		$columns['attached_objects'] = __( 'Attached Objects', 'smd' );

		return $columns;
	}

	/**
	 * Displays usage list under 'Attached Object' column
	 *
	 * @param $column_name
	 * @param $post_id
	 */
	function fill_attached_objects_column( $column_name, $post_id ) {
		if ( $column_name != 'attached_objects' ) {
			return;
		}

		$usage_list = get_image_usage_list( $post_id, [ 'featured-image', 'content', 'term' ] );
		if ( empty( $usage_list['posts'] ) && empty( $usage_list['terms']) ) {
			echo '-';

			return;
		}

		$links = $this->get_edit_links( $usage_list );
		echo implode( ', ', $links );
	}


	/**
	 * Adds the image usage list field to the attachment edit form.
	 *
	 * @param array    $form_fields The current form fields.
	 * @param WP_Post  $post        The attachment post object.
	 *
	 * @return array The updated form fields.
	 */
	function add_image_usage_list_field( $form_fields, $post ) {
		// Check if the post is an attachment
		if ( 'attachment' !== $post->post_type ) {
			return $form_fields;
		}

		// Get the attached objects
		$usage_types      = [ 'featured-image', 'content', 'term' ];
		$image_usage_list = get_image_usage_list( $post->ID, $usage_types );
	

		$links = $this->get_edit_links( $image_usage_list );
		$links = implode( ', ', $links );

		// Add the usage list field to the form
		$form_fields['image_usage_list'] = [
			'label' => __( 'Usage List:', 'smd' ),
			'input' => 'html',
			'html'  => '<span style="vertical-align: -webkit-baseline-middle;">' . wp_kses_post( $links ) . '</span>',
		];

		return $form_fields;
	}
}

