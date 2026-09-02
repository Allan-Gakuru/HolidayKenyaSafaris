<?php
/**
 * Official brand identity for browser icons and shared-link previews.
 *
 * @package HKS_Wayfinder
 */

defined( 'ABSPATH' ) || exit;

/**
 * Replace a configured legacy Site Icon without changing the media library.
 *
 * @param string $url  Configured icon URL.
 * @param int    $size Requested size.
 * @return string
 */
function hks_wayfinder_official_site_icon( string $url, int $size ): string {
	$filename = basename( (string) wp_parse_url( $url, PHP_URL_PATH ) );
	if ( ! preg_match( '/^(?:cropped-)?(?:hks-wayfinder-[a-z0-9-]+|social-avatar-512|site-icon-512|favicon-32|apple-touch-icon-180)(?:-\d+x\d+)?\.(?:svg|png|ico)$/i', $filename ) ) {
		return $url;
	}

	$asset = $size <= 32 ? 'favicon-32.png' : ( $size <= 180 ? 'apple-touch-icon-180.png' : 'site-icon-512.png' );
	return get_theme_file_uri( 'assets/images/brand/holiday-kenya-safaris-' . $asset );
}
add_filter( 'get_site_icon_url', 'hks_wayfinder_official_site_icon', 10, 2 );

/**
 * Give sharing clients an explicit raster image instead of an inferred favicon.
 *
 * All public page types use the approved logo with their own title and URL.
 * The image URL has a new filename so clients do not reuse the old logo bytes.
 *
 * @return void
 */
function hks_wayfinder_sharing_metadata(): void {
	if ( is_admin() || is_feed() || is_404() ) {
		return;
	}

	$image       = get_theme_file_uri( 'assets/images/brand/holiday-kenya-safaris-social-1200x630.png' );
	$title       = wp_get_document_title();
	$description = get_bloginfo( 'description' );
	$url         = get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) );

	if ( is_singular() ) {
		$url = get_permalink( get_queried_object_id() );
		if ( ! post_password_required() ) {
			$excerpt = (string) get_post_field( 'post_excerpt', get_queried_object_id() );
			if ( '' !== trim( $excerpt ) ) {
				$description = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $excerpt ) ), 35, '…' );
			}
		}
	}

	$description = wp_strip_all_tags( $description );
	?>
	<meta property="og:site_name" content="Holiday Kenya Safaris">
	<meta property="og:type" content="<?php echo esc_attr( is_singular( 'post' ) ? 'article' : 'website' ); ?>">
	<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
	<meta property="og:url" content="<?php echo esc_url( $url ); ?>">
	<meta property="og:image" content="<?php echo esc_url( $image ); ?>">
	<meta property="og:image:type" content="image/png">
	<meta property="og:image:width" content="1200">
	<meta property="og:image:height" content="630">
	<meta property="og:image:alt" content="Holiday Kenya Safaris official logo">
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
	<meta name="twitter:image" content="<?php echo esc_url( $image ); ?>">
	<meta name="twitter:image:alt" content="Holiday Kenya Safaris official logo">
	<?php if ( '' !== trim( $description ) ) : ?>
		<meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
		<meta name="twitter:description" content="<?php echo esc_attr( $description ); ?>">
	<?php endif; ?>
	<?php
}
add_action( 'wp_head', 'hks_wayfinder_sharing_metadata', 5 );
