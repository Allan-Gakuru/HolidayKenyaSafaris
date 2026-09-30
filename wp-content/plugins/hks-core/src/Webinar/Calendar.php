<?php
/** Calendar invitations share the presentation configuration and contain no registrant data. */
namespace HolidayKenyaSafaris\Core\Webinar;

defined( 'ABSPATH' ) || exit;

final class Calendar {
	public static function agenda(): string {
		$event = Event::details();
		return 'Join Holiday Kenya Safaris for a practical walkthrough of the rooms, journey, meals, children’s activities and whole-family price at Diani Sea Resort, followed by live Q&A.'
			. "\n\nJoin online: " . $event['joining_url'] . "\nContact: " . $event['contact']
			. "\n\nRegistering is for the presentation. It does not book the holiday or take a payment.";
	}

	public static function google_url(): string {
		if ( ! Event::calendar_ready() ) {
			return '';
		}
		$utc = new \DateTimeZone( 'UTC' );
		$event = Event::details();
		return 'https://calendar.google.com/calendar/render?' . http_build_query( array(
			'action' => 'TEMPLATE', 'text' => $event['title'],
			'dates' => Event::start()->setTimezone( $utc )->format( 'Ymd\THis\Z' ) . '/' . Event::end()->setTimezone( $utc )->format( 'Ymd\THis\Z' ),
			// esc_url strips encoded newlines; use readable separators in the URL value.
			'ctz' => Event::TIMEZONE, 'details' => preg_replace( '/\n+/', ' | ', self::agenda() ), 'location' => $event['joining_url'],
		), '', '&', PHP_QUERY_RFC3986 );
	}

	public static function download_url(): string {
		return Event::calendar_ready() ? add_query_arg( 'action', 'hks_webinar_calendar', admin_url( 'admin-post.php' ) ) : '';
	}

	/** UTC event times are unambiguous in every calendar; original timezone is explicit. */
	public static function ics(): string {
		if ( ! Event::calendar_ready() ) {
			return '';
		}
		$event = Event::details();
		$utc = new \DateTimeZone( 'UTC' );
		$lines = array(
			'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Holiday Kenya Safaris//Family Presentation//EN',
			'CALSCALE:GREGORIAN', 'METHOD:PUBLISH', 'X-WR-TIMEZONE:' . Event::TIMEZONE,
			'BEGIN:VEVENT', 'UID:' . Event::KEY . '@holidaykenyasafaris.ke',
			'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ),
			'DTSTART:' . Event::start()->setTimezone( $utc )->format( 'Ymd\THis\Z' ),
			'DTEND:' . Event::end()->setTimezone( $utc )->format( 'Ymd\THis\Z' ),
			'SUMMARY:' . self::escape( $event['title'] ),
			'DESCRIPTION:' . self::escape( self::agenda() ),
			'LOCATION:' . self::escape( $event['joining_url'] ), 'URL:' . $event['joining_url'],
			'ORGANIZER;CN=Holiday Kenya Safaris:mailto:' . $event['contact'],
			'STATUS:CONFIRMED', 'END:VEVENT', 'END:VCALENDAR',
		);
		return implode( "\r\n", array_map( array( self::class, 'fold' ), $lines ) ) . "\r\n";
	}

	private static function escape( string $value ): string {
		return str_replace( array( '\\', "\r\n", "\r", "\n", ';', ',' ), array( '\\\\', '\\n', '\\n', '\\n', '\\;', '\\,' ), $value );
	}

	/** RFC 5545: fold at 75 octets without splitting a UTF-8 codepoint. */
	private static function fold( string $line ): string {
		$out = '';
		while ( strlen( $line ) > 75 ) {
			$cut = 75;
			while ( $cut > 0 && ( ord( $line[ $cut ] ) & 0xC0 ) === 0x80 ) {
				--$cut;
			}
			$out .= substr( $line, 0, $cut ) . "\r\n";
			$line = ' ' . substr( $line, $cut );
		}
		return $out . $line;
	}

	public static function download(): void {
		if ( ! Event::published() ) {
			wp_die( 'Presentation calendar details are not available yet.', 'Calendar unavailable', array( 'response' => 404 ) );
		}
		nocache_headers();
		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="hks-diani-presentation.ics"' );
		header( 'X-Content-Type-Options: nosniff' );
		echo self::ics(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Encoded calendar text, not HTML.
		exit;
	}
}
