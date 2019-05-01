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
	public function get_term_id() : int {
		return absint( $this->term->term_id ?? 0 );
	}

	/**
	 * Set the term object.
	 *
	 * @param mixed $term Term object, term ID, or null to use global $term
	 *                    object.
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function set_term( $term = null ) : self {

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
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function term_has_set() : self {
		return $this;
	}

	/**
	 * Determine if term has set correctly.
	 *
	 * @return bool
	 */
	public function is_valid_term() : bool {
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
	public function wp_term_get_id() : int {
		$term_id = $this->term->term_id ?? 0;
		return absint( $term_id );
	}

	/**
	 * Set the `id` config to the term ID.
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function wp_term_set_id() : self {
		return $this->set_config( 'id', $this->wp_term_get_id() );
	}

	/**
	 * Get the term name.
	 *
	 * @return string
	 */
	public function wp_term_get_name() : string {
		$name = $this->term->name ?? '';
		return html_entity_decode( $name );
	}

	/**
	 * Set the `name` config to the term name.
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function wp_term_set_name() : self {
		return $this->set_config( 'name', $this->wp_term_get_name() );
	}

	/**
	 * Get the term taxonomy.
	 *
	 * @return string
	 */
	public function wp_term_get_taxonomy() : string {
		return $this->term->taxonomy ?? '';
	}

	/**
	 * Get the term taxonomy slug, with any rewrites applied.
	 *
	 * @return string
	 */
	public function wp_term_get_taxonomy_slug() : string {
		if ( empty( $this->wp_term_get_taxonomy() ) ) {
			return '';
		}

		$taxonomy = get_taxonomy( $this->wp_term_get_taxonomy() );

		return $taxonomy->rewrite['slug'];
	}

	/**
	 * Set the `taxonomy` config to the term taxonomy.
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function wp_term_set_taxonomy() : self {
		return $this->set_config( 'taxonomy', $this->wp_term_get_taxonomy() );
	}

	/**
	 * Get the term slug.
	 *
	 * @return string
	 */
	public function wp_term_get_slug() : string {
		return $this->term->slug ?? '';
	}

	/**
	 * Set the `slug` config to the term slug.
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function wp_term_set_slug() : self {
		return $this->set_config( 'slug', $this->wp_term_get_slug() );
	}

	/**
	 * Get the term link.
	 *
	 * @return string
	 */
	public function wp_term_get_link() : string {
		if ( $this->is_valid_term() ) {
			return get_term_link( $this->term );
		}
		return '';
	}

	/**
	 * Set the `link` config to the term link.
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function wp_term_set_link() : self {
		return $this->set_config( 'link', $this->wp_term_get_link() );
	}
}
