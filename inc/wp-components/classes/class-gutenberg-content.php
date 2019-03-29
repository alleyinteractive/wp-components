<?php
/**
 * Gutenberg Content component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Gutenberg Content.
 */
class Gutenberg_Content extends Component {

	use WP_Post;

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'gutenberg-content';

	/**
	 * Fires after the post object has been set on this class.
	 *
	 * @return self
	 */
	public function post_has_set() : self {

		// If gutenberg is not enabled return the post's content as raw HTML.
		if ( ! function_exists( 'parse_blocks' ) ) {

			// Use a generic HTML component to deliver the post content.
			$this->append_child(
				( new HTML() )
					->set_config(
						'content',
						// phpcs:ignore
						apply_filters( 'the_content', $this->post->post_content ?? '' )
					)
			);
		} else {
			$blocks = (array) parse_blocks( $this->post->post_content ?? '' );

			// Filter any empty parsed blocks.
			$blocks = array_values(
				array_filter(
					$blocks,
					function ( $block ) {
						$block = (array) $block;
						// Check if innerHTML is only whitespace.
						return ! preg_match( '/^\s+$/', $block['innerHTML'] );
					}
				)
			);

			$blocks_as_components = array_reduce( $blocks, [ $this, 'convert_block_to_component' ], [] );
			$this->append_children( $blocks_as_components );
		}

		return $this;
	}

	/**
	 * Map a block array to a Component instance.
	 *
	 * @param array $blocks         Accumulated array of blocks.
	 * @param array $current_block  Current block.
	 * @return object Component instance
	 */
	private function convert_block_to_component( $blocks, $current_block ) : array {
		$block = (array) $current_block;
		/**
		 * Filters array of non-dynamic blocks for which you'd like to bypass the render step (and any core markup)
		 * and render your own markup in React instead.
		 *
		 * @param array $exceptions Array of block render excepctions.
		 */
		$block_render_exceptions = apply_filters(
			'wp_components_block_render_exception',
			[
				'core/columns',
				'core/column',
			]
		);

		if ( empty( $block['blockName'] ) && ! empty( $block['innerHTML'] ) ) {
				// phpcs:ignore
				$blocks[] = ( new HTML() )->set_config( 'content', apply_filters( 'the_content', $block['innerHTML'] ) );
				return $blocks;
		}
		// Handle gutenberg embeds.
		if ( strpos( $block['blockName'] ?? '', 'core-embed' ) === 0 ) {
			$blocks[] = ( new Blocks\Core_Embed() )->set_from_block( $block );
			return $blocks;
		}

		// The presence of html means this is a non-dynamic block.
		if ( ! empty( $block['innerHTML'] ) && ! in_array( $block['blockName'], $block_render_exceptions, true ) ) {
			$last_block = end( $blocks );

			// Render block and clean up extraneous whitespace characters.
			$content = render_block( $block );
			$content = preg_replace( '/[\r\n\t\f\v]/', '', $content );

			// Merge rendered static blocks into a single HTML component.
			if ( $last_block instanceof HTML ) {
				$last_block->set_config(
					'content',
					$last_block->get_config( 'content' ) . $content
				);
			} else {
				$blocks[] = ( new HTML() )->set_config( 'content', $content );
			}

			return $blocks;
		}

		// Handle nested blocks.
		$children_blocks_as_components = array_reduce(
			(array) ( $block['innerBlocks'] ?? [] ),
			[ $this, 'convert_block_to_component' ],
			[]
		);

		// A dynamic block. All attributes will be available.
		// @todo perhaps eventually allow dynamic creation of a block-specific class with a "prepare_config" function or something.
		$blocks[] = ( new Component() )
			->set_name( $block['blockName'] ?? '' )
			->merge_config( $block['attrs'] ?? [] )
			->append_children( $children_blocks_as_components );

		return $blocks;
	}
}
