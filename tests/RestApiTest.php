<?php
declare( strict_types=1 );

use WP_Mock\Tools\TestCase;

class RestApiTest extends TestCase {

	public function setUp(): void {
		WP_Mock::setUp();
		parent::setUp();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
		parent::tearDown();
	}

	public function test_init_registers_rest_api_init_hook(): void {
		WP_Mock::expectActionAdded( 'rest_api_init', [ \Mockery::any(), 'register_routes' ] );
		( new UniRate_REST_API() )->init();
		$this->addToAssertionCount( 1 );
	}

	public function test_register_routes_registers_three_endpoints(): void {
		WP_Mock::userFunction( 'register_rest_route' )->times( 3 )->andReturn( true );
		( new UniRate_REST_API() )->register_routes();
		$this->addToAssertionCount( 1 );
	}

	public function test_handle_rate_returns_503_when_no_api_key(): void {
		WP_Mock::userFunction( 'get_option' )->andReturn( '' );

		$request  = new WP_REST_Request( [ 'from' => 'USD', 'to' => 'EUR' ] );
		$response = ( new UniRate_REST_API() )->handle_rate( $request );

		$this->assertSame( 503, $response->get_status() );
	}

	public function test_handle_convert_returns_503_when_no_api_key(): void {
		WP_Mock::userFunction( 'get_option' )->andReturn( '' );

		$request  = new WP_REST_Request( [ 'from' => 'USD', 'to' => 'EUR', 'amount' => 100 ] );
		$response = ( new UniRate_REST_API() )->handle_convert( $request );

		$this->assertSame( 503, $response->get_status() );
	}

	public function test_handle_currencies_returns_503_when_no_api_key(): void {
		WP_Mock::userFunction( 'get_option' )->andReturn( '' );

		$request  = new WP_REST_Request();
		$response = ( new UniRate_REST_API() )->handle_currencies( $request );

		$this->assertSame( 503, $response->get_status() );
	}

	public function test_handle_rate_returns_200_with_rate(): void {
		WP_Mock::userFunction( 'get_option' )->andReturn( 'ur_live_test' );
		$this->mock_client_request( '{"rate":"0.92"}' );

		$request  = new WP_REST_Request( [ 'from' => 'USD', 'to' => 'EUR' ] );
		$response = ( new UniRate_REST_API() )->handle_rate( $request );

		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertArrayHasKey( 'rate', $data );
		$this->assertEqualsWithDelta( 0.92, $data['rate'], 0.001 );
	}

	public function test_handle_convert_returns_200_with_result(): void {
		WP_Mock::userFunction( 'get_option' )->andReturn( 'ur_live_test' );
		$this->mock_client_request( '{"result":"92.50"}' );

		$request  = new WP_REST_Request( [ 'from' => 'USD', 'to' => 'EUR', 'amount' => 100 ] );
		$response = ( new UniRate_REST_API() )->handle_convert( $request );

		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertArrayHasKey( 'result', $data );
	}

	public function test_handle_rate_returns_429_on_rate_limit(): void {
		WP_Mock::userFunction( 'get_option' )->andReturn( 'ur_live_test' );
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( [] );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 429 );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '' );

		$request  = new WP_REST_Request( [ 'from' => 'USD', 'to' => 'EUR' ] );
		$response = ( new UniRate_REST_API() )->handle_rate( $request );

		$this->assertSame( 429, $response->get_status() );
	}

	// ── helpers ──────────────────────────────────────────────────────────────

	private function mock_client_request( string $body ): void {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( [] );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 200 );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( $body );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );
	}
}
