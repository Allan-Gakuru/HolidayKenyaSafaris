<?php
/**
 * Public office and contact details for the About page.
 *
 * @package HKS_Wayfinder
 */

namespace HKS_Wayfinder;

defined( 'ABSPATH' ) || exit;

final class AboutPage {

	/** Register the settings-backed presentation block. */
	public static function register(): void {
		register_block_type(
			get_theme_file_path( 'blocks/about-contact' ),
			array( 'render_callback' => array( self::class, 'render_contact' ) )
		);
	}

	/** Render only populated public settings; never expose private recipients. */
	public static function render_contact(): string {
		$address = self::setting( 'postal_address' );
		$hours   = self::setting( 'business_hours' );
		$map_url = esc_url( self::setting( 'map_url' ), array( 'https', 'http' ) );
		$embed   = self::map_embed_url();
		$phone   = self::setting( 'public_phone' );
		$tel     = preg_replace( '/[^0-9+]/', '', $phone );
		$number  = preg_replace( '/\D/', '', self::setting( 'whatsapp_number' ) );
		$chat    = '';
		$social  = self::social_links();

		if ( ! preg_match( '/^\+?[0-9]{7,15}$/', $tel ) ) {
			$phone = '';
		}

		if ( preg_match( '/^[1-9][0-9]{6,14}$/', $number ) ) {
			$chat = 'https://wa.me/' . $number . '?text=' . rawurlencode( __( "Hi Holiday Kenya Safaris, I'd like help choosing and planning a trip.", 'hks-wayfinder' ) );
		}

		if ( ! $address && ! $hours && ! $map_url && ! $embed && ! $phone && ! $chat && ! $social ) {
			return '';
		}

		ob_start();
		?>
		<div class="hks-about-details"><div class="hks-shell hks-about-columns">
		<?php if ( $address || $hours || $map_url || $embed ) : ?>
			<section class="hks-about-visit" aria-labelledby="hks-about-visit-title">
					<h2 id="hks-about-visit-title"><?php esc_html_e( 'Visit Us', 'hks-wayfinder' ); ?></h2>
					<div class="hks-about-visit__details">
						<?php if ( $hours ) : ?>
							<div class="hks-about-hours"><p><?php esc_html_e( 'Working hours', 'hks-wayfinder' ); ?></p><p><?php echo nl2br( esc_html( $hours ) ); ?></p></div>
						<?php endif; ?>
						<?php if ( $address ) : ?>
							<address><?php echo nl2br( esc_html( $address ) ); ?></address>
						<?php endif; ?>
						<?php if ( $embed ) : ?>
							<iframe class="hks-about-map" src="<?php echo esc_url( $embed ); ?>" title="<?php esc_attr_e( 'Office location on Google Maps', 'hks-wayfinder' ); ?>" width="600" height="220" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
						<?php endif; ?>
						<?php if ( $map_url ) : ?>
							<a class="hks-about-directions" href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get directions on Google Maps', 'hks-wayfinder' ); ?><span class="hks-sr-only"><?php esc_html_e( ' (opens in a new tab)', 'hks-wayfinder' ); ?></span></a>
						<?php endif; ?>
					</div>
			</section>
		<?php endif; ?>

		<?php if ( $phone || $chat || $social ) : ?>
			<section class="hks-about-contact" aria-labelledby="hks-about-contact-title">
				<h2 id="hks-about-contact-title"><?php esc_html_e( 'Contact Us', 'hks-wayfinder' ); ?></h2>
				<?php if ( $phone || $chat ) : ?>
					<div class="hks-about-contact__actions">
						<?php if ( $phone ) : ?>
							<a class="hks-about-phone" href="<?php echo esc_url( 'tel:' . $tel ); ?>"><?php esc_html_e( 'Call ', 'hks-wayfinder' ); ?><?php echo esc_html( $phone ); ?></a>
						<?php endif; ?>
						<?php if ( $chat ) : ?>
							<a class="hks-button hks-about-chat" href="<?php echo esc_url( $chat ); ?>" target="_blank" rel="noopener noreferrer"><svg class="hks-about-chat__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-1.76-.88-2.91-1.57-4.07-3.56-.31-.53.31-.49.88-1.63.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49 1.88.81 2.62.88 3.56.74.57-.09 1.76-.72 2.01-1.41.25-.69.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35ZM12.05 21.8h-.01a9.9 9.9 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26C2.16 6.45 6.6 2 12.06 2a9.83 9.83 0 0 1 9.89 9.9c0 5.45-4.44 9.9-9.9 9.9Zm8.41-18.3A11.82 11.82 0 0 0 12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.9 11.9 0 0 0 5.69 1.45c6.55 0 11.89-5.34 11.89-11.89 0-3.18-1.23-6.16-3.48-8.41Z"></path></svg><span><?php esc_html_e( 'Chat on WhatsApp', 'hks-wayfinder' ); ?></span><span class="hks-sr-only"><?php esc_html_e( ' (opens in a new tab)', 'hks-wayfinder' ); ?></span></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ( $social ) : ?>
					<div class="hks-about-social">
						<p><?php esc_html_e( 'Keep up with Holiday Kenya Safaris updates on social media.', 'hks-wayfinder' ); ?></p>
						<div class="hks-about-social__links">
							<?php foreach ( $social as $network => $url ) : ?>
								<a class="hks-about-follow hks-about-follow--<?php echo esc_attr( strtolower( $network ) ); ?>" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
									<?php if ( 'Facebook' === $network ) : ?>
										<svg class="hks-about-follow__icon hks-about-follow__icon--facebook" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M14 8h3V4.5c-.5-.1-2.2-.2-3.4-.2-3.4 0-5.7 2.1-5.7 6v3.3H4v3.9h3.9V24h4.8v-6.5h3.6l.6-3.9h-4.2v-2.9C12.7 9.6 13 8 14 8Z"></path></svg>
									<?php else : ?>
										<svg class="hks-about-follow__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle cx="17.4" cy="6.6" r="1" fill="currentColor" stroke="none"></circle></svg>
									<?php endif; ?>
									<span><?php echo esc_html( sprintf( __( 'Follow us on %s', 'hks-wayfinder' ), $network ) ); ?></span><span class="hks-sr-only"><?php esc_html_e( ' (opens in a new tab)', 'hks-wayfinder' ); ?></span>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>
		</div></div>
		<?php
		return (string) ob_get_clean();
	}

