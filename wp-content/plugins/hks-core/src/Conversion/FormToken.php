<?php
/**
 * Signed public-form context tokens.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Conversion;

defined( 'ABSPATH' ) || exit;

/**
 * Prevents arbitrary Tour and Campaign identities from being posted to the API.
 */
final class FormToken {

	/**
	 * Maximum token age, allowing for normal full-page caching.
	 */
	private const MAX_AGE = DAY_IN_SECONDS;

	/**
	 * Issue a signed token for one public quote context.
	 *
	 * @param int $tour_id     Tour post ID.
	 * @param int $campaign_id Campaign post ID or zero.
	 * @return string
	 */
	public static function issue( $tour_id, $campaign_id ) {
		$payload = wp_json_encode(
			array(
				'tour'     => (int) $tour_id,
				'campaign' => (int) $campaign_id,
				'issued'   => time(),
			)
		);
		$encoded = self::base64_url_encode( $payload );
		$hash    = hash_hmac( 'sha256', $encoded, wp_salt( 'nonce' ) );

		return $encoded . '.' . $hash;
	}

	/**
	 * Verify that a token is intact, current, and bound to the posted context.
	 *
	 * @param string $token       Signed token.
	 * @param int    $tour_id     Posted Tour ID.
	 * @param int    $campaign_id Posted Campaign ID.
	 * @return bool
	 */
	public static function verify( $token, $tour_id, $campaign_id ) {
		$parts = explode( '.', (string) $token, 2 );

		if ( 2 !== count( $parts ) || ! ctype_xdigit( $parts[1] ) ) {
			return false;
		}

		$expected = hash_hmac( 'sha256', $parts[0], wp_salt( 'nonce' ) );

		if ( ! hash_equals( $expected, $parts[1] ) ) {
			return false;
		}

		$payload = json_decode( self::base64_url_decode( $parts[0] ), true );
		$issued  = (int) ( $payload['issued'] ?? 0 );

		return is_array( $payload )
			&& (int) ( $payload['tour'] ?? 0 ) === (int) $tour_id
			&& (int) ( $payload['campaign'] ?? 0 ) === (int) $campaign_id
			&& $issued <= time()
			&& $issued >= time() - self::MAX_AGE;
	}

	/**
	 * URL-safe base64 encoding.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	private static function base64_url_encode( $value ) {
		return rtrim( strtr( base64_encode( (string) $value ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * URL-safe base64 decoding.
	 *
	 * @param string $value Encoded value.
	 * @return string
	 */
	private static function base64_url_decode( $value ) {
		$padding = strlen( $value ) % 4;

		if ( $padding ) {
			$value .= str_repeat( '=', 4 - $padding );
		}

		return (string) base64_decode( strtr( $value, '-_', '+/' ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	}
}
