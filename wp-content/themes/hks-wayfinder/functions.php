<?php
/**
 * Theme setup and asset loading for HKS Wayfinder.
 *
 * @package HKS_Wayfinder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
