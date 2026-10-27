<?php
/** Integration regression checks. Run only against the isolated local WordPress fixture. */
if ( PHP_SAPI !== 'cli' || empty( $argv[1] ) ) { exit( "Supply an isolated wp-load.php path.\n" ); }
require $argv[1];
if ( ! defined( 'HKS_WEBINAR_TESTING' ) || true !== HKS_WEBINAR_TESTING || 'local' !== wp_get_environment_type() ) {
	throw new RuntimeException( 'Refusing to change a non-test WordPress installation.' );
}
use HolidayKenyaSafaris\Core\Campaigns\ChristmasCoast;
use HolidayKenyaSafaris\Core\Conversion\{FormToken, InquiryNotification, QuoteBlock};
use HolidayKenyaSafaris\Core\Fields\{FieldsModule, PublicationRules};
use HKS_Wayfinder\TourBlocks;

$checks = 0;
function check_christmas( $condition, $message ) {
	global $checks;
	if ( ! $condition ) { throw new RuntimeException( $message ); }
	++$checks;
}
function christmas_request( $payload ) {
	static $ip = 30;
	$_SERVER['REMOTE_ADDR'] = '192.0.2.' . ++$ip;
	$request = new WP_REST_Request( 'POST', '/hks/v1/inquiries' );
	$request->set_header( 'Content-Type', 'application/json' );
	$request->set_body( wp_json_encode( $payload ) );
	return rest_get_server()->dispatch( $request );
}
wp_set_current_user( 1 );
$module = new ChristmasCoast();
$module->ensure_drafts();
$ids = get_option( 'hks_christmas_coast_drafts' );
$tour = $ids['tour']; $campaign = $ids['campaign'];
check_christmas( 'draft' === get_post_status( $tour ) && 'draft' === get_post_status( $campaign ), 'Both records start as drafts.' );
check_christmas( (int) get_post_meta( $campaign, 'hks_linked_tour', true ) === (int) $tour, 'Campaign links to its coast Tour.' );
check_christmas( ChristmasCoast::matches( $campaign ) && ! ChristmasCoast::matches( $tour ) && ! ChristmasCoast::matches( 0 ), 'Only the recorded Campaign matches.' );
$module->ensure_drafts();
check_christmas( $ids === get_option( 'hks_christmas_coast_drafts' ), 'Repeated setup is idempotent.' );
( new FieldsModule() )->register_field_groups();
$group = ChristmasCoast::field_group();
check_christmas( (string) $campaign === $group['location'][0][0]['value'], 'Payment controls are restricted to the saved Campaign ID.' );
check_christmas( ! ChristmasCoast::payment( $campaign ), 'Unconfirmed payment stays blank.' );
$snapshot = array();
foreach ( array_merge( ChristmasCoast::field_names(), array( 'hks_campaign_from_price_ksh' ) ) as $key ) { $snapshot[ $key ] = get_post_meta( $campaign, $key, true ); }
$tour_price = get_post_meta( $tour, 'hks_from_price_ksh', true );
$normal = 0; $inquiries = array(); $mail = array();
add_filter( 'pre_wp_mail', static function ( $value, $args ) use ( &$mail ) { $mail[] = $args; return true; }, PHP_INT_MAX, 2 );
add_filter( 'hks_inquiry_notification_recipients', static fn() => array( 'fixture@example.test' ) );
try {
	wp_update_post( array( 'ID' => $tour, 'post_status' => 'publish' ) );
	wp_update_post( array( 'ID' => $campaign, 'post_status' => 'publish' ) );
	check_christmas( 'draft' === get_post_status( $campaign ), 'Publishing without confirmed payment is blocked.' );
	$native = array( 'post_title' => 'Christmas at the coast' );
	$valid = array( 'hks_linked_tour' => $tour, 'hks_christmas_advance_ksh' => '12500', 'hks_christmas_advance_basis' => 'family' );
	foreach ( array( '', '-5', '0', '12.5', '1e4', array( 12500 ), '1000000000' ) as $bad ) {
		check_christmas( ! empty( PublicationRules::validate( 'hks_campaign', array_replace( $valid, array( 'hks_christmas_advance_ksh' => $bad ) ), $campaign, $native ) ), 'Reject invalid booking payment.' );
	}
	check_christmas( ! empty( PublicationRules::validate( 'hks_campaign', array_replace( $valid, array( 'hks_christmas_advance_basis' => '' ) ), $campaign, $native ) ), 'Payment basis is required.' );
	check_christmas( ! empty( PublicationRules::validate( 'hks_campaign', $valid + array( 'hks_christmas_advance_note' => 'CLIENT CONFIRMATION REQUIRED' ), $campaign, $native ) ), 'Public placeholder cannot be published.' );
	update_field( 'field_hks_tour_from_price_ksh', 99000, $tour );
	update_field( 'field_hks_campaign_from_price_ksh', 88000, $campaign );
	$summary = new ReflectionMethod( TourBlocks::class, 'campaign_price_summary' );
	check_christmas( array() === $summary->invoke( null, $campaign, $tour ), 'Blank retainer cannot fall back to either full price.' );
	foreach ( $valid as $key => $value ) { update_post_meta( $campaign, $key, $value ); }
	update_field( 'field_hks_christmas_advance_note', 'Local test terms only.', $campaign );
	wp_update_post( array( 'ID' => $campaign, 'post_status' => 'publish' ) );
	check_christmas( 'publish' === get_post_status( $campaign ), 'Complete Campaign can publish with a public Tour.' );
	$price = $summary->invoke( null, $campaign, $tour );
	check_christmas( 'KSh 12,500 per family' === $price['label'] && str_contains( $price['status'], 'not the full holiday price' ), 'Advance payment has its correct basis and disclosure.' );
	$normal = wp_insert_post( array( 'post_type' => 'hks_campaign', 'post_status' => 'draft', 'post_title' => 'Local ordinary Campaign fixture', 'meta_input' => array( 'hks_linked_tour' => $tour ) ) );
	wp_update_post( array( 'ID' => $normal, 'post_status' => 'publish' ) );
	check_christmas( 'publish' === get_post_status( $normal ) && ! ChristmasCoast::matches( $normal ), 'An ordinary Campaign needs no retainer settings.' );
	check_christmas( 'From KSh 99,000 per person' === $summary->invoke( null, $normal, $tour )['label'], 'Other Campaigns inherit their Tour starting price.' );
	update_post_meta( $normal, '_hks_christmas_coast', 'campaign' );
	check_christmas( ! ChristmasCoast::matches( $normal ), 'Copying the marker does not enable the exception.' );
	update_post_meta( $normal, 'hks_campaign_from_price_ksh', 55000 );
	check_christmas( 'From KSh 55,000 per person' === $summary->invoke( null, $normal, $tour )['label'], 'Normal Campaign overrides still work.' );
	foreach ( array( $campaign => false, $normal => true, $tour => true ) as $id => $has_date ) {
		$GLOBALS['post'] = get_post( $id ); setup_postdata( $GLOBALS['post'] );
		$html = QuoteBlock::render( array() );
		check_christmas( '' !== $html && str_contains( $html, 'name="preferred_date"' ) === $has_date, 'Only the Christmas form omits its date field.' );
		check_christmas( str_contains( $html, 'name="email"' ) && str_contains( $html, 'name="travelers"' ), 'Essential qualification fields remain.' );
	}
	wp_set_current_user( 0 );
	$base = array( 'tour_id' => $tour, 'campaign_id' => $campaign, 'form_token' => FormToken::issue( $tour, $campaign ), 'request_key' => wp_generate_uuid4(), 'started_at' => (int) ( microtime( true ) * 1000 ) - 3000, 'name' => 'Fixture Traveller', 'phone' => '0712 345 678', 'email' => 'traveller@example.test', 'travelers' => 2, 'website' => '' );
	$first = christmas_request( $base );
	check_christmas( 201 === $first->get_status() && $first->get_data()['saved'], 'Date-free Campaign inquiry saves: ' . wp_json_encode( $first->get_data() ) );
	$id = (int) substr( $first->get_data()['reference'], 4 ); $inquiries[] = $id;
	check_christmas( 'private' === get_post_status( $id ) && '' === get_post_meta( $id, '_hks_inquiry_preferred_date', true ), 'Private inquiry contains no fabricated date.' );
	check_christmas( wp_next_scheduled( InquiryNotification::CRON_HOOK, array( $id ) ) && ! $mail, 'Notification is queued without sending during save.' );
	check_christmas( 200 === christmas_request( $base )->get_status(), 'Identical retries reuse the saved inquiry.' );
	check_christmas( 200 === christmas_request( $base + array( 'preferred_date' => 'forged date' ) )->get_status() && '' === get_post_meta( $id, '_hks_inquiry_preferred_date', true ), 'Out-of-scope dates are discarded.' );
	check_christmas( InquiryNotification::send_saved( $id ) && count( $mail ) === 1, 'Background notification is accepted.' );
	check_christmas( ! str_contains( $mail[0]['message'], 'Preferred travel date' ) && str_contains( $mail[0]['message'], 'Christmas coast advance saver' ), 'Team notification has offer context and no blank date line.' );
	check_christmas( InquiryNotification::send_saved( $id ) && count( $mail ) === 1, 'Repeated notification is deduplicated.' );
	foreach ( array( $normal, 0 ) as $other ) {
		$payload = array_replace( $base, array( 'campaign_id' => $other, 'form_token' => FormToken::issue( $tour, $other ), 'request_key' => wp_generate_uuid4(), 'collects_date' => false, 'hksChristmasCoast' => true ) );
		$response = christmas_request( $payload );
		check_christmas( 422 === $response->get_status() && 'preferred_date' === $response->get_data()['data']['field'], 'Client flags cannot waive dates on another page.' );
	}
	check_christmas( 403 === christmas_request( array_replace( $base, array( 'campaign_id' => $normal ) ) )->get_status(), 'Signed token cannot move to another Campaign.' );
	update_post_meta( $campaign, 'hks_christmas_advance_ksh', '' );
	check_christmas( 422 === christmas_request( $base )->get_status(), 'Cleared payment closes stale public forms.' );
} finally {
	wp_set_current_user( 1 );
	wp_update_post( array( 'ID' => $campaign, 'post_status' => 'draft' ) );
	wp_update_post( array( 'ID' => $tour, 'post_status' => 'draft' ) );
	foreach ( $snapshot as $key => $value ) { update_post_meta( $campaign, $key, $value ); }
	update_post_meta( $tour, 'hks_from_price_ksh', $tour_price );
	if ( $normal ) { wp_delete_post( $normal, true ); }
	foreach ( $inquiries as $id ) { wp_clear_scheduled_hook( InquiryNotification::CRON_HOOK, array( $id ) ); wp_delete_post( $id, true ); }
	wp_reset_postdata();
}
echo "Christmas Campaign: $checks integration checks passed. Drafts restored; no test price published.\n";
echo 'Local site: ' . home_url( '/' ) . "\n";
