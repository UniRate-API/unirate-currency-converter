<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UniRate_Block {

	public function init(): void {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function register(): void {
		register_block_type(
			UNIRATE_PLUGIN_DIR . 'blocks/currency-converter',
			array(
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/** @param array<string,mixed> $attributes */
	public function render( array $attributes, string $content ): string {
		$from   = strtoupper( (string) ( $attributes['from'] ?? 'USD' ) );
		$to     = strtoupper( (string) ( $attributes['to'] ?? 'EUR' ) );
		$amount = max( 0.0, (float) ( $attributes['amount'] ?? 1.0 ) );
		$style  = $attributes['displayStyle'] ?? 'widget';

		$shortcode = new UniRate_Shortcode();
		$shortcode->init();

		if ( 'rate' === $style ) {
			return $shortcode->render_rate( compact( 'from', 'to' ) );
		}
		if ( 'convert' === $style ) {
			return $shortcode->render_convert(
				array(
					'from'   => $from,
					'to'     => $to,
					'amount' => $amount,
				)
			);
		}

		return $shortcode->render_widget(
			array(
				'from'   => $from,
				'to'     => $to,
				'amount' => $amount,
			)
		);
	}
}
