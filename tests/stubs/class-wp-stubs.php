<?php
/**
 * Minimal WordPress stub classes for unit tests.
 * These let us type-hint against WP classes without a full WP install.
 */

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		private string $code;
		private string $message;

		public function __construct( string $code = '', string $message = '' ) {
			$this->code    = $code;
			$this->message = $message;
		}

		public function get_error_message(): string {
			return $this->message;
		}

		public function get_error_code(): string {
			return $this->code;
		}
	}
}

if ( ! class_exists( 'WP_REST_Request' ) ) {
	class WP_REST_Request {
		/** @var array<string,mixed> */
		private array $params;

		/** @param array<string,mixed> $params */
		public function __construct( array $params = [] ) {
			$this->params = $params;
		}

		public function get_param( string $key ): mixed {
			return $this->params[ $key ] ?? null;
		}

		/** @param array<string,mixed> $params */
		public function set_params( array $params ): void {
			$this->params = $params;
		}
	}
}

if ( ! class_exists( 'WP_REST_Response' ) ) {
	class WP_REST_Response {
		private int $status;
		private mixed $data;

		public function __construct( mixed $data = null, int $status = 200 ) {
			$this->data   = $data;
			$this->status = $status;
		}

		public function get_status(): int {
			return $this->status;
		}

		public function get_data(): mixed {
			return $this->data;
		}
	}
}
