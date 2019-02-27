<?php
/**
 * Social Sharing Item component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Social Sharing.
 */
class Social_Sharing_Item extends Component {

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'social-sharing-item';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() {
		return [
			'display_icon' => true,
			'type'         => '',
			'url'          => '',
		];
	}
}