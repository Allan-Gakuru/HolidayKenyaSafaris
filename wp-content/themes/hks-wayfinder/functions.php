<?php
/**
 * Theme setup and asset loading for HKS Wayfinder.
 *
 * @package HKS_Wayfinder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_theme_file_path( 'inc/TourBlocks.php' );

/**
 * Register theme supports and editor styles.
 *
 * @return void
 */
function hks_wayfinder_setup(): void {
	load_theme_textdomain( 'hks-wayfinder', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'wp-block-styles' );

	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'hks_wayfinder_setup' );

/**
 * Enqueue the small structural stylesheet that complements theme.json.
 *
 * @return void
 */
function hks_wayfinder_enqueue_styles(): void {
	$stylesheet_path = get_stylesheet_directory() . '/style.css';
	$version         = is_readable( $stylesheet_path ) ? (string) filemtime( $stylesheet_path ) : wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'hks-wayfinder-style',
		get_stylesheet_uri(),
		array(),
		$version
	);
}
add_action( 'wp_enqueue_scripts', 'hks_wayfinder_enqueue_styles' );

/**
 * Load the small interaction layer for the navigation and canonical Tour UI.
 * All essential content remains server rendered when JavaScript is unavailable.
 *
 * @return void
 */
function hks_wayfinder_enqueue_scripts(): void {
	$navigation_path = get_theme_file_path( 'assets/js/navigation.js' );
	$navigation_uri  = get_theme_file_uri( 'assets/js/navigation.js' );

	wp_enqueue_script(
		'hks-wayfinder-navigation',
		$navigation_uri,
		array(),
		is_readable( $navigation_path ) ? (string) filemtime( $navigation_path ) : wp_get_theme()->get( 'Version' ),
		array( 'in_footer' => true, 'strategy' => 'defer' )
	);

	if ( is_singular( array( 'hks_tour', 'hks_campaign' ) ) ) {
		$tour_ui_path = get_theme_file_path( 'assets/js/tour-ui.js' );

		wp_enqueue_script(
			'hks-wayfinder-tour-ui',
			get_theme_file_uri( 'assets/js/tour-ui.js' ),
			array(),
			is_readable( $tour_ui_path ) ? (string) filemtime( $tour_ui_path ) : wp_get_theme()->get( 'Version' ),
			array( 'in_footer' => true, 'strategy' => 'defer' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'hks_wayfinder_enqueue_scripts' );

/**
 * Provide deployable favicon assets until an editor configures a Site Icon.
 *
 * WordPress owns the Site Icon when one has been selected in the dashboard, so
 * these links intentionally disappear as soon as that setting exists.
 *
 * @return void
 */
function hks_wayfinder_favicon_fallback(): void {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
		return;
	}

	$brand_path = 'assets/images/brand/';
	?>
	<link rel="icon" href="<?php echo esc_url( get_theme_file_uri( $brand_path . 'hks-wayfinder-favicon.svg' ) ); ?>" type="image/svg+xml">
	<link rel="icon" href="<?php echo esc_url( get_theme_file_uri( $brand_path . 'favicon-32.png' ) ); ?>" sizes="32x32" type="image/png">
	<link rel="icon" href="<?php echo esc_url( get_theme_file_uri( $brand_path . 'site-icon-512.png' ) ); ?>" sizes="512x512" type="image/png">
	<link rel="apple-touch-icon" href="<?php echo esc_url( get_theme_file_uri( $brand_path . 'apple-touch-icon-180.png' ) ); ?>" sizes="180x180">
	<?php
}
add_action( 'wp_head', 'hks_wayfinder_favicon_fallback', 2 );

add_action( 'init', array( \HKS_Wayfinder\TourBlocks::class, 'register' ), 20 );

/**
 * Respect Campaign noindex governance independently of SEO plugins.
 *
 * @param array<string, bool> $robots Existing directives.
 * @return array<string, bool>
 */
function hks_wayfinder_campaign_robots( array $robots ): array {
	if ( ! is_singular( 'hks_campaign' ) ) {
		return $robots;
	}

	$post_id = get_queried_object_id();
	$noindex = function_exists( 'get_field' ) ? get_field( 'hks_noindex', $post_id ) : get_post_meta( $post_id, 'hks_noindex', true );

	if ( $noindex ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = false;
	}

	return $robots;
}
add_filter( 'wp_robots', 'hks_wayfinder_campaign_robots' );

/**
 * Add Campaign navigation-mode classes for focused landing pages.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function hks_wayfinder_campaign_body_class( array $classes ): array {
	if ( is_singular( 'hks_campaign' ) ) {
		$post_id = get_queried_object_id();
		$mode    = function_exists( 'get_field' ) ? get_field( 'hks_navigation_mode', $post_id ) : get_post_meta( $post_id, 'hks_navigation_mode', true );
		$classes[] = 'hks-campaign-navigation-' . sanitize_html_class( $mode ?: 'campaign_minimal' );
	}

	return $classes;
}
add_filter( 'body_class', 'hks_wayfinder_campaign_body_class' );

/**
 * Return populated terms for public navigation and catalogue controls.
 *
 * @param string $taxonomy Taxonomy name.
 * @param int    $limit    Maximum terms to return. Zero returns all.
 * @return WP_Term[]
 */
function hks_wayfinder_populated_terms( string $taxonomy, int $limit = 0 ): array {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'number'     => max( 0, $limit ),
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Build a usable catalogue URL for public and editor-only Tour taxonomies.
 *
 * @param WP_Term $term Term object.
 * @return string
 */
function hks_wayfinder_term_url( WP_Term $term ): string {
	if ( 'hks_destination' === $term->taxonomy ) {
		$link = get_term_link( $term );

		return is_wp_error( $link ) ? '' : $link;
	}

	$archive = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );

	return add_query_arg( $term->taxonomy, $term->slug, $archive );
}

/**
 * Find a published page route without creating placeholder navigation.
 *
 * @param string $path Page path.
 * @return string
 */
function hks_wayfinder_published_page_url( string $path ): string {
	$page = get_page_by_path( $path, OBJECT, 'page' );

	return $page && 'publish' === $page->post_status ? get_permalink( $page ) : '';
}

/**
 * Apply allowlisted catalogue filters without changing dashboard queries.
 *
 * @param WP_Query $query Main query.
 * @return void
 */
function hks_wayfinder_filter_tour_archive( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'hks_tour' ) ) {
		return;
	}

	$tax_query = array();
	$filters   = array( 'hks_destination', 'hks_tour_type', 'hks_occasion', 'hks_travel_style' );

	foreach ( $filters as $taxonomy ) {
		$raw   = $_GET[ $taxonomy ] ?? '';
		$value = is_string( $raw ) ? sanitize_title( wp_unslash( $raw ) ) : '';

		if ( '' !== $value && term_exists( $value, $taxonomy ) ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $value,
			);
		}
	}

	if ( $tax_query ) {
		$query->set( 'tax_query', $tax_query );
	}

	$raw_sort = $_GET['hks_sort'] ?? '';
	$sort     = is_string( $raw_sort ) ? sanitize_key( wp_unslash( $raw_sort ) ) : 'recommended';

	if ( 'title' === $sort ) {
		$query->set( 'orderby', 'title' );
		$query->set( 'order', 'ASC' );
	} elseif ( 'newest' === $sort ) {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	} else {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
	}
}
add_action( 'pre_get_posts', 'hks_wayfinder_filter_tour_archive' );
