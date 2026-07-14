<?php
/**
 * Destination taxonomy.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Content\Taxonomies;

use HolidayKenyaSafaris\Core\Content\PostTypes\Tour;

defined( 'ABSPATH' ) || exit;

/**
 * Registers geographic destinations for canonical tours.
 */
final class Destination {

	/**
	 * WordPress taxonomy name.
	 *
	 * @var string
	 */
	public const TAXONOMY = 'hks_destination';

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
				'description'        => __( 'Geographic places used to group tours and build destination discovery pages. Add only destinations supported by approved tours or verified editorial content.', 'hks-core' ),
				'public'             => true,
				'publicly_queryable' => true,
				'hierarchical'       => true,
				'show_ui'            => true,
				'show_admin_column'  => true,
				'show_in_nav_menus'  => true,
				'show_tagcloud'      => false,
				'show_in_quick_edit' => true,
				'show_in_rest'       => true,
				'rest_base'          => 'destinations',
				'rest_namespace'     => 'wp/v2',
				'query_var'          => true,
				'rewrite'            => array(
					'slug'         => 'destinations',
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
			'name'                       => __( 'Destinations', 'hks-core' ),
			'singular_name'              => __( 'Destination', 'hks-core' ),
			'menu_name'                  => __( 'Destinations', 'hks-core' ),
			'all_items'                  => __( 'All Destinations', 'hks-core' ),
			'edit_item'                  => __( 'Edit Destination', 'hks-core' ),
			'view_item'                  => __( 'View Destination', 'hks-core' ),
			'update_item'                => __( 'Update Destination', 'hks-core' ),
			'add_new_item'               => __( 'Add New Destination', 'hks-core' ),
			'new_item_name'              => __( 'New Destination Name', 'hks-core' ),
			'parent_item'                => __( 'Parent Destination', 'hks-core' ),
			'parent_item_colon'          => __( 'Parent Destination:', 'hks-core' ),
			'search_items'               => __( 'Search Destinations', 'hks-core' ),
			'popular_items'              => __( 'Popular Destinations', 'hks-core' ),
			'separate_items_with_commas' => __( 'Separate destinations with commas', 'hks-core' ),
			'add_or_remove_items'        => __( 'Add or remove destinations', 'hks-core' ),
			'choose_from_most_used'      => __( 'Choose from the most-used destinations', 'hks-core' ),
			'not_found'                  => __( 'No destinations found.', 'hks-core' ),
			'no_terms'                   => __( 'No destinations', 'hks-core' ),
			'filter_by_item'             => __( 'Filter by destination', 'hks-core' ),
			'items_list_navigation'      => __( 'Destinations list navigation', 'hks-core' ),
			'items_list'                 => __( 'Destinations list', 'hks-core' ),
			'back_to_items'              => __( '&larr; Back to Destinations', 'hks-core' ),
			'item_link'                  => __( 'Destination Link', 'hks-core' ),
			'item_link_description'      => __( 'A link to a destination.', 'hks-core' ),
		);
	}
}
