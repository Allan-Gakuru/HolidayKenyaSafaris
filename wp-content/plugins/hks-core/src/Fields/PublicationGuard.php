<?php
/**
 * Publication validation entry points for Tours and Campaigns.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Fields;

use HolidayKenyaSafaris\Core\Content\PostTypes\Campaign;
use HolidayKenyaSafaris\Core\Content\PostTypes\Tour;
use HolidayKenyaSafaris\Core\Contracts\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps unsafe candidate content out of public and scheduled post statuses.
 */
final class PublicationGuard implements Module {

	/**
	 * Per-user transient prefix for redirect-safe downgrade notices.
	 *
	 * @var string
	 */
	private const NOTICE_TRANSIENT_PREFIX = 'hks_core_publication_notice_';

	/**
	 * Successfully validated REST candidates awaiting wp_insert_post_data.
	 *
	 * @var array<string, array{fields:array<string,mixed>,native:array<string,mixed>}>
	 */
	private $rest_candidates = array();

	/**
	 * Prevent nested insert-filter validation.
	 *
	 * @var bool
	 */
	private $guarding_insert = false;

	/**
	 * Prevent duplicate SCF validation in one callback stack.
	 *
	 * @var bool
	 */
	private $validating_scf = false;

	/**
	 * Prevent recursive Campaign updates while a Tour leaves a public status.
	 *
	 * @var bool
	 */
	private $cascading_campaigns = false;

	/**
	 * Cached deterministic SCF field-key to field-name map.
	 *
	 * @var array<string, string>|null
	 */
	private $field_key_map = null;

	/**
	 * Register all publication guard hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'acf/validate_save_post', array( $this, 'validate_scf_save' ), 20 );
		add_filter( 'rest_pre_insert_hks_tour', array( $this, 'validate_rest_tour' ), 20, 2 );
		add_filter( 'rest_pre_insert_hks_campaign', array( $this, 'validate_rest_campaign' ), 20, 2 );
		add_filter( 'wp_insert_post_data', array( $this, 'guard_insert' ), 99, 4 );
		add_action( 'transition_post_status', array( $this, 'protect_linked_campaigns' ), 20, 3 );
		add_action( 'before_delete_post', array( $this, 'protect_campaigns_before_tour_delete' ), 20, 2 );
		add_action( 'admin_notices', array( $this, 'display_downgrade_notice' ) );
	}

	/**
	 * Give SCF editors immediate, shared-rule feedback before a public save.
	 *
	 * Draft, pending, private, and auto-draft records remain saveable so missing
	 * facts can be completed in stages.
	 *
	 * @return void
	 */
	public function validate_scf_save() {
		if ( $this->validating_scf || ! function_exists( 'acf_add_validation_error' ) ) {
			return;
		}

		$post_id   = $this->post_id_from_globals();
		$post_type = $this->post_type_from_globals( $post_id );
		$status    = $this->post_status_from_globals( $post_id );

		if ( ! $this->is_supported_post_type( $post_type ) || ! $this->is_public_status( $status ) ) {
			return;
		}

		$this->validating_scf = true;

		$fields = $this->candidate_fields(
			$post_id,
			$post_type,
			$this->submitted_scf_from_globals()
		);
		$native = $this->native_from_globals( $post_id );
		$errors = PublicationRules::validate( $post_type, $fields, $post_id, $native );

		if ( ! empty( $errors ) ) {
			acf_add_validation_error( '', $this->error_summary( $errors ) );
		}

		$this->validating_scf = false;
	}

	/**
	 * Validate a Tour REST create or update before WordPress writes the post.
	 *
	 * @param mixed $prepared_post Prepared post object.
	 * @param mixed $request       REST request.
	 * @return mixed
	 */
	public function validate_rest_tour( $prepared_post, $request ) {
		return $this->validate_rest_request( Tour::POST_TYPE, $prepared_post, $request );
	}

