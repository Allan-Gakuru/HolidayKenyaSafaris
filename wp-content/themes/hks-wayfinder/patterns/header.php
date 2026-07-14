<?php
/**
 * Title: Wayfinder header
 * Slug: hks-wayfinder/header
 * Categories: header
 * Block Types: core/template-part/header
 * Inserter: no
 *
 * @package HKS_Wayfinder
 */

defined( 'ABSPATH' ) || exit;

$home_url = home_url( '/' );
$logo_url = get_theme_file_uri( 'assets/images/brand/hks-wayfinder-horizontal-primary.svg' );
?>
<!-- wp:group {"align":"full","className":"hks-site-header","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull hks-site-header has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20)"><!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide"><!-- wp:html -->
<a class="hks-brand-link" href="<?php echo esc_url( $home_url ); ?>"><img src="<?php echo esc_url( $logo_url ); ?>" width="720" height="256" alt="<?php echo esc_attr__( 'Holiday Kenya Safaris', 'hks-wayfinder' ); ?>"></a>
<!-- /wp:html -->

<!-- wp:navigation {"overlayMenu":"mobile","layout":{"type":"flex","justifyContent":"right"}} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
