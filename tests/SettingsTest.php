<?php
declare( strict_types=1 );

use WP_Mock\Tools\TestCase;

class SettingsTest extends TestCase {

	public function setUp(): void {
		WP_Mock::setUp();
		parent::setUp();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
		parent::tearDown();
	}

	public function test_get_api_key_returns_empty_string_by_default(): void {
		WP_Mock::userFunction( 'get_option' )
			->with( 'unirate_api_key', '' )
			->andReturn( '' );

		$this->assertSame( '', UniRate_Settings::get_api_key() );
	}

	public function test_get_api_key_returns_stored_value(): void {
		WP_Mock::userFunction( 'get_option' )
			->with( 'unirate_api_key', '' )
			->andReturn( 'ur_live_abc123' );

		$this->assertSame( 'ur_live_abc123', UniRate_Settings::get_api_key() );
	}

	public function test_init_registers_admin_menu_hook(): void {
		WP_Mock::expectActionAdded( 'admin_menu', [ \Mockery::any(), 'add_settings_page' ] );
		WP_Mock::expectActionAdded( 'admin_init', [ \Mockery::any(), 'register_settings' ] );

		( new UniRate_Settings() )->init();
		$this->addToAssertionCount( 2 );
	}

	public function test_sanitize_api_key_trims_whitespace(): void {
		WP_Mock::userFunction( 'sanitize_text_field' )->andReturnUsing( fn( $v ) => $v );
		$settings = new UniRate_Settings();
		$this->assertSame( 'ur_live_abc', $settings->sanitize_api_key( '  ur_live_abc  ' ) );
	}

	public function test_sanitize_api_key_strips_html(): void {
		WP_Mock::userFunction( 'sanitize_text_field' )
			->andReturnUsing( fn( $v ) => strip_tags( $v ) );

		$settings = new UniRate_Settings();
		$result   = $settings->sanitize_api_key( '<script>bad</script>ur_live_abc' );
		$this->assertStringNotContainsString( '<script>', $result );
	}

	public function test_render_settings_page_outputs_form(): void {
		WP_Mock::userFunction( 'current_user_can' )->andReturn( true );
		WP_Mock::userFunction( 'get_admin_page_title' )->andReturn( 'UniRate Currency Converter' );
		WP_Mock::userFunction( 'settings_fields' )->andReturn( null );
		WP_Mock::userFunction( 'do_settings_sections' )->andReturn( null );
		WP_Mock::userFunction( 'submit_button' )->andReturn( null );

		ob_start();
		( new UniRate_Settings() )->render_settings_page();
		$output = ob_get_clean();

		$this->assertStringContainsString( '<form', $output );
		$this->assertStringContainsString( 'options.php', $output );
	}

	public function test_render_settings_page_skips_when_not_admin(): void {
		WP_Mock::userFunction( 'current_user_can' )->andReturn( false );

		ob_start();
		( new UniRate_Settings() )->render_settings_page();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}
}
