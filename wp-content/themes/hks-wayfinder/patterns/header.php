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

$home_url         = home_url( '/' );
$logo_url         = get_theme_file_uri( 'assets/images/brand/hks-wayfinder-horizontal-primary.svg' );
$tours_url        = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );
$destination_terms = function_exists( 'hks_wayfinder_populated_terms' ) ? hks_wayfinder_populated_terms( 'hks_destination', 8 ) : array();
$tour_type_terms   = function_exists( 'hks_wayfinder_populated_terms' ) ? hks_wayfinder_populated_terms( 'hks_tour_type', 8 ) : array();
$is_quote_context  = is_singular( array( 'hks_tour', 'hks_campaign' ) );
$menu_id           = wp_unique_id( 'hks-mobile-menu-' );
$about_url          = function_exists( 'hks_wayfinder_published_page_url' ) ? hks_wayfinder_published_page_url( 'about' ) : '';
$contact_url        = function_exists( 'hks_wayfinder_published_page_url' ) ? hks_wayfinder_published_page_url( 'contact' ) : '';
$group_url          = function_exists( 'hks_wayfinder_published_page_url' ) ? hks_wayfinder_published_page_url( 'group-travel' ) : '';
$group_url          = $group_url ?: $home_url . '#group-travel';
$safari_terms       = array();
$coast_terms        = array();

foreach ( $tour_type_terms as $term ) {
	if ( preg_match( '/coast|stay|beach|diani|mombasa|watamu|malindi|lamu|kilifi/i', $term->name . ' ' . $term->slug ) ) {
		$coast_terms[] = $term;
	} else {
		$safari_terms[] = $term;
	}
}

foreach ( $destination_terms as $term ) {
	if ( preg_match( '/coast|beach|diani|mombasa|watamu|malindi|lamu|kilifi/i', $term->name . ' ' . $term->slug ) ) {
		$coast_terms[] = $term;
	}
}

/**
 * Render a concise list of populated terms.
 *
 * @param WP_Term[] $terms Terms to render.
 * @return void
 */
