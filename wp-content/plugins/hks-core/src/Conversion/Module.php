<?php
/**
 * Qualified inquiry and WhatsApp conversion module.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Conversion;

use HolidayKenyaSafaris\Core\Contracts\Module as ModuleContract;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the quote block, private capture API, and inquiry administration.
 */
final class Module implements ModuleContract {

	/**
	 * Register conversion hooks.
	 *
	 * @return void
	 */
	public function register() {
		$repository = new InquiryRepository();
		$admin      = new InquiryAdmin();

		add_action( 'init', array( $this, 'register_assets' ), 8 );
		add_action( 'init', array( $this, 'register_quote_block' ), 10 );
		add_action( 'rest_api_init', array( $repository, 'register_routes' ) );

		$admin->register();
	}

	/**
	 * Register front-end assets without loading them on unrelated pages.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_style(
			'hks-inquiry',
			HKS_CORE_URL . 'assets/css/inquiry.css',
			array(),
			HKS_CORE_VERSION
		);
		wp_register_script(
			'hks-inquiry',
			HKS_CORE_URL . 'assets/js/inquiry.js',
			array(),
			HKS_CORE_VERSION,
			true
		);
	}

	/**
	 * Register the server-rendered CTA and intake block.
	 *
	 * @return void
	 */
	public function register_quote_block() {
		register_block_type(
			HKS_CORE_PATH . 'blocks/quote-cta',
			array(
				'render_callback' => array( QuoteBlock::class, 'render' ),
			)
		);
	}
}
