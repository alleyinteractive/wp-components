<?php
/**
 * Byline component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Byline.
 */
class Byline extends Component {

	use \WP_Components\WP_User;
	use \WP_Components\Guest_Author;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'byline';


	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [
			'link' => '',
			'name' => '',
		];
	}

	/**
	 * Get an array of byline components for a given post.
	 *
	 * @param int|null|\WP_Post $post_id Post ID, null to use global $post, or
	 *                                   WP_Post object.
	 * @return array Byline components.
	 */
	public static function get_post_bylines( $post_id = null ) : array {

		// Use global $post object.
		if ( is_null( $post_id ) ) {
			global $post;
			$post_id = $post->ID;
		}

		// Use WP_Post object.
		if ( $post_id instanceof \WP_Post ) {
			$post_id = $post->ID;
		}

		// Handle users.
		if ( ! function_exists( 'get_coauthors' ) ) {

			// Setup byline using post author.
			$post_object = get_post( $post_id );
			if ( $post_object instanceof \WP_Post ) {
				$byline = new Byline();
				$byline->set_user( $post_object->post_author );
				return [ $byline ];
			}
			return [];
		}

		// Setup byline using guest authors.
		$coauthors = get_coauthors( $post_id );
		$bylines   = [];

		// Loop through coauthors, creating new byline objects as needed.
		foreach ( $coauthors as $coauthor ) {
			$byline = new Byline();
			if ( $coauthor instanceof \WP_User ) {
				$byline->set_user( $coauthor );
			} elseif ( 'guest-author' === ( $coauthor->data['type'] ?? '' ) ) {
				$byline->set_guest_author( $coauthor );
			}
			$bylines[] = $byline;
		}

		return array_filter( $bylines );
	}

	/**
	 * Handling for WP_User objects.
	 *
	 * @return self
	 */
	public function user_has_set() : self {
		$this->set_config( 'name', $this->user->data->display_name ?? '' );
		$this->set_config( 'link', get_author_posts_url( $this->user->data->ID, $this->user->data->user_nicename ) );
		return $this;
	}

	/**
	 * Handling for Co-Authors Plus guest author objects.
	 *
	 * @return self
	 */
	public function guest_author_has_set() : self {
		$this->set_config( 'name', $this->guest_author->display_name ?? '' );
		$this->set_config( 'link', get_author_posts_url( $this->guest_author->ID, $this->guest_author->user_nicename ) );
		return $this;
	}
}
