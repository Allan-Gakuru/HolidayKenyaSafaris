<?php
/** Expiring database locks, released only by their owning request. */
namespace HolidayKenyaSafaris\Core\Webinar;

defined( 'ABSPATH' ) || exit;

final class Lock {
	public static function acquire( string $key, int $seconds = 120 ): string {
		$owner = ( time() + $seconds ) . ':' . wp_generate_uuid4();
		if ( add_option( $key, $owner, '', false ) ) {
			return $owner;
		}
		$previous = (string) get_option( $key, '' );
		if ( $previous && (int) $previous < time() ) {
			self::release( $key, $previous );
			return add_option( $key, $owner, '', false ) ? $owner : '';
		}
		return '';
	}

	public static function release( string $key, string $owner ): void {
		global $wpdb;
		// Compare-and-delete prevents an expired request from releasing a newer lock.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s", $key, $owner ) );
		wp_cache_delete( $key, 'options' );
	}
}
