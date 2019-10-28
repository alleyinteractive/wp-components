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
* Helpers
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
