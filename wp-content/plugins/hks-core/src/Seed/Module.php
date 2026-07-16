<?php
/**
 * WordPress admin entry point for explicit draft imports.
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
		add_action( 'admin_post_hks_seed_site_pages', array( $this, 'handle_site_pages_import' ) );
		add_action( 'admin_post_hks_seed_catalogue', array( $this, 'handle_catalogue_import' ) );
	}

	/**
	 * Register the Tours submenu page.
	 *
	 * @return void
	 */
	public function register_page() {
		add_submenu_page(
			'edit.php?post_type=hks_tour',
			__( 'HKS draft importer', 'hks-core' ),
			__( 'Import site drafts', 'hks-core' ),
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
			wp_die( esc_html__( 'You are not allowed to import site drafts.', 'hks-core' ) );
		}

		$result_key = 'hks_mvp_seed_' . get_current_user_id();
		$result     = get_transient( $result_key );

		if ( false !== $result ) {
			delete_transient( $result_key );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Holiday Kenya Safaris draft importer', 'hks-core' ); ?></h1>
			<p><?php esc_html_e( 'Each action creates or refreshes only draft records from a version-controlled seed file.', 'hks-core' ); ?></p>
			<p><?php esc_html_e( 'The importer never publishes records, imports photographs, converts USD rates, or replaces records that are no longer drafts.', 'hks-core' ); ?></p>

			<?php if ( is_array( $result ) ) : ?>
				<div class="notice <?php echo empty( $result['errors'] ) ? 'notice-success' : 'notice-warning'; ?> is-dismissible">
					<?php if ( ! empty( $result['label'] ) ) : ?>
						<p><strong><?php echo esc_html( $result['label'] ); ?></strong></p>
					<?php endif; ?>
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

			<hr>
			<h2><?php esc_html_e( 'Phase 6: Standard site pages', 'hks-core' ); ?></h2>
			<p><?php esc_html_e( 'Creates editor-ready drafts for About, Group Travel, Contact, Privacy Policy, Website Terms, Booking Terms, and Cancellation and Refund Policy. Legal and contact drafts must remain unpublished until their final details are supplied.', 'hks-core' ); ?></p>
			<?php $this->render_import_form( 'hks_seed_site_pages', __( 'Create or refresh site page drafts', 'hks-core' ), 'primary' ); ?>

			<hr>
			<h2><?php esc_html_e( 'Phase 7: Ashford catalogue migration', 'hks-core' ); ?></h2>
			<p><?php esc_html_e( 'Imports the 40 remaining eligible local catalogue records in controlled draft batches. Tours remain price-free, and no media is assigned automatically.', 'hks-core' ); ?></p>
			<?php foreach ( $this->catalogue_batches() as $batch => $details ) : ?>
				<?php
				$this->render_import_form(
					'hks_seed_catalogue',
					sprintf(
						/* translators: 1: batch number, 2: batch label, 3: Tour count. */
						__( 'Import batch %1$d: %2$s (%3$d drafts)', 'hks-core' ),
						$batch,
						$details['label'],
						$details['count']
					),
					'secondary',
					array( 'batch' => $batch )
				);
				?>
			<?php endforeach; ?>

			<details>
				<summary><?php esc_html_e( 'Previously imported MVP records', 'hks-core' ); ?></summary>
				<p><?php esc_html_e( 'Use this only when you intentionally want to refresh the original three Tour drafts and three linked Campaign drafts.', 'hks-core' ); ?></p>
				<?php $this->render_import_form( 'hks_seed_mvp', __( 'Create or refresh MVP drafts', 'hks-core' ), 'secondary' ); ?>
			</details>
		</div>
		<?php
	}

	/**
	 * Render one nonce-protected importer form.
	 *
	 * @param string               $action WordPress admin-post action.
	 * @param string               $label  Button label.
	 * @param string               $type   WordPress button type.
	 * @param array<string, scalar> $extra  Additional hidden values.
	 * @return void
	 */
	private function render_import_form( $action, $label, $type, $extra = array() ) {
		?>
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" style="margin-bottom:1rem">
			<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
			<?php foreach ( $extra as $name => $value ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>">
			<?php endforeach; ?>
			<?php wp_nonce_field( $action ); ?>
			<?php submit_button( $label, $type, 'submit', false ); ?>
		</form>
		<?php
	}

	/**
	 * Read catalogue batch labels and counts for the operator UI.
	 *
	 * @return array<int, array{label: string, count: int}>
	 */
	private function catalogue_batches() {
		$file = HKS_CORE_PATH . 'data/catalogue-seed.json';

		if ( ! is_readable( $file ) ) {
			return array();
		}

		$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( ! is_array( $data ) || empty( $data['tours'] ) || ! is_array( $data['tours'] ) ) {
			return array();
		}

		$batches = array();

		foreach ( $data['tours'] as $tour ) {
			$batch = (int) ( $tour['batch'] ?? 0 );

			if ( $batch < 1 ) {
				continue;
			}

			if ( ! isset( $batches[ $batch ] ) ) {
				$batches[ $batch ] = array(
					'label' => sanitize_text_field( $data['batch_labels'][ (string) $batch ] ?? sprintf( __( 'Batch %d', 'hks-core' ), $batch ) ),
					'count' => 0,
				);
			}

			++$batches[ $batch ]['count'];
		}

		ksort( $batches );

		return $batches;
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

		$this->run_import( new MvpSeeder(), __( 'Original MVP drafts', 'hks-core' ) );
	}

	/**
	 * Import Phase 6 standard Page drafts.
	 *
	 * @return void
	 */
	public function handle_site_pages_import() {
		$this->authorize( 'hks_seed_site_pages' );
		$this->run_import(
			new MvpSeeder( HKS_CORE_PATH . 'data/site-pages-seed.json' ),
			__( 'Phase 6 site page drafts', 'hks-core' )
		);
	}

	/**
	 * Import one allowlisted Phase 7 catalogue batch.
	 *
	 * @return void
	 */
	public function handle_catalogue_import() {
		$this->authorize( 'hks_seed_catalogue' );

		$batch   = isset( $_POST['batch'] ) ? absint( wp_unslash( $_POST['batch'] ) ) : 0;
		$batches = $this->catalogue_batches();

		if ( ! isset( $batches[ $batch ] ) ) {
			wp_die( esc_html__( 'Select a valid catalogue batch.', 'hks-core' ) );
		}

		$this->run_import(
			new MvpSeeder( HKS_CORE_PATH . 'data/catalogue-seed.json', $batch ),
			sprintf(
				/* translators: 1: batch number, 2: batch label. */
				__( 'Phase 7 batch %1$d: %2$s', 'hks-core' ),
				$batch,
				$batches[ $batch ]['label']
			)
		);
	}

	/**
	 * Validate an importer request.
	 *
	 * @param string $action Nonce action.
	 * @return void
	 */
	private function authorize( $action ) {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You are not allowed to import site drafts.', 'hks-core' ) );
		}

		check_admin_referer( $action );
	}

	/**
	 * Run a configured draft seeder and return to the importer screen.
	 *
	 * @param MvpSeeder $seeder Configured seeder.
	 * @param string    $label  Result label.
	 * @return void
	 */
	private function run_import( MvpSeeder $seeder, $label ) {
		$result          = $seeder->run();
		$result['label'] = $label;

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
