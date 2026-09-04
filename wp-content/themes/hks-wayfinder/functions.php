<?php
/**
 * Theme setup and asset loading for HKS Wayfinder.
 *
 * @package HKS_Wayfinder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_theme_file_path( 'inc/NavMenus.php' );
require_once get_theme_file_path( 'inc/TourBlocks.php' );
require_once get_theme_file_path( 'inc/ArticleBlocks.php' );
require_once get_theme_file_path( 'inc/Branding.php' );
require_once get_theme_file_path( 'inc/AboutPage.php' );

/**
 * Whether the current request renders one of the acquisition/conversion pages.
 *
 * @return bool
 */
function hks_wayfinder_is_conversion_content_page(): bool {
	return is_singular( array( 'hks_tour', 'hks_campaign', 'post' ) );
}

/**
 * Compress anonymous conversion-page HTML when the server has no compression.
 *
 * The production origin currently sends HTML uncompressed. ob_gzhandler is
 * deliberately limited to public GET requests and stands down when PHP or an
 * upstream layer has already enabled content encoding.
 *
 * @return void
 */
function hks_wayfinder_enable_output_compression(): void {
	$request_method = strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) );
	$accept_encoding = (string) ( $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '' );
	$zlib_enabled     = filter_var( ini_get( 'zlib.output_compression' ), FILTER_VALIDATE_BOOLEAN );

	if (
		'GET' !== $request_method
		|| ! hks_wayfinder_is_conversion_content_page()
		|| is_admin()
		|| is_user_logged_in()
		|| is_preview()
		|| headers_sent()
		|| ! function_exists( 'ob_gzhandler' )
		|| $zlib_enabled
		|| false === stripos( $accept_encoding, 'gzip' )
	) {
		return;
	}

	foreach ( headers_list() as $header ) {
		if ( 0 === stripos( $header, 'Content-Encoding:' ) ) {
			return;
		}
	}

	foreach ( ob_get_status( true ) as $buffer ) {
		if ( 'ob_gzhandler' === ( $buffer['name'] ?? '' ) ) {
			return;
		}
	}

	ob_start( 'ob_gzhandler' );
}
add_action( 'template_redirect', 'hks_wayfinder_enable_output_compression', 0 );

/**
 * Preload the exact responsive image used above the fold on conversion pages.
 *
 * @return void
 */
function hks_wayfinder_preload_primary_image(): void {
	$image_id = 0;
	$sizes    = '';

	if ( is_singular( array( 'hks_tour', 'hks_campaign' ) ) ) {
		$image_id = \HKS_Wayfinder\TourBlocks::current_gallery_image_id();
		$sizes    = '(max-width: 56rem) calc(100vw - 2rem), (max-width: 80rem) 54vw, 760px';
	} elseif ( is_singular( 'post' ) ) {
		$image_id = \HKS_Wayfinder\ArticleBlocks::current_advertorial_image_id();
		$sizes    = '100vw';
	}

	if ( ! $image_id ) {
		return;
	}

	$src    = wp_get_attachment_image_url( $image_id, 'large' );
	$srcset = wp_get_attachment_image_srcset( $image_id, 'large' );

	if ( ! $src ) {
		return;
	}
	?>
	<link rel="preload" as="image" href="<?php echo esc_url( $src ); ?>" fetchpriority="high"<?php if ( $srcset ) : ?> imagesrcset="<?php echo esc_attr( $srcset ); ?>" imagesizes="<?php echo esc_attr( $sizes ); ?>"<?php endif; ?>>
	<?php
}
add_action( 'wp_head', 'hks_wayfinder_preload_primary_image', 1 );

/**
 * Register theme supports and editor styles.
 *
 * @return void
 */
function hks_wayfinder_setup(): void {
	load_theme_textdomain( 'hks-wayfinder', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'wp-block-styles' );
	\HKS_Wayfinder\NavMenus::register();

	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'hks_wayfinder_setup' );

add_action( 'admin_menu', array( \HKS_Wayfinder\NavMenus::class, 'register_admin_page' ), 90 );

/**
 * Enqueue the small structural stylesheet that complements theme.json.
 *
 * @return void
 */
function hks_wayfinder_enqueue_styles(): void {
	$stylesheet_path = get_stylesheet_directory() . '/style.css';
	$version         = is_readable( $stylesheet_path ) ? (string) filemtime( $stylesheet_path ) : wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'hks-wayfinder-style',
		get_stylesheet_uri(),
		array(),
		$version
	);
}
add_action( 'wp_enqueue_scripts', 'hks_wayfinder_enqueue_styles' );

