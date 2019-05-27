<?php
/**
 * Image component.
 *
 * @package WP_Components
 */

namespace WP_Components;

/**
 * Image
 */
class Image extends Component {
	/**
	 * Unique component slug.
	 *
	 * @var string
	 */
	public $name = 'image';

	/**
	 * Image sizes.
	 *
	 * @var array
	 */
	public static $sizes = [];

	/**
	 * Media queries for sizes attribute or source tags.
	 *
	 * @var array
	 */
	public static $breakpoints = [];

	/**
	 * WPCOM Thumbnail Editor Sizes
	 *
	 * @var array
	 */
	public static $crop_sizes = [];

	/**
	 * Define the default config of an image.
	 *
	 * @return array Default config.
	 */
	public function default_config() : array {
		return [
			'aspect_ratio'       => 9 / 16,
			'attachment_id'      => 0,
			'alt'                => '',
			'caption'            => '',
			'crops'              => '',
			'fallback_image_url' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
			'height'             => 0,
			'image_size'         => 'full',
			'lazyload'           => true,
			'lqip_src'           => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
			'post_id'            => 0,
			'retina'             => true,
			'show_caption'       => false,
			'sources'            => [],
			'source_tags'        => [],
			'src'                => '',
			'srcset'             => '',
			'url'                => '',
			'use_basic_img'      => false,
			'using_fallback'     => false,
			'width'              => 0,
		];
	}

	/**
	 * Register sizes at once.
	 *
	 * @param array $sizes Array of arguments for register_size.
	 */
	public static function register_sizes( array $sizes ) {
		self::$sizes = array_merge( self::$sizes, $sizes );
	}

	/**
	 * Register breakpoints.
	 *
	 * @param array $breakpoints Array of breakpoints.
	 */
	public static function register_breakpoints( array $breakpoints ) {
		self::$breakpoints = array_merge( self::$breakpoints, $breakpoints );
	}

	/**
	 * Register crops for WPCOM Thumbnail Editor.
	 *
	 * @param  array $crop_sizes Array of crop sizes.
	 */
	public static function register_crop_sizes( array $crop_sizes ) {
		self::$crop_sizes = array_merge( self::$crop_sizes, $crop_sizes );

		// Register image sizes.
		add_action(
			'after_setup_theme',
			function() {
				foreach ( Image::$crop_sizes as $crop_size ) {
					foreach ( $crop_size as $key => $params ) {
						$params = wp_parse_args(
							$params,
							[
								'crop'   => false,
								'height' => 0,
								'width'  => 0,
							]
						);
						add_image_size( $key, $params['width'], $params['height'], $params['crop'] );
					}
				}
			}
		);

		// Setup WPCOM Thumbnail Editor image ratio map.
		add_filter(
			'wpcom_thumbnail_editor_args',
			function( $args ) {
				$mapping = [];
				foreach ( Image::$crop_sizes as $key => $crop_size ) {
					$mapping[ $key ] = $mapping[ $key ] ?? [];
					$mapping[ $key ] = array_merge( $mapping[ $key ], array_keys( $crop_size ) );
				}
				$args['image_ratio_map'] = $mapping;
				return $args;
			}
		);
	}

	/**
	 * Setup this component using a post.
	 *
	 * @param int $post_id Post ID.
	 * @return Component Current instance of this class.
	 */
	public function set_post_id( $post_id ) {
		// Get the URL.
		$attachment_id = get_post_thumbnail_id( $post_id );
		$this->set_config( 'post_id', $post_id );

		return $this->set_attachment_id( $attachment_id );
	}

	/**
	 * Setup this component using an attachment.
	 *
	 * @param int $attachment_id Attachemnt ID.
	 * @return Component Current instance of this class.
	 */
	public function set_attachment_id( $attachment_id ) {
		$this->set_config( 'attachment_id', absint( $attachment_id ) );

		// Get crops from post meta.
		$crops = (array) get_post_meta( $attachment_id, 'wpcom_thumbnail_edit', true );
		$this->set_config( 'crops', array_filter( $crops ) );

		return $this;
	}

	/**
	 * Set the URL.
	 *
	 * @param string $url Image URL.
	 * @return Component Current instance of this class.
	 */
	public function set_url( string $url ) {
		$this->set_config( 'url', $url );
		return $this;
	}

