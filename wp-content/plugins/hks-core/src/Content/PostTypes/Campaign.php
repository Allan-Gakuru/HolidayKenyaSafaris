<?php
/**
 * Campaign post type.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Registers focused campaign landing-page variants.
 */
final class Campaign {

	/**
	 * WordPress post type name.
	 *
	 * @var string
	 */
	public const POST_TYPE = 'hks_campaign';

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
				'description'         => __( 'Focused landing-page messaging linked to a canonical Tour. Keep itinerary, inclusion, price, and other package facts on the linked Tour.', 'hks-core' ),
				'public'              => false,
				'hierarchical'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => 'edit.php?post_type=hks_tour',
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => true,
				'show_in_rest'        => true,
				'rest_base'           => 'campaigns',
				'rest_namespace'      => 'wp/v2',
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'supports'            => array( 'title', 'thumbnail', 'revisions' ),
				'has_archive'         => false,
				'rewrite'             => array(
					'slug'       => 'campaigns',
					'with_front' => false,
					'feeds'      => false,
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
			'name'                     => __( 'Campaigns', 'hks-core' ),
			'singular_name'            => __( 'Campaign', 'hks-core' ),
			'menu_name'                => __( 'Campaigns', 'hks-core' ),
			'name_admin_bar'           => __( 'Campaign', 'hks-core' ),
			'add_new'                  => __( 'Add Campaign', 'hks-core' ),
			'add_new_item'             => __( 'Add New Campaign', 'hks-core' ),
			'new_item'                 => __( 'New Campaign', 'hks-core' ),
			'edit_item'                => __( 'Edit Campaign', 'hks-core' ),
			'view_item'                => __( 'View Campaign', 'hks-core' ),
			'view_items'               => __( 'View Campaigns', 'hks-core' ),
			'all_items'                => __( 'All Campaigns', 'hks-core' ),
			'search_items'             => __( 'Search Campaigns', 'hks-core' ),
			'parent_item_colon'        => __( 'Parent Campaign:', 'hks-core' ),
			'not_found'                => __( 'No campaigns found.', 'hks-core' ),
			'not_found_in_trash'       => __( 'No campaigns found in Trash.', 'hks-core' ),
			'archives'                 => __( 'Campaign Archives', 'hks-core' ),
			'attributes'               => __( 'Campaign Attributes', 'hks-core' ),
			'featured_image'           => __( 'Campaign Hero Image', 'hks-core' ),
			'set_featured_image'       => __( 'Set campaign hero image', 'hks-core' ),
			'remove_featured_image'    => __( 'Remove campaign hero image', 'hks-core' ),
			'use_featured_image'       => __( 'Use as campaign hero image', 'hks-core' ),
			'insert_into_item'         => __( 'Insert into campaign', 'hks-core' ),
			'uploaded_to_this_item'    => __( 'Uploaded to this campaign', 'hks-core' ),
			'filter_items_list'        => __( 'Filter campaigns list', 'hks-core' ),
			'items_list_navigation'    => __( 'Campaigns list navigation', 'hks-core' ),
			'items_list'               => __( 'Campaigns list', 'hks-core' ),
			'item_published'           => __( 'Campaign published.', 'hks-core' ),
			'item_published_privately' => __( 'Campaign published privately.', 'hks-core' ),
			'item_reverted_to_draft'   => __( 'Campaign reverted to draft.', 'hks-core' ),
			'item_scheduled'           => __( 'Campaign scheduled.', 'hks-core' ),
			'item_updated'             => __( 'Campaign updated.', 'hks-core' ),
			'item_link'                => __( 'Campaign Link', 'hks-core' ),
			'item_link_description'    => __( 'A link to a campaign landing page.', 'hks-core' ),
		);
	}
}
