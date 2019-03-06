<?php
/**
 * Disqus component.
 *
 * @package WP_Components
 */

namespace WP_Components\Integrations;

/**
 * Disqus.
 */
class Disqus extends \WP_Components\Component {

	use \WP_Components\WP_Post;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'disqus';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [
			'forum_shortname' => '',
			'page_identifier' => '',
			'page_url'        => '',
		];
	}

	/**
	 * Hook into post being set.
	 *
	 * @return self
	 */
	public function post_has_set() : self {
		$this->merge_config(
			[
				'page_url'        => get_the_permalink( $this->post ),
				'page_identifier' => $this->post->ID . ' ' . $this->post->guid,
			]
		);

		return $this;
	}
}
