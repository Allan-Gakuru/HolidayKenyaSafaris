<?php
/**
 * Plugin activation, upgrade, and deactivation behavior.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Provides deliberately conservative plugin lifecycle hooks.
 */
final class Lifecycle {

	/**
	 * Option used to track the code version applied to a site or network.
	 *
	 * @var string
	 */
	private const VERSION_OPTION = 'hks_core_version';

	/**
	 * Local one-shot flag consumed after rewrite-providing objects register.
	 *
	 * @var string
	 */
	private const FLUSH_REWRITE_OPTION = 'hks_core_flush_rewrite_rules';

	/**
	 * Network generation used to schedule a lazy flush on every subsite.
	 *
	 * @var string
	 */
	private const REWRITE_GENERATION_OPTION = 'hks_core_rewrite_generation';

	/**
	 * Upgrade error raised during the current request, when present.
	 *
	 * @var \WP_Error|null
	 */
	private static $upgrade_error;

	/**
	 * Whether upgrade notices have been registered for this request.
	 *
	 * @var bool
	 */
	private static $upgrade_notice_registered = false;

	/**
	 * Validate the environment and apply the activated code version.
	 *
	 * @param bool $network_wide Whether the plugin is being network activated.
	 * @return void
	 */
	public static function activate( $network_wide = false ) {
		$network_wide = (bool) $network_wide;

		$network_dependency = self::validate_network_activation_dependency( $network_wide );

		if ( is_wp_error( $network_dependency ) ) {
			self::abort_activation( $network_dependency, $network_wide );
			return;
		}

		if ( ! Requirements::is_satisfied( true ) ) {
			self::abort_activation(
				new \WP_Error(
					'hks_core_requirements_not_met',
					Requirements::message( true )
				),
				$network_wide
			);
			return;
		}

		$result = self::maybe_upgrade( $network_wide );

		if ( is_wp_error( $result ) ) {
			self::abort_activation( $result, $network_wide );
			return;
		}

		$rewrite_result = self::schedule_activation_rewrite_flush( $network_wide );

		if ( is_wp_error( $rewrite_result ) ) {
			self::abort_activation( $rewrite_result, $network_wide );
			return;
		}

		/**
		 * Fires after HKS Core has passed activation checks and upgrades.
		 *
		 * @param bool $network_wide Whether the plugin was network activated.
		 */
		do_action( 'hks_core_activated', $network_wide );
	}

	/**
	 * Apply pending upgrades for the active scope or scopes.
	 *
	 * Activation passes its known scope. Normal boot leaves the argument null so
	 * the network-active state can be read from WordPress. A network-active plugin
	 * upgrades network state once, then the current site's state. Other sites are
	 * upgraded lazily on their first request; this method never loops over sites.
	 *
	 * @param bool|null $network_wide Whether the plugin is network active. Null detects it.
	 * @return true|\WP_Error True on success, otherwise a recoverable upgrade error.
	 */
	public static function maybe_upgrade( $network_wide = null ) {
		if ( null === $network_wide ) {
			$network_wide = self::is_network_active();
		}

		if ( is_multisite() && (bool) $network_wide ) {
			$result = self::upgrade_scope( 'network' );

			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		return self::upgrade_scope( 'site' );
	}

	/**
	 * Leave content and settings untouched when the plugin is deactivated.
	 *
	 * @param bool $network_wide Whether the plugin is being network deactivated.
	 * @return void
	 */
	public static function deactivate( $network_wide = false ) {
		/**
		 * Fires when HKS Core is deactivated.
		 *
		 * No options or future catalogue data are removed here.
		 *
		 * @param bool $network_wide Whether the plugin was network deactivated.
		 */
		do_action( 'hks_core_deactivated', (bool) $network_wide );
	}

	/**
	 * Display a persistent administrator notice after a boot-time upgrade error.
	 *
	 * @return void
	 */
	public static function render_upgrade_notice() {
		if (
			! self::$upgrade_error instanceof \WP_Error
			|| ! current_user_can( 'activate_plugins' )
		) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p><strong>%1$s</strong> %2$s</p></div>',
			esc_html__( 'Holiday Kenya Safaris Core is paused.', 'hks-core' ),
			esc_html( self::$upgrade_error->get_error_message() )
		);
	}

