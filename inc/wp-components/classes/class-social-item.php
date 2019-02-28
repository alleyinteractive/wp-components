<?php
/**
 * Social Item component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Social Item.
 */
class Social_Item extends Component {

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'social-item';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [
			'display_icon' => true,
			'type'         => '',
			'url'          => '',
		];
	}
}
