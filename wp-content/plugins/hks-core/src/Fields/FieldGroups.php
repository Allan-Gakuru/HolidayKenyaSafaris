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
 * Supplies the deliberately small, deterministic client-editor schema.
 */
final class FieldGroups {

	/**
	 * Return every active HKS field group in a stable registration order.
	 *
	 * Legacy field keys are intentionally not registered. Their stored values remain
	 * untouched for backward compatibility, but they no longer burden the editor.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function all() {
		return array(
			self::tour_package_group(),
			self::tour_itinerary_group(),
			self::tour_inclusions_group(),
			self::tour_suitability_group(),
			self::tour_policies_group(),
			self::tour_media_group(),
			self::campaign_public_group(),
			self::faq_public_group(),
			self::destination_public_group(),
			self::settings_group(),
		);
	}

	/**
	 * Public Tour facts used by cards and the canonical template.
	 *
	 * @return array<string, mixed>
	 */
	private static function tour_package_group() {
		return self::group(
			'tour_package',
			__( 'Tour: Package details', 'hks-core' ),
			array(
				self::message(
					'tour_native_mappings',
					__( 'Where the main content goes', 'hks-core' ),
					__( 'Use the WordPress title for the Tour name, the excerpt for the catalogue summary, the editor for the overview, and the featured image for the main Tour image.', 'hks-core' )
				),
				self::tab( 'tour_tab_summary', __( 'Summary', 'hks-core' ) ),
				self::field( 'tour_featured', __( 'Feature on the homepage', 'hks-core' ), 'hks_featured', 'true_false', array( 'instructions' => __( 'Moves this Tour ahead of non-featured Tours in the homepage selection.', 'hks-core' ), 'ui' => 1, 'default_value' => 0 ) ),
				self::field( 'tour_duration_label', __( 'Duration', 'hks-core' ), 'hks_duration_label', 'text', array( 'instructions' => __( 'Displayed publicly, for example “3 days / 2 nights” or “4 hours”.', 'hks-core' ) ) ),
				self::field( 'tour_start_location', __( 'Starts in', 'hks-core' ), 'hks_start_location', 'text', array( 'instructions' => __( 'Displayed in Tour facts and catalogue context.', 'hks-core' ) ) ),
				self::field( 'tour_end_location', __( 'Ends in', 'hks-core' ), 'hks_end_location', 'text', array( 'instructions' => __( 'Displayed in the Tour facts when entered.', 'hks-core' ) ) ),
				self::field( 'tour_route_summary', __( 'Route', 'hks-core' ), 'hks_route_summary', 'text', array( 'instructions' => __( 'Displayed on cards and Tour pages, for example “Nairobi → Maasai Mara → Nairobi”.', 'hks-core' ) ) ),

				self::tab( 'tour_tab_logistics', __( 'Practical details', 'hks-core' ) ),
				self::field( 'tour_transport_types', __( 'Transport', 'hks-core' ), 'hks_transport_types', 'checkbox', array( 'instructions' => __( 'Displayed in the Tour facts. Use Tour Type to distinguish road safaris, flying safaris, coast experiences, and other products.', 'hks-core' ), 'choices' => Choices::transport_types(), 'layout' => 'vertical', 'return_format' => 'value', 'allow_custom' => 0, 'save_custom' => 0 ) ),
				self::field( 'tour_accommodation_basis', __( 'Accommodation', 'hks-core' ), 'hks_accommodation_basis', 'textarea', array( 'instructions' => __( 'Displayed under Practical details when entered.', 'hks-core' ), 'rows' => 3, 'new_lines' => 'br' ) ),
				self::field( 'tour_meals_summary', __( 'Meals', 'hks-core' ), 'hks_meals_summary', 'text', array( 'instructions' => __( 'Displayed under Practical details. Use plain language.', 'hks-core' ) ) ),
			),
			self::location( 'post_type', Tour::POST_TYPE ),
			true,
			__( 'Only package facts consumed by the current public templates.', 'hks-core' ),
			0
		);
	}

