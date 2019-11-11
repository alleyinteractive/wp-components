<?php
/**
 * Byline Manager Profile trait.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Byline_Manager_Profile trait.
 */
trait Byline_Manager_Profile {

	/**
	 * Byline Manager profile.
	 *
	 * @var null|\Byline_Manager\Models\Profile
	 */
	public $byline_manager_profile = null;

	/**
	 * Get the profile ID.
	 *
	 * @return int
	 */
	public function get_byline_manager_profile_id() : int {
		return absint( $this->byline_manager_profile->ID ?? 0 );
	}

	/**
	 * Set the profile object.
	 *
	 * @param \WP_Post|null $byline_manager_profile Profile post.
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function set_byline_manager_profile( $byline_manager_profile = null ) : self {
		// Post was passed.
		if ( 'profile' === ( $byline_manager_profile->post_type ?? '' ) ) {
			$this->byline_manager_profile = $byline_manager_profile;
			$this->byline_manager_profile_has_set();
		}

		return $this;
	}

	/**
	 * Callback function for classes to override.
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function byline_manager_profile_has_set() : self {
		return $this;
	}

	/**
	 * Create Image component and add to children.
	 *
	 * @todo Add a fallback image.
	 *
	 * @param string $size Image size to use for child image component.
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function byline_manager_profile_set_avatar( $size = 'full' ) : self {
		$this->append_child(
			( new \WP_Components\Image() )
				->set_post_id( $this->get_byline_manager_profile_id() )
				->set_config_for_size( $size )
		);

		return $this;
	}
}
