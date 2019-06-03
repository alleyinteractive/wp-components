<?php
/**
 * WP_Post trait.
 *
 * @package WP_Components
 */

namespace WP_Components;

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
		return $this->wp_post_get_id();
	}

	/**
	 * Set the post object.
	 *
	 * @param mixed $post Post object, post ID, or null to use global $post
	 *                    object.
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function set_post( $post = null ) : self {

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
	public function post_has_set() : self {
		return $this;
	}

	/**
	 * Determine if post has set correctly.
	 *
	 * @return bool
	 */
	public function is_valid_post() {
		if ( $this->post instanceof \WP_Post ) {
			return true;
		}
		return false;
	}

	/**
	 * Get the post id.
	 *
	 * @return int
	 */
	public function wp_post_get_id() : int {
		$post_id = $this->post->ID ?? 0;
		return absint( $post_id );
	}

	/**
	 * Set the `id` config to the post ID.
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function wp_post_set_id() : self {
		$this->set_config( 'id', $this->wp_post_get_id() );
		return $this;
	}

	/**
	 * Get the post title.
	 *
	 * @return string
	 */
	public function wp_post_get_title() : string {
		return html_entity_decode( get_the_title( $this->post ) );
	}

	/**
	 * Set the `title` config to the post title.
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function wp_post_set_title() : self {
		$this->set_config( 'title', $this->wp_post_get_title() );
		return $this;
	}

	/**
	 * Get the post permalink.
	 *
	 * @return string
	 */
	public function wp_post_get_permalink() : string {
		if ( $this->is_valid_post() ) {
			return get_permalink( $this->post );
		}
		return '';
	}

	/**
	 * Set the `permalink` config to the post permalink.
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function wp_post_set_permalink() : self {
		$this->set_config( 'permalink', $this->wp_post_get_permalink() );
		return $this;
	}

	/**
	 * Get the post excerpt.
	 *
	 * @return string
	 */
	public function wp_post_get_excerpt() : string {

		// Modify global state.
		global $post;

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
		$backup_post = $post;

		// Setup post data for this item.
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
		$post = $this->post;
		setup_postdata( $post );

		$excerpt = get_the_excerpt();

		// Undo global modification.
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
		$post = $backup_post;
		setup_postdata( $post );

		return html_entity_decode( (string) $excerpt );
	}

	/**
	 * Set the `excerpt` config to the post excerpt.
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function wp_post_set_excerpt() : self {
		$this->set_config( 'excerpt', $this->wp_post_get_excerpt() );
		return $this;
	}

	/**
	 * Create Image component and append to children.
	 *
	 * @param string $size Image size to use for child image component.
	 * @param array  $config Additional config for Image component.
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function wp_post_set_featured_image( $size = 'full', $config = [] ) : self {
		return $this->append_children(
			[
				( new \WP_Components\Image() )
					->set_post_id( $this->get_post_id() )
					->set_config_for_size( $size )
					->merge_config( $config ),
			]
		);
	}
}
