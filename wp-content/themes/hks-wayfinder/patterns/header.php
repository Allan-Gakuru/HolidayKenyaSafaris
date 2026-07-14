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
$tours_url = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );
?>
<!-- wp:html -->
<a class="hks-skip-link" href="#main-content"><?php echo esc_html__( 'Skip to content', 'hks-wayfinder' ); ?></a>
<!-- /wp:html -->

<!-- wp:group {"align":"full","className":"hks-site-header","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull hks-site-header has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20)"><!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide"><!-- wp:html -->
<a class="hks-brand-link" href="<?php echo esc_url( $home_url ); ?>"><img src="<?php echo esc_url( $logo_url ); ?>" width="720" height="256" alt="<?php echo esc_attr__( 'Holiday Kenya Safaris', 'hks-wayfinder' ); ?>"></a>
<!-- /wp:html -->

<!-- wp:navigation {"overlayMenu":"mobile","layout":{"type":"flex","justifyContent":"right"}} -->
<!-- wp:navigation-link {"label":"<?php echo esc_attr__( 'Home', 'hks-wayfinder' ); ?>","url":"<?php echo esc_url( $home_url ); ?>","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"<?php echo esc_attr__( 'Tours', 'hks-wayfinder' ); ?>","url":"<?php echo esc_url( $tours_url ); ?>","kind":"custom","isTopLevelLink":true} /-->
<!-- /wp:navigation --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
