<?php
/**
 * Trait that handles logic handling between WP_User objects and guest author
 * objects.
 *
 * Use this when you're not sure if the object will be a User or a CAP Guest
 * Author.
 *
 * @package WP_Components
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
	public function get_author_type() : string {
		if ( ! is_null( $this->user ?? null ) ) {
			return 'wp_user';
		}

		if ( ! is_null( $this->guest_author ?? null ) ) {
			return 'guest_author';
		}

		return '';
	}

	/**
	 * Get the author ID.
	 *
	 * @return int
	 */
	public function get_author_id() : int {
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
	public function get_author_display_name() : string {
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
	 * @param mixed $author The author.
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function set_author( $author = null ) : self {

		// Use \WP_User object.
		if ( $author instanceof \WP_User ) {
			$this->set_user( $author );
			$this->author_has_set();
			return $this;
		}

		if (
			$author instanceof \WP_Post
			&& 'guest-author' === ( $author->post_type ?? '' )
		) {
			$this->set_guest_author( $author );
			$this->author_has_set();
			return $this;
		}

		// Something else went wrong.
		$this->has_error( __( 'Author was not an instance of \WP_User, or a Guest Author post type.', 'wp-components' ) );
		return $this;
	}

	/**
	 * Callback function for classes to override.
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function author_has_set() : self {
		return $this;
	}
}
