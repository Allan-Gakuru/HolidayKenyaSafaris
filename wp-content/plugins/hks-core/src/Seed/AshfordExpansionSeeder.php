<?php
/**
 * Client-authorized Ashford catalogue expansion importer.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Seed;

use HolidayKenyaSafaris\Core\Content\PostTypes\Tour;
use HolidayKenyaSafaris\Core\Fields\FieldGroups;

defined( 'ABSPATH' ) || exit;

/**
 * Applies audited prices to existing Tours and imports authorized international Tours.
 */
final class AshfordExpansionSeeder {

	/**
	 * Absolute seed file path.
	 *
	 * @var string
	 */
	private $seed_file;

	/**
	 * Zero updates existing Tours; a positive number imports one expansion batch.
	 *
	 * @var int
	 */
	private $batch;

	/**
	 * Create the importer.
	 *
	 * @param int    $batch     Import mode or international batch.
	 * @param string $seed_file Optional absolute seed file path.
	 */
	public function __construct( $batch = 0, $seed_file = '' ) {
		$this->batch     = max( 0, (int) $batch );
		$this->seed_file = $seed_file ? (string) $seed_file : HKS_CORE_PATH . 'data/ashford-expansion-seed.json';
	}

	/**
	 * Run the selected authorized migration.
	 *
	 * @return array<string, mixed>
	 */
	public function run() {
		$result = array(
			'created'   => 0,
			'updated'   => 0,
			'protected' => 0,
			'published' => 0,
			'drafted'   => 0,
			'media'     => 0,
			'errors'    => array(),
		);

		if ( ! function_exists( 'update_field' ) ) {
			$result['errors'][] = __( 'Secure Custom Fields is not available.', 'hks-core' );
			return $result;
		}

		$data = $this->load_data();

		if ( is_wp_error( $data ) ) {
			$result['errors'][] = $data->get_error_message();
			return $result;
		}

		if ( 0 === $this->batch ) {
			foreach ( $data['existing_tour_updates'] as $update ) {
				$outcome = $this->update_existing_tour( $update );

				if ( is_wp_error( $outcome ) ) {
					$result['errors'][] = $outcome->get_error_message();
				} else {
					++$result[ $outcome ];
				}
			}

			return $result;
		}

		foreach ( $data['new_tours'] as $tour ) {
			if ( $this->batch !== (int) ( $tour['batch'] ?? 0 ) ) {
				continue;
			}

			$record = $this->upsert_new_tour( $tour, $data['exchange_rate'] );

			if ( is_wp_error( $record ) ) {
				$result['errors'][] = $record->get_error_message();
				continue;
			}

			++$result[ $record['outcome'] ];

			if ( ! empty( $record['media'] ) ) {
				++$result['media'];
			}

			if ( ! empty( $record['published'] ) ) {
				++$result['published'];
			} elseif ( empty( $record['protected'] ) ) {
				++$result['drafted'];
			}
		}

		return $result;
	}

