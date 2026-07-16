<?php
/**
 * Tour post type.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Registers canonical tour packages.
 */
final class Tour {

	/**
	 * WordPress post type name.
	 *
	 * @var string
	 */
	public const POST_TYPE = 'hks_tour';

	/**
	 * Register the post type.
	 *
	 * @return void
	 */
	public static function register() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => self::labels(),
				'description'         => __( 'Canonical local tour packages. Create each product once and reuse its facts across catalogues, destinations, and campaigns.', 'hks-core' ),
				'public'              => true,
				'hierarchical'        => false,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'show_in_rest'        => true,
				'rest_base'           => 'tours',
				'rest_namespace'      => 'wp/v2',
				'menu_position'       => 20,
				'menu_icon'           => 'dashicons-location-alt',
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
				'template'            => array(
					array(
						'core/paragraph',
						array(
				'placeholder' => __( 'Write a concise, source-backed Tour overview for local Kenyan travelers. Keep itinerary, inclusions, and logistics in their structured fields. Tours do not display prices.', 'hks-core' ),
						),
					),
				),
				'template_lock'       => 'all',
				'has_archive'         => 'tours',
				'rewrite'             => array(
					'slug'       => 'tours',
					'with_front' => false,
					'feeds'      => true,
					'pages'      => true,
				),
				'query_var'           => true,
				'can_export'          => true,
				'delete_with_user'    => false,
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
			'name'                     => __( 'Tours', 'hks-core' ),
			'singular_name'            => __( 'Tour', 'hks-core' ),
			'menu_name'                => __( 'Tours', 'hks-core' ),
			'name_admin_bar'           => __( 'Tour', 'hks-core' ),
			'add_new'                  => __( 'Add Tour', 'hks-core' ),
			'add_new_item'             => __( 'Add New Tour', 'hks-core' ),
			'new_item'                 => __( 'New Tour', 'hks-core' ),
			'edit_item'                => __( 'Edit Tour', 'hks-core' ),
			'view_item'                => __( 'View Tour', 'hks-core' ),
			'view_items'               => __( 'View Tours', 'hks-core' ),
			'all_items'                => __( 'All Tours', 'hks-core' ),
			'search_items'             => __( 'Search Tours', 'hks-core' ),
			'parent_item_colon'        => __( 'Parent Tour:', 'hks-core' ),
			'not_found'                => __( 'No tours found.', 'hks-core' ),
			'not_found_in_trash'       => __( 'No tours found in Trash.', 'hks-core' ),
			'archives'                 => __( 'Tour Archives', 'hks-core' ),
			'attributes'               => __( 'Tour Attributes', 'hks-core' ),
			'featured_image'           => __( 'Tour Hero Image', 'hks-core' ),
			'set_featured_image'       => __( 'Set tour hero image', 'hks-core' ),
			'remove_featured_image'    => __( 'Remove tour hero image', 'hks-core' ),
			'use_featured_image'       => __( 'Use as tour hero image', 'hks-core' ),
			'insert_into_item'         => __( 'Insert into tour', 'hks-core' ),
			'uploaded_to_this_item'    => __( 'Uploaded to this tour', 'hks-core' ),
			'filter_items_list'        => __( 'Filter tours list', 'hks-core' ),
			'items_list_navigation'    => __( 'Tours list navigation', 'hks-core' ),
			'items_list'               => __( 'Tours list', 'hks-core' ),
			'item_published'           => __( 'Tour published.', 'hks-core' ),
			'item_published_privately' => __( 'Tour published privately.', 'hks-core' ),
			'item_reverted_to_draft'   => __( 'Tour reverted to draft.', 'hks-core' ),
			'item_scheduled'           => __( 'Tour scheduled.', 'hks-core' ),
			'item_updated'             => __( 'Tour updated.', 'hks-core' ),
			'item_link'                => __( 'Tour Link', 'hks-core' ),
			'item_link_description'    => __( 'A link to a tour.', 'hks-core' ),
		);
	}
}
