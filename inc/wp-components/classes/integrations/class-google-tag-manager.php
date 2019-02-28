<?php
/**
 * Google Tag Manager component.
 *
 * @package WP_Components
 */

namespace WP_Components\Integrations;

/**
 * Google Tag Manager.
 */
class Google_Tag_Manager extends Component {

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'google-tag-manager';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [
			'container_id' => '',
		];
	}
}
