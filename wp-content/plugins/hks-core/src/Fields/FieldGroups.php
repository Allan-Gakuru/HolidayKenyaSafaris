<?php
/**
 * Code-owned Secure Custom Fields definitions.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Fields;

use HolidayKenyaSafaris\Core\Content\PostTypes\Campaign;
use HolidayKenyaSafaris\Core\Content\PostTypes\Faq;
use HolidayKenyaSafaris\Core\Content\PostTypes\Tour;
use HolidayKenyaSafaris\Core\Content\Taxonomies\Destination;

defined( 'ABSPATH' ) || exit;

/**
 * Supplies deterministic SCF groups without database-only configuration.
 */
final class FieldGroups {

	/**
	 * Return every HKS field group in a stable registration order.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function all() {
		return array(
			self::tour_source_group(),
			self::tour_package_group(),
			self::tour_pricing_group(),
			self::tour_itinerary_group(),
			self::tour_inclusions_group(),
			self::tour_suitability_group(),
			self::tour_policies_group(),
			self::tour_media_conversion_group(),
			self::tour_operations_group(),
			self::campaign_public_group(),
			self::campaign_brief_group(),
			self::campaign_proof_group(),
			self::campaign_governance_group(),
			self::faq_public_group(),
			self::faq_audit_group(),
			self::destination_public_group(),
			self::destination_audit_group(),
			self::attachment_rights_group(),
			self::settings_group(),
		);
	}

	/**
	 * Private source and audit fields for a canonical Tour.
	 *
	 * @return array<string, mixed>
	 */
	private static function tour_source_group() {
		return self::group(
			'tour_source',
			__( 'Tour: Source and audit (internal)', 'hks-core' ),
			array(
				self::message(
					'tour_native_mappings',
					__( 'Canonical WordPress field mappings', 'hks-core' ),
					__( 'Use the WordPress title as the public Tour title, the WordPress excerpt as the short listing summary, and the featured image as the Tour hero. Do not recreate those values in custom fields.', 'hks-core' )
				),
				self::field( 'tour_internal_product_id', __( 'Internal product ID', 'hks-core' ), 'hks_internal_product_id', 'text', array( 'instructions' => __( 'Stable identifier independent of the WordPress post ID. Do not recycle it for another product.', 'hks-core' ) ) ),
				self::field( 'tour_original_ashford_title', __( 'Original Ashford title', 'hks-core' ), 'hks_original_ashford_title', 'text', array( 'instructions' => __( 'Internal source reference only. Local-facing titles belong in the native WordPress title.', 'hks-core' ) ) ),
				self::field( 'tour_source_url', __( 'Primary source URL', 'hks-core' ), 'hks_source_url', 'url', array( 'instructions' => __( 'Current Ashford product page or client-confirmed source.', 'hks-core' ) ) ),
				self::field( 'tour_source_reference', __( 'Source reference', 'hks-core' ), 'hks_source_reference', 'text', array( 'instructions' => __( 'Document name, catalogue page, email reference, or another traceable source when a URL is not sufficient.', 'hks-core' ) ) ),
				self::field( 'tour_source_checked_date', __( 'Source checked date', 'hks-core' ), 'hks_source_checked_date', 'date_picker', self::date_args() ),
				self::field( 'tour_source_status', __( 'Source status', 'hks-core' ), 'hks_source_status', 'select', self::choice_args( Choices::source_status(), true ) ),
				self::field( 'tour_source_snapshot', __( 'Original source notes or snapshot', 'hks-core' ), 'hks_source_snapshot', 'textarea', array( 'instructions' => __( 'Preserve factual source wording needed for audit. This is never public marketing copy.', 'hks-core' ), 'rows' => 8, 'new_lines' => '' ) ),
				self::field( 'tour_source_internal_notes', __( 'Internal review notes', 'hks-core' ), 'hks_source_internal_notes', 'textarea', array( 'rows' => 4, 'new_lines' => '' ) ),
			),
			self::location( 'post_type', Tour::POST_TYPE ),
			false,
			__( 'Private provenance used to verify canonical facts. This group is intentionally excluded from REST responses.', 'hks-core' ),
			0
		);
	}

	/**
	 * Public Tour identity, logistics, and pricing facts.
	 *
	 * @return array<string, mixed>
	 */
	private static function tour_package_group() {
		return self::group(
			'tour_package',
			__( 'Tour: Public package facts', 'hks-core' ),
			array(
				self::tab( 'tour_tab_identity', __( 'Package', 'hks-core' ) ),
				self::field( 'tour_featured', __( 'Featured Tour', 'hks-core' ), 'hks_featured', 'true_false', array( 'instructions' => __( 'Editorial curation only. This is not a claim about popularity.', 'hks-core' ), 'ui' => 1, 'default_value' => 0 ) ),
				self::field( 'tour_duration_days', __( 'Duration days', 'hks-core' ), 'hks_duration_days', 'number', array( 'min' => 0, 'step' => 1 ) ),
				self::field( 'tour_duration_nights', __( 'Duration nights', 'hks-core' ), 'hks_duration_nights', 'number', array( 'min' => 0, 'step' => 1 ) ),
				self::field( 'tour_duration_label', __( 'Duration display label', 'hks-core' ), 'hks_duration_label', 'text', array( 'instructions' => __( 'Plain-language value such as "3 days / 2 nights". Keep it consistent with the structured day and night values.', 'hks-core' ) ) ),
				self::field( 'tour_start_location', __( 'Start location', 'hks-core' ), 'hks_start_location', 'text' ),
				self::field( 'tour_end_location', __( 'End location', 'hks-core' ), 'hks_end_location', 'text' ),
				self::field( 'tour_route_summary', __( 'Route summary', 'hks-core' ), 'hks_route_summary', 'text', array( 'instructions' => __( 'Use a readable route such as Nairobi -> Maasai Mara -> Nairobi.', 'hks-core' ) ) ),

				self::tab( 'tour_tab_logistics', __( 'Logistics', 'hks-core' ) ),
				self::field( 'tour_transport_types', __( 'Transport types', 'hks-core' ), 'hks_transport_types', 'checkbox', array( 'choices' => Choices::transport_types(), 'layout' => 'vertical', 'return_format' => 'value', 'allow_custom' => 0, 'save_custom' => 0 ) ),
				self::field( 'tour_min_group_size', __( 'Minimum group size', 'hks-core' ), 'hks_min_group_size', 'number', array( 'min' => 1, 'step' => 1 ) ),
				self::field( 'tour_max_group_size', __( 'Maximum group size', 'hks-core' ), 'hks_max_group_size', 'number', array( 'min' => 1, 'step' => 1 ) ),
				self::field( 'tour_residency_basis', __( 'Residency basis', 'hks-core' ), 'hks_residency_basis', 'select', self::choice_args( Choices::residency_basis(), true ) ),
				self::field( 'tour_accommodation_basis', __( 'Accommodation basis', 'hks-core' ), 'hks_accommodation_basis', 'textarea', array( 'instructions' => __( 'Name confirmed accommodation or explain the confirmed tier and room basis. Do not imply availability.', 'hks-core' ), 'rows' => 3, 'new_lines' => 'br' ) ),
				self::field( 'tour_meals_summary', __( 'Meals summary', 'hks-core' ), 'hks_meals_summary', 'text', array( 'instructions' => __( 'Use plain language; avoid unexplained meal-plan abbreviations.', 'hks-core' ) ) ),
			),
			self::location( 'post_type', Tour::POST_TYPE ),
			true,
			__( 'Canonical package identity and logistics intended for controlled public presentation.', 'hks-core' ),
			10
		);
	}

