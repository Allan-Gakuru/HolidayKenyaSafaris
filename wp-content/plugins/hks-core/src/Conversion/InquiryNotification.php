<?php
/**
 * Internal email notification for saved quote inquiries.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Conversion;

use HolidayKenyaSafaris\Core\Content\PostTypes\Inquiry;

defined( 'ABSPATH' ) || exit;

/**
 * Queues and sends one operational email when an inquiry is reviewed.
 */
final class InquiryNotification {

	/**
	 * Background notification hook.
	 */
	public const CRON_HOOK = 'hks_send_inquiry_notification';

	/**
	 * Private HKS Settings field containing operational recipients.
	 */
	private const RECIPIENTS_FIELD = 'hks_settings_inquiry_notification_recipients';

	/**
	 * Queue delivery outside the visitor-facing REST request.
	 *
	 * @param int $inquiry_id Inquiry post ID.
	 * @return bool Whether delivery is scheduled.
	 */
	public static function queue( $inquiry_id ) {
		$inquiry_id = absint( $inquiry_id );
		$args       = array( $inquiry_id );

		if ( ! $inquiry_id || Inquiry::POST_TYPE !== get_post_type( $inquiry_id ) ) {
			return false;
		}

		$scheduled = wp_next_scheduled( self::CRON_HOOK, $args );
		if ( ! $scheduled ) {
			$scheduled = wp_schedule_single_event( time(), self::CRON_HOOK, $args, true );
		}

		if ( is_wp_error( $scheduled ) || false === $scheduled ) {
			update_post_meta( $inquiry_id, '_hks_inquiry_notification_failed_at', current_time( 'mysql', true ) );
			return false;
		}

		update_post_meta( $inquiry_id, '_hks_inquiry_notification_queued_at', current_time( 'mysql', true ) );

		if ( function_exists( 'spawn_cron' ) ) {
			spawn_cron();
		}

		return true;
	}

	/**
	 * Send a queued notification from the saved private inquiry.
	 *
	 * @param int $inquiry_id Inquiry post ID.
	 * @return bool Whether WordPress accepted the notification for delivery.
	 */
	public static function send_saved( $inquiry_id ) {
		$inquiry_id = absint( $inquiry_id );

		if ( ! $inquiry_id || Inquiry::POST_TYPE !== get_post_type( $inquiry_id ) ) {
			return false;
		}

		$context = array(
			'tour_id'       => absint( self::meta( $inquiry_id, 'tour_id' ) ),
			'campaign_id'   => absint( self::meta( $inquiry_id, 'campaign_id' ) ),
			'package_label' => self::meta( $inquiry_id, 'package_label' ),
		);
		$values  = array(
			'name'              => self::meta( $inquiry_id, 'name' ),
			'phone'             => self::meta( $inquiry_id, 'phone' ),
			'email'             => self::meta( $inquiry_id, 'email' ),
			'preferred_date'    => self::meta( $inquiry_id, 'preferred_date' ),
			'travelers'         => absint( self::meta( $inquiry_id, 'travelers' ) ),
			'destination_label' => self::meta( $inquiry_id, 'destination' ),
			'inquiry_route'     => self::meta( $inquiry_id, 'route' ),
			'attribution'       => self::attribution( self::meta( $inquiry_id, 'attribution' ) ),
		);

		foreach ( array( 'departure_town', 'adults', 'children', 'residency', 'vehicle_preference', 'accommodation_preference', 'budget_range' ) as $field ) {
			$value = self::meta( $inquiry_id, $field );
			if ( '' !== $value ) {
				$values[ $field ] = in_array( $field, array( 'adults', 'children' ), true ) ? absint( $value ) : $value;
			}
		}

		return self::send( $inquiry_id, InquiryRepository::reference( $inquiry_id ), $context, $values );
	}

