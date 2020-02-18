<?php
/**
 * Yoast Schema Component.
 *
 * @package WP_Components
 */

namespace WP_Components\Integrations;

/**
 * Yoast_Schema Class.
 */
class Yoast_Schema extends \WP_Components\Component {

	use \WP_Components\WP_Post;
    use \WP_Components\WP_Query;
	use \WP_Components\WP_Term;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'yoast-schema';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config(): array {
		return [ 'content' => '' ];
	}

    /**
	 * Set schema for a regular query.
	 *
	 * @return self
	 */
	public function query_has_set(): self {
        return $this->merge_config( [ 'content' => $this->get_json() ] );
	}

    /**
	 * Set schema for a post query.
	 *
	 * @return self
	 */
	public function post_has_set(): self {
		return $this->merge_config( [ 'content' => $this->get_json() ] );
	}

	/**
	 * Set schema for a term query.
	 *
	 * @return self
	 */
	public function term_has_set(): self {
		return $this->merge_config( [ 'content' => $this->get_json() ] );
	}

    /**
     * Get Yoast Schema Json
     *
     * @return string
     */
    public function get_json(): string {
		ob_start();
		do_action( 'wpseo_json_ld' );
		$schema = ob_get_contents();
		ob_end_clean();

		if ( empty( $schema ) ) {
			return '';
		}

		return \strip_tags( $schema );
	}
}