	/**
	 * Set alt text for image.
	 *
	 * @param string $alt Alt text for image.
	 * @return Component Current instance of this class.
	 */
	public function alt( string $alt ) {
		$this->set_config( 'alt', $alt );
		return $this;
	}

	/**
	 * Loads a predefined array of settings from the static sizes array.
	 *
	 * @param string $image_size Key of the size.
	 * @param bool   $picture Whether or not to use a <picture> element.
	 * @return Component Current instance of this class.
	 */
	public function set_config_for_size( string $image_size, $picture = false ) {
		$sizes       = self::$sizes;
		$size_config = [];
		$crops       = $this->config['crops'];

		if ( empty( $sizes[ $image_size ] ) ) {
			// Return empty component if missing image size or URL.
			$this->config['src'] = $this->config['url'];
			return $this;
		} else {
			$size_config        = $sizes[ $image_size ];
			$fallback_image_url = $size_config['fallback_image_url'] ?? $this->config['fallback_image_url'];
			$attachment_url     = strtok( wp_get_attachment_image_url( $this->get_config( 'attachment_id' ), 'full' ), '?' );
			$url                = ! empty( $attachment_url ) ? $attachment_url : $fallback_image_url;

			$this->merge_config(
				[
					'image_size'         => $image_size,
					'sources'            => $size_config['sources'],
					'retina'             => $size_config['retina'] ?? $this->config['retina'],
					'lazyload'           => $size_config['lazyload'] ?? $this->config['lazyload'],
					'fallback_image_url' => $fallback_image_url,
					'url'                => $url,
				]
			);
		}

		// Set aspect ratio.
		$this->set_config( 'aspect_ratio', $this->get_aspect_ratio( $size_config ) );

		// If the size key matches a crop option, apply that transform.
		if ( ! empty( $crops[ $image_size ] ) ) {
			$sources = $this->config['sources'];
			foreach ( $sources as &$source ) {
				// Convert stored coordinates into crop friendly parameters.
				$source['transforms'] = array_merge(
					[
						'crop' => [
							$crops[ $image_size ][0] . 'px',
							$crops[ $image_size ][1] . 'px',
							( $crops[ $image_size ][2] - $crops[ $image_size ][0] ) . 'px',
							( $crops[ $image_size ][3] - $crops[ $image_size ][1] ) . 'px',
						],
					],
					$source['transforms']
				);
			}
			$this->set_config( 'sources', $sources );
		}

		$this->configure( $picture );

		return $this;
	}

	/**
	 * Prepare config for use with an <img> or <picture> tag.
	 *
	 * @param bool $picture Whether or not to use a <picture> element.
	 * @return Component Current instance of this class.
	 */
	public function configure( $picture ) {
		$image_meta = wp_get_attachment_metadata( $this->config['attachment_id'] );

		// Set flags for using a basic <img> tag or using the fallback URL.
		$this->merge_config(
			[
				'use_basic_img'  => ( 1 === count( $this->get_config( 'sources' ) ) && ! $this->get_config( 'retina' ) ),
				'using_fallback' => $this->get_config( 'fallback_image_url' ) === $this->get_config( 'url' ),
			]
		);

		// Set image config.
		$this->merge_config(
			[
				'alt'            => $this->get_alt_text(),
				'caption'        => ! empty( $this->config['attachment_id'] ) ? wp_get_attachment_caption( $this->config['attachment_id'] ) : '',
				'height'         => $image_meta['height'] ?? 0,
				'lqip_src'       => $this->get_lqip_src(),
				'url'            => $this->get_config( 'url' ),
				'picture'        => $picture,
				'sizes'          => $this->get_sizes(),
				'source_tags'    => $picture ? $this->get_source_tags() : [],
				'src'            => $this->get_src(),
				'srcset'         => $this->get_srcset(),
				'width'          => $image_meta['width'] ?? 0,
			]
		);

		return $this;
	}

