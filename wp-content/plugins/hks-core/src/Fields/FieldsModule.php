<?php
/**
 * Secure Custom Fields integration module.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Fields;

use HolidayKenyaSafaris\Core\Contracts\Module as ModuleContract;

defined( 'ABSPATH' ) || exit;

/**
 * Registers code-owned field groups and the site settings screen.
 */
final class FieldsModule implements ModuleContract {

	/**
	 * Register SCF hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'acf/include_fields', array( $this, 'register_field_groups' ) );
		add_action( 'acf/init', array( $this, 'register_options_page' ) );
	}

	/**
	 * Register deterministic, version-controlled field definitions.
	 *
	 * @return void
	 */
	public function register_field_groups() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		foreach ( FieldGroups::all() as $field_group ) {
			acf_add_local_field_group( $field_group );
		}
	}

	/**
	 * Register the private HKS Settings options page.
	 *
	 * @return void
	 */
	public function register_options_page() {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		acf_add_options_page(
			array(
				'page_title' => __( 'Holiday Kenya Safaris Settings', 'hks-core' ),
				'menu_title' => __( 'HKS Settings', 'hks-core' ),
				'menu_slug'  => 'hks-settings',
				'capability' => 'manage_options',
				'position'   => 59,
				'icon_url'   => 'dashicons-admin-settings',
				'post_id'    => 'hks_settings',
				'redirect'   => false,
				'autoload'   => false,
			)
		);
	}
}
