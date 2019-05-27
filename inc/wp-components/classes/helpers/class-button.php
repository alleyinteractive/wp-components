<?php
/**
 * Button component. This helper is a generic button to be used anywhere.
 *
 * @package WP_Components.
 */

namespace WP_Components\Helpers;

/**
 * Class for a button.
 */
class Button extends \WP_Components\Component {

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'button';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [
			'button_style' => '',
			'class_name'   => '',
			'link'         => '',
			'type'         => '',
		];
	}
}
