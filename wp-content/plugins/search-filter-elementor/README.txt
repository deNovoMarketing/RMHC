=== Search & Filter - Elementor Extension ===
Contributors: CodeAmp
Donate link:
Tags: elementor, posts, woocommerce, products, portfolio, archive
Requires at least: 3.7
Tested up to: 5.4
Stable tag: 1.0.8

Adds Search & Filter integration for Elementor - filter your Posts, Posts Archive, Portfolio, Products & Products Archive widgets

== Description ==

Adds Search & Filter integration for Elementor - filter your Posts, Posts Archive, Portfolio, Products & Products Archive widgets

= Features: =

* Simply choose "Search & Filter" as a data source in your Elementor widgets and get setup instantly.
* Ajax is preconfigured - all you need to do is enable Ajax in your search forms and you're good to go.
* Extra: Adds a "no results found" message to widgets that wouldn't usually support it (posts, products, portfolio)


== Installation ==

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `search-filter-elementor.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard


= Using FTP =

1. Download `search-filter-elementor.zip`
2. Extract the `search-filter-elementor` directory to your computer
3. Upload the `search-filter-elementor` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard


== Frequently Asked Questions ==


== Screenshots ==


== Changelog ==

= 1.0.8 =
* Fix - an undefined index error being thrown
* Fix - a JS error relating the window not being declared properly when accessing `elementorFrontend`

= 1.0.7 =
* Improvement - register as a S&F extension to prevent conflicts
* Fix - be more specific with the ajax pagination selector to prevent conflicts between instances
* New - hooks to render custom content at the start/end of rendering (add content inside the ajax container)

= 1.0.6 =
* Fix - an issue throwing a fatal error when using Elementor Free version only
* New - add support for Anywhere Elementor Post Blocks

= 1.0.5 =
* Fix - an issue with range sliders being duplicated in Elementor popups
* Fix - remove some unnecessary error_log calls

= 1.0.4 =
* Fix - a JS warning when editing a popup with our search form in
* Fix - the "No Results" message was not working for the Product widget

= 1.0.3 =
* New - Support search forms in popups
* Fix - don't assume Elementor is being used to build archives, check first before configuring settings
* Fix - notice text when minimum PHP version is not met
* Improvement - to workaround Polylang issues, we now show search forms for all languages in Elementor Widgets when Polylang is enabled

= 1.0.2 =
* Fix - a PHP warning when no pagination was set
* Fix - only re-init front end JS of elements we've updated - no more `elementorFrontend.init()`

= 1.0.1 =
* Remove some unused defines
* Added in a missing `wp_reset_postdata()`

= 1.0.0 =
* Initial release


== Upgrade Notice ==

= 1.0.0 =
Initial release
