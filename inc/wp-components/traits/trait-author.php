<?php
/**
 * Trait that handles logic handling between WP_User objects and guest author
 * objects.
 *
 * @package WP_Component
 */

namespace WP_Components;

/**
 * Author trait.
 */
trait Author {

	/**
	 * Return 'wp_user' or 'guest_author' based on the objects set.
	 *
	 * @return string
	 */
	public function get_author_type() {
		if ( ! is_null( $this->user ) ) {
			return 'wp_user';
		}

		if ( ! is_null( $this->guest_author ) ) {
			return 'guest_author';
		}

		return '';
	}

	/**
	 * Get the author ID.
	 *
	 * @return int
	 */
	public function get_author_id() {
		$id = 0;

		switch ( $this->get_author_type() ) {
			case 'wp_user':
				$id = $this->user->data->ID;
				break;

			case 'guest_author':
				$id = $this->ID;
				break;
		}

		return absint( $id );
	}

	/**
	 * Get the author display name.
	 *
	 * @return string
	 */
	public function get_author_display_name() {
		$display_name = '';

		switch ( $this->get_author_type() ) {
			case 'wp_user':
				$display_name = $this->user->data->display_name;
				break;

			case 'guest_author':
				$display_name = $this->display_name;
				break;
		}

		return $display_name;

	}

	/**
	 * Set the author using either a WP_User or Guest Author post object.
	 *
	 * @param mixed $user
	 */
	public function set_author( $author = null ) {

		// Use \WP_User object.
		if ( $author instanceof \WP_User ) {
			$this->set_user( $author );
			$this->author_has_set();
			return $this;
		}

		if (
			$author instanceof \WP_Post
			&& 'guest_author' === ( $author->type ?? '' )
		) {
			$this->set_guest_author( $author );
			$this->author_has_set();
			return $this;
		}

		// Something else went wrong.
		// @todo deuserine how to handle error messages.
		return $this;
	}

	/**
	 * Callback function for classes to override.
	 */
	public function author_has_set() {
		// Silence is golden.
	}
}