	/**
	 * Upgrade one storage scope to the running code version.
	 *
	 * Migration callbacks must be idempotent because WordPress cannot provide a
	 * transaction across arbitrary option, post, taxonomy, and rewrite changes.
	 * The stored version is advanced only after every applicable callback and the
	 * pre-commit hook complete successfully.
	 *
	 * @param string $scope Either "site" or "network".
	 * @return true|\WP_Error True on success, otherwise an upgrade error.
	 */
	private static function upgrade_scope( $scope ) {
		$stored_version = self::get_stored_version( $scope );

		if ( is_wp_error( $stored_version ) ) {
			return self::record_upgrade_failure( $stored_version, $scope );
		}

		if ( version_compare( $stored_version, HKS_CORE_VERSION, '>' ) ) {
			return self::record_upgrade_failure(
				new \WP_Error(
					'hks_core_code_downgrade',
					sprintf(
						/* translators: 1: Stored plugin version. 2: Running code version. */
						__( 'The stored HKS Core version is %1$s, but the deployed code is older (%2$s). Restore or deploy a matching newer plugin version; no data was changed.', 'hks-core' ),
						$stored_version,
						HKS_CORE_VERSION
					)
				),
				$scope
			);
		}

		if ( version_compare( $stored_version, HKS_CORE_VERSION, '=' ) ) {
			return true;
		}

		try {
			/**
			 * Fires before upgrade callbacks run for a site or network scope.
			 *
			 * Throwing an exception prevents the stored version from advancing.
			 *
			 * @param string $stored_version Existing stored version.
			 * @param string $target_version Running code version.
			 * @param string $scope          Either "site" or "network".
			 */
			do_action( 'hks_core_before_upgrade', $stored_version, HKS_CORE_VERSION, $scope );

			$migration_result = self::run_migrations( $stored_version, HKS_CORE_VERSION, $scope );

			if ( is_wp_error( $migration_result ) ) {
				return self::record_upgrade_failure( $migration_result, $scope );
			}

			/**
			 * Fires after migrations finish but before their version is committed.
			 *
			 * Use this hook only for required, idempotent upgrade work. Throwing an
			 * exception prevents the stored version from advancing.
			 *
			 * @param string $stored_version Existing stored version.
			 * @param string $target_version Running code version.
			 * @param string $scope          Either "site" or "network".
			 */
			do_action( 'hks_core_before_version_commit', $stored_version, HKS_CORE_VERSION, $scope );
		} catch ( \Throwable $throwable ) {
			return self::record_upgrade_failure(
				new \WP_Error(
					'hks_core_upgrade_exception',
					sprintf(
						/* translators: 1: Previous plugin version. 2: Target plugin version. 3: Error detail. */
						__( 'HKS Core could not upgrade from %1$s to %2$s. No version change was recorded. Detail: %3$s', 'hks-core' ),
						$stored_version,
						HKS_CORE_VERSION,
						$throwable->getMessage()
					)
				),
				$scope
			);
		}

		if ( ! self::set_stored_version( $scope, HKS_CORE_VERSION ) ) {
			return self::record_upgrade_failure(
				new \WP_Error(
					'hks_core_version_write_failed',
					sprintf(
						/* translators: %s: Target plugin version. */
						__( 'HKS Core completed its upgrade work but could not record version %s. The migrations are safe to retry because they must be idempotent.', 'hks-core' ),
						HKS_CORE_VERSION
					)
				),
				$scope
			);
		}

		/**
		 * Fires after a scope has upgraded and its version has been committed.
		 *
		 * This is a completion notification, not a migration work hook. Required
		 * upgrade work belongs in the registry or hks_core_before_version_commit.
		 *
		 * @param string $stored_version Previous stored version.
		 * @param string $target_version Newly stored version.
		 * @param string $scope          Either "site" or "network".
		 */
		do_action( 'hks_core_after_upgrade', $stored_version, HKS_CORE_VERSION, $scope );

		return true;
	}

