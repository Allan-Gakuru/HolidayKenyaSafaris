<?php
/** Exercise emitted sharing markup and legacy icon replacement without WordPress. */
define( 'ABSPATH', __DIR__ );
set_error_handler( static function ( $severity, $message, $file, $line ) { throw new ErrorException( $message, 0, $severity, $file, $line ); } );
$context = array();
$hooks   = array();

function add_action( $hook, $callback, $priority = 10 ) { $GLOBALS['hooks'][ $hook ][] = $callback; }
function add_filter( $hook, $callback, $priority = 10, $arguments = 1 ) { $GLOBALS['hooks'][ $hook ][] = $callback; }
function is_admin() { return ! empty( $GLOBALS['context']['admin'] ); }
function is_feed() { return ! empty( $GLOBALS['context']['feed'] ); }
function is_404() { return ! empty( $GLOBALS['context']['404'] ); }
function is_singular( $type = null ) { $current = $GLOBALS['context']['type'] ?? ''; return '' !== $current && ( null === $type || $type === $current ); }
function post_password_required() { return ! empty( $GLOBALS['context']['protected'] ); }
function get_queried_object_id() { return 42; }
function get_post_field( $field, $id ) { return $GLOBALS['context']['excerpt'] ?? ''; }
function get_permalink( $id ) { return $GLOBALS['context']['url']; }
function get_pagenum_link( $page ) { return $GLOBALS['context']['url']; }
function get_query_var( $name ) { return 0; }
function get_theme_file_uri( $path ) { return 'https://holidaykenyasafaris.ke/wp-content/themes/hks-wayfinder/' . $path; }
function get_bloginfo( $name ) { return $GLOBALS['context']['description'] ?? 'Trips for Kenyan travellers.'; }
function wp_get_document_title() { return $GLOBALS['context']['title'] ?? 'Holiday Kenya Safaris'; }
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function strip_shortcodes( $value ) { return preg_replace( '/\[[^\]]+\]/', '', $value ); }
function wp_trim_words( $value, $limit, $more ) { $words = preg_split( '/\s+/', $value ); return count( $words ) > $limit ? implode( ' ', array_slice( $words, 0, $limit ) ) . $more : $value; }
function wp_parse_url( $url, $component ) { return parse_url( $url, $component ); }
function esc_attr( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }

require __DIR__ . '/../wp-content/themes/hks-wayfinder/inc/Branding.php';

function check_brand( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
}

function render_brand_metadata( array $values ): array {
	$GLOBALS['context'] = $values;
	ob_start();
	hks_wayfinder_sharing_metadata();
	$html = ob_get_clean();
	$document = new DOMDocument();
	$document->loadHTML( '<html><head>' . $html . '</head><body></body></html>' );
	$tags = array();
	foreach ( $document->getElementsByTagName( 'meta' ) as $tag ) {
		$key = $tag->getAttribute( 'property' ) ?: $tag->getAttribute( 'name' );
		check_brand( ! isset( $tags[ $key ] ), 'Duplicate sharing tag: ' . $key );
		$tags[ $key ] = $tag->getAttribute( 'content' );
	}
	return $tags;
}

$image = get_theme_file_uri( 'assets/images/brand/holiday-kenya-safaris-social-1200x630.png' );
foreach ( array( '', 'page', 'hks_tour', 'hks_campaign', 'post' ) as $type ) {
	$url  = 'https://holidaykenyasafaris.ke/' . ( $type ?: 'destinations' ) . '/';
	$tags = render_brand_metadata( array( 'type' => $type, 'url' => $url, 'title' => 'A "Kenyan" trip & more', 'excerpt' => '<b>Approved itinerary.</b>' ) );
	check_brand( $tags['og:image'] === $image && $tags['twitter:image'] === $image, 'Wrong shared image for ' . $type );
	check_brand( $tags['og:url'] === $url, 'Wrong canonical sharing URL' );
	check_brand( $tags['og:title'] === 'A "Kenyan" trip & more', 'Title was not safely escaped' );
	check_brand( $tags['og:type'] === ( 'post' === $type ? 'article' : 'website' ), 'Wrong sharing type' );
	check_brand( $tags['og:image:width'] === '1200' && $tags['og:image:height'] === '630', 'Incorrect image dimensions' );
	check_brand( ! str_contains( $tags['og:description'], '<b>' ), 'HTML leaked into description' );
}
$tags = render_brand_metadata( array( 'type' => 'post', 'url' => 'https://holidaykenyasafaris.ke/private/', 'protected' => true, 'excerpt' => 'Do not expose this private excerpt.' ) );
check_brand( $tags['og:description'] === 'Trips for Kenyan travellers.', 'Protected excerpt leaked' );
$tags = render_brand_metadata( array( 'url' => 'https://holidaykenyasafaris.ke/', 'description' => '' ) );
check_brand( ! isset( $tags['og:description'] ), 'Blank descriptions must be omitted' );
foreach ( array( 'admin', 'feed', '404' ) as $surface ) {
	check_brand( array() === render_brand_metadata( array( $surface => true ) ), 'Metadata appeared on ' . $surface );
}
foreach ( array( 'site-icon-512.png', 'cropped-social-avatar-512-180x180.png', 'hks-wayfinder-favicon.svg' ) as $legacy ) {
	$replacement = hks_wayfinder_official_site_icon( 'https://holidaykenyasafaris.ke/uploads/' . $legacy, 512 );
	check_brand( str_ends_with( $replacement, 'holiday-kenya-safaris-site-icon-512.png' ), 'Legacy icon was not replaced' );
}
check_brand( '' === hks_wayfinder_official_site_icon( '', 32 ), 'Empty icon must preserve the theme fallback' );
$custom = 'https://holidaykenyasafaris.ke/uploads/official-approved-icon.png';
check_brand( $custom === hks_wayfinder_official_site_icon( $custom, 512 ), 'Unrelated editor-selected icon changed' );
check_brand( in_array( 'hks_wayfinder_sharing_metadata', $hooks['wp_head'], true ), 'Missing sharing output hook' );
echo "Branding checks passed: page types, official images, escaping, protected excerpts, and legacy icons.\n";