	/**
	 * Price records kept out of anonymous SCF REST responses.
	 *
	 * Templates may render only the selected display mode with its status and
	 * assumptions. Keeping the source rows private prevents hidden, converted, or
	 * expired working values from leaking through an unfiltered REST response.
	 *
	 * @return array<string, mixed>
	 */
	private static function tour_pricing_group() {
		return self::group(
			'tour_pricing',
			__( 'Tour: Pricing and assumptions (controlled)', 'hks-core' ),
			array(
				self::tab( 'tour_tab_from_price', __( 'From-price presentation', 'hks-core' ) ),
				self::message( 'tour_price_currency_note', __( 'Currency rule', 'hks-core' ), __( 'All public prices in this group are Kenyan shillings (KSh). Do not enter converted USD estimates as approved KSh rates.', 'hks-core' ) ),
				self::field( 'tour_price_display_mode', __( 'Price display mode', 'hks-core' ), 'hks_price_display_mode', 'select', array_merge( self::choice_args( Choices::price_display_mode(), false ), array( 'default_value' => 'request_current_rate', 'required' => 1 ) ) ),
				self::field( 'tour_from_price_ksh', __( 'From price (KSh)', 'hks-core' ), 'hks_from_price_ksh', 'number', array( 'instructions' => __( 'Numbers only. A displayed from-price also needs every assumption below.', 'hks-core' ), 'min' => 0, 'step' => 1, 'prepend' => 'KSh' ) ),
				self::field( 'tour_price_unit', __( 'Price unit', 'hks-core' ), 'hks_price_unit', 'select', self::choice_args( Choices::price_units(), true ) ),
				self::field( 'tour_price_status', __( 'Price status', 'hks-core' ), 'hks_price_status', 'select', array_merge( self::choice_args( Choices::price_status(), false ), array( 'default_value' => 'placeholder', 'required' => 1 ) ) ),
				self::field( 'tour_price_checked_date', __( 'Price checked date', 'hks-core' ), 'hks_price_checked_date', 'date_picker', self::date_args() ),
				self::field( 'tour_price_valid_until', __( 'Price valid until', 'hks-core' ), 'hks_price_valid_until', 'date_picker', self::date_args() ),
				self::field( 'tour_price_season_assumption', __( 'Season assumption', 'hks-core' ), 'hks_price_season_assumption', 'text', array( 'instructions' => __( 'Season or travel window to which the from-price applies.', 'hks-core' ) ) ),
				self::field( 'tour_price_residency_assumption', __( 'Residency assumption', 'hks-core' ), 'hks_price_residency_assumption', 'select', self::choice_args( Choices::residency_basis(), true ) ),
				self::field( 'tour_price_group_size_assumption', __( 'Group-size assumption', 'hks-core' ), 'hks_price_group_size_assumption', 'text', array( 'instructions' => __( 'For example: per adult sharing, based on two travelers.', 'hks-core' ) ) ),
				self::field( 'tour_price_transport_assumption', __( 'Transport assumption', 'hks-core' ), 'hks_price_transport_assumption', 'text', array( 'instructions' => __( 'State the vehicle or flight basis included in the from-price.', 'hks-core' ) ) ),
				self::field( 'tour_price_accommodation_assumption', __( 'Accommodation assumption', 'hks-core' ), 'hks_price_accommodation_assumption', 'text', array( 'instructions' => __( 'State property or tier, room occupancy, and meal plan.', 'hks-core' ) ) ),
				self::field( 'tour_price_inclusions_assumption', __( 'Inclusions assumption', 'hks-core' ), 'hks_price_inclusions_assumption', 'textarea', array( 'instructions' => __( 'Summarize exactly which major components are included in this from-price.', 'hks-core' ), 'rows' => 3, 'new_lines' => 'br' ) ),
				self::field( 'tour_price_basis_summary', __( 'Price basis summary', 'hks-core' ), 'hks_price_basis_summary', 'text', array( 'instructions' => __( 'Short public synthesis of the season, residency, traveler, room, and transport assumptions.', 'hks-core' ) ) ),
				self::field( 'tour_price_disclaimer', __( 'Price disclaimer', 'hks-core' ), 'hks_price_disclaimer', 'textarea', array( 'instructions' => __( 'Plain-language public caveat covering availability, dates, and quote confirmation without hiding material assumptions.', 'hks-core' ), 'rows' => 3, 'new_lines' => 'br' ) ),

				self::tab( 'tour_tab_seasonal_rates', __( 'Seasonal rates', 'hks-core' ) ),
				self::field(
					'tour_seasonal_rates',
					__( 'Seasonal rates', 'hks-core' ),
					'hks_seasonal_rates',
					'repeater',
					array(
						'instructions' => __( 'Each row is a source-governed KSh rate. A row must never be presented as current unless its status and validity support that claim.', 'hks-core' ),
						'layout'       => 'block',
						'button_label' => __( 'Add seasonal rate', 'hks-core' ),
						'sub_fields'   => array(
							self::field( 'seasonal_rate_name', __( 'Season or rate name', 'hks-core' ), 'season_name', 'text' ),
							self::field( 'seasonal_rate_valid_from', __( 'Valid from', 'hks-core' ), 'valid_from', 'date_picker', self::date_args() ),
							self::field( 'seasonal_rate_valid_until', __( 'Valid until', 'hks-core' ), 'valid_until', 'date_picker', self::date_args() ),
							self::field( 'seasonal_rate_residency', __( 'Residency', 'hks-core' ), 'residency', 'select', self::choice_args( Choices::residency_basis(), true ) ),
							self::field( 'seasonal_rate_min_group', __( 'Minimum travelers', 'hks-core' ), 'minimum_travelers', 'number', array( 'min' => 1, 'step' => 1 ) ),
							self::field( 'seasonal_rate_max_group', __( 'Maximum travelers', 'hks-core' ), 'maximum_travelers', 'number', array( 'min' => 1, 'step' => 1 ) ),
							self::field( 'seasonal_rate_transport', __( 'Transport basis', 'hks-core' ), 'transport_basis', 'text' ),
							self::field( 'seasonal_rate_accommodation', __( 'Accommodation basis', 'hks-core' ), 'accommodation_basis', 'text' ),
							self::field( 'seasonal_rate_room_basis', __( 'Room or occupancy basis', 'hks-core' ), 'room_basis', 'text' ),
							self::field( 'seasonal_rate_meal_basis', __( 'Meal basis', 'hks-core' ), 'meal_basis', 'text' ),
							self::field( 'seasonal_rate_unit', __( 'Price unit', 'hks-core' ), 'price_unit', 'select', self::choice_args( Choices::price_units(), true ) ),
							self::field( 'seasonal_rate_adult_ksh', __( 'Adult price (KSh)', 'hks-core' ), 'adult_price_ksh', 'number', array( 'min' => 0, 'step' => 1, 'prepend' => 'KSh' ) ),
							self::field( 'seasonal_rate_child_ksh', __( 'Child price (KSh)', 'hks-core' ), 'child_price_ksh', 'number', array( 'min' => 0, 'step' => 1, 'prepend' => 'KSh' ) ),
							self::field( 'seasonal_rate_child_basis', __( 'Child age or sharing basis', 'hks-core' ), 'child_basis', 'text' ),
							self::field( 'seasonal_rate_single_supplement', __( 'Single supplement (KSh)', 'hks-core' ), 'single_supplement_ksh', 'number', array( 'min' => 0, 'step' => 1, 'prepend' => 'KSh' ) ),
							self::field( 'seasonal_rate_status', __( 'Rate status', 'hks-core' ), 'price_status', 'select', self::choice_args( Choices::price_status(), false ) ),
							self::field( 'seasonal_rate_checked_date', __( 'Rate checked date', 'hks-core' ), 'checked_date', 'date_picker', self::date_args() ),
							self::field( 'seasonal_rate_public_note', __( 'Public rate note', 'hks-core' ), 'public_note', 'textarea', array( 'rows' => 2, 'new_lines' => 'br' ) ),
						),
					)
				),

				self::tab( 'tour_tab_supplements', __( 'Mandatory supplements', 'hks-core' ) ),
				self::field(
					'tour_mandatory_supplements',
					__( 'Mandatory supplements', 'hks-core' ),
					'hks_mandatory_supplements',
					'repeater',
					array(
						'layout'       => 'block',
						'button_label' => __( 'Add mandatory supplement', 'hks-core' ),
						'sub_fields'   => array(
							self::field( 'supplement_name', __( 'Event, season, or supplement', 'hks-core' ), 'name', 'text' ),
							self::field( 'supplement_amount_ksh', __( 'Amount (KSh)', 'hks-core' ), 'amount_ksh', 'number', array( 'min' => 0, 'step' => 1, 'prepend' => 'KSh' ) ),
							self::field( 'supplement_unit', __( 'Unit', 'hks-core' ), 'unit', 'select', self::choice_args( Choices::price_units(), true ) ),
							self::field( 'supplement_valid_from', __( 'Applies from', 'hks-core' ), 'valid_from', 'date_picker', self::date_args() ),
							self::field( 'supplement_valid_until', __( 'Applies until', 'hks-core' ), 'valid_until', 'date_picker', self::date_args() ),
							self::field( 'supplement_applies_to', __( 'Applies to', 'hks-core' ), 'applies_to', 'text' ),
							self::field( 'supplement_status', __( 'Price status', 'hks-core' ), 'price_status', 'select', self::choice_args( Choices::price_status(), false ) ),
							self::field( 'supplement_checked_date', __( 'Checked date', 'hks-core' ), 'checked_date', 'date_picker', self::date_args() ),
							self::field( 'supplement_public_note', __( 'Public note', 'hks-core' ), 'public_note', 'textarea', array( 'rows' => 2, 'new_lines' => 'br' ) ),
						),
					)
				),
			),
			self::location( 'post_type', Tour::POST_TYPE ),
			false,
			__( 'Canonical KSh price records. Public templates must expose the selected status and every material assumption; raw working rates are intentionally excluded from REST.', 'hks-core' ),
			15
		);
	}