	/**
	 * Run ordered migrations that fall between two plugin versions.
	 *
	 * Registry format:
	 *
	 *     '0.2.0' => array(
	 *         'all'     => array( callable ),
	 *         'site'    => array( callable ),
	 *         'network' => array( callable ),
	 *     ),
	 *
	 * A callback receives the previous version, its migration target, and scope.
	 * It may return null/true/another non-false value for success. Returning false,
	 * returning WP_Error, or throwing prevents the version from being committed.
	 *
	 * @param string $from_version Existing stored version.
	 * @param string $to_version   Running code version.
	 * @param string $scope        Either "site" or "network".
	 * @return true|\WP_Error True on success, otherwise a migration error.
	 */
	private static function run_migrations( $from_version, $to_version, $scope ) {
		/**
		 * Filters the HKS Core versioned migration registry.
		 *
		 * Migrations should normally be registered in code shipped with HKS Core.
		 * This filter exists for tightly coupled extensions and automated tests.
		 *
		 * @param array<string, array<string, callable|callable[]>> $migrations Versioned migrations.
		 */
		$migrations = apply_filters( 'hks_core_migrations', self::migration_registry() );

		if ( ! is_array( $migrations ) ) {
			return new \WP_Error(
				'hks_core_invalid_migration_registry',
				__( 'The HKS Core migration registry is invalid; no version change was recorded.', 'hks-core' )
			);
		}

		uksort( $migrations, 'version_compare' );

		foreach ( $migrations as $migration_version => $scoped_callbacks ) {
			if ( ! is_string( $migration_version ) || '' === trim( $migration_version ) ) {
				return new \WP_Error(
					'hks_core_invalid_migration_version',
					__( 'An HKS Core migration has an invalid target version; no version change was recorded.', 'hks-core' )
				);
			}

			if (
				version_compare( $migration_version, $from_version, '<=' )
				|| version_compare( $migration_version, $to_version, '>' )
			) {
				continue;
			}

			if ( ! is_array( $scoped_callbacks ) ) {
				return self::invalid_migration_error( $migration_version, $scope );
			}

			$callbacks = array();

			foreach ( array( 'all', $scope ) as $callback_scope ) {
				if ( ! array_key_exists( $callback_scope, $scoped_callbacks ) ) {
					continue;
				}

				$normalized = self::normalize_callbacks( $scoped_callbacks[ $callback_scope ] );

				if ( is_wp_error( $normalized ) ) {
					return self::invalid_migration_error( $migration_version, $scope );
				}

				$callbacks = array_merge( $callbacks, $normalized );
			}

			foreach ( $callbacks as $callback ) {
				try {
					$result = call_user_func( $callback, $from_version, $migration_version, $scope );
				} catch ( \Throwable $throwable ) {
					return new \WP_Error(
						'hks_core_migration_exception',
						sprintf(
							/* translators: 1: Migration target version. 2: Error detail. */
							__( 'The HKS Core migration for version %1$s failed. Detail: %2$s', 'hks-core' ),
							$migration_version,
							$throwable->getMessage()
						)
					);
				}

				if ( is_wp_error( $result ) ) {
					return $result;
				}

				if ( false === $result ) {
					return new \WP_Error(
						'hks_core_migration_failed',
						sprintf(
							/* translators: %s: Migration target version. */
							__( 'The HKS Core migration for version %s did not complete; no version change was recorded.', 'hks-core' ),
							$migration_version
						)
					);
				}
			}
		}

		return true;
	}

	/**
	 * Return migrations bundled with this version of HKS Core.
	 *
	 * Add future rewrite or data migrations here, keyed by their first code
	 * version. Keep each callback idempotent and explicitly scoped.
	 *
	 * @return array<string, array<string, callable|callable[]>>
	 */
	private static function migration_registry() {
		return array(
			'0.2.0' => array(
				'site' => array( self::class, 'schedule_rewrite_flush' ),
			),
		);
	}

