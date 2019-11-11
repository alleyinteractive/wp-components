<?php
/**
 * WP_Widget_Sidebar trait.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * WP_Widget_Sidebar trait.
 */
trait WP_Widget_Sidebar {

	/**
	 * Component groups.
	 *
	 * @var array
	 */
	public $wp_widget_sidebar_component_group = '';

	/**
	 * Set and render the sidebar.
	 *
	 * @param int|string $index Optional, default is 1. Index, name or ID of dynamic sidebar.
	 * @return self
	 */
	public function set_sidebar( $index = 1 ) : self {
		add_filter( 'widget_display_callback', [ $this, 'create_component_for_widget' ], 10, 3 );
		dynamic_sidebar( $index );
		remove_filter( 'widget_display_callback', [ $this, 'create_component_for_widget' ] );
		return $this;
	}

	/**
	 * Change the target group/component group for this widget sidebar.
	 *
	 * @param string $group Name of the group.
	 * @return self
	 */
	public function set_sidebar_group( $group ) {
		$this->wp_widget_sidebar_component_group = $group;
		return $this;
	}

	/**
	 * Create a Component for a widget.
	 *
	 * @param array     $instance The current widget instance's settings.
	 * @param WP_Widget $widget   The current widget instance.
	 * @param array     $args     An array of default widget arguments.
	 */
	public function create_component_for_widget( $instance, $widget, $args ) {

		// If this widget has a `create_component` method defined, use that.
		// Short of that, if there's a custom callback defined in the mapping, use that.
		// Fallback to HTML component of the content.
		if ( method_exists( $widget, 'create_component' ) ) {
			$child = $widget->create_component( $args, $instance );
			$this->append_child( $this->wp_widget_sidebar_component_group, $child );
		} elseif ( ! empty( $this->wp_widget_sidebar_get_mapping()[ get_class( $widget ) ] ) ) {
			$callback = $this->wp_widget_sidebar_get_mapping()[ get_class( $widget ) ];
			$child    = call_user_func( [ $this, $callback ], $args, $instance );
			$this->append_child( $this->wp_widget_sidebar_component_group, $child );
		} else {
			// Get title.
			$title = $instance['title'];
			unset( $instance['title'] );

			// Get the, HTML, content only.
			ob_start();
			$widget->widget( $args, $instance );
			$content = ob_get_clean();
			$this->append_child(
				$this->wp_widget_sidebar_component_group,
				( new \WP_Components\HTML() )
					->set_config( 'content', $content ?? '' )
					->set_config( 'title', $title ?? '' )
			);
		}

		// Short-circuit display.
		return false;
	}

	/**
	 * Get the sidebar widget mapping.
	 *
	 * @return array
	 */
	public function wp_widget_sidebar_get_mapping() : array {
		/**
		 * Filter the mapping of widget classes to their callbacks.
		 *
		 * @param array Array of [ `widget_class` => `callback` ]
		 */
		return apply_filters( 'wp_components_widget_sidebar_mapping', [] );
	}
}
