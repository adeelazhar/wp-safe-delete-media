<?php
/**
 * Test_Assignment_Endpoints class file.
 */

// Ensure your plugin is loaded before the tests run.
require_once dirname( dirname( __FILE__ ) ) . '/safe-media-delete.php';

class Test_Assignment_Endpoints extends WP_Test_REST_TestCase {
	protected $server;

	public function setUp() {
		parent::setUp();
		$this->server = rest_get_server();
	}

	// ... Other test cases ...
	public function test_get_image_details_error() {
		// Send a request to your endpoint with an invalid ID
		$request  = new WP_REST_Request( 'GET', '/assignment/v1/image/99999999' );
		$response = $this->server->dispatch( $request );

		// Assert the response status and error code
		$this->assertEquals( 404, $response->get_status() );
		$this->assertEquals( 'not_found', $response->get_error_code() );
	}

	public function test_get_image_details_valid() {
		// Set up your test data here. For example, create an image attachment.
		 $attachment_id = 26;

		// Send a request to your endpoint
		$request  = new WP_REST_Request( 'GET', '/assignment/v1/image/' . $attachment_id );
		$response = $this->server->dispatch( $request );

		// Assert the response status and data
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertArrayHasKey( 'ID', $data );
		$this->assertEquals( $attachment_id, $data['ID'] );

	}
}
