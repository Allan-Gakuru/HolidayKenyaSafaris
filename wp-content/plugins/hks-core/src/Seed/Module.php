<?php
/**
 * WordPress admin entry point for the explicit MVP seed import.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Seed;

use HolidayKenyaSafaris\Core\Contracts\Module as ModuleContract;

defined( 'ABSPATH' ) || exit;

/**
 * Adds a guarded, operator-triggered draft importer under Tours.
 */
final class Module implements ModuleContract {

	/**
	 * Admin page slug.
	 */
	private const PAGE_SLUG = 'hks-mvp-seed';

	/**
	 * Required capability.
	 */
	private const CAPABILITY = 'manage_options';

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'register_page' ) );
		add_action( 'admin_post_hks_seed_mvp', array( $this, 'handle_import' ) );
	}

	/**
	 * Register the Tours submenu page.
	 *
	 * @return void
	 */
	public function register_page() {
		add_submenu_page(
			'edit.php?post_type=hks_tour',
			__( 'MVP draft importer', 'hks-core' ),
			__( 'Import MVP drafts', 'hks-core' ),
			self::CAPABILITY,
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render importer explanation, results, and explicit action.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to import the MVP drafts.', 'hks-core' ) );
		}

		$result_key = 'hks_mvp_seed_' . get_current_user_id();
		$result     = get_transient( $result_key );

		if ( false !== $result ) {
			delete_transient( $result_key );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Holiday Kenya Safaris MVP drafts', 'hks-core' ); ?></h1>
			<p><?php esc_html_e( 'This creates or refreshes three Tour drafts and three linked Campaign drafts from the version-controlled seed file.', 'hks-core' ); ?></p>
			<p><?php esc_html_e( 'It never publishes records, imports photographs, converts USD rates, or creates unconfirmed policies. Existing records that are no longer drafts are protected.', 'hks-core' ); ?></p>

			<?php if ( is_array( $result ) ) : ?>
				<div class="notice <?php echo empty( $result['errors'] ) ? 'notice-success' : 'notice-warning'; ?> is-dismissible">
					<p>
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: created, 2: updated, 3: protected. */
								__( 'Created: %1$d. Updated: %2$d. Protected: %3$d.', 'hks-core' ),
								(int) $result['created'],
								(int) $result['updated'],
								(int) $result['protected']
							)
						);
						?>
					</p>
					<?php if ( ! empty( $result['errors'] ) ) : ?>
						<ul>
							<?php foreach ( $result['errors'] as $error ) : ?>
								<li><?php echo esc_html( $error ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="hks_seed_mvp">
				<?php wp_nonce_field( 'hks_seed_mvp' ); ?>
				<?php submit_button( __( 'Create or refresh MVP drafts', 'hks-core' ), 'primary' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Validate the admin request and run the importer.
	 *
	 * @return void
	 */
	public function handle_import() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to import the MVP drafts.', 'hks-core' ) );
		}

		check_admin_referer( 'hks_seed_mvp' );

		$result = ( new MvpSeeder() )->run();
		set_transient( 'hks_mvp_seed_' . get_current_user_id(), $result, 5 * MINUTE_IN_SECONDS );

		wp_safe_redirect(
			add_query_arg(
				array(
					'post_type' => 'hks_tour',
					'page'      => self::PAGE_SLUG,
				),
				admin_url( 'edit.php' )
			)
		);
		exit;
	}
}
