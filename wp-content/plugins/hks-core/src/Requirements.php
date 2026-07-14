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
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_notices', array( self::class, 'render_admin_notice' ) );
		add_action( 'network_admin_notices', array( self::class, 'render_admin_notice' ) );
	}

	/**
	 * Determine whether the current runtime meets the project baseline.
	 *
	 * @param bool|null $include_scf Whether to check SCF after plugins have loaded.
	 * @return bool
	 */
	public static function is_satisfied( $include_scf = null ) {
		return array() === self::unmet( $include_scf );
	}

	/**
	 * Return unmet requirements keyed by runtime.
	 *
	 * @param bool|null $include_scf Whether to check SCF after plugins have loaded.
	 * @return array<string, array<string, string>>
	 */
	public static function unmet( $include_scf = null ) {
		global $wp_version;

		$unmet              = array();
		$current_wp_version = is_string( $wp_version ) ? $wp_version : 'unknown';
		$include_scf        = null === $include_scf ? did_action( 'plugins_loaded' ) > 0 : (bool) $include_scf;

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

		if ( $include_scf ) {
			$scf_plugin_active    = self::is_scf_plugin_active();
			$current_scf_version = $scf_plugin_active && defined( 'ACF_VERSION' ) ? (string) ACF_VERSION : 'unavailable';
			$scf_api_available    = function_exists( 'acf_add_local_field_group' );

			if (
				! $scf_plugin_active
				|| ! $scf_api_available
				|| 'unavailable' === $current_scf_version
				|| version_compare( $current_scf_version, HKS_CORE_MINIMUM_SCF_VERSION, '<' )
			) {
				$unmet['scf'] = array(
					'required' => HKS_CORE_MINIMUM_SCF_VERSION,
					'current'  => $current_scf_version,
				);
			}
		}

		return $unmet;
	}

	/**
	 * Build a human-readable description of all unmet requirements.
	 *
	 * @param bool|null $include_scf Whether to check SCF after plugins have loaded.
	 * @return string
	 */
	public static function message( $include_scf = null ) {
		$messages = array();
		$unmet    = self::unmet( $include_scf );

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

		if ( isset( $unmet['scf'] ) ) {
			$messages[] = sprintf(
				/* translators: 1: Required SCF version. 2: Current SCF version or unavailable. */
				__( 'HKS Core requires Secure Custom Fields %1$s or later; the available version is %2$s.', 'hks-core' ),
				$unmet['scf']['required'],
				$unmet['scf']['current']
			);
		}

		return implode( ' ', $messages );
	}

	/**
	 * Confirm that the declared Secure Custom Fields plugin is active.
	 *
	 * Checking the canonical basename prevents an unrelated ACF-compatible plugin
	 * from satisfying the API/version probes while the required dependency is absent.
	 *
	 * @return bool
	 */
	private static function is_scf_plugin_active() {
		$active_plugins = get_option( 'active_plugins', array() );

		if ( is_array( $active_plugins ) && in_array( HKS_CORE_SCF_BASENAME, $active_plugins, true ) ) {
			return true;
		}

		if ( ! is_multisite() ) {
			return false;
		}

		$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );

		return is_array( $network_active_plugins )
			&& array_key_exists( HKS_CORE_SCF_BASENAME, $network_active_plugins );
	}

	/**
	 * Display a dismiss-proof compatibility error to plugin administrators.
	 *
	 * @return void
	 */
	public static function render_admin_notice() {
		if ( self::is_satisfied( true ) || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p><strong>%1$s</strong> %2$s</p></div>',
			esc_html__( 'Holiday Kenya Safaris Core is not running.', 'hks-core' ),
			esc_html( self::message( true ) )
		);
	}
}
