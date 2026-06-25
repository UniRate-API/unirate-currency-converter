<?php
declare( strict_types=1 );

// WordPress constants required by plugin classes.
define( 'ABSPATH', '/' );
define( 'UNIRATE_VERSION', '0.1.0' );
define( 'UNIRATE_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
define( 'UNIRATE_PLUGIN_URL', 'http://example.com/wp-content/plugins/unirate-currency-converter/' );
define( 'HOUR_IN_SECONDS', 3600 );

require_once __DIR__ . '/../vendor/autoload.php';

// WordPress class stubs (WP_Error, WP_REST_Request, WP_REST_Response).
require_once __DIR__ . '/stubs/class-wp-stubs.php';

// Bootstrap WP_Mock — mocks global WP functions.
WP_Mock::bootstrap();

// Load plugin classes.
require_once UNIRATE_PLUGIN_DIR . 'includes/class-unirate-exception.php';
require_once UNIRATE_PLUGIN_DIR . 'includes/class-unirate-client.php';
require_once UNIRATE_PLUGIN_DIR . 'includes/class-unirate-settings.php';
require_once UNIRATE_PLUGIN_DIR . 'includes/class-unirate-rest-api.php';
require_once UNIRATE_PLUGIN_DIR . 'includes/class-unirate-shortcode.php';
require_once UNIRATE_PLUGIN_DIR . 'includes/class-unirate-block.php';
