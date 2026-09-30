<?php
/** Dedicated presentation flow, scoped to two normal draft WordPress Pages. */
namespace HolidayKenyaSafaris\Core\Webinar;

use HolidayKenyaSafaris\Core\Contracts\Module as ModuleContract;

defined( 'ABSPATH' ) || exit;

final class Module implements ModuleContract {
	public function register() {
		$repository = new Registration();
		$admin = new Admin();
		add_action( 'init', array( Registration::class, 'register_type' ) );
		add_action( 'rest_api_init', array( $repository, 'register_routes' ) );
		add_action( Notification::HOOK, array( Notification::class, 'send' ) );
		add_action( 'acf/include_fields', array( $this, 'fields' ) );
		add_action( 'admin_init', array( $this, 'ensure_pages' ) );
		add_action( 'template_redirect', array( $this, 'private_response' ), -1 );
		add_action( 'admin_post_hks_webinar_calendar', array( Calendar::class, 'download' ) );
		add_action( 'admin_post_nopriv_hks_webinar_calendar', array( Calendar::class, 'download' ) );
		add_filter( 'wp_robots', array( $this, 'robots' ) );
		add_filter( 'document_title_parts', array( $this, 'document_title' ) );
		add_filter( 'rest_post_dispatch', array( $this, 'private_api_response' ), 10, 3 );
		$admin->register();
	}

	/** Code deployment creates drafts once; it never publishes an incomplete event. */
	public function ensure_pages(): void {
		if ( ! current_user_can( 'manage_options' ) || get_option( 'hks_webinar_pages_seeded' ) ) {
			return;
		}
		$parent = get_page_by_path( 'diani-christmas-presentation', OBJECT, 'page' );
		if ( $parent && 'registration' !== get_post_meta( $parent->ID, '_hks_webinar_page', true ) ) {
			return; // An independently authored route must never be overwritten.
		}
		if ( ! $parent ) {
			$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => Event::TITLE, 'post_name' => 'diani-christmas-presentation', 'post_content' => '', 'meta_input' => array( '_wp_page_template' => 'webinar-registration', '_hks_webinar_page' => 'registration' ) ), true );
			if ( is_wp_error( $id ) || ! $id ) {
				return;
			}
			$parent = get_post( $id );
		}
		$thanks = get_page_by_path( 'diani-christmas-presentation/thank-you', OBJECT, 'page' );
		if ( $thanks && 'thanks' !== get_post_meta( $thanks->ID, '_hks_webinar_page', true ) ) {
			return;
		}
		if ( ! $thanks ) {
			$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'You’re registered. Add it to your calendar.', 'post_name' => 'thank-you', 'post_parent' => $parent->ID, 'post_content' => '', 'meta_input' => array( '_wp_page_template' => 'webinar-thanks', '_hks_webinar_page' => 'thanks' ) ), true );
			if ( is_wp_error( $id ) || ! $id ) {
				return;
			}
		}
		update_option( 'hks_webinar_pages_seeded', 1, false );
	}

	public function private_response(): void {
		if ( is_singular( 'page' ) && 'thanks' === get_post_meta( get_queried_object_id(), '_hks_webinar_page', true ) ) {
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}
			nocache_headers();
		}
	}

	public function private_api_response( $response, $server, $request ) {
		if ( str_starts_with( $request->get_route(), '/hks/v1/webinar/' ) ) {
			$response = rest_ensure_response( $response );
			$response->header( 'Cache-Control', 'no-store, private' );
		}
		return $response;
	}

	public function robots( array $robots ): array {
		if ( is_singular( 'page' ) && get_post_meta( get_queried_object_id(), '_hks_webinar_page', true ) ) {
			$robots['noindex'] = true;
			unset( $robots['index'] );
		}
		return $robots;
	}

	public function document_title( array $parts ): array {
		if ( is_singular( 'page' ) && 'thanks' === get_post_meta( get_queried_object_id(), '_hks_webinar_page', true ) && ! Registration::receipt_id() && ! is_preview() ) {
			$parts['title'] = 'Reserve your presentation place';
		}
		return $parts;
	}

	/** Event details only: no second set of holiday facts or payment processing. */
	public function fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		$spec = array(
			'title' => array( 'Presentation title', 'text', array( 'default_value' => Event::TITLE ) ),
			'date' => array( 'Confirmed webinar date', 'date_picker', array( 'display_format' => 'j F Y', 'return_format' => 'Y-m-d', 'instructions' => '[Webinar date] — October presentation date, not the December holiday.' ) ),
			'time' => array( 'Start time EAT', 'time_picker', array( 'display_format' => 'g:i a', 'return_format' => 'H:i', 'instructions' => '[Start time EAT] — Africa/Nairobi. End time is calculated from the duration.' ) ),
			'duration' => array( 'Duration in minutes', 'number', array( 'default_value' => 55, 'min' => 15, 'max' => 180, 'step' => 1 ) ),
			'joining_url' => array( 'Joining URL', 'url', array( 'instructions' => '[Joining URL] — enter the actual HTTPS presentation link. Pages, confirmation email and calendars share this URL.' ) ),
			'presenter_name' => array( 'Presenter name', 'text', array( 'instructions' => '[Presenter name] — optional until confirmed. Blank uses the approved HKS/Ashford host line.' ) ),
			'presenter_image' => array( 'Presenter photograph', 'image', array( 'return_format' => 'id', 'preview_size' => 'thumbnail', 'instructions' => 'Optional approved photograph. It appears only with a presenter name.' ) ),
			'hero_image' => array( 'Diani Sea Resort photograph', 'image', array( 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Assign one approved real photograph of this resort. Any room shown must be the quoted Comfort category.' ) ),
		);
		$fields = array( array( 'key' => 'field_hks_webinar_help', 'type' => 'message', 'label' => 'Before opening registration', 'message' => 'The two presentation Pages are created as drafts. Fill in the confirmed date, time and joining URL, assign the approved image, check the existing published privacy policy, then publish both Pages. Registration and calendar downloads remain closed while details are incomplete.' ) );
		foreach ( $spec as $name => [ $label, $type, $extra ] ) {
			$fields[] = array_merge( array( 'key' => 'field_hks_webinar_' . $name, 'name' => 'hks_webinar_' . $name, 'label' => $label, 'type' => $type, 'required' => 0 ), $extra );
		}
		acf_add_local_field_group( array( 'key' => 'group_hks_webinar_event', 'title' => 'Webinar presentation', 'fields' => $fields, 'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'hks-settings' ) ) ), 'menu_order' => 15, 'show_in_rest' => false, 'active' => true ) );
	}
}
