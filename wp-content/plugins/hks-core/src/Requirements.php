<?php
/**
 * Runtime requirement checks.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Prevents site functionality from booting on an unsupported environment.
 */
final class Requirements {

	/**
	 * Register an administrator-facing notice when requirements are unmet.
	 *
	 * @return void
	 */
	public static function register() {
		if ( self::is_satisfied() || ! is_admin() ) {
			return;
		}

		add_action( 'admin_notices', array( self::class, 'render_admin_notice' ) );
		add_action( 'network_admin_notices', array( self::class, 'render_admin_notice' ) );
	}

	/**
	 * Determine whether the current runtime meets the project baseline.
	 *
	 * @return bool
	 */
	public static function is_satisfied() {
		return array() === self::unmet();
	}

	/**
	 * Return unmet requirements keyed by runtime.
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function unmet() {
		global $wp_version;

		$unmet              = array();
		$current_wp_version = is_string( $wp_version ) ? $wp_version : 'unknown';

		if ( version_compare( PHP_VERSION, HKS_CORE_MINIMUM_PHP_VERSION, '<' ) ) {
			$unmet['php'] = array(
				'required' => HKS_CORE_MINIMUM_PHP_VERSION,
				'current'  => PHP_VERSION,
			);
		}

		if (
			'unknown' === $current_wp_version
			|| version_compare( $current_wp_version, HKS_CORE_MINIMUM_WP_VERSION, '<' )
		) {
			$unmet['wordpress'] = array(
				'required' => HKS_CORE_MINIMUM_WP_VERSION,
				'current'  => $current_wp_version,
			);
		}

		return $unmet;
	}

	/**
	 * Build a human-readable description of all unmet requirements.
	 *
	 * @return string
	 */
	public static function message() {
		$messages = array();
		$unmet    = self::unmet();

		if ( isset( $unmet['php'] ) ) {
			$messages[] = sprintf(
				/* translators: 1: Required PHP version. 2: Current PHP version. */
				__( 'HKS Core requires PHP %1$s or later; this server is running PHP %2$s.', 'hks-core' ),
				$unmet['php']['required'],
				$unmet['php']['current']
			);
		}

		if ( isset( $unmet['wordpress'] ) ) {
			$messages[] = sprintf(
				/* translators: 1: Required WordPress version. 2: Current WordPress version. */
				__( 'HKS Core requires WordPress %1$s or later; this site is running WordPress %2$s.', 'hks-core' ),
				$unmet['wordpress']['required'],
				$unmet['wordpress']['current']
			);
		}

		return implode( ' ', $messages );
	}

	/**
	 * Display a dismiss-proof compatibility error to plugin administrators.
	 *
	 * @return void
	 */
	public static function render_admin_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p><strong>%1$s</strong> %2$s</p></div>',
			esc_html__( 'Holiday Kenya Safaris Core is not running.', 'hks-core' ),
			esc_html( self::message() )
		);
	}
}