	/**
	 * Structured itinerary fields that are rendered publicly.
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
							self::field( 'itinerary_day_number', __( 'Day number or range', 'hks-core' ), 'day_number', 'text', array( 'instructions' => __( 'Examples: 1, 2, or 3–4.', 'hks-core' ) ) ),
							self::field( 'itinerary_day_title', __( 'Day title', 'hks-core' ), 'day_title', 'text' ),
							self::field( 'itinerary_description', __( 'Description', 'hks-core' ), 'description', 'textarea', array( 'rows' => 6, 'new_lines' => 'wpautop' ) ),
							self::field( 'itinerary_activities', __( 'Main activities', 'hks-core' ), 'activities', 'textarea', array( 'rows' => 4, 'new_lines' => 'br' ) ),
							self::field( 'itinerary_accommodation', __( 'Accommodation', 'hks-core' ), 'accommodation', 'text' ),
							self::field( 'itinerary_meals', __( 'Meals', 'hks-core' ), 'meals', 'text' ),
						),
					)
				),
			),
			self::location( 'post_type', Tour::POST_TYPE ),
			true,
			__( 'The same itinerary is used by the Tour and its Campaigns.', 'hks-core' ),
			20
		);
	}

	/**
	 * Structured inclusions and exclusions.
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
			__( 'Tour: Included and excluded', 'hks-core' ),
			array(
				self::field( 'tour_inclusions', __( 'Included items', 'hks-core' ), 'hks_inclusions', 'repeater', array( 'layout' => 'table', 'button_label' => __( 'Add inclusion', 'hks-core' ), 'sub_fields' => self::list_item_fields( 'inclusion', $categories ) ) ),
				self::field( 'tour_exclusions', __( 'Excluded items', 'hks-core' ), 'hks_exclusions', 'repeater', array( 'layout' => 'table', 'button_label' => __( 'Add exclusion', 'hks-core' ), 'sub_fields' => self::list_item_fields( 'exclusion', $categories ) ) ),
			),
			self::location( 'post_type', Tour::POST_TYPE ),
			true,
			__( 'Each row is displayed publicly when it contains an item.', 'hks-core' ),
			30
		);
	}

	/**
	 * Optional practical details rendered in the Overview.
	 *
	 * @return array<string, mixed>
	 */
	private static function tour_suitability_group() {
		return self::group(
			'tour_suitability',
			__( 'Tour: Who this trip suits', 'hks-core' ),
			array(
				self::field( 'tour_best_for', __( 'Best for', 'hks-core' ), 'hks_best_for', 'textarea', array( 'instructions' => __( 'Displayed under Practical details when entered.', 'hks-core' ), 'rows' => 3, 'new_lines' => 'br' ) ),
				self::field( 'tour_child_suitability', __( 'Child suitability', 'hks-core' ), 'hks_child_suitability', 'textarea', array( 'instructions' => __( 'Displayed under Practical details when entered.', 'hks-core' ), 'rows' => 3, 'new_lines' => 'br' ) ),
				self::field( 'tour_accessibility_notes', __( 'Accessibility', 'hks-core' ), 'hks_accessibility_notes', 'textarea', array( 'instructions' => __( 'Displayed under Practical details when entered.', 'hks-core' ), 'rows' => 4, 'new_lines' => 'br' ) ),
			),
			self::location( 'post_type', Tour::POST_TYPE ),
			true,
			__( 'Optional public suitability details.', 'hks-core' ),
			40
		);
	}