	/**
	 * Structured public itinerary.
	 *
	 * @return array<string, mixed>
	 */
	private static function tour_itinerary_group() {
		return self::group(
			'tour_itinerary',
			__( 'Tour: Itinerary', 'hks-core' ),
			array(
				self::field(
					'tour_itinerary',
					__( 'Itinerary days', 'hks-core' ),
					'hks_itinerary',
					'repeater',
					array(
						'layout'       => 'block',
						'button_label' => __( 'Add itinerary day', 'hks-core' ),
						'sub_fields'   => array(
							self::field( 'itinerary_day_number', __( 'Day number or range', 'hks-core' ), 'day_number', 'text', array( 'instructions' => __( 'Examples: 1, 2, or 3-4.', 'hks-core' ) ) ),
							self::field( 'itinerary_day_title', __( 'Day title', 'hks-core' ), 'day_title', 'text' ),
							self::field( 'itinerary_origin', __( 'Origin', 'hks-core' ), 'origin', 'text' ),
							self::field( 'itinerary_destination', __( 'Destination', 'hks-core' ), 'destination', 'text' ),
							self::field( 'itinerary_description', __( 'Description', 'hks-core' ), 'description', 'textarea', array( 'rows' => 6, 'new_lines' => 'wpautop' ) ),
							self::field( 'itinerary_activities', __( 'Main activities', 'hks-core' ), 'activities', 'textarea', array( 'instructions' => __( 'Enter one activity per line.', 'hks-core' ), 'rows' => 4, 'new_lines' => 'br' ) ),
							self::field( 'itinerary_accommodation', __( 'Accommodation', 'hks-core' ), 'accommodation', 'text' ),
							self::field( 'itinerary_meals', __( 'Meals', 'hks-core' ), 'meals', 'text' ),
							self::field( 'itinerary_departure_time', __( 'Approximate departure time', 'hks-core' ), 'departure_time', 'text', array( 'instructions' => __( 'Only include when operationally confirmed.', 'hks-core' ) ) ),
							self::field( 'itinerary_drive_time', __( 'Approximate drive or travel time', 'hks-core' ), 'drive_time', 'text', array( 'instructions' => __( 'Only include when supported by the source.', 'hks-core' ) ) ),
							self::field( 'itinerary_notes', __( 'Optional public notes', 'hks-core' ), 'notes', 'textarea', array( 'rows' => 3, 'new_lines' => 'br' ) ),
						),
					)
				),
			),
			self::location( 'post_type', Tour::POST_TYPE ),
			true,
			__( 'The canonical itinerary inherited by Tour and Campaign templates.', 'hks-core' ),
			20
		);
	}

	/**
	 * Structured public inclusions and exclusions.
	 *
	 * @return array<string, mixed>
	 */
	private static function tour_inclusions_group() {
		$categories = array(
			'transport'     => __( 'Transport', 'hks-core' ),
			'guide'         => __( 'Guide or driver', 'hks-core' ),
			'fees'          => __( 'Park or entry fees', 'hks-core' ),
			'accommodation' => __( 'Accommodation', 'hks-core' ),
			'meals'         => __( 'Meals', 'hks-core' ),
			'activities'    => __( 'Game drives or activities', 'hks-core' ),
			'water'         => __( 'Drinking water', 'hks-core' ),
			'pickup'        => __( 'Pickup or drop-off', 'hks-core' ),
			'taxes'         => __( 'Taxes', 'hks-core' ),
			'personal'      => __( 'Personal expenses', 'hks-core' ),
			'tips'          => __( 'Tips', 'hks-core' ),
			'optional'      => __( 'Optional activities', 'hks-core' ),
			'drinks'        => __( 'Drinks', 'hks-core' ),
			'insurance'     => __( 'Insurance', 'hks-core' ),
			'flights'       => __( 'Flights', 'hks-core' ),
			'supplements'   => __( 'Supplements', 'hks-core' ),
			'other'         => __( 'Other', 'hks-core' ),
		);

		return self::group(
			'tour_inclusions',
			__( 'Tour: Inclusions and exclusions', 'hks-core' ),
			array(
				self::field( 'tour_inclusions', __( 'Included items', 'hks-core' ), 'hks_inclusions', 'repeater', array( 'layout' => 'table', 'button_label' => __( 'Add inclusion', 'hks-core' ), 'sub_fields' => self::list_item_fields( 'inclusion', $categories ) ) ),
				self::field( 'tour_exclusions', __( 'Excluded items', 'hks-core' ), 'hks_exclusions', 'repeater', array( 'layout' => 'table', 'button_label' => __( 'Add exclusion', 'hks-core' ), 'sub_fields' => self::list_item_fields( 'exclusion', $categories ) ) ),
			),
			self::location( 'post_type', Tour::POST_TYPE ),
			true,
			__( 'Use structured rows so important inclusions and exclusions remain visible and reusable.', 'hks-core' ),
			30
		);
	}

