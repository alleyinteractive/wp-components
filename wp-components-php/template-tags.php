<?php
/**
 * Shortcut helpers for component PHP templates
 *
 * @package WP_Component
 */

namespace WP_Component\PHP;

/**
 * Helper for rendering a component
 *
 * @param \WP_Component\Component $component_instance Instance of a component (or template) to render
 * @param bool                    $return             Should this component's markup be returned instead of
 *                                                    printed?
 * @param bool|array              $cache              If set, the template part will be cached. If
 *                                                    true, results will be cached for
 *                                                    {@see Render_Controller::$default_cache_ttl} and
 *                                                    the transient will be generated from this
 *                                                    variable. Optionally, either can be set by
 *                                                    passing an array with 'key' and/or 'ttl' keys.
 */
function render( $component_instance ) {
	Render_Controller::instance()->render( $component_instance );
}

/**
 * Helper for rendering and caching a component
 *
 * @param \WP_Component\Component $component_instance Instance of a component (or template) to render
 * @param bool|array              $cache              If set, the template part will be cached. If
 *                                                    true, results will be cached for
 *                                                    {@see Render_Controller::$default_cache_ttl} and
 *                                                    the transient will be generated from this
 *                                                    variable. Optionally, either can be set by
 *                                                    passing an array with 'key' and/or 'ttl' keys.
 */
function render_cached( $component_instance, $cache = true ) {
	Render_Controller::instance()->render( $component_instance, false, $cache );
}

/**
 * Return the markup of a component template
 *
 * @param \Wp_Component\Component $component_instance Instance of a component (or template) to render
 */
function return_template( $component_instance ) {
	return Render_Controller::instance()->render( $component_instance, true );
}

/**
 * Get the current component instance
 */
function get_component() {
	return Render_Controller::instance()->current_renderable->component_instance;
}

/**
 * Get a config value from the current component instance
 *
 * @param string $key Config key
 */
function get_config( $key ) {
	return Render_Controller::instance()->current_renderable->component_instance->get_config( $key );
}

/**
 * Get the current component instance's child components
 */
function get_children() {
	return Render_Controller::instance()->current_renderable->component_instance->children;
}

/**
 * Get the current component instance's child components
 *
 * @param array $children An array of children to render.
 */
function render_children( $children = [] ) {
	return Render_Controller::instance()->render_children( $children );
}

/**
 * Filter children based on a config value
 *
 * @param string $key   The config key to access
 * @param string $value The value to check against.
 * @return \Wp_Component\Component
 */
function filter_children( $key, $value ) {
	return array_values(
		array_filter( get_children(), function( $child ) use ( $key, $value ) {
			return $value === $child->get_config( $key );
		} )
	);
};

/**
 * Filter children based on name
 *
 * @param string $name The name of the component
 * @return \Wp_Component\Component
 */
function filter_children_by_name( $name ) {
	return array_values(
		array_filter( get_children(), function( $child ) use ( $name ) {
			return $name === $child->name;
		} )
	);
};

/**
 * Find a single child component based on a config value
 *
 * @param string $key   The config key to access
 * @param string $value The value to check against.
 * @return \Wp_Component\Component
 */
function find_child( $key, $value ) {
	$children = filter_children( $key, $value );

	if ( ! empty( $children ) ) {
		return array_pop( $children );
	}

	return false;
};

/**
 * Find a single child component by name
 *
 * @param string $name The name of the component
 * @return \Wp_Component\Component
 */
function find_child_by_name( $name ) {
	$children = filter_children_by_name( $name );

	if ( ! empty( $children ) ) {
		return array_pop( $children );
	}

	return false;
};
