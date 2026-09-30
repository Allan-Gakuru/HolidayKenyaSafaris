<?php
/** Background confirmation mail through wp_mail and the configured FluentSMTP transport. */
namespace HolidayKenyaSafaris\Core\Webinar;

defined( 'ABSPATH' ) || exit;

final class Notification {
	public const HOOK = 'hks_send_webinar_confirmation';

	public static function queue( int $id ): bool {
		if ( Registration::POST_TYPE !== get_post_type( $id ) ) {
			return false;
		}
		$fingerprint = self::fingerprint();
		if ( $fingerprint === get_post_meta( $id, '_hks_webinar_email_hash', true ) ) {
			return true;
		}
		if ( ! wp_next_scheduled( self::HOOK, array( $id ) ) ) {
			$result = wp_schedule_single_event( time(), self::HOOK, array( $id ), true );
			if ( is_wp_error( $result ) || ! $result ) {
				update_post_meta( $id, '_hks_webinar_email_state', 'failed' );
				return false;
			}
		}
		update_post_meta( $id, '_hks_webinar_email_state', 'queued' );
		return true;
	}

	public static function fingerprint(): string {
		return hash( 'sha256', wp_json_encode( array( Event::details()['title'], Event::start() ? Event::start()->format( DATE_ATOM ) : '', Event::details()['duration'], Event::details()['joining_url'], Event::details()['host'] ) ) );
	}

	/** A transport acceptance is recorded honestly; FluentSMTP logs confirm delivery. */
	public static function send( int $id ): bool {
		if ( Registration::POST_TYPE !== get_post_type( $id ) || 'private' !== get_post_status( $id ) || ! Event::calendar_ready() ) {
			return false;
		}
		$hash = self::fingerprint();
		if ( $hash === get_post_meta( $id, '_hks_webinar_email_hash', true ) ) {
			return true;
		}
		$lock = 'hks_webinar_mail_lock_' . $id;
		$owner = Lock::acquire( $lock, 10 * MINUTE_IN_SECONDS );
		if ( ! $owner ) {
			return false;
		}
		try {
			if ( $hash === get_post_meta( $id, '_hks_webinar_email_hash', true ) ) {
				return true;
			}
			$email = get_post_meta( $id, '_hks_webinar_email', true );
			$accepted = is_email( $email ) && wp_mail( $email, 'You’re registered: ' . Event::details()['title'], self::body( $id ), array( 'Content-Type: text/html; charset=UTF-8', 'Reply-To: Holiday Kenya Safaris <info@holidaykenyasafaris.ke>' ) );
			if ( $accepted ) {
				update_post_meta( $id, '_hks_webinar_email_hash', $hash );
				update_post_meta( $id, '_hks_webinar_email_state', 'accepted' );
				update_post_meta( $id, '_hks_webinar_email_accepted_at', current_time( 'mysql', true ) );
				delete_post_meta( $id, '_hks_webinar_email_attempts' );
			} else {
				$attempts = (int) get_post_meta( $id, '_hks_webinar_email_attempts', true ) + 1;
				update_post_meta( $id, '_hks_webinar_email_attempts', $attempts );
				update_post_meta( $id, '_hks_webinar_email_state', 'failed' );
				if ( $attempts < 3 ) {
					wp_schedule_single_event( time() + 5 * MINUTE_IN_SECONDS, self::HOOK, array( $id ) );
				}
			}
			return (bool) $accepted;
		} finally {
			Lock::release( $lock, $owner );
		}
	}

	public static function body( int $id ): string {
		$event = Event::details();
		$name = get_post_meta( $id, '_hks_webinar_name', true );
		$date = Event::start()->format( 'l j F Y' );
		$time = Event::start()->format( 'g:i a' ) . '–' . Event::end()->format( 'g:i a' ) . ' EAT (Africa/Nairobi)';
		return '<!doctype html><html lang="en"><body style="margin:0;background:#F3F1EA;color:#182B3A;font-family:Arial,sans-serif"><div style="max-width:580px;margin:0 auto;padding:32px 24px;background:#fff">'
			. '<p>Holiday Kenya Safaris<br><small>Operated by Ashford Tours &amp; Travel</small></p>'
			. '<h1 style="font-size:28px;line-height:1.25">You’re registered. Add it to your calendar.</h1>'
			. '<p>Hello ' . esc_html( $name ) . ',</p><p>Your place at our live family holiday presentation is reserved.</p>'
			. '<h2 style="font-size:21px">' . esc_html( $event['title'] ) . '</h2>'
			. '<p>' . esc_html( $date ) . '<br>' . esc_html( $time ) . '<br>Live online · ' . (int) $event['duration'] . ' minutes, including Q&amp;A</p>'
			. '<p><a href="' . esc_url( Calendar::google_url() ) . '" style="display:inline-block;background:#182B3A;color:#fff;padding:14px 20px;text-decoration:none">Add to Google Calendar</a></p>'
			. '<p><a href="' . esc_url( Calendar::download_url() ) . '" style="color:#2C7A78">Add to Apple Calendar or Outlook (.ics)</a></p>'
			. '<p>Your calendar event includes the joining link.</p><p><strong>Join the presentation:</strong><br><a href="' . esc_url( $event['joining_url'] ) . '">' . esc_html( $event['joining_url'] ) . '</a></p>'
			. ( $event['host'] ? '<p>Presented by ' . esc_html( $event['host'] ) . '.</p>' : '' )
			. '<p>We’ll walk you through the room, journey, meals, children’s activities and full family price, with time for your questions. Bring the one question you most want answered before booking.</p>'
			. '<p>Holiday places are limited and may fill during the presentation. If the holiday suits your family, come prepared for the KES 60,000 booking payment, which counts towards your holiday total.</p>'
			. '<p><small>This confirms your presentation registration. Registering does not book the holiday or take a payment. We’ll use this email for presentation details; you haven’t been enrolled in unrelated marketing.</small></p>'
			. '<p><a href="mailto:info@holidaykenyasafaris.ke" style="color:#2C7A78">info@holidaykenyasafaris.ke</a></p></div></body></html>';
	}
}
