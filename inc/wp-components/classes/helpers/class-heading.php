<?php
/**
 * Heading component. This helper is a generic heading to be used anywhere.
 *
 * @package WP_Components.
 */

namespace WP_Components\Helpers;

/**
 * Class for a heading.
 */
class Heading extends \WP_Components\Component {

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'heading';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [
			'class_name'  => '',
			'font_family' => '',
			'link'        => '',
			'tag'         => '',
			'type_style'  => '',
		];
	}
}
