<?php
/**
 * Render_Controller class file.
 *
 * @package WP_Component
 */

namespace WP_Component\PHP;

/**
 * Render Controller.
 */
class Render_Controller {

	/**
	 * Set the default cache TTL to 15 minutes for cached partials.
	 *
	 * @var integer
	 */
	public $default_cache_ttl = 900;

	/**
	 * Holds references to the singleton instances.
	 *
	 * @var array
	 */
	private static $instance;

	/**
	 * Unused.
	 */
	private function __construct() {
		// Don't do anything, needs to be initialized via instance() method.
	}

	/**
	 * Get an instance of the class.
	 *
	 * @return Render_Controller
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	/**
	 * The template stack.
	 *
	 * @var array
	 */
	public $stack = array();

	/**
	 * The currently-active renderable.
	 *
	 * @var Renderable
	 */
	public $current_renderable;

	/**
	 * Render a component and its children
	 *
	 * @param \Wp_Component\Component $component_instance Instance of a component (or template) to render
	 * @param bool                    $return             Should this component's markup be return instead of
	 *                                                    printed?
	 * @param bool|array              $cache              If set, the template part will be cached. If
	 *                                                    true, results will be cached for
	 *                                                    {@see Render_Controller::$default_cache_ttl} and
	 *                                                    the transient will be generated from this
	 *                                                    variable. Optionally, either can be set by
	 *                                                    passing an array with 'key' and/or 'ttl' keys.
	 */
	public function render( $component_instance, $return = false, $cache = false ) {
		if ( $cache ) {

			if ( is_bool( $cache ) ) {
				$cache = array();
			}

			// If no key was provided, make one.
			if ( empty( $cache['key'] ) ) {
				$cache['key'] = self::cache_key( $args );
			}

			// If no TTL was supplied, set a default.
			if ( ! isset( $cache['ttl'] ) ) {
				$cache['ttl'] = $this->default_cache_ttl;
			}

			// If we have a cache hit, serve it.
			$renderable = get_transient( $cache['key'] );
			if ( false !== $renderable ) {
				if ( $return ) {
					return $renderable;
				} else {
					echo $renderable;
					return;
				}
			}
		}

		if ( $return || $cache ) {
			ob_start();
		}

		$results = $this->render_instance( $component_instance, $return );

		// If we are returning or caching, kill the output buffer.
		if ( $return || $cache ) {
			$contents = ob_get_clean();

			if ( $cache ) {
				set_transient( $cache['key'], $contents, $cache['ttl'] );
			}

			if ( $return ) {
				return $contents;
			} else {
				echo $contents; // wpcs: xss ok.
			}
		}
	}

	/**
	 * Render an individual component's template
	 *
	 * @see Partial::render().
	 * @see Render_Controller::push().
	 * @see Render_Controller::pop().
	 *
	 * @param \Wp_Component\Component $component_instance {@see Render_Controller::render()}.
	 * @param bool                    $return             {@see Render_Controller::render()}.
	 */
	public function render_instance( $component_instance, $return ) {
		$name = $component_instance->name;
		$this->push( new Renderable( $component_instance, $return ) );

		// Render component markup and assets
		$this->current_renderable->render_css();
		$this->current_renderable->render_js();
		$results = $this->current_renderable->render();

		$this->pop();
		return $results;
	}

	/**
	 * Render an array of components, by default the current component's children
	 *
	 * @param array $children An array of children to render.
	 */
	public function render_children( $children = [] ) {
		$contents = '';

		// Use current renderable if provided child array is empty
		if ( empty( $children ) ) {
			$children = $this->current_renderable->component_instance->children;
		}

		// If we don't still have an empty array, render all children
		if ( ! empty( $children ) ) {
			foreach ( $children as $child ) {
				$contents = $this->render( $child );
			}
		}

		return $contents;
	}

	/**
	 * Push a renderable onto the stack and set it as the current renderable.
	 *
	 * @param Renderable $renderable The renderable we're loading.
	 */
	protected function push( $renderable ) {
		$this->stack[] = $renderable;
		$this->current_renderable = $renderable;
	}

	/**
	 * Pop a renderable off the top of the stack and set the current renderable to the
	 * next one down.
	 */
	protected function pop() {
		array_pop( $this->stack );
		$this->current_renderable = end( $this->stack );
	}

	/**
	 * Generate a cache key from arbitrary arguments.
	 *
	 * @param  mixed $args Arguments to md5 into a cache key.
	 * @return string
	 */
	public static function cache_key( $args ) {
		return 'partial_' . md5( serialize( $args ) );
	}
}
