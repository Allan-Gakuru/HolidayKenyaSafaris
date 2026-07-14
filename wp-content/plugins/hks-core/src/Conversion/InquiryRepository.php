<?php
/**
 * Anonymous inquiry capture and WhatsApp-open recovery state.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Conversion;

use HolidayKenyaSafaris\Core\Content\PostTypes\Campaign;
use HolidayKenyaSafaris\Core\Content\PostTypes\Inquiry;
use HolidayKenyaSafaris\Core\Content\PostTypes\Tour;

defined( 'ABSPATH' ) || exit;

/**
 * Stores a minimal private inquiry after an explicit visitor action.
 */
final class InquiryRepository {

	/**
	 * Public API namespace.
	 */
	public const REST_NAMESPACE = 'hks/v1';

	/**
	 * Form disclosure/consent contract version.
	 */
	public const CONSENT_VERSION = 'hks_inquiry_v1';

	/**
	 * Whitelisted attribution fields.
	 */
	private const ATTRIBUTION_KEYS = array(
		'utm_source',
		'utm_medium',
		'utm_campaign',
		'utm_content',
		'utm_term',
		'landing_path',
		'referrer_host',
	);

	/**
	 * Whitelisted optional field names.
	 */
	private const OPTIONAL_FIELDS = array(
		'departure_town',
		'adults',
		'children',
		'residency',
		'vehicle_preference',
		'accommodation_preference',
		'budget_range',
	);

	/**
	 * Register public capture routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/inquiries',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'capture' ),
				'permission_callback' => array( $this, 'allow_public_request' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/inquiries/(?P<request_key>[a-f0-9-]{36})/whatsapp-open',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'mark_whatsapp_opened' ),
				'permission_callback' => array( $this, 'allow_public_request' ),
			)
		);
	}

	/**
	 * Anonymous visitors may reach these signed, validated endpoints.
	 *
	 * @return true
	 */
	public function allow_public_request() {
		return true;
	}