	/** Read only the two requested public profiles, in a consistent order. */
	private static function social_links(): array {
		$rows  = function_exists( 'get_field' ) ? get_field( 'field_hks_settings_social_links', 'hks_settings' ) : array();
		$links = array();

		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			if ( ! is_array( $row ) || ! is_string( $row['network'] ?? null ) || ! is_string( $row['url'] ?? null ) ) {
				continue;
			}
			$network = strtolower( trim( $row['network'] ) );
			$url     = esc_url( trim( $row['url'] ), array( 'https', 'http' ) );
			if ( $url && in_array( $network, array( 'facebook', 'instagram' ), true ) ) {
				$links[ ucfirst( $network ) ] = $url;
			}
		}

		return array_merge( array_intersect_key( array( 'Facebook' => '', 'Instagram' => '' ), $links ), $links );
	}

	/** Accept only Google's HTTPS embed endpoint, never arbitrary iframe content. */
	private static function map_embed_url(): string {
		$url   = self::setting( 'map_embed_url' );
		$parts = wp_parse_url( $url );

		if ( ! is_array( $parts ) || 'https' !== ( $parts['scheme'] ?? '' ) || 'www.google.com' !== ( $parts['host'] ?? '' ) || '/maps/embed' !== ( $parts['path'] ?? '' ) || isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['port'] ) ) {
			return '';
		}

		return $url;
	}

	/** Read the existing SCF group, retaining line breaks but no HTML or audit fields. */
	private static function setting( string $name ): string {
		if ( ! function_exists( 'get_field' ) ) {
			return '';
		}

		// Registered keys also resolve defaults before a new option has been saved.
		$group = get_field( 'field_hks_settings_' . $name, 'hks_settings' );
		$value = is_array( $group ) ? ( $group['value'] ?? '' ) : '';

		if ( ! is_string( $value ) || str_contains( $value, 'CLIENT CONFIRMATION REQUIRED' ) ) {
			return '';
		}

		$value = preg_replace( '/<br\s*\/?>(?:\r\n|\r|\n)?/i', "\n", $value );
		return trim( wp_strip_all_tags( $value ) );
	}
}
