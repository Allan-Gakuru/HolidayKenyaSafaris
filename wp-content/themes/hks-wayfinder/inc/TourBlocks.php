<?php
/**
 * Fail-closed dynamic presentation blocks for HKS Tours and Campaigns.
 *
 * @package HKS_Wayfinder
 */

namespace HKS_Wayfinder;

defined( 'ABSPATH' ) || exit;

/**
 * Renders approved catalogue data while keeping Campaigns in conversion mode.
 */
final class TourBlocks {

	/**
	 * Internal confirmation sentinel that must never reach public markup.
	 */
	private const SENTINEL = 'CLIENT CONFIRMATION REQUIRED';

	/**
	 * Register each server-rendered theme block.
	 *
	 * @return void
	 */
	public static function register(): void {
		$blocks = array(
			'tour-hero'          => 'render_tour_hero',
			'tour-details'       => 'render_tour_details',
			'tour-card'          => 'render_tour_card',
			'destination-intro'  => 'render_destination_intro',
			'home-experience'    => 'render_home_experience',
			'catalogue-controls' => 'render_catalogue_controls',
		);

		foreach ( $blocks as $directory => $callback ) {
			register_block_type(
				get_theme_file_path( 'blocks/' . $directory ),
				array( 'render_callback' => array( self::class, $callback ) )
			);
		}
	}

	/**
	 * Render either the focused Campaign hero or canonical Tour title/gallery.
	 *
	 * @return string
	 */
	public static function render_tour_hero(): string {
		$context = self::tour_context();

		if ( ! $context ) {
			return '';
		}

		return $context['campaign_id'] ? self::render_campaign_hero( $context ) : self::render_canonical_hero( $context['tour_id'] );
	}

	/**
	 * Render the canonical Tour title band and approved gallery.
	 *
	 * @param int $tour_id Tour ID.
	 * @return string
	 */
	private static function render_canonical_hero( int $tour_id ): string {
		$title        = self::public_text( get_the_title( $tour_id ) );
		$route        = self::public_text( self::field( 'hks_route_summary', $tour_id ) );
		$destinations = self::term_names( $tour_id, 'hks_destination' );
		$images       = self::tour_images( $tour_id );
		$tours_url    = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );

		ob_start();
		?>
		<section class="hks-tour-lead">
			<div class="hks-title-band">
				<div class="hks-shell">
					<?php self::breadcrumbs( array( __( 'Tours', 'hks-wayfinder' ) => $tours_url, $title => '' ) ); ?>
					<h1><?php echo esc_html( $title ); ?></h1>
				</div>
			</div>
			<?php if ( $images ) : ?>
				<?php self::render_gallery( $images, $title ); ?>
			<?php endif; ?>
			<?php if ( $destinations || $route ) : ?>
				<div class="hks-shell"><p class="hks-tour-lead__route"><?php echo esc_html( implode( ' · ', array_filter( array( implode( ', ', $destinations ), $route ) ) ) ); ?></p></div>
			<?php endif; ?>
		</section>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Preserve the emotionally concentrated Campaign lead.
	 *
	 * @param array<string, int> $context Campaign context.
	 * @return string
	 */
	private static function render_campaign_hero( array $context ): string {
		$tour_id      = $context['tour_id'];
		$campaign_id  = $context['campaign_id'];
		$title        = self::public_text( self::field( 'hks_hero_headline', $campaign_id ) ) ?: self::public_text( get_the_title( $campaign_id ) );
		$introduction = self::public_html( self::field( 'hks_supporting_copy', $campaign_id ) ) ?: self::public_text( get_post_field( 'post_excerpt', $tour_id ) );
		$duration     = self::public_text( self::field( 'hks_duration_label', $tour_id ) );
		$route        = self::public_text( self::field( 'hks_route_summary', $tour_id ) );
		$price        = self::price_summary( $tour_id );
		$image_id     = get_post_thumbnail_id( $campaign_id );

		if ( ! self::media_allowed( $image_id ) ) {
			$image_id = get_post_thumbnail_id( $tour_id );
		}

		if ( ! self::media_allowed( $image_id ) ) {
			$image_id = 0;
		}

		$destinations = self::term_names( $tour_id, 'hks_destination' );

		ob_start();
		?>
		<section class="hks-campaign-hero<?php echo $image_id ? ' hks-campaign-hero--with-image' : ''; ?>">
			<div class="hks-campaign-hero__content">
				<p class="hks-kicker"><?php echo esc_html( $destinations ? $destinations[0] : get_the_title( $tour_id ) ); ?></p>
				<h1><?php echo esc_html( $title ); ?></h1>
				<?php if ( $introduction ) : ?><div class="hks-campaign-hero__intro"><?php echo wp_kses_post( wpautop( $introduction ) ); ?></div><?php endif; ?>
				<ul class="hks-fast-facts" aria-label="<?php esc_attr_e( 'Package summary', 'hks-wayfinder' ); ?>">
					<?php if ( $duration ) : ?><li><span><?php esc_html_e( 'Time', 'hks-wayfinder' ); ?></span><strong><?php echo esc_html( $duration ); ?></strong></li><?php endif; ?>
					<?php if ( $route ) : ?><li><span><?php esc_html_e( 'Route', 'hks-wayfinder' ); ?></span><strong><?php echo esc_html( $route ); ?></strong></li><?php endif; ?>
					<?php if ( $price ) : ?><li><span><?php esc_html_e( 'Rate', 'hks-wayfinder' ); ?></span><strong><?php echo esc_html( $price['label'] ); ?></strong></li><?php endif; ?>
				</ul>
			</div>
			<?php if ( $image_id ) : ?>
				<figure class="hks-campaign-hero__media"><?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'large', false, array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 800px) 100vw, 48vw' ) ) ); ?><?php self::render_credit( $image_id ); ?></figure>
			<?php endif; ?>
		</section>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render canonical Tour workspace or the existing Campaign detail flow.
	 *
	 * @return string
	 */
	public static function render_tour_details(): string {
		$context = self::tour_context();

		if ( ! $context ) {
			return '';
		}

		return $context['campaign_id'] ? self::render_campaign_details( $context ) : self::render_canonical_details( $context['tour_id'] );
	}