/**
 * Load the small interaction layer for the navigation and canonical Tour UI.
 * All essential content remains server rendered when JavaScript is unavailable.
 *
 * @return void
 */
function hks_wayfinder_enqueue_scripts(): void {
	$navigation_path = get_theme_file_path( 'assets/js/navigation.js' );
	$navigation_uri  = get_theme_file_uri( 'assets/js/navigation.js' );

	wp_enqueue_script(
		'hks-wayfinder-navigation',
		$navigation_uri,
		array(),
		is_readable( $navigation_path ) ? (string) filemtime( $navigation_path ) : wp_get_theme()->get( 'Version' ),
		array( 'in_footer' => true, 'strategy' => 'defer' )
	);

	if ( is_front_page() ) {
		$home_gallery_path = get_theme_file_path( 'assets/js/home-gallery.js' );

		wp_enqueue_script(
			'hks-wayfinder-home-gallery',
			get_theme_file_uri( 'assets/js/home-gallery.js' ),
			array(),
			is_readable( $home_gallery_path ) ? (string) filemtime( $home_gallery_path ) : wp_get_theme()->get( 'Version' ),
			array( 'in_footer' => true, 'strategy' => 'defer' )
		);
	}

	if ( is_post_type_archive( 'hks_tour' ) ) {
		$catalogue_filters_path = get_theme_file_path( 'assets/js/catalogue-filters.js' );

		wp_enqueue_script(
			'hks-wayfinder-catalogue-filters',
			get_theme_file_uri( 'assets/js/catalogue-filters.js' ),
			array(),
			is_readable( $catalogue_filters_path ) ? (string) filemtime( $catalogue_filters_path ) : wp_get_theme()->get( 'Version' ),
			array( 'in_footer' => true, 'strategy' => 'defer' )
		);
	}

	if ( is_singular( array( 'hks_tour', 'hks_campaign' ) ) ) {
		$tour_ui_path = get_theme_file_path( 'assets/js/tour-ui.js' );

		wp_enqueue_script(
			'hks-wayfinder-tour-ui',
			get_theme_file_uri( 'assets/js/tour-ui.js' ),
			array(),
			is_readable( $tour_ui_path ) ? (string) filemtime( $tour_ui_path ) : wp_get_theme()->get( 'Version' ),
			array( 'in_footer' => true, 'strategy' => 'defer' )
		);
	}

	if ( is_singular( 'post' ) || is_tax( 'hks_article_topic' ) || is_home() ) {
		$article_path = get_theme_file_path( 'assets/js/article-ui.js' );

		wp_enqueue_script(
			'hks-wayfinder-article-ui',
			get_theme_file_uri( 'assets/js/article-ui.js' ),
			array(),
			is_readable( $article_path ) ? (string) filemtime( $article_path ) : wp_get_theme()->get( 'Version' ),
			array( 'in_footer' => true, 'strategy' => 'defer' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'hks_wayfinder_enqueue_scripts' );

/**
 * Move Meta's signal helper to the footer on acquisition pages.
 *
 * The official plugin normally prints this synchronous file in the document
 * head. Keeping it synchronous, but moving it to the footer, preserves the
 * plugin's required initialization order without making the first render wait.
 *
 * @return bool Whether the registered helper was moved.
 */
function hks_wayfinder_move_meta_signal_to_footer(): bool {
	if ( ! wp_script_is( 'facebook-signal', 'enqueued' ) ) {
		return false;
	}

	return (bool) wp_scripts()->add_data( 'facebook-signal', 'group', 1 );
}

/**
 * Capture the official Meta callback before its server-side event flush.
 *
 * The official fbq queue stub, FacebookSignal initialization and PageView are
 * light and remain in their original order as soon as the helper is printed.
 * Only the large external fbevents runtime moves to the post-load scheduler.
 *
 * @param callable $callback Official plugin render callback.
 * @return void
 */
function hks_wayfinder_capture_deferred_meta_pixel( callable $callback ): void {
	ob_start();
	call_user_func( $callback );
	$markup          = (string) ob_get_clean();
	$state           = array(
		'fallback'   => $markup,
		'javascript' => '',
	);
	$residual_markup = preg_replace( '#<script\b[^>]*>.*?</script>#is', '', $markup );
	$residual_markup = preg_replace( '#<!--\s*(?:Meta Pixel Code|End Meta Pixel Code)\s*-->#i', '', (string) $residual_markup );

	if (
		! preg_match_all( '#<script\b[^>]*>(.*?)</script>#is', $markup, $matches )
		|| 3 !== count( $matches[1] )
		|| preg_match( '#<script\b[^>]*\bsrc\s*=#i', $markup )
		|| '' !== trim( (string) $residual_markup )
	) {
		$GLOBALS['hks_wayfinder_deferred_meta_pixel'][] = $state;
		return;
	}

	$base_javascript     = (string) $matches[1][0];
	$signal_javascript   = (string) $matches[1][1];
	$pageview_javascript = (string) $matches[1][2];
	$base_tokens         = array(
		'connect.facebook.net/en_US/fbevents.js',
		'if(f.fbq)return',
		'if(!f._fbq)f._fbq=n',
		'n.loaded=!0',
		"n.version='2.0'",
		'n.queue=[]',
	);

	foreach ( $base_tokens as $base_token ) {
		if ( false === strpos( $base_javascript, $base_token ) ) {
			$GLOBALS['hks_wayfinder_deferred_meta_pixel'][] = $state;
			return;
		}
	}

	$signal_init_position  = strpos( $signal_javascript, 'FacebookSignal.init(' );
	$pixel_init_position   = strpos( $signal_javascript, 'FacebookSignal.initPixel(' );
	$pageview_position     = strpos( $pageview_javascript, "fbq('track', 'PageView'" );

	if (
		false === $signal_init_position
		|| false === $pixel_init_position
		|| $signal_init_position >= $pixel_init_position
		|| false === $pageview_position
	) {
		$GLOBALS['hks_wayfinder_deferred_meta_pixel'][] = $state;
		return;
	}

	$loader_tail_pattern = '#t=b\.createElement\(e\);t\.async=!0;\s*t\.src=v;s=b\.getElementsByTagName\(e\)\[0\];s\.parentNode\.insertBefore\(t,s\)#s';
	$queue_bootstrap     = preg_replace(
		$loader_tail_pattern,
		'f.__hksDeferredMetaPixelOwnsLoader=!0',
		$base_javascript,
		1,
		$loader_tail_count
	);

	if (
		1 !== $loader_tail_count
		|| ! is_string( $queue_bootstrap )
		|| false !== strpos( $queue_bootstrap, 'createElement' )
		|| false !== strpos( $queue_bootstrap, 'insertBefore' )
	) {
		$GLOBALS['hks_wayfinder_deferred_meta_pixel'][] = $state;
		return;
	}

	$immediate_javascript = implode( "\n", array( $queue_bootstrap, $signal_javascript, $pageview_javascript ) );
	if ( ! wp_add_inline_script( 'facebook-signal', $immediate_javascript, 'after' ) ) {
		$GLOBALS['hks_wayfinder_deferred_meta_pixel'][] = $state;
		return;
	}

	$state['fallback']   = '';
	$state['javascript'] = <<<'JS'
!function(d,u,s,f){
	if(!window.__hksDeferredMetaPixelOwnsLoader)return;
	if((window.fbq&&window.fbq.callMethod)||d.querySelector('script[src="'+u+'"]')){window.__hksDeferredMetaPixelOwnsLoader=!1;return;}
	window.__hksDeferredMetaPixelOwnsLoader=!1;
	s=d.createElement('script');s.async=!0;s.src=u;f=d.getElementsByTagName('script')[0];
	if(f&&f.parentNode)f.parentNode.insertBefore(s,f);else(d.head||d.documentElement).appendChild(s);
}(document,'https://connect.facebook.net/en_US/fbevents.js');
JS;
	$GLOBALS['hks_wayfinder_deferred_meta_pixel'][] = $state;
}

/**
 * Print captured Meta browser code after the helper reaches the footer.
 *
 * A bounded fallback ensures PageView still fires if another resource delays
 * the window load event indefinitely. If Meta changes its markup shape, the
 * untouched original callback output is restored after its helper.
 *
 * @return void
 */
function hks_wayfinder_print_deferred_meta_pixel(): void {
	$states = $GLOBALS['hks_wayfinder_deferred_meta_pixel'] ?? array();
	unset( $GLOBALS['hks_wayfinder_deferred_meta_pixel'] );

	foreach ( $states as $state ) {
		$fallback   = (string) ( $state['fallback'] ?? '' );
		$javascript = (string) ( $state['javascript'] ?? '' );

		if ( '' !== $fallback ) {
			echo $fallback; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated by the official Meta Pixel callback.
			continue;
		}

		if ( '' === $javascript ) {
			continue;
		}
		?>
	<script data-hks-deferred-meta-pixel>
	(function () {
		var fired = false;
		var watchdog = 0;
		var run = function () {
			if (fired) return;
			fired = true;
			if (watchdog) window.clearTimeout(watchdog);
			<?php echo $javascript; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated by the official Meta Pixel callback. ?>
		};
		var schedule = function () {
			if ('requestIdleCallback' in window) {
				window.requestIdleCallback(run, { timeout: 1000 });
			} else {
				window.setTimeout(run, 0);
			}
		};

		watchdog = window.setTimeout(run, 5000);
		if (document.readyState === 'complete') schedule();
		else window.addEventListener('load', schedule, { once: true });
	})();
	</script>
	<?php
	}
}

/**
 * Relocate the official Meta Pixel bootstrap out of the document head.
 *
 * Meta's plugin attaches an object callback directly to wp_head, so moving the
 * script handle alone would let its inline bootstrap run too early. Relocating
 * that exact callback keeps PageView/CAPI behavior intact and schedules the
 * large third-party runtime after the page's LCP resources.
 *
 * @return void
 */
function hks_wayfinder_move_meta_pixel_to_footer(): void {
	if ( ! hks_wayfinder_is_conversion_content_page() || ! wp_script_is( 'facebook-signal', 'enqueued' ) ) {
		return;
	}

	global $wp_filter;

	$head_hook = $wp_filter['wp_head'] ?? null;
	if ( ! $head_hook instanceof WP_Hook ) {
		return;
	}

	$callbacks = array();
	foreach ( $head_hook->callbacks as $priority => $entries ) {
		foreach ( $entries as $entry ) {
			$callback = $entry['function'] ?? null;
			if (
				2 < (int) $priority
				&& is_array( $callback )
				&& isset( $callback[0], $callback[1] )
				&& is_object( $callback[0] )
				&& is_a( $callback[0], 'FacebookPixelPlugin\\Core\\FacebookWordpressPixelInjection' )
				&& 'inject_pixel_code' === $callback[1]
			) {
				$callbacks[] = array( $callback, (int) $priority );
			}
		}
	}

	if ( ! $callbacks ) {
		return;
	}

	$removed_callbacks = array();
	foreach ( $callbacks as $registered_callback ) {
		$callback = $registered_callback[0];
		$priority = $registered_callback[1];

		if ( ! remove_action( 'wp_head', $callback, $priority ) ) {
			foreach ( $removed_callbacks as $removed_callback ) {
				add_action( 'wp_head', $removed_callback[0], $removed_callback[1] );
			}
			return;
		}

		$removed_callbacks[] = $registered_callback;
	}

	if ( ! hks_wayfinder_move_meta_signal_to_footer() ) {
		foreach ( $removed_callbacks as $removed_callback ) {
			add_action( 'wp_head', $removed_callback[0], $removed_callback[1] );
		}
		return;
	}

	foreach ( $removed_callbacks as $removed_callback ) {
		$callback = $removed_callback[0];
		add_action(
			'wp_footer',
			static function () use ( $callback ): void {
				hks_wayfinder_capture_deferred_meta_pixel( $callback );
			},
			9
		);
	}

	add_action( 'wp_footer', 'hks_wayfinder_print_deferred_meta_pixel', 21 );
}
add_action( 'wp_head', 'hks_wayfinder_move_meta_pixel_to_footer', 2 );

/**
 * Provide deployable favicon assets until an editor configures a Site Icon.
 *
 * WordPress owns the Site Icon when one has been selected in the dashboard, so
 * these links intentionally disappear as soon as that setting exists.
 *
 * @return void
 */
function hks_wayfinder_favicon_fallback(): void {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
		return;
	}

	$brand_path = 'assets/images/brand/';
	?>
	<link rel="icon" href="<?php echo esc_url( get_theme_file_uri( $brand_path . 'holiday-kenya-safaris-icon.svg' ) ); ?>" type="image/svg+xml">
	<link rel="icon" href="<?php echo esc_url( get_theme_file_uri( $brand_path . 'holiday-kenya-safaris-favicon-32.png' ) ); ?>" sizes="32x32" type="image/png">
	<link rel="icon" href="<?php echo esc_url( get_theme_file_uri( $brand_path . 'holiday-kenya-safaris-site-icon-512.png' ) ); ?>" sizes="512x512" type="image/png">
	<link rel="apple-touch-icon" href="<?php echo esc_url( get_theme_file_uri( $brand_path . 'holiday-kenya-safaris-apple-touch-icon-180.png' ) ); ?>" sizes="180x180">
	<?php
}
add_action( 'wp_head', 'hks_wayfinder_favicon_fallback', 2 );

add_action( 'init', array( \HKS_Wayfinder\TourBlocks::class, 'register' ), 20 );
add_action( 'init', array( \HKS_Wayfinder\ArticleBlocks::class, 'register' ), 20 );
add_action( 'init', array( \HKS_Wayfinder\AboutPage::class, 'register' ), 20 );

/**
 * Respect Campaign noindex governance independently of SEO plugins.
 *
 * @param array<string, bool> $robots Existing directives.
 * @return array<string, bool>
 */
function hks_wayfinder_campaign_robots( array $robots ): array {
	if ( ! is_singular( 'hks_campaign' ) ) {
		return $robots;
	}

	$post_id = get_queried_object_id();
	$noindex = metadata_exists( 'post', $post_id, 'hks_noindex' )
		? (bool) get_post_meta( $post_id, 'hks_noindex', true )
		: true;

	if ( $noindex ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = false;
	}

	return $robots;
}
add_filter( 'wp_robots', 'hks_wayfinder_campaign_robots' );

/**
 * Add public mode classes for focused Campaign and article presentation.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function hks_wayfinder_campaign_body_class( array $classes ): array {
	if ( is_singular( 'hks_campaign' ) ) {
		$post_id = get_queried_object_id();
		$mode    = function_exists( 'get_field' ) ? get_field( 'hks_navigation_mode', $post_id ) : get_post_meta( $post_id, 'hks_navigation_mode', true );
		$classes[] = 'hks-campaign-navigation-' . sanitize_html_class( $mode ?: 'campaign_minimal' );
	}

	if ( is_singular( 'post' ) ) {
		$post_id = get_queried_object_id();
		$format  = function_exists( 'get_field' ) ? get_field( 'hks_article_format', $post_id ) : get_post_meta( $post_id, 'hks_article_format', true );
		$classes[] = 'hks-article-format-' . sanitize_html_class( 'advertorial' === $format ? 'advertorial' : 'guide' );
	}

	return $classes;
}
add_filter( 'body_class', 'hks_wayfinder_campaign_body_class' );

/**
 * Return the current cache generation for catalogue-derived navigation data.
 *
 * @return string
 */
function hks_wayfinder_catalogue_cache_version(): string {
	$version = (string) get_option( 'hks_wayfinder_catalogue_cache_version', '' );

	if ( '' === $version ) {
		$version = (string) microtime( true );
		add_option( 'hks_wayfinder_catalogue_cache_version', $version, '', false );
	}

	return $version;
}

/**
 * Advance the catalogue cache generation after an editor changes public data.
 *
 * @return void
 */
function hks_wayfinder_bump_catalogue_cache_version(): void {
	update_option( 'hks_wayfinder_catalogue_cache_version', (string) microtime( true ), false );
}

/**
 * Invalidate catalogue caches when a Tour is saved or removed.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function hks_wayfinder_invalidate_catalogue_cache_for_tour( int $post_id ): void {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || 'hks_tour' !== get_post_type( $post_id ) ) {
		return;
	}

	hks_wayfinder_bump_catalogue_cache_version();
}
add_action( 'save_post_hks_tour', 'hks_wayfinder_invalidate_catalogue_cache_for_tour' );
add_action( 'before_delete_post', 'hks_wayfinder_invalidate_catalogue_cache_for_tour' );

/**
 * Invalidate catalogue caches when a public Tour taxonomy changes.
 *
 * @param int    $term_id  Term ID.
 * @param int    $tt_id    Term-taxonomy ID.
 * @param string $taxonomy Taxonomy name.
 * @return void
 */
function hks_wayfinder_invalidate_catalogue_cache_for_term( int $term_id, int $tt_id, string $taxonomy ): void {
	unset( $term_id, $tt_id );

	if ( in_array( $taxonomy, array( 'hks_tour_scope', 'hks_destination', 'hks_tour_type', 'hks_occasion', 'hks_travel_style' ), true ) ) {
		hks_wayfinder_bump_catalogue_cache_version();
	}
}
add_action( 'created_term', 'hks_wayfinder_invalidate_catalogue_cache_for_term', 10, 3 );
add_action( 'edited_term', 'hks_wayfinder_invalidate_catalogue_cache_for_term', 10, 3 );
add_action( 'delete_term', 'hks_wayfinder_invalidate_catalogue_cache_for_term', 10, 3 );

/**
 * Invalidate catalogue caches when Tour term relationships change.
 *
 * @param int    $object_id Object ID.
 * @param mixed  $terms     Assigned terms.
 * @param int[]  $tt_ids    Term-taxonomy IDs.
 * @param string $taxonomy  Taxonomy name.
 * @return void
 */
function hks_wayfinder_invalidate_catalogue_cache_for_relationship( int $object_id, $terms, array $tt_ids, string $taxonomy ): void {
	unset( $terms, $tt_ids );

	if ( 'hks_tour' === get_post_type( $object_id ) ) {
		hks_wayfinder_invalidate_catalogue_cache_for_term( 0, 0, $taxonomy );
	}
}
add_action( 'set_object_terms', 'hks_wayfinder_invalidate_catalogue_cache_for_relationship', 10, 4 );

/**
 * Return populated terms for public navigation and catalogue controls.
 *
 * @param string $taxonomy Taxonomy name.
 * @param int    $limit    Maximum terms to return. Zero returns all.
 * @return WP_Term[]
 */
function hks_wayfinder_populated_terms( string $taxonomy, int $limit = 0 ): array {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	$cache_key = 'hks_terms_' . md5( hks_wayfinder_catalogue_cache_version() . '|' . $taxonomy );
	$cached    = get_transient( $cache_key );

	if ( is_array( $cached ) && isset( $cached['terms'] ) && is_array( $cached['terms'] ) ) {
		return array_slice( $cached['terms'], 0, $limit > 0 ? $limit : count( $cached['terms'] ) );
	}

	if ( 'hks_destination' === $taxonomy ) {
		$tour_ids = get_posts(
			array(
				'post_type'      => 'hks_tour',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		if ( ! $tour_ids ) {
			set_transient( $cache_key, array( 'terms' => array() ), 12 * HOUR_IN_SECONDS );
			return array();
		}

		update_object_term_cache( $tour_ids, 'hks_tour' );
		$terms  = array();
		$counts = array();
		foreach ( $tour_ids as $tour_id ) {
			foreach ( (array) get_the_terms( $tour_id, $taxonomy ) as $term ) {
				if ( ! $term instanceof WP_Term ) {
					continue;
				}
				if ( ! isset( $terms[ $term->term_id ] ) ) {
					$terms[ $term->term_id ] = clone $term;
				}
				$counts[ $term->term_id ] = ( $counts[ $term->term_id ] ?? 0 ) + 1;
			}
		}
		$terms = array_values( $terms );
		foreach ( $terms as $term ) {
			$term->count = (int) ( $counts[ $term->term_id ] ?? 0 );
		}
		usort( $terms, static fn( $left, $right ) => $right->count <=> $left->count ?: strnatcasecmp( $left->name, $right->name ) );
		set_transient( $cache_key, array( 'terms' => $terms ), 12 * HOUR_IN_SECONDS );
		return array_slice( $terms, 0, $limit > 0 ? $limit : count( $terms ) );
	}

	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'number'     => 0,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	$terms = is_wp_error( $terms ) ? array() : $terms;
	set_transient( $cache_key, array( 'terms' => $terms ), 12 * HOUR_IN_SECONDS );

	return array_slice( $terms, 0, $limit > 0 ? $limit : count( $terms ) );
}

/**
 * Return populated Destinations used by published Tours in one Tour Scope.
 *
 * @param WP_Term $scope Tour Scope term.
 * @param int     $limit Maximum Destinations.
 * @return WP_Term[]
 */
function hks_wayfinder_destinations_for_scope( WP_Term $scope, int $limit = 8 ): array {
	if ( 'hks_tour_scope' !== $scope->taxonomy || ! taxonomy_exists( 'hks_destination' ) ) {
		return array();
	}

	$cache_key = 'hks_scope_dest_' . md5( hks_wayfinder_catalogue_cache_version() . '|' . $scope->term_id );
	$cached    = get_transient( $cache_key );

	if ( is_array( $cached ) && isset( $cached['terms'] ) && is_array( $cached['terms'] ) ) {
		return array_slice( $cached['terms'], 0, max( 0, $limit ) );
	}

	$tour_ids = get_posts(
		array(
			'post_type'      => 'hks_tour',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'      => array(
				array(
					'taxonomy' => 'hks_tour_scope',
					'field'    => 'term_id',
					'terms'    => $scope->term_id,
				),
			),
		)
	);

	if ( ! $tour_ids ) {
		set_transient( $cache_key, array( 'terms' => array() ), 12 * HOUR_IN_SECONDS );
		return array();
	}

	$terms = wp_get_object_terms(
		$tour_ids,
		'hks_destination',
		array(
			'orderby' => 'count',
			'order'   => 'DESC',
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	set_transient( $cache_key, array( 'terms' => $terms ), 12 * HOUR_IN_SECONDS );

	return array_slice( $terms, 0, max( 0, $limit ) );
}

/**
 * Build a usable catalogue URL for Tour taxonomy terms.
 *
 * @param WP_Term $term Term object.
 * @return string
 */
function hks_wayfinder_term_url( WP_Term $term ): string {
	$public_taxonomies = array( 'hks_tour_scope', 'hks_destination', 'hks_tour_type', 'hks_occasion', 'hks_travel_style' );

	if ( in_array( $term->taxonomy, $public_taxonomies, true ) ) {
		$link = get_term_link( $term );

		return is_wp_error( $link ) ? '' : $link;
	}

	$archive = get_post_type_archive_link( 'hks_tour' ) ?: home_url( '/tours/' );

	return add_query_arg( $term->taxonomy, $term->slug, $archive );
}

/**
 * Build a traveller-facing archive title that names the current taxonomy term.
 *
 * @param WP_Term $term Current archive term.
 * @return string
 */
function hks_wayfinder_taxonomy_archive_title( WP_Term $term ): string {
	switch ( $term->taxonomy ) {
		case 'hks_tour_scope':
			return $term->name;
		case 'hks_destination':
			return sprintf(
				/* translators: %s: Destination name. */
				__( 'Tours in %s', 'hks-wayfinder' ),
				$term->name
			);
		case 'hks_tour_type':
			return sprintf(
				/* translators: %s: Tour type name. */
				__( '%s tours', 'hks-wayfinder' ),
				$term->name
			);
		case 'hks_occasion':
			return sprintf(
				/* translators: %s: Occasion name. */
				__( 'Tours for %s', 'hks-wayfinder' ),
				$term->name
			);
		case 'hks_travel_style':
			return sprintf(
				/* translators: %s: Travel style name. */
				__( '%s tours', 'hks-wayfinder' ),
				$term->name
			);
		default:
			return $term->name;
	}
}

/**
 * Build a traveller-facing archive introduction that names the taxonomy and term.
 *
 * @param WP_Term $term Current archive term.
 * @return string
 */
function hks_wayfinder_taxonomy_archive_description( WP_Term $term ): string {
	switch ( $term->taxonomy ) {
		case 'hks_tour_scope':
			return sprintf(
				/* translators: %s: Tour Scope name. */
				__( 'Explore %s by destination, trip type and duration, then request a tailored quote for your dates and group.', 'hks-wayfinder' ),
				$term->name
			);
		case 'hks_destination':
			return sprintf(
				/* translators: %s: Destination name. */
				__( 'Explore tours in the %s destination. Compare routes, durations and trip details, then request a tailored quote for your dates and group.', 'hks-wayfinder' ),
				$term->name
			);
		case 'hks_tour_type':
			return sprintf(
				/* translators: %s: Tour type name. */
				__( 'Explore the %s tour type. Compare destinations, routes and durations to find the trip that suits you.', 'hks-wayfinder' ),
				$term->name
			);
		case 'hks_occasion':
			return sprintf(
				/* translators: %s: Occasion name. */
				__( 'Explore tours selected for the %s occasion. Compare destinations and trip details before you request a quote.', 'hks-wayfinder' ),
				$term->name
			);
		case 'hks_travel_style':
			return sprintf(
				/* translators: %s: Travel style name. */
				__( 'Explore the %s travel style. Compare destinations, routes and durations to choose the right trip.', 'hks-wayfinder' ),
				$term->name
			);
		default:
			return '';
	}
}

/**
 * Keep taxonomy document titles as useful as their visible archive headings.
 *
 * @param array<string,string> $parts Document title parts.
 * @return array<string,string>
 */
function hks_wayfinder_taxonomy_document_title( array $parts ): array {
	if ( is_tax( 'hks_article_topic' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$parts['title'] = sprintf( __( '%s travel guides', 'hks-wayfinder' ), $term->name );
		}
		return $parts;
	}

	if ( ! is_tax( array( 'hks_tour_scope', 'hks_destination', 'hks_tour_type', 'hks_occasion', 'hks_travel_style' ) ) ) {
		return $parts;
	}

	$term = get_queried_object();

	if ( $term instanceof WP_Term ) {
		$parts['title'] = hks_wayfinder_taxonomy_archive_title( $term );
	}

	return $parts;
}
add_filter( 'document_title_parts', 'hks_wayfinder_taxonomy_document_title' );

/**
 * Find a published page route without creating placeholder navigation.
 *
 * @param string $path Page path.
 * @return string
 */
function hks_wayfinder_published_page_url( string $path ): string {
	$page = get_page_by_path( $path, OBJECT, 'page' );

	return $page && 'publish' === $page->post_status ? get_permalink( $page ) : '';
}

/**
 * Apply allowlisted catalogue filters without changing dashboard queries.
 *
 * @param WP_Query $query Main query.
 * @return void
 */
function hks_wayfinder_filter_tour_archive( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_home() ) {
		$tax_query = array();
		$filters   = array(
			'hks_guide_destination' => 'hks_destination',
			'hks_guide_topic'       => 'hks_article_topic',
		);
		foreach ( $filters as $parameter => $taxonomy ) {
			$raw  = $_GET[ $parameter ] ?? '';
			$slug = is_string( $raw ) ? sanitize_title( wp_unslash( $raw ) ) : '';
			if ( '' !== $slug && taxonomy_exists( $taxonomy ) && term_exists( $slug, $taxonomy ) ) {
				$tax_query[] = array( 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $slug );
			}
		}
		if ( $tax_query ) {
			$query->set( 'tax_query', $tax_query );
		}
		$query->set( 'post_type', 'post' );
		$query->set( 'post_status', 'publish' );
		$query->set( 'posts_per_page', 12 );
		$query->set( 'ignore_sticky_posts', true );
		return;
	}

	if ( $query->is_tax( 'hks_article_topic' ) ) {
		$query->set( 'post_type', 'post' );
		$query->set( 'post_status', 'publish' );
		$query->set( 'posts_per_page', 12 );
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
		return;
	}

	$public_taxonomies = array( 'hks_tour_scope', 'hks_destination', 'hks_tour_type', 'hks_occasion', 'hks_travel_style' );
	$is_tour_archive   = $query->is_post_type_archive( 'hks_tour' );

	if ( ! $is_tour_archive && $query->is_tax( $public_taxonomies ) ) {
		// Occasion is also assigned to Campaigns; public taxonomy archives are Tour catalogue routes.
		$query->set( 'post_type', 'hks_tour' );
		$query->set( 'post_status', 'publish' );
		$query->set( 'posts_per_page', 12 );
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		return;
	}

	if ( ! $is_tour_archive ) {
		return;
	}

	$tax_query = array();
	$filters   = array( 'hks_tour_scope', 'hks_destination', 'hks_tour_type', 'hks_occasion', 'hks_travel_style' );

	foreach ( $filters as $taxonomy ) {
		$raw   = $_GET[ $taxonomy ] ?? '';
		$value = is_string( $raw ) ? sanitize_title( wp_unslash( $raw ) ) : '';

		if ( '' !== $value && term_exists( $value, $taxonomy ) ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $value,
			);
		}
	}

	if ( $tax_query ) {
		$query->set( 'tax_query', $tax_query );
	}

	$raw_sort = $_GET['hks_sort'] ?? '';
	$sort     = is_string( $raw_sort ) ? sanitize_key( wp_unslash( $raw_sort ) ) : 'recommended';

	if ( 'title' === $sort ) {
		$query->set( 'orderby', 'title' );
		$query->set( 'order', 'ASC' );
	} elseif ( 'newest' === $sort ) {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	} else {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
	}
}
add_action( 'pre_get_posts', 'hks_wayfinder_filter_tour_archive' );
