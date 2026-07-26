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
$logo_url         = get_theme_file_uri( 'assets/images/brand/holiday-kenya-safaris-logo.svg' );
$tours_url        = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );
$destination_terms = function_exists( 'hks_wayfinder_populated_terms' ) ? hks_wayfinder_populated_terms( 'hks_destination', 8 ) : array();
$tour_type_terms   = function_exists( 'hks_wayfinder_populated_terms' ) ? hks_wayfinder_populated_terms( 'hks_tour_type', 8 ) : array();
$scope_terms       = function_exists( 'hks_wayfinder_populated_terms' ) ? hks_wayfinder_populated_terms( 'hks_tour_scope', 4 ) : array();
$is_quote_context  = is_singular( array( 'hks_tour', 'hks_campaign' ) );
$menu_id           = wp_unique_id( 'hks-mobile-menu-' );
$about_url          = function_exists( 'hks_wayfinder_published_page_url' ) ? hks_wayfinder_published_page_url( 'about' ) : '';
$contact_url        = function_exists( 'hks_wayfinder_published_page_url' ) ? hks_wayfinder_published_page_url( 'contact' ) : '';
$group_url          = function_exists( 'hks_wayfinder_published_page_url' ) ? hks_wayfinder_published_page_url( 'group-travel' ) : '';
$group_url          = $group_url ?: home_url( '/group-travel/' );
$public_email       = 'info@holidaykenyasafaris.ke';
$instagram_url      = 'https://www.instagram.com/holidaykenyasafaris/';
$facebook_url       = 'https://www.facebook.com/people/Holiday-Kenya-Safaris/61591508593846/';
$whatsapp_number    = '254712965131';
$whatsapp_message   = __( "Hi Holiday Kenya Safaris, I'd like help choosing and planning a trip.", 'hks-wayfinder' );
$safari_terms       = array();
$coast_terms        = array();
$kenya_scope        = null;
$international_scope = null;
$kenya_destinations = array();
$international_destinations = array();

foreach ( $scope_terms as $scope_term ) {
	if ( 'kenya-tours' === $scope_term->slug ) {
		$kenya_scope = $scope_term;
	} elseif ( 'international-tours' === $scope_term->slug ) {
		$international_scope = $scope_term;
	}
}

if ( $kenya_scope instanceof WP_Term && function_exists( 'hks_wayfinder_destinations_for_scope' ) ) {
	$kenya_destinations = hks_wayfinder_destinations_for_scope( $kenya_scope, 8 );
}

if ( $international_scope instanceof WP_Term && function_exists( 'hks_wayfinder_destinations_for_scope' ) ) {
	$international_destinations = hks_wayfinder_destinations_for_scope( $international_scope, 8 );
}

if ( $is_quote_context ) {
	$whatsapp_message = sprintf(
		/* translators: 1: Tour or Campaign title, 2: canonical page URL. */
		__( "Hi Holiday Kenya Safaris, I'm interested in %1\$s. Please help me plan this trip: %2\$s", 'hks-wayfinder' ),
		wp_strip_all_tags( get_the_title() ),
		get_permalink()
	);
}

