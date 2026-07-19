<?php
/**
 * Occasion taxonomy.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Content\Taxonomies;

use HolidayKenyaSafaris\Core\Content\PostTypes\Campaign;
use HolidayKenyaSafaris\Core\Content\PostTypes\Tour;

defined( 'ABSPATH' ) || exit;

/**
 * Registers non-exclusive audience and occasion groupings.
 */
final class Occasion {

	/**
	 * WordPress taxonomy name.
	 *
	 * @var string
	 */
	public const TAXONOMY = 'hks_occasion';

	/**
	 * Register the taxonomy.
	 *
	 * @return void
	 */
	public static function register() {
		register_taxonomy(
			self::TAXONOMY,
			array( Tour::POST_TYPE, Campaign::POST_TYPE ),
			array(
				'labels'             => self::labels(),
				'description'        => __( 'Flexible occasion or audience groupings for trip discovery and campaign organization. These labels are not rigid customer identities.', 'hks-core' ),
				'public'             => true,
				'publicly_queryable' => true,
				'hierarchical'       => true,
				'show_ui'            => true,
				'show_admin_column'  => true,
				'show_in_nav_menus'  => true,
				'show_tagcloud'      => false,
				'show_in_quick_edit' => true,
				'show_in_rest'       => true,
				'rest_base'          => 'occasions',
				'rest_namespace'     => 'wp/v2',
				'query_var'          => true,
				'rewrite'            => array(
					'slug'         => 'occasions',
					'with_front'   => false,
					'hierarchical' => true,
				),
			)
		);
	}

	/**
	 * Return editor and administration labels.
	 *
	 * @return array<string, string>
	 */
	private static function labels() {
		return array(
			'name'                       => __( 'Occasions & Audiences', 'hks-core' ),
			'singular_name'              => __( 'Occasion or Audience', 'hks-core' ),
			'menu_name'                  => __( 'Occasions & Audiences', 'hks-core' ),
			'all_items'                  => __( 'All Occasions & Audiences', 'hks-core' ),
			'edit_item'                  => __( 'Edit Occasion or Audience', 'hks-core' ),
			'view_item'                  => __( 'View Occasion or Audience', 'hks-core' ),
			'update_item'                => __( 'Update Occasion or Audience', 'hks-core' ),
			'add_new_item'               => __( 'Add New Occasion or Audience', 'hks-core' ),
			'new_item_name'              => __( 'New Occasion or Audience Name', 'hks-core' ),
			'parent_item'                => __( 'Parent Occasion or Audience', 'hks-core' ),
			'parent_item_colon'          => __( 'Parent Occasion or Audience:', 'hks-core' ),
			'search_items'               => __( 'Search Occasions & Audiences', 'hks-core' ),
			'popular_items'              => __( 'Popular Occasions & Audiences', 'hks-core' ),
			'separate_items_with_commas' => __( 'Separate occasions and audiences with commas', 'hks-core' ),
			'add_or_remove_items'        => __( 'Add or remove occasions and audiences', 'hks-core' ),
			'choose_from_most_used'      => __( 'Choose from the most-used occasions and audiences', 'hks-core' ),
			'not_found'                  => __( 'No occasions or audiences found.', 'hks-core' ),
			'no_terms'                   => __( 'No occasions or audiences', 'hks-core' ),
			'filter_by_item'             => __( 'Filter by occasion or audience', 'hks-core' ),
			'items_list_navigation'      => __( 'Occasions & Audiences list navigation', 'hks-core' ),
			'items_list'                 => __( 'Occasions & Audiences list', 'hks-core' ),
			'back_to_items'              => __( '&larr; Back to Occasions & Audiences', 'hks-core' ),
			'item_link'                  => __( 'Occasion or Audience Link', 'hks-core' ),
			'item_link_description'      => __( 'A link to an occasion or audience.', 'hks-core' ),
		);
	}
}