	/**
	 * Public suitability guidance, separate from unconfirmed policy records.
	 *
	 * @return array<string, mixed>
	 */
	private static function tour_suitability_group() {
		return self::group(
			'tour_suitability',
			__( 'Tour: Public suitability and preparation', 'hks-core' ),
			array(
				self::field( 'tour_best_for', __( 'Best for', 'hks-core' ), 'hks_best_for', 'textarea', array( 'rows' => 3, 'new_lines' => 'br' ) ),
				self::field( 'tour_physical_difficulty', __( 'Physical difficulty', 'hks-core' ), 'hks_physical_difficulty', 'select', self::choice_args( array( 'easy' => __( 'Easy', 'hks-core' ), 'moderate' => __( 'Moderate', 'hks-core' ), 'challenging' => __( 'Challenging', 'hks-core' ), 'specialist' => __( 'Specialist or technical', 'hks-core' ) ), true ) ),
				self::field( 'tour_child_suitability', __( 'Child suitability', 'hks-core' ), 'hks_child_suitability', 'textarea', array( 'instructions' => __( 'Suitability guidance only. Store formal age, rate, and cancellation terms in the confirmation-governed policy group.', 'hks-core' ), 'rows' => 3, 'new_lines' => 'br' ) ),
				self::field( 'tour_accessibility_notes', __( 'Accessibility notes', 'hks-core' ), 'hks_accessibility_notes', 'textarea', array( 'rows' => 4, 'new_lines' => 'br' ) ),
				self::field( 'tour_packing_guidance', __( 'Packing guidance', 'hks-core' ), 'hks_packing_guidance', 'textarea', array( 'rows' => 5, 'new_lines' => 'br' ) ),
				self::field( 'tour_weather_season_notes', __( 'Weather and season notes', 'hks-core' ), 'hks_weather_season_notes', 'textarea', array( 'rows' => 4, 'new_lines' => 'br' ) ),
				self::field( 'tour_safety_notes', __( 'Safety and practical notes', 'hks-core' ), 'hks_safety_notes', 'textarea', array( 'instructions' => __( 'Use sourced, package-specific guidance. Do not create broad safety guarantees.', 'hks-core' ), 'rows' => 4, 'new_lines' => 'br' ) ),
			),
			self::location( 'post_type', Tour::POST_TYPE ),
			true,
			__( 'Public guidance that helps travelers judge fit and prepare for the trip.', 'hks-core' ),
			40
		);
	}