	/**
	 * Validate a Campaign REST create or update before WordPress writes the post.
	 *
	 * @param mixed $prepared_post Prepared post object.
	 * @param mixed $request       REST request.
	 * @return mixed
	 */
	public function validate_rest_campaign( $prepared_post, $request ) {
		return $this->validate_rest_request( Campaign::POST_TYPE, $prepared_post, $request );
	}

	/**
	 * Fail closed for non-REST, programmatic, or validation-bypassing inserts.
	 *
	 * Unsafe publish and future requests are downgraded to Draft. Private records
	 * are not public and therefore are not changed by this guard.
	 *
	 * @param array<string, mixed> $data                Slashed, sanitized post data.
	 * @param array<string, mixed> $postarr             Original post array.
	 * @param array<string, mixed> $unsanitized_postarr Unslashed original post array.
	 * @param bool                 $update              Whether this is an update.
	 * @return array<string, mixed>
	 */
	public function guard_insert( $data, $postarr, $unsanitized_postarr, $update ) {
		unset( $update );

		if ( $this->guarding_insert || ! is_array( $data ) ) {
			return $data;
		}

		$post_type = isset( $data['post_type'] ) ? (string) $data['post_type'] : '';
		$status    = isset( $data['post_status'] ) ? (string) $data['post_status'] : '';

		if ( ! $this->is_supported_post_type( $post_type ) || ! $this->is_public_status( $status ) ) {
			return $data;
		}

		$post_id = $this->post_id_from_insert_arrays( $postarr, $unsanitized_postarr );
		$key     = $this->validation_key( $post_type, $post_id );

		$this->guarding_insert = true;

		if ( isset( $this->rest_candidates[ $key ] ) ) {
			$fields = $this->rest_candidates[ $key ]['fields'];
			unset( $this->rest_candidates[ $key ] );
		} else {
			$submitted = $this->submitted_scf_from_insert_arrays( $postarr, $unsanitized_postarr );
			$fields    = $this->candidate_fields( $post_id, $post_type, $submitted );
		}

		$native = $this->native_from_insert_data( $data, $post_id );
		$errors = PublicationRules::validate( $post_type, $fields, $post_id, $native );

		if ( ! empty( $errors ) ) {
			$data['post_status'] = 'draft';
			$this->store_downgrade_notice( $post_type, $errors );
		}

		$this->guarding_insert = false;

		return $data;
	}

	/**
	 * Return linked public Campaigns to Draft when their canonical Tour is hidden.
	 *
	 * @param string   $new_status New Tour status.
	 * @param string   $old_status Previous Tour status.
	 * @param \WP_Post $post       Updated post.
	 * @return void
	 */
	public function protect_linked_campaigns( $new_status, $old_status, $post ) {
		if (
			$this->cascading_campaigns
			|| ! is_object( $post )
			|| ! isset( $post->ID, $post->post_type )
			|| Tour::POST_TYPE !== $post->post_type
			|| ! $this->is_public_status( $old_status )
			|| 'publish' === $new_status
		) {
			return;
		}

		$this->draft_linked_campaigns( (int) $post->ID );
	}

	/**
	 * Protect Campaigns when a Tour is permanently deleted without being trashed.
	 *
	 * @param int      $post_id Post ID being deleted.
	 * @param \WP_Post $post    Post being deleted.
	 * @return void
	 */
	public function protect_campaigns_before_tour_delete( $post_id, $post ) {
		if (
			$this->cascading_campaigns
			|| ! is_object( $post )
			|| ! isset( $post->post_type )
			|| Tour::POST_TYPE !== $post->post_type
		) {
			return;
		}

		$this->draft_linked_campaigns( (int) $post_id );
	}

