<?php
/** Integration checks against an isolated, installed WordPress with SCF and HKS active.
 * Usage: php tools/test_webinar_wp.php /absolute/path/to/test-wordpress/wp-load.php
 * The fixture MUST define HKS_WEBINAR_TESTING=true and WP_ENVIRONMENT_TYPE=local.
 * Mail is intercepted here; no real messages are sent.
 */
if ( PHP_SAPI !== 'cli' || empty( $argv[1] ) ) { exit( "Supply the isolated test wp-load.php path.\n" ); }
require $argv[1];
if ( ! defined( 'HKS_WEBINAR_TESTING' ) || true !== HKS_WEBINAR_TESTING || 'local' !== wp_get_environment_type() ) {
	throw new RuntimeException( 'Refusing to mutate a non-test WordPress installation.' );
}
use HolidayKenyaSafaris\Core\Webinar\{Event, Registration, Notification, Calendar, Module, Lock, Admin};
use HKS_Wayfinder\WebinarPage;

$checks = 0;
function verify( $condition, string $message ): void {
	global $checks;
	if ( ! $condition ) { throw new RuntimeException( $message ); }
	++$checks;
}
function setting( string $name, $value ): void { update_field( 'field_hks_webinar_' . $name, $value, 'hks_settings' ); }
function submit( array $payload ): WP_REST_Response {
	static $address = 10;
	$_SERVER['REMOTE_ADDR'] = '192.0.2.' . ++$address;
	$request = new WP_REST_Request( 'POST', '/hks/v1/webinar/registrations' );
	$request->set_header( 'Content-Type', 'application/json' );
	$request->set_body( wp_json_encode( $payload ) );
	return rest_get_server()->dispatch( $request );
}

wp_set_current_user( 1 );
( new Module() )->ensure_pages();
$page = Event::page( 'registration' );
$thanks = Event::page( 'thanks' );
verify( $page && $thanks, 'Creates both normal Pages.' );
verify( 'webinar-registration' === get_page_template_slug( $page ), 'Registration Page uses the dedicated template.' );
wp_update_post( array( 'ID' => $page->ID, 'post_status' => 'draft' ) );
wp_update_post( array( 'ID' => $thanks->ID, 'post_status' => 'draft' ) );
setting( 'date', '' ); setting( 'time', '' ); setting( 'joining_url', '' );
verify( ! Event::accepting() && '' === Calendar::ics() && '' === Calendar::google_url(), 'Incomplete events cannot register or create invitations.' );

// Dates/URL are test data only; production seeds none of these values.
$date = ( new DateTimeImmutable( '+7 days', new DateTimeZone( Event::TIMEZONE ) ) )->format( 'Ymd' );
setting( 'date', $date ); setting( 'time', '19:00:00' ); setting( 'duration', 55 );
setting( 'joining_url', 'https://example.com/presentation?room=family&lang=en' );
setting( 'title', Event::TITLE );
verify( Event::start() && '19:00' === Event::start()->format( 'H:i' ), 'SCF formats date/time correctly in Africa/Nairobi.' );
verify( ! Event::accepting(), 'Draft Pages remain closed with complete timing.' );
foreach ( array( $page->ID, $thanks->ID ) as $id ) { wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) ); }
$privacy = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Test privacy policy', 'post_content' => 'Local fixture only.' ) );
update_option( 'wp_page_for_privacy_policy', $privacy );
setting( 'hero_image', 0 );
verify( ! Event::accepting(), 'Registration requires an editor-assigned image.' );
$image = wp_insert_attachment( array( 'post_title' => 'Local test image fixture', 'post_mime_type' => 'image/webp', 'post_status' => 'inherit' ) );
update_attached_file( $image, ABSPATH . 'wp-content/uploads/test-fixture.webp' );
setting( 'hero_image', $image );
verify( Event::accepting(), 'Complete, published event opens registration: ' . wp_json_encode( array( Event::details(), Event::privacy_url(), Event::page( 'registration' )->post_status, Event::page( 'thanks' )->post_status, Event::start()->format( DATE_ATOM ), time(), wp_attachment_is_image( $image ) ) ) );