	/**
	 * Retrieve aspect ratio value from either image size config, image component config, or original width and height values
	 *
	 * @param array $size_config Configuration for current image size.
	 * @return string
	 */
	public function get_aspect_ratio( $size_config ) {
		$aspect_ratio = ( $size_config['aspect_ratio'] ?? $this->config['aspect_ratio'] ) ?? false;

		// Useful if you're ouptutting an image with only one constrained dimension (like a max height or max width, but no specific aspect ratio). Usually involves `fit`, `w`, or `h` transforms.
		if ( 'auto' === $aspect_ratio ) {
			$image_meta = wp_get_attachment_metadata( $this->config['attachment_id'] );
			if ( ! empty( $image_meta['width'] ) && ! empty( $image_meta['height'] ) ) {
				return intval( $image_meta['height'] ) / intval( $image_meta['width'] );
			}

			return false;
		}

		return $aspect_ratio;
	}

	/**
	 * Retrieve alt text for current image.
	 *
	 * @return string
	 */
	public function get_alt_text() {
		if ( ! empty( $this->config['alt'] ) ) {
			return esc_attr( $this->config['alt'] );
		}

		$attachment_id = $this->config['attachment_id'];

		if ( ! empty( $attachment_id ) ) {
			// First check attachment alt text field.
			$image_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
			if ( ! empty( $image_alt ) ) {
				return esc_attr( $image_alt );
			}

			// Use image caption as a fallback.
			if ( ! empty( $this->config['caption'] ) ) {
				return esc_attr( $this->config['caption'] );
			}

			// Use image description as final fallback.
			$post = get_post( $attachment_id );
			if ( $post ) {
				// We can't rely on get_the_excerpt(), because it relies on The Loop
				// global variables that are not correctly set within the Irving context.
				return esc_attr( $post->post_excerpt );
			}
		}

		return '';
	}

	/**
	 * Prepare config for use with an <picture> tag.
	 *
	 * @return array
	 */
	public function get_source_tags() {
		$source_tags = [];
		$sources     = (array) $this->config['sources'];

		// Don't set this if we're using a basic <img> tag or using the fallback image.
		if ( $this->get_config( 'use_basic_img' ) || $this->get_config( 'using_fallback' ) ) {
			return [];
		}

		foreach ( $sources as $params ) {
			// Get source URL.
			$transforms = $params['transforms'];
			$src_url    = $this->apply_transforms( $transforms );

			// Add retina source to srcset, if applicable.
			if ( $this->config['retina'] ) {
				$retina_url    = $this->apply_transforms( $transforms, 2 );
				$srcset_string = "{$src_url} 1x, {$retina_url} 2x";
			} else {
				$srcset_string = $src_url;
			}

			// Construct source tag.
			$source_tags[] = [
				'srcset' => $srcset_string,
				'media'  => esc_attr( $this->get_media( $params['media'] ?? '' ) ),
			];
		}

		return $source_tags;
	}

	/**
	 * Get source attribute for this image.
	 *
	 * @return string LQIP source URL
	 */
	public function get_src() : string {
		if ( $this->get_config( 'using_fallback' ) || ! $this->get_config( 'use_basic_img' ) ) {
			return $this->get_config( 'url' );
		}

		return $this->apply_transforms( $this->get_config( 'sources' )[0]['transforms'] );
	}

	/**
	 * Get source URL for lqip functionality
	 *
	 * @return string LQIP source URL
	 */
	public function get_lqip_src() : string {
		$aspect_ratio = $this->config['aspect_ratio'];

		// Return default if no aspect ratio is set.
		if ( empty( $aspect_ratio ) || strstr( $this->get_config( 'url' ), 'data:' ) ) {
			return $this->get_config( 'lqip_src' );
		}

		return $this->apply_transforms(
			[
				'quality' => [ 60 ],
				'resize'  => [ 60, 60 * $aspect_ratio ],
			]
		);
	}

	/**
	 * Get the srcset for <img> element.
	 *
	 * @return array Sources.
	 */
	public function get_srcset() : string {
		$srcset  = [];
		$sources = (array) $this->config['sources'];

		// Don't set this if we're using a basic <img> tag or using the fallback image.
		if ( $this->get_config( 'use_basic_img' ) || $this->get_config( 'using_fallback' ) ) {
			return '';
		}

		foreach ( $sources as $params ) {
			// Get source URL.
			$src_url    = $this->apply_transforms( $params['transforms'] );

			// Get descriptor.
			$descriptor = $params['descriptor'] ?? 0;

			// Add retina source to srcset, if applicable.
			if ( is_numeric( $descriptor ) ) {
				if ( $this->config['retina'] && ( empty( $params['retina'] ?? '' ) || $params['retina'] ) ) {
					$retina_url        = $this->apply_transforms( $params['transforms'], 2 );
					$retina_descriptor = absint( $descriptor ) * 2;
					$srcset[]          = "{$retina_url} {$retina_descriptor}w";
				}

				$srcset[] = "{$src_url} {$descriptor}w";
			} else {
				$srcset[] = "{$src_url} {$descriptor}";
			}
		}

		return html_entity_decode( esc_attr( implode( $srcset, ',' ) ) );
	}

