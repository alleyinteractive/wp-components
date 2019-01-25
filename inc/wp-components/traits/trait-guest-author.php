<?php
/**
 * Guest Author trait.
 *
 * @package WP_Components
 */

namespace WP_Components;

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
	 * @todo Finish implementing this trait setter.
	 *
	 * @param \WP_Post|null $guest_author Guest Author post.
	 */
	public function set_guest_author( $guest_author = null ) {

		// Post was passed.
		if ( 'guest-author' === ( $guest_author->type ?? '' ) ) {
			$this->guest_author = $guest_author;
			$this->guest_author_has_set();
			return $this;
		}

		// Something else went wrong.
		// @todo determine how to handle error messages.
		return $this;
	}

	/**
	 * Callback function for classes to override.
	 */
	public function guest_author_has_set() {
		// Silence is golden.
	}
}