$render_terms = static function ( array $terms ): void {
	foreach ( $terms as $term ) {
		$url = function_exists( 'hks_wayfinder_term_url' ) ? hks_wayfinder_term_url( $term ) : '';
		if ( '' === $url ) {
			continue;
		}
		?>
		<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $term->name ); ?><span aria-hidden="true"><?php echo esc_html( (string) $term->count ); ?></span></a></li>
		<?php
	}
};
?>
<!-- wp:html -->
<a class="hks-skip-link" href="#main-content"><?php echo esc_html__( 'Skip to content', 'hks-wayfinder' ); ?></a>
<div class="hks-site-header" data-hks-site-header>
	<div class="hks-utility">
		<div class="hks-shell hks-utility__inner">
			<span><?php esc_html_e( 'Operated by Ashford Tours & Travel', 'hks-wayfinder' ); ?></span>
			<span><?php esc_html_e( 'WhatsApp +254 722 742 799', 'hks-wayfinder' ); ?></span>
		</div>
	</div>

	<div class="hks-primary-header">
		<div class="hks-shell hks-primary-header__inner">
			<a class="hks-brand-link" href="<?php echo esc_url( $home_url ); ?>" aria-label="<?php echo esc_attr__( 'Holiday Kenya Safaris home', 'hks-wayfinder' ); ?>">
				<img src="<?php echo esc_url( $logo_url ); ?>" width="720" height="256" alt="<?php echo esc_attr__( 'Holiday Kenya Safaris', 'hks-wayfinder' ); ?>">
			</a>

			<nav class="hks-primary-nav" aria-label="<?php echo esc_attr__( 'Primary navigation', 'hks-wayfinder' ); ?>">
				<a href="<?php echo esc_url( $home_url ); ?>"><?php esc_html_e( 'Home', 'hks-wayfinder' ); ?></a>

				<?php if ( $safari_terms ) : ?>
					<details class="hks-nav-menu" data-hks-nav-menu>
						<summary><?php esc_html_e( 'Safaris', 'hks-wayfinder' ); ?></summary>
						<div class="hks-nav-menu__panel">
							<p><?php esc_html_e( 'Browse safaris and local trips', 'hks-wayfinder' ); ?></p>
							<ul><?php $render_terms( $safari_terms ); ?></ul>
							<a class="hks-nav-menu__all" href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'See every tour', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a>
						</div>
					</details>
				<?php else : ?>
					<a href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'Tours', 'hks-wayfinder' ); ?></a>
				<?php endif; ?>

				<?php if ( $coast_terms ) : ?>
					<details class="hks-nav-menu" data-hks-nav-menu>
						<summary><?php esc_html_e( 'Coast & Stays', 'hks-wayfinder' ); ?></summary>
						<div class="hks-nav-menu__panel">
							<p><?php esc_html_e( 'Coast trips and staycations', 'hks-wayfinder' ); ?></p>
							<ul><?php $render_terms( $coast_terms ); ?></ul>
						</div>
					</details>
				<?php endif; ?>

				<?php if ( $destination_terms ) : ?>
					<details class="hks-nav-menu" data-hks-nav-menu>
						<summary><?php esc_html_e( 'Destinations', 'hks-wayfinder' ); ?></summary>
						<div class="hks-nav-menu__panel hks-nav-menu__panel--wide">
							<p><?php esc_html_e( 'Choose where to go', 'hks-wayfinder' ); ?></p>
							<ul><?php $render_terms( $destination_terms ); ?></ul>
							<a class="hks-nav-menu__all" href="<?php echo esc_url( $home_url . '#destinations' ); ?>"><?php esc_html_e( 'View all destinations', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a>
						</div>
					</details>
				<?php endif; ?>

				<a href="<?php echo esc_url( $group_url ); ?>"><?php esc_html_e( 'Group Travel', 'hks-wayfinder' ); ?></a>
				<?php if ( $about_url ) : ?><a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'About', 'hks-wayfinder' ); ?></a><?php endif; ?>
				<?php if ( $contact_url ) : ?><a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contact', 'hks-wayfinder' ); ?></a><?php endif; ?>
			</nav>

			<div class="hks-header-actions">
				<?php if ( $is_quote_context ) : ?>
					<button class="hks-button hks-button--quote" type="button" data-hks-quote-proxy><?php esc_html_e( 'Request quote on WhatsApp', 'hks-wayfinder' ); ?></button>
				<?php else : ?>
					<a class="hks-button hks-button--quote" href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'Request quote on WhatsApp', 'hks-wayfinder' ); ?></a>
				<?php endif; ?>
				<button class="hks-menu-toggle" type="button" data-hks-menu-open aria-controls="<?php echo esc_attr( $menu_id ); ?>" aria-expanded="false">
					<span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Open menu', 'hks-wayfinder' ); ?></span>
				</button>
			</div>
		</div>
	</div>

	<dialog class="hks-mobile-menu" id="<?php echo esc_attr( $menu_id ); ?>" data-hks-mobile-menu aria-label="<?php echo esc_attr__( 'Mobile navigation', 'hks-wayfinder' ); ?>">
		<div class="hks-mobile-menu__header">
			<a class="hks-brand-link" href="<?php echo esc_url( $home_url ); ?>"><img src="<?php echo esc_url( $logo_url ); ?>" width="720" height="256" alt="<?php echo esc_attr__( 'Holiday Kenya Safaris', 'hks-wayfinder' ); ?>"></a>
			<button type="button" data-hks-menu-close aria-label="<?php echo esc_attr__( 'Close menu', 'hks-wayfinder' ); ?>"><span aria-hidden="true">×</span></button>
		</div>
		<nav class="hks-mobile-menu__nav" aria-label="<?php echo esc_attr__( 'Mobile primary navigation', 'hks-wayfinder' ); ?>">
			<a href="<?php echo esc_url( $home_url ); ?>"><?php esc_html_e( 'Home', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a>
			<?php if ( $safari_terms ) : ?>
				<details><summary><?php esc_html_e( 'Safaris', 'hks-wayfinder' ); ?></summary><ul><?php $render_terms( $safari_terms ); ?><li><a href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'All tours', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a></li></ul></details>
			<?php else : ?>
				<a href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'Tours', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a>
			<?php endif; ?>
			<?php if ( $coast_terms ) : ?>
				<details><summary><?php esc_html_e( 'Coast & Stays', 'hks-wayfinder' ); ?></summary><ul><?php $render_terms( $coast_terms ); ?></ul></details>
			<?php endif; ?>
			<?php if ( $destination_terms ) : ?>
				<details><summary><?php esc_html_e( 'Destinations', 'hks-wayfinder' ); ?></summary><ul><?php $render_terms( $destination_terms ); ?></ul></details>
			<?php endif; ?>
			<a href="<?php echo esc_url( $group_url ); ?>"><?php esc_html_e( 'Group Travel', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a>
			<?php if ( $about_url ) : ?><a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'About', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a><?php endif; ?>
			<?php if ( $contact_url ) : ?><a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contact', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a><?php endif; ?>
		</nav>
		<div class="hks-mobile-menu__footer">
			<?php if ( $is_quote_context ) : ?>
				<button class="hks-button hks-button--quote" type="button" data-hks-quote-proxy><?php esc_html_e( 'Request quote on WhatsApp', 'hks-wayfinder' ); ?></button>
			<?php else : ?>
				<a class="hks-button hks-button--quote" href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'Request quote on WhatsApp', 'hks-wayfinder' ); ?></a>
			<?php endif; ?>
			<span><?php esc_html_e( 'WhatsApp +254 722 742 799', 'hks-wayfinder' ); ?></span>
		</div>
	</dialog>
</div>
<!-- /wp:html -->
