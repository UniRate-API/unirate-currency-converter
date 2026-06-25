<?php
declare( strict_types=1 );

use WP_Mock\Tools\TestCase;

class ShortcodeTest extends TestCase {

	public function setUp(): void {
		WP_Mock::setUp();
		parent::setUp();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
		parent::tearDown();
	}

	public function test_init_registers_three_shortcodes(): void {
		WP_Mock::userFunction( 'add_shortcode' )->times( 3 );
		( new UniRate_Shortcode() )->init();
		$this->addToAssertionCount( 1 );
	}

	public function test_render_rate_outputs_formatted_rate(): void {
		$this->mock_client_api_key();
		$this->mock_client_request( '{"rate":"0.9235"}' );
		WP_Mock::userFunction( 'shortcode_atts' )->andReturnUsing(
			fn( $defaults, $atts, $tag ) => array_merge( $defaults, (array) $atts )
		);

		$output = ( new UniRate_Shortcode() )->render_rate( [ 'from' => 'USD', 'to' => 'EUR' ] );

		$this->assertStringContainsString( 'USD', $output );
		$this->assertStringContainsString( 'EUR', $output );
		$this->assertStringContainsString( 'unirate-rate', $output );
	}

	public function test_render_convert_outputs_converted_amount(): void {
		$this->mock_client_api_key();
		$this->mock_client_request( '{"result":"92.50"}' );
		WP_Mock::userFunction( 'shortcode_atts' )->andReturnUsing(
			fn( $defaults, $atts, $tag ) => array_merge( $defaults, (array) $atts )
		);

		$output = ( new UniRate_Shortcode() )->render_convert( [ 'from' => 'USD', 'to' => 'EUR', 'amount' => '100' ] );

		$this->assertStringContainsString( 'EUR', $output );
		$this->assertStringContainsString( 'unirate-convert', $output );
	}

	public function test_render_widget_outputs_form_html(): void {
		$this->mock_client_api_key();
		WP_Mock::userFunction( 'shortcode_atts' )->andReturnUsing(
			fn( $defaults, $atts, $tag ) => array_merge( $defaults, (array) $atts )
		);
		WP_Mock::userFunction( 'rest_url' )->andReturn( 'http://example.com/wp-json/unirate/v1/convert' );
		WP_Mock::userFunction( 'wp_enqueue_style' )->andReturn( null );
		WP_Mock::userFunction( 'wp_enqueue_script' )->andReturn( null );

		$output = ( new UniRate_Shortcode() )->render_widget( [ 'from' => 'USD', 'to' => 'EUR' ] );

		$this->assertStringContainsString( 'unirate-widget', $output );
		$this->assertStringContainsString( '<form', $output );
		$this->assertStringContainsString( 'USD', $output );
		$this->assertStringContainsString( 'EUR', $output );
	}

	public function test_render_rate_returns_empty_for_missing_key_non_admin(): void {
		WP_Mock::userFunction( 'get_option' )->andReturn( '' );
		WP_Mock::userFunction( 'current_user_can' )->andReturn( false );
		WP_Mock::userFunction( 'shortcode_atts' )->andReturnUsing(
			fn( $defaults, $atts, $tag ) => array_merge( $defaults, (array) $atts )
		);

		$output = ( new UniRate_Shortcode() )->render_rate( [ 'from' => 'USD', 'to' => 'EUR' ] );

		$this->assertSame( '', $output );
	}

	public function test_render_rate_shows_admin_notice_for_missing_key(): void {
		WP_Mock::userFunction( 'get_option' )->andReturn( '' );
		WP_Mock::userFunction( 'current_user_can' )->andReturn( true );
		WP_Mock::userFunction( 'admin_url' )->andReturn( 'http://example.com/wp-admin/options-general.php?page=unirate-settings' );
		WP_Mock::userFunction( 'shortcode_atts' )->andReturnUsing(
			fn( $defaults, $atts, $tag ) => array_merge( $defaults, (array) $atts )
		);

		$output = ( new UniRate_Shortcode() )->render_rate( [ 'from' => 'USD', 'to' => 'EUR' ] );

		$this->assertStringContainsString( 'unirate-notice', $output );
	}

	public function test_render_rate_escapes_currency_code_output(): void {
		$this->mock_client_api_key();
		$this->mock_client_request( '{"rate":"0.92"}' );
		WP_Mock::userFunction( 'shortcode_atts' )->andReturnUsing(
			fn( $defaults, $atts, $tag ) => array_merge( $defaults, (array) $atts )
		);

		// Attempt XSS via currency code — the shortcode uppercases and escapes.
		$output = ( new UniRate_Shortcode() )->render_rate( [ 'from' => '<script>', 'to' => 'EUR' ] );

		$this->assertStringNotContainsString( '<script>', $output );
	}

	public function test_render_convert_shows_error_on_api_exception(): void {
		$this->mock_client_api_key();
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( [] );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 503 );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( '' );
		WP_Mock::userFunction( 'shortcode_atts' )->andReturnUsing(
			fn( $defaults, $atts, $tag ) => array_merge( $defaults, (array) $atts )
		);

		$output = ( new UniRate_Shortcode() )->render_convert( [ 'from' => 'USD', 'to' => 'EUR', 'amount' => '100' ] );

		$this->assertStringContainsString( 'unirate-error', $output );
	}

	// ── helpers ──────────────────────────────────────────────────────────────

	private function mock_client_api_key(): void {
		WP_Mock::userFunction( 'get_option' )->andReturn( 'ur_live_test' );
	}

	private function mock_client_request( string $body ): void {
		WP_Mock::userFunction( 'get_transient' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_get' )->andReturn( [] );
		WP_Mock::userFunction( 'is_wp_error' )->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 200 );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( $body );
		WP_Mock::userFunction( 'set_transient' )->andReturn( true );
	}
}
