<?php
/**
 * WP_Post trait.
 *
 * @package WP_Component
 */

namespace WP_Component;

/**
 * WP_Post trait.
 */
trait WP_Post {

	/**
	 * Post object.
	 *
	 * @var null|\WP_Post
	 */
	public $post = null;

	/**
	 * Get the post ID.
	 *
	 * @return int
	 */
	public function get_post_id() {
		return absint( $this->post->ID ?? 0 );
	}

	/**
	 * Set the post object.
	 *
	 * @param mixed $post Post object, post ID, or null to use global $post
	 *                    object.
	 */
	public function set_post( $post = null ) {

		// Post was passed.
		if ( $post instanceof \WP_Post ) {
			$this->post = $post;
			$this->post_has_set();
			return $this;
		}

		// Use global $post.
		if ( is_null( $post ) ) {
			global $post;
			$this->post = $post;
			$this->post_has_set();
			return $this;
		}

		// Post ID was passed.
		if ( 0 !== absint( $post ) ) {
			$this->set_post( get_post( $post ) );
			$this->post_has_set();
			return $this;
		}

		// Something else went wrong.
		// @todo determine how to handle error messages.
		return $this;
	}

	/**
	 * Callback function for classes to override.
	 */
	public function post_has_set() {
		// Silence is golden.
	}
}
