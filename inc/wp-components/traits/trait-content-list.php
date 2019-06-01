<?php
/**
 * Content_List trait.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Content_List trait.
 */
trait Content_List {

	/**
	 * Get content list items.
	 *
	 * @param array $post_ids Array of post IDs or post objects.
	 * @return array
	 */
	public function get_content_list_items( array $post_ids ) : array {

		// Classes using this trait should implement this method that returns a
		// content list item initialized by post ID.
		if ( ! method_exists( $this, 'get_content_list_item' ) ) {
			return [];
		}

		return array_filter(
			array_map(
				[ $this, 'get_content_list_item' ],
				$post_ids
			)
		);
	}

	/**
	 * Parse an array of post IDs to be used by this component.
	 *
	 * @param array   $ids           Post IDs.
	 * @param integer $backfill_to   How many content items should this component
	 *                               have.
	 * @param array   $backfill_args WP_Query arguments for the backfill.
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function parse_from_post_ids( array $ids, $backfill_to = 0, $backfill_args = [] ) : self {

		// Backfill as needed.
		$content_item_ids = $this->backfill_content_item_ids(
			$ids,
			$backfill_to,
			$backfill_args
		);

		$this->append_children( static::get_content_list_items( $content_item_ids ) );

		return $this;
	}

	/**
	 * Parse a WP_Query object to be used by this component.
	 *
	 * @param \WP_Query $wp_query      \WP_Query object.
	 * @param integer   $backfill_to   How many content items should this component
	 *                                 have.
	 * @param array     $backfill_args WP_Query arguments for the backfill.
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function parse_from_wp_query( \WP_Query $wp_query, $backfill_to = 0, $backfill_args = [] ) : self {

		// Extract the post ids from the wp_query to be used in this content list.
		$post_ids = wp_list_pluck( $wp_query->posts ?? [], 'ID' );

		$this->parse_from_post_ids(
			$post_ids,
			$backfill_to,
			$backfill_args
		);

		return $this;
	}

	/**
	 * Setup the content items based on Jetpack Related Posts results.
	 *
	 * @param integer $post_id       Post ID.
	 * @param integer $backfill_to   How many content items should this component
	 *                               have.
	 * @param array   $backfill_args WP_Query arguments for the backfill.
	 * @return object Instance of the class this trait is implemented on.
	 */
	public function parse_from_jetpack_related( $post_id, $backfill_to = 0, $backfill_args = [] ) : self {

		$content_item_ids = [];

		if (
			class_exists( '\Jetpack_RelatedPosts' )
			&& method_exists( '\Jetpack_RelatedPosts', 'init_raw' )
		) {

			// Query Jetpack Related Posts.
			$related_content = (array) \Jetpack_RelatedPosts::init_raw()
				->get_for_post_id(
					$post_id,
					[
						'size' => $backfill_to,
					]
				);

			// Extract IDs from results.
			if ( ! empty( $related_content ) ) {
				$content_item_ids = wp_list_pluck( $related_content, 'id' );
			}
		}

		$this->parse_from_post_ids(
			$content_item_ids,
			$backfill_to,
			$backfill_args
		);

		return $this;
	}

	/**
	 * Backfill an array of post ids.
	 *
	 * @param array   $content_item_ids Array of post ids.
	 * @param integer $backfill_to      Amount of content needed.
	 * @param array   $backfill_args    Arguments for WP_Query.
	 * @return array
	 */
	public function backfill_content_item_ids( array $content_item_ids = [], $backfill_to = 0, $backfill_args = [] ) {

		// Backfill is disabled, or unnecessary.
		if ( 0 === $backfill_to || $backfill_to <= count( $content_item_ids ) ) {
			return $content_item_ids;
		}

		// Modify backfill args.
		$backfill_args['post__not_in']   = $content_item_ids;
		$backfill_args['posts_per_page'] = $backfill_to - count( $content_item_ids );
		$backfill_args['fields']         = 'ids';

		$backfill_query = $this->get_backfill_wp_query( $backfill_args );

		if ( ! empty( $backfill_query->posts ?? [] ) ) {
			$content_item_ids = array_merge( $content_item_ids, $backfill_query->posts );
		}

		return $content_item_ids;
	}

	/**
	 * Wrapper method for executing a backfill query. Allows for easy
	 * overriding.
	 *
	 * @param array $args \WP_Query args.
	 * @return mixed WP_Query object (or something that works like WP_Query).
	 */
	public function get_backfill_wp_query( $backfill_args ) {
		return new \WP_Query( $backfill_args );
	}
}