$mail = array(); $mail_accept = true;
add_filter( 'pre_wp_mail', static function ( $value, $args ) use ( &$mail, &$mail_accept ) { $mail[] = $args; return $mail_accept; }, PHP_INT_MAX, 2 );
$values = array( 'name' => 'Test Parent', 'email' => 'parent-' . wp_generate_uuid4() . '@example.test', 'payment_band' => '60000_79999', 'priority' => 'Comfortable sleeping arrangements. Keep A\\B together.', 'website' => '', 'started_at' => (int) ( microtime( true ) * 1000 ) - 2500, 'form_token' => Event::token() );
wp_set_current_user( 0 );
verify( count( Event::BANDS ) === 3 && ! isset( Event::BANDS['50000_59999'] ), 'Only the three approved payment bands exist.' );
foreach ( array( array( 'name', '' ), array( 'email', "a@example.test\nBcc: x@example.test" ), array( 'payment_band', '50000_59999' ), array( 'payment_band', array( '60000_79999' ) ), array( 'priority', str_repeat( 'é', 501 ) ) ) as [ $field, $value ] ) {
	verify( is_wp_error( Registration::validate( array_replace( $values, array( $field => $value ) ) ) ), 'Reject invalid ' . $field );
}
verify( 403 === submit( array_replace( $values, array( 'form_token' => 'tampered' ) ) )->get_status(), 'Reject tampered token.' );
verify( 422 === submit( array_replace( $values, array( 'website' => 'spam' ) ) )->get_status(), 'Reject honeypot without false success.' );
verify( 422 === submit( array_replace( $values, array( 'started_at' => microtime( true ) * 1000 ) ) )->get_status(), 'Reject implausibly fast submissions.' );
$fail_insert = static fn( $empty, $post ) => Registration::POST_TYPE === $post['post_type'] ? true : $empty;
add_filter( 'wp_insert_post_empty_content', $fail_insert, 10, 2 );
verify( 500 === submit( $values )->get_status(), 'Storage failure never reports success.' );
remove_filter( 'wp_insert_post_empty_content', $fail_insert, 10 );
$response = submit( $values );
verify( 201 === $response->get_status(), 'Valid registration saves: ' . wp_json_encode( $response->get_data() ) );
verify( $response->get_data()['stored'] && $response->get_data()['created'], 'Success means stored.' );
verify( 0 === count( $mail ), 'Saving does not block on sending mail.' );
verify( ! str_contains( wp_json_encode( $response->get_data() ), $values['email'] ), 'No email or receipt in response URL.' );
$ids = get_posts( array( 'post_type' => Registration::POST_TYPE, 'post_status' => 'private', 'fields' => 'ids', 'meta_key' => '_hks_webinar_email', 'meta_value' => $values['email'] ) );
verify( 1 === count( $ids ), 'One private registration persisted.' ); $id = (int) $ids[0];
verify( wp_next_scheduled( Notification::HOOK, array( $id ) ), 'Mail is queued in WP Cron.' );
$duplicate = submit( array_replace( $values, array( 'email' => strtoupper( $values['email'] ) ) ) );
verify( 200 === $duplicate->get_status() && ! $duplicate->get_data()['created'], 'Case-insensitive retry is idempotent.' );
verify( 'private' === get_post_status( $id ) && ! get_post_type_object( Registration::POST_TYPE )->show_in_rest, 'Registrations stay private and off public REST.' );
verify( ! current_user_can( 'read_post', $id ), 'Anonymous users cannot read stored details.' );
verify( 404 === rest_get_server()->dispatch( new WP_REST_Request( 'GET', '/wp/v2/hks_webinar_signup' ) )->get_status(), 'No public registration collection.' );
verify( Notification::send( $id ) && 1 === count( $mail ), 'Background worker sends confirmation.' );
verify( Notification::send( $id ) && 1 === count( $mail ), 'Repeated workers do not resend accepted mail.' );
verify( str_contains( $mail[0]['message'], 'calendar.google.com' ) && str_contains( $mail[0]['message'], 'hks_webinar_calendar' ), 'Email contains Google and ICS links.' );
verify( ! str_contains( $mail[0]['message'], $values['priority'] ), 'Private qualification answers are omitted from email.' );
delete_post_meta( $id, '_hks_webinar_email_hash' ); $mail_accept = false;
verify( ! Notification::send( $id ) && 'failed' === get_post_meta( $id, '_hks_webinar_email_state', true ), 'SMTP failure is recorded without losing registration.' );
$mail_accept = true;
verify( Notification::send( $id ) && 'accepted' === get_post_meta( $id, '_hks_webinar_email_state', true ), 'Retry recovers failed mail.' );
$_COOKIE[ Registration::COOKIE ] = Registration::receipt_value( $id );
verify( $id === Registration::receipt_id(), 'Signed receipt permits genuine thank-you.' );
verify( str_contains( WebinarPage::thanks(), 'You’re registered.' ), 'Saved visitor receives calendar thank-you.' );
$_COOKIE[ Registration::COOKIE ] .= 'x';
verify( 0 === Registration::receipt_id() && ! str_contains( WebinarPage::thanks(), 'You’re registered.' ), 'Forged/direct thank-you never claims success.' );
unset( $_COOKIE[ Registration::COOKIE ] );
$google = array(); parse_str( wp_parse_url( Calendar::google_url(), PHP_URL_QUERY ), $google );
verify( 'Africa/Nairobi' === $google['ctz'] && str_contains( $google['dates'], 'T160000Z/' ), 'Google Calendar converts 19:00 EAT to 16:00 UTC.' );
$ics = Calendar::ics();
verify( str_contains( $ics, 'T165500Z' ) && str_contains( $ics, 'UID:diani-christmas-2026@holidaykenyasafaris.ke' ), 'ICS duration and UID are stable.' );
verify( str_contains( str_replace( "\r\n ", '', $ics ), Event::details()['joining_url'] ), 'ICS carries actual joining URL.' );
foreach ( explode( "\r\n", $ics ) as $line ) { verify( strlen( $line ) <= 75 && 1 === preg_match( '//u', $line ), 'ICS folding preserves UTF-8 and 75-octet limit.' ); }
verify( ! str_contains( $ics, $values['email'] ) && ! str_contains( $ics, $values['name'] ), 'Calendar has no attendee PII.' );
verify( "' =SUM(A1)" === Admin::csv_cell( ' =SUM(A1)' ), 'CSV neutralizes spreadsheet formulas.' );
delete_option( 'hks_webinar_test_lock' );
$owner = Lock::acquire( 'hks_webinar_test_lock' );
verify( $owner && '' === Lock::acquire( 'hks_webinar_test_lock' ), 'Concurrent capture cannot acquire the same lock.' );
Lock::release( 'hks_webinar_test_lock', 'wrong-owner' );
verify( $owner === get_option( 'hks_webinar_test_lock' ), 'Other requests cannot release the lock.' );
Lock::release( 'hks_webinar_test_lock', $owner );
add_option( 'hks_webinar_test_lock', ( time() - 10 ) . ':old', '', false );
$owner = Lock::acquire( 'hks_webinar_test_lock' ); verify( (bool) $owner, 'Interrupted requests expire and can recover.' );
Lock::release( 'hks_webinar_test_lock', $owner );
setting( 'date', '20200231' ); verify( null === Event::start(), 'Impossible dates cannot silently roll forward.' );
setting( 'date', '20200101' ); verify( ! Event::accepting() && Event::published(), 'Registration closes after start while invitations remain available.' );
setting( 'date', $date );
echo "Passed {$checks} WordPress webinar integration checks.\n";
