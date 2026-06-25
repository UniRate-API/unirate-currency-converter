<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class UniRate_Client {

	private const BASE_URL = 'https://api.unirateapi.com';
	private const CACHE_TTL = HOUR_IN_SECONDS;

	public function __construct( private readonly string $api_key ) {}

	/**
	 * @return float|array<string,float>
	 */
	public function get_rate( string $from = 'USD', ?string $to = null ): float|array {
		$from   = strtoupper( $from );
		$params = array(
			'from'    => $from,
			'api_key' => $this->api_key,
		);
		if ( null !== $to ) {
			$params['to'] = strtoupper( $to );
		}
		$data = $this->request( '/api/rates', $params );
		if ( isset( $data['rate'] ) ) {
			return (float) $data['rate'];
		}
		return array_map( 'floatval', $data['rates'] ?? array() );
	}

	public function convert( string $to, float $amount = 1.0, string $from = 'USD' ): float {
		$data = $this->request(
			'/api/convert',
			array(
				'from'    => strtoupper( $from ),
				'to'      => strtoupper( $to ),
				'amount'  => $amount,
				'api_key' => $this->api_key,
			)
		);
		return (float) ( $data['result'] ?? 0.0 );
	}

	/** @return string[] */
	public function get_currencies(): array {
		$data = $this->request( '/api/currencies', array( 'api_key' => $this->api_key ) );
		return $data['currencies'] ?? array();
	}

	private function cache_key( string $endpoint, array $params ): string {
		unset( $params['api_key'] );
		ksort( $params );
		return 'unirate_' . md5( $endpoint . serialize( $params ) );
	}

	/** @return array<string,mixed> */
	private function request( string $endpoint, array $params ): array {
		$key    = $this->cache_key( $endpoint, $params );
		$cached = get_transient( $key );
		if ( false !== $cached ) {
			return $cached;
		}

		$url      = self::BASE_URL . $endpoint . '?' . http_build_query( $params );
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 30,
				'headers' => array(
					'Accept'     => 'application/json',
					'User-Agent' => 'unirate-wordpress/' . UNIRATE_VERSION,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new UniRate_Exception( $response->get_error_message(), 0 );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( 401 === $code ) {
			throw new UniRate_Authentication_Exception( 'Missing or invalid API key', 401 );
		}
		if ( 403 === $code ) {
			throw new UniRate_Exception( 'Endpoint requires a Pro subscription', 403 );
		}
		if ( 404 === $code ) {
			throw new UniRate_Currency_Exception( 'Currency not found or no data available', 404 );
		}
		if ( 429 === $code ) {
			throw new UniRate_Rate_Limit_Exception( 'Rate limit exceeded', 429 );
		}
		if ( $code < 200 || $code >= 300 ) {
			throw new UniRate_Exception( 'API error: ' . $body, $code );
		}

		$data = json_decode( $body, true );
		if ( ! is_array( $data ) ) {
			throw new UniRate_Exception( 'Invalid JSON response from UniRate API', $code );
		}

		set_transient( $key, $data, self::CACHE_TTL );
		return $data;
	}
}
