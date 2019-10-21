<?php
/**
 * Byline Wrapper component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Byline Wrapper.
 */
class Byline_Wrapper extends Component {

	use \WP_Components\WP_Post;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'byline-wrapper';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config(): array {
		return [
			'delimiter'      => esc_html__( ', ', 'wp-components' ),
			'last_delimiter' => esc_html__( ', and ', 'wp-components' ),
			'pre_text'       => esc_html__( 'By ', 'wp-components' ),
			'solo_delimiter' => esc_html__( ' and ', 'wp-components' ),
			'timestamp'      => '',
		];
	}

	/**
	 * Hook into post being set.
	 *
	 * @return self
	 */
	public function post_has_set(): self {

		$byline_components = [];

		// Use guest author.
		if ( function_exists( 'get_coauthors' ) ) {
			$byline_components = $this->get_cap_authors_as_bylines();
		}

		// Use post author.
		if ( empty( $byline_components ) ) {
			$byline_components = $this->get_post_author_as_byline();
		}

		$this->append_children(
			array_filter( $byline_components )
		);
		return $this;
	}

	/**
	 * Setup byline using guest authors.
	 *
	 * @return arry Byline components.
	 */
	public function get_cap_authors_as_bylines() {
		return array_map(
			function( $coauthor ) {
				return $this
					->get_new_byline_component()
					->callback(
						function( $byline ) use ( $coauthor ) {

							// Guest author byline.
							if ( 'guest-author' === ( $coauthor->type ?? '' ) ) {
								return $byline->set_guest_author( $coauthor );
							}

							// Post author byline.
							if ( $coauthor instanceof \WP_User ) {
								return $byline->set_user( $coauthor );
							}

							return $byline;
						}
					);
			},
			get_coauthors( $this->wp_post_get_id() )
		);
	}

	/**
	 * Setup byline using post author.
	 *
	 * @return arry Byline components.
	 */
	public function get_post_author_as_byline() {
		return [
			$this->get_new_byline_component()->set_user( $this->wp_post->post_author ),
		];
	}

	/**
	 * Return the byline component that should be used to populate this
	 * wrapper.
	 *
	 * @return \Byline Byline component used in the wrapper.
	 */
	public function get_new_byline_component() {
		return new Byline();
	}
}
