Components
==========
Build WordPress themes using Components.

Components are reusable building blocks. They provide a base for easily
writing and maintaing nearly anything that can be considered stand alone
and discrete in functionality. If you follow the primary principles of
developing with components you'll cut time from your development cycle.

## Table of Contents
* Components
    * Parent Component Class - Root component class that all other components extend.
    * Body - Helper for display the body content of your site. Could correspond to the `<body>` or `<main>` tags.
    * Byline - An author byline component.
    * Gutenberg Content - Helper for converting gutenberg blocks into a component instance (or an instance of the Raw HTML component).
    * Head - Helper for display tags within `<head>`.
    * Raw HTML - Helper representing raw HTML.
    * Image - Component representing responsive images, integrated with WordPress attachments.
    * Menu - Component representing a WordPress menu.
    * Menu Item - Component representing a single item in a WordPress menu.
    * Pagination - Component representing pagination on an archive of posts.
    * Pagination Item - A single pagination item (such as a page number).
    * Social Links - Links to social profiles.
    * Social Sharing - Links to share content on various social media platforms.
    * Social Item - A single social item.
    * Term - Component representing a WordPress taxonomy term.
* Integrations
    * Disqus - Integrate with the Disqus commenting platform.
    * Google Analytics - Integrate with GA.
    * Google Tag Manager - Basic integration with GTM.
    * Parse.ly - Basic integration with Parse.ly.
* Heleprs
    * Button - Helper representing a reusable button component.
* Blocks
    * Core OEmbed - Component for integrating with WordPress Core OEmbed functionality.
* Traits - PHP Traits to supply your components with helper methods for integrating with and managing data pulled from various WordPress entities.
    * User - Integrate with core WordPress user objects.
    * Guest Author - Integrate with Coauthors Plus Guest Author posts.
    * Author - Combine Users and Guest Authors.
    * Menu - Integrate with core WordPress Menus.
    * Menu Item - Use in conjuction with the Menu trait to work with individual menu items.
    * Post - Integrate data from any WordPress post.
    * Query - Integrate data from a WP_Query, whether custom or default.
    * Term - Integrate with a taxonomy term.
    * Widget Sidebar - Helpers for using core WordPress Widgets.

### Image Component
This component represents any image uploaded to the WP media library, including featured images for all content types. Functionality is also included for:
* Responsive image markup using both:
    * `<img>` with `srcset` and `sizes`.
    * `<picture>`/`<source>` for art direction.
* Determining if an image should be lazyloaded or not (lazyload implementation is up to you, though).
* Configurable handling of image sizes/positions.
* Integration with the [Photon API](https://developer.wordpress.com/docs/photon/api/) to apply image transformations. Currently supported transforms:
    * `w` - set the width of an image
    * `h` - set the height of an image
    * `crop` - crop an image
    * `resize` - resize and crop an image to an exact width and height
    * `fit` - fit an image into a containing width and height while maintaining original aspect ratio
    * `quality` - modify compression quality of image
* Handling of [intrinsic ratio sizing](https://alistapart.com/d/creating-intrinsic-ratios-for-video/example2.html) using CSS.

#### Using the component
Creating a new image component consists primarily of supplying the component with either a Post ID or an Attachemnt ID in combination with a string representing an image size configuration. For example:
```php
( new Image() )
    ->set_attachment_id( $featured_image_id )
    ->size( 'large-feature' );
```
Below is more detailed documentation on the methods available:
* `set_post_id` - Provide a Post ID. The thumbnail of that post will be used to create the image. You must use either this method or `set_attachment_id` for the Image component to function properly.
* `set_attachment_id` - Provide an Attachment ID. You must use either this method or `set_post_id` for the Image component to function properly.
* `set_config_for_size` - Provide an image size to use for creating the `srcset` attribute or `<source>` tags.
* `set_url` - Set a new URL for the image
* `disable_lazyload` - Disable lazy loading for this image.
* `aspect_ratio` - Set aspect ratio for use with CSS intrinsic ratio sizing. Pass `false` to this function to disable CSS sizing entirely.

In addition, there are two static methods for configuring image sizes which must be used to allow `set_config_for_size` to work:
* `register_sizes` - Register image sizes and corresponding configurations (see configuration docs below)
* `register_breakpoints` - Register breakpoints for use in media attributes (see configuration docs below)

## Configuration
Some configuration is required to use the image component. You need to declare at least one image size and configure its corresponding transforms and descriptor using the `register_sizes` method of the `Image` class. Example:
```php
/**
 * Register image sizes for use by the Image component.
 */
\WP_Components\Image::register_sizes( [
    'large-feature' => [
        'sources' => [
            [
                'transforms' => [
                    'resize' => [ 950, 627 ],
                ],
                'descriptor' => 949,
            ],
            [
                'transforms' => [
                    'resize' => [ 800, 528 ],
                ],
                'descriptor' => 800,
            ],
        ],
    ],
] );
```

In addition, if you need to use a `<picture>` element you'll need to register breakpoints and use them in the `media` property for each image source. The reason for this is `<source>` tags will be ignored if a `media` attribute is not provided. Example:

```php

\WP_Components\Image::register_breakpoints( [
    'xl' => '80rem',
    'lg' => '64rem',
    'md' => '48rem',
    'sm' => '32rem',
] );

\WP_Components\Image::register_sizes( [
    'large-feature' => [
        'sources' => [
            [
                'transforms' => [
                    'resize' => [ 950, 627 ],
                ],
                'descriptor' => 949,
                'media' => [ 'min' => 'lg' ]
            ],
            [
                'transforms' => [
                    'resize' => [ 800, 528 ],
                ],
                'descriptor' => 800,
                'media' => [ 'max' => 'md' ]
            ],
        ],
    ],
] );
```

Below is a more detailed breakdown of each configuration properties when registering sizes:
* [`size_string`] - image size key. Used when calling `set_config_for_size` method before rendering the component.
    * `sources` - array of sources to include in `srcset` attribute in the case of an `<img>` tag, or `<source>` tags in the case of `<picture>`
        * `transforms` - array of Photon transformations to apply to this source. You can apply any number or combination of transforms per-source.
            * [`transform_string`] - configuration for Photon transforms. Any of the transforms listed in the first section of this document may be configured here.
        * `descriptor` - an integer representing a width descriptor for the image. This is required for the browser to determine which image source to choose (see documentation for [`srcset`](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img) attribute)
        * `media` - an array describing the media conditions for which this source should be applied. Media has three options:
            * `min` - a string representing a breakpoint (taken from your registered breakpoints) for use as a `min-width`
            * `max` - a string representing a breakpoint (taken from your registered breakpoints) for use as a `max-width`
            * `custom` - a custom breakpoint value. If used, this should contain the _full_ representation of the media query. For example, if you want both a min and max height you need to write out `(min-height: 400px) and (max-height: 600px)`
    * `aspect_ratio` - Aspect ratio represented as `height / width` or a decimal. This can be used to set intrinsic CSS on the resulting image markup. Set to `false` to bypass intrinsic sizing.
    * `lazyload` - Turn on or off lazyloading for this image size.
    * `fallback_image_url` - Set a size-specific fallback image. Useful if you have, for example, an avatar image that needs to have different fallback from all other images on the site.
    * `retina` - Turn on or off automatic handling of `2x` image resolution for retina screens.

And when registering breakpoints:
* `xl` - name for the breakpoint, for use with the `media` `min` or `max` settings
* `80rem` - breakpoint that will be supplied as the value for `min-width` or `max-width`