	/**
	 * Render the 68/32 canonical Tour workspace, sections and one quote form.
	 *
	 * @param int $tour_id Tour ID.
	 * @return string
	 */
	private static function render_canonical_details( int $tour_id ): string {
		$overview   = get_post_field( 'post_content', $tour_id );
		$itinerary  = self::rows( self::field( 'hks_itinerary', $tour_id ) );
		$inclusions = self::rows( self::field( 'hks_inclusions', $tour_id ) );
		$exclusions = self::rows( self::field( 'hks_exclusions', $tour_id ) );
		$price      = self::price_summary( $tour_id );
		$policies   = self::approved_policies( $tour_id );
		$faqs       = self::approved_faqs( array( 'tour_id' => $tour_id, 'campaign_id' => 0 ) );
		$facts      = self::tour_facts( $tour_id );
		$quote      = do_blocks( '<!-- wp:hks/quote-cta {"location":"tour_sidebar","label":"Request quote on WhatsApp"} /-->' );

		ob_start();
		?>
		<section class="hks-tour-workspace hks-shell" data-hks-tour-id="<?php echo esc_attr( (string) $tour_id ); ?>">
			<?php if ( $facts ) : ?>
				<dl class="hks-tour-facts" aria-label="<?php esc_attr_e( 'Tour facts', 'hks-wayfinder' ); ?>">
					<?php foreach ( $facts as $label => $value ) : ?><div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div><?php endforeach; ?>
				</dl>
			<?php endif; ?>

			<aside class="hks-tour-quote" aria-label="<?php esc_attr_e( 'Request a quote', 'hks-wayfinder' ); ?>">
				<div class="hks-tour-quote__panel" data-hks-primary-quote>
					<p class="hks-tour-quote__label"><?php esc_html_e( 'Plan this trip', 'hks-wayfinder' ); ?></p>
					<h2><?php echo esc_html( $price ? $price['label'] : __( 'Request current KSh rate', 'hks-wayfinder' ) ); ?></h2>
					<?php if ( $price ) : ?><p><?php echo esc_html( $price['status'] ); ?></p><?php endif; ?>
					<?php echo $quote; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted server-rendered block. ?>
					<p class="hks-tour-quote__note"><?php esc_html_e( 'Share your dates and group size, review the message, then choose whether to send it in WhatsApp.', 'hks-wayfinder' ); ?></p>
				</div>
			</aside>

			<div class="hks-tour-sections" data-hks-tour-sections>
					<div class="hks-tour-tabs" data-hks-tour-tabs aria-label="<?php esc_attr_e( 'Tour information', 'hks-wayfinder' ); ?>"></div>

					<details class="hks-tour-section" id="hks-tour-overview" data-hks-tour-section data-hks-section="overview" data-hks-section-label="<?php echo esc_attr__( 'Overview', 'hks-wayfinder' ); ?>" open>
						<summary><span><?php esc_html_e( 'Overview', 'hks-wayfinder' ); ?></span></summary>
						<div class="hks-tour-section__content">
							<h2><?php esc_html_e( 'Trip overview', 'hks-wayfinder' ); ?></h2>
							<?php if ( self::public_text( $overview ) ) : ?><div class="hks-prose"><?php echo wp_kses_post( do_blocks( $overview ) ); ?></div><?php else : ?><p><?php echo esc_html( self::public_text( get_post_field( 'post_excerpt', $tour_id ) ) ); ?></p><?php endif; ?>
							<?php self::render_practical_details( $tour_id ); ?>
						</div>
					</details>

					<details class="hks-tour-section" id="hks-tour-itinerary" data-hks-tour-section data-hks-section="itinerary" data-hks-section-label="<?php echo esc_attr__( 'Itinerary', 'hks-wayfinder' ); ?>">
						<summary><span><?php esc_html_e( 'Itinerary', 'hks-wayfinder' ); ?></span></summary>
						<div class="hks-tour-section__content">
							<h2><?php esc_html_e( 'Day-by-day itinerary', 'hks-wayfinder' ); ?></h2>
							<?php self::render_itinerary( $itinerary ); ?>
						</div>
					</details>

					<details class="hks-tour-section" id="hks-tour-included" data-hks-tour-section data-hks-section="included" data-hks-section-label="<?php echo esc_attr__( 'Included / Excluded', 'hks-wayfinder' ); ?>">
						<summary><span><?php esc_html_e( 'Included / Excluded', 'hks-wayfinder' ); ?></span></summary>
						<div class="hks-tour-section__content">
							<h2><?php esc_html_e( 'Included and not included', 'hks-wayfinder' ); ?></h2>
							<?php if ( $inclusions || $exclusions ) : ?><div class="hks-list-columns"><?php self::render_item_list( __( 'Included', 'hks-wayfinder' ), $inclusions, 'included' ); ?><?php self::render_item_list( __( 'Not included', 'hks-wayfinder' ), $exclusions, 'excluded' ); ?></div><?php else : ?><p><?php esc_html_e( 'No public inclusion list is available for this Tour yet. Ask for the current package breakdown in your quote.', 'hks-wayfinder' ); ?></p><?php endif; ?>
						</div>
					</details>

					<details class="hks-tour-section" id="hks-tour-rates" data-hks-tour-section data-hks-section="rates" data-hks-section-label="<?php echo esc_attr__( 'Rates & Important Information', 'hks-wayfinder' ); ?>">
						<summary><span><?php esc_html_e( 'Rates & Important Information', 'hks-wayfinder' ); ?></span></summary>
						<div class="hks-tour-section__content">
							<h2><?php esc_html_e( 'Rates and important information', 'hks-wayfinder' ); ?></h2>
							<?php self::render_rate_information( $price, $policies, $faqs ); ?>
						</div>
					</details>
			</div>
		</section>

		<?php self::render_related_tours( $tour_id ); ?>
		<div class="hks-mobile-quote-bar"><button type="button" data-hks-quote-proxy><?php esc_html_e( 'Request quote on WhatsApp', 'hks-wayfinder' ); ?></button></div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Keep Campaign details linear and conversion focused.
	 *
	 * @param array<string, int> $context Campaign context.
	 * @return string
	 */
	private static function render_campaign_details( array $context ): string {
		$tour_id    = $context['tour_id'];
		$overview   = get_post_field( 'post_content', $tour_id );
		$price      = self::price_summary( $tour_id );
		$itinerary  = self::rows( self::field( 'hks_itinerary', $tour_id ) );
		$inclusions = self::rows( self::field( 'hks_inclusions', $tour_id ) );
		$exclusions = self::rows( self::field( 'hks_exclusions', $tour_id ) );

		ob_start();
		?>
		<div class="hks-campaign-details hks-shell">
			<?php if ( self::public_text( $overview ) ) : ?><section class="hks-campaign-section"><h2><?php esc_html_e( 'The trip at a glance', 'hks-wayfinder' ); ?></h2><div class="hks-prose"><?php echo wp_kses_post( do_blocks( $overview ) ); ?></div></section><?php endif; ?>
			<?php if ( $price ) : ?><section class="hks-price-panel"><div><p class="hks-kicker"><?php esc_html_e( 'Price context', 'hks-wayfinder' ); ?></p><h2><?php echo esc_html( $price['label'] ); ?></h2><p><?php echo esc_html( $price['status'] ); ?></p></div><?php if ( $price['basis'] ) : ?><p><?php echo esc_html( $price['basis'] ); ?></p><?php endif; ?><?php if ( $price['disclaimer'] ) : ?><p class="hks-small-print"><?php echo esc_html( $price['disclaimer'] ); ?></p><?php endif; ?></section><?php endif; ?>
			<?php if ( $itinerary ) : ?><section class="hks-campaign-section"><h2><?php esc_html_e( 'Your itinerary', 'hks-wayfinder' ); ?></h2><?php self::render_itinerary( $itinerary ); ?></section><?php endif; ?>
			<?php if ( $inclusions || $exclusions ) : ?><section class="hks-campaign-section"><h2><?php esc_html_e( 'Included and not included', 'hks-wayfinder' ); ?></h2><div class="hks-list-columns"><?php self::render_item_list( __( 'Included', 'hks-wayfinder' ), $inclusions, 'included' ); ?><?php self::render_item_list( __( 'Not included', 'hks-wayfinder' ), $exclusions, 'excluded' ); ?></div></section><?php endif; ?>
			<section class="hks-quote-process"><h2><?php esc_html_e( 'Save the request, review it, then choose whether to send', 'hks-wayfinder' ); ?></h2><ol><li><?php esc_html_e( 'Share the dates, group size and useful package details.', 'hks-wayfinder' ); ?></li><li><?php esc_html_e( 'The website saves your request privately and builds the WhatsApp message.', 'hks-wayfinder' ); ?></li><li><?php esc_html_e( 'Review the message and send it when you are ready.', 'hks-wayfinder' ); ?></li></ol></section>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render one responsive catalogue card for the current Tour.
	 *
	 * @return string
	 */
	public static function render_tour_card(): string {
		$tour_id = get_the_ID();

		if ( 'hks_tour' !== get_post_type( $tour_id ) ) {
			return '';
		}

		$image_id    = get_post_thumbnail_id( $tour_id );
		$has_image   = self::media_allowed( $image_id );
		$duration    = self::public_text( self::field( 'hks_duration_label', $tour_id ) );
		$route       = self::public_text( self::field( 'hks_route_summary', $tour_id ) );
		$departure   = self::public_text( self::field( 'hks_start_location', $tour_id ) );
		$price       = self::price_summary( $tour_id );
		$destinations = self::term_names( $tour_id, 'hks_destination' );
		$link        = get_permalink( $tour_id );

		ob_start();
		?>
		<article class="hks-tour-card<?php echo $has_image ? '' : ' hks-tour-card--no-image'; ?>">
			<?php if ( $has_image ) : ?><a class="hks-tour-card__media" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true"><?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'medium_large', false, array( 'loading' => 'lazy', 'sizes' => '(max-width: 700px) 100vw, 33vw' ) ) ); ?></a><?php endif; ?>
			<div class="hks-tour-card__body">
				<?php if ( $destinations ) : ?><p class="hks-tour-card__destination"><?php echo esc_html( implode( ', ', $destinations ) ); ?></p><?php endif; ?>
				<h3><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( self::public_text( get_the_title( $tour_id ) ) ); ?></a></h3>
				<ul class="hks-tour-card__facts" aria-label="<?php esc_attr_e( 'Tour summary', 'hks-wayfinder' ); ?>">
					<?php if ( $duration ) : ?><li><?php echo esc_html( $duration ); ?></li><?php endif; ?>
					<?php if ( $departure ) : ?><li><?php echo esc_html( sprintf( __( 'From %s', 'hks-wayfinder' ), $departure ) ); ?></li><?php endif; ?>
				</ul>
				<?php if ( $route ) : ?><p class="hks-tour-card__route"><?php echo esc_html( $route ); ?></p><?php endif; ?>
				<div class="hks-tour-card__footer">
					<div class="hks-tour-card__price"><?php if ( $price ) : ?><strong><?php echo esc_html( $price['label'] ); ?></strong><span><?php echo esc_html( $price['status'] ); ?></span><?php else : ?><strong><?php esc_html_e( 'Request current KSh rate', 'hks-wayfinder' ); ?></strong><?php endif; ?></div>
					<a class="hks-tour-card__link" href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'View trip', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a>
				</div>
			</div>
		</article>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render the Destination title band and optional public media.
	 *
	 * @return string
	 */
	public static function render_destination_intro(): string {
		$term = get_queried_object();

		if ( ! $term instanceof \WP_Term || 'hks_destination' !== $term->taxonomy ) {
			return '';
		}

		$summary   = self::public_text( self::field( 'hks_short_summary', $term ) );
		$overview  = self::public_html( self::field( 'hks_overview', $term ) );
		$image_id  = absint( self::field( 'hks_hero_image', $term ) );
		$tours_url = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );

		if ( ! self::media_allowed( $image_id ) ) {
			$image_id = 0;
		}

		ob_start();
		?>
		<section class="hks-destination-intro<?php echo $image_id ? ' hks-destination-intro--with-image' : ''; ?>">
			<div class="hks-title-band"><div class="hks-shell"><?php self::breadcrumbs( array( __( 'Tours', 'hks-wayfinder' ) => $tours_url, $term->name => '' ) ); ?><h1><?php echo esc_html( self::public_text( $term->name ) ); ?></h1><p><?php echo esc_html( $summary ?: __( 'Browse the currently published tours for this destination.', 'hks-wayfinder' ) ); ?></p></div></div>
			<?php if ( $image_id || $overview ) : ?><div class="hks-shell hks-destination-intro__body"><?php if ( $overview ) : ?><div class="hks-prose"><?php echo wp_kses_post( $overview ); ?></div><?php endif; ?><?php if ( $image_id ) : ?><figure><?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'large', false, array( 'loading' => 'eager' ) ) ); ?><?php self::render_credit( $image_id ); ?></figure><?php endif; ?></div><?php endif; ?>
		</section>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render the complete homepage sequence from published catalogue data.
	 *
	 * @return string
	 */
	public static function render_home_experience(): string {
		$tours_url   = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );
		$featured    = self::tour_query( 6, true );
		$destinations = function_exists( 'hks_wayfinder_populated_terms' ) ? hks_wayfinder_populated_terms( 'hks_destination', 6 ) : array();
		$types       = function_exists( 'hks_wayfinder_populated_terms' ) ? hks_wayfinder_populated_terms( 'hks_tour_type', 8 ) : array();
		$occasions   = function_exists( 'hks_wayfinder_populated_terms' ) ? hks_wayfinder_populated_terms( 'hks_occasion', 8 ) : array();
		$hero_id     = 0;
		$hero_tour   = 0;

		foreach ( $featured as $post ) {
			$candidate = get_post_thumbnail_id( $post->ID );
			if ( self::media_allowed( $candidate ) ) {
				$hero_id   = $candidate;
				$hero_tour = $post->ID;
				break;
			}
		}

		ob_start();
		?>
		<div class="hks-home">
			<section class="hks-home-hero<?php echo $hero_id ? ' hks-home-hero--with-image' : ''; ?>">
				<?php if ( $hero_id ) : ?><div class="hks-home-hero__media"><?php echo wp_kses_post( wp_get_attachment_image( $hero_id, 'full', false, array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw' ) ) ); ?></div><?php endif; ?>
				<div class="hks-shell hks-home-hero__content">
					<p class="hks-home-hero__label"><?php esc_html_e( 'Kenya trips for local travellers', 'hks-wayfinder' ); ?></p>
					<h1><?php esc_html_e( 'Find a Kenya trip that fits the people you are bringing.', 'hks-wayfinder' ); ?></h1>
					<p><?php esc_html_e( 'Browse the route and practical details, then request a current quote on WhatsApp without committing to a booking.', 'hks-wayfinder' ); ?></p>
					<div class="hks-home-hero__actions"><a class="hks-button" href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'Explore tours', 'hks-wayfinder' ); ?></a><?php if ( $hero_tour ) : ?><a href="<?php echo esc_url( get_permalink( $hero_tour ) ); ?>"><?php esc_html_e( 'View featured trip', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a><?php endif; ?></div>
				</div>
			</section>

			<section class="hks-home-section hks-shell" aria-labelledby="hks-featured-title">
				<div class="hks-section-heading"><div><p><?php esc_html_e( 'Featured tours', 'hks-wayfinder' ); ?></p><h2 id="hks-featured-title"><?php esc_html_e( 'Start with a trip worth opening', 'hks-wayfinder' ); ?></h2></div><a href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'View all tours', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a></div>
				<?php if ( $featured ) : ?><div class="hks-tour-grid"><?php foreach ( $featured as $tour_post ) : $GLOBALS['post'] = $tour_post; setup_postdata( $tour_post ); echo self::render_tour_card(); endforeach; wp_reset_postdata(); ?></div><?php else : ?><p><?php esc_html_e( 'Published tours will appear here.', 'hks-wayfinder' ); ?></p><?php endif; ?>
			</section>

			<?php if ( $destinations ) : ?>
				<section class="hks-home-section hks-home-section--mist" id="destinations" aria-labelledby="hks-destinations-title"><div class="hks-shell"><div class="hks-section-heading"><div><p><?php esc_html_e( 'Browse by destination', 'hks-wayfinder' ); ?></p><h2 id="hks-destinations-title"><?php esc_html_e( 'Choose the place first', 'hks-wayfinder' ); ?></h2></div></div><div class="hks-destination-grid"><?php foreach ( $destinations as $term ) : $url = hks_wayfinder_term_url( $term ); $image = self::destination_image( $term ); ?><a class="hks-destination-card<?php echo $image ? '' : ' hks-destination-card--no-image'; ?>" href="<?php echo esc_url( $url ); ?>"><?php if ( $image ) : ?><span class="hks-destination-card__media"><?php echo wp_kses_post( wp_get_attachment_image( $image, 'medium_large', false, array( 'loading' => 'lazy' ) ) ); ?></span><?php endif; ?><span class="hks-destination-card__body"><strong><?php echo esc_html( $term->name ); ?></strong><span><?php echo esc_html( sprintf( _n( '%s tour', '%s tours', $term->count, 'hks-wayfinder' ), number_format_i18n( $term->count ) ) ); ?></span></span></a><?php endforeach; ?></div></div></section>
			<?php endif; ?>

			<?php if ( $types || $occasions ) : ?>
				<section class="hks-home-section hks-shell" aria-labelledby="hks-trip-type-title"><div class="hks-section-heading"><div><p><?php esc_html_e( 'Browse your way', 'hks-wayfinder' ); ?></p><h2 id="hks-trip-type-title"><?php esc_html_e( 'Start with the trip type or the occasion', 'hks-wayfinder' ); ?></h2></div></div><div class="hks-browse-groups"><?php if ( $types ) : ?><div><h3><?php esc_html_e( 'Trip type', 'hks-wayfinder' ); ?></h3><div class="hks-term-links"><?php self::render_term_links( $types ); ?></div></div><?php endif; ?><?php if ( $occasions ) : ?><div><h3><?php esc_html_e( 'Occasion', 'hks-wayfinder' ); ?></h3><div class="hks-term-links"><?php self::render_term_links( $occasions ); ?></div></div><?php endif; ?></div></section>
			<?php endif; ?>

			<section class="hks-operator-section"><div class="hks-shell hks-operator-section__grid"><div><p><?php esc_html_e( 'A clear operator relationship', 'hks-wayfinder' ); ?></p><h2><?php esc_html_e( 'Holiday Kenya Safaris is operated by Ashford Tours & Travel.', 'hks-wayfinder' ); ?></h2></div><div><p><?php esc_html_e( 'The site is built for local Kenyan travellers. The operator relationship is stated plainly so you know who handles the human quote conversation.', 'hks-wayfinder' ); ?></p><a href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'Browse the catalogue', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a></div></div></section>

			<section class="hks-home-section hks-shell" aria-labelledby="hks-quote-process-title"><div class="hks-section-heading"><div><p><?php esc_html_e( 'Request a quote on WhatsApp', 'hks-wayfinder' ); ?></p><h2 id="hks-quote-process-title"><?php esc_html_e( 'A short path from trip page to a useful conversation', 'hks-wayfinder' ); ?></h2></div></div><ol class="hks-process-grid"><li><span>1</span><h3><?php esc_html_e( 'Choose a Tour', 'hks-wayfinder' ); ?></h3><p><?php esc_html_e( 'Open the route, itinerary and current public price context.', 'hks-wayfinder' ); ?></p></li><li><span>2</span><h3><?php esc_html_e( 'Share the essentials', 'hks-wayfinder' ); ?></h3><p><?php esc_html_e( 'Add your preferred dates, group size and package-specific details.', 'hks-wayfinder' ); ?></p></li><li><span>3</span><h3><?php esc_html_e( 'Review before WhatsApp', 'hks-wayfinder' ); ?></h3><p><?php esc_html_e( 'Your request is saved privately. You review the message and decide whether to send it.', 'hks-wayfinder' ); ?></p></li></ol></section>

			<section class="hks-group-route" id="group-travel"><div class="hks-shell hks-group-route__inner"><div><p><?php esc_html_e( 'Group travel', 'hks-wayfinder' ); ?></p><h2><?php esc_html_e( 'Planning for family, friends or colleagues?', 'hks-wayfinder' ); ?></h2><p><?php esc_html_e( 'Start with a Tour, then share the group size, dates and departure town so the quote conversation begins with useful context.', 'hks-wayfinder' ); ?></p></div><a class="hks-button" href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'Find a group trip', 'hks-wayfinder' ); ?></a></div></section>

			<section class="hks-home-section hks-shell hks-proof-section" aria-labelledby="hks-proof-title"><div><p><?php esc_html_e( 'What you can check first', 'hks-wayfinder' ); ?></p><h2 id="hks-proof-title"><?php esc_html_e( 'Practical detail before the sales conversation', 'hks-wayfinder' ); ?></h2></div><ul><li><?php esc_html_e( 'Route, duration and departure context', 'hks-wayfinder' ); ?></li><li><?php esc_html_e( 'Day-by-day itinerary where published', 'hks-wayfinder' ); ?></li><li><?php esc_html_e( 'Included and excluded items where published', 'hks-wayfinder' ); ?></li><li><?php esc_html_e( 'One clear KSh price line or request-rate fallback', 'hks-wayfinder' ); ?></li></ul></section>

			<section class="hks-final-cta"><div class="hks-shell"><h2><?php esc_html_e( 'Ready to narrow down the options?', 'hks-wayfinder' ); ?></h2><p><?php esc_html_e( 'Choose a Tour first. Its quote button will carry the package into the intake and WhatsApp message.', 'hks-wayfinder' ); ?></p><a class="hks-button" href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'Explore all tours', 'hks-wayfinder' ); ?></a></div></section>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render GET-based Tour catalogue controls.
	 *
	 * @return string
	 */
	public static function render_catalogue_controls(): string {
		$taxonomies = array(
			'hks_destination' => __( 'Destination', 'hks-wayfinder' ),
			'hks_tour_type'   => __( 'Trip type', 'hks-wayfinder' ),
			'hks_occasion'    => __( 'Occasion', 'hks-wayfinder' ),
			'hks_travel_style' => __( 'Travel style', 'hks-wayfinder' ),
		);
		$available = array();

		foreach ( $taxonomies as $taxonomy => $label ) {
			$terms = function_exists( 'hks_wayfinder_populated_terms' ) ? hks_wayfinder_populated_terms( $taxonomy ) : array();
			if ( $terms ) {
				$available[ $taxonomy ] = array( 'label' => $label, 'terms' => $terms );
			}
		}

		if ( ! $available ) {
			return '';
		}

		$archive = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );

		ob_start();
		?>
		<form class="hks-catalogue-controls" method="get" action="<?php echo esc_url( $archive ); ?>" data-hks-catalogue-controls>
			<div class="hks-catalogue-controls__fields">
				<?php foreach ( $available as $taxonomy => $group ) : $raw_selected = $_GET[ $taxonomy ] ?? ''; $selected = is_string( $raw_selected ) ? sanitize_title( wp_unslash( $raw_selected ) ) : ''; ?>
					<label><span><?php echo esc_html( $group['label'] ); ?></span><select name="<?php echo esc_attr( $taxonomy ); ?>"><option value=""><?php echo esc_html( sprintf( __( 'All %s', 'hks-wayfinder' ), strtolower( $group['label'] ) ) ); ?></option><?php foreach ( $group['terms'] as $term ) : ?><option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $selected, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option><?php endforeach; ?></select></label>
				<?php endforeach; ?>
				<?php $raw_sort = $_GET['hks_sort'] ?? ''; $selected_sort = is_string( $raw_sort ) ? sanitize_key( wp_unslash( $raw_sort ) ) : ''; ?>
				<label><span><?php esc_html_e( 'Sort', 'hks-wayfinder' ); ?></span><select name="hks_sort"><option value="recommended"><?php esc_html_e( 'Recommended', 'hks-wayfinder' ); ?></option><option value="newest" <?php selected( $selected_sort, 'newest' ); ?>><?php esc_html_e( 'Newest', 'hks-wayfinder' ); ?></option><option value="title" <?php selected( $selected_sort, 'title' ); ?>><?php esc_html_e( 'A–Z', 'hks-wayfinder' ); ?></option></select></label>
			</div>
			<div class="hks-catalogue-controls__actions"><button type="submit"><?php esc_html_e( 'Apply filters', 'hks-wayfinder' ); ?></button><a href="<?php echo esc_url( $archive ); ?>"><?php esc_html_e( 'Clear', 'hks-wayfinder' ); ?></a></div>
		</form>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render an approved Tour gallery and native-dialog lightbox.
	 *
	 * @param int[]  $images Image IDs.
	 * @param string $title  Tour title.
	 * @return void
	 */
	private static function render_gallery( array $images, string $title ): void {
		$count = count( $images );
		?>
		<div class="hks-tour-gallery hks-tour-gallery--<?php echo esc_attr( (string) min( 3, $count ) ); ?> hks-shell" data-hks-gallery>
			<div class="hks-tour-gallery__grid">
				<?php foreach ( array_slice( $images, 0, 3 ) as $index => $image_id ) : ?><button type="button" class="hks-tour-gallery__item" data-hks-gallery-open="<?php echo esc_attr( (string) $index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Open image %1$s of %2$s for %3$s', 'hks-wayfinder' ), $index + 1, $count, $title ) ); ?>"><?php echo wp_kses_post( wp_get_attachment_image( $image_id, 0 === $index ? 'large' : 'medium_large', false, array( 'loading' => 0 === $index ? 'eager' : 'lazy', 'sizes' => 0 === $index ? '(max-width: 768px) 100vw, 66vw' : '(max-width: 768px) 50vw, 33vw' ) ) ); ?></button><?php endforeach; ?>
			</div>
			<button type="button" class="hks-tour-gallery__view" data-hks-gallery-open="0"><?php esc_html_e( 'View gallery', 'hks-wayfinder' ); ?><span><?php echo esc_html( sprintf( _n( '%s image', '%s images', $count, 'hks-wayfinder' ), number_format_i18n( $count ) ) ); ?></span></button>
			<dialog class="hks-gallery-lightbox" data-hks-gallery-dialog aria-label="<?php echo esc_attr( sprintf( __( '%s image gallery', 'hks-wayfinder' ), $title ) ); ?>">
				<div class="hks-gallery-lightbox__bar"><span data-hks-gallery-counter></span><button type="button" data-hks-gallery-close aria-label="<?php esc_attr_e( 'Close gallery', 'hks-wayfinder' ); ?>">×</button></div>
				<div class="hks-gallery-lightbox__slides"><?php foreach ( $images as $index => $image_id ) : ?><figure data-hks-gallery-slide <?php echo 0 === $index ? '' : 'hidden'; ?>><?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'full', false, array( 'loading' => 'lazy' ) ) ); ?><?php self::render_credit( $image_id ); ?></figure><?php endforeach; ?></div>
				<?php if ( $count > 1 ) : ?><div class="hks-gallery-lightbox__controls"><button type="button" data-hks-gallery-prev><?php esc_html_e( 'Previous', 'hks-wayfinder' ); ?></button><button type="button" data-hks-gallery-next><?php esc_html_e( 'Next', 'hks-wayfinder' ); ?></button></div><?php endif; ?>
			</dialog>
		</div>
		<?php
	}

	/**
	 * Render itinerary as accessible expandable days.
	 *
	 * @param array<int, array<string, mixed>> $itinerary Itinerary rows.
	 * @return void
	 */
	private static function render_itinerary( array $itinerary ): void {
		if ( ! $itinerary ) {
			echo '<p>' . esc_html__( 'No public day-by-day itinerary is available for this Tour yet. Ask for the current plan in your quote.', 'hks-wayfinder' ) . '</p>';
			return;
		}
		?>
		<div class="hks-itinerary" data-hks-itinerary>
			<?php if ( count( $itinerary ) > 3 ) : ?><div class="hks-itinerary__controls" data-hks-itinerary-controls><button type="button" data-action="expand"><?php esc_html_e( 'Expand all', 'hks-wayfinder' ); ?></button><button type="button" data-action="collapse"><?php esc_html_e( 'Collapse all', 'hks-wayfinder' ); ?></button></div><?php endif; ?>
			<div class="hks-itinerary__days">
				<?php foreach ( $itinerary as $index => $day ) : $day_title = self::public_text( $day['day_title'] ?? '' ); if ( ! $day_title ) { continue; } ?>
					<details data-hks-itinerary-day <?php echo 0 === $index ? 'open' : ''; ?>><summary><span class="hks-itinerary__marker"><?php echo esc_html( self::public_text( $day['day_number'] ?? '' ) ?: (string) ( $index + 1 ) ); ?></span><span><?php echo esc_html( $day_title ); ?></span></summary><div class="hks-itinerary__content"><?php if ( self::public_text( $day['description'] ?? '' ) ) : ?><p><?php echo esc_html( self::public_text( $day['description'] ) ); ?></p><?php endif; ?><?php self::day_meta( $day ); ?></div></details>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render practical facts inside Overview.
	 *
	 * @param int $tour_id Tour ID.
	 * @return void
	 */
	private static function render_practical_details( int $tour_id ): void {
		$items = array(
			__( 'Accommodation basis', 'hks-wayfinder' ) => self::public_text( self::field( 'hks_accommodation_basis', $tour_id ) ),
			__( 'Meals', 'hks-wayfinder' )               => self::public_text( self::field( 'hks_meals_summary', $tour_id ) ),
			__( 'Best for', 'hks-wayfinder' )            => self::public_text( self::field( 'hks_best_for', $tour_id ) ),
			__( 'Child suitability', 'hks-wayfinder' )   => self::public_text( self::field( 'hks_child_suitability', $tour_id ) ),
			__( 'Accessibility', 'hks-wayfinder' )       => self::public_text( self::field( 'hks_accessibility_notes', $tour_id ) ),
		);

		if ( ! array_filter( $items ) ) {
			return;
		}
		?>
		<h3><?php esc_html_e( 'Practical details', 'hks-wayfinder' ); ?></h3><dl class="hks-practical-grid"><?php foreach ( $items as $label => $value ) : if ( $value ) : ?><div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div><?php endif; endforeach; ?></dl>
		<?php
	}

	/**
	 * Render the price line, package notes and published FAQs.
	 *
	 * @param array<string, string>|null         $price    Price summary.
	 * @param string[]                          $policies Public package notes.
	 * @param array<int, array<string, string>> $faqs     Published FAQs.
	 * @return void
	 */
	private static function render_rate_information( ?array $price, array $policies, array $faqs ): void {
		$price = $price ?: self::request_rate_fallback();
		?>
		<div class="hks-rate-information"><div class="hks-rate-information__lead"><strong><?php echo esc_html( $price['label'] ); ?></strong><p><?php echo esc_html( $price['status'] ); ?></p></div></div>
		<?php if ( $policies ) : ?><h3><?php esc_html_e( 'Important package notes', 'hks-wayfinder' ); ?></h3><ul class="hks-note-list"><?php foreach ( $policies as $policy ) : ?><li><?php echo esc_html( $policy ); ?></li><?php endforeach; ?></ul><?php endif; ?>
		<?php if ( $faqs ) : ?><h3><?php esc_html_e( 'Questions before you request a quote', 'hks-wayfinder' ); ?></h3><div class="hks-faqs"><?php foreach ( $faqs as $faq ) : ?><details><summary><?php echo esc_html( $faq['question'] ); ?></summary><div><?php echo wp_kses_post( $faq['answer'] ); ?></div></details><?php endforeach; ?></div><?php endif; ?>
		<?php
	}

	/**
	 * Render related Tours from shared destinations, then catalogue fallback.
	 *
	 * @param int $tour_id Current Tour.
	 * @return void
	 */
	private static function render_related_tours( int $tour_id ): void {
		$term_ids = wp_get_post_terms( $tour_id, 'hks_destination', array( 'fields' => 'ids' ) );
		$args     = array( 'post_type' => 'hks_tour', 'post_status' => 'publish', 'posts_per_page' => 3, 'post__not_in' => array( $tour_id ), 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );

		if ( ! is_wp_error( $term_ids ) && $term_ids ) {
			$args['tax_query'] = array( array( 'taxonomy' => 'hks_destination', 'field' => 'term_id', 'terms' => $term_ids ) );
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return;
		}
		?>
		<section class="hks-related-tours"><div class="hks-shell"><div class="hks-section-heading"><div><p><?php esc_html_e( 'Keep exploring', 'hks-wayfinder' ); ?></p><h2><?php esc_html_e( 'Related tours', 'hks-wayfinder' ); ?></h2></div></div><div class="hks-tour-grid"><?php while ( $query->have_posts() ) : $query->the_post(); ?><div data-hks-related-tour="<?php echo esc_attr( (string) get_the_ID() ); ?>"><?php echo self::render_tour_card(); ?></div><?php endwhile; wp_reset_postdata(); ?></div></div></section>
		<?php
	}

	/**
	 * Return Tour facts with only meaningful values.
	 *
	 * @param int $tour_id Tour ID.
	 * @return array<string, string>
	 */
	private static function tour_facts( int $tour_id ): array {
		$facts = array(
			__( 'Duration', 'hks-wayfinder' )      => self::public_text( self::field( 'hks_duration_label', $tour_id ) ),
			__( 'Starts in', 'hks-wayfinder' )     => self::public_text( self::field( 'hks_start_location', $tour_id ) ),
			__( 'Ends in', 'hks-wayfinder' )       => self::public_text( self::field( 'hks_end_location', $tour_id ) ),
			__( 'Route', 'hks-wayfinder' )         => self::public_text( self::field( 'hks_route_summary', $tour_id ) ),
			__( 'Transport', 'hks-wayfinder' )     => self::transport_labels( self::field( 'hks_transport_types', $tour_id ) ),
			__( 'Travel style', 'hks-wayfinder' )  => implode( ', ', self::term_names( $tour_id, 'hks_travel_style' ) ),
		);

		return array_filter( $facts );
	}

	/**
	 * Collect featured image and ordered gallery under media governance.
	 *
	 * @param int $tour_id Tour ID.
	 * @return int[]
	 */
	private static function tour_images( int $tour_id ): array {
		$candidates = array( get_post_thumbnail_id( $tour_id ) );
		$gallery    = self::field( 'hks_gallery', $tour_id );

		foreach ( is_array( $gallery ) ? $gallery : array() as $image ) {
			$candidates[] = is_array( $image ) ? absint( $image['ID'] ?? $image['id'] ?? 0 ) : absint( $image );
		}

		$allowed = array();
		foreach ( array_unique( array_filter( $candidates ) ) as $image_id ) {
			if ( self::media_allowed( (int) $image_id ) ) {
				$allowed[] = (int) $image_id;
			}
		}

		return $allowed;
	}

	/**
	 * Find an approved image for a Destination card.
	 *
	 * @param \WP_Term $term Destination term.
	 * @return int
	 */
	private static function destination_image( \WP_Term $term ): int {
		$image_id = absint( self::field( 'hks_hero_image', $term ) );

		if ( self::media_allowed( $image_id ) ) {
			return $image_id;
		}

		$query = new \WP_Query( array( 'post_type' => 'hks_tour', 'post_status' => 'publish', 'posts_per_page' => 4, 'fields' => 'ids', 'tax_query' => array( array( 'taxonomy' => 'hks_destination', 'field' => 'term_id', 'terms' => $term->term_id ) ) ) );
		foreach ( $query->posts as $tour_id ) {
			$image_id = get_post_thumbnail_id( $tour_id );
			if ( self::media_allowed( $image_id ) ) {
				return $image_id;
			}
		}

		return 0;
	}

	/**
	 * Get curated Tours, falling back to newest published Tours.
	 *
	 * @param int  $limit         Maximum posts.
	 * @param bool $prefer_featured Prefer featured meta.
	 * @return \WP_Post[]
	 */
	private static function tour_query( int $limit, bool $prefer_featured = false ): array {
		$posts = get_posts( array( 'post_type' => 'hks_tour', 'post_status' => 'publish', 'posts_per_page' => 50, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ) ) );

		if ( $prefer_featured ) {
			usort(
				$posts,
				static function ( $left, $right ): int {
					return (int) (bool) self::field( 'hks_featured', $right->ID ) <=> (int) (bool) self::field( 'hks_featured', $left->ID );
				}
			);
		}

		return array_slice( $posts, 0, $limit );
	}

	/**
	 * Render term links.
	 *
	 * @param \WP_Term[] $terms Terms.
	 * @return void
	 */
	private static function render_term_links( array $terms ): void {
		foreach ( $terms as $term ) {
			$url = function_exists( 'hks_wayfinder_term_url' ) ? hks_wayfinder_term_url( $term ) : '';
			if ( $url ) {
				echo '<a href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '<span>' . esc_html( (string) $term->count ) . '</span></a>';
			}
		}
	}

	/**
	 * Render compact breadcrumbs.
	 *
	 * @param array<string, string> $items Labels and URLs; blank URL is current.
	 * @return void
	 */
	private static function breadcrumbs( array $items ): void {
		echo '<nav class="hks-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'hks-wayfinder' ) . '"><ol><li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'hks-wayfinder' ) . '</a></li>';
		foreach ( $items as $label => $url ) {
			echo '<li>' . ( $url ? '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>' : '<span aria-current="page">' . esc_html( $label ) . '</span>' ) . '</li>';
		}
		echo '</ol></nav>';
	}

	/**
	 * Resolve the canonical Tour behind the current Tour or Campaign.
	 *
	 * @return array<string, int>|null
	 */
	private static function tour_context(): ?array {
		$view_id = get_the_ID();
		$type    = get_post_type( $view_id );

		if ( 'hks_tour' === $type ) {
			return array( 'view_id' => $view_id, 'tour_id' => $view_id, 'campaign_id' => 0 );
		}

		if ( 'hks_campaign' === $type ) {
			$tour_id = absint( self::field( 'hks_linked_tour', $view_id ) );
			if ( $tour_id && 'hks_tour' === get_post_type( $tour_id ) ) {
				return array( 'view_id' => $view_id, 'tour_id' => $tour_id, 'campaign_id' => $view_id );
			}
		}

		return null;
	}

	/**
	 * Build the single public per-person price line or its fallback.
	 *
	 * @param int $tour_id Tour ID.
	 * @return array<string, string>
	 */
	private static function price_summary( int $tour_id ): array {
		$amount = absint( self::field( 'hks_from_price_ksh', $tour_id ) );

		if ( ! $amount ) {
			return self::request_rate_fallback();
		}

		return array(
			'label'      => sprintf( __( 'From KSh %s per person', 'hks-wayfinder' ), number_format_i18n( $amount, 0 ) ),
			'status'     => __( 'Final price is confirmed for your dates, group and availability in the quote.', 'hks-wayfinder' ),
			'basis'      => '',
			'disclaimer' => '',
			'is_from'    => '1',
		);
	}

	/**
	 * Safe fallback when no public starting price is entered.
	 *
	 * @return array<string, string>
	 */
	private static function request_rate_fallback(): array {
		return array( 'label' => __( 'Request current KSh rate', 'hks-wayfinder' ), 'status' => __( 'Final price is confirmed for your dates, group and availability in the quote.', 'hks-wayfinder' ), 'basis' => '', 'disclaimer' => '', 'is_from' => '' );
	}

	/**
	 * Collect every entered public package note.
	 *
	 * @param int $tour_id Tour ID.
	 * @return string[]
	 */
	private static function approved_policies( int $tour_id ): array {
		$approved = array();
		foreach ( self::rows( self::field( 'hks_policies', $tour_id ) ) as $policy ) {
			$summary = self::public_text( $policy['public_summary'] ?? '' );
			if ( $summary ) {
				$approved[] = $summary;
			}
		}
		return $approved;
	}

	/**
	 * Collect selected published FAQs with a question and answer.
	 *
	 * @param array<string, int> $context Tour context.
	 * @return array<int, array<string, string>>
	 */
	private static function approved_faqs( array $context ): array {
		$faq_ids = $context['campaign_id'] ? self::field( 'hks_featured_faqs', $context['campaign_id'] ) : array();
		if ( ! is_array( $faq_ids ) || ! $faq_ids ) {
			$faq_ids = self::field( 'hks_featured_faqs', $context['tour_id'] );
		}
		$approved = array();
		foreach ( is_array( $faq_ids ) ? $faq_ids : array() as $faq_id ) {
			$faq_id   = absint( is_object( $faq_id ) ? $faq_id->ID : $faq_id );
			$question = self::public_text( get_the_title( $faq_id ) );
			$answer   = self::public_html( self::field( 'hks_faq_answer', $faq_id ) );
			if ( 'hks_faq' === get_post_type( $faq_id ) && 'publish' === get_post_status( $faq_id ) && $question && $answer ) {
				$approved[] = array( 'question' => $question, 'answer' => $answer );
			}
		}
		return $approved;
	}

	/**
	 * Check that an image exists and has useful native alt text.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool
	 */
	private static function media_allowed( int $attachment_id ): bool {
		if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
			return false;
		}
		$alt = self::public_text( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );

		return '' !== $alt;
	}

	/**
	 * Render the native attachment caption when one was supplied.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return void
	 */
	private static function render_credit( int $attachment_id ): void {
		$credit = self::public_text( wp_get_attachment_caption( $attachment_id ) );
		if ( $credit ) {
			echo '<figcaption>' . esc_html( $credit ) . '</figcaption>';
		}
	}

	/**
	 * Render itinerary secondary facts.
	 *
	 * @param array<string, mixed> $day Itinerary row.
	 * @return void
	 */
	private static function day_meta( array $day ): void {
		$items = array( __( 'Activities', 'hks-wayfinder' ) => self::public_text( $day['activities'] ?? '' ), __( 'Accommodation', 'hks-wayfinder' ) => self::public_text( $day['accommodation'] ?? '' ), __( 'Meals', 'hks-wayfinder' ) => self::public_text( $day['meals'] ?? '' ) );
		if ( ! array_filter( $items ) ) {
			return;
		}
		echo '<dl class="hks-itinerary__facts">';
		foreach ( $items as $label => $value ) {
			if ( $value ) {
				echo '<div><dt>' . esc_html( $label ) . '</dt><dd>' . nl2br( esc_html( $value ) ) . '</dd></div>';
			}
		}
		echo '</dl>';
	}

	/**
	 * Render one inclusion or exclusion list.
	 *
	 * @param string                            $heading Heading.
	 * @param array<int, array<string, mixed>> $rows Rows.
	 * @param string                            $type CSS modifier.
	 * @return void
	 */
	private static function render_item_list( string $heading, array $rows, string $type ): void {
		if ( ! $rows ) {
			return;
		}
		?>
		<div class="hks-detail-list hks-detail-list--<?php echo esc_attr( $type ); ?>"><h3><?php echo esc_html( $heading ); ?></h3><ul><?php foreach ( $rows as $row ) : $item = self::public_text( $row['item'] ?? '' ); if ( $item ) : ?><li><strong><?php echo esc_html( $item ); ?></strong><?php if ( self::public_text( $row['detail'] ?? '' ) ) : ?><span><?php echo esc_html( self::public_text( $row['detail'] ) ); ?></span><?php endif; ?></li><?php endif; endforeach; ?></ul></div>
		<?php
	}

	/**
	 * Return term labels for one Tour.
	 *
	 * @param int    $tour_id  Tour ID.
	 * @param string $taxonomy Taxonomy.
	 * @return string[]
	 */
	private static function term_names( int $tour_id, string $taxonomy ): array {
		$terms = get_the_terms( $tour_id, $taxonomy );
		if ( ! is_array( $terms ) ) {
			return array();
		}
		return array_values( array_filter( array_map( static fn( $term ) => self::public_text( $term->name ), $terms ) ) );
	}

	/**
	 * Convert stored transport values to visitor labels.
	 *
	 * @param mixed $values Transport values.
	 * @return string
	 */
	private static function transport_labels( $values ): string {
		$labels = array( 'safari_van' => __( 'Safari van', 'hks-wayfinder' ), 'land_cruiser' => __( 'Land Cruiser', 'hks-wayfinder' ), 'flight' => __( 'Flight', 'hks-wayfinder' ), 'bus' => __( 'Bus or coach', 'hks-wayfinder' ), 'other' => __( 'Other confirmed transport', 'hks-wayfinder' ) );
		$output = array();
		foreach ( is_array( $values ) ? $values : array() as $value ) {
			if ( isset( $labels[ $value ] ) ) {
				$output[] = $labels[ $value ];
			}
		}
		return implode( ', ', $output );
	}

	/**
	 * Normalize a repeater value.
	 *
	 * @param mixed $value Field value.
	 * @return array<int, array<string, mixed>>
	 */
	private static function rows( $value ): array {
		return is_array( $value ) ? array_values( array_filter( $value, 'is_array' ) ) : array();
	}

	/**
	 * Remove internal sentinels from plain public text.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	private static function public_text( $value ): string {
		if ( is_array( $value ) || is_object( $value ) ) {
			return '';
		}
		$text = trim( wp_strip_all_tags( (string) $value ) );
		return false !== stripos( html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' ), self::SENTINEL ) ? '' : $text;
	}

	/**
	 * Remove internal sentinels while retaining safe editor markup.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	private static function public_html( $value ): string {
		if ( is_array( $value ) || is_object( $value ) || false !== stripos( html_entity_decode( wp_strip_all_tags( (string) $value ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ), self::SENTINEL ) ) {
			return '';
		}
		return wp_kses_post( (string) $value );
	}

	/**
	 * Return an SCF field for a post or taxonomy term.
	 *
	 * @param string       $name Field name.
	 * @param int|\WP_Term $object Object.
	 * @return mixed
	 */
	private static function field( string $name, $object ) {
		if ( function_exists( 'get_field' ) ) {
			return get_field( $name, $object );
		}
		if ( $object instanceof \WP_Term ) {
			return get_term_meta( $object->term_id, $name, true );
		}
		return get_post_meta( (int) $object, $name, true );
	}
}
