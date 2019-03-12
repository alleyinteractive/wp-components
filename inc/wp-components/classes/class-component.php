<?php
/**
 * Component class.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Component.
 */
class Component implements \JsonSerializable {

	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * Component config.
	 *
	 * @var array
	 */
	public $config = [];

	/**
	 * Component children.
	 *
	 * @var array
	 */
	public $children = [];

	/**
	 * Determine which config keys should be passed into result.
	 *
	 * @var array
	 */
	public $whitelist = [];

	/**
	 * Determine which config keys should not be transformed into camelCase.
	 *
	 * NOTE: this will not prevent the key itself from being camelcased,
	 * only the keys of the config value if it is an associative array.
	 *
	 * @var array
	 */
	public $preserve_inner_keys = [];

	/**
	 * Flag to determine if the component has encountered a "fatal" error.
	 * Rather than returning `null`, we set this flag so that the component can
	 * be removed during the `to_array()` or render equivalent function. This
	 * approach allows us to preserve method chaining without using an
	 * `optional()` helper or similar functionality.
	 *
	 * @var boolean
	 */
	public $is_valid = true;

	/**
	 * Component constructor.
	 */
	public function __construct() {
		$this->config   = $this->default_config();
		$this->children = $this->default_children();
	}

	/**
	 * Helper to change a components name.
	 *
	 * @param  string $name New component name.
	 * @return self
	 */
	public function set_name( string $name ) : self {
		$this->name = $name;
		return $this;
	}

	/**
	 * Define a default config.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [];
	}

	/**
	 * Helper to set a top level config value.
	 *
	 * @param array|string $key   Config key or entire config array.
	 * @param mixed        $value Config value.
	 * @return self
	 */
	public function set_config( $key, $value = null ) : self {
		if ( is_array( $key ) && is_null( $value ) ) {
			$this->config = $key;
		} else {
			$this->config[ $key ] = $value;
		}
		return $this;
	}

	/**
	 * Helper to set a top level config value.
	 *
	 * @param string $key Config key.
	 * @return mixed Config value or null.
	 */
	public function get_config( $key ) {
		if ( array_key_exists( $key, $this->config ) ) {
			return $this->config[ $key ];
		}
		return null;
	}

	/**
	 * Merge new values into the current config.
	 *
	 * @param array $new_config Config array to merge in.
	 * @return self
	 */
	public function merge_config( array $new_config ) : self {
		$this->config = wp_parse_args( $new_config, $this->config );
		return $this;
	}

	/**
	 * Define default children.
	 *
	 * @return array Default children.
	 */
	public function default_children() : array {
		return [];
	}

	/**
	 * Helper to set children components.
	 *
	 * @param  array   $children Children for this component.
	 * @param  boolean $append   Append children to existing children.
	 * @return self
	 */
	public function set_children( array $children, $append = false ) : self {
		if ( $append ) {
			$this->children = array_merge(
				$this->children,
				array_filter( $children )
			);
		} else {
			$this->children = array_filter( $children );
		}
		return $this;
	}

	/**
	 * Append an array of components to the children array.
	 *
	 * @param array $children Array of components.
	 * @return self
	 */
	public function append_children( array $children ) : self {
		if ( ! empty( $children ) ) {
			$children = array_filter( $children );
			$this->children = array_merge( $this->children, $children );
		}
		return $this;
	}

	/**
	 * Prepend an array of components to the children array.
	 *
	 * @param array $children Array of components.
	 * @return self
	 */
	public function prepend_children( array $children ) : self {
		if ( ! empty( $children ) ) {
			$children = array_filter( $children );
			$this->children = array_merge( $children, $this->children );
		}
		return $this;
	}

	/**
	 * Append a component to the children array.
	 *
	 * @param Component $child Child component.
	 * @return self
	 */
	public function append_child( $child ) : self {
		if ( ! empty( $child ) ) {
			if ( is_array( $child ) ) {
				$child = $child[0];
			}
			array_push( $this->children, $child );
		}
		return $this;
	}

	/**
	 * Prepend a component to the children array.
	 *
	 * @param Component $child Child component.
	 * @return self
	 */
	public function prepend_child( $child ) : self {
		if ( ! empty( $child ) ) {
			if ( is_array( $child ) ) {
				$child = $child[0];
			}
			array_unshift( $this->children, $child );
		}
		return $this;
	}

	/**
	 * Render the frontend component.
	 */
	public function render() {
		// Override me.
	}

