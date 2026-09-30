<?php
/** Focused, server-rendered presentation Pages in the existing Wayfinder identity. */
namespace HKS_Wayfinder;

use HolidayKenyaSafaris\Core\Webinar\Event;
use HolidayKenyaSafaris\Core\Webinar\Calendar;
use HolidayKenyaSafaris\Core\Webinar\Registration;

defined( 'ABSPATH' ) || exit;

final class WebinarPage {
	public static function register(): void {
		register_block_type( 'hks-wayfinder/webinar-registration', array( 'api_version' => 3, 'render_callback' => array( self::class, 'registration' ) ) );
		register_block_type( 'hks-wayfinder/webinar-thanks', array( 'api_version' => 3, 'render_callback' => array( self::class, 'thanks' ) ) );
	}

	public static function is_current(): bool {
		return is_singular( 'page' ) && in_array( get_page_template_slug(), array( 'webinar-registration', 'webinar-thanks' ), true );
	}

	public static function enqueue(): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
		wp_dequeue_style( 'wp-emoji-styles' );
		$css = get_theme_file_path( 'assets/css/webinar.css' );
		$js = get_theme_file_path( 'assets/js/webinar.js' );
		wp_enqueue_style( 'hks-webinar', get_theme_file_uri( 'assets/css/webinar.css' ), array(), (string) filemtime( $css ) );
		wp_enqueue_script( 'hks-webinar', get_theme_file_uri( 'assets/js/webinar.js' ), array(), (string) filemtime( $js ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}

	private static function header(): string {
		return '<a class="hks-webinar-skip" href="#main-content">Skip to content</a><header class="hks-webinar-header"><a href="' . esc_url( home_url( '/' ) ) . '" aria-label="Holiday Kenya Safaris home"><img src="' . esc_url( get_theme_file_uri( 'assets/images/brand/holiday-kenya-safaris-logo.svg' ) ) . '" width="224" height="68" alt="Holiday Kenya Safaris"></a><span>Operated by<br><strong>Ashford Tours &amp; Travel</strong></span></header>';
	}

	private static function footer(): string {
		$privacy = Event::privacy_url();
		return '<footer class="hks-webinar-footer"><span>Holiday Kenya Safaris · Operated by Ashford Tours &amp; Travel</span>' . ( $privacy ? '<a href="' . esc_url( $privacy ) . '">Privacy policy</a>' : '' ) . '</footer>';
	}

	private static function arrow( bool $down = false ): string {
		$path = $down ? 'M12 5v14m-6-6 6 6 6-6' : 'M5 12h14m-6-6 6 6-6 6';
		return '<svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="' . $path . '"></path></svg>';
	}

	private static function event_details( bool $full = false ): string {
		$start = Event::start();
		$event = Event::details();
		$date = $start ? $start->format( 'l j F Y' ) : '[Webinar date]';
		$time = $start ? $start->format( 'g:i a' ) . ( $full ? '–' . Event::end()->format( 'g:i a' ) : '' ) . ' EAT' : '[Start time EAT]';
		return '<div class="hks-webinar-event"><p class="hks-webinar-event__date">' . esc_html( $date ) . '</p><p>' . esc_html( $time ) . ' <span aria-hidden="true">·</span> Live online <span aria-hidden="true">·</span> About ' . (int) $event['duration'] . ' minutes' . ( $full ? '<br><small>Timezone: Africa/Nairobi · Includes Q&amp;A</small>' : '</p><p class="hks-webinar-event__note">Includes time for your questions' ) . '</p></div>';
	}

	public static function registration(): string {
		if ( ! class_exists( Event::class ) ) {
			return '';
		}
		$event = Event::details();
		$preview = is_preview() && current_user_can( 'edit_pages' );
		$open = Event::accepting();
		if ( ! $open && ! $preview ) {
			return '<div class="hks-webinar">' . self::header() . '<main id="main-content" class="hks-webinar-closed"><h1>Christmas in Diani.<br>A holiday for you, too.</h1><p>' . ( Event::start() && Event::start()->getTimestamp() <= time() ? 'Registration for this presentation has closed.' : 'Presentation details are being confirmed. Registration will open soon.' ) . '</p></main>' . self::footer() . '</div>';
		}
		ob_start();
		?>
		<!-- THESIS: A useful family holiday walkthrough earns an informed registration.
		OWN-WORLD: Wayfinder Navy, Mist, Teal and Saffron; Montserrat; a real Diani photograph.
		STORY: Picture the holiday, understand the full cost, reserve a presentation place.
		FIRST VIEWPORT: Left promise and event details, right compact form; mobile leads with promise and action.
		FORM: Code-led scoped extension of the user-approved Wayfinder identity and precise page brief; no concept roll or generated comp.
		FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance -->
		<div class="hks-webinar" data-hks-webinar="<?php echo esc_attr( Event::KEY ); ?>">
		<?php echo self::header(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<main id="main-content" class="hks-webinar-layout">
			<section class="hks-webinar-story" aria-labelledby="hks-webinar-title">
				<h1 id="hks-webinar-title">Christmas in Diani.<br><span>A holiday for you, too.</span></h1>
				<p class="hks-webinar-lead">You want to see the children enjoying themselves, and feel like you’ve had a holiday too.</p>
				<?php echo self::event_details(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<a class="hks-button hks-webinar-opening-cta" href="#registration" data-webinar-cta="hero">Reserve my place<?php echo self::arrow(); ?></a>
				<p class="hks-webinar-intro">Join Holiday Kenya Safaris for a practical walkthrough of five nights at Diani Sea Resort. See the Comfort rooms, meals, children’s activities and full family cost, then ask your questions live.</p>
				<div class="hks-webinar-reasons"><h2>Come with questions. Leave with clarity.</h2><ul>
					<li><strong>Where will everyone sleep?</strong> Picture the room and how a family day could work.</li>
					<li><strong>What’s included?</strong> Understand the meals, selected drinks and costs to allow for.</li>
					<li><strong>What will our whole family pay?</strong> See the full price and how the booking payment works.</li>
				</ul></div>
				<div class="hks-webinar-host">
					<?php if ( $event['host'] && $event['presenter_image_id'] ) { echo wp_get_attachment_image( $event['presenter_image_id'], 'thumbnail', false, array( 'alt' => $event['host'], 'loading' => 'lazy', 'class' => 'hks-webinar-host__photo' ) ); } ?>
					<p><?php if ( $event['host'] ) : ?><strong><?php echo esc_html( $event['host'] ); ?></strong><br><?php endif; ?>Senior tour consultant and holiday expert.</p>
				</div>
			</section>
			<aside id="registration" class="hks-webinar-form-panel" aria-labelledby="hks-registration-title" tabindex="-1">
				<h2 id="hks-registration-title">Reserve your presentation place</h2>
				<p>Bring the one question you most want answered before booking.</p>
				<form method="post" action="<?php echo esc_url( Event::url( 'registration' ) ); ?>" data-webinar-form data-registration-open="<?php echo $open ? 'true' : 'false'; ?>" data-context-url="<?php echo esc_url( rest_url( 'hks/v1/webinar/form' ) ); ?>" data-submit-url="<?php echo esc_url( rest_url( 'hks/v1/webinar/registrations' ) ); ?>" novalidate>
					<div class="hks-webinar-status" data-form-status role="alert" tabindex="-1" hidden></div>
					<div class="hks-webinar-field"><label for="webinar-name">Your name <small>(Required)</small></label><input id="webinar-name" name="name" autocomplete="name" required maxlength="140" aria-describedby="webinar-name-error"><p id="webinar-name-error" data-error-for="name" class="hks-webinar-error" hidden></p></div>
					<div class="hks-webinar-field"><label for="webinar-email">Email address <small>(Required)</small></label><input id="webinar-email" name="email" type="email" autocomplete="email" required maxlength="254" aria-describedby="webinar-email-help webinar-email-error"><p id="webinar-email-help" class="hks-webinar-help">Use the email address where you want to receive presentation details.</p><p id="webinar-email-error" data-error-for="email" class="hks-webinar-error" hidden></p></div>
					<div class="hks-webinar-field"><label for="webinar-payment">If this holiday is right for your family, how much could you put towards the booking payment? <small>(Required)</small></label><select id="webinar-payment" name="payment_band" required aria-describedby="webinar-payment-help webinar-payment-error"><option value="" selected>Select an amount</option><?php foreach ( Event::BANDS as $value => $label ) : ?><option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select><p id="webinar-payment-help" class="hks-webinar-help">The planned booking payment is KES 60,000 per family and counts toward your holiday total. This answer is not a commitment. Choosing a higher amount won’t change the holiday price.</p><p id="webinar-payment-error" data-error-for="payment_band" class="hks-webinar-error" hidden></p></div>
					<div class="hks-webinar-field"><label for="webinar-priority">What’s the one thing you most want us to get right for your family? <small>(Optional)</small></label><textarea id="webinar-priority" name="priority" rows="2" maxlength="500" aria-describedby="webinar-priority-help webinar-priority-error"></textarea><p id="webinar-priority-help" class="hks-webinar-help">For example: food the children enjoy, comfortable sleeping arrangements, or time for you to relax.</p><p id="webinar-priority-error" data-error-for="priority" class="hks-webinar-error" hidden></p></div>
					<div class="hks-webinar-trap" aria-hidden="true"><label for="webinar-website">Leave this field empty</label><input id="webinar-website" name="website" tabindex="-1" autocomplete="off"></div>
					<p class="hks-webinar-registration-note">Registering saves your place at the presentation. It does not book the holiday or take a payment.</p>
					<button class="hks-button hks-webinar-submit" type="submit" data-webinar-cta="form" disabled>Reserve my place<?php echo self::arrow(); ?></button>
					<?php if ( Event::privacy_url() ) : ?><p class="hks-webinar-privacy">We’ll use your details for this presentation and its confirmation email. No unrelated marketing. <a href="<?php echo esc_url( Event::privacy_url() ); ?>">Privacy policy</a>.</p><?php else : ?><p class="hks-webinar-privacy">[Published privacy policy required before registration opens]</p><?php endif; ?>
					<?php if ( ! $open ) : ?><p class="hks-webinar-help">Draft preview. Registration opens after the confirmed event details are saved and both pages are published.</p><?php endif; ?>
					<noscript><p>Please enable JavaScript to register for the presentation.</p></noscript>
				</form>
			</aside>
			<figure class="hks-webinar-photo">
				<?php if ( $event['hero_id'] ) : ?>
					<?php echo wp_get_attachment_image( $event['hero_id'], 'large', false, array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 767px) calc(100vw - 40px), (max-width: 1100px) 50vw, 660px' ) ); ?>
				<?php elseif ( $preview && is_readable( get_theme_file_path( 'assets/images/webinar/diani-coast-preview.webp' ) ) ) : ?>
					<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/webinar/diani-coast-preview.webp' ) ); ?>" width="960" height="540" alt="Diani Sea Resort pool and palm gardens beside the Indian Ocean" loading="eager" fetchpriority="high">
				<?php endif; ?>
				<figcaption><strong>Diani Sea Resort · Comfort accommodation</strong><br>Hotel stay: 23–28 December 2026 · Five nights<br>Outbound economy SGR: evening of 22 December · Return: 28 December</figcaption>
			</figure>
			<p class="hks-webinar-readiness">Holiday places are limited and may fill during the presentation. Families ready to book will be invited to make the KES 60,000 booking payment before the live presentation ends. Come prepared if the holiday is right for you.</p>
		</main>
		<?php echo self::footer(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	public static function thanks(): string {
		if ( ! class_exists( Event::class ) ) {
			return '';
		}
		$id = Registration::receipt_id();
		$preview = is_preview() && current_user_can( 'edit_pages' );
		ob_start();
		?>
		<div class="hks-webinar" data-hks-webinar="<?php echo esc_attr( Event::KEY ); ?>">
		<?php echo self::header(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<main id="main-content" class="hks-webinar-thanks">
		<?php if ( ! $id && ! $preview ) : ?>
			<h1>Reserve your place at the presentation.</h1><p>Complete the registration form to receive presentation details and add the event to your calendar.</p><a class="hks-button" href="<?php echo esc_url( Event::url( 'registration' ) ); ?>">Reserve my place</a>
		<?php else : ?>
			<?php if ( $preview ) : ?><p class="hks-webinar-preview-note">Draft thank-you preview · A visitor sees this after a saved registration.</p><?php endif; ?>
			<div class="hks-webinar-success-mark" aria-hidden="true"><svg viewBox="0 0 32 32" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 16 6 6L25 9"></path></svg></div>
			<h1>You’re registered.<br>Add it to your calendar.</h1>
			<p class="hks-webinar-lead">We’ll walk you through the room, journey, meals, children’s activities and full family price, with time for your questions.</p><p>Bring the one question you most want answered before booking.</p>
			<section class="hks-webinar-event-card" aria-label="Presentation details"><h2><?php echo esc_html( Event::details()['title'] ); ?></h2><?php echo self::event_details( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( Event::calendar_ready() ) : ?>
				<details class="hks-webinar-calendar"><summary class="hks-button" data-calendar-open>Add to calendar<?php echo self::arrow( true ); ?></summary><div class="hks-webinar-calendar-options"><a href="<?php echo esc_url( Calendar::google_url() ); ?>" target="_blank" rel="noopener noreferrer" data-calendar-choice="google">Google Calendar <span class="hks-webinar-sr">(opens in a new tab)</span></a><a href="<?php echo esc_url( Calendar::download_url() ); ?>" data-calendar-choice="ics">Apple Calendar, Outlook or other (.ics)</a></div></details>
				<p class="hks-webinar-help">Your calendar event includes the joining link.</p><p class="hks-webinar-joining"><strong>Joining details</strong><br><a href="<?php echo esc_url( Event::details()['joining_url'] ); ?>"><?php echo esc_html( Event::details()['joining_url'] ); ?></a></p>
			<?php else : ?>
				<button class="hks-button" disabled>Add to calendar</button><p class="hks-webinar-help">[Joining URL] · Add the confirmed date, time and joining link to enable calendar invitations.</p>
			<?php endif; ?>
			</section>
			<?php $state = $id ? get_post_meta( $id, '_hks_webinar_email_state', true ) : ''; ?>
			<p class="hks-webinar-mail-note"><?php echo esc_html( 'accepted' === $state ? 'Your presentation details have been passed to our email service. Check your inbox and spam folder.' : ( 'queued' === $state ? 'Your confirmation email is queued. You can add the presentation to your calendar now.' : ( $preview ? 'The confirmation email will include the same presentation details and calendar links.' : 'Your registration is saved, but we couldn’t send the confirmation email. Use the calendar and joining details above, or contact info@holidaykenyasafaris.ke.' ) ) ); ?></p>
		<?php endif; ?>
		</main><?php echo self::footer(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