	/**
	 * Prepare config for use with an <picture> tag.
	 *
	 * @return string
	 */
	public function get_sizes() : string {
		$sizes   = [];
		$sources = (array) $this->config['sources'];
		$default = false;

		// Don't set this if we're using a basic <img> tag or using the fallback image.
		if ( $this->get_config( 'use_basic_img' ) || $this->get_config( 'using_fallback' ) ) {
			return '';
		}

		foreach ( $sources as $params ) {

			// Ensure descriptor is set.
			if ( ! isset( $params['descriptor'] ) ) {
				$params['descriptor'] = 0;
			}

			if ( is_numeric( $params['descriptor'] ) ) {
				if ( ! empty( $params['default'] ) ) {
					$default = "{$params['descriptor']}px";
					continue;
				}

				if ( ! empty( $params['media'] ) ) {
					$sizes[] = "{$this->get_media( $params['media'] )} {$params['descriptor']}px";
				}
			}
		}

		$sizes[] = ! empty( $default ) ? $default : '100vw';

		return esc_attr( implode( $sizes, ',' ) );
	}

	/**
	 * Get media attribute for a specific source
	 *
	 * @param array $media_params Media query parameters.
	 * @return string Media attribute content.
	 */
	public function get_media( $media_params ) : string {
		$breakpoints = self::$breakpoints;

		if ( ! is_array( $media_params ) ) {
			return false;
		}

		// Use custom media if it's set.
		if ( ! empty( $media_params['custom'] ) ) {
			return $media_params['custom'];
		}

		// Compile min and max width settings.
		$min_width = ! empty( $media_params['min'] ) ?
			"(min-width: {$breakpoints[ $media_params['min'] ]})" :
			false;
		$max_width = ! empty( $media_params['max'] ) ?
			"(max-width: {$breakpoints[ $media_params['max'] ]})" :
			false;

		if ( $min_width && $max_width ) {
			return "{$min_width} and {$max_width}";
		} elseif ( $min_width ) {
			return $min_width;
		} elseif ( $max_width ) {
			return $max_width;
		}

		// Default to 'all'.
		return 'all';
	}

	/**
	 * Disable lazyloading for this instance.
	 */
	public function disable_lazyload() {
		$this->set_config( 'lazyload', false );
		return $this;
	}

	/**
	 * Set aspect ratio of image for use with CSS intrinsic ratio sizing.
	 * Set to false to turn off intrinsic sizing.
	 *
	 * @param float|bool $ratio Aspect ratio of image expressed as a decimal.
	 */
	public function aspect_ratio( $ratio ) {
		$this->set_config( 'aspect_ratio', $ratio );
		return $this;
	}

	/**
	 * Set the width of an image. Defaults to pixels, supports percentages.
	 *
	 * @see  https://developer.wordpress.com/docs/photon/api/#w
	 *
	 * @param int $width  Resized width.
	 * @param int $density_multiplier screen density multiplier.
	 * @return array Transform values prepared to be added as query args.
	 */
	public function w( int $width, $density_multiplier = 1 ) {
		$value = absint( $width ) * $density_multiplier;
		return [ 'w' => $value ];
	}

	/**
	 * Set the height of an image. Defaults to pixels, supports percentages.
	 *
	 * @see  https://developer.wordpress.com/docs/photon/api/#h
	 *
	 * @param int $height  Resized height.
	 * @param int $density_multiplier screen density multiplier.
	 * @return array Transform values prepared to be added as query args.
	 */
	public function h( int $height, $density_multiplier = 1 ) {
		$value = absint( $height ) * $density_multiplier;
		return [ 'h' => $value ];
	}

