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
 * Validates the small set of values that can make public content invalid.
 */
final class PublicationRules {

	/** Editorial sentinel that must never become public copy. */
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
	 * A published Tour is treated as client-approved. Only its public content can
	 * block publication; legacy Tour prices are not loaded or validated.
	 *
	 * @param array<string, mixed> $values  Complete candidate SCF values.
	 * @param int                  $post_id Current post ID. Retained for API compatibility.
	 * @param array<string, mixed> $native  Candidate native WordPress fields.
	 * @return array<int, array{code:string,field:string,message:string}>
	 */
	public static function validate_tour( $values, $post_id = 0, $native = array() ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- shared validator signature.
		$errors = array();

		if ( ! self::is_meaningful( self::value( $native, 'post_title' ) ) ) {
			self::add_error(
				$errors,
				'hks_tour_title_required',
				'post_title',
				__( 'Add the public Tour title before publishing.', 'hks-core' )
			);
		}

		$public_values           = self::select_values( $values, self::tour_public_field_names() );
		$public_values['native'] = self::select_values( $native, array( 'post_title', 'post_excerpt', 'post_content' ) );

		if ( self::contains_confirmation_sentinel( $public_values ) ) {
			self::add_error(
				$errors,
				'hks_tour_public_confirmation_sentinel',
				'hks_public_content',
				__( 'Remove the CLIENT CONFIRMATION REQUIRED placeholder from public Tour content before publishing.', 'hks-core' )
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
		$errors   = array();
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

		$price = self::value( $values, 'hks_campaign_from_price_ksh' );
		if ( self::is_meaningful( $price ) && ! self::is_positive_whole_number( $price ) ) {
			self::add_error(
				$errors,
				'hks_campaign_from_price_invalid',
				'hks_campaign_from_price_ksh',
				__( 'The Campaign From price must be a positive whole KSh amount, or left blank.', 'hks-core' )
			);
		}

		self::validate_campaign_dates( $values, $errors );

		$public_values           = self::select_values( $values, self::campaign_public_field_names() );
		$public_values['native'] = self::select_values( $native, array( 'post_title', 'post_excerpt', 'post_content' ) );

		if ( self::contains_confirmation_sentinel( $public_values ) ) {
			self::add_error(
				$errors,
				'hks_campaign_public_confirmation_sentinel',
				'hks_public_content',
				__( 'Remove the CLIENT CONFIRMATION REQUIRED placeholder from public Campaign content before publishing.', 'hks-core' )
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
			return self::tour_public_field_names();
		}

		if ( Campaign::POST_TYPE === $post_type ) {
			return self::campaign_public_field_names();
		}

		return array();
	}

	/** Validate optional Campaign dates and their order. */
	private static function validate_campaign_dates( $values, &$errors ) {
		$start     = self::value( $values, 'hks_campaign_start_date' );
		$end       = self::value( $values, 'hks_campaign_end_date' );
		$start_ts  = self::is_meaningful( $start ) ? self::date_timestamp( $start ) : null;
		$end_ts    = self::is_meaningful( $end ) ? self::date_timestamp( $end ) : null;

		if ( self::is_meaningful( $start ) && null === $start_ts ) {
			self::add_error( $errors, 'hks_campaign_start_date_invalid', 'hks_campaign_start_date', __( 'Enter a valid Campaign start date or leave it blank.', 'hks-core' ) );
		}

		if ( self::is_meaningful( $end ) && null === $end_ts ) {
			self::add_error( $errors, 'hks_campaign_end_date_invalid', 'hks_campaign_end_date', __( 'Enter a valid Campaign end date or leave it blank.', 'hks-core' ) );
		}

		if ( null !== $start_ts && null !== $end_ts && $end_ts < $start_ts ) {
			self::add_error( $errors, 'hks_campaign_date_range_invalid', 'hks_campaign_end_date', __( 'The Campaign end date cannot be before its start date.', 'hks-core' ) );
		}
	}

	/** Public Tour field names used for candidate loading and sentinel checks. */
	private static function tour_public_field_names() {
		return array(
			'hks_featured',
			'hks_duration_label',
			'hks_start_location',
			'hks_end_location',
			'hks_route_summary',
			'hks_transport_types',
			'hks_accommodation_basis',
			'hks_meals_summary',
			'hks_itinerary',
			'hks_inclusions',
			'hks_exclusions',
			'hks_best_for',
			'hks_child_suitability',
			'hks_accessibility_notes',
			'hks_policies',
			'hks_gallery',
			'hks_featured_faqs',
		);
	}

	/** Public and planning Campaign field names. */
	private static function campaign_public_field_names() {
		return array(
			'hks_linked_tour',
			'hks_hero_headline',
			'hks_supporting_copy',
			'hks_campaign_from_price_ksh',
			'hks_navigation_mode',
			'hks_campaign_start_date',
			'hks_campaign_end_date',
		);
	}

	/** Add one structured validation error. */
	private static function add_error( &$errors, $code, $field, $message ) {
		$errors[] = array( 'code' => $code, 'field' => $field, 'message' => $message );
	}

	/** Read an array value without notices. */
	private static function value( $values, $key ) {
		return is_array( $values ) && array_key_exists( $key, $values ) ? $values[ $key ] : null;
	}

	/** Determine whether a value has visible content. */
	private static function is_meaningful( $value ) {
		if ( is_array( $value ) || is_object( $value ) ) {
			return ! empty( $value );
		}

		return '' !== self::visible_text( $value );
	}

	/** Validate a positive integer without accepting decimals. */
	private static function is_positive_whole_number( $value ) {
		if ( ! is_numeric( $value ) ) {
			return false;
		}

		$number = (float) $value;

		return $number > 0 && floor( $number ) === $number;
	}

	/** Find the confirmation sentinel anywhere in visible text recursively. */
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

		return is_string( $value ) && false !== stripos( self::visible_text( $value ), self::CONFIRMATION_SENTINEL );
	}

	/** Reduce public copy to plain visible text. */
	private static function visible_text( $value ) {
		if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
			return '';
		}

		$text = (string) $value;
		if ( function_exists( 'strip_shortcodes' ) ) {
			$text = strip_shortcodes( $text );
		}
		if ( function_exists( 'wp_strip_all_tags' ) ) {
			$text = wp_strip_all_tags( $text, true );
		} else {
			$text = strip_tags( $text );
		}

		return trim( html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	}

	/** Select a stable subset of candidate values. */
	private static function select_values( $values, $field_names ) {
		$selected = array();
		foreach ( $field_names as $field_name ) {
			$selected[ $field_name ] = self::value( $values, $field_name );
		}

		return $selected;
	}

	/** Parse a supported editor date without relative-date behavior. */
	private static function date_timestamp( $value ) {
		$text = self::visible_text( $value );
		if ( '' === $text ) {
			return null;
		}

		foreach ( array( '!Y-m-d', '!Ymd' ) as $format ) {
			$date   = \DateTimeImmutable::createFromFormat( $format, $text, new \DateTimeZone( 'UTC' ) );
			$errors = \DateTimeImmutable::getLastErrors();
			$valid  = false === $errors || ( 0 === $errors['warning_count'] && 0 === $errors['error_count'] );
			if ( false !== $date && $valid ) {
				return $date->getTimestamp();
			}
		}

		return null;
	}

	/** Normalize SCF post-object values to unique positive IDs. */
	private static function relationship_ids( $value ) {
		$items = is_array( $value ) ? $value : array( $value );
		$ids   = array();

		foreach ( $items as $item ) {
			if ( is_object( $item ) && isset( $item->ID ) ) {
				$item = $item->ID;
			}
			if ( is_array( $item ) && isset( $item['ID'] ) ) {
				$item = $item['ID'];
			}
			if ( is_numeric( $item ) && (int) $item > 0 ) {
				$ids[] = (int) $item;
			}
		}

		return array_values( array_unique( $ids ) );
	}

	/** Confirm that a relationship target is a published canonical Tour. */
	private static function is_published_tour( $post_id ) {
		return function_exists( 'get_post_type' )
			&& function_exists( 'get_post_status' )
			&& Tour::POST_TYPE === get_post_type( $post_id )
			&& 'publish' === get_post_status( $post_id );
	}

	/** Prevent construction. */
	private function __construct() {}
}