	/**
	 * Public package notes without approval envelopes.
	 *
	 * @return array<string, mixed>
	 */
	private static function tour_policies_group() {
		return self::group(
			'tour_policies',
			__( 'Tour: Important package notes', 'hks-core' ),
			array(
				self::field(
					'tour_policies',
					__( 'Public notes', 'hks-core' ),
					'hks_policies',
					'repeater',
					array(
						'instructions' => __( 'Every entered note appears in Important Information.', 'hks-core' ),
						'layout'       => 'table',
						'button_label' => __( 'Add package note', 'hks-core' ),
						'sub_fields'   => array(
							self::field( 'policy_public_summary', __( 'Public note', 'hks-core' ), 'public_summary', 'textarea', array( 'rows' => 3, 'new_lines' => 'br' ) ),
						),
					)
				),
			),
			self::location( 'post_type', Tour::POST_TYPE ),
			true,
			__( 'Publishing the Tour approves the entered notes for public display.', 'hks-core' ),
			50
		);
	}

	/**
	 * Public gallery and reusable FAQs.
	 *
	 * @return array<string, mixed>
	 */
	private static function tour_media_group() {
		return self::group(
			'tour_media',
			__( 'Tour: Gallery and FAQs', 'hks-core' ),
			array(
				self::message( 'tour_hero_mapping', __( 'Images', 'hks-core' ), __( 'Use the featured image as the main Tour image. Add the remaining public images below in gallery order. Give each image useful native alt text.', 'hks-core' ) ),
				self::field( 'tour_gallery', __( 'Tour gallery', 'hks-core' ), 'hks_gallery', 'gallery', self::gallery_args() ),
				self::field( 'tour_featured_faqs', __( 'Questions shown on this Tour', 'hks-core' ), 'hks_featured_faqs', 'post_object', self::post_object_args( Faq::POST_TYPE, true ) ),
			),
			self::location( 'post_type', Tour::POST_TYPE ),
			true,
			__( 'Only media and FAQ relationships consumed by the Tour template.', 'hks-core' ),
			60
		);
	}

