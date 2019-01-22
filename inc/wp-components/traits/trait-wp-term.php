<?php
/**
 * WP_Term trait.
 *
 * @package WP_Component
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
			$this->term_has_set();
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
}
