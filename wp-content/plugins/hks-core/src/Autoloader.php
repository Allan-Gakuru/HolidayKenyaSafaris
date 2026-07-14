<?php
/**
 * Lightweight project autoloader.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Loads classes in the HolidayKenyaSafaris\Core namespace from src/.
 */
final class Autoloader {

	/**
	 * Namespace prefix handled by this loader.
	 *
	 * @var string
	 */
	private const PREFIX = __NAMESPACE__ . '\\';

	/**
	 * Absolute source directory with a trailing separator.
	 *
	 * @var string
	 */
	private static $base_directory = '';

	/**
	 * Whether the loader has already been registered.
	 *
	 * @var bool
	 */
	private static $registered = false;

	/**
	 * Register the autoloader once.
	 *
	 * @param string $base_directory Absolute path to the plugin source directory.
	 * @return void
	 */
	public static function register( $base_directory ) {
		if ( self::$registered ) {
			return;
		}

		self::$base_directory = rtrim( (string) $base_directory, '/\\' ) . DIRECTORY_SEPARATOR;
		self::$registered     = spl_autoload_register( array( self::class, 'autoload' ) );
	}

	/**
	 * Load a matching project class.
	 *
	 * @param string $class Fully qualified class name.
	 * @return void
	 */
	public static function autoload( $class ) {
		$class = ltrim( (string) $class, '\\' );

		if ( 0 !== strncmp( $class, self::PREFIX, strlen( self::PREFIX ) ) ) {
			return;
		}

		$relative_class = substr( $class, strlen( self::PREFIX ) );

		if ( false !== strpos( $relative_class, '..' ) ) {
			return;
		}

		$file = self::$base_directory
			. str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class )
			. '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
}
