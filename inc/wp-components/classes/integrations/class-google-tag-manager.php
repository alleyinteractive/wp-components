<?php
/**
 * Google Tag Manager component.
 *
 * @package WP_Components
 */

namespace WP_Components\Integrations;

/**
 * Google Tag Manager.
 */
class Google_Tag_Manager extends \WP_Components\Component {

	use \WP_Components\WP_Query;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'google-tag-manager';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [
			'container_id' => '',
			'data_layer'   => [],
		];
	}

	/**
	 * Set targeting arguments from wp_query.
	 *
	 * @return Google_Tag_Manager
	 */
	public function query_has_set() : self {
		return $this->merge_config(
			[
				'data_layer' => [],
			]
		);
	}

	/**
	 * Get value for content type targeting.
	 *
	 * @return string
	 */
	public function get_authors() : array {
		if ( $this->query->is_single() ) {
			$bylines = ( new \WP_Components\Byline_Wrapper() )->set_post( $this->query->ID );

			// Return array of author names.
			return array_map(
				function( $byline ) {
					return $byline->get_config( 'name' );
				},
				$bylines->children
			);
		}

		return [];
	}

	/**
	 * Get value for content type targeting.
	 *
	 * @return string
	 */
	public function get_post_type() : string {
		if ( $this->query->is_single() ) {
			return $this->query->post->post_type;
		}

		return '';
	}

	/**
	 * Get value for content id targeting.
	 *
	 * @return string
	 */
	public function get_post_id() : string {
		if ( $this->query->is_single() ) {
			return $this->query->post->ID;
		}

		return '';
	}

	/**
	 * Get array of taxnomy terms for data layer.
	 *
	 * @param  string $taxonomy Taxonomy for which terms should be extracted.
	 * @return array
	 */
	public function get_taxonomy_terms( $taxonomy ) : array {
		// Single article.
		if ( $this->query->is_single() ) {
			$terms = wp_get_post_terms( $this->query->post->ID, $taxonomy );

			// Check for error.
			if ( is_wp_error( $terms ) ) {
				return [];
			}

			return array_map(
				function( $term ) {
					return $term->name;
				},
				$terms
			);
		}

		// Taxonomy landing.
		if ( $this->query->is_tax( $taxonomy ) ) {
			return [ $this->query->queried_object->name ];
		}

		return [];
	}

	/**
	 * Get taxonomy term, if applicable.
	 *
	 * @param  string $taxonomy Taxonomy for which the first term should be extracted.
	 * @return string|null
	 */
	public function get_single_taxonomy_term( $taxonomy ) {
		// Single article.
		if ( ! is_null( $this->query ) && $this->query->is_single() ) {
			$terms = wp_get_post_terms( $this->query->post->ID, $taxonomy );

			// Check for error.
			if ( is_wp_error( $terms ) ) {
				return null;
			}

			return $terms[0]->name ?? null;
		}

		// Taxonomy landing.
		if ( $this->query->is_tax( $taxonomy ) ) {
			return $this->query->queried_object->name ?? null;
		}

		return null;
	}

	/**
	 * Get post title, if applicable.
	 *
	 * @return string
	 */
	public function get_title() : string {
		if ( $this->query->is_single() ) {
			return $this->query->post->post_title ?? '';
		}

		return '';
	}

	/**
	 * Get Publish date, if applicable.
	 *
	 * @return string
	 */
	public function get_pub_date() : string {
		if ( $this->query->is_single() ) {
			return $this->query->post->post_date ?? '';
		}

		return '';
	}
}
