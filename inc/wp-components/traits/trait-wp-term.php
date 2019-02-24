<?php
/**
 * WP_Term trait.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * WP_Term trait.
 */
trait WP_Term {

	/**
	 * Term object.
	 *
	 * @var null|\WP_Term
	 */
	public $term = null;

	/**
	 * Get the term ID.
	 *
	 * @return int
	 */
	public function get_term_id() {
		return absint( $this->term->ID ?? 0 );
	}

	/**
	 * Set the term object.
	 *
	 * @param mixed $term Term object, term ID, or null to use global $term
	 *                    object.
	 */
	public function set_term( $term = null ) {

		// Term was passed.
		if ( $term instanceof \WP_Term ) {
			$this->term = $term;
			$this->term_has_set();
			return $this;
		}

		// Use global $term.
		if ( is_null( $term ) ) {
			global $term;
			$this->term = $term;
			$this->term_has_set();
			return $this;
		}

		// term ID was passed.
		if ( 0 !== absint( $term ) ) {
			$this->set_term( get_term( $term ) );
			return $this;
		}

		// Something else went wrong.
		// @todo determine how to handle error messages.
		return $this;
	}

	/**
	 * Callback function for classes to override.
	 */
	public function term_has_set() {
		// Silence is golden.
	}

	/**
	 * Determine if term has set correctly.
	 *
	 * @return bool
	 */
	public function is_valid_term() {
		if ( $this->term instanceof \WP_Term ) {
			return true;
		}
		return false;
	}

	/**
	 * Get the term id.
	 *
	 * @return int
	 */
	public function wp_term_get_id() {
		$term_id = $this->term->term_id ?? 0;
		return absint( $term_id );
	}

	/**
	 * Set the `id` config to the term ID.
	 */
	public function wp_term_set_id() {
		$this->set_config( 'id', $this->wp_term_get_id() );
	}

	/**
	 * Get the term name.
	 *
	 * @return string
	 */
	public function wp_term_get_name() {
		$name = $this->term->name ?? '';
		return html_entity_decode( $name );
	}

	/**
	 * Set the `name` config to the term name.
	 */
	public function wp_term_set_name() {
		$this->set_config( 'name', $this->wp_term_get_name() );
	}

	/**
	 * Get the term taxonomy.
	 *
	 * @return string
	 */
	public function wp_term_get_taxonomy() {
		return $this->term->taxonomy ?? '';
	}

	/**
	 * Set the `taxonomy` config to the term taxonomy.
	 */
	public function wp_term_set_taxonomy() {
		$this->set_config( 'taxonomy', $this->wp_term_get_taxonomy() );
	}

	/**
	 * Get the term slug.
	 *
	 * @return string
	 */
	public function wp_term_get_slug() {
		return $this->term->slug ?? '';
	}

	/**
	 * Set the `slug` config to the term slug.
	 */
	public function wp_term_set_slug() {
		$this->set_config( 'slug', $this->wp_term_get_slug() );
	}

	/**
	 * Get the term link.
	 *
	 * @return string
	 */
	public function wp_term_get_link() {
		if ( $this->is_valid_term() ) {
			return get_term_link( $this->term );
		}
		return '';
	}

	/**
	 * Set the `link` config to the term link.
	 */
	public function wp_term_set_link() {
		$this->set_config( 'link', $this->wp_term_get_link() );
	}
}
