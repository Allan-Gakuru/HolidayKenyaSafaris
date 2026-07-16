<?php
/**
 * Plugin Name:       HKS Core
 * Description:       Site functionality foundation for the Holiday Kenya Safaris catalogue, campaigns, and inquiry journey.
 * Version:           0.6.0
 * Update URI:        https://github.com/Allan-Gakuru/HolidayKenyaSafaris
 * Requires at least: 6.6
 * Requires PHP:      8.3
 * Requires Plugins:  secure-custom-fields
 * Author:            Holiday Kenya Safaris
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hks-core
 * Domain Path:       /languages
 *
 * @package HolidayKenyaSafaris\Core
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'HKS_CORE_VERSION' ) ) {
	define( 'HKS_CORE_VERSION', '0.6.0' );
}

if ( ! defined( 'HKS_CORE_MINIMUM_PHP_VERSION' ) ) {
	define( 'HKS_CORE_MINIMUM_PHP_VERSION', '8.3' );
}

if ( ! defined( 'HKS_CORE_MINIMUM_WP_VERSION' ) ) {
	define( 'HKS_CORE_MINIMUM_WP_VERSION', '6.6' );
}

if ( ! defined( 'HKS_CORE_MINIMUM_SCF_VERSION' ) ) {
	define( 'HKS_CORE_MINIMUM_SCF_VERSION', '6.9.1' );
}

if ( ! defined( 'HKS_CORE_SCF_BASENAME' ) ) {
	define( 'HKS_CORE_SCF_BASENAME', 'secure-custom-fields/secure-custom-fields.php' );
}

if ( ! defined( 'HKS_CORE_FILE' ) ) {
	define( 'HKS_CORE_FILE', __FILE__ );
}

if ( ! defined( 'HKS_CORE_PATH' ) ) {
	define( 'HKS_CORE_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'HKS_CORE_URL' ) ) {
	define( 'HKS_CORE_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'HKS_CORE_BASENAME' ) ) {
	define( 'HKS_CORE_BASENAME', plugin_basename( __FILE__ ) );
}

require_once HKS_CORE_PATH . 'src/Autoloader.php';

\HolidayKenyaSafaris\Core\Autoloader::register( HKS_CORE_PATH . 'src' );

register_activation_hook(
	HKS_CORE_FILE,
	array( \HolidayKenyaSafaris\Core\Lifecycle::class, 'activate' )
);

register_deactivation_hook(
	HKS_CORE_FILE,
	array( \HolidayKenyaSafaris\Core\Lifecycle::class, 'deactivate' )
);

\HolidayKenyaSafaris\Core\Requirements::register();

if ( ! \HolidayKenyaSafaris\Core\Requirements::is_satisfied( false ) ) {
	return;
}

add_action(
	'plugins_loaded',
	static function () {
		if ( ! \HolidayKenyaSafaris\Core\Requirements::is_satisfied( true ) ) {
			return;
		}

		\HolidayKenyaSafaris\Core\Plugin::instance()->boot();
	},
	20
);