	/**
	 * Load and validate the authorization-bound seed.
	 *
	 * @return array<string, mixed>|\WP_Error
	 */
	private function load_data() {
		if ( ! is_readable( $this->seed_file ) ) {
			return new \WP_Error( 'hks_expansion_missing', __( 'The Ashford expansion seed is not readable.', 'hks-core' ) );
		}

		$data = json_decode( (string) file_get_contents( $this->seed_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( ! is_array( $data ) || JSON_ERROR_NONE !== json_last_error() ) {
			return new \WP_Error( 'hks_expansion_json', __( 'The Ashford expansion seed is not valid JSON.', 'hks-core' ) );
		}

		if (
			1 !== (int) ( $data['schema_version'] ?? 0 )
			|| 'client_authorized_publish' !== ( $data['publication_policy'] ?? '' )
			|| '2026-07-24' !== ( $data['authorization_date'] ?? '' )
		) {
			return new \WP_Error( 'hks_expansion_contract', __( 'The Ashford expansion authorization contract is incomplete or unsupported.', 'hks-core' ) );
		}

		$data['existing_tour_updates'] = is_array( $data['existing_tour_updates'] ?? null ) ? $data['existing_tour_updates'] : array();
		$data['new_tours']             = is_array( $data['new_tours'] ?? null ) ? $data['new_tours'] : array();

		return $data;
	}

	/**
	 * Add scope and an audited price to an existing Tour without replacing its content.
	 *
	 * @param array<string, mixed> $update Existing Tour update.
	 * @return string|\WP_Error
	 */
	private function update_existing_tour( $update ) {
		$slug = sanitize_title( $update['slug'] ?? '' );

		if ( '' === $slug ) {
			return new \WP_Error( 'hks_expansion_existing_slug', __( 'An existing Tour update has no slug.', 'hks-core' ) );
		}

		$post = get_page_by_path( $slug, OBJECT, Tour::POST_TYPE );

		if ( ! $post instanceof \WP_Post ) {
			return new \WP_Error(
				'hks_expansion_existing_missing',
				sprintf(
					/* translators: %s: Tour slug. */
					__( 'Existing Tour not found: %s', 'hks-core' ),
					$slug
				)
			);
		}

		$this->append_terms(
			(int) $post->ID,
			array( 'hks_tour_scope' => array( sanitize_text_field( $update['scope'] ?? 'Kenya Tours' ) ) )
		);

		if ( ! empty( $update['hks_from_price_ksh'] ) ) {
			$this->update_fields(
				(int) $post->ID,
				array( 'hks_from_price_ksh' => absint( $update['hks_from_price_ksh'] ) )
			);
			update_post_meta( (int) $post->ID, 'hks_price_conversion_audit', wp_json_encode( $update['conversion'] ?? array() ) );
		}

		update_post_meta( (int) $post->ID, 'hks_source_url', esc_url_raw( $update['source_url'] ?? '' ) );
		update_post_meta( (int) $post->ID, 'hks_scope_price_migrated_20260724', '1' );

		return 'updated';
	}

	/**
	 * Create or refresh one importer-owned international Tour, then publish only when complete.
	 *
	 * @param array<string, mixed> $tour          Tour record.
	 * @param array<string, mixed> $exchange_rate Exchange-rate evidence.
	 * @return array<string, mixed>|\WP_Error
	 */
	private function upsert_new_tour( $tour, $exchange_rate ) {
		$identity = sanitize_text_field( $tour['product_id'] ?? '' );
		$slug     = sanitize_title( $tour['slug'] ?? '' );

		if ( '' === $identity || '' === $slug || empty( $tour['title'] ) || empty( $tour['source']['url'] ) ) {
			return new \WP_Error( 'hks_expansion_tour', __( 'An expansion Tour is missing its identity, slug, title, or source URL.', 'hks-core' ) );
		}

		$owned = get_posts(
			array(
				'post_type'        => Tour::POST_TYPE,
				'post_status'      => get_post_stati(),
				'numberposts'      => 2,
				'fields'           => 'ids',
				'meta_key'         => 'hks_ashford_expansion_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'       => $identity, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'suppress_filters' => true,
			)
		);

		if ( count( $owned ) > 1 ) {
			return new \WP_Error( 'hks_expansion_duplicate', sprintf( __( 'Duplicate expansion identity: %s', 'hks-core' ), $identity ) );
		}

		$existing = $owned ? get_post( (int) $owned[0] ) : get_page_by_path( $slug, OBJECT, Tour::POST_TYPE );

		if ( $existing instanceof \WP_Post && ! $owned ) {
			return array(
				'outcome'   => 'protected',
				'protected' => true,
				'media'     => false,
				'published' => false,
			);
		}

		if ( $existing instanceof \WP_Post && 'draft' !== $existing->post_status ) {
			return array(
				'outcome'   => 'protected',
				'protected' => true,
				'media'     => false,
				'published' => false,
			);
		}

		$postarr = array(
			'post_type'    => Tour::POST_TYPE,
			'post_status'  => 'draft',
			'post_title'   => sanitize_text_field( $tour['title'] ),
			'post_name'    => $slug,
			'post_excerpt' => sanitize_textarea_field( $tour['excerpt'] ?? '' ),
			'post_content' => '<!-- wp:paragraph --><p>' . esc_html( $tour['overview'] ?? '' ) . '</p><!-- /wp:paragraph -->',
		);

		if ( $existing instanceof \WP_Post ) {
			$postarr['ID'] = (int) $existing->ID;
			$post_id       = wp_update_post( wp_slash( $postarr ), true );
			$outcome       = 'updated';
		} else {
			$post_id = wp_insert_post( wp_slash( $postarr ), true );
			$outcome = 'created';
		}

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		$post_id = (int) $post_id;
		update_post_meta( $post_id, 'hks_ashford_expansion_id', $identity );
		update_post_meta( $post_id, 'hks_internal_product_id', $identity );
		update_post_meta( $post_id, 'hks_source_url', esc_url_raw( $tour['source']['url'] ) );
		update_post_meta( $post_id, 'hks_source_checked_date', sanitize_text_field( $tour['source']['checked_date'] ?? '' ) );
		update_post_meta( $post_id, 'hks_source_category', sanitize_text_field( $tour['source']['category'] ?? '' ) );
		update_post_meta( $post_id, 'hks_price_conversion_audit', wp_json_encode( $tour['conversion'] ?? array() ) );
		update_post_meta( $post_id, 'hks_exchange_rate_audit', wp_json_encode( $exchange_rate ) );

		$this->update_fields( $post_id, is_array( $tour['fields'] ?? null ) ? $tour['fields'] : array() );
		$this->append_terms( $post_id, is_array( $tour['taxonomies'] ?? null ) ? $tour['taxonomies'] : array() );

		$media_added = false;

		$featured_image = is_array( $tour['media'][0] ?? null ) ? $tour['media'][0] : array();

		if ( ! has_post_thumbnail( $post_id ) && ! empty( $featured_image['url'] ) ) {
			$attachment = $this->sideload_image( $post_id, $featured_image );

			if ( is_wp_error( $attachment ) ) {
				return new \WP_Error(
					'hks_expansion_media',
					sprintf(
						/* translators: 1: Tour title, 2: media error. */
						__( '%1$s remains a draft because its image could not be imported: %2$s', 'hks-core' ),
						sanitize_text_field( $tour['title'] ),
						$attachment->get_error_message()
					)
				);
			}

			$media_added = true;
		}

		$should_publish = 'publish' === ( $tour['publication_status'] ?? '' )
			&& ! empty( $tour['fields']['hks_from_price_ksh'] )
			&& has_post_thumbnail( $post_id );

		if ( $should_publish ) {
			$published = wp_update_post(
				array(
					'ID'          => $post_id,
					'post_status' => 'publish',
				),
				true
			);

			if ( is_wp_error( $published ) ) {
				return $published;
			}
		}

		return array(
			'outcome'   => $outcome,
			'protected' => false,
			'media'     => $media_added,
			'published' => $should_publish,
		);
	}

	/**
	 * Sideload and assign one featured image.
	 *
	 * @param int                  $post_id Post ID.
	 * @param array<string, mixed> $image   Image record.
	 * @return int|\WP_Error
	 */
	private function sideload_image( $post_id, $image ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = download_url( esc_url_raw( $image['url'] ), 30 );

		if ( is_wp_error( $tmp ) ) {
			return $tmp;
		}

		$path = (string) wp_parse_url( $image['url'], PHP_URL_PATH );
		$file = array(
			'name'     => sanitize_file_name( wp_basename( $path ) ?: sanitize_title( $image['alt'] ?? 'tour-image' ) . '.jpg' ),
			'tmp_name' => $tmp,
		);

		$attachment_id = media_handle_sideload( $file, $post_id, sanitize_text_field( $image['alt'] ?? '' ) );

		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.unlink_unlink
			return $attachment_id;
		}

		update_post_meta( (int) $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $image['alt'] ?? '' ) );
		set_post_thumbnail( $post_id, (int) $attachment_id );

		return (int) $attachment_id;
	}

