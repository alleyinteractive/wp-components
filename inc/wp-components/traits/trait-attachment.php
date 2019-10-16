<?php
/**
 * Attachment trait.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Attachment trait.
 */
trait Attachment {

	/**
	 * Post object.
	 *
	 * @var null|\Attachment
	 */
	public $attachment = null;

	/**
	 * Set the post object.
	 *
	 * @param mixed $post Post object, post ID, or null to use global $post
	 *                    object.
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function set_attachment( $attachment = null ) : self {

		// Post was passed.
		if ( $attachment instanceof \WP_Post ) {
			$this->attachment = $attachment;
			$this->attachment_has_set();
			return $this;
		}

		// Post ID was passed.
		if ( 0 !== absint( $attachment ) ) {
			$post = get_post( $post );

			if ( 'post' === $post->post_type ) {
				$attachment_id = get_post_thumbnail_id( $post );
				$post = get_post( $attachment_id );
			}

			// Don't set post if empty.
			if ( ! empty( $post ) ) {
				$this->attachment = $attachment;
			}

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
	public function attachment_has_set() : self {
		return $this;
	}

	/**
	 * Determine if attachment has set correctly.
	 *
	 * @return bool
	 */
	public function is_valid_post() {
		if ( $this->attachment instanceof \WP_Post ) {
			return true;
		}
		return false;
	}

	/**
	 * Get the attachment ID.
	 *
	 * @return int
	 */
	public function get_attachment_id() : int {
		$attachment_id = $this->attachment->ID ?? 0;
		return absint( $attachment_id );
	}

	/**
	 * Set the `id` config to the post ID.
	 *
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function set_attachment_id() : self {
		$this->set_config( 'id', $this->attachment_get_id() );
		return $this;
	}

	/**
	 * Get the post title.
	 *
	 * @return string
	 */
	public function get_attachment_title() : string {
		return html_entity_decode( get_the_title( $this->attachment ) );
	}

	/**
	 * Get the post permalink.
	 *
	 * @return string
	 */
	public function get_attachment_permalink() : string {
		if ( $this->is_valid_post() ) {

			// Handle unpublished content.
			if ( 'publish' !== $this->attachment->post_status ) {
				return get_preview_post_link( $this->attachment );
			}

			return get_permalink( $this->attachment );
		}
		return '';
	}

	/**
	 * Get the post permalink.
	 *
	 * @return string
	 */
	public function get_attachment_url( $size = 'full' ): string {
		wp_get_attachment_image_url( $this->get_attachment_id(), $size );
	}

	/**
	 * Retrieve alt text for current attachment.
	 *
	 * @return string
	 */
	public function get_attachment_alt(): string {
		// First check attachment alt text meta.
		$id = $this->get_attachment_id();
		$image_alt = get_post_meta( $id, '_wp_attachment_image_alt', true );

		if ( ! empty( $image_alt ) ) {
			return esc_attr( $image_alt );
		}

		// Use if a 'caption' config is set, use that as a fallback.
		$caption = $this->get_attachment_caption();
		if ( ! empty( $caption ) ) {
			return esc_attr( $caption );
		}

		// Use image description as final fallback.
		$post = get_post( $attachment_id );
		if ( $post ) {
			// We can't rely on get_the_excerpt(), because it relies on The Loop
			// global variables that are not correctly set within the Irving context.
			return esc_attr( $post->post_excerpt );
		}

		return '';
	}

	/**
	 * Retrieve caption for current attachment.
	 *
	 * @return string
	 */
	public function get_attachment_caption(): string {
		return wp_get_attachment_caption( $this->get_attachment_id() ) ?? '';
	}

	/**
	 * Retrieve metadata for the attachment.
	 *
	 * @return array
	 */
	public function get_attachment_meta(): array {
		return wp_get_attachment_metadata( $this->get_attachment_id() ) ?? [];
	}

	/**
	 * Set width and height dimensions for image.
	 *
	 * @return self.
	 */
	public function set_attachment_dimensions(): self {
		$attachment_meta = $this->get_attachment_meta();

		return $this->merge_config(
			[
				'width'  => $attachment_meta['width'] ?? 0,
				'height' => $attachment_meta['height'] ?? 0,
			]
		);
	}
}
