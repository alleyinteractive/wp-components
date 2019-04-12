<?php
/**
 * Google Analytics component.
 *
 * @package WP_Components
 */

namespace WP_Components\Integrations;

/**
 * Google Analytics.
 */
class Google_Analytics extends \WP_Components\Component {

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'google-analytics';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [
			'tracking_id' => '',
		];
	}
}
