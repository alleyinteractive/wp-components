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
		if ( 'guest-author' === ( $guest_author->post_type ?? '' ) ) {
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

	/**
	 * Create Image component and add to children.
	 *
	 * @todo Add a fallback image.
	 *
	 * @param string $size Image size to use for child image component.
	 * @return self
	 */
	public function guest_author_set_avatar( $size = 'full' ) : self {
		$this->append_child(
			( new \WP_Components\Image() )
				->set_post_id( $this->get_guest_author_id() )
				->set_config_for_size( $size )
		);

		return $this;
	}
}
