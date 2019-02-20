<?php
/**
 * Term component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Term.
 */
class Term extends Component {

	use WP_Term;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'term';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() {
		return [
			'id'   => 0,
			'link' => '',
			'name' => '',
			'slug' => '',
		];
	}

	/**
	 * Fires after the term object has been set on this class.
	 */
	public function term_has_set() {
		$this->wp_term_set_name();
		$this->wp_term_set_id();
		$this->wp_term_set_slug();
		$this->wp_term_set_link();
	}
}