	/**
	 * Store Tour SCF values using their deterministic field keys.
	 *
	 * @param int                  $post_id Post ID.
	 * @param array<string, mixed> $values  Values by field name.
	 * @return void
	 */
	private function update_fields( $post_id, $values ) {
		$fields = array();

		foreach ( FieldGroups::all() as $group ) {
			foreach ( $group['location'] ?? array() as $rules ) {
				foreach ( $rules as $rule ) {
					if ( 'post_type' === ( $rule['param'] ?? '' ) && Tour::POST_TYPE === ( $rule['value'] ?? '' ) ) {
						foreach ( $group['fields'] as $field ) {
							$fields[ $field['name'] ] = $field;
						}
					}
				}
			}
		}

		foreach ( $values as $name => $value ) {
			if ( isset( $fields[ $name ] ) ) {
				update_field( $fields[ $name ]['key'], $this->normalize_value( $fields[ $name ], $value ), $post_id );
			}
		}
	}

	/**
	 * Replace repeater row names with SCF sub-field keys.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Value.
	 * @return mixed
	 */
	private function normalize_value( $field, $value ) {
		if ( empty( $field['sub_fields'] ) || ! is_array( $value ) ) {
			return $value;
		}

		$normalize_row = function ( $row ) use ( $field ) {
			$normalized = array();

			foreach ( $field['sub_fields'] as $sub_field ) {
				$name = $sub_field['name'] ?? '';

				if ( $name && is_array( $row ) && array_key_exists( $name, $row ) ) {
					$normalized[ $sub_field['key'] ] = $this->normalize_value( $sub_field, $row[ $name ] );
				}
			}

			return $normalized;
		};

		if ( 'repeater' === ( $field['type'] ?? '' ) ) {
			return array_map( $normalize_row, $value );
		}

		return $normalize_row( $value );
	}

	/**
	 * Append seed terms without deleting editor-assigned discovery terms.
	 *
	 * @param int                                      $post_id    Post ID.
	 * @param array<string, array<int, string>> $taxonomies Taxonomies and term names.
	 * @return void
	 */
	private function append_terms( $post_id, $taxonomies ) {
		foreach ( $taxonomies as $taxonomy => $names ) {
			if ( ! taxonomy_exists( $taxonomy ) || ! is_array( $names ) ) {
				continue;
			}

			$term_ids = array();

			foreach ( $names as $name ) {
				$name = sanitize_text_field( $name );
				$term = term_exists( $name, $taxonomy );

				if ( ! $term ) {
					$term = wp_insert_term( $name, $taxonomy );
				}

				if ( ! is_wp_error( $term ) ) {
					$term_ids[] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
				}
			}

			if ( $term_ids ) {
				wp_set_object_terms( $post_id, $term_ids, $taxonomy, true );
			}
		}
	}
}
