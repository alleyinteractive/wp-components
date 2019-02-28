<?php
/**
 * WP_Query trait.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * WP_Query trait.
 */
trait WP_Query {

	/**
	 * WP_Query object.
	 *
	 * @var null|\WP_Query
	 */
	public $query = null;

	/**
	 * Get the query posts.
	 *
	 * @return array
	 */
	public function get_posts() : array {
		return $this->query->posts ?? [];
	}

	/**
	 * Get the queried object.
	 *
	 * @return object
	 */
	public function get_queried_object() {
		return $this->query->get_queried_object();
	}

	/**
	 * Get the queried object ID.
	 *
	 * @return int
	 */
	public function get_queried_object_id() : int {
		return absint( $this->query->get_queried_object_id() ?? 0 );
	}

	/**
	 * Set the query object.
	 *
	 * @param mixed $wp_query \WP_Query object, or null to use global $wp_query
	 *                        object.
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function set_query( $wp_query = null ) : self {

		// WP_Query object was passed.
		if ( $wp_query instanceof \WP_Query ) {
			$this->query = $wp_query;
			$this->query_has_set();
			return $this;
		}

		// Use global $wp_query.
		if ( is_null( $wp_query ) ) {
			global $wp_query;
			$this->query = $wp_query;
			$this->query_has_set();
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
	public function query_has_set() : self {
		return $this;
	}
}
