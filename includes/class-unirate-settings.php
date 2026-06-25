<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UniRate_Settings {

	private const OPTION_KEY = 'unirate_api_key';
	private const PAGE_SLUG  = 'unirate-settings';
	private const SECTION    = 'unirate_main';

	public function init(): void {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public static function get_api_key(): string {
		return (string) get_option( self::OPTION_KEY, '' );
	}

	public function add_settings_page(): void {
		add_options_page(
			__( 'UniRate Currency Converter', 'unirate-currency-converter' ),
			__( 'UniRate', 'unirate-currency-converter' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings(): void {
		register_setting(
			self::PAGE_SLUG,
			self::OPTION_KEY,
			array(
				'sanitize_callback' => array( $this, 'sanitize_api_key' ),
				'default'           => '',
			)
		);

		add_settings_section(
			self::SECTION,
			__( 'API Configuration', 'unirate-currency-converter' ),
			'__return_null',
			self::PAGE_SLUG
		);

		add_settings_field(
			self::OPTION_KEY,
			__( 'UniRate API Key', 'unirate-currency-converter' ),
			array( $this, 'render_api_key_field' ),
			self::PAGE_SLUG,
			self::SECTION
		);
	}

	public function sanitize_api_key( string $input ): string {
		return sanitize_text_field( trim( $input ) );
	}

	public function render_api_key_field(): void {
		$value = esc_attr( self::get_api_key() );
		printf(
			'<input type="text" id="unirate_api_key" name="unirate_api_key" value="%s" class="regular-text" placeholder="%s" />
			<p class="description">%s <a href="%s" target="_blank">%s</a>.</p>',
			$value,
			esc_attr__( 'ur_live_xxxxxxxx', 'unirate-currency-converter' ),
			esc_html__( 'Enter your UniRate API key. Get one free at', 'unirate-currency-converter' ),
			esc_url( 'https://unirateapi.com' ),
			esc_html__( 'unirateapi.com', 'unirate-currency-converter' )
		);
	}

	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( self::PAGE_SLUG );
				do_settings_sections( self::PAGE_SLUG );
				submit_button( __( 'Save Settings', 'unirate-currency-converter' ) );
				?>
			</form>
		</div>
		<?php
	}
}
