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
			'taxonomy-intro'     => 'render_taxonomy_intro',
			'home-experience'    => 'render_home_experience',
			'catalogue-controls' => 'render_catalogue_controls',
			'footer-brand'       => 'render_footer_brand',
			'footer-navigation'  => 'render_footer_navigation',
			'page-title'         => 'render_page_title',
			'group-travel-page'  => 'render_group_travel_page',
		);

		foreach ( $blocks as $directory => $callback ) {
			register_block_type(
				get_theme_file_path( 'blocks/' . $directory ),
				array( 'render_callback' => array( self::class, $callback ) )
			);
		}
	}

	/**
	 * Return the first approved gallery image for the current Tour or Campaign.
	 *
	 * The theme uses this before block rendering so the browser can discover the
	 * eventual LCP image from a responsive preload in the document head.
	 *
	 * @return int Attachment ID, or zero when no approved gallery image exists.
	 */
	public static function current_gallery_image_id(): int {
		if ( ! is_singular( array( 'hks_tour', 'hks_campaign' ) ) ) {
			return 0;
		}

		$context = self::tour_context();
		if ( ! $context ) {
			return 0;
		}

		$images = $context['campaign_id']
			? self::campaign_images( $context['campaign_id'], $context['tour_id'] )
			: self::tour_images( $context['tour_id'] );

		return absint( $images[0] ?? 0 );
	}

	/**
	 * Render the reversed production lockup on the dark footer surface.
	 *
	 * @return string
	 */
	public static function render_footer_brand(): string {
		$home_url = home_url( '/' );
		$logo_url = get_theme_file_uri( 'assets/images/brand/holiday-kenya-safaris-logo-reversed.svg' );

		return sprintf(
			'<a class="hks-footer-brand" href="%1$s" rel="home" aria-label="%2$s"><img src="%3$s" width="895" height="342" alt="%4$s" loading="lazy" decoding="async"></a>',
			esc_url( $home_url ),
			esc_attr__( 'Holiday Kenya Safaris home', 'hks-wayfinder' ),
			esc_url( $logo_url ),
			esc_attr__( 'Holiday Kenya Safaris', 'hks-wayfinder' )
		);
	}

	/**
	 * Render the dashboard-managed footer menu with its existing safe fallback.
	 *
	 * @return string
	 */
	public static function render_footer_navigation(): string {
		return NavMenus::render_footer();
	}

	/**
	 * Render a semantic title band for a standard WordPress Page.
	 *
	 * @return string
	 */
	public static function render_page_title(): string {
		if ( ! is_singular( 'page' ) ) {
			return '';
		}

		$title = self::public_text( get_the_title() );

		if ( '' === $title ) {
			return '';
		}

		ob_start();
		?>
		<div class="hks-title-band">
			<div class="hks-shell">
				<?php self::breadcrumbs( array( $title => '' ) ); ?>
				<h1><?php echo esc_html( $title ); ?></h1>
			</div>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render the Group Travel planner from published catalogue data.
	 *
	 * @return string
	 */
	public static function render_group_travel_page(): string {
		if ( ! is_page( 'group-travel' ) ) {
			return '';
		}

		$visuals   = array();
		$image_ids = array();

		foreach ( self::tour_query( 18, true ) as $tour ) {
			$images = self::tour_images( $tour->ID );

			if ( ! $images || in_array( $images[0], $image_ids, true ) ) {
				continue;
			}

			$image_ids[] = $images[0];
			$visuals[]   = array(
				'image_id'    => $images[0],
				'title'       => self::public_text( get_the_title( $tour ) ),
				'destination' => implode( ', ', self::term_names( $tour->ID, 'hks_destination' ) ),
			);

			if ( 3 === count( $visuals ) ) {
				break;
			}
		}

		$planner   = do_blocks( '<!-- wp:hks/quote-cta {"location":"group_travel_page","mode":"group_travel"} /-->' );
		$tours_url = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );

		ob_start();
		?>
		<div class="hks-group-travel">
			<section class="hks-group-travel-lead" aria-labelledby="hks-group-travel-lead-title">
				<div class="hks-shell hks-group-travel-lead__inner">
					<div class="hks-group-travel-lead__copy">
						<p class="hks-group-travel__eyebrow"><?php esc_html_e( 'Plan together', 'hks-wayfinder' ); ?></p>
						<h2 id="hks-group-travel-lead-title"><?php esc_html_e( 'Put the whole group on one clear plan.', 'hks-wayfinder' ); ?></h2>
						<p><?php esc_html_e( 'Choose a destination and tour, add your dates and group size, then review a ready-to-send WhatsApp request.', 'hks-wayfinder' ); ?></p>
						<a class="hks-button" href="#group-travel-planner"><?php esc_html_e( 'Build your group request', 'hks-wayfinder' ); ?></a>
					</div>
					<?php if ( $visuals ) : ?>
						<div class="hks-group-travel-visuals hks-group-travel-visuals--<?php echo esc_attr( (string) count( $visuals ) ); ?>" aria-label="<?php esc_attr_e( 'Featured tour highlights', 'hks-wayfinder' ); ?>">
							<?php foreach ( $visuals as $index => $visual ) : ?>
								<figure>
									<?php
									echo wp_get_attachment_image(
										$visual['image_id'],
										'large',
										false,
										array(
											'loading'       => 0 === $index ? 'eager' : 'lazy',
											'fetchpriority' => 0 === $index ? 'high' : 'auto',
											'sizes'         => '(min-width: 1024px) 24vw, (min-width: 768px) 30vw, 78vw',
										)
									); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core generates and escapes attachment markup.
									?>
									<figcaption><strong><?php echo esc_html( $visual['title'] ); ?></strong><?php if ( $visual['destination'] ) : ?><span><?php echo esc_html( $visual['destination'] ); ?></span><?php endif; ?></figcaption>
								</figure>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</section>

			<section class="hks-group-travel-planner" id="group-travel-planner" aria-labelledby="hks-group-travel-planner-title">
				<div class="hks-shell hks-group-travel-planner__inner">
					<div class="hks-group-travel-planner__heading">
						<p class="hks-group-travel__eyebrow"><?php esc_html_e( 'Your starting point', 'hks-wayfinder' ); ?></p>
						<h2 id="hks-group-travel-planner-title"><?php esc_html_e( 'Start with the trip you can point everyone to.', 'hks-wayfinder' ); ?></h2>
						<p><?php esc_html_e( 'Choose your destination first, then select from the tours available for that place.', 'hks-wayfinder' ); ?></p>
					</div>
					<div class="hks-group-travel-planner__form">
						<?php if ( $planner ) : ?>
							<?php echo $planner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered by the registered HKS block. ?>
						<?php else : ?>
							<p><?php esc_html_e( 'No tours are available in the group planner yet. Browse all tours to keep exploring.', 'hks-wayfinder' ); ?></p>
							<a class="hks-button" href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'Browse all tours', 'hks-wayfinder' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</section>

			<section class="hks-group-travel-process" aria-labelledby="hks-group-travel-process-title">
				<div class="hks-shell">
					<div class="hks-group-travel-process__heading">
						<p class="hks-group-travel__eyebrow"><?php esc_html_e( 'What happens next', 'hks-wayfinder' ); ?></p>
						<h2 id="hks-group-travel-process-title"><?php esc_html_e( 'Send a useful group request in three steps.', 'hks-wayfinder' ); ?></h2>
					</div>
					<ol>
					<li><span>1</span><div><h3><?php esc_html_e( 'Choose the trip', 'hks-wayfinder' ); ?></h3><p><?php esc_html_e( 'Select a destination and the tour your group is considering.', 'hks-wayfinder' ); ?></p></div></li>
						<li><span>2</span><div><h3><?php esc_html_e( 'Share the essentials', 'hks-wayfinder' ); ?></h3><p><?php esc_html_e( 'Add the proposed dates, number of travelers and contact details.', 'hks-wayfinder' ); ?></p></div></li>
					<li><span>3</span><div><h3><?php esc_html_e( 'Check your message', 'hks-wayfinder' ); ?></h3><p><?php esc_html_e( 'Review the prepared message, then choose WhatsApp or email when you are ready to send it.', 'hks-wayfinder' ); ?></p></div></li>
					</ol>
					<p class="hks-group-travel-process__operator"><?php esc_html_e( 'Holiday Kenya Safaris is operated by Ashford Tours & Travel.', 'hks-wayfinder' ); ?></p>
				</div>
			</section>
		</div>
		<?php

		return (string) ob_get_clean();
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
	 * Render the canonical Tour title band.
	 *
	 * @param int $tour_id Tour ID.
	 * @return string
	 */
	private static function render_canonical_hero( int $tour_id ): string {
		$title     = self::public_text( get_the_title( $tour_id ) );
		$tours_url = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );

		ob_start();
		?>
		<section class="hks-tour-lead">
			<div class="hks-title-band">
				<div class="hks-shell">
					<?php self::breadcrumbs( array( __( 'Tours', 'hks-wayfinder' ) => $tours_url, $title => '' ) ); ?>
					<h1><?php echo esc_html( $title ); ?></h1>
				</div>
			</div>
		</section>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render Campaign-controlled copy inside the canonical Tour title band.
	 *
	 * @param array<string, int> $context Campaign context.
	 * @return string
	 */
	private static function render_campaign_hero( array $context ): string {
		$campaign_id  = $context['campaign_id'];
		$title        = self::public_text( self::field( 'hks_hero_headline', $campaign_id ) ) ?: self::public_text( get_the_title( $campaign_id ) );
		$introduction = self::public_html( self::field( 'hks_supporting_copy', $campaign_id ) );
		$tours_url    = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );

		ob_start();
		?>
		<section class="hks-tour-lead hks-campaign-lead">
			<div class="hks-title-band">
				<div class="hks-shell">
					<?php self::breadcrumbs( array( __( 'Tours', 'hks-wayfinder' ) => $tours_url, $title => '' ) ); ?>
					<h1><?php echo esc_html( $title ); ?></h1>
					<?php if ( $introduction ) : ?><div class="hks-campaign-lead__intro"><?php echo wp_kses_post( wpautop( $introduction ) ); ?></div><?php endif; ?>
				</div>
			</div>
		</section>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render the canonical Tour workspace for Tours and Campaigns.
	 *
	 * @return string
	 */
	public static function render_tour_details(): string {
		$context = self::tour_context();

		if ( ! $context ) {
			return '';
		}

		return self::render_canonical_details( $context['tour_id'], $context['campaign_id'] );
	}

	/**
	 * Render the 68/32 canonical Tour workspace, sections and one quote form.
	 *
	 * @param int $tour_id     Tour ID.
	 * @param int $campaign_id Optional Campaign ID.
	 * @return string
	 */
	private static function render_canonical_details( int $tour_id, int $campaign_id = 0 ): string {
		$title        = $campaign_id ? self::public_text( self::field( 'hks_hero_headline', $campaign_id ) ) ?: self::public_text( get_the_title( $campaign_id ) ) : self::public_text( get_the_title( $tour_id ) );
		$overview     = get_post_field( 'post_content', $tour_id );
		$itinerary    = self::rows( self::field( 'hks_itinerary', $tour_id ) );
		$inclusions   = self::rows( self::field( 'hks_inclusions', $tour_id ) );
		$exclusions   = self::rows( self::field( 'hks_exclusions', $tour_id ) );
		$policies     = self::approved_policies( $tour_id );
		$faqs         = self::approved_faqs( array( 'tour_id' => $tour_id, 'campaign_id' => $campaign_id ) );
		$facts        = self::tour_facts( $tour_id );
		$price        = $campaign_id ? self::campaign_price_summary( $campaign_id, $tour_id ) : self::tour_price_summary( $tour_id );
		$images       = $campaign_id ? self::campaign_images( $campaign_id, $tour_id ) : self::tour_images( $tour_id );
		$route        = self::public_text( self::field( 'hks_route_summary', $tour_id ) );
		$destinations = self::term_names( $tour_id, 'hks_destination' );
		$quote_location = $campaign_id ? 'campaign_sidebar' : 'tour_sidebar';
		$quote          = do_blocks( sprintf( '<!-- wp:hks/quote-cta {"location":"%s","label":"Request a quote"} /-->', $quote_location ) );

		ob_start();
		?>
		<section class="hks-tour-workspace hks-shell" data-hks-tour-id="<?php echo esc_attr( (string) $tour_id ); ?>">
			<?php if ( $images || $destinations || $route ) : ?>
				<div class="hks-tour-media">
					<?php if ( $images ) : ?>
						<?php self::render_gallery( $images, $title ); ?>
					<?php endif; ?>
					<?php if ( $destinations || $route ) : ?>
						<p class="hks-tour-lead__route"><?php echo esc_html( implode( ' · ', array_filter( array( implode( ', ', $destinations ), $route ) ) ) ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $facts ) : ?>
				<dl class="hks-tour-facts" aria-label="<?php esc_attr_e( 'Tour facts', 'hks-wayfinder' ); ?>">
					<?php foreach ( $facts as $label => $value ) : ?><div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div><?php endforeach; ?>
				</dl>
			<?php endif; ?>

			<aside class="hks-tour-quote" aria-label="<?php esc_attr_e( 'Request a quote', 'hks-wayfinder' ); ?>">
				<div class="hks-tour-quote__panel" data-hks-primary-quote>
					<p class="hks-tour-quote__label"><?php esc_html_e( 'Plan this trip', 'hks-wayfinder' ); ?></p>
					<h2><?php esc_html_e( 'Request a tailored quote', 'hks-wayfinder' ); ?></h2>
					<?php if ( $price ) : ?><p class="hks-tour-quote__price"><?php echo esc_html( $price['label'] ); ?></p><p class="hks-tour-quote__price-note"><?php echo esc_html( $price['status'] ); ?></p><?php endif; ?>
					<ul class="hks-tour-quote__reassurances">
						<li><span aria-hidden="true">✅</span><span><?php esc_html_e( 'Inclusions and exclusions clarified', 'hks-wayfinder' ); ?></span></li>
						<li><span aria-hidden="true">✅</span><span><?php esc_html_e( 'No booking commitment required', 'hks-wayfinder' ); ?></span></li>
						<li><span aria-hidden="true">✅</span><span><?php esc_html_e( 'Fast Responses to all queries', 'hks-wayfinder' ); ?></span></li>
					</ul>
					<?php echo $quote; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted server-rendered block. ?>
					<p class="hks-tour-quote__note"><?php esc_html_e( 'Tell us your dates and group size, check the prepared message, then choose WhatsApp or email.', 'hks-wayfinder' ); ?></p>
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
							<?php if ( $inclusions || $exclusions ) : ?><div class="hks-list-columns"><?php self::render_item_list( __( 'Included', 'hks-wayfinder' ), $inclusions, 'included' ); ?><?php self::render_item_list( __( 'Not included', 'hks-wayfinder' ), $exclusions, 'excluded' ); ?></div><?php else : ?><p><?php esc_html_e( 'Inclusions and exclusions are not listed yet. Ask us to confirm the current package breakdown in your quote.', 'hks-wayfinder' ); ?></p><?php endif; ?>
						</div>
					</details>

					<details class="hks-tour-section" id="hks-tour-rates" data-hks-tour-section data-hks-section="important" data-hks-section-label="<?php echo esc_attr__( 'Important Information', 'hks-wayfinder' ); ?>">
						<summary><span><?php esc_html_e( 'Important Information', 'hks-wayfinder' ); ?></span></summary>
						<div class="hks-tour-section__content">
							<h2><?php esc_html_e( 'Important information', 'hks-wayfinder' ); ?></h2>
							<?php self::render_important_information( $policies, $faqs ); ?>
						</div>
					</details>
			</div>
		</section>

		<?php self::render_related_tours( $tour_id ); ?>
		<div class="hks-mobile-quote-bar"><button type="button" data-hks-quote-proxy><?php esc_html_e( 'Request a quote', 'hks-wayfinder' ); ?></button></div>
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
		$destinations = self::term_names( $tour_id, 'hks_destination' );
		$price        = self::tour_price_summary( $tour_id );
		$link        = get_permalink( $tour_id );

		ob_start();
		?>
		<article class="hks-tour-card<?php echo $has_image ? '' : ' hks-tour-card--no-image'; ?>">
			<?php if ( $has_image ) : ?><a class="hks-tour-card__media" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true"><?php echo wp_get_attachment_image( $image_id, 'medium_large', false, array( 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 700px) 100vw, 33vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core generates and escapes attachment markup. ?></a><?php endif; ?>
			<div class="hks-tour-card__body">
				<?php if ( $destinations ) : ?><p class="hks-tour-card__destination"><?php echo esc_html( implode( ', ', $destinations ) ); ?></p><?php endif; ?>
				<h3><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( self::public_text( get_the_title( $tour_id ) ) ); ?></a></h3>
				<ul class="hks-tour-card__facts" aria-label="<?php esc_attr_e( 'Tour summary', 'hks-wayfinder' ); ?>">
					<?php if ( $duration ) : ?><li><?php echo esc_html( $duration ); ?></li><?php endif; ?>
					<?php if ( $departure ) : ?><li><?php echo esc_html( sprintf( __( 'From %s', 'hks-wayfinder' ), $departure ) ); ?></li><?php endif; ?>
				</ul>
				<?php if ( $route ) : ?><p class="hks-tour-card__route"><?php echo esc_html( $route ); ?></p><?php endif; ?>
				<div class="hks-tour-card__footer">
					<?php if ( $price ) : ?><strong class="hks-tour-card__price"><?php echo esc_html( $price['label'] ); ?></strong><?php endif; ?>
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

		$summary     = self::public_text( self::field( 'hks_short_summary', $term ) );
		$overview    = self::public_html( self::field( 'hks_overview', $term ) );
		$image_id    = absint( self::field( 'hks_hero_image', $term ) );
		$tours_url   = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );
		$title       = function_exists( 'hks_wayfinder_taxonomy_archive_title' ) ? hks_wayfinder_taxonomy_archive_title( $term ) : sprintf( __( 'Tours in %s', 'hks-wayfinder' ), $term->name );
		$lead        = function_exists( 'hks_wayfinder_taxonomy_archive_description' ) ? hks_wayfinder_taxonomy_archive_description( $term ) : sprintf( __( 'Explore tours in the %s destination.', 'hks-wayfinder' ), $term->name );
		$description = trim( $lead . ( $summary ? ' ' . $summary : '' ) );

		if ( ! self::media_allowed( $image_id ) ) {
			$image_id = 0;
		}

		ob_start();
		?>
		<section class="hks-destination-intro<?php echo $image_id ? ' hks-destination-intro--with-image' : ''; ?>">
			<div class="hks-title-band"><div class="hks-shell"><?php self::breadcrumbs( array( __( 'Tours', 'hks-wayfinder' ) => $tours_url, $term->name => '' ) ); ?><p class="hks-taxonomy-intro__label"><?php esc_html_e( 'Destination', 'hks-wayfinder' ); ?></p><h1><?php echo esc_html( $title ); ?></h1><p><?php echo esc_html( $description ); ?></p></div></div>
			<?php if ( $image_id || $overview ) : ?><div class="hks-shell hks-destination-intro__body"><?php if ( $overview ) : ?><div class="hks-prose"><?php echo wp_kses_post( $overview ); ?></div><?php endif; ?><?php if ( $image_id ) : ?><figure><?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'loading' => 'eager', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core generates and escapes attachment markup. ?><?php self::render_credit( $image_id ); ?></figure><?php endif; ?></div><?php endif; ?>
		</section>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render the title band for a public Tour classification archive.
	 *
	 * @return string
	 */
	public static function render_taxonomy_intro(): string {
		$term   = get_queried_object();
		$labels = array(
			'hks_tour_scope'   => __( 'Tour scope', 'hks-wayfinder' ),
			'hks_tour_type'    => __( 'Tour type', 'hks-wayfinder' ),
			'hks_occasion'     => __( 'Occasion', 'hks-wayfinder' ),
			'hks_travel_style' => __( 'Travel style', 'hks-wayfinder' ),
		);

		if ( ! $term instanceof \WP_Term || ! isset( $labels[ $term->taxonomy ] ) ) {
			return '';
		}

		$term_name   = self::public_text( $term->name );
		$title       = function_exists( 'hks_wayfinder_taxonomy_archive_title' ) ? hks_wayfinder_taxonomy_archive_title( $term ) : $term_name;
		$lead        = function_exists( 'hks_wayfinder_taxonomy_archive_description' ) ? hks_wayfinder_taxonomy_archive_description( $term ) : '';
		$term_detail = self::public_text( $term->description );
		$description = trim( $lead . ( $term_detail ? ' ' . $term_detail : '' ) );
		$tours_url   = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );

		if ( '' === $term_name ) {
			return '';
		}

		ob_start();
		?>
		<section class="hks-taxonomy-intro">
			<div class="hks-title-band">
				<div class="hks-shell">
					<?php self::breadcrumbs( array( __( 'Tours', 'hks-wayfinder' ) => $tours_url, $term_name => '' ) ); ?>
					<p class="hks-taxonomy-intro__label"><?php echo esc_html( $labels[ $term->taxonomy ] ); ?></p>
					<h1><?php echo esc_html( $title ); ?></h1>
					<p><?php echo esc_html( $description ); ?></p>
				</div>
			</div>
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
		$tours_url      = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );
		$group_url      = function_exists( 'hks_wayfinder_published_page_url' ) ? hks_wayfinder_published_page_url( 'group-travel' ) : '';
		$group_url      = $group_url ?: home_url( '/group-travel/' );
		$priority_tours = self::tour_query( 18, true );
		$featured       = array_slice( $priority_tours, 0, 6 );
		$destinations   = function_exists( 'hks_wayfinder_populated_terms' ) ? hks_wayfinder_populated_terms( 'hks_destination', 6 ) : array();
		$types          = function_exists( 'hks_wayfinder_populated_terms' ) ? hks_wayfinder_populated_terms( 'hks_tour_type', 8 ) : array();
		$occasions      = function_exists( 'hks_wayfinder_populated_terms' ) ? hks_wayfinder_populated_terms( 'hks_occasion', 8 ) : array();
		$hero_tours     = self::home_featured_tours( 5 );
		$active_tour    = $hero_tours[0] ?? null;

		ob_start();
		?>
		<div class="hks-home">
			<?php if ( $active_tour ) : ?>
				<section class="hks-home-hero hks-home-hero--featured" aria-labelledby="hks-home-title">
					<div class="hks-home-gallery<?php echo count( $hero_tours ) < 2 ? ' is-static' : ''; ?>" data-hks-home-gallery data-hks-gallery-interval="5000" role="region" aria-roledescription="carousel" aria-labelledby="hks-home-title" aria-describedby="hks-home-gallery-instructions">
						<p class="hks-sr-only" id="hks-home-gallery-instructions"><?php esc_html_e( 'Featured tours. Select a preview, swipe or drag the card queue, use the previous and next buttons, or use the left and right arrow keys while the gallery is focused.', 'hks-wayfinder' ); ?></p>
						<figure class="hks-home-gallery__stage" data-hks-home-gallery-stage>
							<?php
							echo wp_get_attachment_image(
								$active_tour['image_id'],
								'full',
								false,
								array(
									'loading'       => 'eager',
									'fetchpriority' => 'high',
									'decoding'      => 'async',
									'sizes'         => '100vw',
									'draggable'     => 'false',
									'alt'           => '',
									'data-hks-home-gallery-active-image' => '',
								)
							); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core generates and escapes attachment markup.
							?>
						</figure>
						<div class="hks-home-gallery__wash" aria-hidden="true"></div>
						<span class="hks-home-gallery__progress" data-hks-home-gallery-progress aria-hidden="true"></span>
						<div class="hks-shell hks-home-gallery__inner">
							<div class="hks-home-gallery__copy" data-hks-home-gallery-copy>
								<p class="hks-home-gallery__eyebrow" data-hks-home-gallery-eyebrow><?php echo esc_html( $active_tour['eyebrow'] ); ?></p>
								<h1 id="hks-home-title" data-hks-home-gallery-title><?php echo esc_html( $active_tour['caption'] ); ?></h1>
								<dl class="hks-home-gallery__details" data-hks-home-gallery-details aria-label="<?php esc_attr_e( 'Active tour details', 'hks-wayfinder' ); ?>" <?php echo array_filter( array( $active_tour['price'], $active_tour['route'], $active_tour['included'] ) ) ? '' : 'hidden'; ?>>
									<div class="hks-home-gallery__detail" data-hks-home-gallery-detail-item <?php echo $active_tour['price'] ? '' : 'hidden'; ?>>
										<dt><?php esc_html_e( 'Price', 'hks-wayfinder' ); ?></dt>
										<dd data-hks-home-gallery-price><?php echo esc_html( $active_tour['price'] ); ?></dd>
									</div>
									<div class="hks-home-gallery__detail" data-hks-home-gallery-detail-item <?php echo $active_tour['route'] ? '' : 'hidden'; ?>>
										<dt><?php esc_html_e( 'Route', 'hks-wayfinder' ); ?></dt>
										<dd data-hks-home-gallery-route><?php echo esc_html( $active_tour['route'] ); ?></dd>
									</div>
									<div class="hks-home-gallery__detail" data-hks-home-gallery-detail-item <?php echo $active_tour['included'] ? '' : 'hidden'; ?>>
										<dt><?php esc_html_e( 'Included', 'hks-wayfinder' ); ?></dt>
										<dd data-hks-home-gallery-included><?php echo esc_html( $active_tour['included'] ); ?></dd>
									</div>
								</dl>
								<div class="hks-home-gallery__actions">
									<a class="hks-home-gallery__cta" href="<?php echo esc_url( $active_tour['url'] ); ?>" data-hks-home-gallery-link><?php esc_html_e( 'Click here to book tour', 'hks-wayfinder' ); ?></a>
									<a class="hks-home-gallery__cta hks-home-gallery__cta--destinations" href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'Click here to browse destinations', 'hks-wayfinder' ); ?></a>
								</div>
							</div>

							<div class="hks-home-gallery__queue" aria-label="<?php esc_attr_e( 'Choose a featured tour', 'hks-wayfinder' ); ?>">
								<button class="hks-home-gallery__arrow hks-home-gallery__arrow--previous" type="button" data-hks-home-gallery-prev aria-controls="hks-home-gallery-track" aria-label="<?php esc_attr_e( 'Show previous featured tour', 'hks-wayfinder' ); ?>"><span aria-hidden="true">&#8249;</span></button>
								<div class="hks-home-gallery__viewport" data-hks-home-gallery-track tabindex="0" id="hks-home-gallery-track">
									<?php foreach ( $hero_tours as $index => $hero_tour ) : ?>
										<button
											class="hks-home-gallery__slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
											type="button"
											data-hks-home-gallery-slide
											data-hks-tour-title="<?php echo esc_attr( $hero_tour['caption'] ); ?>"
											data-hks-tour-label="<?php echo esc_attr( $hero_tour['title'] ); ?>"
											data-hks-tour-eyebrow="<?php echo esc_attr( $hero_tour['eyebrow'] ); ?>"
											data-hks-tour-url="<?php echo esc_url( $hero_tour['url'] ); ?>"
											data-hks-tour-price="<?php echo esc_attr( $hero_tour['price'] ); ?>"
											data-hks-tour-route="<?php echo esc_attr( $hero_tour['route'] ); ?>"
											data-hks-tour-included="<?php echo esc_attr( $hero_tour['included'] ); ?>"
											aria-label="<?php echo esc_attr( sprintf( __( 'Show %s', 'hks-wayfinder' ), $hero_tour['title'] ) ); ?>"
										>
											<span class="hks-home-gallery__media">
												<?php
												echo wp_get_attachment_image(
													$hero_tour['image_id'],
													'full',
													false,
													array(
														'loading'   => $index < 2 ? 'eager' : 'lazy',
														'decoding'  => 'async',
														'sizes'     => '(min-width: 1280px) 11rem, (min-width: 768px) 10rem, 52vw',
														'draggable' => 'false',
														'alt'       => '',
													)
												); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core generates and escapes attachment markup.
												?>
											</span>
											<span class="hks-home-gallery__caption"><?php echo esc_html( $hero_tour['caption'] ); ?></span>
										</button>
									<?php endforeach; ?>
								</div>
								<button class="hks-home-gallery__arrow hks-home-gallery__arrow--next" type="button" data-hks-home-gallery-next aria-controls="hks-home-gallery-track" aria-label="<?php esc_attr_e( 'Show next featured tour', 'hks-wayfinder' ); ?>"><span aria-hidden="true">&#8250;</span></button>
								<div class="hks-home-gallery__controls">
									<button class="hks-home-gallery__pause" type="button" data-hks-home-gallery-pause aria-label="<?php esc_attr_e( 'Pause featured tour rotation', 'hks-wayfinder' ); ?>"><span data-hks-home-gallery-pause-icon aria-hidden="true">&#x23F8;</span></button>
									<span class="hks-sr-only" aria-live="polite" aria-atomic="true" data-hks-home-gallery-announcer></span>
								</div>
							</div>
						</div>
					</div>
				</section>
			<?php else : ?>
				<section class="hks-home-hero hks-home-hero--fallback" aria-labelledby="hks-home-title">
					<div class="hks-shell hks-home-hero__content">
						<h1 id="hks-home-title"><?php esc_html_e( 'Plan your next trip with Holiday Kenya Safaris.', 'hks-wayfinder' ); ?></h1>
						<a class="hks-button" href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'Explore all tours', 'hks-wayfinder' ); ?></a>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $destinations ) : ?>
				<section class="hks-home-section hks-home-section--mist" id="destinations" aria-labelledby="hks-destinations-title"><div class="hks-shell"><div class="hks-section-heading"><div><p><?php esc_html_e( 'Browse by destination', 'hks-wayfinder' ); ?></p><h2 id="hks-destinations-title"><?php esc_html_e( 'Choose the place first', 'hks-wayfinder' ); ?></h2></div></div><div class="hks-destination-grid"><?php foreach ( $destinations as $term ) : $url = hks_wayfinder_term_url( $term ); $image = self::destination_image( $term ); ?><a class="hks-destination-card<?php echo $image ? '' : ' hks-destination-card--no-image'; ?>" href="<?php echo esc_url( $url ); ?>"><?php if ( $image ) : ?><span class="hks-destination-card__media"><?php echo wp_get_attachment_image( $image, 'medium_large', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core generates and escapes attachment markup. ?></span><?php endif; ?><span class="hks-destination-card__body"><strong><?php echo esc_html( $term->name ); ?></strong><span><?php echo esc_html( sprintf( _n( '%s tour', '%s tours', $term->count, 'hks-wayfinder' ), number_format_i18n( $term->count ) ) ); ?></span></span></a><?php endforeach; ?></div></div></section>
			<?php endif; ?>

			<section class="hks-home-section hks-shell" aria-labelledby="hks-featured-title">
				<div class="hks-section-heading"><div><p><?php esc_html_e( 'Featured tours', 'hks-wayfinder' ); ?></p><h2 id="hks-featured-title"><?php esc_html_e( 'Start with a trip worth opening', 'hks-wayfinder' ); ?></h2></div><a href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'View all tours', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a></div>
				<?php if ( $featured ) : ?><div class="hks-tour-grid"><?php foreach ( $featured as $tour_post ) : $GLOBALS['post'] = $tour_post; setup_postdata( $tour_post ); echo self::render_tour_card(); endforeach; wp_reset_postdata(); ?></div><?php else : ?><p><?php esc_html_e( 'Featured tours are coming soon. Explore all tours to keep browsing.', 'hks-wayfinder' ); ?></p><?php endif; ?>
			</section>

			<?php if ( $types || $occasions ) : ?>
				<section class="hks-home-section hks-shell" aria-labelledby="hks-trip-type-title"><div class="hks-section-heading"><div><p><?php esc_html_e( 'Browse your way', 'hks-wayfinder' ); ?></p><h2 id="hks-trip-type-title"><?php esc_html_e( 'Start with the trip type or the occasion', 'hks-wayfinder' ); ?></h2></div></div><div class="hks-browse-groups"><?php if ( $types ) : ?><div><h3><?php esc_html_e( 'Trip type', 'hks-wayfinder' ); ?></h3><div class="hks-term-links"><?php self::render_term_links( $types ); ?></div></div><?php endif; ?><?php if ( $occasions ) : ?><div><h3><?php esc_html_e( 'Occasion', 'hks-wayfinder' ); ?></h3><div class="hks-term-links"><?php self::render_term_links( $occasions ); ?></div></div><?php endif; ?></div></section>
			<?php endif; ?>

			<section class="hks-operator-section"><div class="hks-shell hks-operator-section__grid"><div><p><?php esc_html_e( 'Your safari team', 'hks-wayfinder' ); ?></p><h2><?php esc_html_e( 'Holiday Kenya Safaris is operated by Ashford Tours & Travel.', 'hks-wayfinder' ); ?></h2></div><div><p><?php esc_html_e( 'Choose your trip with Holiday Kenya Safaris, then speak with the Ashford Tours & Travel team about your dates, group and quote.', 'hks-wayfinder' ); ?></p><a href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'Explore all tours', 'hks-wayfinder' ); ?><span aria-hidden="true">→</span></a></div></div></section>

			<section class="hks-home-section hks-shell" aria-labelledby="hks-quote-process-title"><div class="hks-section-heading"><div><p><?php esc_html_e( 'Request a quote', 'hks-wayfinder' ); ?></p><h2 id="hks-quote-process-title"><?php esc_html_e( 'Start with a trip, then tell us what you need', 'hks-wayfinder' ); ?></h2></div></div><ol class="hks-process-grid"><li><span>1</span><h3><?php esc_html_e( 'Choose your tour', 'hks-wayfinder' ); ?></h3><p><?php esc_html_e( 'Compare the route, itinerary and practical details.', 'hks-wayfinder' ); ?></p></li><li><span>2</span><h3><?php esc_html_e( 'Add your trip details', 'hks-wayfinder' ); ?></h3><p><?php esc_html_e( 'Share your preferred dates, group size and useful preferences.', 'hks-wayfinder' ); ?></p></li><li><span>3</span><h3><?php esc_html_e( 'Check your message', 'hks-wayfinder' ); ?></h3><p><?php esc_html_e( 'Review the prepared message, then choose WhatsApp or email when you are ready to send it.', 'hks-wayfinder' ); ?></p></li></ol></section>

			<section class="hks-group-route" id="group-travel"><div class="hks-shell hks-group-route__inner"><div><p><?php esc_html_e( 'Group travel', 'hks-wayfinder' ); ?></p><h2><?php esc_html_e( 'Planning for family, friends or colleagues?', 'hks-wayfinder' ); ?></h2><p><?php esc_html_e( 'Choose a tour, then tell us your group size, dates and departure town so we can prepare a relevant quote.', 'hks-wayfinder' ); ?></p></div><a class="hks-button" href="<?php echo esc_url( $group_url ); ?>"><?php esc_html_e( 'Plan group travel', 'hks-wayfinder' ); ?></a></div></section>

			<section class="hks-home-section hks-shell hks-proof-section" aria-labelledby="hks-proof-title"><div><p><?php esc_html_e( 'Compare with confidence', 'hks-wayfinder' ); ?></p><h2 id="hks-proof-title"><?php esc_html_e( 'Check the trip details before you request a quote', 'hks-wayfinder' ); ?></h2></div><ul><li><?php esc_html_e( 'Route, duration and departure context', 'hks-wayfinder' ); ?></li><li><?php esc_html_e( 'Any available day-by-day itinerary', 'hks-wayfinder' ); ?></li><li><?php esc_html_e( 'Any listed inclusions and exclusions', 'hks-wayfinder' ); ?></li><li><?php esc_html_e( 'Any available accommodation and transport details', 'hks-wayfinder' ); ?></li></ul></section>

			<section class="hks-final-cta"><div class="hks-shell"><h2><?php esc_html_e( 'Ready to narrow down the options?', 'hks-wayfinder' ); ?></h2><p><?php esc_html_e( 'Choose a tour, then use its WhatsApp quote button to tell us your dates and group size.', 'hks-wayfinder' ); ?></p><a class="hks-button" href="<?php echo esc_url( $tours_url ); ?>"><?php esc_html_e( 'Explore all tours', 'hks-wayfinder' ); ?></a></div></section>
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
			'hks_tour_scope'  => __( 'Tour scope', 'hks-wayfinder' ),
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

		$archive          = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );
		$drawer_id        = wp_unique_id( 'hks-catalogue-filter-drawer-' );
		$drawer_title_id  = $drawer_id . '-title';
		$sidebar_title_id = wp_unique_id( 'hks-catalogue-filter-sidebar-title-' );

		ob_start();
		?>
		<div class="hks-catalogue-filter-shell" data-hks-catalogue-filters>
			<button
				class="hks-catalogue-filter-toggle"
				type="button"
				aria-controls="<?php echo esc_attr( $drawer_id ); ?>"
				aria-expanded="false"
				data-hks-filter-open
			>
				<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 5h16l-6.5 7.2v5.3l-3 1.5v-6.8L4 5Z"></path></svg>
				<span><?php esc_html_e( 'Filters', 'hks-wayfinder' ); ?></span>
			</button>

			<aside class="hks-catalogue-filter-sidebar" aria-labelledby="<?php echo esc_attr( $sidebar_title_id ); ?>">
				<h2 id="<?php echo esc_attr( $sidebar_title_id ); ?>"><?php esc_html_e( 'Filters', 'hks-wayfinder' ); ?></h2>
				<?php self::render_catalogue_filter_form( $available, $archive, 'sidebar' ); ?>
			</aside>

			<dialog
				class="hks-catalogue-filter-drawer"
				id="<?php echo esc_attr( $drawer_id ); ?>"
				aria-labelledby="<?php echo esc_attr( $drawer_title_id ); ?>"
				data-hks-filter-dialog
			>
				<div class="hks-catalogue-filter-drawer__header">
					<h2 id="<?php echo esc_attr( $drawer_title_id ); ?>"><?php esc_html_e( 'Filters', 'hks-wayfinder' ); ?></h2>
					<button type="button" aria-label="<?php esc_attr_e( 'Close filters', 'hks-wayfinder' ); ?>" data-hks-filter-close>
						<svg aria-hidden="true" viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"></path></svg>
					</button>
				</div>
				<?php self::render_catalogue_filter_form( $available, $archive, 'drawer' ); ?>
			</dialog>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render one catalogue filter form for the current viewport presentation.
	 *
	 * @param array<string,array{label:string,terms:array}> $available Available filter groups.
	 * @param string                                       $archive   Tour archive URL.
	 * @param string                                       $variant   Sidebar or drawer.
	 * @return void
	 */
	private static function render_catalogue_filter_form( array $available, string $archive, string $variant ): void {
		?>
		<form class="hks-catalogue-controls hks-catalogue-controls--<?php echo esc_attr( $variant ); ?>" method="get" action="<?php echo esc_url( $archive ); ?>" data-hks-catalogue-controls="<?php echo esc_attr( $variant ); ?>">
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
	}

	/**
	 * Render an approved Tour gallery and native-dialog lightbox.
	 *
	 * @param int[]  $images Image IDs.
	 * @param string $title  Tour title.
	 * @return void
	 */
	private static function render_gallery( array $images, string $title ): void {
		$count                     = count( $images );
		$initial_image_id          = $images[0];
		$desktop_thumbnail_limit   = 6;
		$additional_desktop_images = max( 0, $count - $desktop_thumbnail_limit );
		?>
		<div class="hks-tour-gallery hks-tour-gallery--<?php echo esc_attr( (string) min( 3, $count ) ); ?>" data-hks-gallery data-hks-gallery-interval="5000">
			<div class="hks-tour-gallery__grid<?php echo 1 === $count ? ' hks-tour-gallery__grid--single' : ''; ?>">
				<?php if ( $count > 1 ) : ?>
					<div class="hks-tour-gallery__thumbnails" role="group" aria-label="<?php esc_attr_e( 'Choose a gallery image', 'hks-wayfinder' ); ?>">
						<?php
						foreach ( $images as $index => $image_id ) :
							$stage_src    = wp_get_attachment_image_url( $image_id, 'large' ) ?: wp_get_attachment_url( $image_id );
							$stage_srcset = wp_get_attachment_image_srcset( $image_id, 'large' ) ?: '';
							$stage_alt    = trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) );
							$stage_label  = sprintf( __( 'Open image %1$s of %2$s for %3$s', 'hks-wayfinder' ), $index + 1, $count, $title );
							$is_more_thumbnail = $additional_desktop_images > 0 && ( $desktop_thumbnail_limit - 1 ) === $index;
							$is_desktop_overflow = $index >= $desktop_thumbnail_limit;
							$thumbnail_class = 'hks-tour-gallery__thumbnail';
							$thumbnail_label = sprintf( __( 'Show image %1$s of %2$s for %3$s', 'hks-wayfinder' ), $index + 1, $count, $title );

							if ( $is_more_thumbnail ) {
								$thumbnail_class .= ' hks-tour-gallery__thumbnail--more';
								$thumbnail_label  = sprintf(
									/* translators: 1: image position, 2: gallery image count, 3: Tour title, 4: number of later images. */
									_n( 'View image %1$s of %2$s for %3$s; %4$s additional image follows', 'View image %1$s of %2$s for %3$s; %4$s additional images follow', $additional_desktop_images, 'hks-wayfinder' ),
									$index + 1,
									$count,
									$title,
									number_format_i18n( $additional_desktop_images )
								);
							}

							if ( $is_desktop_overflow ) {
								$thumbnail_class .= ' hks-tour-gallery__thumbnail--desktop-overflow';
							}
							?>
							<button
								type="button"
								class="<?php echo esc_attr( $thumbnail_class ); ?>"
								data-hks-gallery-thumb="<?php echo esc_attr( (string) $index ); ?>"
								<?php if ( $is_more_thumbnail ) : ?>data-hks-gallery-more-open="<?php echo esc_attr( (string) $index ); ?>"<?php endif; ?>
								data-hks-gallery-stage-src="<?php echo esc_url( $stage_src ); ?>"
								data-hks-gallery-stage-srcset="<?php echo esc_attr( $stage_srcset ); ?>"
								data-hks-gallery-stage-alt="<?php echo esc_attr( $stage_alt ); ?>"
								data-hks-gallery-stage-label="<?php echo esc_attr( $stage_label ); ?>"
								aria-label="<?php echo esc_attr( $thumbnail_label ); ?>"
								aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>"
							>
								<?php echo wp_get_attachment_image( $image_id, 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy', 'fetchpriority' => 'low', 'decoding' => 'async', 'sizes' => '112px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core generates and escapes attachment markup. ?>
								<?php if ( $is_more_thumbnail ) : ?>
									<span class="hks-tour-gallery__thumbnail-more" aria-hidden="true">
										<strong>+<?php echo esc_html( number_format_i18n( $additional_desktop_images ) ); ?></strong>
										<span><?php echo esc_html( _n( 'more image', 'more images', $additional_desktop_images, 'hks-wayfinder' ) ); ?></span>
									</span>
								<?php endif; ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<div class="hks-tour-gallery__stage-wrap">
					<button type="button" class="hks-tour-gallery__stage" data-hks-gallery-stage data-hks-gallery-open="0" aria-label="<?php echo esc_attr( sprintf( __( 'Open image %1$s of %2$s for %3$s', 'hks-wayfinder' ), 1, $count, $title ) ); ?>"><?php echo wp_get_attachment_image( $initial_image_id, 'large', false, array( 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '(max-width: 56rem) calc(100vw - 2rem), (max-width: 80rem) 54vw, 760px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core generates and escapes attachment markup. ?></button>
					<?php if ( $count > 1 ) : ?>
						<button type="button" class="hks-tour-gallery__nav hks-tour-gallery__nav--prev" data-hks-gallery-stage-prev aria-label="<?php esc_attr_e( 'Show previous gallery image', 'hks-wayfinder' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg></button>
						<button type="button" class="hks-tour-gallery__nav hks-tour-gallery__nav--next" data-hks-gallery-stage-next aria-label="<?php esc_attr_e( 'Show next gallery image', 'hks-wayfinder' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 6 6 6-6 6"></path></svg></button>
					<?php endif; ?>
					<button type="button" class="hks-tour-gallery__view" data-hks-gallery-view data-hks-gallery-open="0"><?php esc_html_e( 'View gallery', 'hks-wayfinder' ); ?><span><?php echo esc_html( sprintf( _n( '%s image', '%s images', $count, 'hks-wayfinder' ), number_format_i18n( $count ) ) ); ?></span></button>
				</div>
			</div>
			<dialog class="hks-gallery-lightbox" data-hks-gallery-dialog aria-label="<?php echo esc_attr( sprintf( __( '%s image gallery', 'hks-wayfinder' ), $title ) ); ?>">
				<div class="hks-gallery-lightbox__bar"><span data-hks-gallery-counter></span><button type="button" data-hks-gallery-close aria-label="<?php esc_attr_e( 'Close gallery', 'hks-wayfinder' ); ?>">×</button></div>
				<div class="hks-gallery-lightbox__slides"><?php foreach ( $images as $index => $image_id ) : ?><figure data-hks-gallery-slide <?php echo 0 === $index ? '' : 'hidden'; ?>><?php echo wp_get_attachment_image( $image_id, 'full', false, array( 'loading' => 'lazy', 'fetchpriority' => 'low', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core generates and escapes attachment markup. ?><?php self::render_credit( $image_id ); ?></figure><?php endforeach; ?></div>
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
			echo '<p>' . esc_html__( 'A day-by-day itinerary is not listed yet. Ask us to confirm the current plan in your quote.', 'hks-wayfinder' ) . '</p>';
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
	 * Render public package notes and published FAQs.
	 *
	 * @param string[]                          $policies Public package notes.
	 * @param array<int, array<string, string>> $faqs     Published FAQs.
	 * @return void
	 */
	private static function render_important_information( array $policies, array $faqs ): void {
		if ( ! $policies && ! $faqs ) {
			echo '<p>' . esc_html__( 'No extra trip notes are listed yet. Ask us to confirm the details that matter to your group.', 'hks-wayfinder' ) . '</p>';
			return;
		}
		?>
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
		$args     = array(
			'post_type'           => 'hks_tour',
			'post_status'         => 'publish',
			'posts_per_page'      => 3,
			'post__not_in'        => array( $tour_id ),
			'orderby'             => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		);

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
		$inclusions = array_filter( array_map(
			static fn( $row ) => self::public_text( $row['item'] ?? '' ),
			self::rows( self::field( 'hks_inclusions', $tour_id ) )
		) );

		$facts = array(
			__( 'Tour scope', 'hks-wayfinder' )    => implode( ', ', self::term_names( $tour_id, 'hks_tour_scope' ) ),
			__( 'Duration', 'hks-wayfinder' )      => self::public_text( self::field( 'hks_duration_label', $tour_id ) ),
			__( 'Starts in', 'hks-wayfinder' )     => self::public_text( self::field( 'hks_start_location', $tour_id ) ),
			__( 'Ends in', 'hks-wayfinder' )       => self::public_text( self::field( 'hks_end_location', $tour_id ) ),
			__( 'Route', 'hks-wayfinder' )         => self::public_text( self::field( 'hks_route_summary', $tour_id ) ),
			__( 'Transport', 'hks-wayfinder' )     => self::transport_labels( self::field( 'hks_transport_types', $tour_id ) ),
			__( 'Travel style', 'hks-wayfinder' )  => implode( ', ', self::term_names( $tour_id, 'hks_travel_style' ) ),
			__( 'Included', 'hks-wayfinder' )      => implode( ', ', $inclusions ),
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
		static $cache = array();

		if ( isset( $cache[ $tour_id ] ) ) {
			return $cache[ $tour_id ];
		}

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

		$cache[ $tour_id ] = $allowed;

		return $cache[ $tour_id ];
	}

	/**
	 * Lead the canonical gallery with the Campaign image when one is approved.
	 *
	 * @param int $campaign_id Campaign ID.
	 * @param int $tour_id     Linked Tour ID.
	 * @return int[]
	 */
	private static function campaign_images( int $campaign_id, int $tour_id ): array {
		static $cache = array();
		$cache_key    = $campaign_id . ':' . $tour_id;

		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		$campaign_image = absint( get_post_thumbnail_id( $campaign_id ) );
		$images         = self::tour_images( $tour_id );

		if ( self::media_allowed( $campaign_image ) ) {
			array_unshift( $images, $campaign_image );
		}

		$cache[ $cache_key ] = array_values( array_unique( $images ) );

		return $cache[ $cache_key ];
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
	 * Build the exact Featured Tour set used by the homepage hero.
	 *
	 * @param int $limit Maximum Tours.
	 * @return array<int, array<string, int|string>>
	 */
	private static function home_featured_tours( int $limit = 5 ): array {
		$posts = get_posts(
			array(
				'post_type'      => 'hks_tour',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				),
				'meta_query'     => array(
					array(
						'key'     => 'hks_featured',
						'value'   => '1',
						'compare' => '=',
					),
				),
			)
		);
		$tours = array();

		foreach ( $posts as $tour ) {
			$title    = self::public_text( get_the_title( $tour ) );
			$url      = get_permalink( $tour );
			$image_id = absint( get_post_thumbnail_id( $tour ) );

			if ( '' === $title || ! is_string( $url ) || '' === $url || ! self::hero_media_allowed( $image_id ) ) {
				continue;
			}

			$destination_names = self::term_names( $tour->ID, 'hks_destination' );
			$eyebrow           = implode( ', ', $destination_names );

			if ( '' === $eyebrow ) {
				$eyebrow = implode( ', ', self::term_names( $tour->ID, 'hks_tour_scope' ) );
			}

			$duration      = self::public_text( self::field( 'hks_duration_label', $tour->ID ) );
			$caption_parts = array_filter( array( $destination_names[0] ?? '', $duration ) );
			$caption       = $caption_parts ? implode( ' · ', $caption_parts ) : $title;
			$price         = self::tour_price_summary( $tour->ID );
			$route         = self::public_text( self::field( 'hks_route_summary', $tour->ID ) );
			$included      = self::hero_inclusions_summary( $tour->ID );

			$tours[] = array(
				'id'         => $tour->ID,
				'title'      => $title,
				'url'        => $url,
				'image_id'   => $image_id,
				'eyebrow'    => $eyebrow ?: __( 'Featured tour', 'hks-wayfinder' ),
				'caption'    => $caption,
				'price'      => $price['label'] ?? '',
				'route'      => $route,
				'included'   => $included,
			);

			if ( count( $tours ) >= $limit ) {
				break;
			}
		}

		return $tours;
	}

	/**
	 * Build a concise, sourced inclusion summary for the Featured Tour hero.
	 *
	 * @param int $tour_id Tour ID.
	 * @return string
	 */
	private static function hero_inclusions_summary( int $tour_id ): string {
		$items = array();

		foreach ( self::rows( self::field( 'hks_inclusions', $tour_id ) ) as $row ) {
			$item = self::public_text( $row['item'] ?? '' );
			if ( $item ) {
				$items[] = $item;
			}
		}

		if ( ! $items ) {
			return '';
		}

		$summary = implode( ' · ', array_slice( $items, 0, 2 ) );
		$more    = count( $items ) - 2;

		return 0 < $more
			? sprintf( __( '%1$s · +%2$d more', 'hks-wayfinder' ), $summary, $more )
			: $summary;
	}

	/**
	 * Check that a Featured Tour image is large enough for the full-bleed hero.
	 *
	 * Hero images are decorative because the Tour title and labeled controls
	 * provide the equivalent context, so native alt text is not an eligibility
	 * requirement here.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool
	 */
	private static function hero_media_allowed( int $attachment_id ): bool {
		if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
			return false;
		}

		$image = wp_get_attachment_image_src( $attachment_id, 'full' );

		return is_array( $image ) && isset( $image[1], $image[2] ) && 1200 <= (int) $image[1] && 675 <= (int) $image[2];
	}

	/**
	 * Get curated Tours, falling back to the newest available Tours.
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
		static $cache = array();

		$view_id = get_queried_object_id() ?: get_the_ID();
		if ( array_key_exists( $view_id, $cache ) ) {
			return $cache[ $view_id ];
		}

		$type    = get_post_type( $view_id );

		if ( 'hks_tour' === $type ) {
			$cache[ $view_id ] = array( 'view_id' => $view_id, 'tour_id' => $view_id, 'campaign_id' => 0 );
			return $cache[ $view_id ];
		}

		if ( 'hks_campaign' === $type ) {
			$tour_id = absint( self::field( 'hks_linked_tour', $view_id ) );
			if ( $tour_id && 'hks_tour' === get_post_type( $tour_id ) ) {
				$cache[ $view_id ] = array( 'view_id' => $view_id, 'tour_id' => $tour_id, 'campaign_id' => $view_id );
				return $cache[ $view_id ];
			}
		}

		$cache[ $view_id ] = null;

		return $cache[ $view_id ];
	}

	/**
	 * Build the optional Campaign-only per-person price line.
	 *
	 * @param int $campaign_id Campaign ID.
	 * @param int $tour_id     Linked Tour ID.
	 * @return array<string, string>
	 */
	private static function campaign_price_summary( int $campaign_id, int $tour_id ): array {
		$amount = absint( self::field( 'hks_campaign_from_price_ksh', $campaign_id ) );

		if ( ! $amount ) {
			return self::tour_price_summary( $tour_id );
		}

		return array(
			'label'  => sprintf( __( 'From KSh %s per person', 'hks-wayfinder' ), number_format_i18n( $amount, 0 ) ),
			'status' => __( 'Your quote confirms the final package for your dates and group.', 'hks-wayfinder' ),
		);
	}

	/**
	 * Build the optional Tour per-person starting-price line.
	 *
	 * @param int $tour_id Tour ID.
	 * @return array<string, string>
	 */
	private static function tour_price_summary( int $tour_id ): array {
		$amount = absint( self::field( 'hks_from_price_ksh', $tour_id ) );

		if ( ! $amount ) {
			return array();
		}

		return array(
			'label'  => sprintf( __( 'From KSh %s per person', 'hks-wayfinder' ), number_format_i18n( $amount, 0 ) ),
			'status' => __( 'Your quote confirms the final package for your dates and group.', 'hks-wayfinder' ),
		);
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
