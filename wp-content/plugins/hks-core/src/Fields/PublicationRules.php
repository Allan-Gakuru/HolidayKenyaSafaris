<?php
/**
 * Shared publication rules for Tours and Campaigns.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Fields;

use HolidayKenyaSafaris\Core\Content\PostTypes\Campaign;
use HolidayKenyaSafaris\Core\Content\PostTypes\Tour;

defined( 'ABSPATH' ) || exit;

/**
 * Validates the complete candidate state before a public post status is saved.
 *
 * The class does not mutate submitted or stored values. Every entry point receives
 * the same normalized candidate arrays and gets the same structured error list.
 */
final class PublicationRules {

	/**
	 * Exact editorial sentinel that must never become public copy.
	 *
	 * @var string
	 */
	private const CONFIRMATION_SENTINEL = 'CLIENT CONFIRMATION REQUIRED';

	/**
	 * Validate a supported post type.
	 *
	 * @param string               $post_type Post type name.
	 * @param array<string, mixed> $values    Complete candidate SCF values.
	 * @param int                  $post_id   Current post ID, or zero for a new post.
	 * @param array<string, mixed> $native    Candidate native WordPress fields.
	 * @return array<int, array{code:string,field:string,message:string}>
	 */
	public static function validate( $post_type, $values, $post_id = 0, $native = array() ) {
		if ( Tour::POST_TYPE === $post_type ) {
			return self::validate_tour( $values, $post_id, $native );
		}

		if ( Campaign::POST_TYPE === $post_type ) {
			return self::validate_campaign( $values, $native );
		}

		return array();
	}

	/**
	 * Validate a canonical Tour candidate.
	 *
	 * @param array<string, mixed> $values  Complete candidate SCF values.
	 * @param int                  $post_id Current post ID, or zero for a new post.
	 * @param array<string, mixed> $native  Candidate native WordPress fields.
	 * @return array<int, array{code:string,field:string,message:string}>
	 */
	public static function validate_tour( $values, $post_id = 0, $native = array() ) {
		$errors     = array();
		$product_id = self::text( self::value( $values, 'hks_internal_product_id' ) );

		if ( ! self::is_meaningful( self::value( $native, 'post_title' ) ) ) {
			self::add_error(
				$errors,
				'hks_tour_title_required',
				'post_title',
				__( 'Add the public Tour title before publishing.', 'hks-core' )
			);
		}

		if ( ! self::is_meaningful( self::value( $native, 'post_excerpt' ) ) ) {
			self::add_error(
				$errors,
				'hks_tour_excerpt_required',
				'post_excerpt',
				__( 'Add the short Tour listing summary before publishing.', 'hks-core' )
			);
		}

		if ( ! self::is_meaningful( self::value( $native, 'post_content' ) ) ) {
			self::add_error(
				$errors,
				'hks_tour_overview_required',
				'post_content',
				__( 'Add the concise public Tour overview before publishing.', 'hks-core' )
			);
		}

		if ( ! self::is_meaningful( $product_id ) ) {
			self::add_error(
				$errors,
				'hks_tour_product_id_required',
				'hks_internal_product_id',
				__( 'Add a stable internal product ID before publishing this Tour.', 'hks-core' )
			);
		} elseif ( self::tour_product_id_exists( $product_id, $post_id ) ) {
			self::add_error(
				$errors,
				'hks_tour_product_id_duplicate',
				'hks_internal_product_id',
				__( 'The internal product ID is already assigned to another Tour.', 'hks-core' )
			);
		}

		$source_url       = self::text( self::value( $values, 'hks_source_url' ) );
		$source_reference = self::text( self::value( $values, 'hks_source_reference' ) );

		if ( ! self::is_meaningful( $source_url ) && ! self::is_meaningful( $source_reference ) ) {
			self::add_error(
				$errors,
				'hks_tour_source_required',
				'hks_source_url',
				__( 'Add at least one traceable source URL or source reference before publishing this Tour.', 'hks-core' )
			);
		}

		$source_checked_date = self::value( $values, 'hks_source_checked_date' );
		if ( ! self::is_meaningful( $source_checked_date ) || null === self::date_timestamp( $source_checked_date ) ) {
			self::add_error(
				$errors,
				'hks_tour_source_date_required',
				'hks_source_checked_date',
				__( 'Record a valid date on which the Tour source was checked.', 'hks-core' )
			);
		}

		$source_status = self::text( self::value( $values, 'hks_source_status' ) );

		if ( ! in_array( $source_status, array( 'reviewed', 'client_confirmed' ), true ) ) {
			self::add_error(
				$errors,
				'hks_tour_source_status_unapproved',
				'hks_source_status',
				__( 'Set the source status to Operator reviewed or Client confirmed before publishing this Tour.', 'hks-core' )
			);
		}

		self::validate_numeric_range(
			$errors,
			self::value( $values, 'hks_min_group_size' ),
			self::value( $values, 'hks_max_group_size' ),
			'hks_tour_group_range',
			'hks_min_group_size',
			__( 'The Tour minimum group size cannot exceed its maximum group size.', 'hks-core' )
		);

		self::validate_tour_price( $values, $errors );
		self::validate_tour_rows( $values, $errors );

		$public_values = self::select_values( $values, self::tour_public_field_names() );
		$public_values['native'] = self::select_values(
			$native,
			array( 'post_title', 'post_excerpt', 'post_content' )
		);

		if ( self::contains_confirmation_sentinel( $public_values ) ) {
			self::add_error(
				$errors,
				'hks_tour_public_confirmation_sentinel',
				'hks_public_content',
				__( 'Remove the CLIENT CONFIRMATION REQUIRED placeholder from public Tour fields before publishing.', 'hks-core' )
			);
		}

		return $errors;
	}