	/**
	 * Display and consume a redirect-safe publication downgrade notice.
	 *
	 * @return void
	 */
	public function display_downgrade_notice() {
		if ( ! function_exists( 'get_current_user_id' ) || ! function_exists( 'get_transient' ) ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( $user_id <= 0 ) {
			return;
		}

		$key    = self::NOTICE_TRANSIENT_PREFIX . $user_id;
		$notice = get_transient( $key );

		if ( false === $notice || ! is_array( $notice ) ) {
			return;
		}

		delete_transient( $key );

		$post_label = isset( $notice['post_label'] ) ? (string) $notice['post_label'] : __( 'Content', 'hks-core' );
		$errors     = isset( $notice['errors'] ) && is_array( $notice['errors'] ) ? $notice['errors'] : array();

		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<strong>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: Tour or Campaign. */
							__( '%s publication was blocked and the post was saved as a draft.', 'hks-core' ),
							$post_label
						)
					);
					?>
				</strong>
			</p>
			<?php if ( ! empty( $errors ) ) : ?>
				<ul>
					<?php foreach ( $errors as $error ) : ?>
						<?php if ( is_array( $error ) && isset( $error['message'] ) ) : ?>
							<li><?php echo esc_html( (string) $error['message'] ); ?></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Shared REST validation implementation.
	 *
	 * @param string $post_type     Supported post type.
	 * @param mixed  $prepared_post Prepared post object.
	 * @param mixed  $request       REST request.
	 * @return mixed
	 */
	private function validate_rest_request( $post_type, $prepared_post, $request ) {
		$post_id = $this->post_id_from_rest( $prepared_post, $request );
		$status  = $this->post_status_from_rest( $prepared_post, $request, $post_id );

		if ( ! $this->is_public_status( $status ) ) {
			return $prepared_post;
		}

		$raw_scf = null;
		if ( is_object( $request ) && method_exists( $request, 'get_param' ) ) {
			$raw_scf = $request->get_param( 'acf' );
		} elseif ( is_array( $request ) && array_key_exists( 'acf', $request ) ) {
			$raw_scf = $request['acf'];
		}

		$submitted = $this->normalize_scf_payload( $raw_scf );
		$fields    = $this->candidate_fields( $post_id, $post_type, $submitted );
		$native    = $this->native_from_rest( $prepared_post, $post_id );
		$errors    = PublicationRules::validate( $post_type, $fields, $post_id, $native );

		if ( ! empty( $errors ) ) {
			return new \WP_Error(
				'hks_publication_blocked',
				$this->error_summary( $errors ),
				array(
					'status'     => 400,
					'hks_errors' => $errors,
				)
			);
		}

		$this->rest_candidates[ $this->validation_key( $post_type, $post_id ) ] = array(
			'fields' => $fields,
			'native' => $native,
		);

		return $prepared_post;
	}

	/**
	 * Build a complete candidate field array from stored and submitted SCF data.
	 *
	 * Submitted top-level fields replace the stored version of that field. This is
	 * important for repeaters: removing a submitted row must not merge it back in.
	 *
	 * @param int                  $post_id   Current post ID.
	 * @param string               $post_type Supported post type.
	 * @param array<string, mixed> $submitted Normalized submitted fields.
	 * @return array<string, mixed>
	 */
	private function candidate_fields( $post_id, $post_type, $submitted ) {
		$stored = $this->stored_fields( $post_id, $post_type );

		foreach ( $submitted as $field_name => $value ) {
			$stored[ $field_name ] = $value;
		}

		return $stored;
	}

	/**
	 * Load unformatted stored SCF values, with individual-field fallbacks.
	 *
	 * @param int    $post_id   Current post ID.
	 * @param string $post_type Supported post type.
	 * @return array<string, mixed>
	 */
	private function stored_fields( $post_id, $post_type ) {
		if ( $post_id <= 0 ) {
			return array();
		}

		$values = array();
		if ( function_exists( 'get_fields' ) ) {
			$loaded = get_fields( $post_id, false );
			if ( is_array( $loaded ) ) {
				$values = $loaded;
			}
		}

		if ( function_exists( 'get_field' ) ) {
			foreach ( PublicationRules::field_names( $post_type ) as $field_name ) {
				if ( ! array_key_exists( $field_name, $values ) ) {
					$values[ $field_name ] = get_field( $field_name, $post_id, false );
				}
			}
		}

		return $values;
	}

