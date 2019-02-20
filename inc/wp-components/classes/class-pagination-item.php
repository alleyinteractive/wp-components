<?php
/**
 * Pagination Item component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Pagination Item.
 */
class Pagination_Item extends Component {

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'pagination-item';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() {
		return [
			'current' => false,
			'text'    => '',
			'url'     => '',
		];
	}

	/**
	 * Map the raw html of a paginated_links() item to a pagination-link component.
	 *
	 * @param string $link A paginated_links() generated html link item.
	 * @return \WP_Components\Pagination_Item
	 */
	public function set_from_html( string $link ) {

		// Use DOMDocument to parse markup.
		$doc = new \DOMDocument();
		$doc->loadHTML( $link );

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->set_config( 'text', $doc->textContent );

		// Handle classes.
		$span = $doc->getElementsByTagName( 'span' )[0];
		if ( $span ) {
			$class_name = $span->getAttribute( 'class' );
			if ( strstr( $class_name, 'current' ) ) {
				$this->set_config( 'current', true );
			}
		}

		// Handle URL.
		$anchor = $doc->getElementsByTagName( 'a' )[0];
		if ( $anchor ) {
			$this->set_config( 'url', $anchor->getAttribute( 'href' ) );
		}

		return $this;
	}

	/**
	 * Remove one or more url parameter from the item url.
	 *
	 * @param array $params URL parameters.
	 * @return \WP_Components\Pagination_Item
	 */
	public function remove_url_params( array $params = [] ) {

		// If URL isn't empty.
		if ( ! empty( $this->get_config( 'url' ) ) ) {

			// Remove $params from the url.
			$this->set_config(
				'url',
				remove_query_arg(
					$params,
					$this->get_config( 'url' )
				)
			);
		}

		return $this;
	}
}