	/**
	 * Validate a Campaign candidate.
	 *
	 * @param array<string, mixed> $values Complete candidate SCF values.
	 * @param array<string, mixed> $native Candidate native WordPress fields.
	 * @return array<int, array{code:string,field:string,message:string}>
	 */
	public static function validate_campaign( $values, $native = array() ) {
		$errors  = array();
		$tour_ids = self::relationship_ids( self::value( $values, 'hks_linked_tour' ) );

		if ( ! self::is_meaningful( self::value( $native, 'post_title' ) ) ) {
			self::add_error(
				$errors,
				'hks_campaign_title_required',
				'post_title',
				__( 'Add the Campaign name before publishing.', 'hks-core' )
			);
		}

		if ( 1 !== count( $tour_ids ) ) {
			self::add_error(
				$errors,
				'hks_campaign_tour_required',
				'hks_linked_tour',
				__( 'Link this Campaign to exactly one canonical Tour before publishing.', 'hks-core' )
			);
		} elseif ( ! self::is_published_tour( $tour_ids[0] ) ) {
			self::add_error(
				$errors,
				'hks_campaign_tour_not_public',
				'hks_linked_tour',
				__( 'The linked Tour must be published before this Campaign can be public.', 'hks-core' )
			);
		}

		$campaign_status = self::text( self::value( $values, 'hks_campaign_status' ) );

		if ( ! in_array( $campaign_status, array( 'testing', 'active' ), true ) ) {
			self::add_error(
				$errors,
				'hks_campaign_status_not_public',
				'hks_campaign_status',
				__( 'Set the Campaign lifecycle status to Testing or Active before making the page public. Draft, paused, and archived Campaigns must remain non-public.', 'hks-core' )
			);
		}

		$required_fields = array(
			'hks_internal_label'           => __( 'Add the internal campaign label.', 'hks-core' ),
			'hks_target_audience'          => __( 'Identify the target audience or occasion.', 'hks-core' ),
			'hks_primary_desire'           => __( 'Record the audience\'s primary desire.', 'hks-core' ),
			'hks_primary_problem'          => __( 'Record the current pressure or problem.', 'hks-core' ),
			'hks_primary_objective'        => __( 'Record the desired outcome or primary objective.', 'hks-core' ),
			'hks_primary_objection'        => __( 'Record the primary objection or trust barrier.', 'hks-core' ),
			'hks_next_step'                => __( 'Define the intended next step.', 'hks-core' ),
			'hks_hero_headline'            => __( 'Add the Campaign hero headline.', 'hks-core' ),
			'hks_supporting_copy'          => __( 'Add the Campaign supporting copy.', 'hks-core' ),
			'hks_analytics_campaign_label' => __( 'Add the stable analytics campaign label.', 'hks-core' ),
		);

		foreach ( $required_fields as $field_name => $message ) {
			if ( ! self::is_meaningful( self::value( $values, $field_name ) ) ) {
				self::add_error(
					$errors,
					'hks_campaign_required_' . substr( $field_name, 4 ),
					$field_name,
					$message
				);
			}
		}

		$sentinel_fields = array_merge(
			self::campaign_public_field_names(),
			array_keys( $required_fields )
		);
		$public_values   = self::select_values( $values, array_values( array_unique( $sentinel_fields ) ) );
		$public_values['native'] = self::select_values(
			$native,
			array( 'post_title', 'post_excerpt', 'post_content' )
		);

		if ( self::contains_confirmation_sentinel( $public_values ) ) {
			self::add_error(
				$errors,
				'hks_campaign_public_confirmation_sentinel',
				'hks_public_content',
				__( 'Remove the CLIENT CONFIRMATION REQUIRED placeholder from Campaign fields required for publication.', 'hks-core' )
			);
		}

		return $errors;
	}

