<?php
/**
 * Server-rendered Travel Guides presentation blocks.
 *
 * @package HKS_Wayfinder
 */

namespace HKS_Wayfinder;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps native WordPress Posts useful as both editorial guides and conversion stories.
 */
final class ArticleBlocks {

	public static function register(): void {
		$blocks = array(
			'article-archive-intro' => 'render_archive_intro',
			'article-card'         => 'render_article_card',
			'article-page'         => 'render_article_page',
			'destination-guides'   => 'render_destination_guides',
		);

		foreach ( $blocks as $directory => $callback ) {
			register_block_type(
				get_theme_file_path( 'blocks/' . $directory ),
				array( 'render_callback' => array( self::class, $callback ) )
			);
		}
	}

	public static function render_archive_intro(): string {
		$title       = __( 'Travel Guides', 'hks-wayfinder' );
		$description = __( 'Practical destination guides, planning help and travel inspiration from Holiday Kenya Safaris.', 'hks-wayfinder' );
		$term        = get_queried_object();

		if ( is_tax( 'hks_article_topic' ) && $term instanceof \WP_Term ) {
			$title       = sprintf( __( '%s travel guides', 'hks-wayfinder' ), $term->name );
			$description = term_description( $term, 'hks_article_topic' ) ?: sprintf( __( 'Browse Holiday Kenya Safaris guides about %s.', 'hks-wayfinder' ), $term->name );
		}

		ob_start();
		?>
		<section class="hks-article-archive-intro">
			<div class="hks-title-band"><div class="hks-shell">
				<?php self::breadcrumbs( is_tax( 'hks_article_topic' ) ? array( __( 'Travel Guides', 'hks-wayfinder' ) => home_url( '/travel-guides/' ), $title => '' ) : array( $title => '' ) ); ?>
				<h1><?php echo esc_html( $title ); ?></h1>
				<p class="hks-article-archive-intro__promise"><?php echo wp_kses_post( wp_strip_all_tags( (string) $description ) ); ?></p>
			</div></div>
			<?php if ( is_home() ) : ?><?php echo self::render_archive_filters(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by the renderer. ?><?php endif; ?>
			<?php if ( is_tax( 'hks_article_topic' ) ) : ?><p class="hks-article-archive-intro__back"><a href="<?php echo esc_url( home_url( '/travel-guides/' ) ); ?>"><?php esc_html_e( 'Browse all Travel Guides', 'hks-wayfinder' ); ?> <span aria-hidden="true">→</span></a></p><?php endif; ?>
		</section>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render destination and topic discovery controls for the Travel Guides hub.
	 *
	 * @return string
	 */
	private static function render_archive_filters(): string {
		$destinations        = self::terms_for_posts( 'hks_destination' );
		$topics              = self::terms_for_posts( 'hks_article_topic' );
		$selected_destination = isset( $_GET['hks_guide_destination'] ) ? sanitize_title( wp_unslash( (string) $_GET['hks_guide_destination'] ) ) : '';
		$selected_topic       = isset( $_GET['hks_guide_topic'] ) ? sanitize_title( wp_unslash( (string) $_GET['hks_guide_topic'] ) ) : '';

		if ( ! $destinations && ! $topics ) {
			return '';
		}

		ob_start();
		?>
		<div class="hks-article-discovery">
			<form class="hks-article-discovery__form" action="<?php echo esc_url( home_url( '/travel-guides/' ) ); ?>" method="get" aria-label="<?php esc_attr_e( 'Filter Travel Guides', 'hks-wayfinder' ); ?>">
				<?php if ( $destinations ) : ?>
					<label><span><?php esc_html_e( 'Destination', 'hks-wayfinder' ); ?></span><select name="hks_guide_destination"><option value=""><?php esc_html_e( 'All destinations', 'hks-wayfinder' ); ?></option><?php foreach ( $destinations as $destination ) : ?><option value="<?php echo esc_attr( $destination->slug ); ?>"<?php selected( $selected_destination, $destination->slug ); ?>><?php echo esc_html( $destination->name ); ?></option><?php endforeach; ?></select></label>
				<?php endif; ?>
				<?php if ( $topics ) : ?>
					<label><span><?php esc_html_e( 'Article topic', 'hks-wayfinder' ); ?></span><select name="hks_guide_topic"><option value=""><?php esc_html_e( 'All topics', 'hks-wayfinder' ); ?></option><?php foreach ( $topics as $topic ) : ?><option value="<?php echo esc_attr( $topic->slug ); ?>"<?php selected( $selected_topic, $topic->slug ); ?>><?php echo esc_html( $topic->name ); ?></option><?php endforeach; ?></select></label>
				<?php endif; ?>
				<button type="submit"><?php esc_html_e( 'Show guides', 'hks-wayfinder' ); ?></button>
				<?php if ( $selected_destination || $selected_topic ) : ?><a href="<?php echo esc_url( home_url( '/travel-guides/' ) ); ?>"><?php esc_html_e( 'Clear filters', 'hks-wayfinder' ); ?></a><?php endif; ?>
			</form>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	public static function render_article_card(): string {
		$post_id = get_the_ID();
		if ( ! $post_id || 'post' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
			return '';
		}

		$link        = get_permalink( $post_id );
		$title       = self::text( get_the_title( $post_id ) );
		$excerpt     = self::text( get_the_excerpt( $post_id ) );
		$destination = self::term_names( $post_id, 'hks_destination' );
		$topics      = self::term_names( $post_id, 'hks_article_topic' );
		$image_id    = get_post_thumbnail_id( $post_id );
		$heading     = is_singular( 'post' ) || is_tax( 'hks_destination' ) ? 'h3' : 'h2';

		ob_start();
		?>
		<article class="hks-article-card<?php echo $image_id ? '' : ' hks-article-card--no-image'; ?>">
			<a class="hks-article-card__media<?php echo $image_id ? '' : ' hks-article-card__media--placeholder'; ?>" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true">
				<?php if ( $image_id ) : ?>
					<?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'medium_large', false, array( 'loading' => 'lazy', 'sizes' => '(max-width: 700px) 100vw, 33vw' ) ) ); ?>
				<?php else : ?>
					<span><?php esc_html_e( 'Holiday Kenya Safaris', 'hks-wayfinder' ); ?></span>
					<strong><?php esc_html_e( 'Travel Guide', 'hks-wayfinder' ); ?></strong>
				<?php endif; ?>
			</a>
			<div class="hks-article-card__body">
				<?php if ( $topics || $destination ) : ?><p class="hks-article-card__meta"><?php echo esc_html( implode( ' · ', array_slice( array_merge( $topics, $destination ), 0, 2 ) ) ); ?></p><?php endif; ?>
				<<?php echo esc_attr( $heading ); ?>><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a></<?php echo esc_attr( $heading ); ?>>
				<?php if ( $excerpt ) : ?><p><?php echo esc_html( wp_trim_words( $excerpt, 28 ) ); ?></p><?php endif; ?>
				<a class="hks-article-card__link" href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Read guide', 'hks-wayfinder' ); ?> <span aria-hidden="true">→</span></a>
			</div>
		</article>
		<?php
		return (string) ob_get_clean();
	}

	public static function render_article_page(): string {
		$post_id = get_the_ID();
		if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
			return '';
		}

		$title       = self::text( get_the_title( $post_id ) );
		$excerpt     = self::text( get_post_field( 'post_excerpt', $post_id ) );
		$format      = sanitize_key( (string) self::field( 'hks_article_format', $post_id ) ) ?: 'guide';
		$is_ad       = 'advertorial' === $format;
		$tour_id     = self::post_id( self::field( 'hks_article_primary_tour', $post_id ) );
		$tour_valid  = $tour_id && 'hks_tour' === get_post_type( $tour_id ) && 'publish' === get_post_status( $tour_id );
		$image_id    = get_post_thumbnail_id( $post_id );
		$destinations = self::term_names( $post_id, 'hks_destination' );
		$topics       = self::term_names( $post_id, 'hks_article_topic' );
		$content      = apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
		$outline      = $is_ad ? self::prepare_article_outline( $content ) : array( 'content' => $content, 'headings' => array() );
		$content      = $outline['content'];
		$toc          = $is_ad ? self::render_article_toc( $outline['headings'] ) : '';
		$tour_title   = $tour_valid ? self::text( get_the_title( $tour_id ) ) : '';
		$tour_link    = $tour_valid ? get_permalink( $tour_id ) : '';
		$quote        = $is_ad && $tour_valid ? do_blocks( '<!-- wp:hks/quote-cta {"location":"article_sidebar","label":"Request a quote"} /-->' ) : '';
		if ( $is_ad && ! $image_id && $tour_valid ) {
			$image_id = get_post_thumbnail_id( $tour_id );
		}
		$learn_more_target = $toc ? '#hks-article-toc' : '#hks-article-content';

		ob_start();
		?>
		<article class="hks-article hks-article--<?php echo esc_attr( $is_ad ? 'advertorial' : 'guide' ); ?>" data-hks-article-id="<?php echo esc_attr( (string) $post_id ); ?>" data-hks-article-format="<?php echo esc_attr( $is_ad ? 'advertorial' : 'guide' ); ?>" data-hks-primary-tour-id="<?php echo esc_attr( (string) ( $tour_valid ? $tour_id : 0 ) ); ?>">
			<?php if ( $is_ad ) : ?>
				<header class="hks-article-hero hks-article-hero--advertorial-cover<?php echo $image_id ? ' hks-article-hero--with-image' : ' hks-article-hero--without-image'; ?>">
					<?php if ( $image_id ) : ?><figure class="hks-article-hero__media" aria-hidden="true"><?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'full', false, array( 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw' ) ) ); ?></figure><?php endif; ?>
					<span class="hks-article-hero__wash" aria-hidden="true"></span>
					<div class="hks-shell hks-article-hero__inner">
						<div class="hks-article-hero__copy">
							<?php self::breadcrumbs( array( __( 'Travel Guides', 'hks-wayfinder' ) => home_url( '/travel-guides/' ), $title => '' ) ); ?>
							<?php if ( $topics || $destinations ) : ?><p class="hks-article-kicker"><?php echo esc_html( implode( ' · ', array_slice( array_merge( $destinations, $topics ), 0, 2 ) ) ); ?></p><?php endif; ?>
							<h1><?php echo esc_html( $title ); ?></h1>
							<?php if ( $excerpt ) : ?><p class="hks-article-hero__promise"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
							<div class="hks-article-hero__actions">
								<?php if ( $quote ) : ?><button class="hks-button hks-article-hero__quote hks-article-hero__cta--outline" type="button" data-hks-quote-proxy data-hks-article-early-quote data-hks-cta-location="article_hero"><?php esc_html_e( 'Book this tour now', 'hks-wayfinder' ); ?></button><?php endif; ?>
								<a class="hks-button hks-article-hero__learn" href="<?php echo esc_url( $learn_more_target ); ?>"><?php esc_html_e( 'Learn more', 'hks-wayfinder' ); ?></a>
							</div>
						</div>
					</div>
				</header>
				<?php if ( $toc ) : ?><section class="hks-article-outline" aria-label="<?php esc_attr_e( 'Article contents', 'hks-wayfinder' ); ?>"><div class="hks-shell"><?php echo $toc; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated and escaped by render_article_toc(). ?></div></section><?php endif; ?>
			<?php else : ?>
				<header class="hks-article-hero<?php echo $image_id ? ' hks-article-hero--with-image' : ''; ?>">
					<div class="hks-article-hero__copy">
						<?php self::breadcrumbs( array( __( 'Travel Guides', 'hks-wayfinder' ) => home_url( '/travel-guides/' ), $title => '' ) ); ?>
						<?php if ( $topics || $destinations ) : ?><p class="hks-article-kicker"><?php echo esc_html( implode( ' · ', array_slice( array_merge( $destinations, $topics ), 0, 2 ) ) ); ?></p><?php endif; ?>
						<h1><?php echo esc_html( $title ); ?></h1>
						<?php if ( $excerpt ) : ?><p class="hks-article-hero__promise"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
						<?php if ( $tour_valid ) : ?><a class="hks-button hks-article-hero__tour-link" data-hks-article-primary-tour-click data-hks-cta-location="article_hero" href="<?php echo esc_url( $tour_link ); ?>"><?php esc_html_e( 'View this trip', 'hks-wayfinder' ); ?> <span aria-hidden="true">→</span></a><?php endif; ?>
					</div>
					<?php if ( $image_id ) : ?><figure class="hks-article-hero__media"><?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'large', false, array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 800px) 100vw, 48vw' ) ) ); ?></figure><?php endif; ?>
				</header>
			<?php endif; ?>
			<div class="hks-article-layout<?php echo $is_ad && $quote ? ' hks-article-layout--conversion' : ''; ?>">
				<div id="hks-article-content" class="hks-article-content hks-prose">
					<?php if ( $is_ad && $tour_valid && $tour_title ) : ?><p class="hks-article-intent"><?php echo esc_html( sprintf( __( 'Considering %s?', 'hks-wayfinder' ), $tour_title ) ); ?></p><?php endif; ?>
					<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Filtered through the_content. ?>
					<?php if ( $tour_valid && ! $is_ad ) : ?><div class="hks-article-tour-cta"><p><?php esc_html_e( 'Ready to explore the route?', 'hks-wayfinder' ); ?></p><a class="hks-button" data-hks-article-primary-tour-click data-hks-cta-location="article_footer" href="<?php echo esc_url( $tour_link ); ?>"><?php esc_html_e( 'View this trip', 'hks-wayfinder' ); ?> <span aria-hidden="true">→</span></a></div><?php endif; ?>
					<?php if ( $is_ad && $quote ) : ?><section class="hks-article-final-quote" aria-labelledby="hks-article-final-quote-title"><p class="hks-article-kicker"><?php esc_html_e( 'Your next step', 'hks-wayfinder' ); ?></p><h2 id="hks-article-final-quote-title"><?php esc_html_e( 'Turn the idea into a trip that fits your dates.', 'hks-wayfinder' ); ?></h2><p><?php esc_html_e( 'Share your preferred dates and group size. You will review the prepared message before choosing WhatsApp or email.', 'hks-wayfinder' ); ?></p><button class="hks-button" type="button" data-hks-quote-proxy data-hks-cta-location="article_final"><?php esc_html_e( 'Request a quote', 'hks-wayfinder' ); ?></button></section><?php endif; ?>
				</div>
				<?php if ( $is_ad && $quote ) : ?>
					<aside class="hks-article-conversion-panel" aria-label="<?php esc_attr_e( 'Request a quote for this trip', 'hks-wayfinder' ); ?>">
						<p class="hks-article-kicker"><?php esc_html_e( 'Plan this trip', 'hks-wayfinder' ); ?></p>
						<h2><?php echo esc_html( $tour_title ); ?></h2>
						<ul class="hks-tour-quote__reassurances hks-article-conversion-panel__reassurances">
							<li><span aria-hidden="true">✅</span><span><?php esc_html_e( 'Inclusions and exclusions clarified', 'hks-wayfinder' ); ?></span></li>
							<li><span aria-hidden="true">✅</span><span><?php esc_html_e( 'No booking commitment required', 'hks-wayfinder' ); ?></span></li>
							<li><span aria-hidden="true">✅</span><span><?php esc_html_e( 'Fast Responses to all queries', 'hks-wayfinder' ); ?></span></li>
						</ul>
						<?php echo $quote; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Registered HKS quote block. ?>
					</aside>
				<?php endif; ?>
			</div>
			<?php self::render_related_posts( $post_id ); ?>
			<?php if ( $is_ad && $quote ) : ?><div class="hks-article-mobile-quote" data-hks-article-mobile-quote aria-hidden="true"><button type="button" data-hks-quote-proxy data-hks-cta-location="article_mobile_sticky"><?php esc_html_e( 'Request a quote', 'hks-wayfinder' ); ?></button></div><?php endif; ?>
		</article>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Add stable, unique IDs to every rendered H2 and H3 and return their labels.
	 *
	 * The outline is derived after the_content filters run so it always follows
	 * the headings a visitor actually sees. Existing IDs are kept when they do
	 * not collide with another element; later duplicates receive a stable suffix.
	 *
	 * @param string $content Filtered article HTML.
	 * @return array{content:string,headings:array<int,array{level:int,id:string,label:string}>}
	 */
	private static function prepare_article_outline( string $content ): array {
		if ( ! class_exists( '\\WP_HTML_Processor' ) || '' === trim( $content ) ) {
			return array( 'content' => $content, 'headings' => array() );
		}

		$processor        = \WP_HTML_Processor::create_fragment( $content );
		$headings         = array();
		$current_heading  = null;
		$non_heading_ids  = array(
			'hks-article-content'           => true,
			'hks-article-toc'               => true,
			'hks-article-toc-title'         => true,
			'hks-article-final-quote-title' => true,
		);
		$original_ids     = $non_heading_ids;

		while ( $processor->next_token() ) {
			$token_type = $processor->get_token_type();

			if ( '#tag' === $token_type ) {
				$tag       = $processor->get_tag();
				$is_closer = $processor->is_tag_closer();

				if ( ! $is_closer ) {
					$id         = $processor->get_attribute( 'id' );
					$id         = is_string( $id ) ? trim( $id ) : '';
					$is_heading = 'H2' === $tag || 'H3' === $tag;

					if ( '' !== $id ) {
						$original_ids[ $id ] = true;
						if ( ! $is_heading ) {
							$non_heading_ids[ $id ] = true;
						}
					}

					if ( $is_heading ) {
						$headings[]        = array(
							'level'       => (int) substr( $tag, 1 ),
							'original_id' => $id,
							'label'       => '',
						);
						$current_heading = array_key_last( $headings );
					}
				} elseif ( null !== $current_heading && 'H' . $headings[ $current_heading ]['level'] === $tag ) {
					$current_heading = null;
				}
			} elseif ( '#text' === $token_type && null !== $current_heading ) {
				$headings[ $current_heading ]['label'] .= $processor->get_modifiable_text();
			}
		}

		if ( null !== $processor->get_last_error() || ! $headings ) {
			return array( 'content' => $content, 'headings' => array() );
		}

		$assigned_ids  = array();
		$preserved_ids = array();

		foreach ( $headings as $index => &$heading ) {
			$label = wp_specialchars_decode( wp_strip_all_tags( $heading['label'] ), ENT_QUOTES );
			$label = preg_replace( '/\s+/u', ' ', trim( $label ) );
			$label = is_string( $label ) && '' !== $label ? $label : sprintf( __( 'Section %d', 'hks-wayfinder' ), $index + 1 );

			$original_id = $heading['original_id'];
			$can_preserve = '' !== $original_id
				&& ! isset( $non_heading_ids[ $original_id ] )
				&& ! isset( $preserved_ids[ $original_id ] );

			if ( $can_preserve ) {
				$id = $original_id;
				$preserved_ids[ $id ] = true;
			} else {
				$base = sanitize_title( $label );
				$base = '' !== $base ? $base : 'section-' . ( $index + 1 );
				$id   = $base;
				$suffix = 2;
				while ( isset( $original_ids[ $id ] ) || isset( $assigned_ids[ $id ] ) ) {
					$id = $base . '-' . $suffix;
					++$suffix;
				}
			}

			$heading = array(
				'level' => $heading['level'],
				'id'    => $id,
				'label' => $label,
			);
			$assigned_ids[ $id ] = true;
		}
		unset( $heading );

		$rewriter      = \WP_HTML_Processor::create_fragment( $content );
		$heading_index = 0;
		while ( $rewriter->next_tag() && isset( $headings[ $heading_index ] ) ) {
			$tag = $rewriter->get_tag();
			if ( 'H2' !== $tag && 'H3' !== $tag ) {
				continue;
			}
			$rewriter->set_attribute( 'id', $headings[ $heading_index ]['id'] );
			++$heading_index;
		}

		if ( null !== $rewriter->get_last_error() || $heading_index !== count( $headings ) ) {
			return array( 'content' => $content, 'headings' => array() );
		}

		return array(
			'content'  => $rewriter->get_updated_html(),
			'headings' => $headings,
		);
	}

	/**
	 * Render the advertorial's simple, fully expanded table of contents.
	 *
	 * @param array<int,array{level:int,id:string,label:string}> $headings Article outline.
	 */
	private static function render_article_toc( array $headings ): string {
		if ( ! $headings ) {
			return '';
		}

		$groups        = array();
		$current_group = null;
		foreach ( $headings as $heading ) {
			if ( 2 === $heading['level'] ) {
				$groups[]      = array( 'heading' => $heading, 'children' => array(), 'orphan' => false );
				$current_group = array_key_last( $groups );
			} elseif ( null !== $current_group ) {
				$groups[ $current_group ]['children'][] = $heading;
			} else {
				$groups[] = array( 'heading' => $heading, 'children' => array(), 'orphan' => true );
			}
		}

		ob_start();
		?>
		<nav id="hks-article-toc" class="hks-article-toc" aria-labelledby="hks-article-toc-title">
			<h2 id="hks-article-toc-title"><?php esc_html_e( 'What we’ll cover', 'hks-wayfinder' ); ?></h2>
			<ul class="hks-article-toc__list">
				<?php foreach ( $groups as $group ) : ?>
					<li<?php echo $group['orphan'] ? ' class="hks-article-toc__orphan"' : ''; ?>><a href="#<?php echo esc_attr( $group['heading']['id'] ); ?>"><?php echo esc_html( $group['heading']['label'] ); ?></a>
						<?php if ( $group['children'] ) : ?>
							<ul>
								<?php foreach ( $group['children'] as $child ) : ?><li><a href="#<?php echo esc_attr( $child['id'] ); ?>"><?php echo esc_html( $child['label'] ); ?></a></li><?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<?php
		return (string) ob_get_clean();
	}

	public static function render_destination_guides(): string {
		$term = get_queried_object();
		if ( ! $term instanceof \WP_Term || 'hks_destination' !== $term->taxonomy ) {
			return '';
		}
		$query = new \WP_Query(
			array(
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => 3,
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'tax_query'              => array( array( 'taxonomy' => 'hks_destination', 'field' => 'term_id', 'terms' => $term->term_id ) ),
			)
		);
		if ( ! $query->have_posts() ) {
			return '';
		}
		ob_start();
		?><section class="hks-destination-guides" aria-labelledby="hks-destination-guides-title"><div class="hks-shell"><div class="hks-section-heading"><div><p><?php esc_html_e( 'Travel Guides', 'hks-wayfinder' ); ?></p><h2 id="hks-destination-guides-title"><?php esc_html_e( 'Plan your visit', 'hks-wayfinder' ); ?></h2></div><a href="<?php echo esc_url( home_url( '/travel-guides/' ) ); ?>"><?php esc_html_e( 'Browse all guides', 'hks-wayfinder' ); ?> <span aria-hidden="true">→</span></a></div><div class="hks-article-grid"><?php while ( $query->have_posts() ) : $query->the_post(); echo self::render_article_card(); endwhile; ?></div></div></section><?php
		wp_reset_postdata();
		return (string) ob_get_clean();
	}

	private static function render_related_posts( int $post_id ): void {
		$selected = self::field( 'hks_article_related_posts', $post_id );
		$ids      = array();
		foreach ( is_array( $selected ) ? $selected : array() as $item ) {
			$id = self::post_id( $item );
			if ( $id && $id !== $post_id && 'post' === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) {
				$ids[] = $id;
			}
		}
		$ids = array_values( array_unique( $ids ) );
		foreach ( array( 'hks_destination', 'hks_article_topic' ) as $taxonomy ) {
			if ( count( $ids ) >= 3 ) { break; }
			$terms = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
			if ( is_wp_error( $terms ) || ! $terms ) { continue; }
			$query = new \WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3, 'post__not_in' => array_merge( array( $post_id ), $ids ), 'tax_query' => array( array( 'taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => $terms ) ), 'no_found_rows' => true ) );
			while ( $query->have_posts() && count( $ids ) < 3 ) { $query->the_post(); $ids[] = get_the_ID(); }
			wp_reset_postdata();
		}
		if ( ! $ids ) { return; }
		$query = new \WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3, 'post__in' => $ids, 'orderby' => 'post__in', 'no_found_rows' => true ) );
		?><section class="hks-article-related" data-hks-article-quote-stop aria-labelledby="hks-article-related-title"><div class="hks-shell"><div class="hks-section-heading"><div><p><?php esc_html_e( 'Keep planning', 'hks-wayfinder' ); ?></p><h2 id="hks-article-related-title"><?php esc_html_e( 'More Travel Guides', 'hks-wayfinder' ); ?></h2></div></div><div class="hks-article-grid"><?php while ( $query->have_posts() ) : $query->the_post(); echo self::render_article_card(); endwhile; ?></div></div></section><?php
		wp_reset_postdata();
	}

	private static function field( string $name, int $post_id ) {
		return function_exists( 'get_field' ) ? get_field( $name, $post_id ) : get_post_meta( $post_id, $name, true );
	}

	private static function post_id( $value ): int {
		if ( is_object( $value ) && isset( $value->ID ) ) { return absint( $value->ID ); }
		return absint( $value );
	}

	private static function text( $value ): string {
		return trim( wp_strip_all_tags( (string) $value ) );
	}

	private static function term_names( int $post_id, string $taxonomy ): array {
		$terms = get_the_terms( $post_id, $taxonomy );
		return is_array( $terms ) ? array_values( array_filter( array_map( static fn( $term ) => self::text( $term->name ), $terms ) ) ) : array();
	}

	/**
	 * Return only terms attached to published native Posts.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @return \WP_Term[]
	 */
	private static function terms_for_posts( string $taxonomy ): array {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return array();
		}

		$post_ids = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		if ( ! $post_ids ) {
			return array();
		}

		$terms = wp_get_object_terms( $post_ids, $taxonomy, array( 'orderby' => 'name', 'order' => 'ASC' ) );
		return is_wp_error( $terms ) ? array() : $terms;
	}

	private static function breadcrumbs( array $items ): void {
		echo '<nav class="hks-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'hks-wayfinder' ) . '"><ol><li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'hks-wayfinder' ) . '</a></li>';
		foreach ( $items as $label => $url ) {
			echo '<li>' . ( $url ? '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>' : '<span aria-current="page">' . esc_html( $label ) . '</span>' ) . '</li>';
		}
		echo '</ol></nav>';
	}
}
