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
	 * @todo: Create "columns" and "column" components that can handle placing child blocks within the wrapping markup.
	 *
	 * @param array $blocks         Accumulated array of blocks.
	 * @param array $current_block  Current block.
	 * @return object Component instance
	 */
	private function convert_block_to_component( $blocks, $current_block ) : array {
		$block = (array) $current_block;

		// Handle gutenberg embeds.
		if ( strpos( $block['blockName'] ?? '', 'core-embed' ) === 0 ) {
			$blocks[] = ( new Blocks\Core_Embed() )->set_from_block( $block );
			return $blocks;
		}

		// The presence of html means this is a non dynamic block.
		if ( ! empty( $block['innerHTML'] ) ) {
			$content = $this->get_block_html_content( $block );
			$last_block = end( $blocks );

			// Merge non-dynamic block content into a single HTML component.
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
		$children_blocks_as_components = array_map(
			[ $this, 'convert_block_to_component' ],
			(array) ( $block['innerBlocks'] ?? [] )
		);

		// A dynamic block. All attributes will be available.
		$blocks[] = ( new Component() )
			->set_name( $block['blockName'] ?? '' )
			->merge_config( $block['attrs'] ?? [] )
			->append_children( $children_blocks_as_components );

		return $blocks;
	}

	/**
	 * Map a block array to a Component instance.
	 *
	 * @todo: Create "columns" and "column" components that can handle placing child blocks within the wrapping markup.
	 *
	 * @param array $block Gutenberg block from which to retrieve HTMl content.
	 * @return string HTMl content of gutenberg block.
	 */
	public function get_block_html_content( $block ) : string {
		$content = '';
		$inner_blocks = $block['innerBlocks'];

		// Loop through inner content if it's not empty.
		if ( ! empty( $block['innerContent'] ) ) {
			foreach ( $block['innerContent'] as $inner_content ) {
				// Add inner content item if it's not empty, otherwise add content of inner blocks.
				if ( ! empty( $inner_content ) ) {
					$content .= $inner_content;
				} else if ( ! empty( $inner_blocks ) ) {
					$content .= $this->get_block_html_content( array_shift( $inner_blocks ) );
				}
			}
		} else {
			$content = $block['innerHTML'];
		}

		// Missing blockName means it's a "classic" block, run the_content.
		if ( empty( $block['blockName'] ) ) {
			$content = apply_filters( 'the_content', $content );
		}

		// Clean up extraneous whitespace characters.
		$content = preg_replace( '/[\r\n\t\f\v]/', '', $content );

		return $content;
	}
}
