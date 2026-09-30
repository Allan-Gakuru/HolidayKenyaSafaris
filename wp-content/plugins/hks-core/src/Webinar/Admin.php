<?php
/** Administrator-only registration list, details, CSV export and mail retry. */
namespace HolidayKenyaSafaris\Core\Webinar;

defined( 'ABSPATH' ) || exit;

final class Admin {
	public function register(): void {
		add_filter( 'use_block_editor_for_post_type', static fn( $use, $type ) => Registration::POST_TYPE === $type ? false : $use, 10, 2 );
		add_filter( 'manage_' . Registration::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . Registration::POST_TYPE . '_posts_custom_column', array( $this, 'column' ), 10, 2 );
		add_action( 'add_meta_boxes_' . Registration::POST_TYPE, array( $this, 'meta_box' ) );
		add_action( 'restrict_manage_posts', array( $this, 'export_link' ) );
		add_action( 'admin_post_hks_webinar_export', array( $this, 'export' ) );
		add_action( 'admin_post_hks_webinar_retry_mail', array( $this, 'retry' ) );
		add_filter( 'post_row_actions', array( $this, 'row_actions' ), 10, 2 );
		add_action( 'pre_get_posts', array( $this, 'search' ) );
	}

	public function columns( array $columns ): array {
		return array( 'cb' => $columns['cb'] ?? '', 'title' => 'Reference', 'hks_name' => 'Name', 'hks_email' => 'Email', 'hks_band' => 'Booking-payment answer', 'hks_mail' => 'Confirmation email', 'date' => 'Registered' );
	}

	public function column( string $column, int $id ): void {
		$field = array( 'hks_name' => 'name', 'hks_email' => 'email', 'hks_band' => 'payment_band', 'hks_mail' => 'email_state' )[ $column ] ?? '';
		$value = $field ? get_post_meta( $id, '_hks_webinar_' . $field, true ) : '';
		if ( 'payment_band' === $field ) {
			$value = Event::BANDS[ $value ] ?? '';
		}
		echo esc_html( 'email_state' === $field ? self::mail_label( $value ) : (string) $value );
	}

	public static function mail_label( string $state ): string {
		return array( 'queued' => 'Queued', 'accepted' => 'Accepted by mailer', 'failed' => 'Failed — retry available' )[ $state ] ?? 'Not queued';
	}

	public function meta_box(): void {
		add_meta_box( 'hks-webinar-registration', 'Registration details', array( $this, 'details' ), Registration::POST_TYPE, 'normal', 'high' );
	}

	public function details( \WP_Post $post ): void {
		echo '<table class="widefat striped"><tbody>';
		foreach ( array( 'name' => 'Name', 'email' => 'Email', 'payment_band' => 'Booking-payment answer', 'priority' => 'Family priority', 'event' => 'Presentation', 'registered_at' => 'Registered (UTC)', 'email_state' => 'Confirmation email', 'email_accepted_at' => 'Mailer accepted (UTC)' ) as $field => $label ) {
			$value = (string) get_post_meta( $post->ID, '_hks_webinar_' . $field, true );
			$value = 'payment_band' === $field ? ( Event::BANDS[ $value ] ?? '' ) : $value;
			$value = 'email_state' === $field ? self::mail_label( $value ) : $value;
			echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . nl2br( esc_html( $value ) ) . '</td></tr>';
		}
		echo '</tbody></table><p>These details are private. “Accepted by mailer” means WordPress passed the message to FluentSMTP; check FluentSMTP logs for delivery. These attendees have not been enrolled in unrelated marketing.</p>';
		$url = wp_nonce_url( add_query_arg( array( 'action' => 'hks_webinar_retry_mail', 'registration' => $post->ID ), admin_url( 'admin-post.php' ) ), 'hks_webinar_retry_' . $post->ID );
		echo '<p><a class="button" href="' . esc_url( $url ) . '">Retry confirmation email</a></p>';
	}

	public function row_actions( array $actions, \WP_Post $post ): array {
		if ( Registration::POST_TYPE === $post->post_type ) {
			unset( $actions['view'], $actions['inline hide-if-no-js'] );
		}
		return $actions;
	}

	public function search( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() || Registration::POST_TYPE !== $query->get( 'post_type' ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$term = $query->get( 's' );
		if ( is_string( $term ) && '' !== $term ) {
			$query->set( 's', '' );
			$query->set( 'meta_query', array( 'relation' => 'OR', array( 'key' => '_hks_webinar_name', 'value' => sanitize_text_field( $term ), 'compare' => 'LIKE' ), array( 'key' => '_hks_webinar_email', 'value' => sanitize_text_field( $term ), 'compare' => 'LIKE' ) ) );
		}
	}

	public function export_link( string $type ): void {
		if ( Registration::POST_TYPE !== $type || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$url = wp_nonce_url( add_query_arg( 'action', 'hks_webinar_export', admin_url( 'admin-post.php' ) ), 'hks_webinar_export' );
		echo '<a class="button" href="' . esc_url( $url ) . '">Download attendee CSV</a>';
	}

	/** Prevent spreadsheet formula execution, including leading whitespace. */
	public static function csv_cell( string $value ): string {
		return preg_match( '/^[\s]*[=+@-]/u', $value ) ? "'" . $value : $value;
	}

	public function export(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Access denied.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'hks_webinar_export' );
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="hks-webinar-attendees.csv"' );
		header( 'X-Content-Type-Options: nosniff' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" );
		fputcsv( $out, array( 'Name', 'Email', 'Booking-payment answer', 'Family priority', 'Presentation', 'Registered UTC', 'Confirmation email' ), ',', '"', '' );
		$page = 1;
		do {
			$ids = get_posts( array( 'post_type' => Registration::POST_TYPE, 'post_status' => 'private', 'posts_per_page' => 250, 'paged' => $page++, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC', 'no_found_rows' => true, 'meta_key' => '_hks_webinar_event', 'meta_value' => Event::KEY ) );
			foreach ( $ids as $id ) {
				$row = array();
				foreach ( array( 'name', 'email', 'payment_band', 'priority', 'event', 'registered_at', 'email_state' ) as $field ) {
					$value = (string) get_post_meta( $id, '_hks_webinar_' . $field, true );
					$row[] = self::csv_cell( 'payment_band' === $field ? ( Event::BANDS[ $value ] ?? '' ) : $value );
				}
				fputcsv( $out, $row, ',', '"', '' );
			}
		} while ( count( $ids ) === 250 );
		fclose( $out );
		exit;
	}

	public function retry(): void {
		$id = absint( $_GET['registration'] ?? 0 );
		if ( ! current_user_can( 'manage_options' ) || Registration::POST_TYPE !== get_post_type( $id ) ) {
			wp_die( 'Access denied.', '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'hks_webinar_retry_' . $id );
		delete_post_meta( $id, '_hks_webinar_email_attempts' );
		Notification::queue( $id );
		wp_safe_redirect( get_edit_post_link( $id, 'raw' ) );
		exit;
	}
}
