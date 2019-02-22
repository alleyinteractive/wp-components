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
	 */
	public function post_has_set() {

		// If gutenberg is not enabled return the post's content as raw HTML.
		if ( ! function_exists( 'parse_blocks' ) ) {

			// Use a generic HTML component to deliver the post content.
			$this->append_child(
				( new HTML() )
					->set_config(
						'content',
						apply_filters( 'the_content', $this->post->post_content )
					)
			);

		} else {
			$blocks = (array) parse_blocks( $this->post->post_content );

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

			$blocks_as_components = array_map( [ $this, 'convert_block_to_component' ], $blocks );
			$this->append_children( $blocks_as_components );
		}

		return $this;
	}


	/**
	 * Map a block array to a Component instance.
	 *
	 * @todo: Create "columns" and "column" components that can handle placing child blocks within the wrapping markup.
	 *
	 * @param array $block A parsed block associative array.
	 * @return Component
	 */
	private function convert_block_to_component( $block ) {

		$block = (array) $block;

		// Handle gutenberg embeds.
		if ( strpos( $block['blockName'] ?? '', 'core-embed' ) === 0 ) {
			return ( new Blocks\Core_Embed() )->set_from_block( $block );
		}

		// The presence of html means this is a non dynamic block.
		if ( ! empty( $block['innerHTML'] ) ) {
			$content = $block['innerHTML'];

			// Missing blockName means it's a "classic" block, run the_content.
			if ( empty( $block['blockName'] ) ) {
				$content = apply_filters( 'the_content', $content );
			}

			// Clean up extraneous whitespace characters.
			$content = preg_replace( '/[\r\n\t\f\v]/', '', $content );

			// Handle nested blocks.
			$children_blocks_as_components = array_map(
				[ $this, 'convert_block_to_component' ],
				(array) ( $block['innerBlocks'] ?? [] )
			);

			return ( new HTML() )
				->merge_config( $block['attrs'] ?? [] )
				->set_config( 'content', $content )
				->append_children( $children_blocks_as_components );
		}

		// Handle nested blocks.
		$children_blocks_as_components = array_map(
			[ $this, 'convert_block_to_component' ],
			(array) ( $block['innerBlocks'] ?? [] )
		);

		// A dynamic block. All attributes will be available.
		( new Component() )
			->set_name( $block['blockName'] ?? '' )
			->merge_config( $block['attrs'] ?? [] )
			->append_children( $children_blocks_as_components );
	}
}
