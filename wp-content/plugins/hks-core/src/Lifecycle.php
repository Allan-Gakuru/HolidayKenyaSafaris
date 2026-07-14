<?php
/**
 * Plugin activation and deactivation behavior.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Provides deliberately conservative plugin lifecycle hooks.
 */
final class Lifecycle {

	/**
	 * Validate the environment and record the activated code version.
	 *
	 * @param bool $network_wide Whether the plugin is being network activated.
	 * @return void
	 */
	public static function activate( $network_wide = false ) {
		if ( ! Requirements::is_satisfied() ) {
			if ( ! function_exists( 'deactivate_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			deactivate_plugins(
				HKS_CORE_BASENAME,
				true,
				is_multisite() && (bool) $network_wide
			);

			wp_die(
				esc_html( Requirements::message() ),
				esc_html__( 'HKS Core activation failed', 'hks-core' ),
				array(
					'back_link' => true,
					'response'  => 500,
				)
			);
		}

		if ( is_multisite() && $network_wide ) {
			update_site_option( 'hks_core_version', HKS_CORE_VERSION );
		} else {
			update_option( 'hks_core_version', HKS_CORE_VERSION, false );
		}

		/**
		 * Fires after HKS Core has passed its activation checks.
		 *
		 * @param bool $network_wide Whether the plugin was network activated.
		 */
		do_action( 'hks_core_activated', (bool) $network_wide );
	}

	/**
	 * Leave content and settings untouched when the plugin is deactivated.
	 *
	 * @param bool $network_wide Whether the plugin is being network deactivated.
	 * @return void
	 */
	public static function deactivate( $network_wide = false ) {
		/**
		 * Fires when HKS Core is deactivated.
		 *
		 * No options or future catalogue data are removed here.
		 *
		 * @param bool $network_wide Whether the plugin was network deactivated.
		 */
		do_action( 'hks_core_deactivated', (bool) $network_wide );
	}
}
