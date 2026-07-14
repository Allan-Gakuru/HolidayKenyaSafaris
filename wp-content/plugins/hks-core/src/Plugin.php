<?php
/**
 * Main plugin coordinator.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core;

use HolidayKenyaSafaris\Core\Content\Module as ContentModule;
use HolidayKenyaSafaris\Core\Contracts\Module;
use HolidayKenyaSafaris\Core\Fields\FieldsModule;
use HolidayKenyaSafaris\Core\Fields\PublicationGuard;
use HolidayKenyaSafaris\Core\Seed\Module as SeedModule;

defined( 'ABSPATH' ) || exit;

/**
 * Boots independent site-functionality modules after all plugins are available.
 */
final class Plugin {

	/**
	 * Shared plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance;

	/**
	 * Registered modules.
	 *
	 * @var Module[]
	 */
	private $modules = array();

	/**
	 * Whether the plugin has already booted.
	 *
	 * @var bool
	 */
	private $booted = false;

	/**
	 * Whether this request has already attempted to boot the plugin.
	 *
	 * @var bool
	 */
	private $boot_attempted = false;

	/**
	 * Return the shared plugin instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( ! self::$instance instanceof self ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register translations and each configured module once.
	 *
	 * @return void
	 */
	public function boot() {
		if ( $this->booted || $this->boot_attempted ) {
			return;
		}

		$this->boot_attempted = true;

		$upgrade_result = Lifecycle::maybe_upgrade();

		if ( is_wp_error( $upgrade_result ) ) {
			return;
		}

		$this->booted = true;

		add_action( 'init', array( $this, 'load_textdomain' ), 0 );
		$this->register_modules();

		/**
		 * Fires once the HKS Core coordinator has registered every module.
		 *
		 * @param Plugin $plugin Plugin coordinator instance.
		 */
		do_action( 'hks_core_loaded', $this );
	}

	/**
	 * Load translations from the plugin languages directory.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'hks-core',
			false,
			dirname( HKS_CORE_BASENAME ) . '/languages'
		);
	}

	/**
	 * Return the registered modules for diagnostics and tests.
	 *
	 * @return Module[]
	 */
	public function modules() {
		return $this->modules;
	}

	/**
	 * Instantiate and register configured module classes.
	 *
	 * @return void
	 */
	private function register_modules() {
		/**
		 * Filters module class names before HKS Core registers them.
		 *
		 * Classes must implement HolidayKenyaSafaris\Core\Contracts\Module and
		 * expose a zero-argument constructor.
		 *
		 * @param string[] $module_classes Fully qualified module class names.
		 */
		$module_classes = apply_filters(
			'hks_core_module_classes',
			array(
				ContentModule::class,
				FieldsModule::class,
				PublicationGuard::class,
				SeedModule::class,
			)
		);

		if ( ! is_array( $module_classes ) ) {
			return;
		}

		foreach ( $module_classes as $module_class ) {
			if (
				! is_string( $module_class )
				|| ! class_exists( $module_class )
				|| ! is_subclass_of( $module_class, Module::class )
			) {
				continue;
			}

			$module = new $module_class();
			$module->register();

			$this->modules[] = $module;
		}
	}

	/**
	 * Prevent direct construction.
	 */
	private function __construct() {}

	/**
	 * Prevent cloning the coordinator.
	 *
	 * @return void
	 */
	private function __clone() {}
}