	/**
	 * Return every field the guard may need to load individually.
	 *
	 * @param string $post_type Post type name.
	 * @return array<int, string>
	 */
	public static function field_names( $post_type ) {
		if ( Tour::POST_TYPE === $post_type ) {
			return array_values(
				array_unique(
					array_merge(
						array(
							'hks_internal_product_id',
							'hks_source_url',
							'hks_source_reference',
							'hks_source_checked_date',
							'hks_source_status',
						),
						self::tour_public_field_names()
					)
				)
			);
		}

		if ( Campaign::POST_TYPE === $post_type ) {
			return array_values(
				array_unique(
					array_merge(
						self::campaign_public_field_names(),
						array(
							'hks_internal_label',
							'hks_target_audience',
							'hks_primary_desire',
							'hks_primary_problem',
							'hks_primary_objective',
							'hks_primary_objection',
							'hks_next_step',
							'hks_campaign_status',
							'hks_analytics_campaign_label',
						)
					)
				)
			);
		}

		return array();
	}

	/**
	 * Validate the selected Tour price presentation.
	 *
	 * @param array<string, mixed>                                      $values Complete candidate values.
	 * @param array<int, array{code:string,field:string,message:string}> $errors Error collector.
	 * @return void
	 */
	private static function validate_tour_price( $values, &$errors ) {
		$display_mode = self::text( self::value( $values, 'hks_price_display_mode' ) );

		if ( ! in_array( $display_mode, array( 'from_price', 'request_current_rate', 'hidden' ), true ) ) {
			self::add_error(
				$errors,
				'hks_tour_price_display_mode_invalid',
				'hks_price_display_mode',
				__( 'Choose a valid public price display mode before publishing.', 'hks-core' )
			);
			return;
		}

		if ( 'from_price' !== $display_mode ) {
			return;
		}

		$from_price = self::value( $values, 'hks_from_price_ksh' );
		if ( ! is_numeric( $from_price ) || (float) $from_price <= 0 ) {
			self::add_error(
				$errors,
				'hks_tour_from_price_invalid',
				'hks_from_price_ksh',
				__( 'A displayed From price must be a positive KSh amount.', 'hks-core' )
			);
		}

		$price_unit = self::text( self::value( $values, 'hks_price_unit' ) );
		if ( ! in_array( $price_unit, array( 'per_person', 'per_group', 'per_vehicle', 'per_room', 'other' ), true ) ) {
			self::add_error(
				$errors,
				'hks_tour_price_unit_required',
				'hks_price_unit',
				__( 'Choose the unit for the displayed From price.', 'hks-core' )
			);
		}

		$price_status = self::text( self::value( $values, 'hks_price_status' ) );
		if ( ! in_array( $price_status, array( 'placeholder', 'operator_reviewed', 'client_confirmed' ), true ) ) {
			self::add_error(
				$errors,
				'hks_tour_price_status_unapproved',
				'hks_price_status',
				__( 'A displayed From price must be explicitly provisional, Operator reviewed, or Client confirmed. Converted estimates and expired rates cannot be published.', 'hks-core' )
			);
		}

		$price_checked_date = self::value( $values, 'hks_price_checked_date' );
		if ( 'client_confirmed' === $price_status && ( ! self::is_meaningful( $price_checked_date ) || null === self::date_timestamp( $price_checked_date ) ) ) {
			self::add_error(
				$errors,
				'hks_tour_confirmed_price_date_required',
				'hks_price_checked_date',
				__( 'Record a valid checked date for a Client confirmed price.', 'hks-core' )
			);
		}

		$assumption_fields = array(
			'hks_price_season_assumption'       => __( 'Add the season or travel-window assumption for the From price.', 'hks-core' ),
			'hks_price_residency_assumption'    => __( 'Add a confirmed residency assumption for the From price.', 'hks-core' ),
			'hks_price_group_size_assumption'   => __( 'Add the traveler or group-size assumption for the From price.', 'hks-core' ),
			'hks_price_transport_assumption'    => __( 'Add the transport assumption for the From price.', 'hks-core' ),
			'hks_price_accommodation_assumption' => __( 'Add the accommodation and room-basis assumption for the From price.', 'hks-core' ),
			'hks_price_inclusions_assumption'   => __( 'Add the major inclusions assumption for the From price.', 'hks-core' ),
			'hks_price_basis_summary'           => __( 'Add the public price basis summary.', 'hks-core' ),
			'hks_price_disclaimer'              => __( 'Add the public price disclaimer.', 'hks-core' ),
		);

		foreach ( $assumption_fields as $field_name => $message ) {
			$value             = self::value( $values, $field_name );
			$invalid_residency = 'hks_price_residency_assumption' === $field_name
				&& ! in_array( self::text( $value ), array( 'kenyan_citizen', 'resident', 'non_resident', 'mixed' ), true );

			if ( ! self::is_meaningful( $value ) || $invalid_residency ) {
				self::add_error(
					$errors,
					'hks_tour_price_assumption_' . substr( $field_name, 10 ),
					$field_name,
					$message
				);
			}
		}
	}

