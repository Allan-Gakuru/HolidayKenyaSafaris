<?php
/** Standalone validation checks with WordPress boundary stubs; never sends mail. */
if ( 'cli' !== PHP_SAPI ) { exit; }
define( 'ABSPATH', __DIR__ );
function __( $value, $domain = '' ) { return $value; }
function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); }
function sanitize_email( $value ) { return filter_var( $value, FILTER_SANITIZE_EMAIL ); }
function is_email( $value ) { return filter_var( $value, FILTER_VALIDATE_EMAIL ); }
function sanitize_key( $value ) { return strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '', $value ) ); }
function absint( $value ) { return abs( (int) $value ); }
function wp_date( $format, $timestamp, $timezone ) { return ( new DateTimeImmutable( '2026-09-08T22:30:00Z' ) )->setTimezone( $timezone )->format( $format ); }
class WP_Error {
 public function __construct( public $code, public $message, public $data ) {}
}
require dirname( __DIR__ ) . '/wp-content/plugins/hks-core/src/Conversion/InquiryRepository.php';
$repository = new HolidayKenyaSafaris\Core\Conversion\InquiryRepository();
$method = new ReflectionMethod( $repository, 'validate_values' );
$base = array( 'name' => 'Test Traveller', 'phone' => '0712 345 678', 'email' => 'you@example.com', 'preferred_date' => '15-01-2027', 'travelers' => 2 );
$context = array( 'allowed_questions' => array(), 'campaign_id' => 0, 'destination_label' => '' );
$cases = json_decode( file_get_contents( __DIR__ . '/inquiry-validation-cases.json' ), true );
foreach ( $cases as [ $field, $value, $expected ] ) {
 $result = $method->invoke( $repository, array_replace( $base, array( $field => $value ) ), $context );
 if ( ( ! $result instanceof WP_Error ) !== $expected || ( $result instanceof WP_Error && $result->data['field'] !== $field ) ) {
  throw new RuntimeException( 'Unexpected validation result: ' . $field . ' ' . $value );
 }
}
foreach ( array( 'phone', 'email', 'preferred_date' ) as $field ) {
 $result = $method->invoke( $repository, array_replace( $base, array( $field => array( 'bad input' ) ) ), $context );
 if ( ! $result instanceof WP_Error || $result->data['field'] !== $field ) { throw new RuntimeException( 'Non-string input accepted.' ); }
}
echo 'Server validation: ' . ( count( $cases ) + 3 ) . " boundary checks passed.\n";
