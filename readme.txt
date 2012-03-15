=== WP Install Profiles ===

Contributors: rockgod100
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=GC8T5GGQ4AWSA
Plugin Name: Installation Profiles
Plugin URI: http://plugins.ancillaryfactory.com
Tags: wp, plugins, installation, admin, administration
Author URI: http://www.ancillaryfactory.com
Author: Jon Schwab
Requires at least: 3.1
Tested up to: 3.3
Stable tag: 3.0
Version: 3.0

Download custom collections of plugins automatically from the WordPress plugin directory.

== Installation ==

1. Upload `install-profiles` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' Admin screen
1. Go to Plugins -> Bulk Install Profiles


== Description ==
Save time setting up new sites by automatically downloading groups of plugins. Add new plugins by adding the slug from the plugin's url in the WordPress plugin directory. For instance, the plugin "All In One SEO Pack" is listed here: `http://wordpress.org/extend/plugins/all-in-one-seo-pack/`. Add "All In One SEO Pack" to an installation profile by adding `all-in-one-seo-pack` in the plugins field (one plugin per line).

WP Install Profiles (WPIP) allows users to define groups of plugins, called profiles. Once a profile has been entered, WPIP calls to the WordPress Plugin Directory, downloads the plugin files and unzips them to the site's plugins folder. Additionally, WPIP saves the profile in a downloadable format, so you can upload it to your next site and download the same plugins with a single click. 

Store your profiles online at http://plugins.ancillaryfactory.com and import them easily into all of your WordPress installs. [Learn more and create an account](http://plugins.ancillaryfactory.com)

See Install Profiles in action: [http://www.youtube.com/watch?v=W-mBhPA1XGA](http://www.youtube.com/watch?v=W-mBhPA1XGA)

== Screenshots ==

1. Plugins -> Bulk Download Profiles
2. Create and save your install profiles online - [http://plugins.ancillaryfactory.com](http://plugins.ancillaryfactory.com)
3. Import online profiles with your WPIP username

== Changelog ==
= 3.0 =

* New tabbed UI
* More security updates


= 2.5 =

* Major security enhancements - Thanks, [Julio](http://www.boiteaweb.fr/)!
* When downloading a profile of the current site, you can now choose from a list of all plugins, both active and inactive. Great idea, Marikamitsos!
* Bugfixes for Windows hosting environments

= 2.0 =

* Save profiles online
* Minor security improvements

= 1.0 =

* Added feature to download the profile of the current site, to easily set up a new site based on an existing site's profile

* Better handling of plugin names

= 0.7 =

* First release

== Upgrade Notice ==

= 2.5 =

Upgrade STRONGLY recommended to fix several security vulnerabilities

== Other Notes ==

= Required PHP libraries =

* SimpleXML
* ZipArchive

These libraries are installed by default on most shared hosting accounts, but they may need to be installed manually if your site is hosted on Media Temple.
