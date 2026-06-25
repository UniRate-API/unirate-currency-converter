<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UniRate_REST_API {

	private const NAMESPACE = 'unirate/v1';

	public function init(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/rate',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_rate' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'from' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'to'   => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/convert',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_convert' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'from'   => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'to'     => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'amount' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/currencies',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_currencies' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public function handle_rate( WP_REST_Request $request ): WP_REST_Response {
		$client = $this->get_client();
		if ( null === $client ) {
			return new WP_REST_Response( array( 'error' => 'UniRate API key not configured.' ), 503 );
		}
		try {
			$rate = $client->get_rate(
				(string) $request->get_param( 'from' ),
				(string) $request->get_param( 'to' )
			);
			return new WP_REST_Response( array( 'rate' => $rate ), 200 );
		} catch ( UniRate_Rate_Limit_Exception $e ) {
			return new WP_REST_Response( array( 'error' => 'Rate limit exceeded.' ), 429 );
		} catch ( UniRate_Currency_Exception $e ) {
			return new WP_REST_Response( array( 'error' => 'Currency not found.' ), 404 );
		} catch ( UniRate_Exception $e ) {
			return new WP_REST_Response( array( 'error' => 'Unable to fetch rate.' ), 502 );
		}
	}

	public function handle_convert( WP_REST_Request $request ): WP_REST_Response {
		$client = $this->get_client();
		if ( null === $client ) {
			return new WP_REST_Response( array( 'error' => 'UniRate API key not configured.' ), 503 );
		}
		try {
			$result = $client->convert(
				(string) $request->get_param( 'to' ),
				(float) $request->get_param( 'amount' ),
				(string) $request->get_param( 'from' )
			);
			return new WP_REST_Response( array( 'result' => $result ), 200 );
		} catch ( UniRate_Rate_Limit_Exception $e ) {
			return new WP_REST_Response( array( 'error' => 'Rate limit exceeded.' ), 429 );
		} catch ( UniRate_Currency_Exception $e ) {
			return new WP_REST_Response( array( 'error' => 'Currency not found.' ), 404 );
		} catch ( UniRate_Exception $e ) {
			return new WP_REST_Response( array( 'error' => 'Unable to convert amount.' ), 502 );
		}
	}

	public function handle_currencies( WP_REST_Request $request ): WP_REST_Response {
		$client = $this->get_client();
		if ( null === $client ) {
			return new WP_REST_Response( array( 'error' => 'UniRate API key not configured.' ), 503 );
		}
		try {
			$currencies = $client->get_currencies();
			return new WP_REST_Response( array( 'currencies' => $currencies ), 200 );
		} catch ( UniRate_Exception $e ) {
			return new WP_REST_Response( array( 'error' => 'Unable to fetch currencies.' ), 502 );
		}
	}

	private function get_client(): ?UniRate_Client {
		$key = UniRate_Settings::get_api_key();
		if ( '' === $key ) {
			return null;
		}
		return new UniRate_Client( $key );
	}
}
