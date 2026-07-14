<?php
/**
 * Content-model module.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Content;

use HolidayKenyaSafaris\Core\Content\PostTypes\Campaign;
use HolidayKenyaSafaris\Core\Content\PostTypes\Faq;
use HolidayKenyaSafaris\Core\Content\PostTypes\Inquiry;
use HolidayKenyaSafaris\Core\Content\PostTypes\Tour;
use HolidayKenyaSafaris\Core\Content\Taxonomies\Destination;
use HolidayKenyaSafaris\Core\Content\Taxonomies\Occasion;
use HolidayKenyaSafaris\Core\Content\Taxonomies\TourType;
use HolidayKenyaSafaris\Core\Content\Taxonomies\TravelStyle;
use HolidayKenyaSafaris\Core\Contracts\Module as ModuleContract;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the catalogue content model with WordPress.
 */
final class Module implements ModuleContract {

	/**
	 * One-shot site option set by a migration when rewrite rules need refreshing.
	 *
	 * @var string
	 */
	private const FLUSH_REWRITE_OPTION = 'hks_core_flush_rewrite_rules';

	/**
	 * Network generation advanced on each network activation/reactivation.
	 *
	 * @var string
	 */
	private const REWRITE_GENERATION_OPTION = 'hks_core_rewrite_generation';

	/**
	 * Latest network rewrite generation applied to the current site.
	 *
	 * @var string
	 */
	private const APPLIED_REWRITE_GENERATION_OPTION = 'hks_core_rewrite_generation_applied';

	/**
	 * Register the content-model hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_content_model' ), 5 );
		add_action( 'wp_loaded', array( $this, 'maybe_flush_rewrite_rules' ) );
	}

	/**
	 * Register post types before their related taxonomies.
	 *
	 * @return void
	 */
	public function register_content_model() {
		Tour::register();
		Campaign::register();
		Faq::register();
		Inquiry::register();

		Destination::register();
		TourType::register();
		Occasion::register();
		TravelStyle::register();
	}

	/**
	 * Flush pending rewrite rules after init registered every content object.
	 *
	 * The local flag and per-site network generation marker are deliberately not
	 * consumed during init. If option cleanup or the applied-generation write
	 * fails, the next request safely repeats the soft flush.
	 *
	 * @return void
	 */
	public function maybe_flush_rewrite_rules() {
		$local_flush_pending   = (bool) get_option( self::FLUSH_REWRITE_OPTION, false );
		$network_generation    = is_multisite()
			? max( 0, (int) get_site_option( self::REWRITE_GENERATION_OPTION, 0 ) )
			: 0;
		$applied_generation    = max(
			0,
			(int) get_option( self::APPLIED_REWRITE_GENERATION_OPTION, 0 )
		);
		$network_flush_pending = $network_generation > $applied_generation;

		if ( ! $local_flush_pending && ! $network_flush_pending ) {
			return;
		}

		flush_rewrite_rules( false );

		if ( $local_flush_pending ) {
			delete_option( self::FLUSH_REWRITE_OPTION );
		}

		if ( $network_flush_pending ) {
			update_option(
				self::APPLIED_REWRITE_GENERATION_OPTION,
				$network_generation,
				false
			);
		}
	}
}