	/**
	 * Public Campaign fields plus the Campaign-only planning dates.
	 *
	 * @return array<string, mixed>
	 */
	private static function campaign_public_group() {
		return self::group(
			'campaign_public',
			__( 'Campaign: Landing page', 'hks-core' ),
			array(
				self::message( 'campaign_native_mappings', __( 'Campaign content', 'hks-core' ), __( 'Use the WordPress title as the Campaign name and the featured image as its hero. The linked Tour supplies the route, itinerary, inclusions, and exclusions. The optional price belongs to this Campaign only.', 'hks-core' ) ),
				self::tab( 'campaign_tab_content', __( 'Content', 'hks-core' ) ),
				self::field( 'campaign_linked_tour', __( 'Linked Tour', 'hks-core' ), 'hks_linked_tour', 'post_object', array_merge( self::post_object_args( Tour::POST_TYPE, false ), array( 'required' => 1, 'multiple' => 0, 'allow_null' => 0 ) ) ),
				self::field( 'campaign_hero_headline', __( 'Hero headline', 'hks-core' ), 'hks_hero_headline', 'text', array( 'instructions' => __( 'Displayed as the Campaign H1. Leave blank to use the Campaign title.', 'hks-core' ) ) ),
				self::field( 'campaign_supporting_copy', __( 'Supporting copy', 'hks-core' ), 'hks_supporting_copy', 'textarea', array( 'instructions' => __( 'Displayed below the Campaign headline.', 'hks-core' ), 'rows' => 4, 'new_lines' => 'wpautop' ) ),
				self::field(
					'campaign_from_price_ksh',
					__( 'From price per person (KSh)', 'hks-core' ),
					'hks_campaign_from_price_ksh',
					'number',
					array(
						'instructions' => __( 'Optional. Enter a positive whole KSh amount only when price is a selling point for this Campaign. Leave blank to omit price.', 'hks-core' ),
						'min'          => 1,
						'step'         => 1,
						'prepend'      => 'KSh',
						'append'       => __( 'per person', 'hks-core' ),
					)
				),
				self::field( 'campaign_navigation_mode', __( 'Navigation mode', 'hks-core' ), 'hks_navigation_mode', 'select', array_merge( self::choice_args( Choices::navigation_mode(), false ), array( 'default_value' => 'campaign_minimal', 'required' => 1 ) ) ),

				self::tab( 'campaign_tab_dates', __( 'Planning dates', 'hks-core' ) ),
				self::message( 'campaign_dates_note', __( 'Campaign-only dates', 'hks-core' ), __( 'These dates record the intended campaign window. They do not publish, unpublish, expire, or change this Campaign’s optional price.', 'hks-core' ) ),
				self::field( 'campaign_start_date', __( 'Start date', 'hks-core' ), 'hks_campaign_start_date', 'date_picker', self::date_args() ),
				self::field( 'campaign_end_date', __( 'End date', 'hks-core' ), 'hks_campaign_end_date', 'date_picker', self::date_args() ),
			),
			self::location( 'post_type', Campaign::POST_TYPE ),
			true,
			__( 'Campaign fields consumed by the public template, plus the explicit planning-date exception.', 'hks-core' ),
			0
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
				self::message( 'faq_native_mapping', __( 'Question', 'hks-core' ), __( 'Use the WordPress title as the question. Publishing the FAQ approves its answer for display on selected Tours.', 'hks-core' ) ),
				self::field( 'faq_answer', __( 'Answer', 'hks-core' ), 'hks_faq_answer', 'wysiwyg', array( 'instructions' => __( 'Displayed wherever this published FAQ is selected.', 'hks-core' ), 'tabs' => 'visual', 'toolbar' => 'basic', 'media_upload' => 0, 'delay' => 0 ) ),
			),
			self::location( 'post_type', Faq::POST_TYPE ),
			true,
			__( 'A published FAQ needs only a question and answer.', 'hks-core' ),
			0
		);
	}

	/**
	 * Destination values consumed by the current archive template.
	 *
	 * @return array<string, mixed>
	 */
	private static function destination_public_group() {
		return self::group(
			'destination_public',
			__( 'Destination: Public content', 'hks-core' ),
			array(
				self::field( 'destination_short_summary', __( 'Short summary', 'hks-core' ), 'hks_short_summary', 'textarea', array( 'instructions' => __( 'Displayed in the Destination introduction when entered.', 'hks-core' ), 'rows' => 3, 'new_lines' => 'br' ) ),
				self::field( 'destination_overview', __( 'Overview', 'hks-core' ), 'hks_overview', 'wysiwyg', array( 'instructions' => __( 'Displayed on the Destination archive when entered.', 'hks-core' ), 'tabs' => 'visual', 'toolbar' => 'basic', 'media_upload' => 0, 'delay' => 0 ) ),
				self::field( 'destination_hero_image', __( 'Hero image', 'hks-core' ), 'hks_hero_image', 'image', self::image_args() ),
			),
			self::location( 'taxonomy', Destination::TAXONOMY ),
			true,
			__( 'Only Destination fields consumed by the public template.', 'hks-core' ),
			0
		);
	}

	/**
	 * Global settings without confirmation envelopes.
	 *
	 * Existing values keep their group/value storage shape for compatibility.
	 *
	 * @return array<string, mixed>
	 */
	private static function settings_group() {
		$page_args = array(
			'post_type'     => array( 'page' ),
			'post_status'   => array( 'publish', 'draft', 'private' ),
			'allow_null'    => 1,
			'multiple'      => 0,
			'return_format' => 'id',
			'ui'            => 1,
		);

		$fields = array(
			self::message( 'settings_publication_rule', __( 'Settings rule', 'hks-core' ), __( 'Entered values are approved for their intended use. Leave unavailable optional settings blank.', 'hks-core' ) ),
			self::tab( 'settings_tab_identity', __( 'Identity and contact', 'hks-core' ) ),
			self::public_setting( 'company_name', __( 'Exact company name', 'hks-core' ), 'text', array( 'default_value' => 'Holiday Kenya Safaris', 'required' => 1 ), __( 'The public brand name must remain exactly Holiday Kenya Safaris.', 'hks-core' ) ),
			self::public_setting( 'operator_disclosure', __( 'Operator disclosure', 'hks-core' ), 'textarea', array( 'default_value' => 'Holiday Kenya Safaris is operated by Ashford Tours & Travel.', 'required' => 1, 'rows' => 2, 'new_lines' => 'br' ) ),
			self::public_setting( 'whatsapp_number', __( 'WhatsApp destination', 'hks-core' ), 'text', array( 'default_value' => '254722742799', 'required' => 1 ), __( 'Digits only in international format, without plus signs or spaces.', 'hks-core' ) ),
			self::public_setting( 'public_phone', __( 'Public phone number', 'hks-core' ), 'text' ),
			self::public_setting( 'public_email', __( 'Public email address', 'hks-core' ), 'email' ),
			self::public_setting( 'postal_address', __( 'Public address', 'hks-core' ), 'textarea', array( 'rows' => 3, 'new_lines' => 'br' ) ),
			self::public_setting( 'map_url', __( 'Map URL', 'hks-core' ), 'url' ),
			self::public_setting( 'business_hours', __( 'Business hours', 'hks-core' ), 'textarea', array( 'rows' => 3, 'new_lines' => 'br' ) ),
			self::public_setting( 'response_expectation', __( 'Response expectation', 'hks-core' ), 'text' ),

			self::tab( 'settings_tab_social', __( 'Social profiles', 'hks-core' ) ),
			self::field( 'settings_social_links', __( 'Social links', 'hks-core' ), 'hks_settings_social_links', 'repeater', array( 'layout' => 'table', 'button_label' => __( 'Add social profile', 'hks-core' ), 'sub_fields' => array( self::field( 'settings_social_network', __( 'Network name', 'hks-core' ), 'network', 'text' ), self::field( 'settings_social_url', __( 'Profile URL', 'hks-core' ), 'url', 'url' ) ) ) ),

			self::tab( 'settings_tab_conversion', __( 'Conversion', 'hks-core' ) ),
			self::public_setting( 'default_cta', __( 'Default quote CTA wording', 'hks-core' ), 'text' ),

			self::tab( 'settings_tab_legal', __( 'Legal and policy pages', 'hks-core' ) ),
			self::public_setting( 'privacy_page', __( 'Privacy policy page', 'hks-core' ), 'post_object', $page_args ),
			self::public_setting( 'terms_page', __( 'Website terms page', 'hks-core' ), 'post_object', $page_args ),
			self::public_setting( 'booking_terms_page', __( 'Booking terms page', 'hks-core' ), 'post_object', $page_args ),
			self::public_setting( 'cancellation_page', __( 'Cancellation and refund page', 'hks-core' ), 'post_object', $page_args ),

			self::tab( 'settings_tab_analytics', __( 'Analytics integrations', 'hks-core' ) ),
			self::message( 'settings_analytics_safety', __( 'Analytics safety', 'hks-core' ), __( 'Leave IDs blank until supplied. Never send names, phone numbers, dates, budgets, or other intake answers into analytics.', 'hks-core' ) ),
			self::public_setting( 'ga4_measurement_id', __( 'GA4 measurement ID', 'hks-core' ), 'text' ),
			self::public_setting( 'gtm_container_id', __( 'Google Tag Manager container ID', 'hks-core' ), 'text' ),
			self::public_setting( 'meta_pixel_id', __( 'Meta Pixel ID', 'hks-core' ), 'text' ),

			self::tab( 'settings_tab_brand_assets', __( 'Default brand assets', 'hks-core' ) ),
			self::public_setting( 'primary_logo', __( 'Primary logo', 'hks-core' ), 'image', self::image_args() ),
			self::public_setting( 'reverse_logo', __( 'Reverse logo', 'hks-core' ), 'image', self::image_args() ),
			self::public_setting( 'default_social_image', __( 'Default social sharing image', 'hks-core' ), 'image', self::image_args() ),
		);

		return self::group(
			'settings',
			__( 'Holiday Kenya Safaris settings', 'hks-core' ),
			$fields,
			self::location( 'options_page', 'hks-settings' ),
			false,
			__( 'Global public values and integration settings without duplicate approval fields.', 'hks-core' ),
			0
		);
	}

	/** Build a field group with consistent editor settings. */
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

	/** Build one SCF field with a deterministic key. */
	private static function field( $slug, $label, $name, $type, $args = array() ) {
		return array_merge(
			array(
				'key'               => 'field_hks_' . $slug,
				'label'             => $label,
				'name'              => $name,
				'aria-label'        => '',
				'type'              => $type,
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array( 'width' => '', 'class' => '', 'id' => '' ),
			),
			$args
		);
	}

	/** Build an editor message field. */
	private static function message( $slug, $label, $message ) {
		return self::field( $slug, $label, '', 'message', array( 'message' => $message, 'new_lines' => 'wpautop', 'esc_html' => 1 ) );
	}

	/** Build a tab field. */
	private static function tab( $slug, $label ) {
		return self::field( $slug, $label, '', 'tab', array( 'placement' => 'top', 'endpoint' => 0 ) );
	}

	/** Build a standard location rule set. */
	private static function location( $param, $value ) {
		return array( array( array( 'param' => $param, 'operator' => '==', 'value' => $value ) ) );
	}

	/** Shared select configuration. */
	private static function choice_args( $choices, $allow_null ) {
		return array( 'choices' => $choices, 'default_value' => false, 'return_format' => 'value', 'multiple' => 0, 'allow_null' => $allow_null ? 1 : 0, 'ui' => 1, 'ajax' => 0, 'placeholder' => $allow_null ? __( 'Select', 'hks-core' ) : '' );
	}

	/** Shared date picker configuration. */
	private static function date_args() {
		return array( 'display_format' => 'd M Y', 'return_format' => 'Y-m-d', 'first_day' => 1 );
	}

	/** Shared image configuration. */
	private static function image_args() {
		return array( 'return_format' => 'id', 'library' => 'all', 'preview_size' => 'medium' );
	}

	/** Shared gallery configuration. */
	private static function gallery_args() {
		return array( 'return_format' => 'id', 'library' => 'all', 'preview_size' => 'medium', 'insert' => 'append', 'min' => 0 );
	}

	/** Shared post-object relationship configuration. */
	private static function post_object_args( $post_type, $multiple ) {
		return array( 'post_type' => array( $post_type ), 'post_status' => array( 'publish', 'draft', 'private' ), 'return_format' => 'id', 'multiple' => $multiple ? 1 : 0, 'allow_null' => 1, 'ui' => 1 );
	}

	/** Shared row shape for an inclusion or exclusion. */
	private static function list_item_fields( $prefix, $categories ) {
		return array(
			self::field( $prefix . '_category', __( 'Category', 'hks-core' ), 'category', 'select', self::choice_args( $categories, true ) ),
			self::field( $prefix . '_item', __( 'Item', 'hks-core' ), 'item', 'text' ),
			self::field( $prefix . '_detail', __( 'Public detail', 'hks-core' ), 'detail', 'textarea', array( 'rows' => 2, 'new_lines' => 'br' ) ),
		);
	}

	/**
	 * Keep the old group/value storage shape while removing approval metadata.
	 */
	private static function public_setting( $slug, $label, $value_type, $value_args = array(), $instructions = '' ) {
		return self::field(
			'settings_' . $slug,
			$label,
			'hks_settings_' . $slug,
			'group',
			array(
				'instructions' => $instructions,
				'layout'       => 'block',
				'sub_fields'   => array(
					self::field( 'settings_' . $slug . '_value', __( 'Value', 'hks-core' ), 'value', $value_type, $value_args ),
				),
			)
		);
	}

	/** Prevent construction. */
	private function __construct() {}
}
