<?php
declare( strict_types=1 );

use WP_Mock\Tools\TestCase;

class ClientTest extends TestCase {

	public function setUp(): void {
		WP_Mock::setUp();
		parent::setUp();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
		parent::tearDown();
	}

	// ── get_rate ─────────────────────────────────────────────────────────────

	public function test_get_rate_with_to_returns_float(): void {
		$this->mock_successful_request( '{"rate":"0.9235"}' );

		$client = new UniRate_Client( 'test-key' );
		$rate   = $client->get_rate( 'USD', 'EUR' );

		$this->assertEqualsWithDelta( 0.9235, $rate, 0.0001 );
	}

	public function test_get_rate_without_to_returns_array(): void {
		$this->mock_successful_request( '{"rates":{"EUR":"0.92","GBP":"0.79"}}' );

		$client = new UniRate_Client( 'test-key' );
		$rates  = $client->get_rate( 'USD' );

		$this->assertIsArray( $rates );
		$this->assertArrayHasKey( 'EUR', $rates );
		$this->assertEqualsWithDelta( 0.92, $rates['EUR'], 0.001 );
	}

	public function test_get_rate_uppercases_currency_codes(): void {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 200 );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '{"rate":"0.92"}' );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );

		WP_Mock::userFunction( 'wp_remote_get' )
			->once()
			->with(
				\Mockery::on( fn( $url ) => str_contains( $url, 'from=USD' ) && str_contains( $url, 'to=EUR' ) ),
				\Mockery::any()
			)
			->andReturn( [ 'body' => '{"rate":"0.92"}' ] );

		$client = new UniRate_Client( 'test-key' );
		$client->get_rate( 'usd', 'eur' );
		$this->assertTrue( true ); // assertion is in the wp_remote_get mock constraint
	}

	public function test_get_rate_uses_transient_cache(): void {
		WP_Mock::userFunction( 'get_transient' )->once()->andReturn( [ 'rate' => '0.92' ] );
		// wp_remote_get must NOT be called.
		WP_Mock::userFunction( 'wp_remote_get' )->never();

		$client = new UniRate_Client( 'test-key' );
		$rate   = $client->get_rate( 'USD', 'EUR' );

		$this->assertEqualsWithDelta( 0.92, $rate, 0.001 );
	}

	// ── convert ──────────────────────────────────────────────────────────────

	public function test_convert_returns_float(): void {
		$this->mock_successful_request( '{"result":"92.50"}' );

		$client = new UniRate_Client( 'test-key' );
		$result = $client->convert( 'EUR', 100.0, 'USD' );

		$this->assertEqualsWithDelta( 92.50, $result, 0.01 );
	}

	// ── get_currencies ───────────────────────────────────────────────────────

	public function test_get_currencies_returns_array(): void {
		$this->mock_successful_request( '{"currencies":["USD","EUR","GBP"]}' );

		$client      = new UniRate_Client( 'test-key' );
		$currencies  = $client->get_currencies();

		$this->assertContains( 'USD', $currencies );
		$this->assertContains( 'EUR', $currencies );
	}

	// ── error mapping ────────────────────────────────────────────────────────

	public function test_throws_authentication_exception_on_401(): void {
		$this->expectException( UniRate_Authentication_Exception::class );
		$this->mock_error_request( 401 );

		( new UniRate_Client( 'bad-key' ) )->get_rate( 'USD', 'EUR' );
	}

	public function test_throws_unirate_exception_on_403(): void {
		$this->expectException( UniRate_Exception::class );
		$this->mock_error_request( 403 );

		( new UniRate_Client( 'test-key' ) )->get_rate( 'USD', 'EUR' );
	}

	public function test_throws_currency_exception_on_404(): void {
		$this->expectException( UniRate_Currency_Exception::class );
		$this->mock_error_request( 404 );

		( new UniRate_Client( 'test-key' ) )->get_rate( 'USD', 'XYZ' );
	}

	public function test_throws_rate_limit_exception_on_429(): void {
		$this->expectException( UniRate_Rate_Limit_Exception::class );
		$this->mock_error_request( 429 );

		( new UniRate_Client( 'test-key' ) )->get_rate( 'USD', 'EUR' );
	}

	public function test_throws_exception_on_5xx(): void {
		$this->expectException( UniRate_Exception::class );
		$this->mock_error_request( 503 );

		( new UniRate_Client( 'test-key' ) )->get_rate( 'USD', 'EUR' );
	}

	public function test_throws_exception_on_wp_error(): void {
		$this->expectException( UniRate_Exception::class );

		$wpError = new WP_Error( 'http_request_failed', 'Connection timed out' );

		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( $wpError );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( true );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->never();

		( new UniRate_Client( 'test-key' ) )->get_rate( 'USD', 'EUR' );
	}

	public function test_throws_exception_on_invalid_json(): void {
		$this->expectException( UniRate_Exception::class );

		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( [] );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 200 );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( 'not-json' );
		WP_Mock::userFunction( 'set_transient' )->never();

		( new UniRate_Client( 'test-key' ) )->get_rate( 'USD', 'EUR' );
	}

	public function test_cache_key_excludes_api_key(): void {
		// Two requests with different API keys but same pair must hit the same transient key.
		$captured_keys = [];

		WP_Mock::userFunction( 'get_transient' )
			->twice()
			->andReturnUsing( function ( $key ) use ( &$captured_keys ) {
				$captured_keys[] = $key;
				return false;
			} );
		WP_Mock::userFunction( 'wp_remote_get' )->twice()->andReturn( [] );
		WP_Mock::userFunction( 'is_wp_error' )->twice()->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->twice()->andReturn( 200 );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->twice()->andReturn( '{"rate":"0.92"}' );
		WP_Mock::userFunction( 'set_transient' )->twice()->andReturn( true );

		( new UniRate_Client( 'key-one' ) )->get_rate( 'USD', 'EUR' );
		( new UniRate_Client( 'key-two' ) )->get_rate( 'USD', 'EUR' );

		$this->assertCount( 2, $captured_keys );
		$this->assertSame( $captured_keys[0], $captured_keys[1] );
	}

	// ── helpers ──────────────────────────────────────────────────────────────

	private function mock_successful_request( string $body ): void {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( [] );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 200 );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( $body );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );
	}

	private function mock_error_request( int $code ): void {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( [] );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( $code );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '' );
	}
}