	/**
	 * Notify the team unless this exact inquiry payload was already accepted.
	 *
	 * @param int                  $inquiry_id Inquiry post ID.
	 * @param string               $reference  Public inquiry reference.
	 * @param array<string, mixed> $context    Validated Tour/Campaign context.
	 * @param array<string, mixed> $values     Validated visitor values.
	 * @return bool Whether WordPress accepted the notification for delivery.
	 */
	public static function send( $inquiry_id, $reference, $context, $values ) {
		$recipients  = self::recipients();
		$fingerprint = self::fingerprint( $reference, $context, $values, $recipients );
		$sent_hash   = (string) get_post_meta( $inquiry_id, '_hks_inquiry_notification_hash', true );

		if ( '' !== $sent_hash && hash_equals( $sent_hash, $fingerprint ) ) {
			return true;
		}

		if ( ! $recipients ) {
			update_post_meta( $inquiry_id, '_hks_inquiry_notification_failed_at', current_time( 'mysql', true ) );
			do_action( 'hks_inquiry_notification_attempted', false, $inquiry_id, $reference, array() );
			return false;
		}

		$subject = sprintf(
			/* translators: 1: inquiry reference, 2: package name. */
			__( 'New quote request %1$s – %2$s', 'hks-core' ),
			$reference,
			$context['package_label']
		);
		$sent    = wp_mail(
			$recipients,
			$subject,
			self::body( $inquiry_id, $reference, $context, $values ),
			array( 'Content-Type: text/plain; charset=UTF-8' )
		);

		if ( $sent ) {
			update_post_meta( $inquiry_id, '_hks_inquiry_notification_hash', $fingerprint );
			update_post_meta( $inquiry_id, '_hks_inquiry_notification_sent_at', current_time( 'mysql', true ) );
			delete_post_meta( $inquiry_id, '_hks_inquiry_notification_failed_at' );
		} else {
			update_post_meta( $inquiry_id, '_hks_inquiry_notification_failed_at', current_time( 'mysql', true ) );
		}

		/**
		 * Fires after an internal quote-request notification attempt.
		 *
		 * @param bool     $sent       Whether wp_mail() accepted the message.
		 * @param int      $inquiry_id Inquiry post ID.
		 * @param string   $reference  Public inquiry reference.
		 * @param string[] $recipients Notification recipients.
		 */
		do_action( 'hks_inquiry_notification_attempted', $sent, $inquiry_id, $reference, $recipients );

		return $sent;
	}

