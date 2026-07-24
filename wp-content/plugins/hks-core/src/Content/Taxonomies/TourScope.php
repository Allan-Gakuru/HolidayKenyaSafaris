<?php
/**
 * Tour Scope taxonomy.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Content\Taxonomies;

use HolidayKenyaSafaris\Core\Content\PostTypes\Tour;

defined( 'ABSPATH' ) || exit;

/**
 * Separates the Kenya and international catalogue families.
 */
final class TourScope {

	/** WordPress taxonomy name. */
	public const TAXONOMY = 'hks_tour_scope';

	/** Register the taxonomy. */
	public static function register() {
		register_taxonomy(
			self::TAXONOMY,
			array( Tour::POST_TYPE ),
			array(
				'labels'             => self::labels(),
				'description'        => __( 'Separates Kenya Tours from International Tours. Use Destination for the actual country, city, park, coast, or region.', 'hks-core' ),
				'public'             => true,
				'publicly_queryable' => true,
				'hierarchical'       => true,
				'show_ui'            => true,
				'show_admin_column'  => true,
				'show_in_nav_menus'  => true,
				'show_tagcloud'      => false,
				'show_in_quick_edit' => true,
				'show_in_rest'       => true,
				'rest_base'          => 'tour-scopes',
				'rest_namespace'     => 'wp/v2',
				'query_var'          => true,
				'rewrite'            => array(
					'slug'         => 'tour-scope',
					'with_front'   => false,
					'hierarchical' => true,
				),
			)
		);
	}

	/** Return editor and administration labels. */
	private static function labels() {
		return array(
			'name'                       => __( 'Tour Scopes', 'hks-core' ),
			'singular_name'              => __( 'Tour Scope', 'hks-core' ),
			'menu_name'                  => __( 'Tour Scopes', 'hks-core' ),
			'all_items'                  => __( 'All Tour Scopes', 'hks-core' ),
			'edit_item'                  => __( 'Edit Tour Scope', 'hks-core' ),
			'view_item'                  => __( 'View Tour Scope', 'hks-core' ),
			'update_item'                => __( 'Update Tour Scope', 'hks-core' ),
			'add_new_item'               => __( 'Add New Tour Scope', 'hks-core' ),
			'new_item_name'              => __( 'New Tour Scope Name', 'hks-core' ),
			'parent_item'                => __( 'Parent Tour Scope', 'hks-core' ),
			'parent_item_colon'          => __( 'Parent Tour Scope:', 'hks-core' ),
			'search_items'               => __( 'Search Tour Scopes', 'hks-core' ),
			'not_found'                  => __( 'No Tour Scopes found.', 'hks-core' ),
			'no_terms'                   => __( 'No Tour Scope', 'hks-core' ),
			'filter_by_item'             => __( 'Filter by Tour Scope', 'hks-core' ),
			'items_list_navigation'      => __( 'Tour Scopes list navigation', 'hks-core' ),
			'items_list'                 => __( 'Tour Scopes list', 'hks-core' ),
			'back_to_items'              => __( '&larr; Back to Tour Scopes', 'hks-core' ),
			'item_link'                  => __( 'Tour Scope Link', 'hks-core' ),
			'item_link_description'      => __( 'A link to a Tour Scope.', 'hks-core' ),
			'separate_items_with_commas' => __( 'Separate Tour Scopes with commas', 'hks-core' ),
			'add_or_remove_items'        => __( 'Add or remove Tour Scopes', 'hks-core' ),
			'choose_from_most_used'      => __( 'Choose from the most-used Tour Scopes', 'hks-core' ),
		);
	}
}
