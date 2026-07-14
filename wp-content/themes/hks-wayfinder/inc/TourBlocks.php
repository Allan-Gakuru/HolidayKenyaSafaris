<?php
/**
 * Fail-closed dynamic presentation blocks for HKS Tours and Campaigns.
 *
 * @package HKS_Wayfinder
 */

namespace HKS_Wayfinder;

defined( 'ABSPATH' ) || exit;

/**
 * Renders canonical Tour data without exposing unapproved facts or media.
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
			'tour-hero'         => 'render_tour_hero',
			'tour-details'      => 'render_tour_details',
			'tour-card'         => 'render_tour_card',
			'destination-intro' => 'render_destination_intro',
		);

		foreach ( $blocks as $directory => $callback ) {
			register_block_type(
				get_theme_file_path( 'blocks/' . $directory ),
				array( 'render_callback' => array( self::class, $callback ) )
			);
		}
	}

	/**
	 * Render the emotional lead and practical facts for a Tour or Campaign.
	 *
	 * @return string
	 */
	public static function render_tour_hero(): string {
		$context = self::tour_context();

		if ( ! $context ) {
			return '';
		}

		$tour_id      = $context['tour_id'];
		$campaign_id  = $context['campaign_id'];
		$title        = $campaign_id ? self::public_text( self::field( 'hks_hero_headline', $campaign_id ) ) : '';
		$introduction = $campaign_id ? self::public_html( self::field( 'hks_supporting_copy', $campaign_id ) ) : '';

		if ( '' === $title ) {
			$title = self::public_text( get_the_title( $context['view_id'] ) );
		}

		if ( '' === $introduction ) {
			$introduction = self::public_text( get_post_field( 'post_excerpt', $tour_id ) );
		}

		$duration = self::public_text( self::field( 'hks_duration_label', $tour_id ) );
		$route    = self::public_text( self::field( 'hks_route_summary', $tour_id ) );
		$price    = self::price_summary( $tour_id );
		$image_id = get_post_thumbnail_id( $context['view_id'] );

		if ( ! self::media_allowed( $image_id ) ) {
			$image_id = get_post_thumbnail_id( $tour_id );
		}

		if ( ! self::media_allowed( $image_id ) ) {
			$image_id = 0;
		}

		$destinations = get_the_terms( $tour_id, 'hks_destination' );
		$eyebrow      = $campaign_id ? get_the_title( $tour_id ) : __( 'Holiday Kenya Safaris', 'hks-wayfinder' );

		if ( is_array( $destinations ) && $destinations ) {
			$eyebrow = $destinations[0]->name;
		}

		ob_start();
		?>
		<section class="hks-tour-hero<?php echo $image_id ? ' hks-tour-hero--with-image' : ''; ?>">
			<div class="hks-tour-hero__content">
				<p class="hks-kicker"><?php echo esc_html( self::public_text( $eyebrow ) ); ?></p>
				<h1><?php echo esc_html( $title ); ?></h1>
				<?php if ( $introduction ) : ?>
					<div class="hks-tour-hero__intro"><?php echo wp_kses_post( wpautop( $introduction ) ); ?></div>
				<?php endif; ?>

				<ul class="hks-fast-facts" aria-label="<?php esc_attr_e( 'Package summary', 'hks-wayfinder' ); ?>">
					<?php if ( $duration ) : ?><li><span><?php esc_html_e( 'Time', 'hks-wayfinder' ); ?></span><strong><?php echo esc_html( $duration ); ?></strong></li><?php endif; ?>
					<?php if ( $route ) : ?><li><span><?php esc_html_e( 'Route', 'hks-wayfinder' ); ?></span><strong><?php echo esc_html( $route ); ?></strong></li><?php endif; ?>
					<?php if ( $price ) : ?><li><span><?php esc_html_e( 'Rate', 'hks-wayfinder' ); ?></span><strong><?php echo esc_html( $price['label'] ); ?></strong></li><?php endif; ?>
				</ul>
				<?php if ( $price ) : ?>
					<p class="hks-hero-price-context"><?php echo esc_html( $price['status'] ); ?><?php if ( $price['basis'] ) : ?> <?php echo esc_html( $price['basis'] ); ?><?php endif; ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $image_id ) : ?>
				<figure class="hks-tour-hero__media">
					<?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'large', false, array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 800px) 100vw, 48vw' ) ) ); ?>
					<?php self::render_credit( $image_id ); ?>
				</figure>
			<?php endif; ?>
		</section>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render source-backed canonical Tour detail sections.
	 *
	 * @return string
	 */
	public static function render_tour_details(): string {
		$context = self::tour_context();

		if ( ! $context ) {
			return '';
		}

		$tour_id    = $context['tour_id'];
		$overview   = get_post_field( 'post_content', $tour_id );
		$price      = self::price_summary( $tour_id );
		$itinerary  = self::rows( self::field( 'hks_itinerary', $tour_id ) );
		$inclusions = self::rows( self::field( 'hks_inclusions', $tour_id ) );
		$exclusions = self::rows( self::field( 'hks_exclusions', $tour_id ) );
		$best_for   = self::public_text( self::field( 'hks_best_for', $tour_id ) );
		$stay       = self::public_text( self::field( 'hks_accommodation_basis', $tour_id ) );
		$meals      = self::public_text( self::field( 'hks_meals_summary', $tour_id ) );
		$transport  = self::transport_labels( self::field( 'hks_transport_types', $tour_id ) );
		$policies   = self::approved_policies( $tour_id );
		$faqs       = self::approved_faqs( $context );

		ob_start();
		?>
		<div class="hks-tour-details">
			<?php if ( self::public_text( $overview ) ) : ?>
				<section class="hks-tour-section hks-tour-section--intro" aria-labelledby="hks-overview-title">
					<p class="hks-kicker"><?php esc_html_e( 'The trip at a glance', 'hks-wayfinder' ); ?></p>
					<h2 id="hks-overview-title"><?php esc_html_e( 'A clear plan before you request the quote', 'hks-wayfinder' ); ?></h2>
					<div class="hks-prose"><?php echo wp_kses_post( do_blocks( $overview ) ); ?></div>
				</section>
			<?php endif; ?>

			<?php if ( $price ) : ?>
				<section class="hks-price-panel" aria-labelledby="hks-price-title">
					<div>
						<p class="hks-kicker"><?php esc_html_e( 'Price context', 'hks-wayfinder' ); ?></p>
						<h2 id="hks-price-title"><?php echo esc_html( $price['label'] ); ?></h2>
						<p class="hks-price-panel__status"><?php echo esc_html( $price['status'] ); ?></p>
					</div>
					<?php if ( $price['basis'] ) : ?><p><?php echo esc_html( $price['basis'] ); ?></p><?php endif; ?>
					<?php if ( $price['disclaimer'] ) : ?><p class="hks-small-print"><?php echo esc_html( $price['disclaimer'] ); ?></p><?php endif; ?>
				</section>
			<?php endif; ?>

			<?php if ( $itinerary ) : ?>
				<section class="hks-tour-section" aria-labelledby="hks-itinerary-title">
					<p class="hks-kicker"><?php esc_html_e( 'Day by day', 'hks-wayfinder' ); ?></p>
					<h2 id="hks-itinerary-title"><?php esc_html_e( 'Your itinerary', 'hks-wayfinder' ); ?></h2>
					<ol class="hks-itinerary">
						<?php foreach ( $itinerary as $day ) : ?>
							<?php $day_title = self::public_text( $day['day_title'] ?? '' ); ?>
							<?php if ( $day_title ) : ?>
								<li>
									<div class="hks-itinerary__marker"><?php echo esc_html( self::public_text( $day['day_number'] ?? '' ) ); ?></div>
									<div>
										<h3><?php echo esc_html( $day_title ); ?></h3>
										<?php if ( self::public_text( $day['description'] ?? '' ) ) : ?><p><?php echo esc_html( self::public_text( $day['description'] ) ); ?></p><?php endif; ?>
										<?php self::day_meta( $day ); ?>
									</div>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ol>
				</section>
			<?php endif; ?>

			<?php if ( $inclusions || $exclusions ) : ?>
				<section class="hks-tour-section" aria-labelledby="hks-inclusions-title">
					<p class="hks-kicker"><?php esc_html_e( 'Know what the quote covers', 'hks-wayfinder' ); ?></p>
					<h2 id="hks-inclusions-title"><?php esc_html_e( 'Included and not included', 'hks-wayfinder' ); ?></h2>
					<div class="hks-list-columns">
						<?php self::render_item_list( __( 'Included', 'hks-wayfinder' ), $inclusions, 'included' ); ?>
						<?php self::render_item_list( __( 'Not included', 'hks-wayfinder' ), $exclusions, 'excluded' ); ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $stay || $meals || $transport || $best_for ) : ?>
				<section class="hks-tour-section" aria-labelledby="hks-practical-title">
					<p class="hks-kicker"><?php esc_html_e( 'Practical detail', 'hks-wayfinder' ); ?></p>
					<h2 id="hks-practical-title"><?php esc_html_e( 'What to expect', 'hks-wayfinder' ); ?></h2>
					<dl class="hks-practical-grid">
						<?php self::definition( __( 'Accommodation basis', 'hks-wayfinder' ), $stay ); ?>
						<?php self::definition( __( 'Meals', 'hks-wayfinder' ), $meals ); ?>
						<?php self::definition( __( 'Transport options', 'hks-wayfinder' ), $transport ); ?>
						<?php self::definition( __( 'Best for', 'hks-wayfinder' ), $best_for ); ?>
					</dl>
				</section>
			<?php endif; ?>

			<?php if ( $policies ) : ?>
				<section class="hks-tour-section" aria-labelledby="hks-policies-title">
					<h2 id="hks-policies-title"><?php esc_html_e( 'Confirmed package notes', 'hks-wayfinder' ); ?></h2>
					<ul class="hks-detail-list">
						<?php foreach ( $policies as $policy ) : ?><li><?php echo esc_html( $policy ); ?></li><?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( $faqs ) : ?>
				<section class="hks-tour-section" aria-labelledby="hks-faq-title">
					<h2 id="hks-faq-title"><?php esc_html_e( 'Questions before you request a quote', 'hks-wayfinder' ); ?></h2>
					<div class="hks-faqs">
						<?php foreach ( $faqs as $faq ) : ?>
							<details><summary><?php echo esc_html( $faq['question'] ); ?></summary><div><?php echo wp_kses_post( $faq['answer'] ); ?></div></details>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<section class="hks-quote-process" aria-labelledby="hks-process-title">
				<p class="hks-kicker"><?php esc_html_e( 'A small next step', 'hks-wayfinder' ); ?></p>
				<h2 id="hks-process-title"><?php esc_html_e( 'Save the request, review it, then choose whether to send', 'hks-wayfinder' ); ?></h2>
				<ol><li><?php esc_html_e( 'Share the dates, group size and details needed for this package.', 'hks-wayfinder' ); ?></li><li><?php esc_html_e( 'The website saves your request privately and builds the WhatsApp message.', 'hks-wayfinder' ); ?></li><li><?php esc_html_e( 'Review the message, open WhatsApp and send it when you are ready.', 'hks-wayfinder' ); ?></li></ol>
			</section>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render one reusable catalogue card for the current Tour.
	 *
	 * @return string
	 */
	public static function render_tour_card(): string {
		$tour_id = get_the_ID();

		if ( 'hks_tour' !== get_post_type( $tour_id ) ) {
			return '';
		}

		$image_id = get_post_thumbnail_id( $tour_id );
		$duration = self::public_text( self::field( 'hks_duration_label', $tour_id ) );
		$route    = self::public_text( self::field( 'hks_route_summary', $tour_id ) );
		$price    = self::price_summary( $tour_id );
		$excerpt  = self::public_text( get_post_field( 'post_excerpt', $tour_id ) );
		$link     = get_permalink( $tour_id );

		ob_start();
		?>
		<article class="hks-tour-card">
			<?php if ( self::media_allowed( $image_id ) ) : ?>
				<a class="hks-tour-card__media" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true"><?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'medium_large', false, array( 'loading' => 'lazy', 'sizes' => '(max-width: 700px) 100vw, 33vw' ) ) ); ?></a>
			<?php endif; ?>
			<div class="hks-tour-card__body">
				<?php if ( $duration ) : ?><p class="hks-kicker"><?php echo esc_html( $duration ); ?></p><?php endif; ?>
				<h2><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( self::public_text( get_the_title( $tour_id ) ) ); ?></a></h2>
				<?php if ( $route ) : ?><p class="hks-tour-card__route"><?php echo esc_html( $route ); ?></p><?php endif; ?>
				<?php if ( $excerpt ) : ?><p><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
				<div class="hks-tour-card__footer">
					<?php if ( $price ) : ?><div class="hks-tour-card__price"><strong><?php echo esc_html( $price['label'] ); ?></strong><span><?php echo esc_html( $price['status'] ); ?></span><?php if ( $price['basis'] ) : ?><span><?php echo esc_html( $price['basis'] ); ?></span><?php endif; ?></div><?php endif; ?>
					<a href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'View trip details', 'hks-wayfinder' ); ?><span aria-hidden="true"> →</span></a>
				</div>
			</div>
		</article>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render audited Destination guidance, otherwise only safe taxonomy context.
	 *
	 * @return string
	 */
	public static function render_destination_intro(): string {
		$term = get_queried_object();

		if ( ! $term instanceof \WP_Term || 'hks_destination' !== $term->taxonomy ) {
			return '';
		}

		$audit_allowed = in_array( self::field( 'hks_source_status', $term ), array( 'reviewed', 'client_confirmed' ), true )
			&& self::public_text( self::field( 'hks_source_checked_date', $term ) )
			&& ( self::public_text( self::field( 'hks_source_url', $term ) ) || self::public_text( self::field( 'hks_source_reference', $term ) ) );
		$summary       = $audit_allowed ? self::public_text( self::field( 'hks_short_summary', $term ) ) : '';
		$overview      = $audit_allowed ? self::public_html( self::field( 'hks_overview', $term ) ) : '';
		$image_id      = $audit_allowed ? absint( self::field( 'hks_hero_image', $term ) ) : 0;

		if ( ! self::media_allowed( $image_id ) ) {
			$image_id = 0;
		}

		ob_start();
		?>
		<section class="hks-destination-intro<?php echo $image_id ? ' hks-destination-intro--with-image' : ''; ?>">
			<div>
				<p class="hks-kicker"><?php esc_html_e( 'Explore by destination', 'hks-wayfinder' ); ?></p>
				<h1><?php echo esc_html( self::public_text( $term->name ) ); ?></h1>
				<?php if ( $summary ) : ?><p class="hks-destination-intro__lead"><?php echo esc_html( $summary ); ?></p><?php else : ?><p class="hks-destination-intro__lead"><?php esc_html_e( 'Browse the currently published tour packages for this destination.', 'hks-wayfinder' ); ?></p><?php endif; ?>
				<?php if ( $overview ) : ?><div class="hks-prose"><?php echo wp_kses_post( $overview ); ?></div><?php endif; ?>
			</div>
			<?php if ( $image_id ) : ?><figure><?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'large', false, array( 'loading' => 'eager' ) ) ); ?><?php self::render_credit( $image_id ); ?></figure><?php endif; ?>
		</section>
		<?php

		return (string) ob_get_clean();
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
	 * Build a fail-closed public price view.
	 *
	 * @param int $tour_id Tour ID.
	 * @return array<string, string>|null
	 */
	private static function price_summary( int $tour_id ): ?array {
		$mode = self::field( 'hks_price_display_mode', $tour_id );

		if ( 'hidden' === $mode ) {
			return null;
		}

		if ( 'from_price' !== $mode ) {
			return array(
				'label'      => __( 'Request current KSh rate', 'hks-wayfinder' ),
				'status'     => __( 'Dates, group size, residency, vehicle and accommodation are confirmed in your quote.', 'hks-wayfinder' ),
				'basis'      => '',
				'disclaimer' => '',
			);
		}

		$amount     = absint( self::field( 'hks_from_price_ksh', $tour_id ) );
		$status     = self::field( 'hks_price_status', $tour_id );
		$checked    = self::public_text( self::field( 'hks_price_checked_date', $tour_id ) );
		$basis      = self::public_text( self::field( 'hks_price_basis_summary', $tour_id ) );
		$disclaimer = self::public_text( self::field( 'hks_price_disclaimer', $tour_id ) );
		$valid_until = self::public_text( self::field( 'hks_price_valid_until', $tour_id ) );
		$required   = array( 'hks_price_season_assumption', 'hks_price_residency_assumption', 'hks_price_group_size_assumption', 'hks_price_transport_assumption', 'hks_price_accommodation_assumption', 'hks_price_inclusions_assumption' );

		foreach ( $required as $field ) {
			if ( '' === self::public_text( self::field( $field, $tour_id ) ) ) {
				return self::request_rate_fallback();
			}
		}

		if ( ! $amount || ! in_array( $status, array( 'placeholder', 'operator_reviewed', 'client_confirmed' ), true ) || ! $checked || ! $basis || ! $disclaimer || ( $valid_until && $valid_until < gmdate( 'Y-m-d' ) ) ) {
			return self::request_rate_fallback();
		}

		$status_label = 'placeholder' === $status
			? __( 'Provisional from-price — confirm the current rate and assumptions in your quote.', 'hks-wayfinder' )
			: __( 'Source-reviewed from-price — final availability and total are confirmed in your quote.', 'hks-wayfinder' );

		return array(
			'label'      => sprintf( __( 'From KSh %s', 'hks-wayfinder' ), number_format_i18n( $amount, 0 ) ),
			'status'     => $status_label,
			'basis'      => $basis,
			'disclaimer' => $disclaimer,
		);
	}

	/**
	 * Safe fallback when a nominal from-price lacks its full assumptions.
	 *
	 * @return array<string, string>
	 */
	private static function request_rate_fallback(): array {
		return array(
			'label'      => __( 'Request current KSh rate', 'hks-wayfinder' ),
			'status'     => __( 'The website does not yet have a complete approved price basis for public display.', 'hks-wayfinder' ),
			'basis'      => '',
			'disclaimer' => '',
		);
	}

	/**
	 * Filter policy rows by source, status, date, and visible-content sentinel.
	 *
	 * @param int $tour_id Tour ID.
	 * @return string[]
	 */
	private static function approved_policies( int $tour_id ): array {
		$approved = array();

		foreach ( self::rows( self::field( 'hks_policies', $tour_id ) ) as $policy ) {
			$summary = self::public_text( $policy['public_summary'] ?? '' );
			$status  = $policy['confirmation_status'] ?? '';
			$checked = self::public_text( $policy['checked_date'] ?? '' );
			$source  = self::public_text( $policy['source_url'] ?? '' ) ?: self::public_text( $policy['source_reference'] ?? '' );
			$expiry  = self::public_text( $policy['valid_until'] ?? '' );

			if ( $summary && in_array( $status, array( 'operator_reviewed', 'client_confirmed' ), true ) && $checked && $source && ( ! $expiry || $expiry >= gmdate( 'Y-m-d' ) ) ) {
				$approved[] = $summary;
			}
		}

		return $approved;
	}

	/**
	 * Filter selected FAQs by publication, source envelope, and expiry.
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
			$faq_id   = absint( $faq_id );
			$question = self::public_text( get_the_title( $faq_id ) );
			$answer   = self::public_html( self::field( 'hks_faq_answer', $faq_id ) );
			$status   = self::field( 'hks_confirmation_status', $faq_id );
			$checked  = self::public_text( self::field( 'hks_checked_date', $faq_id ) );
			$source   = self::public_text( self::field( 'hks_source_url', $faq_id ) ) ?: self::public_text( self::field( 'hks_source_reference', $faq_id ) );
			$expiry   = self::public_text( self::field( 'hks_valid_until', $faq_id ) );

			if ( 'hks_faq' === get_post_type( $faq_id ) && 'publish' === get_post_status( $faq_id ) && $question && $answer && in_array( $status, array( 'operator_reviewed', 'client_confirmed' ), true ) && $checked && $source && ( ! $expiry || $expiry >= gmdate( 'Y-m-d' ) ) ) {
				$approved[] = array( 'question' => $question, 'answer' => $answer );
			}
		}

		return $approved;
	}

	/**
	 * Check an image's permission, website scope, review date, expiry, and credit.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool
	 */
	private static function media_allowed( int $attachment_id ): bool {
		if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
			return false;
		}

		$status  = self::field( 'hks_permission_status', $attachment_id );
		$scopes  = self::field( 'hks_usage_scopes', $attachment_id );
		$checked = self::public_text( self::field( 'hks_rights_checked_date', $attachment_id ) );
		$expiry  = self::public_text( self::field( 'hks_permission_expiry_date', $attachment_id ) );
		$credit  = self::public_text( self::field( 'hks_credit_line', $attachment_id ) );
		$alt     = self::public_text( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );

		return in_array( $status, array( 'operator_reviewed', 'client_confirmed' ), true )
			&& is_array( $scopes )
			&& in_array( 'website', $scopes, true )
			&& '' !== $checked
			&& '' !== $alt
			&& ( ! $expiry || $expiry >= gmdate( 'Y-m-d' ) )
			&& ( ! self::field( 'hks_credit_required', $attachment_id ) || '' !== $credit );
	}

	/**
	 * Render an approved required credit.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return void
	 */
	private static function render_credit( int $attachment_id ): void {
		if ( self::field( 'hks_credit_required', $attachment_id ) ) {
			$credit = self::public_text( self::field( 'hks_credit_line', $attachment_id ) );
			if ( $credit ) {
				echo '<figcaption>' . esc_html( $credit ) . '</figcaption>';
			}
		}
	}

	/**
	 * Render itinerary secondary facts.
	 *
	 * @param array<string, mixed> $day Itinerary row.
	 * @return void
	 */
	private static function day_meta( array $day ): void {
		$items = array(
			__( 'Activities', 'hks-wayfinder' )    => self::public_text( $day['activities'] ?? '' ),
			__( 'Accommodation', 'hks-wayfinder' ) => self::public_text( $day['accommodation'] ?? '' ),
			__( 'Meals', 'hks-wayfinder' )         => self::public_text( $day['meals'] ?? '' ),
		);

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
	 * @param array<int, array<string, mixed>> $rows    Rows.
	 * @param string                            $type    CSS modifier.
	 * @return void
	 */
	private static function render_item_list( string $heading, array $rows, string $type ): void {
		if ( ! $rows ) {
			return;
		}
		?>
		<div class="hks-detail-list hks-detail-list--<?php echo esc_attr( $type ); ?>">
			<h3><?php echo esc_html( $heading ); ?></h3>
			<ul>
				<?php foreach ( $rows as $row ) : ?>
					<?php if ( self::public_text( $row['item'] ?? '' ) ) : ?><li><strong><?php echo esc_html( self::public_text( $row['item'] ) ); ?></strong><?php if ( self::public_text( $row['detail'] ?? '' ) ) : ?><span><?php echo esc_html( self::public_text( $row['detail'] ) ); ?></span><?php endif; ?></li><?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render one definition only when it has a value.
	 *
	 * @param string $term  Term.
	 * @param string $value Value.
	 * @return void
	 */
	private static function definition( string $term, string $value ): void {
		if ( $value ) {
			echo '<div><dt>' . esc_html( $term ) . '</dt><dd>' . esc_html( $value ) . '</dd></div>';
		}
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
	 * @param string       $name   Field name.
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