	/**
	 * Crop an image by percentages x-offset,y-offset,width,height (x,y,w,h).
	 * Percentages are used so that you don’t need to recalculate the cropping
	 * when transforming the image in other ways such as resizing it.Original
	 * image: 4-MCM_0830-1600×1064.jpgcrop=12,25,60,60 takes a 60% by 60%
	 * rectangle from the source image starting at 12% offset from the left and
	 * 25% offset from the top.
	 *
	 * @see  https://developer.wordpress.com/docs/photon/api/#crop
	 *
	 * @param int|string $x      X-offset value.
	 * @param int|string $y      Y-offset value.
	 * @param int|string $width  Width.
	 * @param int|string $height Height.
	 * @return array Transform values prepared to be added as query args.
	 */
	public function crop( $x, $y, $width, $height ) {
		$value = sprintf(
			'%1$s,%2$s,%3$s,%4$s',
			$x,
			$y,
			$width,
			$height
		);
		return [ 'crop' => $value ];
	}

	/**
	 * Resize and crop an image to exact width,height pixel dimensions. Set the
	 * first number as close to the target size as possible and then crop the
	 * rest. Which direction it’s resized and cropped depends on the aspect
	 * ratios of the original image and the target size.
	 *
	 * @see  https://developer.wordpress.com/docs/photon/api/#resize
	 *
	 * @param int $width  Resized width.
	 * @param int $height Resized height.
	 * @param int $density_multiplier screen density multiplier.
	 * @return array Transform values prepared to be added as query args.
	 */
	public function resize( int $width, int $height, $density_multiplier = 1 ) {
		$value = sprintf(
			'%1$d,%2$d',
			absint( $width ) * $density_multiplier,
			absint( $height ) * $density_multiplier
		);
		return [ 'resize' => $value ];
	}

	/**
	 * Fit an image to a containing box of width,height dimensions. Image
	 * aspect ratio is maintained.
	 *
	 * @see  https://developer.wordpress.com/docs/photon/api/#fit
	 *
	 * @param int $width  Resized width.
	 * @param int $height Resized height.
	 * @param int $density_multiplier screen density multiplier.
	 * @return array Transform values prepared to be added as query args.
	 */
	public function fit( int $width, int $height, $density_multiplier = 1 ) {
		$value = sprintf(
			'%1$d,%2$d',
			absint( $width ) * $density_multiplier,
			absint( $height ) * $density_multiplier
		);
		return [ 'fit' => $value ];
	}

	/**
	 * Modify compression quality of source image
	 *
	 * @see  https://developer.wordpress.com/docs/photon/api/#quality
	 *
	 * @param int $percentage Quality percentage.
	 * @return array Transform values prepared to be added as query args.
	 */
	public function quality( int $percentage ) {
		$value = absint( $percentage );
		return [ 'quality' => $value ];
	}

	/**
	 * Apply a single transform to the current source URL
	 *
	 * @see  https://developer.wordpress.com/docs/photon/api/#fit
	 *
	 * @param array  $transform Transform to apply.
	 * @param array  $args      Array of transform arguments.
	 * @param string $url       Url to which to apply transforms. Defaults to the original attachment URL.
	 * @return string New URL with transform applied
	 */
	public function apply_transform( $transform, $args, $url = '' ) {
		$source_url = ! empty( $url ) ? $url : $this->get_config( 'url' );

		// If transform method doesn't exist, return original URL.
		if ( ! method_exists( $this, $transform ) ) {
			return $source_url;
		}

		// Get prepared transform args.
		$transform_args = call_user_func_array(
			array( $this, $transform ),
			$args
		);

		// Apply the transform to URL.
		return add_query_arg( $transform_args, $source_url );
	}

	/**
	 * Apply an array of transforms to an image src URL
	 *
	 * @param array  $transforms         parameters for all transforms to apply.
	 * @param int    $density_multiplier screen density multiplier.
	 * @param string $url                Url to which to apply transforms. Defaults to the original attachment URL.
	 * @return string Url with all transforms applied
	 */
	public function apply_transforms( array $transforms, $density_multiplier = 1, $url = '' ) {
		// Loop through transforms.
		foreach ( $transforms as $transform => $values ) {
			// Add multiplier.
			$values[] = $density_multiplier;
			$url = $this->apply_transform( $transform, $values, $url );
		}

		return $url;
	}
}