$whatsapp_url = sprintf(
	'https://wa.me/%1$s?text=%2$s',
	rawurlencode( $whatsapp_number ),
	rawurlencode( $whatsapp_message )
);

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
			<span class="hks-utility__operator">
				<svg class="hks-utility__icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"></circle><path d="m14.8 9.2-1.6 4-4 1.6 1.6-4 4-1.6Z"></path></svg>
				<?php esc_html_e( 'Operated by Ashford Tours & Travel', 'hks-wayfinder' ); ?>
			</span>
			<div class="hks-utility__contacts">
				<a class="hks-utility__contact" href="mailto:<?php echo esc_attr( $public_email ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Email Holiday Kenya Safaris at %s', 'hks-wayfinder' ), $public_email ) ); ?>">
					<svg class="hks-utility__icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="1.5"></rect><path d="m4 7 8 6 8-6"></path></svg>
					<span class="hks-utility__contact-text"><?php echo esc_html( $public_email ); ?></span>
				</a>
				<a class="hks-utility__contact hks-utility__whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__( 'Open a WhatsApp chat with Holiday Kenya Safaris at +254 712 965 131', 'hks-wayfinder' ); ?>">
					<svg class="hks-utility__icon hks-utility__icon--whatsapp" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-1.76-.88-2.91-1.57-4.07-3.56-.31-.53.31-.49.88-1.63.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49 1.88.81 2.62.88 3.56.74.57-.09 1.76-.72 2.01-1.41.25-.69.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35ZM12.05 21.8h-.01a9.9 9.9 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26C2.16 6.45 6.6 2 12.06 2a9.83 9.83 0 0 1 9.89 9.9c0 5.45-4.44 9.9-9.9 9.9Zm8.41-18.3A11.82 11.82 0 0 0 12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.9 11.9 0 0 0 5.69 1.45c6.55 0 11.89-5.34 11.89-11.89 0-3.18-1.23-6.16-3.48-8.41Z"></path></svg>
					<span class="hks-utility__contact-text">+254 712 965 131</span>
				</a>
			</div>
			<div class="hks-utility__social" aria-label="<?php echo esc_attr__( 'Follow Holiday Kenya Safaris', 'hks-wayfinder' ); ?>">
				<span class="hks-utility__social-label"><?php esc_html_e( 'Follow us:', 'hks-wayfinder' ); ?></span>
				<a href="<?php echo esc_url( $facebook_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__( 'Holiday Kenya Safaris on Facebook', 'hks-wayfinder' ); ?>">
					<svg class="hks-utility__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8h3V4.5c-.5-.1-2.2-.2-3.4-.2-3.4 0-5.7 2.1-5.7 6v3.3H4v3.9h3.9V24h4.8v-6.5h3.6l.6-3.9h-4.2v-2.9C12.7 9.6 13 8 14 8Z"></path></svg>
				</a>
				<a href="<?php echo esc_url( $instagram_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__( 'Holiday Kenya Safaris on Instagram', 'hks-wayfinder' ); ?>">
					<svg class="hks-utility__icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle cx="17.4" cy="6.6" r="1"></circle></svg>
				</a>
			</div>
		</div>
	</div>

	<div class="hks-primary-header">
		<div class="hks-shell hks-primary-header__inner">
			<a class="hks-brand-link" href="<?php echo esc_url( $home_url ); ?>" aria-label="<?php echo esc_attr__( 'Holiday Kenya Safaris home', 'hks-wayfinder' ); ?>">
				<img src="<?php echo esc_url( $logo_url ); ?>" width="895" height="342" alt="<?php echo esc_attr__( 'Holiday Kenya Safaris', 'hks-wayfinder' ); ?>">
			</a>

			<nav class="hks-primary-nav" aria-label="<?php echo esc_attr__( 'Primary navigation', 'hks-wayfinder' ); ?>">
				<a href="<?php echo esc_url( $home_url ); ?>"><?php esc_html_e( 'Home', 'hks-wayfinder' ); ?></a>

				<?php if ( $kenya_scope instanceof WP_Term ) : ?>
					<details class="hks-nav-menu" data-hks-nav-menu>
						<summary><?php esc_html_e( 'Kenya Tours', 'hks-wayfinder' ); ?></summary>
						<div class="hks-nav-menu__panel">
							<p><?php esc_html_e( 'Explore Kenya by destination', 'hks-wayfinder' ); ?></p>
							<ul><?php $render_terms( $kenya_destinations ?: $safari_terms ); ?></ul>
							<a class="hks-nav-menu__all" href="<?php echo esc_url( hks_wayfinder_term_url( $kenya_scope ) ); ?>"><?php esc_html_e( 'See all Kenya tours', 'hks-wayfinder' ); ?><span aria-hidden="true">&rarr;</span></a>
						</div>
					</details>
				<?php elseif ( $safari_terms ) : ?>
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

				<?php if ( $international_scope instanceof WP_Term ) : ?>
					<details class="hks-nav-menu" data-hks-nav-menu>
						<summary><?php esc_html_e( 'International Tours', 'hks-wayfinder' ); ?></summary>
						<div class="hks-nav-menu__panel">
							<p><?php esc_html_e( 'Explore international destinations', 'hks-wayfinder' ); ?></p>
							<ul><?php $render_terms( $international_destinations ); ?></ul>
							<a class="hks-nav-menu__all" href="<?php echo esc_url( hks_wayfinder_term_url( $international_scope ) ); ?>"><?php esc_html_e( 'See all international tours', 'hks-wayfinder' ); ?><span aria-hidden="true">&rarr;</span></a>
						</div>
					</details>
				<?php endif; ?>

				<?php if ( $coast_terms && ! ( $kenya_scope instanceof WP_Term ) ) : ?>
					<details class="hks-nav-menu" data-hks-nav-menu>
						<summary><?php esc_html_e( 'Coast & Stays', 'hks-wayfinder' ); ?></summary>
						<div class="hks-nav-menu__panel">
							<p><?php esc_html_e( 'Coast trips and staycations', 'hks-wayfinder' ); ?></p>
							<ul><?php $render_terms( $coast_terms ); ?></ul>
						</div>
					</details>
				<?php endif; ?>

				<?php if ( $destination_terms && ! ( $kenya_scope instanceof WP_Term ) && ! ( $international_scope instanceof WP_Term ) ) : ?>
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
				<button class="hks-menu-toggle" type="button" data-hks-menu-open aria-controls="<?php echo esc_attr( $menu_id ); ?>" aria-expanded="false">
					<span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Open menu', 'hks-wayfinder' ); ?></span>
				</button>
			</div>
		</div>
	</div>

	<dialog class="hks-mobile-menu" id="<?php echo esc_attr( $menu_id ); ?>" data-hks-mobile-menu aria-label="<?php echo esc_attr__( 'Mobile navigation', 'hks-wayfinder' ); ?>">
		<div class="hks-mobile-menu__header">
			<a class="hks-brand-link" href="<?php echo esc_url( $home_url ); ?>"><img src="<?php echo esc_url( $logo_url ); ?>" width="895" height="342" alt="<?php echo esc_attr__( 'Holiday Kenya Safaris', 'hks-wayfinder' ); ?>"></a>
			<button type="button" data-hks-menu-close aria-label="<?php echo esc_attr__( 'Close menu', 'hks-wayfinder' ); ?>"><span aria-hidden="true">×</span></button>
		</div>
		<nav class="hks-mobile-menu__nav" aria-label="<?php echo esc_attr__( 'Mobile primary navigation', 'hks-wayfinder' ); ?>">
			<a href="<?php echo esc_url( $home_url ); ?>"><?php esc_html_e( 'Home', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a>
			<?php if ( $kenya_scope instanceof WP_Term ) : ?>
				<details><summary><?php esc_html_e( 'Kenya Tours', 'hks-wayfinder' ); ?></summary><ul><?php $render_terms( $kenya_destinations ?: $safari_terms ); ?><li><a href="<?php echo esc_url( hks_wayfinder_term_url( $kenya_scope ) ); ?>"><?php esc_html_e( 'All Kenya tours', 'hks-wayfinder' ); ?><span aria-hidden="true">&rarr;</span></a></li></ul></details>
			<?php elseif ( $safari_terms ) : ?>
				<details><summary><?php esc_html_e( 'Safaris', 'hks-wayfinder' ); ?></summary><ul><?php $render_terms( $safari_terms ); ?><li><a href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'All tours', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a></li></ul></details>
			<?php else : ?>
				<a href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'Tours', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a>
			<?php endif; ?>
			<?php if ( $international_scope instanceof WP_Term ) : ?>
				<details><summary><?php esc_html_e( 'International Tours', 'hks-wayfinder' ); ?></summary><ul><?php $render_terms( $international_destinations ); ?><li><a href="<?php echo esc_url( hks_wayfinder_term_url( $international_scope ) ); ?>"><?php esc_html_e( 'All international tours', 'hks-wayfinder' ); ?><span aria-hidden="true">&rarr;</span></a></li></ul></details>
			<?php endif; ?>
			<?php if ( $coast_terms && ! ( $kenya_scope instanceof WP_Term ) ) : ?>
				<details><summary><?php esc_html_e( 'Coast & Stays', 'hks-wayfinder' ); ?></summary><ul><?php $render_terms( $coast_terms ); ?></ul></details>
			<?php endif; ?>
			<?php if ( $destination_terms && ! ( $kenya_scope instanceof WP_Term ) && ! ( $international_scope instanceof WP_Term ) ) : ?>
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
			<nav class="hks-mobile-menu__social" aria-label="<?php echo esc_attr__( 'Follow Holiday Kenya Safaris', 'hks-wayfinder' ); ?>">
				<a href="<?php echo esc_url( $facebook_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__( 'Holiday Kenya Safaris on Facebook', 'hks-wayfinder' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8h3V4.5c-.5-.1-2.2-.2-3.4-.2-3.4 0-5.7 2.1-5.7 6v3.3H4v3.9h3.9V24h4.8v-6.5h3.6l.6-3.9h-4.2v-2.9C12.7 9.6 13 8 14 8Z"></path></svg>
					<span><?php esc_html_e( 'Facebook', 'hks-wayfinder' ); ?></span>
				</a>
				<a href="<?php echo esc_url( $instagram_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__( 'Holiday Kenya Safaris on Instagram', 'hks-wayfinder' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle cx="17.4" cy="6.6" r="1"></circle></svg>
					<span><?php esc_html_e( 'Instagram', 'hks-wayfinder' ); ?></span>
				</a>
			</nav>
			<a href="tel:+254712965131"><?php esc_html_e( 'Call or WhatsApp +254 712 965 131', 'hks-wayfinder' ); ?></a>
		</div>
	</dialog>
</div>
<!-- /wp:html -->
