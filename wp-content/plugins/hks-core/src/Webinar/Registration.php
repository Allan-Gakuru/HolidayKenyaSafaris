<?php
/** Private registration capture with an atomic per-event/email lock. */
namespace HolidayKenyaSafaris\Core\Webinar;

defined( 'ABSPATH' ) || exit;

final class Registration {
	public const POST_TYPE = 'hks_webinar_signup';
	public const COOKIE = 'hks_webinar_receipt';

	public static function register_type(): void {
		$capabilities = array_fill_keys( array( 'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'read_private_posts', 'delete_posts', 'delete_private_posts', 'delete_published_posts', 'delete_others_posts', 'edit_private_posts', 'edit_published_posts' ), 'manage_options' );
		$capabilities['create_posts'] = 'do_not_allow';
		$capabilities['publish_posts'] = 'do_not_allow';
		register_post_type( self::POST_TYPE, array(
			'labels' => array( 'name' => 'Webinar registrations', 'singular_name' => 'Webinar registration', 'edit_item' => 'View webinar registration', 'search_items' => 'Search registrations' ),
			'public' => false, 'publicly_queryable' => false, 'exclude_from_search' => true,
			'show_ui' => true, 'show_in_menu' => 'edit.php?post_type=hks_tour', 'show_in_rest' => false,
			'show_in_nav_menus' => false, 'show_in_admin_bar' => false, 'has_archive' => false,
			'rewrite' => false, 'query_var' => false, 'can_export' => false,
			'supports' => array(), 'map_meta_cap' => false, 'capabilities' => $capabilities,
			'delete_with_user' => false,
		) );
	}

	public function register_routes(): void {
		register_rest_route( 'hks/v1', '/webinar/form', array(
			'methods' => 'GET', 'callback' => array( $this, 'form_context' ), 'permission_callback' => '__return_true',
		) );
		register_rest_route( 'hks/v1', '/webinar/registrations', array(
			'methods' => 'POST', 'callback' => array( $this, 'capture' ), 'permission_callback' => '__return_true',
		) );
	}

	public function form_context() {
		if ( ! Event::accepting() ) {
			return self::error( 'event', 'Registration will open when the presentation details are confirmed.', 409 );
		}
		return self::response( array( 'form_token' => Event::token() ), 200 );
	}

	public static function validate( array $payload ) {
		foreach ( array( 'name', 'email', 'payment_band', 'priority' ) as $field ) {
			if ( isset( $payload[ $field ] ) && ! is_string( $payload[ $field ] ) ) {
				return self::error( $field, 'Please check this answer.' );
			}
		}
		$name = trim( $payload['name'] ?? '' );
		if ( '' === $name || strlen( $name ) > 560 || preg_match( '/[\r\n]/', $name ) ) {
			return self::error( 'name', 'Enter your name (up to 140 characters).' );
		}
		$name = sanitize_text_field( $name );
		if ( '' === $name || preg_match_all( '/./us', $name ) > 140 ) {
			return self::error( 'name', 'Enter your name (up to 140 characters).' );
		}
		$email = trim( $payload['email'] ?? '' );
		if ( strlen( $email ) > 254 || ! is_email( $email ) || preg_match( '/[\r\n]/', $email ) ) {
			return self::error( 'email', 'Enter a valid email address.' );
		}
		$band = $payload['payment_band'] ?? '';
		if ( ! array_key_exists( $band, Event::BANDS ) ) {
			return self::error( 'payment_band', 'Select one of the booking-payment amounts.' );
		}
		$priority = trim( $payload['priority'] ?? '' );
		if ( strlen( $priority ) > 2000 || preg_match_all( '/./us', $priority ) > 500 ) {
			return self::error( 'priority', 'Keep your answer to 500 characters or fewer.' );
		}
		return array( 'name' => $name, 'email' => strtolower( sanitize_email( $email ) ), 'payment_band' => $band, 'priority' => sanitize_textarea_field( $priority ) );
	}

	public function capture( \WP_REST_Request $request ) {
		$payload = $request->get_json_params();
		if ( ! is_array( $payload ) ) {
			return self::error( 'request', 'The registration could not be read. Please try again.' );
		}
		if ( ! Event::accepting() ) {
			return self::error( 'event', 'Registration is currently closed. Your answers have been kept on this page.', 409 );
		}
		if ( ! Event::verify_token( $payload['form_token'] ?? null ) ) {
			return self::error( 'request', 'Please refresh the page and try again.', 403 );
		}
		$address = is_string( $_SERVER['REMOTE_ADDR'] ?? null ) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
		$rate_key = 'hks_webinar_rate_' . hash_hmac( 'sha256', $address, wp_salt( 'nonce' ) );
		$count = (int) get_transient( $rate_key );
		if ( $count >= 8 ) {
			return self::error( 'request', 'Too many attempts. Please wait 15 minutes and try again.', 429 );
		}
		set_transient( $rate_key, $count + 1, 15 * MINUTE_IN_SECONDS );
		if ( ! is_string( $payload['website'] ?? '' ) || '' !== trim( $payload['website'] ?? '' ) ) {
			return self::error( 'request', 'We could not accept the registration. Please try again.' );
		}
		$started = $payload['started_at'] ?? 0;
		$now = (int) floor( microtime( true ) * 1000 );
		if ( ! is_numeric( $started ) || $started <= 0 || $started > $now || $now - $started < 1200 ) {
			return self::error( 'request', 'Take a moment to check your answers, then try again.' );
		}
		$values = self::validate( $payload );
		if ( is_wp_error( $values ) ) {
			return $values;
		}
		$identity = hash_hmac( 'sha256', Event::KEY . '|' . $values['email'], wp_salt( 'auth' ) );
		$lock = 'hks_webinar_lock_' . $identity;
		$owner = Lock::acquire( $lock );
		if ( ! $owner ) {
			return self::error( 'request', 'A registration is already being processed. Please try again in a moment.', 409 );
		}
		try {
			$existing = get_posts( array( 'post_type' => self::POST_TYPE, 'post_status' => 'private', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_hks_webinar_identity', 'meta_value' => $identity, 'no_found_rows' => true ) );
			$created = ! $existing;
			$id = $existing ? (int) $existing[0] : wp_insert_post( wp_slash( array(
				'post_type' => self::POST_TYPE, 'post_status' => 'private', 'post_author' => 0,
				'post_title' => 'Presentation registration',
				'meta_input' => array(
					'_hks_webinar_event' => Event::KEY, '_hks_webinar_identity' => $identity,
					'_hks_webinar_name' => $values['name'], '_hks_webinar_email' => $values['email'],
					'_hks_webinar_payment_band' => $values['payment_band'], '_hks_webinar_priority' => $values['priority'],
					'_hks_webinar_registered_at' => current_time( 'mysql', true ),
				),
			) ), true );
			if ( is_wp_error( $id ) || ! $id || ! hash_equals( $identity, (string) get_post_meta( $id, '_hks_webinar_identity', true ) ) ) {
				return self::error( 'request', 'We could not save your registration. Your answers are still here. Please try again.', 500 );
			}
			$stored = self::validate( array(
				'name' => get_post_meta( $id, '_hks_webinar_name', true ),
				'email' => get_post_meta( $id, '_hks_webinar_email', true ),
				'payment_band' => get_post_meta( $id, '_hks_webinar_payment_band', true ),
				'priority' => get_post_meta( $id, '_hks_webinar_priority', true ),
			) );
			if ( is_wp_error( $stored ) || ( $created && $stored !== $values ) ) {
				if ( $created ) {
					wp_delete_post( $id, true );
				}
				return self::error( 'request', 'We could not save all your answers. Please try again.', 500 );
			}
			Notification::queue( (int) $id );
			self::set_receipt( (int) $id );
			return self::response( array( 'stored' => true, 'created' => $created, 'redirect_url' => Event::url( 'thanks' ) ), $created ? 201 : 200 );
		} finally {
			Lock::release( $lock, $owner );
		}
	}

	/** A signed HttpOnly receipt enables genuine success without details in URLs. */
	public static function receipt_value( int $id ): string {
		$value = $id . '.' . ( time() + DAY_IN_SECONDS );
		return $value . '.' . hash_hmac( 'sha256', 'webinar-receipt|' . $value, wp_salt( 'auth' ) );
	}

	private static function set_receipt( int $id ): void {
		setcookie( self::COOKIE, self::receipt_value( $id ), array( 'expires' => time() + DAY_IN_SECONDS, 'path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax' ) );
	}

	public static function receipt_id(): int {
		$value = $_COOKIE[ self::COOKIE ] ?? '';
		if ( ! is_string( $value ) || ! preg_match( '/^(\d+)\.(\d{10})\.([a-f0-9]{64})$/', $value, $parts ) || (int) $parts[2] < time() ) {
			return 0;
		}
		$expected = hash_hmac( 'sha256', 'webinar-receipt|' . $parts[1] . '.' . $parts[2], wp_salt( 'auth' ) );
		$id = (int) $parts[1];
		return hash_equals( $expected, $parts[3] ) && self::POST_TYPE === get_post_type( $id ) && 'private' === get_post_status( $id ) && Event::KEY === get_post_meta( $id, '_hks_webinar_event', true ) ? $id : 0;
	}

	public static function response( array $data, int $status ): \WP_REST_Response {
		$response = new \WP_REST_Response( $data, $status );
		$response->header( 'Cache-Control', 'no-store, private' );
		return $response;
	}

	public static function error( string $field, string $message, int $status = 422 ): \WP_Error {
		return new \WP_Error( 'hks_webinar_' . $field, $message, array( 'status' => $status, 'field' => $field ) );
	}
}
