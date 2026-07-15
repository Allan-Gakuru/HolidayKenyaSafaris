<?php
/**
 * Server-rendered quote CTA and accessible dialog.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Conversion;

use HolidayKenyaSafaris\Core\Content\PostTypes\Campaign;
use HolidayKenyaSafaris\Core\Content\PostTypes\Tour;

defined( 'ABSPATH' ) || exit;

/**
 * Resolves canonical package context and renders a two-step inquiry journey.
 */
final class QuoteBlock {

	/**
	 * Temporary approved WhatsApp destination.
	 */
	private const WHATSAPP_NUMBER = '254722742799';

	/**
	 * Render a context-aware quote CTA.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return string
	 */
	public static function render( $attributes ) {
		$context = self::context();

		if ( ! $context ) {
			return '';
		}

		wp_enqueue_style( 'hks-inquiry' );
		wp_enqueue_script( 'hks-inquiry' );

		$instance_id = wp_unique_id( 'hks-inquiry-' );
		$location    = sanitize_key( $attributes['location'] ?? 'content' );
		$label       = sanitize_text_field( $attributes['label'] ?? '' );

		if ( '' !== $label ) {
			$context['cta_label'] = $label;
		}

		ob_start();
		?>
		<div
			class="hks-inquiry"
			data-hks-inquiry
			data-capture-endpoint="<?php echo esc_url( rest_url( InquiryRepository::REST_NAMESPACE . '/inquiries' ) ); ?>"
			data-launch-endpoint="<?php echo esc_url( rest_url( InquiryRepository::REST_NAMESPACE . '/inquiries/' ) ); ?>"
			data-whatsapp-number="<?php echo esc_attr( self::WHATSAPP_NUMBER ); ?>"
			data-tour-id="<?php echo esc_attr( $context['tour_id'] ); ?>"
			data-tour-slug="<?php echo esc_attr( $context['tour_slug'] ); ?>"
			data-campaign-id="<?php echo esc_attr( $context['campaign_id'] ); ?>"
			data-campaign-label="<?php echo esc_attr( $context['campaign_label'] ); ?>"
			data-page-type="<?php echo esc_attr( $context['page_type'] ); ?>"
			data-cta-location="<?php echo esc_attr( $location ); ?>"
		>
			<button class="hks-inquiry__trigger" type="button" data-hks-inquiry-open>
				<?php echo esc_html( $context['cta_label'] ); ?>
			</button>

			<dialog class="hks-inquiry__dialog" data-hks-inquiry-dialog aria-labelledby="<?php echo esc_attr( $instance_id ); ?>-title">
				<div class="hks-inquiry__panel">
					<button class="hks-inquiry__close" type="button" data-hks-inquiry-close aria-label="<?php esc_attr_e( 'Close quote form', 'hks-core' ); ?>">
						<span aria-hidden="true">&times;</span>
					</button>

					<div data-hks-form-step>
						<p class="hks-inquiry__eyebrow"><?php esc_html_e( 'Step 1 of 2 · Your trip', 'hks-core' ); ?></p>
						<h2 id="<?php echo esc_attr( $instance_id ); ?>-title"><?php esc_html_e( 'Tell us what to quote', 'hks-core' ); ?></h2>
						<p class="hks-inquiry__intro"><?php esc_html_e( 'Share the essentials now. You will review the full message before WhatsApp opens.', 'hks-core' ); ?></p>

						<form class="hks-inquiry__form" data-hks-inquiry-form novalidate>
							<input type="hidden" name="tour_id" value="<?php echo esc_attr( $context['tour_id'] ); ?>">
							<input type="hidden" name="campaign_id" value="<?php echo esc_attr( $context['campaign_id'] ); ?>">
							<input type="hidden" name="form_token" value="<?php echo esc_attr( FormToken::issue( $context['tour_id'], $context['campaign_id'] ) ); ?>">
							<input type="hidden" name="request_key" value="">
							<input type="hidden" name="started_at" value="">
							<input type="hidden" name="consent_version" value="<?php echo esc_attr( InquiryRepository::CONSENT_VERSION ); ?>">

							<div class="hks-inquiry__honeypot" aria-hidden="true">
								<label for="<?php echo esc_attr( $instance_id ); ?>-website">Website</label>
								<input id="<?php echo esc_attr( $instance_id ); ?>-website" type="text" name="website" tabindex="-1" autocomplete="off">
							</div>

							<div class="hks-inquiry__package" aria-label="<?php esc_attr_e( 'Selected package', 'hks-core' ); ?>">
								<span><?php esc_html_e( 'Package', 'hks-core' ); ?></span>
								<strong data-hks-package-label><?php echo esc_html( $context['package_label'] ); ?></strong>
							</div>

							<div class="hks-inquiry__grid">
								<?php self::text_input( $instance_id, 'name', __( 'Your name', 'hks-core' ), 'text', 'name', true ); ?>
								<?php self::text_input( $instance_id, 'phone', __( 'Phone number', 'hks-core' ), 'tel', 'tel', true, __( 'e.g. 0722 000 000', 'hks-core' ) ); ?>
								<?php self::text_input( $instance_id, 'preferred_date', __( 'Preferred date or month', 'hks-core' ), 'text', 'off', true, __( 'e.g. August 2026', 'hks-core' ) ); ?>
								<?php self::text_input( $instance_id, 'travelers', __( 'Number of travelers', 'hks-core' ), 'number', 'off', true, '', '1', '99' ); ?>
							</div>

							<?php self::optional_fields( $instance_id, $context['optional_questions'] ); ?>

							<div class="hks-inquiry__consent">
								<input id="<?php echo esc_attr( $instance_id ); ?>-consent" type="checkbox" name="contact_consent" value="1" required>
								<label for="<?php echo esc_attr( $instance_id ); ?>-consent"><?php esc_html_e( 'I agree that Holiday Kenya Safaris may use these details to respond to this quote request.', 'hks-core' ); ?></label>
							</div>

							<p class="hks-inquiry__save-note"><?php esc_html_e( 'When you continue, we save these details privately in WordPress so the team can recover your request if WhatsApp does not open. Nothing is marked as sent until you send it in WhatsApp.', 'hks-core' ); ?></p>
							<p class="hks-inquiry__status" data-hks-inquiry-status role="status" aria-live="polite"></p>
							<button class="hks-inquiry__submit" type="submit"><?php esc_html_e( 'Save & review WhatsApp message', 'hks-core' ); ?></button>
						</form>
					</div>

					<div class="hks-inquiry__review" data-hks-review-step hidden>
						<p class="hks-inquiry__eyebrow"><?php esc_html_e( 'Step 2 of 2 · Review', 'hks-core' ); ?></p>
						<h2><?php esc_html_e( 'Your request is saved', 'hks-core' ); ?></h2>
						<p><?php esc_html_e( 'Check the message below. The next button opens WhatsApp; you still choose whether to send it.', 'hks-core' ); ?></p>
						<p class="hks-inquiry__reference"><span><?php esc_html_e( 'Request reference', 'hks-core' ); ?></span> <strong data-hks-reference></strong></p>
						<pre class="hks-inquiry__message" data-hks-message tabindex="0"></pre>
						<div class="hks-inquiry__actions">
							<button class="hks-inquiry__back" type="button" data-hks-inquiry-back><?php esc_html_e( 'Edit details', 'hks-core' ); ?></button>
							<a class="hks-inquiry__launch" data-hks-whatsapp-launch href="#" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open WhatsApp to send', 'hks-core' ); ?></a>
						</div>
						<p class="hks-inquiry__send-note"><?php esc_html_e( 'Opening WhatsApp is not confirmation that the message was sent.', 'hks-core' ); ?></p>
					</div>
				</div>
			</dialog>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Resolve the current Tour or Campaign into canonical quote context.
	 *
	 * @return array<string, mixed>|null
	 */
	private static function context() {
		$post_id   = get_the_ID();
		$post_type = get_post_type( $post_id );

		if ( Tour::POST_TYPE === $post_type ) {
			$tour_id     = $post_id;
			$campaign_id = 0;
			$page_type   = 'tour';
		} elseif ( Campaign::POST_TYPE === $post_type ) {
			$tour_id     = absint( self::field( 'hks_linked_tour', $post_id ) );
			$campaign_id = $post_id;
			$page_type   = 'campaign';
		} else {
			return null;
		}

		if ( ! $tour_id || Tour::POST_TYPE !== get_post_type( $tour_id ) ) {
			return null;
		}

		$package_label = sanitize_text_field( self::field( 'hks_whatsapp_package_label', $tour_id ) );
		$cta_label     = $campaign_id ? sanitize_text_field( self::field( 'hks_cta_label', $campaign_id ) ) : '';

		if ( '' === $cta_label ) {
			$cta_label = sanitize_text_field( self::field( 'hks_cta_label', $tour_id ) );
		}

		if ( '' === $cta_label ) {
			$cta_label = __( 'Request quote on WhatsApp', 'hks-core' );
		}

		if ( '' === $package_label ) {
			$package_label = get_the_title( $tour_id );
		}

		$optional_questions = self::field( 'hks_intake_questions', $tour_id );

		return array(
			'tour_id'            => $tour_id,
			'tour_slug'          => get_post_field( 'post_name', $tour_id ),
			'campaign_id'        => $campaign_id,
			'campaign_label'     => $campaign_id ? sanitize_text_field( self::field( 'hks_analytics_campaign_label', $campaign_id ) ) : '',
			'page_type'          => $page_type,
			'package_label'      => $package_label,
			'cta_label'          => $cta_label,
			'optional_questions' => is_array( $optional_questions ) ? $optional_questions : array(),
		);
	}

	/**
	 * Render enabled package-specific questions.
	 *
	 * @param string   $instance_id       Unique block ID.
	 * @param string[] $optional_questions Enabled questions.
	 * @return void
	 */
	private static function optional_fields( $instance_id, $optional_questions ) {
		if ( ! $optional_questions ) {
			return;
		}
		?>
		<fieldset class="hks-inquiry__optional">
			<legend><?php esc_html_e( 'A few useful details', 'hks-core' ); ?></legend>
			<div class="hks-inquiry__grid">
				<?php if ( in_array( 'departure_town', $optional_questions, true ) ) : ?>
					<?php self::text_input( $instance_id, 'departure_town', __( 'Departure town', 'hks-core' ), 'text', 'address-level2' ); ?>
				<?php endif; ?>

				<?php if ( in_array( 'adults_children', $optional_questions, true ) ) : ?>
					<?php self::text_input( $instance_id, 'adults', __( 'Adults', 'hks-core' ), 'number', 'off', false, '', '0', '99' ); ?>
					<?php self::text_input( $instance_id, 'children', __( 'Children', 'hks-core' ), 'number', 'off', false, '', '0', '99' ); ?>
				<?php endif; ?>

				<?php if ( in_array( 'residency', $optional_questions, true ) ) : ?>
					<?php self::select_input( $instance_id, 'residency', __( 'Residency', 'hks-core' ), array( 'kenyan_citizen' => __( 'Kenyan citizen', 'hks-core' ), 'resident' => __( 'Kenyan resident', 'hks-core' ), 'non_resident' => __( 'Non-resident', 'hks-core' ), 'mixed' => __( 'Mixed group', 'hks-core' ), 'not_sure' => __( 'Not sure', 'hks-core' ) ) ); ?>
				<?php endif; ?>

				<?php if ( in_array( 'vehicle_preference', $optional_questions, true ) ) : ?>
					<?php self::select_input( $instance_id, 'vehicle_preference', __( 'Vehicle preference', 'hks-core' ), array( 'safari_van' => __( 'Safari van', 'hks-core' ), 'land_cruiser' => __( 'Land Cruiser', 'hks-core' ), 'no_preference' => __( 'No preference', 'hks-core' ), 'not_sure' => __( 'Not sure', 'hks-core' ) ) ); ?>
				<?php endif; ?>

				<?php if ( in_array( 'accommodation_preference', $optional_questions, true ) ) : ?>
					<?php self::text_input( $instance_id, 'accommodation_preference', __( 'Accommodation preference', 'hks-core' ), 'text', 'off', false, __( 'Room, lodge or comfort preference', 'hks-core' ) ); ?>
				<?php endif; ?>

				<?php if ( in_array( 'budget_range', $optional_questions, true ) ) : ?>
					<?php self::text_input( $instance_id, 'budget_range', __( 'Budget range', 'hks-core' ), 'text', 'off', false, __( 'Optional KSh range for the group', 'hks-core' ) ); ?>
				<?php endif; ?>
			</div>
		</fieldset>
		<?php
	}

	/**
	 * Render one text-like input.
	 *
	 * @param string $instance_id Unique block ID.
	 * @param string $name        Field name.
	 * @param string $label       Label.
	 * @param string $type        Input type.
	 * @param string $autocomplete Autocomplete token.
	 * @param bool   $required    Required state.
	 * @param string $placeholder Placeholder.
	 * @param string $min         Optional numeric minimum.
	 * @param string $max         Optional numeric maximum.
	 * @return void
	 */
	private static function text_input( $instance_id, $name, $label, $type, $autocomplete, $required = false, $placeholder = '', $min = '', $max = '' ) {
		?>
		<div class="hks-inquiry__field">
			<label for="<?php echo esc_attr( $instance_id . '-' . $name ); ?>"><?php echo esc_html( $label ); ?><?php if ( $required ) : ?> <span aria-hidden="true">*</span><?php endif; ?></label>
			<input
				id="<?php echo esc_attr( $instance_id . '-' . $name ); ?>"
				type="<?php echo esc_attr( $type ); ?>"
				name="<?php echo esc_attr( $name ); ?>"
				autocomplete="<?php echo esc_attr( $autocomplete ); ?>"
				<?php if ( $placeholder ) : ?>placeholder="<?php echo esc_attr( $placeholder ); ?>"<?php endif; ?>
				<?php if ( $required ) : ?>required<?php endif; ?>
				<?php if ( $min ) : ?>min="<?php echo esc_attr( $min ); ?>"<?php endif; ?>
				<?php if ( $max ) : ?>max="<?php echo esc_attr( $max ); ?>"<?php endif; ?>
			>
		</div>
		<?php
	}

	/**
	 * Render one optional select.
	 *
	 * @param string                $instance_id Unique block ID.
	 * @param string                $name        Field name.
	 * @param string                $label       Label.
	 * @param array<string, string> $options     Value/label options.
	 * @return void
	 */
	private static function select_input( $instance_id, $name, $label, $options ) {
		?>
		<div class="hks-inquiry__field">
			<label for="<?php echo esc_attr( $instance_id . '-' . $name ); ?>"><?php echo esc_html( $label ); ?></label>
			<select id="<?php echo esc_attr( $instance_id . '-' . $name ); ?>" name="<?php echo esc_attr( $name ); ?>">
				<option value=""><?php esc_html_e( 'Choose if useful', 'hks-core' ); ?></option>
				<?php foreach ( $options as $value => $option_label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $option_label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
	}

	/**
	 * Return an SCF value with metadata fallback.
	 *
	 * @param string $name    Field name.
	 * @param int    $post_id Post ID.
	 * @return mixed
	 */
	private static function field( $name, $post_id ) {
		if ( function_exists( 'get_field' ) ) {
			return get_field( $name, $post_id );
		}

		return get_post_meta( $post_id, $name, true );
	}
}
