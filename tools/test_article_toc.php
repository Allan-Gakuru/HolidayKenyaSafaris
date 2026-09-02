<?php
/** Standalone regression checks for the server-rendered advertorial TOC. */
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

define( 'ABSPATH', __DIR__ . '/' );
function esc_html( string $value ): string {
	return htmlspecialchars( $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
}
function esc_attr( string $value ): string {
	return esc_html( $value );
}
function esc_html_e( string $value, string $domain ): void {
	echo esc_html( $value );
}

require dirname( __DIR__ ) . '/wp-content/themes/hks-wayfinder/inc/ArticleBlocks.php';
$renderer = new ReflectionMethod( HKS_Wayfinder\ArticleBlocks::class, 'render_article_toc' );

function check_equal( mixed $actual, mixed $expected, string $message ): void {
	if ( $actual !== $expected ) {
		throw new RuntimeException( $message . ': ' . json_encode( $actual ) );
	}
}
function parse_toc( string $html ): DOMXPath {
	$document = new DOMDocument();
	libxml_use_internal_errors( true );
	$document->loadHTML( '<?xml encoding="UTF-8">' . $html );
	libxml_clear_errors();
	return new DOMXPath( $document );
}
function toc_numbers( DOMXPath $xpath, string $scope ): array {
	$values = array();
	foreach ( $xpath->query( $scope . '//span[@class="hks-article-toc__number"]' ) as $node ) {
		$values[] = $node->textContent;
	}
	return $values;
}

$headings = array(
	array( 'level' => 2, 'id' => 'anything-is-fine', 'label' => 'The “Anything Is Fine” Gap' ),
	array( 'level' => 2, 'id' => 'comfort', 'label' => 'Comfort is not a hotel rating' ),
	array( 'level' => 2, 'id' => 'brief', 'label' => 'Build a Five-Decision Parent Comfort Brief' ),
	array( 'level' => 3, 'id' => 'travel-day', 'label' => '1. How should the travel day feel?' ),
	array( 'level' => 3, 'id' => 'hotel', 'label' => '2. What should the hotel make easy?' ),
	array( 'level' => 3, 'id' => 'pace', 'label' => '3. What pace and movement feel enjoyable?' ),
	array( 'level' => 3, 'id' => 'meals', 'label' => '4. What meal rhythm should you protect?' ),
	array( 'level' => 3, 'id' => 'details', 'label' => '5. Which experience details need a conversation?' ),
	array( 'level' => 2, 'id' => 'healthy', 'label' => '“My parent is healthy. We do not need all this.”' ),
	array( 'level' => 2, 'id' => 'package', 'label' => 'Put the package through the Comfort Brief' ),
	array( 'level' => 2, 'id' => 'wonder', 'label' => 'Settle the comfort. Keep the wonder.' ),
);
$html   = $renderer->invoke( null, $headings );
$xpath  = parse_toc( $html );
$mobile = '//ol[contains(@class,"hks-article-toc__list--mobile")]';
$left   = '(//ol[contains(@class,"hks-article-toc__column")])[1]';
$right  = '(//ol[contains(@class,"hks-article-toc__column")])[2]';
check_equal( toc_numbers( $xpath, $mobile ), array( '1.', '2.', '3.', '3.1', '3.2', '3.3', '3.4', '3.5', '4.', '5.', '6.' ), 'Mobile numbers follow article order' );
check_equal( toc_numbers( $xpath, $left ), array( '1.', '3.', '3.1', '3.2', '3.3', '3.4', '3.5', '5.' ), 'Left column keeps assigned numbers' );
check_equal( toc_numbers( $xpath, $right ), array( '2.', '4.', '6.' ), 'Right column does not restart numbering' );
check_equal( $xpath->evaluate( 'string(' . $mobile . '//a[@href="#travel-day"]/span[@class="hks-article-toc__label"])' ), 'How should the travel day feel?', 'No duplicate editorial number' );
check_equal( $xpath->evaluate( 'string(' . $right . '/li[2]/@value)' ), '4', 'Ordered-list values match visual numbering' );
check_equal( $renderer->invoke( null, array() ), '', 'Empty outline remains empty' );
check_equal( toc_numbers( parse_toc( $renderer->invoke( null, array( $headings[0] ) ) ), $mobile ), array( '1.' ), 'One heading is numbered' );
check_equal( toc_numbers( parse_toc( $renderer->invoke( null, array( $headings[3], $headings[4] ) ) ), $mobile ), array( '1.', '2.' ), 'H3-only articles keep all topics' );

$edge = array(
	array( 'level' => 3, 'id' => 'leading-h3', 'label' => 'An opening subheading' ),
	array( 'level' => 2, 'id' => 'tour', 'label' => '3 days in Diani' ),
	array( 'level' => 3, 'id' => 'a"b', 'label' => '2) "Airport" & <check-in>' ),
	array( 'level' => 2, 'id' => 'hotel-rating', 'label' => '3.5 star hotel' ),
	array( 'level' => 3, 'id' => 'last-child', 'label' => 'A later subheading' ),
);
$edge_xpath = parse_toc( $renderer->invoke( null, $edge ) );
check_equal( toc_numbers( $edge_xpath, $mobile ), array( '1.', '2.', '2.1', '3.', '3.1' ), 'Leading H3 and child reset are handled without zero prefixes' );
check_equal( $edge_xpath->evaluate( 'string(' . $mobile . '//a[@href="#tour"]/span[@class="hks-article-toc__label"])' ), '3 days in Diani', 'Meaningful title numbers remain' );
check_equal( $edge_xpath->evaluate( 'string(' . $mobile . '//a[@href="#hotel-rating"]/span[@class="hks-article-toc__label"])' ), '3.5 star hotel', 'Decimal facts remain' );
check_equal( $edge_xpath->evaluate( 'string(' . $mobile . '/li[2]/ol/li/a/@href)' ), '#a"b', 'Anchor ID is preserved and escaped' );
check_equal( $edge_xpath->evaluate( 'string(' . $mobile . '/li[2]/ol/li/a/span[@class="hks-article-toc__label"])' ), '"Airport" & <check-in>', 'Labels remain safely escaped' );

$many = array();
for ( $i = 1; $i <= 12; ++$i ) {
	$many[] = array( 'level' => 2, 'id' => 'section-' . $i, 'label' => 'Section ' . $i );
	for ( $j = 1; $j <= 12; ++$j ) {
		$many[] = array( 'level' => 3, 'id' => 'section-' . $i . '-' . $j, 'label' => 'Subheading ' . $j );
	}
}
$numbers = toc_numbers( parse_toc( $renderer->invoke( null, $many ) ), $mobile );
check_equal( count( $numbers ), 156, 'Long outlines retain every heading' );
check_equal( array_slice( $numbers, -3 ), array( '12.10', '12.11', '12.12' ), 'Double-digit numbering is not truncated' );

if ( in_array( '--preview', $argv, true ) ) {
	$root  = dirname( __DIR__ );
	$theme = json_decode( file_get_contents( $root . '/wp-content/themes/hks-wayfinder/theme.json' ), true );
	$vars  = '';
	foreach ( $theme['settings']['color']['palette'] as $color ) {
		$vars .= '--wp--preset--color--' . $color['slug'] . ':' . $color['color'] . ';';
	}
	$preview = '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Advertorial TOC numbering preview</title><link rel="stylesheet" href="../wp-content/themes/hks-wayfinder/style.css"><style>@font-face{font-family:Montserrat;src:url(../wp-content/themes/hks-wayfinder/assets/fonts/montserrat-latin-variable.woff2);font-weight:100 900}:root{' . $vars . '}body{margin:0;font-family:Montserrat,sans-serif;font-size:18px;line-height:1.6;color:var(--wp--preset--color--midnight-navy);background:var(--wp--preset--color--pale-mist)}.preview-body{max-width:720px;margin:auto;padding:32px}</style><main><section class="hks-article-outline"><div class="hks-shell">' . $html . '</div></section><div class="preview-body">';
	foreach ( $headings as $heading ) {
		$tag      = 'h' . $heading['level'];
		$preview .= '<' . $tag . ' id="' . esc_attr( $heading['id'] ) . '">' . esc_html( $heading['label'] ) . '</' . $tag . '><p>Preview section for anchor-link checks.</p>';
	}
	$preview .= '</div></main></html>';
	if ( ! is_dir( $root . '/tmp' ) ) {
		mkdir( $root . '/tmp' );
	}
	file_put_contents( $root . '/tmp/toc-numbering-preview.html', $preview );
	echo "Preview created: tmp/toc-numbering-preview.html\n";
}
echo "Article TOC tests passed (hierarchy, desktop/mobile parity, existing prefixes, anchors, escaping, and long outlines).\n";