	/**
	 * Validate comparable ranges inside optional public Tour rows.
	 *
	 * @param array<string, mixed>                                      $values Complete candidate values.
	 * @param array<int, array{code:string,field:string,message:string}> $errors Error collector.
	 * @return void
	 */
	private static function validate_tour_rows( $values, &$errors ) {
		foreach ( self::rows( self::value( $values, 'hks_seasonal_rates' ) ) as $index => $row ) {
			self::validate_numeric_range(
				$errors,
				self::value( $row, 'minimum_travelers' ),
				self::value( $row, 'maximum_travelers' ),
				'hks_tour_seasonal_group_range_' . $index,
				'hks_seasonal_rates',
				sprintf(
					/* translators: %d: seasonal-rate row number. */
					__( 'Seasonal rate row %d has a minimum traveler count greater than its maximum.', 'hks-core' ),
					$index + 1
				)
			);

			self::validate_date_range(
				$errors,
				self::value( $row, 'valid_from' ),
				self::value( $row, 'valid_until' ),
				'hks_tour_seasonal_date_range_' . $index,
				'hks_seasonal_rates',
				sprintf(
					/* translators: %d: seasonal-rate row number. */
					__( 'Seasonal rate row %d ends before it starts.', 'hks-core' ),
					$index + 1
				)
			);
		}

		foreach ( self::rows( self::value( $values, 'hks_mandatory_supplements' ) ) as $index => $row ) {
			self::validate_date_range(
				$errors,
				self::value( $row, 'valid_from' ),
				self::value( $row, 'valid_until' ),
				'hks_tour_supplement_date_range_' . $index,
				'hks_mandatory_supplements',
				sprintf(
					/* translators: %d: supplement row number. */
					__( 'Mandatory supplement row %d ends before it starts.', 'hks-core' ),
					$index + 1
				)
			);
		}
	}

