<?php
/**
 * Travel Guide topic taxonomy.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Content\Taxonomies;

defined( 'ABSPATH' ) || exit;

/**
 * Registers editorial topics for native Travel Guide Posts.
 */
final class ArticleTopic {

	/** WordPress taxonomy name. */
	public const TAXONOMY = 'hks_article_topic';

	/** One-shot site option consumed after the taxonomy is registered. */
	public const SEED_OPTION = 'hks_core_seed_article_topics';

	/** Register the taxonomy. */
	public static function register() {
		register_taxonomy(
			self::TAXONOMY,
			array( 'post' ),
			array(
				'labels'             => self::labels(),
				'description'        => __( 'Editorial topics used to organise Holiday Kenya Safaris Travel Guides.', 'hks-core' ),
				'public'             => true,
				'publicly_queryable' => true,
				'hierarchical'       => true,
				'show_ui'            => true,
				'show_admin_column'  => true,
				'show_in_nav_menus'  => true,
				'show_tagcloud'      => false,
				'show_in_quick_edit' => true,
				'show_in_rest'       => true,
				'rest_base'          => 'travel-guide-topics',
				'rest_namespace'     => 'wp/v2',
				'query_var'          => true,
				'rewrite'            => array(
					'slug'         => 'travel-guides/topics',
					'with_front'   => false,
					'hierarchical' => true,
				),
			)
		);
	}

	/**
	 * Seed the approved initial topics without replacing editor changes.
	 *
	 * @return true|\WP_Error True on success, otherwise an insertion error.
	 */
	public static function seed_initial_terms() {
		$terms = array(
			'Destination Guides'         => 'destination-guides',
			'Planning & FAQs'            => 'planning-faqs',
			'Travel Inspiration'         => 'travel-inspiration',
			'Comparisons'                => 'comparisons',
			'Holiday Kenya Safaris News' => 'holiday-kenya-safaris-news',
		);

		foreach ( $terms as $name => $slug ) {
			if ( term_exists( $slug, self::TAXONOMY ) ) {
				continue;
			}

			$result = wp_insert_term( $name, self::TAXONOMY, array( 'slug' => $slug ) );

			if ( is_wp_error( $result ) && 'term_exists' !== $result->get_error_code() ) {
				return $result;
			}
		}

		return true;
	}

	/** Return editor and administration labels. */
	private static function labels() {
		return array(
			'name'                       => __( 'Travel Guide Topics', 'hks-core' ),
			'singular_name'              => __( 'Travel Guide Topic', 'hks-core' ),
			'menu_name'                 => __( 'Travel Guide Topics', 'hks-core' ),
			'all_items'                 => __( 'All Travel Guide Topics', 'hks-core' ),
			'edit_item'                 => __( 'Edit Travel Guide Topic', 'hks-core' ),
			'view_item'                 => __( 'View Travel Guide Topic', 'hks-core' ),
			'update_item'               => __( 'Update Travel Guide Topic', 'hks-core' ),
			'add_new_item'              => __( 'Add New Travel Guide Topic', 'hks-core' ),
			'new_item_name'             => __( 'New Travel Guide Topic Name', 'hks-core' ),
			'parent_item'               => __( 'Parent Travel Guide Topic', 'hks-core' ),
			'parent_item_colon'         => __( 'Parent Travel Guide Topic:', 'hks-core' ),
			'search_items'              => __( 'Search Travel Guide Topics', 'hks-core' ),
			'not_found'                 => __( 'No Travel Guide Topics found.', 'hks-core' ),
			'no_terms'                  => __( 'No Travel Guide Topics', 'hks-core' ),
			'filter_by_item'            => __( 'Filter by Travel Guide Topic', 'hks-core' ),
			'items_list_navigation'     => __( 'Travel Guide Topics list navigation', 'hks-core' ),
			'items_list'                => __( 'Travel Guide Topics list', 'hks-core' ),
			'back_to_items'             => __( '&larr; Back to Travel Guide Topics', 'hks-core' ),
			'item_link'                 => __( 'Travel Guide Topic Link', 'hks-core' ),
			'item_link_description'     => __( 'A link to a Travel Guide Topic.', 'hks-core' ),
			'separate_items_with_commas' => __( 'Separate Travel Guide Topics with commas', 'hks-core' ),
			'add_or_remove_items'       => __( 'Add or remove Travel Guide Topics', 'hks-core' ),
			'choose_from_most_used'     => __( 'Choose from the most-used Travel Guide Topics', 'hks-core' ),
		);
	}
}