	/**
	 * Read and normalize the current SCF form payload.
	 *
	 * @return array<string, mixed>
	 */
	private function submitted_scf_from_globals() {
		if ( ! isset( $_POST['acf'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- validation only; the save handler owns authorization.
			return array();
		}

		$raw = $_POST['acf']; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- read-only candidate validation.
		if ( function_exists( 'wp_unslash' ) ) {
			$raw = wp_unslash( $raw );
		}

		return $this->normalize_scf_payload( $raw );
	}

	/**
	 * Collect SCF or meta_input values passed directly to wp_insert_post().
	 *
	 * @param array<string, mixed> $postarr             Original post array.
	 * @param array<string, mixed> $unsanitized_postarr Unslashed original post array.
	 * @return array<string, mixed>
	 */
	private function submitted_scf_from_insert_arrays( $postarr, $unsanitized_postarr ) {
		$submitted = $this->submitted_scf_from_globals();
		$source    = is_array( $unsanitized_postarr ) ? $unsanitized_postarr : $postarr;

		if ( isset( $source['acf'] ) ) {
			$submitted = array_replace( $submitted, $this->normalize_scf_payload( $source['acf'] ) );
		}

		if ( isset( $source['meta_input'] ) && is_array( $source['meta_input'] ) ) {
			foreach ( $source['meta_input'] as $key => $value ) {
				if ( is_string( $key ) && 0 === strpos( $key, 'hks_' ) ) {
					$submitted[ $key ] = $value;
				}
			}
		}

		return $submitted;
	}

	/**
	 * Convert SCF field keys and nested repeater keys to their stored names.
	 *
	 * @param mixed $raw Raw SCF payload.
	 * @return array<string, mixed>
	 */
	private function normalize_scf_payload( $raw ) {
		$normalized = $this->normalize_scf_value( $raw );

		return is_array( $normalized ) ? $normalized : array();
	}

	/**
	 * Normalize one SCF value recursively.
	 *
	 * @param mixed $value Raw value.
	 * @return mixed
	 */
	private function normalize_scf_value( $value ) {
		if ( is_object( $value ) ) {
			$value = get_object_vars( $value );
		}

		if ( ! is_array( $value ) ) {
			return $value;
		}

		$map        = $this->field_key_map();
		$normalized = array();

		foreach ( $value as $key => $child ) {
			$normalized_key = $key;

			if ( is_string( $key ) && isset( $map[ $key ] ) ) {
				$normalized_key = $map[ $key ];
			} elseif ( is_string( $key ) && 0 === strpos( $key, 'field_' ) && function_exists( 'acf_get_field' ) ) {
				$field = acf_get_field( $key );
				if ( is_array( $field ) && ! empty( $field['name'] ) ) {
					$normalized_key = (string) $field['name'];
				}
			}

			$normalized[ $normalized_key ] = $this->normalize_scf_value( $child );
		}

		return $normalized;
	}

	/**
	 * Build the deterministic field-key map from the code-owned definitions.
	 *
	 * @return array<string, string>
	 */
	private function field_key_map() {
		if ( null !== $this->field_key_map ) {
			return $this->field_key_map;
		}

		$this->field_key_map = array();

		foreach ( FieldGroups::all() as $group ) {
			if ( isset( $group['fields'] ) && is_array( $group['fields'] ) ) {
				$this->collect_field_keys( $group['fields'] );
			}
		}

		return $this->field_key_map;
	}

	/**
	 * Add field and nested sub-field keys to the cached map.
	 *
	 * @param array<int, array<string, mixed>> $fields Field definitions.
	 * @return void
	 */
	private function collect_field_keys( $fields ) {
		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}

			if ( ! empty( $field['key'] ) && ! empty( $field['name'] ) ) {
				$this->field_key_map[ (string) $field['key'] ] = (string) $field['name'];
			}

			if ( isset( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ) {
				$this->collect_field_keys( $field['sub_fields'] );
			}
		}
	}

	/**
	 * Build native field candidates from the current admin request.
	 *
	 * @param int $post_id Current post ID.
	 * @return array<string, mixed>
	 */
	private function native_from_globals( $post_id ) {
		$native = $this->stored_native( $post_id );
		$keys   = array(
			'post_title'   => array( 'post_title' ),
			'post_excerpt' => array( 'post_excerpt', 'excerpt' ),
			'post_content' => array( 'post_content', 'content' ),
		);

		foreach ( $keys as $native_key => $request_keys ) {
			foreach ( $request_keys as $request_key ) {
				if ( isset( $_POST[ $request_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- validation only.
					$value = $_POST[ $request_key ]; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- read-only candidate validation.
					$native[ $native_key ] = function_exists( 'wp_unslash' ) ? wp_unslash( $value ) : $value;
					break;
				}
			}
		}

		return $native;
	}

	/**
	 * Build native field candidates from REST-prepared post data.
	 *
	 * @param mixed $prepared_post Prepared post object or array.
	 * @param int   $post_id       Current post ID.
	 * @return array<string, mixed>
	 */
	private function native_from_rest( $prepared_post, $post_id ) {
		$native = $this->stored_native( $post_id );

		foreach ( array( 'post_title', 'post_excerpt', 'post_content' ) as $field_name ) {
			if ( is_object( $prepared_post ) && property_exists( $prepared_post, $field_name ) ) {
				$native[ $field_name ] = $prepared_post->{$field_name};
			} elseif ( is_array( $prepared_post ) && array_key_exists( $field_name, $prepared_post ) ) {
				$native[ $field_name ] = $prepared_post[ $field_name ];
			}
		}

		return $native;
	}

	/**
	 * Build native field candidates from final insert data.
	 *
	 * @param array<string, mixed> $data    Final insert data.
	 * @param int                  $post_id Current post ID.
	 * @return array<string, mixed>
	 */
	private function native_from_insert_data( $data, $post_id ) {
		$native = $this->stored_native( $post_id );

		foreach ( array( 'post_title', 'post_excerpt', 'post_content' ) as $field_name ) {
			if ( array_key_exists( $field_name, $data ) ) {
				$value = $data[ $field_name ];
				$native[ $field_name ] = function_exists( 'wp_unslash' ) ? wp_unslash( $value ) : $value;
			}
		}

		return $native;
	}

	/**
	 * Load existing native post fields.
	 *
	 * @param int $post_id Current post ID.
	 * @return array<string, mixed>
	 */
	private function stored_native( $post_id ) {
		$native = array(
			'post_title'   => '',
			'post_excerpt' => '',
			'post_content' => '',
		);

		if ( $post_id <= 0 || ! function_exists( 'get_post' ) ) {
			return $native;
		}

		$post = get_post( $post_id );
		if ( is_object( $post ) ) {
			foreach ( array_keys( $native ) as $field_name ) {
				if ( isset( $post->{$field_name} ) ) {
					$native[ $field_name ] = $post->{$field_name};
				}
			}
		}

		return $native;
	}

	/**
	 * Resolve the current post ID from an SCF/admin request.
	 *
	 * @return int
	 */
	private function post_id_from_globals() {
		foreach ( array( 'post_ID', 'post_id', '_acf_post_id' ) as $key ) {
			if ( ! isset( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- validation context lookup only.
				continue;
			}

			$value = $_POST[ $key ]; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- cast below.
			if ( function_exists( 'wp_unslash' ) ) {
				$value = wp_unslash( $value );
			}

			if ( is_numeric( $value ) && (int) $value > 0 ) {
				return (int) $value;
			}

			if ( is_string( $value ) && preg_match( '/(?:^|_)(\d+)$/', $value, $matches ) ) {
				return (int) $matches[1];
			}
		}

		return 0;
	}

	/**
	 * Resolve the post type from the request or existing post.
	 *
	 * @param int $post_id Current post ID.
	 * @return string
	 */
	private function post_type_from_globals( $post_id ) {
		if ( isset( $_POST['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- validation context lookup only.
			$value = $_POST['post_type']; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- key normalization below.
			if ( function_exists( 'wp_unslash' ) ) {
				$value = wp_unslash( $value );
			}

			if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
				return '';
			}

			return function_exists( 'sanitize_key' ) ? sanitize_key( $value ) : (string) $value;
		}

		return $post_id > 0 && function_exists( 'get_post_type' ) ? (string) get_post_type( $post_id ) : '';
	}

	/**
	 * Resolve requested post status from the request, then the existing post.
	 *
	 * @param int $post_id Current post ID.
	 * @return string
	 */
	private function post_status_from_globals( $post_id ) {
		foreach ( array( 'post_status', 'status' ) as $key ) {
			if ( isset( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- validation context lookup only.
				$value = $_POST[ $key ]; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- key normalization below.
				if ( function_exists( 'wp_unslash' ) ) {
					$value = wp_unslash( $value );
				}

				if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
					return '';
				}

				return function_exists( 'sanitize_key' ) ? sanitize_key( $value ) : (string) $value;
			}
		}

		return $post_id > 0 && function_exists( 'get_post_status' ) ? (string) get_post_status( $post_id ) : '';
	}

	/**
	 * Resolve a post ID from REST context.
	 *
	 * @param mixed $prepared_post Prepared post object or array.
	 * @param mixed $request       REST request.
	 * @return int
	 */
	private function post_id_from_rest( $prepared_post, $request ) {
		if ( is_object( $request ) && method_exists( $request, 'get_param' ) ) {
			$id = $request->get_param( 'id' );
			if ( is_numeric( $id ) && (int) $id > 0 ) {
				return (int) $id;
			}
		} elseif ( is_array( $request ) && isset( $request['id'] ) && is_numeric( $request['id'] ) ) {
			return max( 0, (int) $request['id'] );
		}

		if ( is_object( $prepared_post ) && isset( $prepared_post->ID ) ) {
			return max( 0, (int) $prepared_post->ID );
		}

		if ( is_array( $prepared_post ) && isset( $prepared_post['ID'] ) ) {
			return max( 0, (int) $prepared_post['ID'] );
		}

		return 0;
	}

	/**
	 * Resolve the prepared or existing REST post status.
	 *
	 * @param mixed $prepared_post Prepared post object or array.
	 * @param mixed $request       REST request.
	 * @param int   $post_id       Current post ID.
	 * @return string
	 */
	private function post_status_from_rest( $prepared_post, $request, $post_id ) {
		if ( is_object( $prepared_post ) && isset( $prepared_post->post_status ) ) {
			return (string) $prepared_post->post_status;
		}

		if ( is_array( $prepared_post ) && isset( $prepared_post['post_status'] ) ) {
			return (string) $prepared_post['post_status'];
		}

		if ( is_object( $request ) && method_exists( $request, 'get_param' ) ) {
			$status = $request->get_param( 'status' );
			if ( is_string( $status ) && '' !== $status ) {
				return $status;
			}
		} elseif ( is_array( $request ) && isset( $request['status'] ) ) {
			return (string) $request['status'];
		}

		return $post_id > 0 && function_exists( 'get_post_status' ) ? (string) get_post_status( $post_id ) : '';
	}

	/**
	 * Resolve a post ID from wp_insert_post_data arguments.
	 *
	 * @param array<string, mixed> $postarr             Original post array.
	 * @param array<string, mixed> $unsanitized_postarr Unslashed original post array.
	 * @return int
	 */
	private function post_id_from_insert_arrays( $postarr, $unsanitized_postarr ) {
		foreach ( array( $unsanitized_postarr, $postarr ) as $source ) {
			if ( is_array( $source ) && isset( $source['ID'] ) && is_numeric( $source['ID'] ) ) {
				return max( 0, (int) $source['ID'] );
			}
		}

		return 0;
	}

	/**
	 * Draft every publicly exposed Campaign related to one Tour.
	 *
	 * Public Campaign templates inherit their factual content from the linked Tour,
	 * so they cannot remain public after that canonical record is hidden or removed.
	 *
	 * @param int $tour_id Canonical Tour ID.
	 * @return void
	 */
	private function draft_linked_campaigns( $tour_id ) {
		if (
			$tour_id <= 0
			|| ! function_exists( 'get_posts' )
			|| ! function_exists( 'wp_update_post' )
		) {
			return;
		}

		$campaign_ids = get_posts(
			array(
				'post_type'              => Campaign::POST_TYPE,
				'post_status'            => array( 'publish', 'future' ),
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'meta_key'               => 'hks_linked_tour',
				'meta_value'             => $tour_id,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'suppress_filters'       => true,
			)
		);

		if ( ! is_array( $campaign_ids ) || empty( $campaign_ids ) ) {
			return;
		}

		$this->cascading_campaigns = true;
		$drafted                    = 0;

		foreach ( $campaign_ids as $campaign_id ) {
			$result = wp_update_post(
				array(
					'ID'          => (int) $campaign_id,
					'post_status' => 'draft',
				),
				true
			);

			if ( ! is_wp_error( $result ) && (int) $result > 0 ) {
				++$drafted;
			}
		}

		$this->cascading_campaigns = false;

		if ( $drafted > 0 ) {
			$this->store_downgrade_notice(
				Campaign::POST_TYPE,
				array(
					array(
						'code'    => 'hks_campaign_linked_tour_hidden',
						'field'   => 'hks_linked_tour',
						'message' => sprintf(
							/* translators: %d: number of Campaigns returned to Draft. */
							_n(
								'%d linked Campaign was returned to Draft because its canonical Tour is no longer public.',
								'%d linked Campaigns were returned to Draft because their canonical Tour is no longer public.',
								$drafted,
								'hks-core'
							),
							$drafted
						),
					),
				)
			);
		}
	}

	/**
	 * Store a safe, per-user notice for the post-save redirect.
	 *
	 * @param string                                                      $post_type Supported post type.
	 * @param array<int, array{code:string,field:string,message:string}> $errors    Validation errors.
	 * @return void
	 */
	private function store_downgrade_notice( $post_type, $errors ) {
		if ( ! function_exists( 'get_current_user_id' ) || ! function_exists( 'set_transient' ) ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( $user_id <= 0 ) {
			return;
		}

		set_transient(
			self::NOTICE_TRANSIENT_PREFIX . $user_id,
			array(
				'post_label' => Tour::POST_TYPE === $post_type ? __( 'Tour', 'hks-core' ) : __( 'Campaign', 'hks-core' ),
				'errors'     => $errors,
			),
			120
		);
	}

	/**
	 * Convert structured errors to one SCF/REST-safe message.
	 *
	 * @param array<int, array{code:string,field:string,message:string}> $errors Validation errors.
	 * @return string
	 */
	private function error_summary( $errors ) {
		$messages = array();
		foreach ( $errors as $error ) {
			if ( isset( $error['message'] ) ) {
				$messages[] = (string) $error['message'];
			}
		}

		$messages = array_values( array_unique( $messages ) );

		return sprintf(
			/* translators: %s: one or more publication validation messages. */
			__( 'Publication blocked: %s', 'hks-core' ),
			implode( ' ', $messages )
		);
	}

	/**
	 * Build a stable in-request REST candidate key.
	 *
	 * @param string $post_type Post type.
	 * @param int    $post_id   Current post ID or zero.
	 * @return string
	 */
	private function validation_key( $post_type, $post_id ) {
		return $post_type . ':' . max( 0, (int) $post_id );
	}

	/**
	 * Whether the post type is governed here.
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	private function is_supported_post_type( $post_type ) {
		return in_array( $post_type, array( Tour::POST_TYPE, Campaign::POST_TYPE ), true );
	}

	/**
	 * Whether the requested status exposes or schedules public content.
	 *
	 * @param string $status Post status.
	 * @return bool
	 */
	private function is_public_status( $status ) {
		return in_array( $status, array( 'publish', 'future' ), true );
	}
}