	/**
	 * Schedule rewrite refreshes for an activation or reactivation.
	 *
	 * Every activation sets the current site's durable pending flag, even when the
	 * stored plugin version already matches the code version. Network activation
	 * also advances one shared generation; subsites observe it lazily rather than
	 * being enumerated during activation.
	 *
	 * @param bool $network_wide Whether network activation was requested.
	 * @return true|\WP_Error True when scheduling is durable, otherwise an error.
	 */
	private static function schedule_activation_rewrite_flush( $network_wide ) {
		if ( ! self::schedule_rewrite_flush( HKS_CORE_VERSION, HKS_CORE_VERSION, 'site' ) ) {
			return new \WP_Error(
				'hks_core_rewrite_schedule_failed',
				__( 'HKS Core could not schedule the required rewrite refresh for this site. Activation was stopped without deleting content.', 'hks-core' )
			);
		}

		if ( ! is_multisite() || ! $network_wide ) {
			return true;
		}

		$current_generation = get_site_option( self::REWRITE_GENERATION_OPTION, 0 );

		if ( false === $current_generation || null === $current_generation || '' === $current_generation ) {
			$current_generation = 0;
		}

		if (
			! is_int( $current_generation )
			&& ! (
				is_string( $current_generation )
				&& 1 === preg_match( '/^\d+$/', $current_generation )
			)
		) {
			return new \WP_Error(
				'hks_core_invalid_rewrite_generation',
				__( 'The stored HKS Core network rewrite generation is invalid. Correct that network option before retrying activation; no content was deleted.', 'hks-core' )
			);
		}

		$current_generation = (int) $current_generation;

		if ( $current_generation < 0 || PHP_INT_MAX === $current_generation ) {
			return new \WP_Error(
				'hks_core_invalid_rewrite_generation',
				__( 'The stored HKS Core network rewrite generation cannot be advanced. Correct that network option before retrying activation; no content was deleted.', 'hks-core' )
			);
		}

		$next_generation = $current_generation + 1;
		$updated         = update_site_option( self::REWRITE_GENERATION_OPTION, $next_generation );
		$stored          = get_site_option( self::REWRITE_GENERATION_OPTION, 0 );

		if ( ! $updated && $next_generation !== (int) $stored ) {
			return new \WP_Error(
				'hks_core_rewrite_generation_write_failed',
				__( 'HKS Core could not schedule rewrite refreshes across this network. Activation was stopped without deleting content.', 'hks-core' )
			);
		}

		return true;
	}

	/**
	 * Schedule one rewrite flush after the 0.2.0 content model registers.
	 *
	 * The Content module performs the flush on wp_loaded, after every post type and
	 * taxonomy exists. Re-running this idempotent migration only restores the flag.
	 *
	 * @param string $from_version      Existing stored version.
	 * @param string $migration_version Migration target version.
	 * @param string $scope             Current storage scope.
	 * @return bool Whether the pending flag is stored.
	 */
	public static function schedule_rewrite_flush( $from_version, $migration_version, $scope ) {
		unset( $from_version, $migration_version, $scope );

		update_option( self::FLUSH_REWRITE_OPTION, '1', false );

		return (bool) get_option( self::FLUSH_REWRITE_OPTION, false );
	}

	/**
	 * Normalize one registry scope into a list of callbacks.
	 *
	 * @param mixed $callbacks One callable or a list of callables.
	 * @return callable[]|\WP_Error
	 */
	private static function normalize_callbacks( $callbacks ) {
		if ( is_callable( $callbacks ) ) {
			return array( $callbacks );
		}

		if ( ! is_array( $callbacks ) ) {
			return new \WP_Error( 'hks_core_invalid_migration_callback' );
		}

		foreach ( $callbacks as $callback ) {
			if ( ! is_callable( $callback ) ) {
				return new \WP_Error( 'hks_core_invalid_migration_callback' );
			}
		}

		return array_values( $callbacks );
	}

	/**
	 * Build a readable error for malformed migration registry entries.
	 *
	 * @param string $migration_version Migration target version.
	 * @param string $scope             Current storage scope.
	 * @return \WP_Error
	 */
	private static function invalid_migration_error( $migration_version, $scope ) {
		return new \WP_Error(
			'hks_core_invalid_migration_callback',
			sprintf(
				/* translators: 1: Migration target version. 2: Migration scope. */
				__( 'The HKS Core migration for version %1$s has an invalid %2$s callback; no version change was recorded.', 'hks-core' ),
				$migration_version,
				$scope
			)
		);
	}

	/**
	 * Read a valid stored version for one scope.
	 *
	 * An absent value represents a fresh install at version 0.0.0.
	 *
	 * @param string $scope Either "site" or "network".
	 * @return string|\WP_Error
	 */
	private static function get_stored_version( $scope ) {
		$value = 'network' === $scope
			? get_site_option( self::VERSION_OPTION, '' )
			: get_option( self::VERSION_OPTION, '' );

		if ( false === $value || '' === $value || null === $value ) {
			return '0.0.0';
		}

		if ( ! is_string( $value ) || '' === trim( $value ) ) {
			return new \WP_Error(
				'hks_core_invalid_stored_version',
				__( 'The stored HKS Core version is invalid. Correct the version option before retrying; no data was changed.', 'hks-core' )
			);
		}

		return trim( $value );
	}

