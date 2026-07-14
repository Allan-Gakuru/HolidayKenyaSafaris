<?php
/**
 * Tour Type taxonomy.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Content\Taxonomies;

use HolidayKenyaSafaris\Core\Content\PostTypes\Tour;

defined( 'ABSPATH' ) || exit;

/**
 * Registers product-format classifications for canonical tours.
 */
final class TourType {

	/**
	 * WordPress taxonomy name.
	 *
	 * @var string
	 */
	public const TAXONOMY = 'hks_tour_type';

	/**
	 * Register the taxonomy.
	 *
	 * @return void
	 */
	public static function register() {
		register_taxonomy(
			self::TAXONOMY,
			array( Tour::POST_TYPE ),
			array(
				'labels'             => self::labels(),
				'description'        => __( 'The package format, such as road safari, day excursion, staycation, or group package. Describe what the trip is, not whom a campaign targets.', 'hks-core' ),
				'public'             => false,
				'publicly_queryable' => false,
				'hierarchical'       => true,
				'show_ui'            => true,
				'show_admin_column'  => true,
				'show_in_nav_menus'  => false,
				'show_tagcloud'      => false,
				'show_in_quick_edit' => true,
				'show_in_rest'       => true,
				'rest_base'          => 'tour-types',
				'rest_namespace'     => 'wp/v2',
				'query_var'          => false,
				'rewrite'            => false,
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
			'name'                       => __( 'Tour Types', 'hks-core' ),
			'singular_name'              => __( 'Tour Type', 'hks-core' ),
			'menu_name'                  => __( 'Tour Types', 'hks-core' ),
			'all_items'                  => __( 'All Tour Types', 'hks-core' ),
			'edit_item'                  => __( 'Edit Tour Type', 'hks-core' ),
			'view_item'                  => __( 'View Tour Type', 'hks-core' ),
			'update_item'                => __( 'Update Tour Type', 'hks-core' ),
			'add_new_item'               => __( 'Add New Tour Type', 'hks-core' ),
			'new_item_name'              => __( 'New Tour Type Name', 'hks-core' ),
			'parent_item'                => __( 'Parent Tour Type', 'hks-core' ),
			'parent_item_colon'          => __( 'Parent Tour Type:', 'hks-core' ),
			'search_items'               => __( 'Search Tour Types', 'hks-core' ),
			'popular_items'              => __( 'Popular Tour Types', 'hks-core' ),
			'separate_items_with_commas' => __( 'Separate tour types with commas', 'hks-core' ),
			'add_or_remove_items'        => __( 'Add or remove tour types', 'hks-core' ),
			'choose_from_most_used'      => __( 'Choose from the most-used tour types', 'hks-core' ),
			'not_found'                  => __( 'No tour types found.', 'hks-core' ),
			'no_terms'                   => __( 'No tour types', 'hks-core' ),
			'filter_by_item'             => __( 'Filter by tour type', 'hks-core' ),
			'items_list_navigation'      => __( 'Tour Types list navigation', 'hks-core' ),
			'items_list'                 => __( 'Tour Types list', 'hks-core' ),
			'back_to_items'              => __( '&larr; Back to Tour Types', 'hks-core' ),
			'item_link'                  => __( 'Tour Type Link', 'hks-core' ),
			'item_link_description'      => __( 'A link to a tour type.', 'hks-core' ),
		);
	}
}
