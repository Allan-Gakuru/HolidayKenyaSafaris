<?php
/**
 * Private inquiry recovery records.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Registers qualified quote inquiries captured before WhatsApp opens.
 */
final class Inquiry {

	/**
	 * Post type name.
	 */
	public const POST_TYPE = 'hks_inquiry';

	/**
	 * Register the private post type.
	 *
	 * Inquiry data is never queryable, searchable, exported, or exposed through
	 * REST. Only administrators with manage_options may access its screens.
	 *
	 * @return void
	 */
	public static function register() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => self::labels(),
				'description'         => __( 'Private quote-request recovery records captured with visitor consent before WhatsApp opens.', 'hks-core' ),
				'public'              => false,
				'hierarchical'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => 'edit.php?post_type=hks_tour',
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'show_in_rest'        => false,
				'menu_icon'           => 'dashicons-format-chat',
				'capability_type'     => 'hks_inquiry',
				'map_meta_cap'        => false,
				'capabilities'        => self::capabilities(),
				'supports'            => array(),
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'can_export'          => false,
				'delete_with_user'    => false,
			)
		);
	}

	/**
	 * Restrict every inquiry operation to site administrators.
	 *
	 * @return array<string, string>
	 */
	private static function capabilities() {
		return array(
			'edit_post'              => 'manage_options',
			'read_post'              => 'manage_options',
			'delete_post'            => 'manage_options',
			'edit_posts'             => 'manage_options',
			'edit_others_posts'      => 'manage_options',
			'publish_posts'          => 'do_not_allow',
			'read_private_posts'     => 'manage_options',
			'delete_posts'           => 'manage_options',
			'delete_private_posts'   => 'manage_options',
			'delete_published_posts' => 'manage_options',
			'delete_others_posts'    => 'manage_options',
			'edit_private_posts'     => 'manage_options',
			'edit_published_posts'   => 'manage_options',
			'create_posts'           => 'do_not_allow',
		);
	}

	/**
	 * Admin labels.
	 *
	 * @return array<string, string>
	 */
	private static function labels() {
		return array(
			'name'               => __( 'Quote inquiries', 'hks-core' ),
			'singular_name'      => __( 'Quote inquiry', 'hks-core' ),
			'menu_name'          => __( 'Quote inquiries', 'hks-core' ),
			'all_items'          => __( 'Quote inquiries', 'hks-core' ),
			'edit_item'          => __( 'View quote inquiry', 'hks-core' ),
			'view_item'          => __( 'View quote inquiry', 'hks-core' ),
			'search_items'       => __( 'Search quote inquiries', 'hks-core' ),
			'not_found'          => __( 'No quote inquiries found.', 'hks-core' ),
			'not_found_in_trash' => __( 'No quote inquiries found in Trash.', 'hks-core' ),
		);
	}
}
