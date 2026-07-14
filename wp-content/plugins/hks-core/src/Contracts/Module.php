<?php
/**
 * Plugin module contract.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Contracts;

defined( 'ABSPATH' ) || exit;

/**
 * A module registers its WordPress hooks and nothing else.
 */
interface Module {

	/**
	 * Register the module's hooks.
	 *
	 * @return void
	 */
	public function register();
}
