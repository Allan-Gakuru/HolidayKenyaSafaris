<?php
/**
 * Restricted inquiry administration.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Conversion;

use HolidayKenyaSafaris\Core\Content\PostTypes\Inquiry;

defined( 'ABSPATH' ) || exit;

/**
 * Gives administrators a read-only operational view of captured inquiries.
 */
final class InquiryAdmin {

	/**
	 * Register administration hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_block_editor' ), 10, 2 );
		add_action( 'add_meta_boxes_' . Inquiry::POST_TYPE, array( $this, 'add_meta_box' ) );
		add_filter( 'manage_' . Inquiry::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . Inquiry::POST_TYPE . '_posts_custom_column', array( $this, 'column_value' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'row_actions' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'privacy_notice' ) );
	}

	/**
	 * Use the classic read-only screen for inquiry records.
	 *
	 * @param bool   $use_block_editor Current choice.
	 * @param string $post_type        Post type.
	 * @return bool
	 */
	public function disable_block_editor( $use_block_editor, $post_type ) {
		return Inquiry::POST_TYPE === $post_type ? false : $use_block_editor;
	}

	/**
	 * Add the operational detail panel.
	 *
	 * @return void
	 */
	public function add_meta_box() {
		add_meta_box(
			'hks-inquiry-details',
			__( 'Quote request details', 'hks-core' ),
			array( $this, 'render_details' ),
			Inquiry::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render escaped private inquiry details.
	 *
	 * @param \WP_Post $post Inquiry post.
	 * @return void
	 */
	public function render_details( $post ) {
		$fields = array(
			__( 'Name', 'hks-core' )                     => $this->meta( $post->ID, 'name' ),
			__( 'Phone', 'hks-core' )                    => $this->meta( $post->ID, 'phone' ),
			__( 'Inquiry route', 'hks-core' )            => $this->route_label( $this->meta( $post->ID, 'route' ) ),
			__( 'Destination', 'hks-core' )              => $this->meta( $post->ID, 'destination' ),
			__( 'Package', 'hks-core' )                  => $this->meta( $post->ID, 'package_label' ),
			__( 'Preferred date or month', 'hks-core' )  => $this->meta( $post->ID, 'preferred_date' ),
			__( 'Travelers', 'hks-core' )                => $this->meta( $post->ID, 'travelers' ),
			__( 'Departure town', 'hks-core' )           => $this->meta( $post->ID, 'departure_town' ),
			__( 'Adults', 'hks-core' )                   => $this->meta( $post->ID, 'adults' ),
			__( 'Children', 'hks-core' )                 => $this->meta( $post->ID, 'children' ),
			__( 'Residency', 'hks-core' )                => $this->meta( $post->ID, 'residency' ),
			__( 'Vehicle preference', 'hks-core' )       => $this->meta( $post->ID, 'vehicle_preference' ),
			__( 'Accommodation preference', 'hks-core' ) => $this->meta( $post->ID, 'accommodation_preference' ),
			__( 'Budget range', 'hks-core' )             => $this->meta( $post->ID, 'budget_range' ),
			__( 'Consent recorded (UTC)', 'hks-core' )   => $this->meta( $post->ID, 'consent_at' ),
			__( 'WhatsApp opened (UTC)', 'hks-core' )    => $this->meta( $post->ID, 'whatsapp_opened_at', true ),
		);
		?>
		<table class="widefat striped" style="max-width: 900px">
			<tbody>
				<?php foreach ( $fields as $label => $value ) : ?>
					<?php if ( '' !== (string) $value ) : ?>
						<tr>
							<th scope="row" style="width: 230px"><?php echo esc_html( $label ); ?></th>
							<td><?php echo esc_html( $value ); ?></td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h3><?php esc_html_e( 'Attribution', 'hks-core' ); ?></h3>
		<?php $attribution = json_decode( $this->meta( $post->ID, 'attribution' ), true ); ?>
		<?php if ( is_array( $attribution ) && $attribution ) : ?>
			<ul>
				<?php foreach ( $attribution as $key => $value ) : ?>
					<li><strong><?php echo esc_html( $key ); ?>:</strong> <?php echo esc_html( $value ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p><?php esc_html_e( 'No campaign attribution was captured.', 'hks-core' ); ?></p>
		<?php endif; ?>

		<p><strong><?php esc_html_e( 'Status note:', 'hks-core' ); ?></strong> <?php esc_html_e( '“WhatsApp opened” records only that the website launched WhatsApp. It does not prove that the visitor sent the message.', 'hks-core' ); ?></p>
		<?php
	}

	/**
	 * Define a compact operational list table.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function columns( $columns ) {
		return array(
			'cb'             => $columns['cb'] ?? '<input type="checkbox">',
			'title'          => __( 'Reference', 'hks-core' ),
			'hks_name'       => __( 'Name', 'hks-core' ),
			'hks_phone'      => __( 'Phone', 'hks-core' ),
			'hks_package'    => __( 'Package', 'hks-core' ),
			'hks_travel'     => __( 'Travel plan', 'hks-core' ),
			'hks_whatsapp'   => __( 'WhatsApp', 'hks-core' ),
			'date'           => __( 'Captured', 'hks-core' ),
		);
	}

	/**
	 * Render one custom list-table cell.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Inquiry ID.
	 * @return void
	 */
	public function column_value( $column, $post_id ) {
		switch ( $column ) {
			case 'hks_name':
				echo esc_html( $this->meta( $post_id, 'name' ) );
				break;
			case 'hks_phone':
				echo esc_html( $this->meta( $post_id, 'phone' ) );
				break;
			case 'hks_package':
				echo esc_html( $this->meta( $post_id, 'package_label' ) );
				$destination = $this->meta( $post_id, 'destination' );
				if ( $destination ) {
					echo '<br><span class="description">' . esc_html( $destination ) . '</span>';
				}
				break;
			case 'hks_travel':
				echo esc_html( $this->meta( $post_id, 'preferred_date' ) );
				echo '<br><span class="description">';
				printf(
					/* translators: %s: traveler count. */
					esc_html__( '%s traveler(s)', 'hks-core' ),
					esc_html( $this->meta( $post_id, 'travelers' ) )
				);
				echo '</span>';
				break;
			case 'hks_whatsapp':
				$opened = $this->meta( $post_id, 'whatsapp_opened_at', true );
				echo $opened ? esc_html__( 'Opened', 'hks-core' ) : esc_html__( 'Not recorded', 'hks-core' );
				break;
		}
	}

	/**
	 * Remove irrelevant preview and quick-edit actions.
	 *
	 * @param array<string, string> $actions Row actions.
	 * @param \WP_Post             $post    Post.
	 * @return array<string, string>
	 */
	public function row_actions( $actions, $post ) {
		if ( Inquiry::POST_TYPE !== $post->post_type ) {
			return $actions;
		}

		unset( $actions['view'], $actions['inline hide-if-no-js'] );

		return $actions;
	}

	/**
	 * Remind administrators that launch retention wording is still a gate.
	 *
	 * @return void
	 */
	public function privacy_notice() {
		$screen = get_current_screen();

		if ( ! $screen || Inquiry::POST_TYPE !== $screen->post_type ) {
			return;
		}
		?>
		<div class="notice notice-warning"><p><?php esc_html_e( 'Quote inquiries contain personal data. Restrict administrator access and approve the privacy notice, retention period, deletion process, and consent behavior before production launch.', 'hks-core' ); ?></p></div>
		<?php
	}

	/**
	 * Read one protected inquiry value.
	 *
	 * @param int    $post_id Inquiry ID.
	 * @param string $name    Meta suffix.
	 * @param bool   $direct  Whether the supplied name is the full suffix.
	 * @return string
	 */
	private function meta( $post_id, $name, $direct = false ) {
		$key = '_hks_' . ( $direct ? $name : 'inquiry_' . $name );

		return (string) get_post_meta( $post_id, $key, true );
	}

	/**
	 * Return a readable source label for the inquiry route.
	 *
	 * @param string $route Stored route key.
	 * @return string
	 */
	private function route_label( $route ) {
		$labels = array(
			'group_travel' => __( 'Group Travel page', 'hks-core' ),
			'campaign'     => __( 'Campaign page', 'hks-core' ),
			'tour'         => __( 'Tour page', 'hks-core' ),
		);

		return $labels[ $route ] ?? '';
	}
}
