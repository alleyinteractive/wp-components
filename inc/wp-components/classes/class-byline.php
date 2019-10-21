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
	use \WP_Components\Byline_Manager_Profile;

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
			$post_id = $post_id->ID;
		}

		// Handle either Coauthors, Byline Manager profiles,
		// or core users.
		if ( function_exists( 'get_coauthors' ) ) {
			// Setup byline using guest authors.
			$coauthors = get_coauthors( $post_id );
			$bylines   = [];

			// Loop through coauthors, creating new byline objects as needed.
			foreach ( $coauthors as $coauthor ) {
				$byline = new static();
				if ( $coauthor instanceof \WP_User ) {
					$byline->set_user( $coauthor );
				} elseif ( 'guest-author' === ( $coauthor->type ?? '' ) ) {
					$byline->set_guest_author( $coauthor );
				}
				$bylines[] = $byline;
			}
		} elseif ( class_exists( '\Byline_Manager\Models\Profile' ) ) {
			$profiles = \Byline_Manager\Utils::get_byline_entries_for_post( $post_id );
			$bylines  = [];

			// Loop through profiles, creating new byline objects as needed.
			foreach ( $profiles as $profile ) {
				$byline = new static();

				if (
					$profile instanceof \WP_Post &&
					'profile' === $profile->post_type
				) {
					$byline->set_byline_manager_profile( $coauthor );
				}

				$bylines[] = $byline;
			}
		} else {
			// Setup byline using post author.
			$post_object = get_post( $post_id );

			if ( $post_object instanceof \WP_Post ) {
				$byline = new static();
				$byline->set_user( $post_object->post_author );
				return [ $byline ];
			}

			return [];
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

	/**
	 * Handling for Byline Manager profiles posts.
	 *
	 * @return self
	 */
	public function byline_manager_profile_has_set() : self {
		$this->set_config( 'name', $this->byline_manager_profile->post_title ?? '' );
		$this->set_config(
			'link',
			get_post_permalink( $this->byline_manager_profile->ID )
		);
		return $this;
	}
}
