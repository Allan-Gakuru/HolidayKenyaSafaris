<?php
/**
 * FAQ post type.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Registers reusable, structured FAQ records for editorial use.
 */
final class Faq {

	/**
	 * WordPress post type name.
	 *
	 * @var string
	 */
	public const POST_TYPE = 'hks_faq';

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
				'description'         => __( 'Reusable questions and approved answers selected by Tours and Campaigns. Keep the answer in the structured FAQ fields.', 'hks-core' ),
				'public'              => false,
				'hierarchical'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => 'edit.php?post_type=hks_tour',
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'show_in_rest'        => false,
				'rest_base'           => 'faqs',
				'rest_namespace'      => 'wp/v2',
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'supports'            => array( 'title', 'revisions', 'custom-fields' ),
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
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
			'name'                     => __( 'FAQs', 'hks-core' ),
			'singular_name'            => __( 'FAQ', 'hks-core' ),
			'menu_name'                => __( 'FAQs', 'hks-core' ),
			'name_admin_bar'           => __( 'FAQ', 'hks-core' ),
			'add_new'                  => __( 'Add FAQ', 'hks-core' ),
			'add_new_item'             => __( 'Add New FAQ', 'hks-core' ),
			'new_item'                 => __( 'New FAQ', 'hks-core' ),
			'edit_item'                => __( 'Edit FAQ', 'hks-core' ),
			'view_item'                => __( 'View FAQ', 'hks-core' ),
			'view_items'               => __( 'View FAQs', 'hks-core' ),
			'all_items'                => __( 'All FAQs', 'hks-core' ),
			'search_items'             => __( 'Search FAQs', 'hks-core' ),
			'parent_item_colon'        => __( 'Parent FAQ:', 'hks-core' ),
			'not_found'                => __( 'No FAQs found.', 'hks-core' ),
			'not_found_in_trash'       => __( 'No FAQs found in Trash.', 'hks-core' ),
			'archives'                 => __( 'FAQ Archives', 'hks-core' ),
			'attributes'               => __( 'FAQ Attributes', 'hks-core' ),
			'insert_into_item'         => __( 'Insert into FAQ', 'hks-core' ),
			'uploaded_to_this_item'    => __( 'Uploaded to this FAQ', 'hks-core' ),
			'filter_items_list'        => __( 'Filter FAQs list', 'hks-core' ),
			'items_list_navigation'    => __( 'FAQs list navigation', 'hks-core' ),
			'items_list'               => __( 'FAQs list', 'hks-core' ),
			'item_published'           => __( 'FAQ published.', 'hks-core' ),
			'item_published_privately' => __( 'FAQ published privately.', 'hks-core' ),
			'item_reverted_to_draft'   => __( 'FAQ reverted to draft.', 'hks-core' ),
			'item_scheduled'           => __( 'FAQ scheduled.', 'hks-core' ),
			'item_updated'             => __( 'FAQ updated.', 'hks-core' ),
			'item_link'                => __( 'FAQ Link', 'hks-core' ),
			'item_link_description'    => __( 'A link to an FAQ record.', 'hks-core' ),
		);
	}
}
