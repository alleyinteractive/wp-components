<?php
/**
 * Core Embed component.
 *
 * @package WP_Components
 */

namespace WP_Components\Blocks;

/**
 * Core Embed.
 */
class Core_Embed extends \WP_Components\Component {

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'core-embed';

	/**
	 * Container for filtered scripts from embed html.
	 *
	 * @var array
	 */
	private static $scripts = [];

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [
			'content'  => '',
			'oembed'   => true,
			'provider' => '',
			'rich'     => true,
		];
	}


	/**
	 * Add a script to the container.
	 *
	 * @param array $script_attrs The attributes of a script tag.
	 */
	public static function add_script( array $script_attrs ) {
		// Avoid duplicated script tags.
		foreach ( static::$scripts as $script ) {
			if ( $script['src'] === $script_attrs['src'] ) {
				return;
			}
		}

		static::$scripts[] = $script_attrs;
	}

	/**
	 * Get all gathered scripts, and clear the container.
	 *
	 * @return array
	 */
	public static function get_scripts() : array {
		$scripts = static::$scripts;
		static::$scripts = [];
		return $scripts;
	}

	/**
	 * Setup this compopnent using a parsed Gutenberg block.
	 *
	 * @param array|object $block The saved markup of an embed block.
	 * @return \WP_Components\Blocks\Core_Embed
	 */
	public function set_from_block( $block ) : self {

		// Typecast to handle differences between Gutenberg versions.
		$block          = (array) $block;
		$block['attrs'] = (array) ( $block['attrs'] ?? [] );
		$html           = wp_oembed_get( $block['attrs']['url'] ?? '' );

		if ( false === $html ) {
			return $this;
		}

		// Prep HTML parsing.
		$doc = new \DOMDocument();

		// Ignore errors related to HTML5 tags being parsed.
		libxml_use_internal_errors( true );
		$doc->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$script_elements = $doc->getElementsByTagName( 'script' );

		// Extract scripts from raw HTML and save them to be rendered separately.
		foreach ( $script_elements as $script ) {
			static::add_script(
				[
					'src'   => $script->getAttribute( 'src' ),
					'defer' => $script->hasAttribute( 'defer' ),
					'async' => $script->hasAttribute( 'async' ),
				]
			);
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$content = str_replace( $block['attrs']['url'], $doc->saveHTML(), html_entity_decode( $block['innerHTML'] ) );

		// Restore original error optional value.
		libxml_use_internal_errors( false );

		$this->set_config( 'provider', $block['attrs']['providerNameSlug'] ?? '' );
		$this->set_config( 'content', $content );

		return $this;
	}
}
