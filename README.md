Garmin for WordPress
====================

A github mirror of the [WordPress plugin](http://wordpress.org/plugins/garmin-connect/).

Description
-----------

This plugin creates a widget for WordPress blogs that can show a number of recent workouts from Garmin Connect. It allows for customization of whether or not to display properties of the workout, including:

 - Filter by type of workout
 - Show only the most recent _x_ workouts
 - Calories burned
 - Heart rate
 - Distance
 - Average pace
 - Description
 - ...and others

Installation
------------

If you want to automatically upload the plugin to your WordPress installation (and have the ability to do so), follow these steps:

1. Go to the Administrator dashboard. Select "Plugins -> Add New" from the menu on the left.
2. From the links at the top, click "Upload."
3. Navigate to the zip file containing the plugin and select it.

If you prefer to manually upload the plugin, or don't have the ability to automatically upload it, follow these steps:

1. Navigate to your WordPress plugins directory. This is located in `~/wordpress_rootdir/wp-content/plugins/`.
2. Unzip the Garmin for WordPress plugin, and place the folder in the `plugins` directory.

Once the plugin is in place, you can activate it via the Plugins menu on the Administrator interface. Once activated, go to the Settings panel. A new submenu titled "Garmin Connect" should appear. Click on it, and configure the plugin with your Garmin Connect username and password.

Finally, to display the plugin on your WordPress site, navigate to the Widgets menu. "Garmin Connect" should appear as an available plugin. Simply drag it to whatever portion of your site you wish to display it on, configure it from the available options, and you're set to go!

Other
-----
This plugin was originally created by WordPress user "Coded Robot LLC." However, the plugin has not been updated since Dec 2011. For that reason, I forked it here so I could provide slightly more up-to-date maintenance. Credit for the creation of the widget, however, does not rest with me.

Furthermore, I have relicensed this plugin (see below). Its original license was GPL, but this release carries the Apache 2.0 license.

License
-------

    Copyright 2013 Shannon Quinn

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

        http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.