	/**
	 * Persist a completed version for one scope and verify the stored value.
	 *
	 * @param string $scope   Either "site" or "network".
	 * @param string $version Version that completed successfully.
	 * @return bool Whether the target version is now stored.
	 */
	private static function set_stored_version( $scope, $version ) {
		if ( 'network' === $scope ) {
			$updated = update_site_option( self::VERSION_OPTION, $version );
			$current = get_site_option( self::VERSION_OPTION, '' );
		} else {
			$updated = update_option( self::VERSION_OPTION, $version, false );
			$current = get_option( self::VERSION_OPTION, '' );
		}

		return (bool) $updated || $version === $current;
	}

	/**
	 * Record and expose an upgrade failure without deleting or changing data.
	 *
	 * @param \WP_Error $error Upgrade error.
	 * @param string    $scope Either "site" or "network".
	 * @return \WP_Error The same error for convenient propagation.
	 */
	private static function record_upgrade_failure( \WP_Error $error, $scope ) {
		self::$upgrade_error = $error;

		if ( is_admin() && ! self::$upgrade_notice_registered ) {
			add_action( 'admin_notices', array( self::class, 'render_upgrade_notice' ) );
			add_action( 'network_admin_notices', array( self::class, 'render_upgrade_notice' ) );

			self::$upgrade_notice_registered = true;
		}

		/**
		 * Fires when an HKS Core upgrade cannot complete.
		 *
		 * @param \WP_Error $error Upgrade error.
		 * @param string    $scope Either "site" or "network".
		 */
		do_action( 'hks_core_upgrade_failed', $error, $scope );

		return $error;
	}

	/**
	 * Require the official SCF plugin to be network active before HKS Core is.
	 *
	 * A dependency that is merely active on the current site cannot satisfy HKS
	 * Core on other subsites. Site-level HKS Core activation remains supported and
	 * continues to use the normal runtime requirements check.
	 *
	 * @param bool $network_wide Whether network activation was requested.
	 * @return true|\WP_Error True when valid or not applicable, otherwise an error.
	 */
	private static function validate_network_activation_dependency( $network_wide ) {
		if ( ! is_multisite() || ! $network_wide ) {
			return true;
		}

		$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
		$scf_network_active     = is_array( $network_active_plugins )
			&& array_key_exists( HKS_CORE_SCF_BASENAME, $network_active_plugins );

		if ( $scf_network_active ) {
			return true;
		}

		return new \WP_Error(
			'hks_core_scf_not_network_active',
			sprintf(
				/* translators: %s: Required plugin name. */
				__( 'Network activation of HKS Core requires %s to be network activated first. Site-level activation remains available.', 'hks-core' ),
				__( 'Secure Custom Fields', 'hks-core' )
			)
		);
	}

	/**
	 * Deactivate and stop a failed activation with a readable administrator error.
	 *
	 * @param \WP_Error $error        Activation error.
	 * @param bool      $network_wide Whether network activation was requested.
	 * @return void
	 */
	private static function abort_activation( \WP_Error $error, $network_wide ) {
		self::deactivate_after_failed_activation( $network_wide );

		wp_die(
			esc_html( $error->get_error_message() ),
			esc_html__( 'HKS Core activation failed', 'hks-core' ),
			array(
				'back_link' => true,
				'response'  => 500,
			)
		);
	}

	/**
	 * Determine whether this plugin is active for the current network.
	 *
	 * Reading the canonical network option avoids loading wp-admin plugin helpers
	 * on every public request.
	 *
	 * @return bool
	 */
	private static function is_network_active() {
		if ( ! is_multisite() ) {
			return false;
		}

		$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );

		return is_array( $network_active_plugins )
			&& array_key_exists( HKS_CORE_BASENAME, $network_active_plugins );
	}

	/**
	 * Deactivate the plugin after an activation-time failure.
	 *
	 * @param bool $network_wide Whether network activation was requested.
	 * @return void
	 */
	private static function deactivate_after_failed_activation( $network_wide ) {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		deactivate_plugins(
			HKS_CORE_BASENAME,
			true,
			is_multisite() && (bool) $network_wide
		);
	}
}
