<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UniRate_Shortcode {

	public function init(): void {
		add_shortcode( 'unirate_rate', array( $this, 'render_rate' ) );
		add_shortcode( 'unirate_convert', array( $this, 'render_convert' ) );
		add_shortcode( 'unirate_widget', array( $this, 'render_widget' ) );
	}

	/** [unirate_rate from="USD" to="EUR"] → "1.00 USD = 0.92 EUR" */
	public function render_rate( array|string $atts ): string {
		$atts = shortcode_atts(
			array(
				'from' => 'USD',
				'to'   => 'EUR',
			),
			$atts,
			'unirate_rate'
		);

		$client = $this->get_client();
		if ( null === $client ) {
			return $this->no_key_notice();
		}

		try {
			$rate = $client->get_rate( $atts['from'], $atts['to'] );
			return sprintf(
				'<span class="unirate-rate">1 %s = %s %s</span>',
				esc_html( strtoupper( $atts['from'] ) ),
				esc_html( number_format( (float) $rate, 4 ) ),
				esc_html( strtoupper( $atts['to'] ) )
			);
		} catch ( UniRate_Exception $e ) {
			return '<span class="unirate-error">' . esc_html__( 'Rate unavailable.', 'unirate-currency-converter' ) . '</span>';
		}
	}

	/** [unirate_convert from="USD" to="EUR" amount="100"] → "92.50" */
	public function render_convert( array|string $atts ): string {
		$atts = shortcode_atts(
			array(
				'from'   => 'USD',
				'to'     => 'EUR',
				'amount' => '1',
			),
			$atts,
			'unirate_convert'
		);

		$client = $this->get_client();
		if ( null === $client ) {
			return $this->no_key_notice();
		}

		try {
			$result = $client->convert(
				$atts['to'],
				(float) $atts['amount'],
				$atts['from']
			);
			return sprintf(
				'<span class="unirate-convert">%s %s</span>',
				esc_html( number_format( $result, 2 ) ),
				esc_html( strtoupper( $atts['to'] ) )
			);
		} catch ( UniRate_Exception $e ) {
			return '<span class="unirate-error">' . esc_html__( 'Conversion unavailable.', 'unirate-currency-converter' ) . '</span>';
		}
	}

	/** [unirate_widget from="USD" to="EUR" amount="1"] → interactive converter */
	public function render_widget( array|string $atts ): string {
		$atts = shortcode_atts(
			array(
				'from'   => 'USD',
				'to'     => 'EUR',
				'amount' => '1',
			),
			$atts,
			'unirate_widget'
		);

		$this->enqueue_assets();

		$rest_url = rest_url( 'unirate/v1/convert' );
		$nonce    = '';

		$from   = esc_attr( strtoupper( $atts['from'] ) );
		$to     = esc_attr( strtoupper( $atts['to'] ) );
		$amount = esc_attr( (float) $atts['amount'] );

		return sprintf(
			'<div class="unirate-widget" data-rest-url="%s" data-nonce="%s">
				<form class="unirate-widget__form">
					<div class="unirate-widget__row">
						<input type="number" class="unirate-widget__amount" value="%s" min="0" step="any" aria-label="%s" />
						<input type="text" class="unirate-widget__from" value="%s" maxlength="3" aria-label="%s" />
						<span class="unirate-widget__arrow">&#8594;</span>
						<input type="text" class="unirate-widget__to" value="%s" maxlength="3" aria-label="%s" />
						<button type="submit" class="unirate-widget__btn">%s</button>
					</div>
				</form>
				<p class="unirate-widget__result" aria-live="polite"></p>
				<p class="unirate-widget__credit">
					<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>
				</p>
			</div>',
			esc_url( $rest_url ),
			esc_attr( $nonce ),
			$amount,
			esc_attr__( 'Amount', 'unirate-currency-converter' ),
			$from,
			esc_attr__( 'From currency', 'unirate-currency-converter' ),
			$to,
			esc_attr__( 'To currency', 'unirate-currency-converter' ),
			esc_html__( 'Convert', 'unirate-currency-converter' ),
			esc_url( 'https://unirateapi.com' ),
			esc_html__( 'Powered by UniRate', 'unirate-currency-converter' )
		);
	}

	public function enqueue_assets(): void {
		wp_enqueue_style(
			'unirate-converter',
			UNIRATE_PLUGIN_URL . 'assets/css/converter.css',
			array(),
			UNIRATE_VERSION
		);
		wp_enqueue_script(
			'unirate-converter',
			UNIRATE_PLUGIN_URL . 'assets/js/converter.js',
			array(),
			UNIRATE_VERSION,
			true
		);
	}

	private function get_client(): ?UniRate_Client {
		$key = UniRate_Settings::get_api_key();
		if ( '' === $key ) {
			return null;
		}
		return new UniRate_Client( $key );
	}

	private function no_key_notice(): string {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}
		return sprintf(
			'<p class="unirate-notice">%s <a href="%s">%s</a>.</p>',
			esc_html__( 'UniRate: API key not configured.', 'unirate-currency-converter' ),
			esc_url( admin_url( 'options-general.php?page=unirate-settings' ) ),
			esc_html__( 'Configure it here', 'unirate-currency-converter' )
		);
	}
}
