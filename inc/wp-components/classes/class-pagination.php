<?php
/**
 * Pagination component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Pagination.
 */
class Pagination extends Component {

	use WP_Query;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'pagination';

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [
			'base_url'             => '',
			'range_end'            => 0,
			'range_start'          => 0,
			'total'                => 0,
			'url_params_to_remove' => [],
		];
	}

	/**
	 * Hook into query being set.
	 *
	 * @return self
	 */
	public function query_has_set() : self {
		// Get the pagination links for the query.
		$pagination_links = $this->get_pagination_links();

		// Convert each HTML link to a Pagination_Item.
		if ( ! empty( $pagination_links ) ) {
			foreach ( $pagination_links as $link_html ) {
				$this->append_child(
					// Create a new pagination item using anchor HTML, and remove various url params.
					( new Pagination_Item() )
						->set_from_html( $link_html )
						->remove_url_params(
							(array) $this->get_config( 'url_params_to_remove' )
						)
				);
			}
		}

		// Figure out the search result meta info.
		$posts_per_page = absint( $this->query->get( 'posts_per_page' ) );
		$page = absint( $this->query->get( 'paged' ) );
		if ( $page < 1 ) {
			$page = 1;
		}

		$this->set_config( 'range_end', $page * $posts_per_page );
		$this->set_config(
			'range_start',
			( $this->get_config( 'range_end' ) - $posts_per_page + 1 )
		);
		$this->set_config( 'total', absint( $this->query->found_posts ?? 0 ) );

		// Ensure the range isn't larger than the total.
		if ( $this->get_config( 'range_end' ) > $this->get_config( 'total' ) ) {
			$this->set_config( 'range_end', absint( $this->get_config( 'total' ) ) );
		}

		return $this;
	}

	/**
	 * We need to carefully insert the Irving query as the global query so
	 * the various core functions reference the correct query.
	 *
	 * @return array
	 */
	public function get_pagination_links() : array {
		global $wp_query;

		// Get the current global object and replace with our current query.
		$current_global_wp_query = $wp_query;

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride
		$wp_query = $this->query;

		// Set the links as an array of HTML elements.
		$links = paginate_links(
			[
				'base' => $this->get_config( 'base_url' ) . '%_%',
				'type' => 'array',
			]
		);

		// Set the global wp_query to what it originally was.
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride
		$wp_query = $current_global_wp_query;

		return (array) $links;
	}
}