	/**
	 * Public Tour field names used for the sentinel audit.
	 *
	 * Internal provenance, policy envelopes, and operational notes are omitted.
	 * Media IDs are included without inspecting attachment rights: rights remain an
	 * editorial warning and launch-audit concern, never an automatic publish gate.
	 *
	 * @return array<int, string>
	 */
	private static function tour_public_field_names() {
		return array(
			'hks_featured',
			'hks_duration_days',
			'hks_duration_nights',
			'hks_duration_label',
			'hks_start_location',
			'hks_end_location',
			'hks_route_summary',
			'hks_transport_types',
			'hks_min_group_size',
			'hks_max_group_size',
			'hks_residency_basis',
			'hks_accommodation_basis',
			'hks_meals_summary',
			'hks_price_display_mode',
			'hks_from_price_ksh',
			'hks_price_unit',
			'hks_price_status',
			'hks_price_checked_date',
			'hks_price_valid_until',
			'hks_price_season_assumption',
			'hks_price_residency_assumption',
			'hks_price_group_size_assumption',
			'hks_price_transport_assumption',
			'hks_price_accommodation_assumption',
			'hks_price_inclusions_assumption',
			'hks_price_basis_summary',
			'hks_price_disclaimer',
			'hks_seasonal_rates',
			'hks_mandatory_supplements',
			'hks_itinerary',
			'hks_inclusions',
			'hks_exclusions',
			'hks_best_for',
			'hks_physical_difficulty',
			'hks_child_suitability',
			'hks_accessibility_notes',
			'hks_packing_guidance',
			'hks_weather_season_notes',
			'hks_safety_notes',
			'hks_gallery',
			'hks_vehicle_image',
			'hks_accommodation_images',
			'hks_route_image',
			'hks_cta_label',
			'hks_whatsapp_package_label',
			'hks_intake_questions',
			'hks_featured_faqs',
		);
	}

	/**
	 * Public Campaign field names used for the sentinel audit.
	 *
	 * @return array<int, string>
	 */
	private static function campaign_public_field_names() {
		return array(
			'hks_linked_tour',
			'hks_hero_headline',
			'hks_supporting_copy',
			'hks_trust_modules',
			'hks_featured_faqs',
			'hks_cta_label',
			'hks_navigation_mode',
		);
	}

	/**
	 * Add one structured validation error.
	 *
	 * @param array<int, array{code:string,field:string,message:string}> $errors Error collector.
	 * @param string                                                      $code    Stable error code.
	 * @param string                                                      $field   Related field name.
	 * @param string                                                      $message Editor-facing message.
	 * @return void
	 */
	private static function add_error( &$errors, $code, $field, $message ) {
		$errors[] = array(
			'code'    => $code,
			'field'   => $field,
			'message' => $message,
		);
	}

	/**
	 * Read an array value without notices.
	 *
	 * @param array<string, mixed> $values Candidate values.
	 * @param string               $key    Field name.
	 * @return mixed
	 */
	private static function value( $values, $key ) {
		return is_array( $values ) && array_key_exists( $key, $values ) ? $values[ $key ] : null;
	}

	/**
	 * Normalize a scalar to validation text without changing stored data.
	 *
	 * @param mixed $value Candidate value.
	 * @return string
	 */
	private static function text( $value ) {
		if ( is_string( $value ) || is_numeric( $value ) ) {
			return trim( (string) $value );
		}

		return '';
	}

	/**
	 * Determine whether a required value is present and is not the sentinel.
	 *
	 * @param mixed $value Candidate value.
	 * @return bool
	 */
	private static function is_meaningful( $value ) {
		if ( is_array( $value ) || is_object( $value ) ) {
			return false;
		}

		$text = self::visible_text( $value );

		return '' !== $text && false === stripos( $text, self::CONFIRMATION_SENTINEL );
	}

