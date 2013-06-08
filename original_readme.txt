=== Garmin Connect ===
Contributors: Coded Robot LLC
Donate link: http://garminconnect.codedrobot.com/
Tags: sports, garmin, garmin connect, fitness, gps, running, triathlon
Requires at least: 2.8
Tested up to: 3.4
Stable tag: 1.1.8

Provides a widget for displaying latest activities from Garmin Connect on your site

== Description ==

Provides a widget for displaying latest activities from Garmin Connect on your site.

Related Links:

* [FAQ Page](http://garminconnect.codedrobot.com/faq/).
* [Different Widget Styles](http://garminconnect.codedrobot.com/widget-styles/)

PHP5 is required for this widget to work properly.

== Installation ==

1. Delete any existing `garmin-connect` folder from the `/wp-content/plugins/` directory
2. Upload `garmin-connect` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to the Garmin Connect panel under the 'Settings' menu and add your Garmin Connect Username and Password and the settings you want
5. Go to the Widgets panel under the 'Appearance' menu and drag a Garmin Connect widget to your side bar.  Configure the options as you wish

== Frequently Asked Questions ==

= How can I display a map of my activity in a post? =

Use the Garmin Connect shortcode in the body of your post.  An example usage is as follows: `[gcmap act="7964312" class="alignleft"]`. 

The value for 'act' is the value at the end of your Garmin Connect activity URL, i.e. - http://connect.garmin.com/activity/7964312

The value for class is the CSS class that will be used.  Appropriate values are 'alignleft', 'alignright', or 'aligncenter'.  It may also be left blank.  You may add your own CSS stylings in the garminconnect.css file.

= Can the widget display in kilometers instead of miles? =

Yes.  Change your settings within Garmin Connect.  Visit [Garmin Connect Settings](http://connect.garmin.com/settings) and on the 'Display Preferences' tab, changed 'Measurement Units' to 'Metric'

= What causes the error message 'Plugin could not be activated because it triggered a fatal error.'? =

This is due to trying to activate the plugin using PHPv4.  PHPv5 is required to use this plugin.  Additional error message details state "Parse error: syntax error, unexpected T_STRING, expecting T_OLD_FUNCTION or T_FUNCTION or T_VAR or '}'"

= Why are no activities showing up? =

In order for activities to show up in the widget, you must have given the activity a name in Garmin Connect.  Ensure that activities do not have the name 'Untitled'.

= Are private activities shown in the widget? =

No

= How do I know what the different widget styles look like =

You can view all the available widget styles in action at the [Widget Styles Page](http://garminconnect.codedrobot.com/widget-styles/)

== Screenshots ==

1. Screenshot of the plugin in action
2. Screenshot of a different style of the plugin
3. Screenshot of the configuration options.
4. Screenshot of a post with the activity map automatically generated.

== Changelog ==

= 1.1.8 =
* Fixed bug with signin caused by latest release of Garmin Connect website
* Updated version of SimplePie included
* Minor bug fixes - thanks to JohnD

= 1.1.5 =
* Bug fix where plugin was unable to retrieve activities

= 1.1.4 =
* Readme Fix

= 1.1.3 =
* Fixed bug with previous release related to dates of Jan 1, 1970
* Updated permissions so the regular administrator of a blog in a multi-blog site install can configure Garmin Connect settings

= 1.1.2 =
* Fixed issue where some activities would always return date of Jan 1, 1970
* New option to set the date and time format for the accordion style widgets

= 1.1.1 =
* Misc Bug Fixes
* Fix styling of widget title to be consistent with other WP plugins
* Fix bug where issues occurred if wordpress was not installed in root directory.

= 1.1.0 =
* Support for Wordpress 3.0
* Added shortcode to allow automatic creation of a Google Map from a Garmin Connect activity.

= 1.0.3 =
* Fixed bug with signin caused by latest release of Garmin Connect website

= 1.0.2 =
* Fixed bug due to new version of Garmin Connect that broke the plugin

= 1.0.1 =
* Fixed bug where cadence was not displayed for cycling activities
* Fixed a few CSS issues
* Added message that PHP5 is required.

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.1.8 =
* Fixed bug with signin caused by latest release of Garmin Connect website
* Updated version of SimplePie included
* Minor bug fixes - thanks to JohnD

= 1.1.5 = 
* Bug fix where plugin was unable to retrieve activities

= 1.1.4 =
* Readme Fix

= 1.1.3 =
* Fixed bug with previous release related to dates of Jan 1, 1970
* Updated permissions so the regular administrator of a blog in a multi-blog site install can configure Garmin Connect settings

= 1.1.2 =
* Fixed issue where some activities would always return date of Jan 1, 1970
* New option to set the date and time format for the accordion style widgets

= 1.1.1 =
* Misc Bug Fixes
* Fix styling of widget title to be consistent with other WP plugins
* Fix bug where issues occurred if wordpress was not installed in root directory.

= 1.1.0 =
* Support for Wordpress 3.0
* Added shortcode to allow automatic creation of a Google Map from a Garmin Connect activity.

= 1.0.3 =
* Fixed bug with signin caused by latest release of Garmin Connect website

= 1.0.2 =
* Fixed bug due to new version of Garmin Connect that broke the plugin

= 1.0.1 =
* Fixed bug where cadence was not displayed for cycling activities
* Fixed a few CSS issues
* Added message that PHP5 is required.

= 1.0.0 =
* Initial release

== Licence ==

This plugin is free for anyone.  It is GPL licenses, so its free for use on both personal and commercial sites.  If you enjoyed the plugin, you can always make a [donation](http://garminconnect.codedrobot.com)

