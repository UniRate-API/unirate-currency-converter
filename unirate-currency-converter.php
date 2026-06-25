<?php
/**
 * Plugin Name:       UniRate Currency Converter
 * Plugin URI:        https://github.com/UniRate-API/unirate-currency-converter
 * Description:       Live currency conversion in posts, pages, and blocks via UniRate API. Shortcodes, a Gutenberg block, and an interactive widget — API key stays server-side.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Unirate Team
 * Author URI:        https://unirateapi.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       unirate-currency-converter
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'UNIRATE_VERSION', '0.1.0' );
define( 'UNIRATE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UNIRATE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'UNIRATE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once UNIRATE_PLUGIN_DIR . 'includes/class-unirate-exception.php';
require_once UNIRATE_PLUGIN_DIR . 'includes/class-unirate-client.php';
require_once UNIRATE_PLUGIN_DIR . 'includes/class-unirate-settings.php';
require_once UNIRATE_PLUGIN_DIR . 'includes/class-unirate-rest-api.php';
require_once UNIRATE_PLUGIN_DIR . 'includes/class-unirate-shortcode.php';
require_once UNIRATE_PLUGIN_DIR . 'includes/class-unirate-block.php';

add_action(
	'plugins_loaded',
	static function () {
		( new UniRate_Settings() )->init();
		( new UniRate_REST_API() )->init();
		( new UniRate_Shortcode() )->init();
		( new UniRate_Block() )->init();
	}
);

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	static function ( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=unirate-settings' ) ),
			esc_html__( 'Settings', 'unirate-currency-converter' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}
);
