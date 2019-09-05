<?php
/**
 * Social Sharing component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Social Sharing.
 */
class Social_Sharing extends Component {

	use WP_Post;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'social-sharing';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [
			'services'      => [],
			'display_icons' => true,
		];
	}

	/**
	 * Hook into post being set.
	 *
	 * @return self
	 */
	public function post_has_set() : self {
		foreach ( $this->config['services'] as $service => $enabled ) {
			if ( (bool) $enabled && method_exists( $this, "get_{$service}_component" ) ) {
				$this->append_child( call_user_func( [ $this, "get_{$service}_component" ] ) );
			}
		}
		return $this;
	}

	/**
	 * Get a Facebook Social_item component.
	 *
	 * @return \WP_Components\Social_item
	 */
	public function get_facebook_component() : Social_Item {
		return ( new Social_Item() )
			->merge_config(
				[
					'type' => 'facebook',
					'url'  => add_query_arg(
						[
							'u' => $this->get_url(),
						],
						'https://www.facebook.com/sharer.php/'
					),
					'display_icon' => $this->get_config( 'display_icons' ),
				]
			);
	}

	/**
	 * Get a Twitter Social_item component.
	 *
	 * @return \WP_Components\Social_item
	 */
	public function get_twitter_component() : Social_Item {
		return ( new Social_Item() )
			->merge_config(
				[
					'type' => 'twitter',
					'url'  => add_query_arg(
						[
							'text' => $this->get_title(),
							'url'  => $this->get_url(),
						],
						'https://twitter.com/intent/tweet'
					),
					'display_icon' => $this->get_config( 'display_icons' ),
				]
			);
	}

	/**
	 * Get a Whatsapp Social_item component.
	 *
	 * @return \WP_Components\Social_item
	 */
	public function get_whatsapp_component() : Social_Item {
		return ( new Social_Item() )
			->merge_config(
				[
					'type' => 'whatsapp',
					'url'  => add_query_arg(
						[
							'text' => rawurlencode(
								sprintf(
									// Translators: %1$s - article title, %2$s - article url.
									esc_html__( 'Check out this story: %1$s %2$s', 'wp-components' ),
									$this->get_title(),
									$this->get_url()
								)
							),
						],
						'https://api.whatsapp.com/send/'
					),
					'display_icon' => $this->get_config( 'display_icons' ),
				]
			);
	}

	/**
	 * Get a LinkedIn Social_item component.
	 *
	 * @return \WP_Components\Social_item
	 */
	public function get_linkedin_component() : Social_Item {
		return ( new Social_Item() )
			->merge_config(
				[
					'type' => 'linkedin',
					'url'  => add_query_arg(
						[
							'url'     => $this->get_url(),
							'title'   => $this->get_title(),
							'summary' => $this->get_excerpt(),
						],
						'https://www.linkedin.com/shareArticle/'
					),
					'display_icon' => $this->get_config( 'display_icons' ),
				]
			);
	}

	/**
	 * Get a Pinterest Social_item component.
	 *
	 * @return \WP_Components\Social_item
	 */
	public function get_pinterest_component() : Social_Item {
		return ( new Social_Item() )
			->merge_config(
				[
					'type' => 'pinterest',
					'url'  => add_query_arg(
						[
							'url'         => $this->get_url(),
							'media'       => $this->get_featured_image_url(),
							'description' => $this->get_excerpt(),
						],
						'https://pinterest.com/pin/create/button/'
					),
					'display_icon' => $this->get_config( 'display_icons' ),
				]
			);
	}

	/**
	 * Get an Email Social_item component.
	 * Sets the subject to the item's title, and
	 * the email body to the URL of the item being shared
	 *
	 * @return \WP_Components\Social_item
	 */
	public function get_email_component() : Social_Item {
		return ( new Social_Item() )
			->merge_config(
				[
					'type' => 'email',
					'url'  => add_query_arg(
						[
							'subject' => $this->get_title(),
							'body'    => $this->get_url(),
						],
						'mailto:'
					),
					'display_icon' => $this->get_config( 'display_icons' ),
				]
			);
	}

	/**
	 * Helper for getting a url encoded url.
	 *
	 * @return string
	 */
	public function get_url() : string {
		return rawurlencode( $this->wp_post_get_permalink() );
	}

	/**
	 * Helper for getting a url encoded title.
	 *
	 * @return string
	 */
	public function get_title() : string {
		return rawurlencode( $this->wp_post_get_title() );
	}

	/**
	 * Helper for getting a url encoded excerpt.
	 *
	 * @return string
	 */
	public function get_excerpt() : string {
		return rawurlencode( $this->wp_post_get_excerpt() );
	}

	/**
	 * Helper for getting a url encoded excerpt.
	 *
	 * @return string
	 */
	public function get_featured_image_url() : string {
		return rawurlencode( get_the_post_thumbnail_url( $this->post, 'full' ) );
	}
}