	/**
	 * Validate and store an inquiry before the WhatsApp review step.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function capture( \WP_REST_Request $request ) {
		$payload = $request->get_json_params();

		if ( ! is_array( $payload ) ) {
			return $this->error( 'request', __( 'The quote request could not be read. Please try again.', 'hks-core' ) );
		}

		$tour_id     = absint( $payload['tour_id'] ?? 0 );
		$campaign_id = absint( $payload['campaign_id'] ?? 0 );

		if ( ! FormToken::verify( $payload['form_token'] ?? '', $tour_id, $campaign_id ) ) {
			return new \WP_Error(
				'hks_inquiry_token',
				__( 'This quote form has expired. Refresh the page and try again.', 'hks-core' ),
				array( 'status' => 403 )
			);
		}

		$context = $this->validate_context( $tour_id, $campaign_id );

		if ( is_wp_error( $context ) ) {
			return $context;
		}

		if ( ! $this->within_rate_limit() ) {
			return new \WP_Error(
				'hks_inquiry_rate_limit',
				__( 'Too many quote requests were received from this connection. Please wait a few minutes and try again.', 'hks-core' ),
				array( 'status' => 429 )
			);
		}

		if ( '' !== trim( (string) ( $payload['website'] ?? '' ) ) ) {
			return $this->response( 0, 'HKS-RECEIVED', $context['package_label'], 201 );
		}

		$started_at = (int) ( $payload['started_at'] ?? 0 );
		$now_ms     = (int) floor( microtime( true ) * 1000 );

		if ( $started_at <= 0 || $started_at > $now_ms || ( $now_ms - $started_at ) < 1200 ) {
			return new \WP_Error(
				'hks_inquiry_timing',
				__( 'Please take a moment to check the quote details, then try again.', 'hks-core' ),
				array( 'status' => 422 )
			);
		}

		$values = $this->validate_values( $payload, $context['allowed_questions'] );

		if ( is_wp_error( $values ) ) {
			return $values;
		}

		$request_key = strtolower( sanitize_text_field( $payload['request_key'] ?? '' ) );

		if ( 1 !== preg_match( '/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/', $request_key ) ) {
			return $this->error( 'request_key', __( 'The quote request reference is invalid. Refresh the page and try again.', 'hks-core' ) );
		}

		$inquiry_id = $this->find_by_request_key( $request_key );
		$meta       = $this->build_meta( $request_key, $context, $values );

		if ( $inquiry_id ) {
			if ( Inquiry::POST_TYPE !== get_post_type( $inquiry_id ) ) {
				return $this->error( 'request_key', __( 'The quote request reference is already in use.', 'hks-core' ) );
			}

			wp_update_post(
				array(
					'ID'          => $inquiry_id,
					'post_status' => 'private',
				)
			);
			$this->update_meta( $inquiry_id, $meta );
			$status = 200;
		} else {
			$inquiry_id = wp_insert_post(
				array(
					'post_type'   => Inquiry::POST_TYPE,
					'post_status' => 'private',
					'post_title'  => __( 'Quote inquiry', 'hks-core' ),
					'post_author' => 0,
					'meta_input'  => $meta,
				),
				true
			);

			if ( is_wp_error( $inquiry_id ) ) {
				return new \WP_Error(
					'hks_inquiry_save',
					__( 'We could not save your quote request. Please try again.', 'hks-core' ),
					array( 'status' => 500 )
				);
			}

			$status = 201;
		}

		$reference = self::reference( $inquiry_id );
		wp_update_post(
			array(
				'ID'         => $inquiry_id,
				'post_title' => sprintf(
					/* translators: %s: non-sensitive inquiry reference. */
					__( 'Quote inquiry %s', 'hks-core' ),
					$reference
				),
			)
		);

		return $this->response( $inquiry_id, $reference, $context['package_label'], $status );
	}

	/**
	 * Record that the website opened WhatsApp, never that a message was sent.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function mark_whatsapp_opened( \WP_REST_Request $request ) {
		$request_key = strtolower( sanitize_text_field( $request['request_key'] ) );
		$inquiry_id  = $this->find_by_request_key( $request_key );

		if ( ! $inquiry_id ) {
			return new \WP_Error( 'hks_inquiry_missing', __( 'Quote request not found.', 'hks-core' ), array( 'status' => 404 ) );
		}

		update_post_meta( $inquiry_id, '_hks_whatsapp_opened_at', current_time( 'mysql', true ) );

		$response = new \WP_REST_Response( array( 'recorded' => true ), 200 );
		$response->header( 'Cache-Control', 'no-store' );

		return $response;
	}

	/**
	 * Validate that public context resolves to a published Tour.
	 *
	 * @param int $tour_id     Tour ID.
	 * @param int $campaign_id Campaign ID.
	 * @return array<string, mixed>|\WP_Error
	 */
	private function validate_context( $tour_id, $campaign_id ) {
		if ( Tour::POST_TYPE !== get_post_type( $tour_id ) || 'publish' !== get_post_status( $tour_id ) ) {
			return $this->error( 'package', __( 'This package is not currently available for quote requests.', 'hks-core' ) );
		}

		if ( $campaign_id ) {
			$linked_tour = absint( $this->field( 'hks_linked_tour', $campaign_id ) );

			if (
				Campaign::POST_TYPE !== get_post_type( $campaign_id )
				|| 'publish' !== get_post_status( $campaign_id )
				|| $linked_tour !== $tour_id
			) {
				return $this->error( 'campaign', __( 'This campaign is not currently available for quote requests.', 'hks-core' ) );
			}
		}

		$package_label = sanitize_text_field( $this->field( 'hks_whatsapp_package_label', $tour_id ) );

		if ( '' === $package_label ) {
			$package_label = get_the_title( $tour_id );
		}

		$allowed_questions = $this->field( 'hks_intake_questions', $tour_id );

		return array(
			'tour_id'           => $tour_id,
			'campaign_id'       => $campaign_id,
			'package_label'     => $package_label,
			'allowed_questions' => is_array( $allowed_questions ) ? $allowed_questions : array(),
		);
	}

	/**
	 * Validate required, optional, consent, and attribution values.
	 *
	 * @param array<string, mixed> $payload           Request payload.
	 * @param string[]             $allowed_questions Enabled Tour questions.
	 * @return array<string, mixed>|\WP_Error
	 */
	private function validate_values( $payload, $allowed_questions ) {
		$name = $this->text( $payload['name'] ?? '', 100 );

		if ( strlen( $name ) < 2 ) {
			return $this->error( 'name', __( 'Enter your name.', 'hks-core' ) );
		}

		$phone = $this->text( $payload['phone'] ?? '', 30 );

		if ( 1 !== preg_match( '/^\+?[0-9][0-9\s().-]{6,24}$/', $phone ) ) {
			return $this->error( 'phone', __( 'Enter a valid phone number.', 'hks-core' ) );
		}

		$travel_date = $this->text( $payload['preferred_date'] ?? '', 80 );

		if ( strlen( $travel_date ) < 2 ) {
			return $this->error( 'preferred_date', __( 'Enter a preferred date or travel month.', 'hks-core' ) );
		}

		$travelers = absint( $payload['travelers'] ?? 0 );

		if ( $travelers < 1 || $travelers > 99 ) {
			return $this->error( 'travelers', __( 'Enter the number of travelers, from 1 to 99.', 'hks-core' ) );
		}

		if ( true !== ( $payload['contact_consent'] ?? false ) || self::CONSENT_VERSION !== ( $payload['consent_version'] ?? '' ) ) {
			return $this->error( 'contact_consent', __( 'Confirm that we may use these details to respond to this quote request.', 'hks-core' ) );
		}

		$values = array(
			'name'           => $name,
			'phone'          => $phone,
			'preferred_date' => $travel_date,
			'travelers'      => $travelers,
			'attribution'    => $this->attribution( $payload['attribution'] ?? array() ),
		);

		foreach ( self::OPTIONAL_FIELDS as $field ) {
			if ( ! $this->optional_field_allowed( $field, $allowed_questions ) ) {
				continue;
			}

			if ( in_array( $field, array( 'adults', 'children' ), true ) ) {
				$value = isset( $payload[ $field ] ) && '' !== $payload[ $field ] ? absint( $payload[ $field ] ) : '';
				$values[ $field ] = '' !== $value ? min( 99, $value ) : '';
			} else {
				$values[ $field ] = $this->text( $payload[ $field ] ?? '', 120 );
			}
		}

		if (
			isset( $values['adults'], $values['children'] )
			&& '' !== $values['adults']
			&& '' !== $values['children']
			&& ( $values['adults'] + $values['children'] ) !== $travelers
		) {
			return $this->error( 'travelers', __( 'The adult and child counts must add up to the total number of travelers.', 'hks-core' ) );
		}

		$choice_errors = array(
			'residency'          => array( 'kenyan_citizen', 'resident', 'non_resident', 'mixed', 'not_sure' ),
			'vehicle_preference' => array( 'safari_van', 'land_cruiser', 'no_preference', 'not_sure' ),
		);

		foreach ( $choice_errors as $field => $allowed ) {
			if ( ! empty( $values[ $field ] ) && ! in_array( $values[ $field ], $allowed, true ) ) {
				return $this->error( $field, __( 'Choose a valid option.', 'hks-core' ) );
			}
		}

		return $values;
	}

	/**
	 * Build protected post meta for an inquiry.
	 *
	 * @param string               $request_key Idempotency key.
	 * @param array<string, mixed> $context     Validated context.
	 * @param array<string, mixed> $values      Validated visitor values.
	 * @return array<string, mixed>
	 */
	private function build_meta( $request_key, $context, $values ) {
		$meta = array(
			'_hks_inquiry_request_key'     => $request_key,
			'_hks_inquiry_status'          => 'captured',
			'_hks_inquiry_name'            => $values['name'],
			'_hks_inquiry_phone'           => $values['phone'],
			'_hks_inquiry_preferred_date'  => $values['preferred_date'],
			'_hks_inquiry_travelers'       => $values['travelers'],
			'_hks_inquiry_tour_id'         => $context['tour_id'],
			'_hks_inquiry_campaign_id'     => $context['campaign_id'],
			'_hks_inquiry_package_label'   => $context['package_label'],
			'_hks_inquiry_attribution'     => wp_json_encode( $values['attribution'] ),
			'_hks_inquiry_consent_version' => self::CONSENT_VERSION,
			'_hks_inquiry_consent_at'      => current_time( 'mysql', true ),
		);

		foreach ( self::OPTIONAL_FIELDS as $field ) {
			if ( array_key_exists( $field, $values ) ) {
				$meta[ '_hks_inquiry_' . $field ] = $values[ $field ];
			}
		}

		return $meta;
	}

	/**
	 * Update each protected value without touching the WhatsApp-open timestamp.
	 *
	 * @param int                  $inquiry_id Inquiry post ID.
	 * @param array<string, mixed> $meta       Protected meta.
	 * @return void
	 */
	private function update_meta( $inquiry_id, $meta ) {
		foreach ( $meta as $key => $value ) {
			update_post_meta( $inquiry_id, $key, $value );
		}
	}

	/**
	 * Find an existing idempotent inquiry.
	 *
	 * @param string $request_key Request key.
	 * @return int
	 */
	private function find_by_request_key( $request_key ) {
		$posts = get_posts(
			array(
				'post_type'        => Inquiry::POST_TYPE,
				'post_status'      => array( 'private', 'trash' ),
				'numberposts'      => 1,
				'fields'           => 'ids',
				'meta_key'         => '_hks_inquiry_request_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'       => $request_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'suppress_filters' => true,
			)
		);

		return empty( $posts ) ? 0 : (int) $posts[0];
	}

	/**
	 * Allow up to eight capture attempts per connection in fifteen minutes.
	 *
	 * The raw IP address is never stored; only a salted hash is used as a short
	 * transient key.
	 *
	 * @return bool
	 */
	private function within_rate_limit() {
		$address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ) );
		$key     = 'hks_inquiry_rate_' . hash_hmac( 'sha256', $address, wp_salt( 'nonce' ) );
		$count   = (int) get_transient( $key );

		if ( $count >= 8 ) {
			return false;
		}

		set_transient( $key, $count + 1, 15 * MINUTE_IN_SECONDS );

		return true;
	}

	/**
	 * Check whether a stored optional value is enabled by the Tour.
	 *
	 * @param string   $field             Request field.
	 * @param string[] $allowed_questions Enabled Tour questions.
	 * @return bool
	 */
	private function optional_field_allowed( $field, $allowed_questions ) {
		if ( in_array( $field, array( 'adults', 'children' ), true ) ) {
			return in_array( 'adults_children', $allowed_questions, true );
		}

		return in_array( $field, $allowed_questions, true );
	}

	/**
	 * Sanitize allowlisted attribution without click identifiers or full URLs.
	 *
	 * @param mixed $attribution Untrusted attribution.
	 * @return array<string, string>
	 */
	private function attribution( $attribution ) {
		$clean = array();

		if ( ! is_array( $attribution ) ) {
			return $clean;
		}

		foreach ( self::ATTRIBUTION_KEYS as $key ) {
			$value = $this->text( $attribution[ $key ] ?? '', 160 );

			if ( '' !== $value ) {
				$clean[ $key ] = $value;
			}
		}

		return $clean;
	}

	/**
	 * Return an SCF value with a metadata fallback.
	 *
	 * @param string $name    Field name.
	 * @param int    $post_id Post ID.
	 * @return mixed
	 */
	private function field( $name, $post_id ) {
		if ( function_exists( 'get_field' ) ) {
			return get_field( $name, $post_id );
		}

		return get_post_meta( $post_id, $name, true );
	}

	/**
	 * Sanitize and limit one text value.
	 *
	 * @param mixed $value  Untrusted value.
	 * @param int   $length Maximum byte length.
	 * @return string
	 */
	private function text( $value, $length ) {
		return substr( sanitize_text_field( (string) $value ), 0, $length );
	}

	/**
	 * Create a field-specific validation error.
	 *
	 * @param string $field   Field name.
	 * @param string $message Public error.
	 * @return \WP_Error
	 */
	private function error( $field, $message ) {
		return new \WP_Error(
			'hks_inquiry_invalid',
			$message,
			array(
				'status' => 422,
				'field'  => $field,
			)
		);
	}

	/**
	 * Build a no-store capture response.
	 *
	 * @param int    $inquiry_id   Inquiry post ID, not exposed.
	 * @param string $reference    Public reference.
	 * @param string $package_label Canonical WhatsApp label.
	 * @param int    $status       HTTP response status.
	 * @return \WP_REST_Response
	 */
	private function response( $inquiry_id, $reference, $package_label, $status ) {
		$response = new \WP_REST_Response(
			array(
				'saved'         => (bool) $inquiry_id,
				'reference'     => $reference,
				'package_label' => $package_label,
			),
			$status
		);
		$response->header( 'Cache-Control', 'no-store' );

		return $response;
	}

	/**
	 * Format the non-sensitive human reference.
	 *
	 * @param int $inquiry_id Inquiry ID.
	 * @return string
	 */
	public static function reference( $inquiry_id ) {
		return 'HKS-' . str_pad( (string) absint( $inquiry_id ), 6, '0', STR_PAD_LEFT );
	}
}
