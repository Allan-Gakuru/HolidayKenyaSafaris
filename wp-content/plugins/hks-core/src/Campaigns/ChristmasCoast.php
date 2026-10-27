<?php
/** Christmas coast exception: one normal Tour and one ID-scoped Campaign. */
namespace HolidayKenyaSafaris\Core\Campaigns;

use HolidayKenyaSafaris\Core\Contracts\Module;

defined( 'ABSPATH' ) || exit;

final class ChristmasCoast implements Module {
	private const OPTION = 'hks_christmas_coast_drafts';
	private const MARKER = '_hks_christmas_coast';

	public function register() {
		add_action( 'admin_init', array( $this, 'ensure_drafts' ) );
	}

	/** Only the recorded, code-created Campaign gets the exception; never match a title or slug. */
	public static function matches( $campaign_id ): bool {
		$ids = get_option( self::OPTION, array() );
		return $campaign_id > 0
			&& (int) ( $ids['campaign'] ?? 0 ) === (int) $campaign_id
			&& 'hks_campaign' === get_post_type( $campaign_id )
			&& 'campaign' === get_post_meta( $campaign_id, self::MARKER, true );
	}

	/** Create once, preserve editorial changes, and never publish or populate unknown facts. */
	public function ensure_drafts(): void {
		if ( ! current_user_can( 'manage_options' ) || get_option( self::OPTION ) ) {
			return;
		}
		// Atomic lock prevents concurrent admin requests from creating duplicates.
		if ( ! add_option( self::OPTION . '_lock', time(), '', false ) ) {
			return;
		}
		try {
			$records = array(
				'tour' => array( 'type' => 'hks_tour', 'slug' => 'christmas-coast-holiday', 'title' => 'Christmas Coast Holiday' ),
				'campaign' => array( 'type' => 'hks_campaign', 'slug' => 'christmas-coast-advance-saver', 'title' => 'Christmas at the coast' ),
			);
			$ids = array();
			// Preflight both routes before making any changes; unrelated content is never adopted.
			foreach ( $records as $role => $record ) {
				$post = get_page_by_path( $record['slug'], OBJECT, $record['type'] );
				if ( $post && $role !== get_post_meta( $post->ID, self::MARKER, true ) ) {
					return;
				}
				$ids[ $role ] = $post ? $post->ID : 0;
			}
			foreach ( $records as $role => $record ) {
				if ( $ids[ $role ] ) {
					continue;
				}
				$meta = array( self::MARKER => $role );
				if ( 'campaign' === $role ) {
					$meta += array(
						'hks_linked_tour' => $ids['tour'], '_hks_linked_tour' => 'field_hks_campaign_linked_tour',
						'hks_hero_headline' => 'Christmas at the coast', '_hks_hero_headline' => 'field_hks_campaign_hero_headline',
						'hks_supporting_copy' => 'Plan your Christmas coast holiday with an advance saver booking payment. Request the full holiday quote and payment details for your group.',
						'_hks_supporting_copy' => 'field_hks_campaign_supporting_copy',
						'hks_navigation_mode' => 'campaign_minimal', '_hks_navigation_mode' => 'field_hks_campaign_navigation_mode',
					);
				}
				$id = wp_insert_post( array( 'post_type' => $record['type'], 'post_status' => 'draft', 'post_title' => $record['title'], 'post_name' => $record['slug'], 'post_content' => '', 'meta_input' => $meta ), true );
				if ( is_wp_error( $id ) || ! $id ) {
					return;
				}
				$ids[ $role ] = $id;
			}
			update_option( self::OPTION, $ids, false );
		} finally {
			delete_option( self::OPTION . '_lock' );
		}
	}

	/** Only this Campaign editor receives these visible payment controls. */
	public static function field_group(): array {
		$ids = get_option( self::OPTION, array() );
		$id = (int) ( $ids['campaign'] ?? 0 );
		$fields = array(
			array( 'key' => 'field_hks_christmas_payment_help', 'label' => 'Christmas advance saver', 'name' => '', 'type' => 'message', 'message' => 'This Campaign uses an advance booking payment instead of the normal From price. Its inquiry form does not ask for a travel date. Complete and publish the linked Tour first. Enter confirmed payment details below; no amount is supplied automatically.' ),
			array( 'key' => 'field_hks_christmas_advance_ksh', 'name' => 'hks_christmas_advance_ksh', 'label' => 'Advance booking payment (KSh)', 'type' => 'number', 'min' => 1, 'step' => 1, 'instructions' => 'Required before publishing. This is part payment, not the full holiday price.' ),
			array( 'key' => 'field_hks_christmas_advance_basis', 'name' => 'hks_christmas_advance_basis', 'label' => 'Payment applies', 'type' => 'select', 'choices' => array( 'person' => 'Per person', 'family' => 'Per family', 'booking' => 'Per booking' ), 'allow_null' => 1, 'default_value' => '' ),
			array( 'key' => 'field_hks_christmas_advance_note', 'name' => 'hks_christmas_advance_note', 'label' => 'Payment details', 'type' => 'textarea', 'rows' => 3, 'instructions' => 'Optional public explanation of confirmed terms, such as credit toward the holiday total. Do not invent refund terms, deadlines or availability.' ),
		);
		return array( 'key' => 'group_hks_christmas_coast', 'title' => 'Christmas coast booking payment', 'fields' => $fields, 'location' => array( array( array( 'param' => 'post', 'operator' => '==', 'value' => self::matches( $id ) ? (string) $id : '0' ), array( 'param' => 'post_type', 'operator' => '==', 'value' => 'hks_campaign' ) ) ), 'show_in_rest' => 1 );
	}

	public static function field_names(): array {
		return array( 'hks_christmas_advance_ksh', 'hks_christmas_advance_basis', 'hks_christmas_advance_note' );
	}

	/** Match both publisher validation and display; never turn negative/fractional input into a price. */
	public static function valid_amount( $amount ): bool {
		return is_scalar( $amount ) && 1 === preg_match( '/^[1-9][0-9]{0,8}$/D', (string) $amount );
	}

	public static function payment( $campaign_id ): array {
		if ( ! self::matches( $campaign_id ) ) {
			return array();
		}
		$amount = get_post_meta( $campaign_id, 'hks_christmas_advance_ksh', true );
		$basis = get_post_meta( $campaign_id, 'hks_christmas_advance_basis', true );
		$labels = array( 'person' => __( 'per person', 'hks-core' ), 'family' => __( 'per family', 'hks-core' ), 'booking' => __( 'per booking', 'hks-core' ) );
		if ( ! self::valid_amount( $amount ) || ! is_string( $basis ) || ! isset( $labels[ $basis ] ) ) {
			return array();
		}
		return array(
			'label' => sprintf( __( 'KSh %1$s %2$s', 'hks-core' ), number_format_i18n( (int) $amount, 0 ), $labels[ $basis ] ),
			'status' => __( 'This is an advance booking payment, not the full holiday price. Request a quote for the total cost and payment terms.', 'hks-core' ),
			'note' => sanitize_textarea_field( get_post_meta( $campaign_id, 'hks_christmas_advance_note', true ) ),
		);
	}
}
