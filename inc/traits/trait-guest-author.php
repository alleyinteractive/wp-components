<?php
/**
 * Guest_Author trait.
 *
 * @package WP_Component
 */

namespace WP_Component;

/**
 * Guest_Author trait.
 */
trait Guest_Author {

	/**
	 * Guest Author object.
	 *
	 * @var null|Object
	 */
	public $guest_author = null;

	/**
	 * Get the guest author ID.
	 *
	 * @return int
	 */
	public function get_guest_author_id() {
		return absint( $this->guest_author->ID ?? 0 );
	}

	/**
	 * Set the user object.
	 *
	 * @param mixed $user User object, user ID, or null to use global $user
	 *                    object.
	 */
	public function set_guest_author( $user = null ) {

		// Post was passed.
		if ( $user instanceof \WP_User ) {
			$this->user = $user;
			$this->user_has_set();
			return $this;
		}

		// Use global $user.
		if ( is_null( $user ) ) {
			global $user;
			$this->user = $user;
			$this->user_has_set();
			return $this;
		}

		// user ID was passed.
		if ( 0 !== absint( $user ) ) {
			$this->set_user( get_user( $user ) );
			$this->user_has_set();
			return $this;
		}

		// Something else went wrong.
		// @todo deuserine how to handle error messages.
		return $this;
	}

	/**
	 * Callback function for classes to override.
	 */
	public function guest_author_has_set() {
		// Silence is golden.
	}
}