	/**
	 * Read, validate, and deduplicate private notification recipients.
	 *
	 * @return string[]
	 */
	public static function recipients() {
		$rows       = function_exists( 'get_field' ) ? get_field( self::RECIPIENTS_FIELD, 'hks_settings' ) : array();
		$recipients = array();

		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$email = sanitize_email( is_array( $row ) ? ( $row['email'] ?? '' ) : '' );

				if ( $email && is_email( $email ) ) {
					$recipients[] = strtolower( $email );
				}
			}
		}

		/**
		 * Filters the validated internal recipients for a quote notification.
		 *
		 * @param string[] $recipients Configured recipient addresses.
		 */
		$filtered   = apply_filters( 'hks_inquiry_notification_recipients', array_values( array_unique( $recipients ) ) );
		$recipients = array();

		foreach ( is_array( $filtered ) ? $filtered : array() as $email ) {
			$email = strtolower( sanitize_email( $email ) );
			if ( $email && is_email( $email ) ) {
				$recipients[] = $email;
			}
		}

		return array_values( array_unique( $recipients ) );
	}

	/**
	 * Build the plain-text operational message.
	 *
	 * @param int                  $inquiry_id Inquiry post ID.
	 * @param string               $reference  Public inquiry reference.
	 * @param array<string, mixed> $context    Validated context.
	 * @param array<string, mixed> $values     Validated values.
	 * @return string
	 */
	private static function body( $inquiry_id, $reference, $context, $values ) {
		$lines = array(
			__( 'A visitor has submitted a quote request on Holiday Kenya Safaris.', 'hks-core' ),
			'',
			self::line( __( 'Reference', 'hks-core' ), $reference ),
			self::line( __( 'Received', 'hks-core' ), wp_date( 'j M Y, g:i a T' ) ),
			self::line( __( 'Name', 'hks-core' ), $values['name'] ),
			self::line( __( 'Phone', 'hks-core' ), $values['phone'] ),
			self::line( __( 'Email', 'hks-core' ), $values['email'] ),
			self::line( __( 'Inquiry route', 'hks-core' ), self::route_label( $values['inquiry_route'] ) ),
			self::line( __( 'Package', 'hks-core' ), $context['package_label'] ),
			self::line( __( 'Destination', 'hks-core' ), $values['destination_label'] ),
			self::line( __( 'Preferred date or month', 'hks-core' ), $values['preferred_date'] ),
			self::line( __( 'Travelers', 'hks-core' ), $values['travelers'] ),
		);

		$optional_labels = array(
			'departure_town'          => __( 'Departure town', 'hks-core' ),
			'adults'                  => __( 'Adults', 'hks-core' ),
			'children'                => __( 'Children', 'hks-core' ),
			'residency'               => __( 'Residency', 'hks-core' ),
			'vehicle_preference'      => __( 'Vehicle preference', 'hks-core' ),
			'accommodation_preference' => __( 'Accommodation preference', 'hks-core' ),
			'budget_range'             => __( 'Budget range', 'hks-core' ),
		);

		foreach ( $optional_labels as $key => $label ) {
			if ( isset( $values[ $key ] ) && '' !== (string) $values[ $key ] ) {
				$lines[] = self::line( $label, self::value_label( $key, $values[ $key ] ) );
			}
		}

		$attribution = $values['attribution'] ?? array();
		if ( is_array( $attribution ) && $attribution ) {
			$lines[] = '';
			$lines[] = __( 'Source attribution', 'hks-core' );
			foreach ( $attribution as $key => $value ) {
				$lines[] = self::line( ucwords( str_replace( '_', ' ', $key ) ), $value );
			}
		}

		$edit_link = get_edit_post_link( $inquiry_id, 'raw' );
		if ( $edit_link ) {
			$lines[] = '';
			$lines[] = self::line( __( 'Open inquiry in WordPress', 'hks-core' ), $edit_link );
		}

		$lines[] = '';
		$lines[] = __( 'This notification was generated when the visitor selected “Review quote request”.', 'hks-core' );

		return implode( "\n", array_filter( $lines, static fn( $line ) => null !== $line ) );
	}

	/**
	 * Format one labeled value.
	 *
	 * @param string $label Label.
	 * @param mixed  $value Value.
	 * @return string
	 */
	private static function line( $label, $value ) {
		return sprintf( '%1$s: %2$s', $label, (string) $value );
	}

	/**
	 * Return a readable page-source label.
	 *
	 * @param string $route Stored route key.
	 * @return string
	 */
	private static function route_label( $route ) {
		$labels = array(
			'group_travel' => __( 'Group Travel page', 'hks-core' ),
			'campaign'     => __( 'Campaign page', 'hks-core' ),
			'article'      => __( 'Travel Guide', 'hks-core' ),
			'tour'         => __( 'Tour page', 'hks-core' ),
		);

		return $labels[ $route ] ?? $route;
	}

	/**
	 * Translate stored select keys into operational labels.
	 *
	 * @param string $field Field name.
	 * @param mixed  $value Stored value.
	 * @return string
	 */
	private static function value_label( $field, $value ) {
		$labels = array(
			'residency' => array(
				'kenyan_citizen' => __( 'Kenyan citizen', 'hks-core' ),
				'resident'        => __( 'Kenyan resident', 'hks-core' ),
				'non_resident'    => __( 'Non-resident', 'hks-core' ),
				'mixed'           => __( 'Mixed group', 'hks-core' ),
				'not_sure'        => __( 'Not sure', 'hks-core' ),
			),
			'vehicle_preference' => array(
				'safari_van'    => __( 'Safari van', 'hks-core' ),
				'land_cruiser'  => __( 'Land Cruiser', 'hks-core' ),
				'no_preference' => __( 'No preference', 'hks-core' ),
				'not_sure'      => __( 'Not sure', 'hks-core' ),
			),
		);

		return $labels[ $field ][ $value ] ?? (string) $value;
	}

	/**
	 * Hash the notification content to suppress identical retries.
	 *
	 * @param string               $reference Public inquiry reference.
	 * @param array<string, mixed> $context   Validated context.
	 * @param array<string, mixed> $values    Validated values.
	 * @param string[]             $recipients Configured internal recipients.
	 * @return string
	 */
	private static function fingerprint( $reference, $context, $values, $recipients ) {
		return hash(
			'sha256',
			(string) wp_json_encode(
				array(
					'reference'  => $reference,
					'context'    => $context,
					'values'     => $values,
					'recipients' => $recipients,
				)
			)
		);
	}

	/**
	 * Read one protected value from a saved inquiry.
	 *
	 * @param int    $inquiry_id Inquiry post ID.
	 * @param string $name       Metadata suffix.
	 * @return string
	 */
	private static function meta( $inquiry_id, $name ) {
		return (string) get_post_meta( $inquiry_id, '_hks_inquiry_' . $name, true );
	}

	/**
	 * Decode stored allowlisted attribution.
	 *
	 * @param string $encoded Stored JSON.
	 * @return array<string, string>
	 */
	private static function attribution( $encoded ) {
		$attribution = json_decode( $encoded, true );

		return is_array( $attribution ) ? $attribution : array();
	}
}
