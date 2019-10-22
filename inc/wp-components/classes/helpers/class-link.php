<?php
/**
 * Helper link component.
 *
 * @package WP_Components.
 */

namespace WP_Components\Helpers;

/**
 * Class for a link.
 */
class Link extends \WP_Components\Component {

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'link';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config(): array {
		return [
			'blank' => '',
			'to'    => '',
		];
	}
}
