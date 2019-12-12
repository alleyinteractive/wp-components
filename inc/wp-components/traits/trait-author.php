<?php
/**
 * Trait that handles logic handling between WP_User objects and guest author
 * objects.
 *
 * Use this when you're not sure if the object will be a User, CAP Guest
 * Author, or Byline Manager profile.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Author trait.
 */
trait Author {

	/**
	 * Return 'wp_user', 'guest_author', or 'byline_manager_profile' based on
	 * the objects set.
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

		if ( ! is_null( $this->byline_manager_profile ?? null ) ) {
			return 'byline_manager_profile';
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
				$id = $this->guest_author->ID;
				break;

			case 'byline_manager_profile':
				$id = $this->byline_manager_profile->ID;
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
				$display_name = $this->guest_author->display_name;
				break;

			case 'byline_manager_profile':
				$display_name = $this->byline_manager_profile->post_title;
		}

		return $display_name;
	}

	/**
	 * Get the author email address.
	 *
	 * @return string
	 */
	public function get_author_email() : string {
		$email_address = '';

		switch ( $this->get_author_type() ) {
			case 'wp_user':
				$email_address = $this->user->data->user_email ?? '';
				break;

			case 'guest_author':
				$email_address = $this->guest_author->user_email ?? '';
				break;

			default:
				break;
		}

		return $email_address;
	}

	/**
	 * Set the author using either a WP_User or Guest Author post object.
	 *
	 * @param mixed $author The author.
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function set_author( $author = null ) : self {
		global $coauthors_plus;

		// User login.
		if (
			function_exists( 'get_coauthors' ) &&
			is_string( $author )
		) {
			$coauthor = $coauthors_plus->get_coauthor_by( 'user_login', $author );
			$this->set_author( $coauthor );
			return $this;
		}

		// Use \WP_User object.
		if ( $author instanceof \WP_User ) {
			$this->set_user( $author );
			$this->author_has_set();
			return $this;
		}

		// Byline Manager profile.
		if (
			$author instanceof \WP_Post
			&& 'profile' === ( $author->post_type ?? '' )
		) {
			$this->set_byline_manager_profile( $author );
			$this->author_has_set();
			return $this;
		}

		// Guest author post.
		if (
			$author instanceof \WP_Post
			&& 'guest-author' === ( $author->post_type ?? '' )
		) {
			$this->set_guest_author( $author );
			$this->author_has_set();
			return $this;
		}

		// Guest author object.
		if (
			is_object( $author )
			&& 'guest-author' === ( $author->type ?? '' )
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