	/**
	 * Confirmation-enveloped policies, deliberately private in REST.
	 *
	 * @return array<string, mixed>
	 */
	private static function tour_policies_group() {
		return self::group(
			'tour_policies',
			__( 'Tour: Policies and confirmation records (internal)', 'hks-core' ),
			array(
				self::message( 'tour_policies_guardrail', __( 'Publishing guardrail', 'hks-core' ), __( 'Policy summaries may be rendered publicly only when their status permits it. Keep unconfirmed wording here for review; never present the status label itself as policy copy.', 'hks-core' ) ),
				self::field(
					'tour_policies',
					__( 'Package policies', 'hks-core' ),
					'hks_policies',
					'repeater',
					array(
						'layout'       => 'block',
						'button_label' => __( 'Add policy record', 'hks-core' ),
						'sub_fields'   => array(
							self::field( 'policy_type', __( 'Policy type', 'hks-core' ), 'policy_type', 'select', self::choice_args( Choices::policy_types(), false ) ),
							self::field( 'policy_public_summary', __( 'Proposed public summary', 'hks-core' ), 'public_summary', 'textarea', array( 'instructions' => __( 'Plain-language wording. It remains non-public until its status is acceptable.', 'hks-core' ), 'rows' => 4, 'new_lines' => 'br' ) ),
							self::field( 'policy_status', __( 'Confirmation status', 'hks-core' ), 'confirmation_status', 'select', self::choice_args( Choices::confirmation_status(), false ) ),
							self::field( 'policy_source_url', __( 'Source URL', 'hks-core' ), 'source_url', 'url' ),
							self::field( 'policy_source_reference', __( 'Source reference', 'hks-core' ), 'source_reference', 'text' ),
							self::field( 'policy_checked_date', __( 'Checked date', 'hks-core' ), 'checked_date', 'date_picker', self::date_args() ),
							self::field( 'policy_valid_until', __( 'Valid until', 'hks-core' ), 'valid_until', 'date_picker', self::date_args() ),
							self::field( 'policy_internal_notes', __( 'Internal notes', 'hks-core' ), 'internal_notes', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
						),
					)
				),
			),
			self::location( 'post_type', Tour::POST_TYPE ),
			false,
			__( 'Source and confirmation envelopes for child, deposit, cancellation, refund, document, insurance, and related package policies.', 'hks-core' ),
			50
		);
	}

	/**
	 * Public media selections and conversion configuration.
	 *
	 * @return array<string, mixed>
	 */
	private static function tour_media_conversion_group() {
		return self::group(
			'tour_media_conversion',
			__( 'Tour: Media and inquiry configuration', 'hks-core' ),
			array(
				self::message( 'tour_hero_mapping', __( 'Hero image mapping', 'hks-core' ), __( 'The native featured image is the Tour hero image. Record source, ownership, permission, scope, and credit on the attachment itself before assigning it.', 'hks-core' ) ),
				self::field( 'tour_gallery', __( 'Tour gallery', 'hks-core' ), 'hks_gallery', 'gallery', self::gallery_args() ),
				self::field( 'tour_vehicle_image', __( 'Vehicle image', 'hks-core' ), 'hks_vehicle_image', 'image', self::image_args() ),
				self::field( 'tour_accommodation_images', __( 'Accommodation images', 'hks-core' ), 'hks_accommodation_images', 'gallery', self::gallery_args() ),
				self::field( 'tour_route_image', __( 'Route or map image', 'hks-core' ), 'hks_route_image', 'image', self::image_args() ),
				self::field( 'tour_cta_label', __( 'CTA label override', 'hks-core' ), 'hks_cta_label', 'text', array( 'instructions' => __( 'Optional. Leave blank to use the confirmed global CTA wording.', 'hks-core' ) ) ),
				self::field( 'tour_whatsapp_package_label', __( 'WhatsApp package label', 'hks-core' ), 'hks_whatsapp_package_label', 'text', array( 'instructions' => __( 'Short, recognizable package name used in the visitor-reviewed WhatsApp message.', 'hks-core' ) ) ),
				self::field( 'tour_intake_questions', __( 'Additional intake questions', 'hks-core' ), 'hks_intake_questions', 'checkbox', array( 'instructions' => __( 'Core fields remain required globally. Select only useful package-specific additions.', 'hks-core' ), 'choices' => Choices::intake_questions(), 'layout' => 'vertical', 'return_format' => 'value', 'allow_custom' => 0, 'save_custom' => 0 ) ),
				self::field( 'tour_featured_faqs', __( 'Featured FAQs', 'hks-core' ), 'hks_featured_faqs', 'post_object', self::post_object_args( Faq::POST_TYPE, true ) ),
			),
			self::location( 'post_type', Tour::POST_TYPE ),
			true,
			__( 'Approved media assignments and controlled inputs for the quote journey.', 'hks-core' ),
			60
		);
	}

	/**
	 * Internal Tour routing configuration.
	 *
	 * @return array<string, mixed>
	 */
	private static function tour_operations_group() {
		return self::group(
			'tour_operations',
			__( 'Tour: Inquiry operations (internal)', 'hks-core' ),
			array(
				self::field( 'tour_consultant_routing_label', __( 'Consultant routing label', 'hks-core' ), 'hks_consultant_routing_label', 'text', array( 'instructions' => __( 'Optional internal routing label. Do not use it as visitor-facing copy.', 'hks-core' ) ) ),
				self::field( 'tour_conversion_notes', __( 'Internal conversion notes', 'hks-core' ), 'hks_conversion_notes', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
			),
			self::location( 'post_type', Tour::POST_TYPE ),
			false,
			__( 'Private operational metadata for the inquiry handoff.', 'hks-core' ),
			70
		);
	}

	/**
	 * Public Campaign relationship, messaging, and presentation choices.
	 *
	 * @return array<string, mixed>
	 */
	private static function campaign_public_group() {
		return self::group(
			'campaign_public',
			__( 'Campaign: Public landing-page configuration', 'hks-core' ),
			array(
				self::message(
					'campaign_native_mappings',
					__( 'Canonical WordPress field mappings', 'hks-core' ),
					__( 'Use the WordPress title as the Campaign name and the featured image as the campaign hero override. The linked Tour remains the source for itinerary, logistics, inclusions, policies, and prices.', 'hks-core' )
				),
				self::field( 'campaign_linked_tour', __( 'Linked Tour', 'hks-core' ), 'hks_linked_tour', 'post_object', array_merge( self::post_object_args( Tour::POST_TYPE, false ), array( 'required' => 1, 'multiple' => 0, 'allow_null' => 0 ) ) ),
				self::field( 'campaign_hero_headline', __( 'Hero headline', 'hks-core' ), 'hks_hero_headline', 'text', array( 'instructions' => __( 'Focused campaign promise; do not change the underlying package facts.', 'hks-core' ) ) ),
				self::field( 'campaign_supporting_copy', __( 'Supporting copy', 'hks-core' ), 'hks_supporting_copy', 'textarea', array( 'rows' => 4, 'new_lines' => 'wpautop' ) ),
				self::field( 'campaign_trust_modules', __( 'Trust modules', 'hks-core' ), 'hks_trust_modules', 'checkbox', array( 'choices' => Choices::trust_modules(), 'layout' => 'vertical', 'return_format' => 'value', 'allow_custom' => 0, 'save_custom' => 0 ) ),
				self::field( 'campaign_featured_faqs', __( 'Featured FAQs', 'hks-core' ), 'hks_featured_faqs', 'post_object', self::post_object_args( Faq::POST_TYPE, true ) ),
				self::field( 'campaign_cta_label', __( 'CTA label override', 'hks-core' ), 'hks_cta_label', 'text' ),
				self::field( 'campaign_navigation_mode', __( 'Navigation mode', 'hks-core' ), 'hks_navigation_mode', 'select', array_merge( self::choice_args( Choices::navigation_mode(), false ), array( 'default_value' => 'campaign_minimal', 'required' => 1 ) ) ),
			),
			self::location( 'post_type', Campaign::POST_TYPE ),
			true,
			__( 'Public-safe Campaign fields. Every Campaign links to one and only one canonical Tour.', 'hks-core' ),
			0
		);
	}

	/**
	 * Internal Campaign angle and creative brief.
	 *
	 * @return array<string, mixed>
	 */
	private static function campaign_brief_group() {
		return self::group(
			'campaign_brief',
			__( 'Campaign: Audience and angle brief (internal)', 'hks-core' ),
			array(
				self::field( 'campaign_internal_label', __( 'Internal campaign label', 'hks-core' ), 'hks_internal_label', 'text', array( 'instructions' => __( 'Operational label distinct from the native Campaign name.', 'hks-core' ) ) ),
				self::field( 'campaign_target_audience', __( 'Target audience or occasion', 'hks-core' ), 'hks_target_audience', 'text' ),
				self::field( 'campaign_primary_desire', __( 'Primary desire', 'hks-core' ), 'hks_primary_desire', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
				self::field( 'campaign_primary_problem', __( 'Current pressure or problem', 'hks-core' ), 'hks_primary_problem', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
				self::field( 'campaign_primary_objective', __( 'Primary objective', 'hks-core' ), 'hks_primary_objective', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
				self::field( 'campaign_primary_objection', __( 'Primary objection or trust barrier', 'hks-core' ), 'hks_primary_objection', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
				self::field( 'campaign_next_step', __( 'Intended next step', 'hks-core' ), 'hks_next_step', 'text', array( 'instructions' => __( 'Usually a qualified, visitor-reviewed WhatsApp quote request.', 'hks-core' ) ) ),
				self::field( 'campaign_copy_notes', __( 'Human rewrite and review notes', 'hks-core' ), 'hks_copy_notes', 'textarea', array( 'instructions' => __( 'Record concrete proof gaps, generic claims removed, and any wording that still needs confirmation.', 'hks-core' ), 'rows' => 4, 'new_lines' => '' ) ),
			),
			self::location( 'post_type', Campaign::POST_TYPE ),
			false,
			__( 'Private planning fields for one audience, occasion, desire, problem, or objection at a time.', 'hks-core' ),
			10
		);
	}

	/**
	 * Source-governed Campaign proof rows.
	 *
	 * @return array<string, mixed>
	 */
	private static function campaign_proof_group() {
		return self::group(
			'campaign_proof',
			__( 'Campaign: Proof records (internal)', 'hks-core' ),
			array(
				self::message( 'campaign_proof_guardrail', __( 'Proof guardrail', 'hks-core' ), __( 'Only proof rows with acceptable confirmation status may be rendered. Do not invent reviews, memberships, availability, or operational claims.', 'hks-core' ) ),
				self::field(
					'campaign_proof_points',
					__( 'Ordered proof points', 'hks-core' ),
					'hks_proof_points',
					'repeater',
					array(
						'layout'       => 'block',
						'button_label' => __( 'Add proof point', 'hks-core' ),
						'sub_fields'   => array(
							self::field( 'campaign_proof_text', __( 'Proposed public proof text', 'hks-core' ), 'proof_text', 'textarea', array( 'rows' => 3, 'new_lines' => 'br' ) ),
							self::field( 'campaign_proof_context', __( 'Supporting context', 'hks-core' ), 'supporting_context', 'textarea', array( 'rows' => 2, 'new_lines' => 'br' ) ),
							self::field( 'campaign_proof_source_url', __( 'Source URL', 'hks-core' ), 'source_url', 'url' ),
							self::field( 'campaign_proof_source_reference', __( 'Source reference', 'hks-core' ), 'source_reference', 'text' ),
							self::field( 'campaign_proof_status', __( 'Confirmation status', 'hks-core' ), 'confirmation_status', 'select', self::choice_args( Choices::confirmation_status(), false ) ),
							self::field( 'campaign_proof_checked_date', __( 'Checked date', 'hks-core' ), 'checked_date', 'date_picker', self::date_args() ),
							self::field( 'campaign_proof_internal_notes', __( 'Internal notes', 'hks-core' ), 'internal_notes', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
						),
					)
				),
			),
			self::location( 'post_type', Campaign::POST_TYPE ),
			false,
			__( 'Private source and approval envelopes for campaign-specific proof order.', 'hks-core' ),
			20
		);
	}

	/**
	 * Private Campaign lifecycle, indexing, and analytics controls.
	 *
	 * @return array<string, mixed>
	 */
	private static function campaign_governance_group() {
		return self::group(
			'campaign_governance',
			__( 'Campaign: Lifecycle and analytics (internal)', 'hks-core' ),
			array(
				self::field( 'campaign_status', __( 'Campaign lifecycle status', 'hks-core' ), 'hks_campaign_status', 'select', array_merge( self::choice_args( Choices::campaign_status(), false ), array( 'default_value' => 'draft', 'required' => 1 ) ) ),
				self::field( 'campaign_start_date', __( 'Planned start date', 'hks-core' ), 'hks_campaign_start_date', 'date_picker', self::date_args() ),
				self::field( 'campaign_end_date', __( 'Planned end date', 'hks-core' ), 'hks_campaign_end_date', 'date_picker', self::date_args() ),
				self::field( 'campaign_analytics_label', __( 'Analytics campaign label', 'hks-core' ), 'hks_analytics_campaign_label', 'text', array( 'instructions' => __( 'Stable, non-sensitive label carried into the event contract.', 'hks-core' ) ) ),
				self::field( 'campaign_meta_campaign_id', __( 'Meta campaign reference', 'hks-core' ), 'hks_meta_campaign_reference', 'text', array( 'instructions' => __( 'Optional operational reference. Do not store visitor data here.', 'hks-core' ) ) ),
				self::field( 'campaign_experiment_label', __( 'Experiment or variant label', 'hks-core' ), 'hks_experiment_label', 'text' ),
				self::field( 'campaign_noindex', __( 'Discourage search indexing', 'hks-core' ), 'hks_noindex', 'true_false', array( 'instructions' => __( 'Defaults on for temporary or paid-campaign variants. Clear only after an intentional SEO decision.', 'hks-core' ), 'ui' => 1, 'default_value' => 1 ) ),
				self::field( 'campaign_governance_notes', __( 'Internal lifecycle notes', 'hks-core' ), 'hks_governance_notes', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
			),
			self::location( 'post_type', Campaign::POST_TYPE ),
			false,
			__( 'Operational state is separate from WordPress post status. New Campaigns default to noindex.', 'hks-core' ),
			30
		);
	}

	/**
	 * Public FAQ answer; the native title stores the question.
	 *
	 * @return array<string, mixed>
	 */
	private static function faq_public_group() {
		return self::group(
			'faq_public',
			__( 'FAQ: Answer', 'hks-core' ),
			array(
				self::message( 'faq_native_mapping', __( 'Question mapping', 'hks-core' ), __( 'Use the native WordPress title as the FAQ question. Do not create a duplicate question field.', 'hks-core' ) ),
				self::field( 'faq_answer', __( 'Answer', 'hks-core' ), 'hks_faq_answer', 'wysiwyg', array( 'instructions' => __( 'Answer only with sourced, approved facts. Keep formatting simple.', 'hks-core' ), 'tabs' => 'visual', 'toolbar' => 'basic', 'media_upload' => 0, 'delay' => 0 ) ),
			),
			self::location( 'post_type', Faq::POST_TYPE ),
			true,
			__( 'Reusable FAQ answer selected by Tour and Campaign records.', 'hks-core' ),
			0
		);
	}

	/**
	 * Private FAQ provenance and confirmation.
	 *
	 * @return array<string, mixed>
	 */
	private static function faq_audit_group() {
		return self::group(
			'faq_audit',
			__( 'FAQ: Source and confirmation (internal)', 'hks-core' ),
			array(
				self::field( 'faq_confirmation_status', __( 'Confirmation status', 'hks-core' ), 'hks_confirmation_status', 'select', self::choice_args( Choices::confirmation_status(), false ) ),
				self::field( 'faq_source_url', __( 'Source URL', 'hks-core' ), 'hks_source_url', 'url' ),
				self::field( 'faq_source_reference', __( 'Source reference', 'hks-core' ), 'hks_source_reference', 'text' ),
				self::field( 'faq_checked_date', __( 'Checked date', 'hks-core' ), 'hks_checked_date', 'date_picker', self::date_args() ),
				self::field( 'faq_valid_until', __( 'Valid until', 'hks-core' ), 'hks_valid_until', 'date_picker', self::date_args() ),
				self::field( 'faq_internal_notes', __( 'Internal notes', 'hks-core' ), 'hks_internal_notes', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
			),
			self::location( 'post_type', Faq::POST_TYPE ),
			false,
			__( 'An FAQ answer must be source-governed before templates expose it.', 'hks-core' ),
			10
		);
	}

	/**
	 * Public Destination guidance stored on taxonomy terms.
	 *
	 * @return array<string, mixed>
	 */
	private static function destination_public_group() {
		return self::group(
			'destination_public',
			__( 'Destination: Public guidance', 'hks-core' ),
			array(
				self::field( 'destination_short_summary', __( 'Short summary', 'hks-core' ), 'hks_short_summary', 'textarea', array( 'rows' => 3, 'new_lines' => 'br' ) ),
				self::field( 'destination_overview', __( 'Overview', 'hks-core' ), 'hks_overview', 'wysiwyg', array( 'tabs' => 'visual', 'toolbar' => 'basic', 'media_upload' => 0, 'delay' => 0 ) ),
				self::field( 'destination_hero_image', __( 'Hero image', 'hks-core' ), 'hks_hero_image', 'image', self::image_args() ),
				self::field( 'destination_best_time', __( 'Best-time guidance', 'hks-core' ), 'hks_best_time_guidance', 'textarea', array( 'instructions' => __( 'Describe tradeoffs by season without guaranteeing sightings, weather, or availability.', 'hks-core' ), 'rows' => 4, 'new_lines' => 'br' ) ),
				self::field( 'destination_travel_time', __( 'Travel-time guidance', 'hks-core' ), 'hks_travel_time_guidance', 'textarea', array( 'instructions' => __( 'State origin and assumptions; distinguish road and flight estimates.', 'hks-core' ), 'rows' => 3, 'new_lines' => 'br' ) ),
				self::field( 'destination_map_context', __( 'Map and route context', 'hks-core' ), 'hks_map_context', 'textarea', array( 'rows' => 3, 'new_lines' => 'br' ) ),
				self::field( 'destination_seo_title', __( 'SEO title override', 'hks-core' ), 'hks_seo_title', 'text', array( 'instructions' => __( 'Optional. Leave blank to derive it from the term name.', 'hks-core' ) ) ),
				self::field( 'destination_meta_description', __( 'Meta description', 'hks-core' ), 'hks_meta_description', 'textarea', array( 'maxlength' => 170, 'rows' => 3, 'new_lines' => '' ) ),
			),
			self::location( 'taxonomy', Destination::TAXONOMY ),
			true,
			__( 'Place-specific guidance for destination archives and discovery modules.', 'hks-core' ),
			0
		);
	}

	/**
	 * Private Destination source audit.
	 *
	 * @return array<string, mixed>
	 */
	private static function destination_audit_group() {
		return self::group(
			'destination_audit',
			__( 'Destination: Source and audit (internal)', 'hks-core' ),
			array(
				self::field( 'destination_source_url', __( 'Primary source URL', 'hks-core' ), 'hks_source_url', 'url' ),
				self::field( 'destination_source_reference', __( 'Source reference', 'hks-core' ), 'hks_source_reference', 'text' ),
				self::field( 'destination_source_status', __( 'Source status', 'hks-core' ), 'hks_source_status', 'select', self::choice_args( Choices::source_status(), true ) ),
				self::field( 'destination_checked_date', __( 'Source checked date', 'hks-core' ), 'hks_source_checked_date', 'date_picker', self::date_args() ),
				self::field( 'destination_internal_notes', __( 'Internal notes', 'hks-core' ), 'hks_internal_notes', 'textarea', array( 'rows' => 4, 'new_lines' => '' ) ),
			),
			self::location( 'taxonomy', Destination::TAXONOMY ),
			false,
			__( 'Private provenance for destination guidance.', 'hks-core' ),
			10
		);
	}

	/**
	 * Private attachment-level rights and provenance.
	 *
	 * @return array<string, mixed>
	 */
	private static function attachment_rights_group() {
		return self::group(
			'attachment_rights',
			__( 'Media rights and provenance (internal)', 'hks-core' ),
			array(
				self::message( 'attachment_native_mapping', __( 'Native media fields', 'hks-core' ), __( 'Use the native attachment alt text for accessible descriptions and the native caption only when it is appropriate public context. Rights metadata below is editorial and does not automatically block a template.', 'hks-core' ) ),
				self::field( 'attachment_asset_owner', __( 'Asset owner', 'hks-core' ), 'hks_asset_owner', 'text' ),
				self::field( 'attachment_creator', __( 'Photographer or creator', 'hks-core' ), 'hks_creator', 'text' ),
				self::field( 'attachment_source_url', __( 'Source URL', 'hks-core' ), 'hks_source_url', 'url' ),
				self::field( 'attachment_source_reference', __( 'Source reference', 'hks-core' ), 'hks_source_reference', 'text' ),
				self::field( 'attachment_permission_status', __( 'Permission status', 'hks-core' ), 'hks_permission_status', 'select', self::choice_args( Choices::confirmation_status(), false ) ),
				self::field( 'attachment_usage_scopes', __( 'Approved usage scopes', 'hks-core' ), 'hks_usage_scopes', 'checkbox', array( 'choices' => Choices::media_scopes(), 'layout' => 'vertical', 'return_format' => 'value', 'allow_custom' => 0, 'save_custom' => 0 ) ),
				self::field( 'attachment_license', __( 'License or permission basis', 'hks-core' ), 'hks_license_basis', 'text' ),
				self::field( 'attachment_permission_evidence', __( 'Permission evidence', 'hks-core' ), 'hks_permission_evidence', 'file', array( 'return_format' => 'id', 'library' => 'all' ) ),
				self::field( 'attachment_permission_granted_date', __( 'Permission granted date', 'hks-core' ), 'hks_permission_granted_date', 'date_picker', self::date_args() ),
				self::field( 'attachment_permission_expiry_date', __( 'Permission expiry date', 'hks-core' ), 'hks_permission_expiry_date', 'date_picker', self::date_args() ),
				self::field( 'attachment_rights_checked_date', __( 'Rights checked date', 'hks-core' ), 'hks_rights_checked_date', 'date_picker', self::date_args() ),
				self::field( 'attachment_credit_required', __( 'Credit required', 'hks-core' ), 'hks_credit_required', 'true_false', array( 'ui' => 1, 'default_value' => 0 ) ),
				self::field( 'attachment_credit_line', __( 'Required credit line', 'hks-core' ), 'hks_credit_line', 'text' ),
				self::field( 'attachment_usage_restrictions', __( 'Usage restrictions', 'hks-core' ), 'hks_usage_restrictions', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
				self::field( 'attachment_rights_notes', __( 'Internal rights notes', 'hks-core' ), 'hks_rights_notes', 'textarea', array( 'rows' => 3, 'new_lines' => '' ) ),
			),
			self::location( 'attachment', 'all' ),
			false,
			__( 'Per-asset ownership, permission, usage scope, and credit records. Editors remain responsible for which media is assigned and published.', 'hks-core' ),
			0
		);
	}

	/**
	 * Private global site settings with per-setting confirmation metadata.
	 *
	 * @return array<string, mixed>
	 */
	private static function settings_group() {
		$page_args = array(
			'post_type'    => array( 'page' ),
			'post_status'  => array( 'publish', 'draft', 'private' ),
			'allow_null'   => 1,
			'multiple'     => 0,
			'return_format' => 'id',
			'ui'           => 1,
		);

		$fields = array(
			self::message(
				'settings_confirmation_rule',
				__( 'Confirmation rule', 'hks-core' ),
				__( 'Only the exact brand name, operator relationship, and temporary WhatsApp destination below have safe defaults. All other public values remain blank until sourced. Record a status, source, and checked date for every setting before relying on it publicly.', 'hks-core' )
			),
			self::tab( 'settings_tab_identity', __( 'Identity and contact', 'hks-core' ) ),
			self::confirmed_setting(
				'company_name',
				__( 'Exact company name', 'hks-core' ),
				'text',
				array(
					'default_value' => 'Holiday Kenya Safaris',
					'required'      => 1,
				),
				__( 'The public brand name must remain exactly Holiday Kenya Safaris.', 'hks-core' )
			),
			self::confirmed_setting(
				'operator_disclosure',
				__( 'Operator disclosure', 'hks-core' ),
				'textarea',
				array(
					'default_value' => 'Holiday Kenya Safaris is operated by Ashford Tours & Travel.',
					'required'      => 1,
					'rows'          => 2,
					'new_lines'     => 'br',
				),
				__( 'Use this relationship wherever operator context materially supports trust.', 'hks-core' )
			),
			self::confirmed_setting(
				'whatsapp_number',
				__( 'WhatsApp destination', 'hks-core' ),
				'text',
				array(
					'default_value' => '254722742799',
					'required'      => 1,
				),
				__( 'Digits only in international format, without plus signs or spaces. This is the documented temporary destination.', 'hks-core' )
			),
			self::confirmed_setting( 'public_phone', __( 'Public phone number', 'hks-core' ), 'text' ),
			self::confirmed_setting( 'public_email', __( 'Public email address', 'hks-core' ), 'email' ),
			self::confirmed_setting( 'postal_address', __( 'Public address', 'hks-core' ), 'textarea', array( 'rows' => 3, 'new_lines' => 'br' ) ),
			self::confirmed_setting( 'map_url', __( 'Map URL', 'hks-core' ), 'url' ),
			self::confirmed_setting( 'business_hours', __( 'Business hours', 'hks-core' ), 'textarea', array( 'rows' => 3, 'new_lines' => 'br' ) ),
			self::confirmed_setting( 'response_expectation', __( 'Response expectation', 'hks-core' ), 'text', array(), __( 'Do not promise a response time until operations confirms it.', 'hks-core' ) ),

			self::tab( 'settings_tab_social', __( 'Social profiles', 'hks-core' ) ),
			self::field(
				'settings_social_links',
				__( 'Social links', 'hks-core' ),
				'hks_settings_social_links',
				'repeater',
				array(
					'instructions' => __( 'Each profile is separately confirmation-governed. Do not infer profiles from an unverified handle.', 'hks-core' ),
					'layout'       => 'block',
					'button_label' => __( 'Add social profile', 'hks-core' ),
					'sub_fields'   => array(
						self::field( 'settings_social_network', __( 'Network name', 'hks-core' ), 'network', 'text' ),
						self::field( 'settings_social_url', __( 'Profile URL', 'hks-core' ), 'url', 'url' ),
						self::field( 'settings_social_status', __( 'Confirmation status', 'hks-core' ), 'confirmation_status', 'select', self::choice_args( Choices::confirmation_status(), false ) ),
						self::field( 'settings_social_source', __( 'Source reference', 'hks-core' ), 'source_reference', 'text' ),
						self::field( 'settings_social_checked_date', __( 'Checked date', 'hks-core' ), 'checked_date', 'date_picker', self::date_args() ),
						self::field( 'settings_social_notes', __( 'Internal notes', 'hks-core' ), 'internal_notes', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
					),
				)
			),

			self::tab( 'settings_tab_conversion', __( 'Conversion and pricing', 'hks-core' ) ),
			self::confirmed_setting( 'default_cta', __( 'Default quote CTA wording', 'hks-core' ), 'text', array(), __( 'Leave blank until the wording is approved. Tour and Campaign records may override it.', 'hks-core' ) ),
			self::confirmed_setting( 'global_price_disclaimer', __( 'Global price disclaimer', 'hks-core' ), 'textarea', array( 'rows' => 4, 'new_lines' => 'br' ), __( 'This complements, but never replaces, package-specific price assumptions.', 'hks-core' ) ),

			self::tab( 'settings_tab_legal', __( 'Legal and policy pages', 'hks-core' ) ),
			self::confirmed_setting( 'privacy_page', __( 'Privacy policy page', 'hks-core' ), 'post_object', $page_args ),
			self::confirmed_setting( 'terms_page', __( 'Website terms page', 'hks-core' ), 'post_object', $page_args ),
			self::confirmed_setting( 'booking_terms_page', __( 'Booking terms page', 'hks-core' ), 'post_object', $page_args ),
			self::confirmed_setting( 'cancellation_page', __( 'Cancellation and refund page', 'hks-core' ), 'post_object', $page_args ),

			self::tab( 'settings_tab_analytics', __( 'Analytics integrations', 'hks-core' ) ),
			self::message( 'settings_analytics_safety', __( 'Analytics safety', 'hks-core' ), __( 'Leave IDs blank until supplied and approved. Never send names, phone numbers, dates, budgets, or other intake answers into analytics.', 'hks-core' ) ),
			self::confirmed_setting( 'ga4_measurement_id', __( 'GA4 measurement ID', 'hks-core' ), 'text' ),
			self::confirmed_setting( 'gtm_container_id', __( 'Google Tag Manager container ID', 'hks-core' ), 'text' ),
			self::confirmed_setting( 'meta_pixel_id', __( 'Meta Pixel ID', 'hks-core' ), 'text' ),

			self::tab( 'settings_tab_brand_assets', __( 'Default brand assets', 'hks-core' ) ),
			self::confirmed_setting( 'primary_logo', __( 'Primary logo', 'hks-core' ), 'image', self::image_args() ),
			self::confirmed_setting( 'reverse_logo', __( 'Reverse logo', 'hks-core' ), 'image', self::image_args() ),
			self::confirmed_setting( 'default_social_image', __( 'Default social sharing image', 'hks-core' ), 'image', self::image_args() ),
		);

		return self::group(
			'settings',
			__( 'Holiday Kenya Safaris settings', 'hks-core' ),
			$fields,
			self::location( 'options_page', 'hks-settings' ),
			false,
			__( 'Private global configuration. Source and confirmation metadata is stored beside each public value.', 'hks-core' ),
			0
		);
	}

	/**
	 * Build a field group with consistent editor settings.
	 *
	 * @param string                    $slug        Stable group slug.
	 * @param string                    $title       Editor-facing title.
	 * @param array<int, array<string, mixed>> $fields Field definitions.
	 * @param array<int, array<int, array<string, string>>> $location Location rules.
	 * @param bool                      $show_in_rest Whether SCF values are REST-visible.
	 * @param string                    $description Group description.
	 * @param int                       $menu_order  Editor order.
	 * @return array<string, mixed>
	 */
	private static function group( $slug, $title, $fields, $location, $show_in_rest, $description, $menu_order ) {
		return array(
			'key'                   => 'group_hks_' . $slug,
			'title'                 => $title,
			'fields'                => $fields,
			'location'              => $location,
			'menu_order'            => $menu_order,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => array(),
			'active'                => true,
			'description'           => $description,
			'show_in_rest'          => $show_in_rest ? 1 : 0,
		);
	}

	/**
	 * Build one SCF field with a deterministic key.
	 *
	 * @param string               $slug Stable key suffix.
	 * @param string               $label Editor label.
	 * @param string               $name Stored field name.
	 * @param string               $type SCF field type.
	 * @param array<string, mixed> $args Type-specific settings.
	 * @return array<string, mixed>
	 */
	private static function field( $slug, $label, $name, $type, $args = array() ) {
		return array_merge(
			array(
				'key'          => 'field_hks_' . $slug,
				'label'        => $label,
				'name'         => $name,
				'aria-label'   => '',
				'type'         => $type,
				'instructions' => '',
				'required'     => 0,
				'conditional_logic' => 0,
				'wrapper'      => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
			),
			$args
		);
	}

	/**
	 * Build an editor message field.
	 *
	 * @param string $slug    Stable key suffix.
	 * @param string $label   Message heading.
	 * @param string $message Message body.
	 * @return array<string, mixed>
	 */
	private static function message( $slug, $label, $message ) {
		return self::field(
			$slug,
			$label,
			'',
			'message',
			array(
				'message'   => $message,
				'new_lines' => 'wpautop',
				'esc_html'  => 1,
			)
		);
	}

	/**
	 * Build a tab field.
	 *
	 * @param string $slug  Stable key suffix.
	 * @param string $label Tab label.
	 * @return array<string, mixed>
	 */
	private static function tab( $slug, $label ) {
		return self::field( $slug, $label, '', 'tab', array( 'placement' => 'top', 'endpoint' => 0 ) );
	}

	/**
	 * Build a standard location rule set.
	 *
	 * @param string $param Location parameter.
	 * @param string $value Required value.
	 * @return array<int, array<int, array<string, string>>>
	 */
	private static function location( $param, $value ) {
		return array(
			array(
				array(
					'param'    => $param,
					'operator' => '==',
					'value'    => $value,
				),
			),
		);
	}

	/**
	 * Shared select configuration.
	 *
	 * @param array<string, string> $choices    Controlled vocabulary.
	 * @param bool                  $allow_null Whether an unset state is allowed.
	 * @return array<string, mixed>
	 */
	private static function choice_args( $choices, $allow_null ) {
		return array(
			'choices'       => $choices,
			'default_value' => false,
			'return_format' => 'value',
			'multiple'      => 0,
			'allow_null'    => $allow_null ? 1 : 0,
			'ui'            => 1,
			'ajax'          => 0,
			'placeholder'   => $allow_null ? __( 'Select', 'hks-core' ) : '',
		);
	}

	/**
	 * Shared date picker configuration.
	 *
	 * @return array<string, mixed>
	 */
	private static function date_args() {
		return array(
			'display_format' => 'd M Y',
			'return_format'  => 'Y-m-d',
			'first_day'      => 1,
		);
	}

	/**
	 * Shared image configuration.
	 *
	 * @return array<string, mixed>
	 */
	private static function image_args() {
		return array(
			'return_format' => 'id',
			'library'       => 'all',
			'preview_size'  => 'medium',
		);
	}

	/**
	 * Shared gallery configuration.
	 *
	 * @return array<string, mixed>
	 */
	private static function gallery_args() {
		return array(
			'return_format' => 'id',
			'library'       => 'all',
			'preview_size'  => 'medium',
			'insert'        => 'append',
			'min'           => 0,
		);
	}

	/**
	 * Shared post-object relationship configuration.
	 *
	 * @param string $post_type Allowed post type.
	 * @param bool   $multiple  Whether several records may be selected.
	 * @return array<string, mixed>
	 */
	private static function post_object_args( $post_type, $multiple ) {
		return array(
			'post_type'     => array( $post_type ),
			'post_status'   => array( 'publish', 'draft', 'private' ),
			'return_format' => 'id',
			'multiple'      => $multiple ? 1 : 0,
			'allow_null'    => 1,
			'ui'            => 1,
		);
	}

	/**
	 * Shared row shape for an inclusion or exclusion.
	 *
	 * @param string                $prefix     Key prefix.
	 * @param array<string, string> $categories Controlled category choices.
	 * @return array<int, array<string, mixed>>
	 */
	private static function list_item_fields( $prefix, $categories ) {
		return array(
			self::field( $prefix . '_category', __( 'Category', 'hks-core' ), 'category', 'select', self::choice_args( $categories, true ) ),
			self::field( $prefix . '_item', __( 'Item', 'hks-core' ), 'item', 'text' ),
			self::field( $prefix . '_detail', __( 'Public detail or basis', 'hks-core' ), 'detail', 'textarea', array( 'rows' => 2, 'new_lines' => 'br' ) ),
		);
	}

	/**
	 * Wrap one global public value with private confirmation metadata.
	 *
	 * @param string               $slug         Stable setting slug.
	 * @param string               $label        Editor label.
	 * @param string               $value_type   SCF type for the public value.
	 * @param array<string, mixed> $value_args   Type-specific value settings.
	 * @param string               $instructions Optional setting guidance.
	 * @return array<string, mixed>
	 */
	private static function confirmed_setting( $slug, $label, $value_type, $value_args = array(), $instructions = '' ) {
		return self::field(
			'settings_' . $slug,
			$label,
			'hks_settings_' . $slug,
			'group',
			array(
				'instructions' => $instructions,
				'layout'       => 'block',
				'sub_fields'   => array(
					self::field( 'settings_' . $slug . '_value', __( 'Public value', 'hks-core' ), 'value', $value_type, $value_args ),
					self::field( 'settings_' . $slug . '_status', __( 'Confirmation status', 'hks-core' ), 'confirmation_status', 'select', self::choice_args( Choices::confirmation_status(), true ) ),
					self::field( 'settings_' . $slug . '_source_url', __( 'Source URL', 'hks-core' ), 'source_url', 'url' ),
					self::field( 'settings_' . $slug . '_source_reference', __( 'Source reference', 'hks-core' ), 'source_reference', 'text' ),
					self::field( 'settings_' . $slug . '_checked_date', __( 'Checked date', 'hks-core' ), 'checked_date', 'date_picker', self::date_args() ),
					self::field( 'settings_' . $slug . '_internal_notes', __( 'Internal notes', 'hks-core' ), 'internal_notes', 'textarea', array( 'rows' => 2, 'new_lines' => '' ) ),
				),
			)
		);
	}

	/**
	 * Prevent construction.
	 */
	private function __construct() {}
}