	/**
	 * Execute a function on each child of this component.
	 *
	 * @param callable $callback Callback function.
	 * @return self
	 */
	public function children_callback( $callback ) : self {
		$this->children = array_map( $callback, $this->children );
		return $this;
	}

	/**
	 * Helper to set this component's is_valid flag to false.
	 *
	 * @return self
	 */
	public function set_invalid() : self {
		$this->is_valid = false;
		return $this;
	}

	/**
	 * Helper to set this component's is_valid flag to true.
	 *
	 * @return self
	 */
	public function set_valid() : self {
		$this->is_valid = true;
		return $this;
	}

	/**
	 * Trigger a fatal error on this component and log a message.
	 *
	 * @param string $error_message Optional error message.
	 * @return self
	 */
	public function has_error( $error_message = '' ) : self {

		// If a message exists and WP debugging is enabled for logging.
		if (
			! empty( $error_message )
			&& defined( 'WP_DEBUG' )
			&& WP_DEBUG
			&& defined( 'WP_DEBUG_LOG' )
			&& WP_DEBUG_LOG
		) {
			error_log( $error_message );
		}
		return $this->set_invalid();
	}

	/**
	 * Helper to set theme on this component
	 *
	 * @param string $theme_name Name of theme to set.
	 * @return self
	 */
	public function set_theme( $theme_name ) : self {
		$this->set_config( 'theme_name', $theme_name );
		return $this;
	}

	/**
	 * Helper to recursively set themes on child components.
	 *
	 * @param array $theme_mapping Array in which keys are component $name properties and values are the theme to use for that component.
	 * @return self
	 */
	public function set_child_themes( $theme_mapping ) : self {
		$component_names = array_keys( $theme_mapping );

		// Recursively set themes for children.
		if ( ! empty( $this->children ) ) {
			foreach ( $this->children as $child ) {
				if ( ! empty( $theme_mapping[ $child->name ] ) ) {
					$child->set_config( 'theme_name', $theme_mapping[ $child->name ] );
				}

				$child->set_child_themes( $theme_mapping );
			}
		}

		return $this;
	}

	/**
	 * Helper to output this class as an array.
	 *
	 * @return array
	 */
	public function to_array() : array {

		// For invalid components, append `-invalid` to the name to indicate
		// that there was a fatal error and it should not be rendered. This
		// approach allows us to still see the data in the endpoint for
		// debugging purposes (and even create a fallback compopnent if
		// desired).
		if ( ! $this->is_valid ) {
			$this->name = $this->name . '-invalid';
		}

		return [
			'name'     => $this->name,
			'config'   => (object) $this->camel_case_keys( $this->config ),
			'children' => array_filter( $this->children ),
		];
	}

	/**
	 * Convert all array keys to camel case.
	 *
	 * @param array $array        Array to convert.
	 * @param array $array_holder Parent array holder for recursive array.
	 * @return array Updated array with camel-cased keys.
	 */
	public function camel_case_keys( $array, $array_holder = [] ) : array {

		// Setup for recursion.
		$camel_case_array = ! empty( $array_holder ) ? $array_holder : [];

		// Loop through each key.
		foreach ( $array as $key => $value ) {
			// Only return keys that are white-listed. Leave $whitelist empty
			// to disable.
			if (
				! empty( $this->whitelist )
				&& ! in_array( $key, $this->whitelist, true )
			) {
				unset( $array[ $key ] );
				continue;
			}

			// Explode each part by underscore.
			$words = explode( '_', $key );

			// Capitalize each key part.
			array_walk(
				$words,
				function( &$word ) {
					$word = ucwords( $word );
				}
			);

			// Reassemble key.
			$new_key = implode( '', $words );

			// Lowercase the first character.
			$new_key[0] = strtolower( $new_key[0] );

			if (
				! is_array( $value )
				// Don't recursively camelCase if this key is in the $preserve_inner_keys property.
				|| ( ! empty( $this->preserve_inner_keys ) && in_array( $key, $this->preserve_inner_keys, true ) )
			) {
				// Set new key value.
				$camel_case_array[ $new_key ] = $value;
			} else {
				// Set new key value, but process the nested array.
				$camel_case_array[ $new_key ] = $this->camel_case_keys( $value, $camel_case_array[ $new_key ] );
			}
		}

		return $camel_case_array;
	}

	/**
	 * Use custom to_array method when component is serialized for API response.
	 *
	 * @return array
	 */
	public function jsonSerialize() : array {
		return $this->to_array();
	}
}
