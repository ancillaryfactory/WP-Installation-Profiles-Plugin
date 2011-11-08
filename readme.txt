=== WP Install Profiles ===

Contributors: rockgod100
Plugin Name: Installation Profiles
Plugin URI: https://github.com/ancillaryfactory/WP-Installation-Profiles-Plugin
Tags: wp, plugins, installation
Author URI: http://www.ancillaryfactory.com
Author: Jon Schwab
Requires at least: 3.0
Tested up to: 3.2
Stable tag: 1.0
Version: 1.0

Download collections of plugins automatically from the Wordpress plugin directory.

== Installation ==

1. Upload `install-profiles` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to Plugins -> Bulk Install Profiles


== Description ==
Save time setting up new sites by automatically downloading groups of plugins. Add new plugins by adding the slug from the plugin's url in the Wordpress plugin directory. For instance, the plugin "All In One SEO Pack" is listed here: http://wordpress.org/extend/plugins/all-in-one-seo-pack/. Add "All In One SEO Pack" to an installation profile by adding 'all-in-one-seo-pack' in the plugins field (one plugin per line).


WP Install Profiles (WPIP) allows users to define groups of plugins, called profiles. Once a profile has been entered, WPIP calls to the Wordpress Plugin Directory, downloads the plugin files and unzips them to the installation's plugins folder. Additionally, WPIP saves the profile in a downloadable format, so that the user can upload it to his/her next site and download the same plugins with a single click. 

See Install Profiles in action: [http://www.youtube.com/watch?v=W-mBhPA1XGA](http://www.youtube.com/watch?v=W-mBhPA1XGA)

== Screenshots ==

1. Plugins -> Bulk Download Profiles

== Changelog ==
= 1.0 =

* Added feature to download the profile of the current site, to easily set up a new site based on an existing site's profile

* Better handling of plugin names

= 0.7 =

* First release