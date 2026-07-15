<?php
/**
 * Idempotent importer for the three MVP Tour and Campaign drafts.
 *
 * @package HolidayKenyaSafaris\Core
 */

namespace HolidayKenyaSafaris\Core\Seed;

use HolidayKenyaSafaris\Core\Content\PostTypes\Campaign;
use HolidayKenyaSafaris\Core\Content\PostTypes\Tour;
use HolidayKenyaSafaris\Core\Fields\FieldGroups;

defined( 'ABSPATH' ) || exit;

/**
 * Creates source-governed MVP records without publishing or replacing reviewed work.
 */
final class MvpSeeder {

	/**
	 * Absolute seed file path.
	 *
	 * @var string
	 */
	private $seed_file;

	/**
	 * Create a seeder.
	 *
	 * @param string $seed_file Optional absolute JSON path.
	 */
	public function __construct( $seed_file = '' ) {
		$this->seed_file = $seed_file ? (string) $seed_file : HKS_CORE_PATH . 'data/mvp-seed.json';
	}

	/**
	 * Import or refresh draft-only MVP records.
	 *
	 * Existing records that are no longer drafts are protected from replacement.
	 *
	 * @return array<string, mixed>
	 */
	public function run() {
		$result = array(
			'created'   => 0,
			'updated'   => 0,
			'protected' => 0,
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

		$tour_ids = array();

		foreach ( $data['tours'] as $tour ) {
			$record = $this->upsert_tour( $tour );

			if ( is_wp_error( $record ) ) {
				$result['errors'][] = $record->get_error_message();
				continue;
			}

			$tour_ids[ $tour['product_id'] ] = $record['post_id'];
			++$result[ $record['outcome'] ];
		}

		foreach ( $data['campaigns'] as $campaign ) {
			$product_id = $campaign['tour_product_id'];

			if ( empty( $tour_ids[ $product_id ] ) ) {
				$result['errors'][] = sprintf(
					/* translators: %s: internal Tour product ID. */
					__( 'Campaign skipped because its Tour could not be imported: %s', 'hks-core' ),
					$product_id
				);
				continue;
			}

			$record = $this->upsert_campaign( $campaign, $tour_ids[ $product_id ] );

			if ( is_wp_error( $record ) ) {
				$result['errors'][] = $record->get_error_message();
				continue;
			}

			++$result[ $record['outcome'] ];
		}

		return $result;
	}

	/**
	 * Read and minimally validate the versioned seed file.
	 *
	 * @return array<string, mixed>|\WP_Error
	 */
	private function load_data() {
		if ( ! is_readable( $this->seed_file ) ) {
			return new \WP_Error( 'hks_seed_missing', __( 'The MVP seed file is not readable.', 'hks-core' ) );
		}

		$contents = file_get_contents( $this->seed_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$data     = json_decode( (string) $contents, true );

		if ( ! is_array( $data ) || JSON_ERROR_NONE !== json_last_error() ) {
			return new \WP_Error( 'hks_seed_json', __( 'The MVP seed file is not valid JSON.', 'hks-core' ) );
		}

		if (
			1 !== (int) ( $data['schema_version'] ?? 0 )
			|| 'draft_only' !== ( $data['publication_policy'] ?? '' )
			|| ! isset( $data['tours'], $data['campaigns'] )
			|| ! is_array( $data['tours'] )
			|| ! is_array( $data['campaigns'] )
		) {
			return new \WP_Error( 'hks_seed_schema', __( 'The MVP seed contract is incomplete or unsupported.', 'hks-core' ) );
		}

		return $data;
	}

	/**
	 * Create or refresh one draft Tour.
	 *
	 * @param array<string, mixed> $tour Seed Tour.
	 * @return array<string, mixed>|\WP_Error
	 */
	private function upsert_tour( $tour ) {
		if ( empty( $tour['product_id'] ) || empty( $tour['title'] ) || empty( $tour['source']['url'] ) ) {
			return new \WP_Error( 'hks_seed_tour', __( 'A seed Tour is missing its product ID, title, or source URL.', 'hks-core' ) );
		}

		$existing = $this->find_post( Tour::POST_TYPE, 'hks_internal_product_id', $tour['product_id'] );
		$post     = array(
			'post_type'    => Tour::POST_TYPE,
			'post_title'   => sanitize_text_field( $tour['title'] ),
			'post_name'    => sanitize_title( $tour['slug'] ),
			'post_excerpt' => sanitize_textarea_field( $tour['excerpt'] ),
			'post_content' => $this->paragraph_block( $tour['overview'] ),
		);

		$record = $this->save_draft_post( $existing, $post, $tour['product_id'] );

		if ( is_wp_error( $record ) || 'protected' === ( $record['outcome'] ?? '' ) ) {
			return $record;
		}

		// Stable seed identity remains hidden system metadata, not an editor burden.
		update_post_meta( $record['post_id'], 'hks_internal_product_id', sanitize_text_field( $tour['product_id'] ) );
		$this->update_fields( Tour::POST_TYPE, $record['post_id'], $tour['fields'] );
		$this->append_terms( $record['post_id'], $tour['taxonomies'] );

		return $record;
	}

	/**
	 * Create or refresh one draft Campaign linked to a seeded Tour.
	 *
	 * @param array<string, mixed> $campaign Seed Campaign.
	 * @param int                  $tour_id  Linked Tour post ID.
	 * @return array<string, mixed>|\WP_Error
	 */
	private function upsert_campaign( $campaign, $tour_id ) {
		if ( empty( $campaign['internal_label'] ) || empty( $campaign['title'] ) ) {
			return new \WP_Error( 'hks_seed_campaign', __( 'A seed Campaign is missing its internal label or title.', 'hks-core' ) );
		}

		$existing = $this->find_post( Campaign::POST_TYPE, 'hks_internal_label', $campaign['internal_label'] );
		$post     = array(
			'post_type'  => Campaign::POST_TYPE,
			'post_title' => sanitize_text_field( $campaign['title'] ),
			'post_name'  => sanitize_title( $campaign['slug'] ),
		);

		$record = $this->save_draft_post( $existing, $post, $campaign['internal_label'] );

		if ( is_wp_error( $record ) || 'protected' === ( $record['outcome'] ?? '' ) ) {
			return $record;
		}

		// Stable seed identity remains hidden system metadata, not an editor burden.
		update_post_meta( $record['post_id'], 'hks_internal_label', sanitize_text_field( $campaign['internal_label'] ) );
		$fields = array_merge( array( 'hks_linked_tour' => (int) $tour_id ), $campaign['fields'] );

		$this->update_fields( Campaign::POST_TYPE, $record['post_id'], $fields );

		return $record;
	}

	/**
	 * Locate a post by stable seed identity.
	 *
	 * @param string $post_type Post type.
	 * @param string $meta_key  Identity field name.
	 * @param string $value     Identity value.
	 * @return int|\WP_Error Zero when absent.
	 */
	private function find_post( $post_type, $meta_key, $value ) {
		$posts = get_posts(
			array(
				'post_type'        => $post_type,
				'post_status'      => get_post_stati(),
				'numberposts'      => 2,
				'fields'           => 'ids',
				'meta_key'         => $meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'       => $value, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'suppress_filters' => true,
			)
		);

		if ( count( $posts ) > 1 ) {
			return new \WP_Error(
				'hks_seed_duplicate',
				sprintf(
					/* translators: %s: stable seed identity. */
					__( 'Duplicate records use the seed identity %s. Resolve them before importing.', 'hks-core' ),
					$value
				)
			);
		}

		return empty( $posts ) ? 0 : (int) $posts[0];
	}

	/**
	 * Save a new draft or refresh an existing draft.
	 *
	 * @param int|\WP_Error       $existing Existing post ID or lookup error.
	 * @param array<string, mixed> $post     Sanitized post data.
	 * @param string                $label    Record label for errors.
	 * @return array<string, mixed>|\WP_Error
	 */
	private function save_draft_post( $existing, $post, $label ) {
		if ( is_wp_error( $existing ) ) {
			return $existing;
		}

		if ( $existing ) {
			if ( 'draft' !== get_post_status( $existing ) ) {
				return array(
					'post_id' => $existing,
					'outcome' => 'protected',
				);
			}

			$post['ID'] = $existing;
			$saved      = wp_update_post( wp_slash( $post ), true );
			$outcome    = 'updated';
		} else {
			$post['post_status'] = 'draft';
			$saved               = wp_insert_post( wp_slash( $post ), true );
			$outcome             = 'created';
		}

		if ( is_wp_error( $saved ) ) {
			return new \WP_Error(
				'hks_seed_save',
				sprintf(
					/* translators: 1: record label, 2: WordPress error. */
					__( 'Could not save %1$s: %2$s', 'hks-core' ),
					$label,
					$saved->get_error_message()
				)
			);
		}

		return array(
			'post_id' => (int) $saved,
			'outcome' => $outcome,
		);
	}

	/**
	 * Store SCF values using deterministic field keys.
	 *
	 * @param string               $post_type Post type.
	 * @param int                  $post_id   Post ID.
	 * @param array<string, mixed> $values    Values keyed by field name.
	 * @return void
	 */
	private function update_fields( $post_type, $post_id, $values ) {
		$fields = $this->fields_for_post_type( $post_type );

		foreach ( $values as $name => $value ) {
			if ( empty( $fields[ $name ] ) ) {
				continue;
			}

			$field = $fields[ $name ];
			update_field( $field['key'], $this->normalize_value( $field, $value ), $post_id );
		}
	}

	/**
	 * Return named field definitions for one post type.
	 *
	 * @param string $post_type Post type.
	 * @return array<string, array<string, mixed>>
	 */
	private function fields_for_post_type( $post_type ) {
		$fields = array();

		foreach ( FieldGroups::all() as $group ) {
			if ( ! $this->group_targets_post_type( $group, $post_type ) ) {
				continue;
			}

			foreach ( $group['fields'] as $field ) {
				if ( ! empty( $field['name'] ) ) {
					$fields[ $field['name'] ] = $field;
				}
			}
		}

		return $fields;
	}

	/**
	 * Check a field group's location rules.
	 *
	 * @param array<string, mixed> $group     Field group.
	 * @param string               $post_type Post type.
	 * @return bool
	 */
	private function group_targets_post_type( $group, $post_type ) {
		foreach ( $group['location'] ?? array() as $rule_group ) {
			foreach ( $rule_group as $rule ) {
				if ( 'post_type' === ( $rule['param'] ?? '' ) && $post_type === ( $rule['value'] ?? '' ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Replace repeater or group row names with deterministic sub-field keys.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Seed value.
	 * @return mixed
	 */
	private function normalize_value( $field, $value ) {
		$sub_fields = $field['sub_fields'] ?? array();

		if ( empty( $sub_fields ) || ! is_array( $value ) ) {
			return $value;
		}

		if ( 'repeater' === ( $field['type'] ?? '' ) ) {
			$rows = array();

			foreach ( $value as $row ) {
				$rows[] = $this->normalize_row( $sub_fields, $row );
			}

			return $rows;
		}

		return $this->normalize_row( $sub_fields, $value );
	}

	/**
	 * Normalize one group or repeater row.
	 *
	 * @param array<int, array<string, mixed>> $sub_fields Sub-fields.
	 * @param array<string, mixed>             $row        Seed row.
	 * @return array<string, mixed>
	 */
	private function normalize_row( $sub_fields, $row ) {
		$normalized = array();

		if ( ! is_array( $row ) ) {
			return $normalized;
		}

		foreach ( $sub_fields as $sub_field ) {
			$name = $sub_field['name'] ?? '';

			if ( '' === $name || ! array_key_exists( $name, $row ) ) {
				continue;
			}

			$normalized[ $sub_field['key'] ] = $this->normalize_value( $sub_field, $row[ $name ] );
		}

		return $normalized;
	}

	/**
	 * Append seed terms without deleting editor-added terms.
	 *
	 * @param int                                      $post_id    Post ID.
	 * @param array<string, array<int, string>> $taxonomies Taxonomy terms.
	 * @return void
	 */
	private function append_terms( $post_id, $taxonomies ) {
		foreach ( $taxonomies as $taxonomy => $names ) {
			if ( ! taxonomy_exists( $taxonomy ) || ! is_array( $names ) ) {
				continue;
			}

			$term_ids = array();

			foreach ( $names as $name ) {
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

	/**
	 * Format the native Tour overview as a locked paragraph block.
	 *
	 * @param string $text Overview text.
	 * @return string
	 */
	private function paragraph_block( $text ) {
		return '<!-- wp:paragraph --><p>' . esc_html( $text ) . '</p><!-- /wp:paragraph -->';
	}
}
