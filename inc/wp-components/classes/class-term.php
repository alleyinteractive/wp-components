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
		$this->set_config( 'id', $this->term->term_id );
		$this->set_config( 'name', html_entity_decode( $this->term->name ) );
		$this->set_config( 'slug', $this->term->slug );
		$this->set_config( 'link', get_term_link( $this->term ) );
	}
}
