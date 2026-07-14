<?php
/**
 * Travel Style taxonomy.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Content\Taxonomies;

use HolidayKenyaSafaris\Core\Content\PostTypes\Tour;

defined( 'ABSPATH' ) || exit;

/**
 * Registers practical travel-style classifications for canonical tours.
 */
final class TravelStyle {

	/**
	 * WordPress taxonomy name.
	 *
	 * @var string
	 */
	public const TAXONOMY = 'hks_travel_style';

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
				'description'        => __( 'Practical ways to experience a tour, such as private, group joining, resident package, family friendly, short break, or active.', 'hks-core' ),
				'public'             => false,
				'publicly_queryable' => false,
				'hierarchical'       => true,
				'show_ui'            => true,
				'show_admin_column'  => true,
				'show_in_nav_menus'  => false,
				'show_tagcloud'      => false,
				'show_in_quick_edit' => true,
				'show_in_rest'       => true,
				'rest_base'          => 'travel-styles',
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
			'name'                       => __( 'Travel Styles', 'hks-core' ),
			'singular_name'              => __( 'Travel Style', 'hks-core' ),
			'menu_name'                  => __( 'Travel Styles', 'hks-core' ),
			'all_items'                  => __( 'All Travel Styles', 'hks-core' ),
			'edit_item'                  => __( 'Edit Travel Style', 'hks-core' ),
			'view_item'                  => __( 'View Travel Style', 'hks-core' ),
			'update_item'                => __( 'Update Travel Style', 'hks-core' ),
			'add_new_item'               => __( 'Add New Travel Style', 'hks-core' ),
			'new_item_name'              => __( 'New Travel Style Name', 'hks-core' ),
			'parent_item'                => __( 'Parent Travel Style', 'hks-core' ),
			'parent_item_colon'          => __( 'Parent Travel Style:', 'hks-core' ),
			'search_items'               => __( 'Search Travel Styles', 'hks-core' ),
			'popular_items'              => __( 'Popular Travel Styles', 'hks-core' ),
			'separate_items_with_commas' => __( 'Separate travel styles with commas', 'hks-core' ),
			'add_or_remove_items'        => __( 'Add or remove travel styles', 'hks-core' ),
			'choose_from_most_used'      => __( 'Choose from the most-used travel styles', 'hks-core' ),
			'not_found'                  => __( 'No travel styles found.', 'hks-core' ),
			'no_terms'                   => __( 'No travel styles', 'hks-core' ),
			'filter_by_item'             => __( 'Filter by travel style', 'hks-core' ),
			'items_list_navigation'      => __( 'Travel Styles list navigation', 'hks-core' ),
			'items_list'                 => __( 'Travel Styles list', 'hks-core' ),
			'back_to_items'              => __( '&larr; Back to Travel Styles', 'hks-core' ),
			'item_link'                  => __( 'Travel Style Link', 'hks-core' ),
			'item_link_description'      => __( 'A link to a travel style.', 'hks-core' ),
		);
	}
}