	/**
	 * Find the confirmation sentinel anywhere in visible text recursively.
	 *
	 * @param mixed $value Candidate value.
	 * @return bool
	 */
	private static function contains_confirmation_sentinel( $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $child ) {
				if ( self::contains_confirmation_sentinel( $child ) ) {
					return true;
				}
			}

			return false;
		}

		if ( is_object( $value ) ) {
			return self::contains_confirmation_sentinel( get_object_vars( $value ) );
		}

		return is_string( $value )
			&& false !== stripos( self::visible_text( $value ), self::CONFIRMATION_SENTINEL );
	}

	/**
	 * Reduce public copy to visible text for empty-value and sentinel checks.
	 *
	 * @param mixed $value Candidate value.
	 * @return string
	 */
	private static function visible_text( $value ) {
		$text = self::text( $value );
		if ( '' === $text ) {
			return '';
		}

		$text = preg_replace( '/<!--.*?-->/s', '', $text );
		$text = strip_tags( is_string( $text ) ? $text : '' );
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$text = preg_replace( '/\s+/u', ' ', $text );

		return trim( is_string( $text ) ? $text : '' );
	}

	/**
	 * Select named values that are present.
	 *
	 * @param array<string, mixed> $values Candidate values.
	 * @param array<int, string>   $names  Field names.
	 * @return array<string, mixed>
	 */
	private static function select_values( $values, $names ) {
		$selected = array();

		if ( ! is_array( $values ) ) {
			return $selected;
		}

		foreach ( $names as $name ) {
			if ( array_key_exists( $name, $values ) ) {
				$selected[ $name ] = $values[ $name ];
			}
		}

		return $selected;
	}

	/**
	 * Validate a numeric minimum and maximum when both are supplied.
	 *
	 * @param array<int, array{code:string,field:string,message:string}> $errors Error collector.
	 * @param mixed                                                       $minimum Candidate minimum.
	 * @param mixed                                                       $maximum Candidate maximum.
	 * @param string                                                      $code    Stable error code.
	 * @param string                                                      $field   Related field.
	 * @param string                                                      $message Editor-facing message.
	 * @return void
	 */
	private static function validate_numeric_range( &$errors, $minimum, $maximum, $code, $field, $message ) {
		if ( is_numeric( $minimum ) && is_numeric( $maximum ) && (float) $minimum > (float) $maximum ) {
			self::add_error( $errors, $code, $field, $message );
		}
	}

	/**
	 * Validate a date range when both endpoints can be parsed.
	 *
	 * @param array<int, array{code:string,field:string,message:string}> $errors Error collector.
	 * @param mixed                                                       $start   Candidate start date.
	 * @param mixed                                                       $end     Candidate end date.
	 * @param string                                                      $code    Stable error code.
	 * @param string                                                      $field   Related field.
	 * @param string                                                      $message Editor-facing message.
	 * @return void
	 */
	private static function validate_date_range( &$errors, $start, $end, $code, $field, $message ) {
		$start_timestamp = self::date_timestamp( $start );
		$end_timestamp   = self::date_timestamp( $end );

		if ( null !== $start_timestamp && null !== $end_timestamp && $start_timestamp > $end_timestamp ) {
			self::add_error( $errors, $code, $field, $message );
		}
	}

	/**
	 * Parse SCF raw or formatted date-picker values.
	 *
	 * @param mixed $value Date candidate.
	 * @return int|null
	 */
	private static function date_timestamp( $value ) {
		$text = self::text( $value );
		if ( '' === $text ) {
			return null;
		}

		if ( preg_match( '/^\d{8}$/', $text ) ) {
			$date = \DateTimeImmutable::createFromFormat( '!Ymd', $text );
			return self::valid_date_timestamp( $date );
		}

		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $text ) ) {
			$date = \DateTimeImmutable::createFromFormat( '!Y-m-d', $text );
			return self::valid_date_timestamp( $date );
		}

		return null;
	}

	/**
	 * Reject date parser rollover warnings as invalid calendar dates.
	 *
	 * @param \DateTimeImmutable|false $date Parsed date candidate.
	 * @return int|null
	 */
	private static function valid_date_timestamp( $date ) {
		if ( false === $date ) {
			return null;
		}

		$parse_errors = \DateTimeImmutable::getLastErrors();
		if ( is_array( $parse_errors ) && ( $parse_errors['warning_count'] > 0 || $parse_errors['error_count'] > 0 ) ) {
			return null;
		}

		return $date->getTimestamp();
	}

	/**
	 * Convert a relationship value to a list of positive post IDs.
	 *
	 * @param mixed $value Relationship candidate.
	 * @return array<int, int>
	 */
	private static function relationship_ids( $value ) {
		if ( is_object( $value ) && isset( $value->ID ) ) {
			$value = $value->ID;
		}

		if ( is_array( $value ) && array_key_exists( 'ID', $value ) ) {
			$value = $value['ID'];
		}

		$items = is_array( $value ) ? array_values( $value ) : array( $value );
		$ids   = array();

		foreach ( $items as $item ) {
			if ( is_object( $item ) && isset( $item->ID ) ) {
				$item = $item->ID;
			} elseif ( is_array( $item ) && isset( $item['ID'] ) ) {
				$item = $item['ID'];
			}

			if ( is_numeric( $item ) && (int) $item > 0 ) {
				$ids[] = (int) $item;
			}
		}

		return $ids;
	}

	/**
	 * Confirm that a relationship target is a published canonical Tour.
	 *
	 * @param int $post_id Candidate Tour ID.
	 * @return bool
	 */
	private static function is_published_tour( $post_id ) {
		if ( ! function_exists( 'get_post_type' ) || ! function_exists( 'get_post_status' ) ) {
			return false;
		}

		return Tour::POST_TYPE === get_post_type( $post_id ) && 'publish' === get_post_status( $post_id );
	}

	/**
	 * Check a product ID across every Tour lifecycle state, including Trash.
	 *
	 * @param string $product_id Candidate identifier.
	 * @param int    $post_id    Current post to exclude.
	 * @return bool
	 */
	private static function tour_product_id_exists( $product_id, $post_id ) {
		if ( ! function_exists( 'get_posts' ) || ! function_exists( 'get_post_meta' ) ) {
			return false;
		}

		$post_statuses = array( 'publish', 'future', 'draft', 'pending', 'private', 'trash', 'auto-draft' );
		if ( function_exists( 'get_post_stati' ) ) {
			$registered_statuses = get_post_stati();
			if ( is_array( $registered_statuses ) && ! empty( $registered_statuses ) ) {
				$post_statuses = array_keys( $registered_statuses );
			}
		}

		$args = array(
			'post_type'              => Tour::POST_TYPE,
			'post_status'            => $post_statuses,
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'meta_key'               => 'hks_internal_product_id',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'suppress_filters'       => true,
		);

		if ( $post_id > 0 ) {
			$args['post__not_in'] = array( $post_id );
		}

		$other_ids = get_posts( $args );
		if ( ! is_array( $other_ids ) ) {
			return false;
		}

		foreach ( $other_ids as $other_id ) {
			$other_product_id = self::text( get_post_meta( $other_id, 'hks_internal_product_id', true ) );
			if ( '' !== $other_product_id && 0 === strcasecmp( $product_id, $other_product_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Normalize a repeater candidate to array rows.
	 *
	 * @param mixed $value Repeater candidate.
	 * @return array<int, array<string, mixed>>
	 */
	private static function rows( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$rows = array();
		foreach ( array_values( $value ) as $row ) {
			if ( is_object( $row ) ) {
				$row = get_object_vars( $row );
			}

			if ( is_array( $row ) ) {
				$rows[] = $row;
			}
		}

		return $rows;
	}

	/**
	 * Prevent construction.
	 */
	private function __construct() {}
}
