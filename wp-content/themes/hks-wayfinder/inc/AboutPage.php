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
		$email   = self::setting( 'public_email' );
		$email   = is_email( $email ) ? $email : '';
		$number  = preg_replace( '/\D/', '', self::setting( 'whatsapp_number' ) );
		$chat    = '';

		if ( ! preg_match( '/^\+?[0-9]{7,15}$/', $tel ) ) {
			$phone = '';
		}

		if ( preg_match( '/^[1-9][0-9]{6,14}$/', $number ) ) {
			$chat = 'https://wa.me/' . $number . '?text=' . rawurlencode( __( "Hi Holiday Kenya Safaris, I'd like help choosing and planning a trip.", 'hks-wayfinder' ) );
		}

		ob_start();
		?>
		<?php if ( $address || $hours || $map_url || $embed ) : ?>
			<section class="hks-about-visit" aria-labelledby="hks-about-visit-title">
				<div class="hks-shell hks-about-details">
					<h2 id="hks-about-visit-title"><?php esc_html_e( 'Visit Us', 'hks-wayfinder' ); ?></h2>
					<div class="hks-about-visit__details">
						<?php if ( $address ) : ?>
							<address><?php echo nl2br( esc_html( $address ) ); ?></address>
						<?php endif; ?>
						<?php if ( $hours ) : ?>
							<div class="hks-about-hours"><p><?php esc_html_e( 'Working hours', 'hks-wayfinder' ); ?></p><p><?php echo nl2br( esc_html( $hours ) ); ?></p></div>
						<?php endif; ?>
						<?php if ( $map_url ) : ?>
							<a class="hks-about-directions" href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get directions on Google Maps', 'hks-wayfinder' ); ?><span class="hks-sr-only"><?php esc_html_e( ' (opens in a new tab)', 'hks-wayfinder' ); ?></span></a>
						<?php endif; ?>
					</div>
					<?php if ( $embed ) : ?>
						<iframe class="hks-about-map" src="<?php echo esc_url( $embed ); ?>" title="<?php esc_attr_e( 'Office location on Google Maps', 'hks-wayfinder' ); ?>" width="1200" height="360" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $phone || $email || $chat ) : ?>
			<section class="hks-about-contact" aria-labelledby="hks-about-contact-title">
				<div class="hks-shell hks-about-details">
					<div><h2 id="hks-about-contact-title"><?php esc_html_e( 'Contact Us', 'hks-wayfinder' ); ?></h2><p><?php esc_html_e( 'Have a trip in mind, or a question before you choose? Talk to our team.', 'hks-wayfinder' ); ?></p></div>
					<dl class="hks-about-contact__links">
						<?php if ( $phone ) : ?>
							<div><dt><?php esc_html_e( 'Call us', 'hks-wayfinder' ); ?></dt><dd><a href="<?php echo esc_url( 'tel:' . $tel ); ?>"><?php echo esc_html( $phone ); ?></a></dd></div>
						<?php endif; ?>
						<?php if ( $email ) : ?>
							<div><dt><?php esc_html_e( 'Email', 'hks-wayfinder' ); ?></dt><dd><a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a></dd></div>
						<?php endif; ?>
						<?php if ( $chat ) : ?>
							<div><dt><?php esc_html_e( 'WhatsApp', 'hks-wayfinder' ); ?></dt><dd><a href="<?php echo esc_url( $chat ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Chat with our team', 'hks-wayfinder' ); ?><span class="hks-sr-only"><?php esc_html_e( ' (opens in a new tab)', 'hks-wayfinder' ); ?></span></a></dd></div>
						<?php endif; ?>
					</dl>
				</div>
			</section>
		<?php endif; ?>
		<?php
		return (string) ob_get_clean();
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

		$group = get_field( 'hks_settings_' . $name, 'hks_settings' );
		$value = is_array( $group ) ? ( $group['value'] ?? '' ) : '';

		if ( ! is_string( $value ) || str_contains( $value, 'CLIENT CONFIRMATION REQUIRED' ) ) {
			return '';
		}

		$value = preg_replace( '/<br\s*\/?>(?:\r\n|\r|\n)?/i', "\n", $value );
		return trim( wp_strip_all_tags( $value ) );
	}
}
