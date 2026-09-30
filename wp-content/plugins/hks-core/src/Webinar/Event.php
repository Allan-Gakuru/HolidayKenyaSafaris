<?php
/** Shared, confirmed presentation configuration; never substitutes holiday dates. */
namespace HolidayKenyaSafaris\Core\Webinar;

defined( 'ABSPATH' ) || exit;

final class Event {
	public const KEY = 'diani-christmas-2026';
	public const TIMEZONE = 'Africa/Nairobi';
	public const TITLE = 'Christmas in Diani. A holiday for you, too.';
	public const BANDS = array(
		'60000_79999' => 'KES 60,000–79,999',
		'80000_99999' => 'KES 80,000–99,999',
		'100000_plus' => 'KES 100,000 or more',
	);

	/** Flat SCF options: one source for page, mail and calendar output. */
	public static function setting( string $name, bool $format = true ) {
		$key = 'hks_webinar_' . $name;
		return function_exists( 'get_field' ) ? get_field( $key, 'hks_settings', $format ) : get_option( 'hks_settings_' . $key, '' );
	}

	public static function details(): array {
		$title = self::setting( 'title' );
		$host  = self::setting( 'presenter_name' );
		$url   = self::setting( 'joining_url' );
		return array(
			'key' => self::KEY,
			'title' => is_string( $title ) && trim( $title ) ? sanitize_text_field( $title ) : self::TITLE,
			'host' => is_string( $host ) ? sanitize_text_field( $host ) : '',
			'joining_url' => is_string( $url ) && wp_http_validate_url( $url ) && 'https' === wp_parse_url( $url, PHP_URL_SCHEME ) && ! preg_match( '/[\[\]<>]/', $url ) ? esc_url_raw( $url ) : '',
			'contact' => 'info@holidaykenyasafaris.ke',
			'duration' => max( 15, min( 180, (int) ( self::setting( 'duration' ) ?: 55 ) ) ),
			'hero_id' => absint( self::setting( 'hero_image' ) ),
			'presenter_image_id' => absint( self::setting( 'presenter_image' ) ),
		);
	}

	/** Strict parsing prevents PHP from silently rolling an invalid date forward. */
	public static function start(): ?\DateTimeImmutable {
		// Inspect raw SCF values: its display formatter can normalize an invalid date.
		$date = self::setting( 'date', false );
		$time = self::setting( 'time', false );
		if ( is_string( $date ) && preg_match( '/^\d{8}$/', $date ) ) {
			$date = substr( $date, 0, 4 ) . '-' . substr( $date, 4, 2 ) . '-' . substr( $date, 6, 2 );
		}
		if ( is_string( $time ) && preg_match( '/^\d{2}:\d{2}:00$/', $time ) ) {
			$time = substr( $time, 0, 5 );
		}
		if ( ! is_string( $date ) || ! is_string( $time ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) || ! preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $time ) ) {
			return null;
		}
		$value = $date . ' ' . $time;
		$start = \DateTimeImmutable::createFromFormat( '!Y-m-d H:i', $value, new \DateTimeZone( self::TIMEZONE ) );
		return $start && $start->format( 'Y-m-d H:i' ) === $value ? $start : null;
	}

	public static function end(): ?\DateTimeImmutable {
		$start = self::start();
		return $start ? $start->modify( '+' . self::details()['duration'] . ' minutes' ) : null;
	}

	public static function page( string $kind ) {
		$path = 'thanks' === $kind ? 'diani-christmas-presentation/thank-you' : 'diani-christmas-presentation';
		$page = get_page_by_path( $path, OBJECT, 'page' );
		return $page && $kind === get_post_meta( $page->ID, '_hks_webinar_page', true ) ? $page : null;
	}

	public static function url( string $kind ): string {
		$page = self::page( $kind );
		return $page ? (string) get_permalink( $page ) : '';
	}

	/** Reuse the existing published privacy policy, omitting unpublished routes. */
	public static function privacy_url(): string {
		$privacy = function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '';
		if ( $privacy ) {
			return $privacy;
		}
		$field = function_exists( 'get_field' ) ? get_field( 'hks_settings_privacy_page', 'hks_settings' ) : array();
		$id = is_array( $field ) ? absint( $field['value'] ?? 0 ) : absint( $field );
		return $id && 'publish' === get_post_status( $id ) ? (string) get_permalink( $id ) : '';
	}

	/** No public registration until the operational configuration is complete. */
	public static function accepting(): bool {
		$start = self::start();
		return $start && $start->getTimestamp() > time() && self::published()
			&& self::privacy_url() && wp_attachment_is_image( self::details()['hero_id'] );
	}

	public static function published(): bool {
		$page  = self::page( 'registration' );
		$thanks = self::page( 'thanks' );
		return self::calendar_ready()
			&& $page && 'publish' === $page->post_status && $thanks && 'publish' === $thanks->post_status;
	}

	/** Test dates may be supplied in tests only; production always reads the editor. */
	public static function calendar_ready(): bool {
		return null !== self::start() && '' !== self::details()['joining_url'];
	}

	public static function token(): string {
		$issued = (string) time();
		return $issued . '.' . hash_hmac( 'sha256', 'webinar|' . self::KEY . '|' . $issued, wp_salt( 'nonce' ) );
	}

	public static function verify_token( $token ): bool {
		if ( ! is_string( $token ) || ! preg_match( '/^(\d{10})\.([a-f0-9]{64})$/', $token, $parts ) ) {
			return false;
		}
		$issued = (int) $parts[1];
		return $issued <= time() && $issued >= time() - DAY_IN_SECONDS
			&& hash_equals( hash_hmac( 'sha256', 'webinar|' . self::KEY . '|' . $parts[1], wp_salt( 'nonce' ) ), $parts[2] );
	}
}
