<?php
/**
 * Controlled vocabularies shared by HKS field groups.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps editorial states consistent across Tours, Campaigns, FAQs, and media.
 */
final class Choices {

	/**
	 * Source review states for canonical factual records.
	 *
	 * @return array<string, string>
	 */
	public static function source_status() {
		return array(
			'imported'         => __( 'Imported — review required', 'hks-core' ),
			'reviewed'         => __( 'Operator reviewed', 'hks-core' ),
			'client_confirmed' => __( 'Client confirmed', 'hks-core' ),
			'archived'         => __( 'Archived', 'hks-core' ),
		);
	}

	/**
	 * Shared confirmation states for claims, policies, proof, and media.
	 *
	 * @return array<string, string>
	 */
	public static function confirmation_status() {
		return array(
			'client_confirmation_required' => __( 'Client confirmation required', 'hks-core' ),
			'operator_reviewed'             => __( 'Operator reviewed', 'hks-core' ),
			'client_confirmed'              => __( 'Client confirmed', 'hks-core' ),
			'expired'                       => __( 'Expired', 'hks-core' ),
			'not_applicable'                => __( 'Not applicable', 'hks-core' ),
		);
	}

	/**
	 * Price approval states.
	 *
	 * @return array<string, string>
	 */
	public static function price_status() {
		return array(
			'placeholder'        => __( 'Placeholder — not approved', 'hks-core' ),
			'converted_estimate' => __( 'Converted estimate — not approved', 'hks-core' ),
			'operator_reviewed'  => __( 'Operator reviewed', 'hks-core' ),
			'client_confirmed'   => __( 'Client confirmed', 'hks-core' ),
			'expired'            => __( 'Expired', 'hks-core' ),
		);
	}

	/**
	 * Public price presentation modes.
	 *
	 * @return array<string, string>
	 */
	public static function price_display_mode() {
		return array(
			'from_price'          => __( 'Show “From KSh…”', 'hks-core' ),
			'request_current_rate' => __( 'Request current rate', 'hks-core' ),
			'hidden'              => __( 'Hide price', 'hks-core' ),
		);
	}

	/**
	 * Campaign operating states, separate from WordPress post status.
	 *
	 * @return array<string, string>
	 */
	public static function campaign_status() {
		return array(
			'draft'    => __( 'Draft', 'hks-core' ),
			'testing'  => __( 'Testing', 'hks-core' ),
			'active'   => __( 'Active', 'hks-core' ),
			'paused'   => __( 'Paused', 'hks-core' ),
			'archived' => __( 'Archived', 'hks-core' ),
		);
	}

	/**
	 * Campaign navigation treatments.
	 *
	 * @return array<string, string>
	 */
	public static function navigation_mode() {
		return array(
			'full'             => __( 'Full site navigation', 'hks-core' ),
			'reduced'          => __( 'Reduced navigation', 'hks-core' ),
			'campaign_minimal' => __( 'Campaign minimal', 'hks-core' ),
		);
	}

	/**
	 * Residency basis choices.
	 *
	 * @return array<string, string>
	 */
	public static function residency_basis() {
		return array(
			'kenyan_citizen'       => __( 'Kenyan citizen', 'hks-core' ),
			'resident'             => __( 'Resident', 'hks-core' ),
			'non_resident'         => __( 'Non-resident', 'hks-core' ),
			'mixed'                => __( 'Mixed', 'hks-core' ),
			'confirmation_required' => __( 'Confirmation required', 'hks-core' ),
		);
	}

	/**
	 * Supported transport modes.
	 *
	 * @return array<string, string>
	 */
	public static function transport_types() {
		return array(
			'safari_van'  => __( 'Safari van', 'hks-core' ),
			'land_cruiser' => __( 'Land Cruiser', 'hks-core' ),
			'flight'       => __( 'Flight', 'hks-core' ),
			'bus'          => __( 'Bus or coach', 'hks-core' ),
			'other'        => __( 'Other confirmed transport', 'hks-core' ),
		);
	}

	/**
	 * Price units.
	 *
	 * @return array<string, string>
	 */
	public static function price_units() {
		return array(
			'per_person' => __( 'Per person', 'hks-core' ),
			'per_group'  => __( 'Per group', 'hks-core' ),
			'per_vehicle' => __( 'Per vehicle', 'hks-core' ),
			'per_room'   => __( 'Per room', 'hks-core' ),
			'other'      => __( 'Other — explain in basis', 'hks-core' ),
		);
	}

	/**
	 * Controlled optional intake questions.
	 *
	 * @return array<string, string>
	 */
	public static function intake_questions() {
		return array(
			'departure_town'          => __( 'Departure town', 'hks-core' ),
			'adults_children'         => __( 'Adults and children', 'hks-core' ),
			'residency'               => __( 'Residency', 'hks-core' ),
			'vehicle_preference'      => __( 'Vehicle preference', 'hks-core' ),
			'accommodation_preference' => __( 'Accommodation preference', 'hks-core' ),
			'budget_range'            => __( 'Budget range', 'hks-core' ),
		);
	}

	/**
	 * Canonical policy categories.
	 *
	 * @return array<string, string>
	 */
	public static function policy_types() {
		return array(
			'child'          => __( 'Child policy', 'hks-core' ),
			'deposit'        => __( 'Deposit', 'hks-core' ),
			'cancellation'   => __( 'Cancellation and amendment', 'hks-core' ),
			'refund'         => __( 'Refund', 'hks-core' ),
			'no_show'        => __( 'No-show', 'hks-core' ),
			'documents'      => __( 'Required documents', 'hks-core' ),
			'insurance'      => __( 'Travel insurance', 'hks-core' ),
			'liability'      => __( 'Liability or force majeure', 'hks-core' ),
			'quote_validity' => __( 'Quote validity', 'hks-core' ),
			'other'          => __( 'Other package-specific policy', 'hks-core' ),
		);
	}

	/**
	 * Reusable campaign trust modules.
	 *
	 * @return array<string, string>
	 */
	public static function trust_modules() {
		return array(
			'operator_disclosure' => __( 'Operator disclosure', 'hks-core' ),
			'itinerary_clarity'   => __( 'Itinerary clarity', 'hks-core' ),
			'price_assumptions'   => __( 'Price assumptions', 'hks-core' ),
			'transport'           => __( 'Transport details', 'hks-core' ),
			'accommodation'       => __( 'Accommodation details', 'hks-core' ),
			'inclusions'          => __( 'Inclusions and exclusions', 'hks-core' ),
			'policy_summary'      => __( 'Confirmed policy summary', 'hks-core' ),
			'source_checked'      => __( 'Source checked detail', 'hks-core' ),
		);
	}

	/**
	 * Approved public media scopes.
	 *
	 * @return array<string, string>
	 */
	public static function media_scopes() {
		return array(
			'website' => __( 'Website', 'hks-core' ),
			'ads'     => __( 'Advertising', 'hks-core' ),
			'social'  => __( 'Social media', 'hks-core' ),
		);
	}

	/**
	 * Proof list ordering choices are stored as rows; no choices required.
	 */
	private function __construct() {}
}
